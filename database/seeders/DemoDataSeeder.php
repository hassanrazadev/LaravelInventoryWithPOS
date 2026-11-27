<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds for local/testing environments.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@test.com')->firstOrFail();

        $categoriesData = [
            [
                'name' => 'Electronics',
                'code' => 'CAT-ELEC',
                'slug' => 'electronics',
                'parent' => null,
                'color' => [52, 152, 219],
                'trashed' => false,
            ],
            [
                'name' => 'Computers & Laptops',
                'code' => 'CAT-COMP',
                'slug' => 'computers-laptops',
                'parent' => 'electronics',
                'color' => [41, 128, 185],
                'trashed' => false,
            ],
            [
                'name' => 'Accessories',
                'code' => 'CAT-ACCS',
                'slug' => 'accessories',
                'parent' => 'electronics',
                'color' => [26, 188, 156],
                'trashed' => false,
            ],
            [
                'name' => 'Office Supplies',
                'code' => 'CAT-OFFC',
                'slug' => 'office-supplies',
                'parent' => null,
                'color' => [243, 156, 18],
                'trashed' => false,
            ],
            [
                'name' => 'Networking',
                'code' => 'CAT-NETW',
                'slug' => 'networking',
                'parent' => null,
                'color' => [155, 89, 182],
                'trashed' => false,
            ],
            [
                'name' => 'Discontinued Items',
                'code' => 'CAT-DISC',
                'slug' => 'discontinued-items',
                'parent' => null,
                'color' => [149, 165, 166],
                'trashed' => true,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $item) {
            $imagePath = 'category/demo-' . $item['slug'] . '.png';
            $this->ensurePlaceholderImage(
                $imagePath,
                $item['color'][0],
                $item['color'][1],
                $item['color'][2],
                $item['code']
            );

            $category = Category::withTrashed()->firstOrCreate(
                ['category_slug' => $item['slug']],
                [
                    'created_by' => $admin->id,
                    'parent_id' => $item['parent'] ? $categories[$item['parent']]->id : null,
                    'category_name' => $item['name'],
                    'category_code' => $item['code'],
                    'category_image' => $imagePath,
                ]
            );

            if ($item['trashed'] && ! $category->trashed()) {
                $category->delete();
            }

            $categories[$item['slug']] = $category;
        }

        $productsData = [
            [
                'name' => 'Dell Latitude 5420 Laptop',
                'code' => 'PRD-DL5420',
                'slug' => 'dell-latitude-5420-laptop',
                'sale_price' => 899.00,
                'category' => 'computers-laptops',
                'color' => [44, 62, 80],
            ],
            [
                'name' => 'HP ProBook 450 G9',
                'code' => 'PRD-HP450G9',
                'slug' => 'hp-probook-450-g9',
                'sale_price' => 799.00,
                'category' => 'computers-laptops',
                'color' => [52, 73, 94],
            ],
            [
                'name' => 'Logitech MX Master 3S Mouse',
                'code' => 'PRD-LGMX3S',
                'slug' => 'logitech-mx-master-3s-mouse',
                'sale_price' => 99.99,
                'category' => 'accessories',
                'color' => [22, 160, 133],
            ],
            [
                'name' => 'Keychron K2 Mechanical Keyboard',
                'code' => 'PRD-KCK2MK',
                'slug' => 'keychron-k2-mechanical-keyboard',
                'sale_price' => 89.00,
                'category' => 'accessories',
                'color' => [39, 174, 96],
            ],
            [
                'name' => 'SanDisk Ultra 128GB USB Flash',
                'code' => 'PRD-SD128U',
                'slug' => 'sandisk-ultra-128gb-usb-flash',
                'sale_price' => 19.50,
                'category' => 'accessories',
                'color' => [192, 57, 43],
            ],
            [
                'name' => 'TP-Link Archer AX55 Router',
                'code' => 'PRD-TPLAX55',
                'slug' => 'tp-link-archer-ax55-router',
                'sale_price' => 129.00,
                'category' => 'networking',
                'color' => [142, 68, 173],
            ],
            [
                'name' => 'A4 Copy Paper Ream 500 Sheets',
                'code' => 'PRD-A4P500',
                'slug' => 'a4-copy-paper-ream-500-sheets',
                'sale_price' => 7.50,
                'category' => 'office-supplies',
                'color' => [211, 84, 0],
            ],
            [
                'name' => 'Ergonomic Mesh Office Chair',
                'code' => 'PRD-ERGCH1',
                'slug' => 'ergonomic-mesh-office-chair',
                'sale_price' => 249.00,
                'category' => 'office-supplies',
                'color' => [230, 126, 34],
            ],
        ];

        $products = [];
        foreach ($productsData as $item) {
            $imagePath = 'product/demo-' . $item['slug'] . '.png';
            $this->ensurePlaceholderImage(
                $imagePath,
                $item['color'][0],
                $item['color'][1],
                $item['color'][2],
                $item['code']
            );

            $products[$item['slug']] = Product::withTrashed()->firstOrCreate(
                ['product_slug' => $item['slug']],
                [
                    'category_id' => $categories[$item['category']]->id,
                    'created_by' => $admin->id,
                    'product_name' => $item['name'],
                    'product_code' => $item['code'],
                    'quantity' => 0,
                    'sale_price' => $item['sale_price'],
                    'product_image' => $imagePath,
                ]
            );
        }

        $suppliersData = [
            [
                'name' => 'TechSource Distributors',
                'email' => 'sales@techsource.example.com',
                'phone' => '+1-555-0101',
                'address' => '100 Hardware Way, Austin, TX',
            ],
            [
                'name' => 'Global Peripherals Co.',
                'email' => 'orders@globalperipherals.example.com',
                'phone' => '+1-555-0102',
                'address' => '250 Silicon Ave, San Jose, CA',
            ],
            [
                'name' => 'Metro Office Solutions',
                'email' => 'supply@metrooffice.example.com',
                'phone' => '+1-555-0103',
                'address' => '75 Commerce Blvd, Chicago, IL',
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $item) {
            $suppliers[$item['email']] = Supplier::withTrashed()->firstOrCreate(
                ['email' => $item['email']],
                [
                    'created_by' => $admin->id,
                    'name' => $item['name'],
                    'phone' => $item['phone'],
                    'address' => $item['address'],
                ]
            );
        }

        $customersData = [
            [
                'name' => 'Walk-in Customer',
                'email' => 'walkin@example.com',
                'phone' => '0000000000',
                'address' => 'In-Store Counter',
            ],
            [
                'name' => 'Ayesha Khan',
                'email' => 'ayesha@example.com',
                'phone' => '+92-300-1112233',
                'address' => 'Lahore, Pakistan',
            ],
            [
                'name' => 'Usman Ali',
                'email' => 'usman@example.com',
                'phone' => '+92-321-4445566',
                'address' => 'Karachi, Pakistan',
            ],
        ];

        foreach ($customersData as $item) {
            Customer::firstOrCreate(
                ['email' => $item['email']],
                [
                    'created_by' => $admin->id,
                    'name' => $item['name'],
                    'phone' => $item['phone'],
                    'address' => $item['address'],
                ]
            );
        }

        if (Purchase::withTrashed()->count() === 0) {
            $purchasesData = [
                [
                    'supplier' => 'sales@techsource.example.com',
                    'items' => [
                        ['slug' => 'dell-latitude-5420-laptop', 'quantity' => 5, 'unit_price' => 720.00],
                        ['slug' => 'hp-probook-450-g9', 'quantity' => 4, 'unit_price' => 640.00],
                    ],
                ],
                [
                    'supplier' => 'orders@globalperipherals.example.com',
                    'items' => [
                        ['slug' => 'logitech-mx-master-3s-mouse', 'quantity' => 20, 'unit_price' => 72.00],
                        ['slug' => 'keychron-k2-mechanical-keyboard', 'quantity' => 15, 'unit_price' => 62.00],
                        ['slug' => 'sandisk-ultra-128gb-usb-flash', 'quantity' => 50, 'unit_price' => 11.00],
                        ['slug' => 'tp-link-archer-ax55-router', 'quantity' => 10, 'unit_price' => 95.00],
                    ],
                ],
                [
                    'supplier' => 'supply@metrooffice.example.com',
                    'items' => [
                        ['slug' => 'a4-copy-paper-ream-500-sheets', 'quantity' => 100, 'unit_price' => 4.50],
                        ['slug' => 'ergonomic-mesh-office-chair', 'quantity' => 8, 'unit_price' => 175.00],
                    ],
                ],
            ];

            DB::transaction(function () use ($admin, $suppliers, $products, $purchasesData): void {
                foreach ($purchasesData as $purchaseSpec) {
                    $total = 0;
                    foreach ($purchaseSpec['items'] as $line) {
                        $total += $line['quantity'] * $line['unit_price'];
                    }

                    $purchase = Purchase::create([
                        'supplier_id' => $suppliers[$purchaseSpec['supplier']]->id,
                        'created_by' => $admin->id,
                        'total' => $total,
                    ]);

                    foreach ($purchaseSpec['items'] as $line) {
                        $product = $products[$line['slug']];
                        $subTotal = $line['quantity'] * $line['unit_price'];

                        $purchase->products()->attach($product->id, [
                            'quantity' => $line['quantity'],
                            'unit_price' => $line['unit_price'],
                            'sub_total' => $subTotal,
                        ]);

                        $product->increment('quantity', $line['quantity']);
                    }
                }
            });
        }

        $expensesData = [
            ['expense_name' => 'Store Rent', 'expense_amount' => 1200.00],
            ['expense_name' => 'Electricity Bill', 'expense_amount' => 185.50],
            ['expense_name' => 'Internet Service', 'expense_amount' => 65.00],
        ];

        foreach ($expensesData as $item) {
            Expense::firstOrCreate(
                ['expense_name' => $item['expense_name']],
                [
                    'created_by' => $admin->id,
                    'expense_amount' => $item['expense_amount'],
                ]
            );
        }
    }

    /**
     * Generate a clean 300x300 PNG placeholder image on the public disk if missing.
     */
    private function ensurePlaceholderImage(string $path, int $r, int $g, int $b, string $label): void
    {
        if (Storage::disk('public')->exists($path)) {
            return;
        }

        $img = imagecreatetruecolor(300, 300);
        $bg = imagecolorallocate($img, $r, $g, $b);
        $border = imagecolorallocate($img, 255, 255, 255);
        $textColor = imagecolorallocate($img, 255, 255, 255);

        imagefilledrectangle($img, 0, 0, 299, 299, $bg);
        imagerectangle($img, 12, 12, 287, 287, $border);

        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($label);
        $textHeight = imagefontheight($font);
        $x = (int) ((300 - $textWidth) / 2);
        $y = (int) ((300 - $textHeight) / 2);
        imagestring($img, $font, $x, $y, $label, $textColor);

        ob_start();
        imagepng($img);
        $pngBytes = ob_get_clean();

        Storage::disk('public')->put($path, $pngBytes);
    }
}
