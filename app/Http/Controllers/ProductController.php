<?php

namespace App\Http\Controllers;

use App\Repositories\ProductRepository;
use App\Repositories\UniversityRepository;
use App\Repositories\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\ProductImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly UserRepository $users,
        private readonly UniversityRepository $universities,
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
            'university' => $request->query('university', ''),
            'semester' => $request->query('semester', ''),
            'min_price' => $request->query('min_price', ''),
            'max_price' => $request->query('max_price', ''),
            'mine' => $request->boolean('mine'),
        ];
        $currentUserId = (string) $request->user()->getAuthIdentifier();

        $products = collect($this->products->all())
            ->filter(function ($product) use ($filters, $currentUserId) {
                if (($product['status'] ?? 'active') !== 'active') {
                    return false;
                }

                if (
                    $filters['mine'] &&
                    ($product['seller_id'] ?? '') !== $currentUserId
                ) {
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

                if (
                    $filters['university'] !== '' &&
                    ($product['university_id'] ?? '') !== $filters['university']
                ) {
                    return false;
                }

                if (
                    $filters['semester'] !== '' &&
                    (string) ($product['semester'] ?? '') !== (string) $filters['semester']
                ) {
                    return false;
                }

                $price = (float) ($product['price'] ?? 0);

                if (is_numeric($filters['min_price']) && $price < (float) $filters['min_price']) {
                    return false;
                }

                if (is_numeric($filters['max_price']) && $price > (float) $filters['max_price']) {
                    return false;
                }

                return true;
            })
            ->sortByDesc('created_at')
            ->values();

        $perPage = (int) config('campusmarket.products_per_page', 12);
        $page = max(1, (int) $request->query('page', 1));
        $paginator = new LengthAwarePaginator(
            $products->forPage($page, $perPage)->values(),
            $products->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('products.index', [
            'products' => $paginator,
            'filters' => $filters,
            'categories' => $this->categories(),
            'specialCategories' => config('campusmarket.categories.special'),
            'universities' => $this->universities->all(),
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $user = $request->user();
        $university = ! empty($user->university_id)
            ? $this->universities->find((string) $user->university_id)
            : null;
        $productId = (string) Str::uuid();

        // Foto bersifat opsional: tanpa foto, photo_url & photo_path disimpan null.
        $photo = null;

        if ($request->hasFile('photo')) {
            try {
                $photo = $this->images->store(
                    $productId,
                    $request->file('photo')
                );
            } catch (\Throwable $e) {
                report($e);

                throw ValidationException::withMessages([
                    'photo' => 'Foto gagal disimpan di server. Pastikan folder storage dapat ditulis.',
                ]);
            }
        }

        try {
            $this->products->create([
                'id' => $productId,
                'seller_id' => (string) $user->getAuthIdentifier(),
                'seller_name' => (string) $user->name,
                'university_id' => $university['id'] ?? null,
                'university_name' => $university['name'] ?? null,
                'semester' => $this->semesterFor($data),
                'name' => trim($data['name']),
                'description' => trim($data['description']),
                'price' => (float) $data['price'],
                'condition' => $data['condition'],
                'location' => trim($data['location']),
                'transaction_type' => $data['transaction_type'],
                'category' => $data['category'],
                'photo_url' => $photo['url'] ?? null,
                'photo_path' => $photo['path'] ?? null,
                'status' => 'active',
            ]);
        } catch (\Throwable $e) {
            $this->images->delete($photo['path'] ?? null);
            report($e);

            throw ValidationException::withMessages([
                'name' => 'Produk gagal disimpan. Periksa koneksi Firestore.',
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('status', 'Produk berhasil ditambahkan.');
    }

    public function edit(Request $request, string $id)
    {
        return view('products.edit', [
            'product' => $this->ownedProduct($request, $id),
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $product = $this->ownedProduct($request, $id);
        $data = $request->validate($this->rules());

        $changes = [
            'name' => trim($data['name']),
            'description' => trim($data['description']),
            'price' => (float) $data['price'],
            'condition' => $data['condition'],
            'location' => trim($data['location']),
            'transaction_type' => $data['transaction_type'],
            'category' => $data['category'],
            'semester' => $this->semesterFor($data),
        ];

        $newPhoto = null;
        $removePhoto = false;

        if ($request->hasFile('photo')) {
            try {
                $newPhoto = $this->images->store($id, $request->file('photo'));
            } catch (\Throwable $e) {
                report($e);

                throw ValidationException::withMessages([
                    'photo' => 'Foto gagal disimpan di server. Pastikan folder storage dapat ditulis.',
                ]);
            }

            $changes['photo_url'] = $newPhoto['url'];
            $changes['photo_path'] = $newPhoto['path'];
        } elseif ($request->boolean('remove_photo')) {
            $removePhoto = true;
            $changes['photo_url'] = null;
            $changes['photo_path'] = null;
        }

        try {
            $this->products->update($id, $changes);
        } catch (\Throwable $e) {
            if ($newPhoto) {
                $this->images->delete($newPhoto['path']);
            }
            report($e);

            throw ValidationException::withMessages([
                'name' => 'Produk gagal diperbarui. Periksa koneksi Firestore.',
            ]);
        }

        // Foto lama dihapus hanya setelah Firestore berhasil diperbarui.
        if ($newPhoto || $removePhoto) {
            $this->images->delete($product['photo_path'] ?? null);
        }

        return redirect()
            ->route('products.show', $id)
            ->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, string $id)
    {
        $product = $this->ownedProduct($request, $id);

        $this->products->delete($id);
        $this->images->delete($product['photo_path'] ?? null);

        return redirect()
            ->route('products.index', ['mine' => 1])
            ->with('status', 'Produk berhasil dihapus.');
    }

    public function show(string $id)
    {
        $product = $this->products->find($id);

        abort_unless(
            $product &&
            ($product['status'] ?? 'active') === 'active',
            404
        );

        // SRS-007: kontak & asal kampus penjual diambil dari profil terbaru penjual.
        $seller = ! empty($product['seller_id']) ? $this->users->find($product['seller_id']) : null;

        return view('products.show', [
            'product' => $product,
            'seller' => $seller,
        ]);
    }

    /** @return string[] semua kategori (khusus SRS-009 lebih dulu). */
    private function categories(): array
    {
        return array_merge(
            config('campusmarket.categories.special'),
            config('campusmarket.categories.general')
        );
    }

    /** Semester hanya disimpan untuk kategori "Buku per Semester". */
    private function semesterFor(array $data): ?int
    {
        return ($data['category'] ?? '') === config('campusmarket.semester_category')
            ? (int) $data['semester']
            : null;
    }

    /** Aturan validasi bersama untuk tambah & ubah produk (foto opsional). */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', Rule::in(['Baru', 'Bekas'])],
            'location' => ['required', 'string', 'max:150'],
            'transaction_type' => ['required', Rule::in(['Jual', 'Sewa', 'Barter'])],
            'category' => ['required', Rule::in($this->categories())],
            'semester' => [
                'nullable',
                'required_if:category,' . config('campusmarket.semester_category'),
                'integer',
                'between:1,8',
            ],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:' . config('campusmarket.max_photo_kb', 10240),
            ],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }

    /** Ambil produk aktif milik user yang sedang login; 404 bila tidak ada, 403 bila bukan miliknya. */
    private function ownedProduct(Request $request, string $id): array
    {
        $product = $this->products->find($id);

        abort_unless(
            $product && ($product['status'] ?? 'active') === 'active',
            404
        );

        abort_unless(
            ($product['seller_id'] ?? null) === (string) $request->user()->getAuthIdentifier(),
            403
        );

        return $product;
    }
}
