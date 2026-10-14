<?php

namespace App\Http\Controllers;

use App\Repositories\ProductRepository;
use App\Services\ProductImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    private array $categories = [
        'Buku',
        'Elektronik',
        'Fashion',
        'Perlengkapan Kuliah',
        'Kos dan Rumah Tangga',
        'Lainnya',
    ];

    public function __construct(
        private readonly ProductRepository $products,
        private readonly ProductImageStorage $images,
    ) {
    }

    public function index(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'condition' => $request->query('condition', ''),
            'location' => trim((string) $request->query('location', '')),
            'transaction_type' => $request->query('transaction_type', ''),
            'category' => $request->query('category', ''),
        ];

        $products = collect($this->products->all())
            ->filter(function ($product) use ($filters) {
                if (($product['status'] ?? 'active') !== 'active') {
                    return false;
                }

                $searchText = implode(' ', [
                    $product['name'] ?? '',
                    $product['description'] ?? '',
                    $product['category'] ?? '',
                ]);

                if (
                    $filters['q'] !== '' &&
                    ! Str::contains(
                        Str::lower($searchText),
                        Str::lower($filters['q'])
                    )
                ) {
                    return false;
                }

                if (
                    $filters['condition'] !== '' &&
                    ($product['condition'] ?? '') !== $filters['condition']
                ) {
                    return false;
                }

                if (
                    $filters['location'] !== '' &&
                    ! Str::contains(
                        Str::lower($product['location'] ?? ''),
                        Str::lower($filters['location'])
                    )
                ) {
                    return false;
                }

                if (
                    $filters['transaction_type'] !== '' &&
                    ($product['transaction_type'] ?? '') !== $filters['transaction_type']
                ) {
                    return false;
                }

                if (
                    $filters['category'] !== '' &&
                    ($product['category'] ?? '') !== $filters['category']
                ) {
                    return false;
                }

                return true;
            })
            ->sortByDesc('created_at')
            ->values();

        return view('products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => $this->categories,
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'categories' => $this->categories,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'condition' => [
                'required',
                Rule::in(['Baru', 'Bekas']),
            ],
            'location' => ['required', 'string', 'max:150'],
            'transaction_type' => [
                'required',
                Rule::in(['Jual', 'Sewa', 'Barter']),
            ],
            'category' => [
                'required',
                Rule::in($this->categories),
            ],
            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ]);

        $user = $request->user();
        $productId = (string) Str::uuid();

        try {
            $photo = $this->images->store(
                $productId,
                $request->file('photo')
            );
        } catch (\Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'photo' => 'Foto gagal diunggah. Periksa konfigurasi Firebase Storage.',
            ]);
        }

        try {
            $this->products->create([
                'id' => $productId,
                'seller_id' => (string) $user->getAuthIdentifier(),
                'seller_name' => (string) $user->name,
                'name' => trim($data['name']),
                'description' => trim($data['description']),
                'price' => (float) $data['price'],
                'condition' => $data['condition'],
                'location' => trim($data['location']),
                'transaction_type' => $data['transaction_type'],
                'category' => $data['category'],
                'photo_url' => $photo['url'],
                'photo_path' => $photo['path'],
                'status' => 'active',
            ]);
        } catch (\Throwable $e) {
            $this->images->delete($photo['path']);
            report($e);

            throw ValidationException::withMessages([
                'name' => 'Produk gagal disimpan. Periksa koneksi Firestore.',
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('status', 'Produk berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $product = $this->products->find($id);

        abort_unless(
            $product &&
            ($product['status'] ?? 'active') === 'active',
            404
        );

        return view('products.show', [
            'product' => $product,
        ]);
    }
}