<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Throwable;

class ThermalReceiptService
{
    private const ESC_INIT = "\x1B\x40";

    private const ALIGN_LEFT = "\x1B\x61\x00";

    private const ALIGN_CENTER = "\x1B\x61\x01";

    private const BOLD_ON = "\x1B\x45\x01";

    private const BOLD_OFF = "\x1B\x45\x00";

    private const SIZE_NORMAL = "\x1D\x21\x00";

    private const SIZE_DOUBLE = "\x1D\x21\x11";

    private const PARTIAL_CUT = "\x1D\x56\x42\x00";

    private const CASH_DRAWER_KICK = "\x1B\x70\x00\x19\xFA";

    public function columnsForWidth(int $paperWidth): int
    {
        return $paperWidth === 58 ? 32 : 48;
    }

    public function isAllowedPrinterHost(string $host): bool
    {
        $normalized = strtolower(trim($host, " \t\n\r\0\x0B[]"));
        if ($normalized === '') {
            return false;
        }

        $configuredHost = strtolower(trim((string) config('pos.printer.host', '127.0.0.1'), " \t\n\r\0\x0B[]"));

        if (in_array($normalized, ['127.0.0.1', 'localhost', '::1', $configuredHost], true)) {
            return true;
        }

        if (filter_var($normalized, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }

        $long = ip2long($normalized);
        if ($long === false) {
            return false;
        }

        // Reject link-local metadata range (169.254.0.0/16) explicitly
        if (($long & 0xFFFF0000) === ip2long('169.254.0.0')) {
            return false;
        }

        // Allow RFC1918 private LAN ranges only
        $isPrivate = (($long & 0xFF000000) === ip2long('10.0.0.0'))
            || (($long & 0xFFF00000) === ip2long('172.16.0.0'))
            || (($long & 0xFFFF0000) === ip2long('192.168.0.0'));

        return $isPrivate;
    }

    public function buildEscPosPayload(Order $order, int $paperWidth = 80, bool $openCashDrawer = false): string
    {
        $cols = $this->columnsForWidth($paperWidth);
        $divider = str_repeat('-', $cols)."\n";

        $storeName = $this->sanitizeText((string) config('pos.store_name', 'IMS-POS'));
        $storeAddress = $this->sanitizeText((string) config('pos.store_address', ''));
        $storePhone = $this->sanitizeText((string) config('pos.store_phone', ''));
        $footer = $this->sanitizeText((string) config('pos.receipt_footer', 'Thank you for your business!'));

        $payload = self::ESC_INIT;
        $payload .= self::ALIGN_CENTER.self::BOLD_ON.self::SIZE_DOUBLE.$storeName."\n";
        $payload .= self::SIZE_NORMAL.self::BOLD_OFF;
        if ($storeAddress !== '') {
            $payload .= $storeAddress."\n";
        }
        if ($storePhone !== '') {
            $payload .= $storePhone."\n";
        }
        $payload .= self::ALIGN_LEFT.$divider;

        $payload .= $this->formatColumns('Order:', $this->sanitizeText($order->order_no), $cols);
        $payload .= $this->formatColumns('Date:', $order->created_at ? $order->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'), $cols);
        $payload .= $this->formatColumns('Customer:', $this->sanitizeText($order->customer ? $order->customer->name : 'Walk-in / Guest'), $cols);
        $payload .= $this->formatColumns('Cashier:', $this->sanitizeText($order->createdBy ? $order->createdBy->name : '-'), $cols);
        $payload .= $this->formatColumns('Status:', strtoupper($this->sanitizeText($order->status)), $cols);
        $payload .= $divider;

        foreach ($order->orderDetails as $detail) {
            $name = $this->sanitizeText($detail->product ? $detail->product->product_name : 'Product');
            $unitPrice = '$'.number_format((float) $detail->unit_price, 2);
            $lineTotal = '$'.number_format((float) $detail->sub_total, 2);
            $qtyLine = "  {$detail->quantity} x {$unitPrice}";

            foreach ($this->wrapText($name, $cols) as $line) {
                $payload .= $line."\n";
            }
            $payload .= $this->formatColumns($qtyLine, $lineTotal, $cols);
        }

        $payload .= $divider;
        $payload .= $this->formatColumns("Subtotal ({$order->total_items} items)", '$'.number_format((float) $order->sub_total, 2), $cols);
        $payload .= $this->formatColumns('Discount', '-$'.number_format((float) $order->discount, 2), $cols);
        $payload .= $this->formatColumns('Tax', '$'.number_format((float) $order->tax, 2), $cols);
        $payload .= $divider;
        $payload .= self::BOLD_ON;
        $payload .= $this->formatColumns('TOTAL', '$'.number_format((float) $order->total, 2), $cols);
        $payload .= self::BOLD_OFF;
        $payload .= $this->formatColumns('Paid ('.$this->sanitizeText($order->paymentMethodLabel()).')', '$'.number_format((float) $order->paid_amount, 2), $cols);
        $payload .= $this->formatColumns('Change', '$'.number_format((float) $order->change_amount, 2), $cols);

        if (! empty($order->notes)) {
            $payload .= $divider;
            foreach ($this->wrapText('Note: '.$this->sanitizeText($order->notes), $cols) as $line) {
                $payload .= $line."\n";
            }
        }

        $payload .= $divider;
        $payload .= self::ALIGN_CENTER.$footer."\n\n\n\n";
        $payload .= self::ALIGN_LEFT.self::PARTIAL_CUT;
        if ($openCashDrawer) {
            $payload .= self::CASH_DRAWER_KICK;
        }

        return $payload;
    }

    public function buildPlainTextReceipt(Order $order, int $paperWidth = 80): string
    {
        $cols = $this->columnsForWidth($paperWidth);
        $divider = str_repeat('-', $cols)."\n";

        $storeName = $this->sanitizeText((string) config('pos.store_name', 'IMS-POS'));
        $storeAddress = $this->sanitizeText((string) config('pos.store_address', ''));
        $storePhone = $this->sanitizeText((string) config('pos.store_phone', ''));
        $footer = $this->sanitizeText((string) config('pos.receipt_footer', 'Thank you for your business!'));

        $lines = $this->centerText($storeName, $cols)."\n";
        if ($storeAddress !== '') {
            $lines .= $this->centerText($storeAddress, $cols)."\n";
        }
        if ($storePhone !== '') {
            $lines .= $this->centerText($storePhone, $cols)."\n";
        }
        $lines .= $divider;

        $lines .= $this->formatColumns('Order:', $this->sanitizeText($order->order_no), $cols);
        $lines .= $this->formatColumns('Date:', $order->created_at ? $order->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'), $cols);
        $lines .= $this->formatColumns('Customer:', $this->sanitizeText($order->customer ? $order->customer->name : 'Walk-in / Guest'), $cols);
        $lines .= $this->formatColumns('Cashier:', $this->sanitizeText($order->createdBy ? $order->createdBy->name : '-'), $cols);
        $lines .= $this->formatColumns('Status:', strtoupper($this->sanitizeText($order->status)), $cols);
        $lines .= $divider;

        foreach ($order->orderDetails as $detail) {
            $name = $this->sanitizeText($detail->product ? $detail->product->product_name : 'Product');
            $unitPrice = '$'.number_format((float) $detail->unit_price, 2);
            $lineTotal = '$'.number_format((float) $detail->sub_total, 2);
            foreach ($this->wrapText($name, $cols) as $line) {
                $lines .= $line."\n";
            }
            $lines .= $this->formatColumns("  {$detail->quantity} x {$unitPrice}", $lineTotal, $cols);
        }

        $lines .= $divider;
        $lines .= $this->formatColumns("Subtotal ({$order->total_items} items)", '$'.number_format((float) $order->sub_total, 2), $cols);
        $lines .= $this->formatColumns('Discount', '-$'.number_format((float) $order->discount, 2), $cols);
        $lines .= $this->formatColumns('Tax', '$'.number_format((float) $order->tax, 2), $cols);
        $lines .= $divider;
        $lines .= $this->formatColumns('TOTAL', '$'.number_format((float) $order->total, 2), $cols);
        $lines .= $this->formatColumns('Paid ('.$this->sanitizeText($order->paymentMethodLabel()).')', '$'.number_format((float) $order->paid_amount, 2), $cols);
        $lines .= $this->formatColumns('Change', '$'.number_format((float) $order->change_amount, 2), $cols);

        if (! empty($order->notes)) {
            $lines .= $divider;
            foreach ($this->wrapText('Note: '.$this->sanitizeText($order->notes), $cols) as $line) {
                $lines .= $line."\n";
            }
        }

        $lines .= $divider;
        $lines .= $this->centerText($footer, $cols)."\n\n";

        return $lines;
    }

    public function buildTestPayload(int $paperWidth = 80, string $host = '127.0.0.1', int $port = 9100): string
    {
        $cols = $this->columnsForWidth($paperWidth);
        $divider = str_repeat('-', $cols)."\n";
        $storeName = $this->sanitizeText((string) config('pos.store_name', 'IMS-POS'));

        $payload = self::ESC_INIT;
        $payload .= self::ALIGN_CENTER.self::BOLD_ON.self::SIZE_DOUBLE.$storeName."\n";
        $payload .= self::SIZE_NORMAL."THERMAL PRINTER TEST\n".self::BOLD_OFF;
        $payload .= self::ALIGN_LEFT.$divider;
        $payload .= $this->formatColumns('Target:', "{$host}:{$port}", $cols);
        $payload .= $this->formatColumns('Paper Width:', "{$paperWidth}mm ({$cols} cols)", $cols);
        $payload .= $this->formatColumns('Printed At:', now()->format('Y-m-d H:i:s'), $cols);
        $payload .= $divider;
        $payload .= self::ALIGN_CENTER."Connection verified successfully.\n\n\n\n";
        $payload .= self::ALIGN_LEFT.self::PARTIAL_CUT;

        return $payload;
    }

    public function buildPlainTextTest(int $paperWidth = 80, string $host = '127.0.0.1', int $port = 9100): string
    {
        $cols = $this->columnsForWidth($paperWidth);
        $divider = str_repeat('-', $cols)."\n";
        $storeName = $this->sanitizeText((string) config('pos.store_name', 'IMS-POS'));

        $text = $this->centerText($storeName, $cols)."\n";
        $text .= $this->centerText('THERMAL PRINTER TEST', $cols)."\n";
        $text .= $divider;
        $text .= $this->formatColumns('Target:', "{$host}:{$port}", $cols);
        $text .= $this->formatColumns('Paper Width:', "{$paperWidth}mm ({$cols} cols)", $cols);
        $text .= $this->formatColumns('Printed At:', now()->format('Y-m-d H:i:s'), $cols);
        $text .= $divider;
        $text .= $this->centerText('Connection verified successfully.', $cols)."\n\n";

        return $text;
    }

    public function printOrder(Order $order, array $options = []): array
    {
        $resolved = $this->resolveOptions($options);
        if (! $this->isAllowedPrinterHost($resolved['host'])) {
            return [
                'status' => false,
                'message' => 'Printer host is not permitted. Use localhost or a local LAN IP address.',
            ];
        }

        $shouldKickDrawer = $resolved['open_cash_drawer'] && $order->payment_method === 'cash';

        $payload = ($resolved['driver'] === 'http' && $resolved['http_format'] === 'text')
            ? $this->buildPlainTextReceipt($order, $resolved['paper_width'])
            : $this->buildEscPosPayload($order, $resolved['paper_width'], $shouldKickDrawer);

        return $this->dispatch($payload, $resolved, "Receipt {$order->order_no} sent to printer");
    }

    public function printTest(array $options = []): array
    {
        $resolved = $this->resolveOptions($options);
        if (! $this->isAllowedPrinterHost($resolved['host'])) {
            return [
                'status' => false,
                'message' => 'Printer host is not permitted. Use localhost or a local LAN IP address.',
            ];
        }

        $payload = ($resolved['driver'] === 'http' && $resolved['http_format'] === 'text')
            ? $this->buildPlainTextTest($resolved['paper_width'], $resolved['host'], $resolved['port'])
            : $this->buildTestPayload($resolved['paper_width'], $resolved['host'], $resolved['port']);

        return $this->dispatch($payload, $resolved, 'Test receipt sent to printer');
    }

    private function resolveOptions(array $options): array
    {
        $defaults = config('pos.printer', []);
        $paperWidth = (int) ($options['paper_width'] ?? $defaults['paper_width'] ?? 80);

        return [
            'driver' => ($options['driver'] ?? $defaults['driver'] ?? 'tcp') === 'http' ? 'http' : 'tcp',
            'host' => trim((string) ($options['host'] ?? $defaults['host'] ?? '127.0.0.1')),
            'port' => max(1, min(65535, (int) ($options['port'] ?? $defaults['port'] ?? 9100))),
            'http_path' => '/'.ltrim((string) ($options['http_path'] ?? $defaults['http_path'] ?? '/'), '/'),
            'http_format' => ($options['http_format'] ?? $defaults['http_format'] ?? 'escpos') === 'text' ? 'text' : 'escpos',
            'paper_width' => $paperWidth === 58 ? 58 : 80,
            'open_cash_drawer' => array_key_exists('open_cash_drawer', $options)
                ? (bool) $options['open_cash_drawer']
                : (bool) ($defaults['open_cash_drawer'] ?? false),
            'timeout' => max(1, min(15, (int) ($options['timeout'] ?? $defaults['timeout'] ?? 3))),
        ];
    }

    private function dispatch(string $payload, array $resolved, string $successPrefix): array
    {
        if ($resolved['driver'] === 'http') {
            return $this->sendHttp($payload, $resolved, $successPrefix);
        }

        return $this->sendTcp($payload, $resolved, $successPrefix);
    }

    private function sendTcp(string $payload, array $resolved, string $successPrefix): array
    {
        $host = str_contains($resolved['host'], ':') ? "[{$resolved['host']}]" : $resolved['host'];
        $target = "tcp://{$host}:{$resolved['port']}";

        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client($target, $errno, $errstr, $resolved['timeout']);

        if ($socket === false) {
            $reason = $errstr !== '' ? $errstr : 'Connection failed';

            return [
                'status' => false,
                'message' => "Unable to connect to thermal printer at {$resolved['host']}:{$resolved['port']} ({$reason}).",
            ];
        }

        try {
            stream_set_timeout($socket, $resolved['timeout']);
            $totalBytes = strlen($payload);
            $written = 0;

            while ($written < $totalBytes) {
                $chunk = @fwrite($socket, substr($payload, $written));
                if ($chunk === false || $chunk === 0) {
                    return [
                        'status' => false,
                        'message' => "Failed writing receipt data to printer at {$resolved['host']}:{$resolved['port']}.",
                    ];
                }
                $written += $chunk;
            }

            return [
                'status' => true,
                'message' => "{$successPrefix} ({$resolved['host']}:{$resolved['port']}, {$written} bytes).",
                'bytes' => $written,
            ];
        } finally {
            @fclose($socket);
        }
    }

    private function sendHttp(string $payload, array $resolved, string $successPrefix): array
    {
        $host = str_contains($resolved['host'], ':') ? "[{$resolved['host']}]" : $resolved['host'];
        $url = "http://{$host}:{$resolved['port']}{$resolved['http_path']}";
        $contentType = $resolved['http_format'] === 'text' ? 'text/plain; charset=utf-8' : 'application/octet-stream';

        try {
            $response = Http::timeout($resolved['timeout'])
                ->withBody($payload, $contentType)
                ->post($url);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => "{$successPrefix} via HTTP ({$url}, HTTP {$response->status()}).",
                    'bytes' => strlen($payload),
                ];
            }

            return [
                'status' => false,
                'message' => "Printer endpoint {$url} returned HTTP {$response->status()}.",
            ];
        } catch (Throwable $e) {
            return [
                'status' => false,
                'message' => "Unable to reach HTTP printer at {$url}: {$e->getMessage()}",
            ];
        }
    }

    private function formatColumns(string $left, string $right, int $cols): string
    {
        $rightLen = strlen($right);
        $maxLeft = max(1, $cols - $rightLen - 1);
        if (strlen($left) > $maxLeft) {
            $left = substr($left, 0, $maxLeft);
        }
        $spaces = max(1, $cols - strlen($left) - $rightLen);

        return $left.str_repeat(' ', $spaces).$right."\n";
    }

    private function centerText(string $text, int $cols): string
    {
        $trimmed = substr($text, 0, $cols);
        $pad = (int) floor(max(0, ($cols - strlen($trimmed)) / 2));

        return str_repeat(' ', $pad).$trimmed;
    }

    /**
     * @return array<int, string>
     */
    private function wrapText(string $text, int $cols): array
    {
        if ($text === '') {
            return [''];
        }

        return explode("\n", wordwrap($text, $cols, "\n", true));
    }

    private function sanitizeText(?string $text): string
    {
        $clean = preg_replace('/[\x00-\x1F\x7F]+/', ' ', (string) $text);

        return trim((string) $clean);
    }
}
