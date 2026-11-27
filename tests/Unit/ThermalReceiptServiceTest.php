<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use App\Services\ThermalReceiptService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ThermalReceiptServiceTest extends TestCase
{
    public function test_columns_for_paper_width(): void
    {
        $service = new ThermalReceiptService;

        $this->assertSame(32, $service->columnsForWidth(58));
        $this->assertSame(48, $service->columnsForWidth(80));
        $this->assertSame(48, $service->columnsForWidth(72));
    }

    public function test_allowed_printer_hosts_enforce_ssrf_allowlist(): void
    {
        $service = new ThermalReceiptService;

        foreach (['127.0.0.1', 'localhost', '::1', '192.168.1.50', '10.0.0.12', '172.16.5.20'] as $allowedHost) {
            $this->assertTrue($service->isAllowedPrinterHost($allowedHost), "Expected {$allowedHost} to be allowed");
        }

        foreach (['', '8.8.8.8', '169.254.169.254', '172.32.0.1', 'printer.example.com', 'http://127.0.0.1'] as $blockedHost) {
            $this->assertFalse($service->isAllowedPrinterHost($blockedHost), "Expected {$blockedHost} to be blocked");
        }
    }

    public function test_build_esc_pos_payload_and_plain_text_receipt(): void
    {
        $service = new ThermalReceiptService;
        $order = $this->makeSampleOrder();

        $escPos80 = $service->buildEscPosPayload($order, 80);
        $this->assertStringStartsWith("\x1B\x40", $escPos80);
        $this->assertStringEndsWith("\x1D\x56\x42\x00", $escPos80);
        $this->assertStringNotContainsString("\x1B\x70\x00\x19\xFA", $escPos80);
        $this->assertStringContainsString(str_repeat('-', 48)."\n", $escPos80);
        $this->assertStringContainsString('ORD-2026-0001', $escPos80);
        $this->assertStringContainsString('Wireless Scanner', $escPos80);
        $this->assertStringContainsString('$145.00', $escPos80);
        $this->assertStringContainsString('Paid (Cash)', $escPos80);
        $this->assertStringContainsString('Note: Counter 1 urgent', $escPos80);

        $escPosWithDrawer = $service->buildEscPosPayload($order, 80, true);
        $this->assertStringEndsWith("\x1D\x56\x42\x00\x1B\x70\x00\x19\xFA", $escPosWithDrawer);

        $escPos58 = $service->buildEscPosPayload($order, 58);
        $this->assertStringContainsString(str_repeat('-', 32)."\n", $escPos58);
        $this->assertStringNotContainsString(str_repeat('-', 48), $escPos58);

        $plainText = $service->buildPlainTextReceipt($order, 80);
        $this->assertStringNotContainsString("\x1B", $plainText);
        $this->assertStringNotContainsString("\x1D", $plainText);
        $this->assertStringContainsString('ORD-2026-0001', $plainText);
        $this->assertStringContainsString('Wireless Scanner', $plainText);
        $this->assertStringContainsString('$145.00', $plainText);
    }

    public function test_build_test_payload_formats_target_and_paper_width(): void
    {
        $service = new ThermalReceiptService;

        $payload80 = $service->buildTestPayload(80, '127.0.0.1', 9100);
        $this->assertStringStartsWith("\x1B\x40", $payload80);
        $this->assertStringEndsWith("\x1D\x56\x42\x00", $payload80);
        $this->assertStringContainsString('THERMAL PRINTER TEST', $payload80);
        $this->assertStringContainsString('127.0.0.1:9100', $payload80);
        $this->assertStringContainsString('80mm (48 cols)', $payload80);

        $payload58 = $service->buildTestPayload(58, '127.0.0.1', 9100);
        $this->assertStringContainsString('58mm (32 cols)', $payload58);
        $this->assertStringContainsString(str_repeat('-', 32)."\n", $payload58);
    }

    public function test_print_order_and_print_test_via_http_driver_using_fake(): void
    {
        Http::fake([
            'http://127.0.0.1:9100/fail' => Http::response('Printer Busy', 500),
            'http://127.0.0.1:9100/*' => Http::response('OK', 200),
            'http://127.0.0.1:9100' => Http::response('OK', 200),
        ]);

        $service = new ThermalReceiptService;
        $order = $this->makeSampleOrder();

        $escPosResult = $service->printOrder($order, [
            'driver' => 'http',
            'host' => '127.0.0.1',
            'port' => 9100,
            'http_path' => '/',
            'http_format' => 'escpos',
            'paper_width' => 80,
            'open_cash_drawer' => true,
        ]);

        $this->assertTrue($escPosResult['status']);
        $this->assertGreaterThan(0, $escPosResult['bytes']);

        $textResult = $service->printOrder($order, [
            'driver' => 'http',
            'host' => '127.0.0.1',
            'port' => 9100,
            'http_path' => '/print',
            'http_format' => 'text',
            'paper_width' => 58,
        ]);

        $this->assertTrue($textResult['status']);
        $this->assertGreaterThan(0, $textResult['bytes']);

        $testResult = $service->printTest([
            'driver' => 'http',
            'host' => '127.0.0.1',
            'port' => 9100,
            'http_path' => '/',
            'http_format' => 'escpos',
            'paper_width' => 80,
        ]);

        $this->assertTrue($testResult['status']);

        $failResult = $service->printTest([
            'driver' => 'http',
            'host' => '127.0.0.1',
            'port' => 9100,
            'http_path' => '/fail',
            'http_format' => 'escpos',
        ]);

        $this->assertFalse($failResult['status']);
        $this->assertStringContainsString('HTTP 500', $failResult['message']);

        Http::assertSent(fn (HttpClientRequest $req) => $req->url() === 'http://127.0.0.1:9100/'
            && str_contains($req->header('Content-Type')[0] ?? '', 'application/octet-stream')
            && (! str_contains($req->body(), 'ORD-2026-0001') || str_contains($req->body(), "\x1B\x70\x00\x19\xFA")));

        Http::assertSent(fn (HttpClientRequest $req) => $req->url() === 'http://127.0.0.1:9100/print'
            && str_contains($req->header('Content-Type')[0] ?? '', 'text/plain')
            && str_contains($req->body(), 'ORD-2026-0001'));
    }

    public function test_print_rejects_disallowed_host_without_network_call(): void
    {
        Http::fake();

        $service = new ThermalReceiptService;
        $order = $this->makeSampleOrder();

        $orderRes = $service->printOrder($order, [
            'driver' => 'http',
            'host' => '8.8.8.8',
            'port' => 9100,
        ]);
        $testRes = $service->printTest([
            'driver' => 'http',
            'host' => '169.254.169.254',
            'port' => 9100,
        ]);

        $this->assertFalse($orderRes['status']);
        $this->assertStringContainsString('not permitted', $orderRes['message']);
        $this->assertFalse($testRes['status']);
        $this->assertStringContainsString('not permitted', $testRes['message']);

        Http::assertNothingSent();
    }

    private function makeSampleOrder(): Order
    {
        $customer = new Customer(['name' => 'Walk-in Guest']);
        $cashier = new User(['name' => 'Hassan Cashier']);
        $product = new Product(['product_name' => 'Wireless Scanner']);

        $detail = new OrderDetail([
            'quantity' => 2,
            'unit_price' => 75.00,
            'sub_total' => 150.00,
        ]);
        $detail->setRelation('product', $product);

        $order = new Order([
            'order_no' => 'ORD-2026-0001',
            'total_items' => 2,
            'sub_total' => 150.00,
            'discount' => 10.00,
            'tax' => 5.00,
            'total' => 145.00,
            'payment_method' => 'cash',
            'paid_amount' => 150.00,
            'change_amount' => 5.00,
            'status' => Order::STATUS_COMPLETED,
            'notes' => "Counter 1\x1Burgent",
        ]);
        $order->created_at = now();
        $order->setRelation('customer', $customer);
        $order->setRelation('createdBy', $cashier);
        $order->setRelation('orderDetails', new Collection([$detail]));

        return $order;
    }
}
