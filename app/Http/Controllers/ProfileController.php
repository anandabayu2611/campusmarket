<?php

namespace App\Http\Controllers;

use App\Models\FirebaseUser;
use App\Repositories\ReviewRepository;
use App\Repositories\UniversityRepository;
use App\Repositories\UniversityVerificationRepository;
use App\Repositories\UserRepository;
use App\Services\AvatarStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UniversityRepository $universities,
        private readonly UniversityVerificationRepository $verifications,
        private readonly ReviewRepository $reviews,
        private readonly AvatarStorage $avatars,
    ) {
    }

    /** SRS-004: profil sendiri (nama, universitas, jurusan, rating penjual/pembeli). */
    public function show(Request $request): View
    {
        $u = $request->user()->toArray();

        $note = null;
        if (($u['role'] ?? 'student') === 'student' && ($u['status_verifikasi'] ?? '') !== 'verified') {
            $note = $this->verifications->find($u['id'])['note'] ?? null;
        }

        return view('profile.show', $this->viewData($u) + ['own' => true, 'note' => $note]);
    }

    /** Profil publik mahasiswa lain (tanpa email/telepon). */
    public function showPublic(string $id): View
    {
        $u = $this->users->find($id);

        abort_unless($u && ($u['role'] ?? 'student') === 'student' && ($u['status'] ?? 'active') === 'active', 404);

        return view('profile.public', $this->viewData($u) + ['own' => false, 'note' => null]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', $this->viewData($request->user()->toArray()));
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var FirebaseUser $user */
        $user = $request->user();

        // Nama terkunci setelah diverifikasi universitas (anti-penipuan identitas).
        // Field yang terkunci tidak dikirim browser (disabled), jadi tidak boleh divalidasi sebagai wajib.
        $nameLocked = $user->isStudentVerified() && ! $user->isStaff();

        $data = $request->validate([
            'name'            => $nameLocked ? ['nullable'] : ['required', 'string', 'min:3', 'max:100'],
            'major'           => ['required', 'string', 'max:100'],
            'phone_number'    => ['nullable', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'campus_location' => ['nullable', 'string', 'max:100'],
            'avatar'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'], // NFRS-009: 10 MB
            'remove_avatar'   => ['nullable', 'boolean'],
        ], [
            'phone_number.regex' => 'Nomor HP tidak valid (contoh: 081234567890).',
            'avatar.max'         => 'Ukuran foto maksimal 10 MB.',
        ]);

        $update = [
            'major'           => trim($data['major']),
            'campus_location' => ! empty($data['campus_location']) ? trim($data['campus_location']) : null,
            'phone_number'    => ! empty($data['phone_number'])
                ? preg_replace('/^(\+62|62|0)/', '+62', $data['phone_number'])
                : null,
        ];

        if (! $nameLocked) {
            $update['name'] = trim($data['name']);
        }

        try {
            if ($request->hasFile('avatar')) {
                $stored = $this->avatars->store($user->id, $request->file('avatar'));
                $this->avatars->delete($user->avatar_path ?? null);
                $update['avatar_url']  = $stored['url'];
                $update['avatar_path'] = $stored['path'];
            } elseif ($request->boolean('remove_avatar')) {
                $this->avatars->delete($user->avatar_path ?? null);
                $update['avatar_url']  = null;
                $update['avatar_path'] = null;
            }
        } catch (\Throwable $e) {
            report($e);

            throw ValidationException::withMessages(['avatar' => 'Gagal mengunggah foto. Coba lagi.']);
        }

        $this->users->update($user->id, $update);

        return redirect()->route('profile.show')->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var FirebaseUser $user */
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers(), 'different:current_password'],
        ]);

        if (! Hash::check($data['current_password'], $user->getAuthPassword())) {
            throw ValidationException::withMessages(['current_password' => 'Kata sandi saat ini salah.']);
        }

        $remembered = Auth::viaRemember();

        // remember_token diganti => sesi "Remember Me" di perangkat lain otomatis tidak berlaku.
        $this->users->update($user->id, [
            'password'       => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ]);

        // Muat ulang agar cookie "remember" perangkat ini memakai hash/token baru.
        Auth::login(new FirebaseUser($this->users->find($user->id)), $remembered);
        $request->session()->regenerate();

        return redirect()->route('profile.show')->with('status', 'Kata sandi berhasil diubah.');
    }

    /** Data pendukung tampilan: universitas (relasi belongsTo) dan ringkasan rating dari `reviews`. */
    private function viewData(array $u): array
    {
        return [
            'u'          => $u,
            'university' => ! empty($u['university_id']) ? $this->universities->find($u['university_id']) : null,
            'ratings'    => $this->reviews->summaryFor($u['id']),
        ];
    }
}