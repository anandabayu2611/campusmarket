<?php

namespace App\Console\Commands;

use App\Repositories\ProductRepository;
use Illuminate\Console\Command;

class SeedProducts extends Command
{
    protected $signature = 'campus:seed-products {--fresh : Hapus dulu produk contoh lama (id berawalan "seed-")}';

    protected $description = 'Isi Firestore (collection "products") dengan produk contoh untuk development';

    public function handle(ProductRepository $products): int
    {
        if ($this->option('fresh')) {
            foreach ($products->all() as $product) {
                if (str_starts_with($product['id'], 'seed-')) {
                    $products->delete($product['id']);
                }
            }
            $this->line('Produk contoh lama dihapus.');
        }

        // Data dibaca dari database/data/products.json (id deterministik "seed-<slug>",
        // jadi menjalankan command berulang memperbarui dokumen yang sama).
        // seller_id "seed" tidak cocok dengan user mana pun -> tidak bisa diubah lewat aplikasi.
        $items = json_decode(file_get_contents(database_path('data/products.json')), true, 512, JSON_THROW_ON_ERROR);

        foreach ($items as $item) {
            $products->create($item);
            $this->line("produk: {$item['name']}");
        }

        $this->info(count($items) . ' produk contoh tersimpan di Firestore.');

        return self::SUCCESS;
    }
}
