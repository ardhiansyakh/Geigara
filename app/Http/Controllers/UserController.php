<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * UserController
 *
 * Manajemen pengguna sistem monitoring:
 * - index       : daftar semua pengguna (hanya admin)
 * - updateRole  : ubah role pengguna (hanya admin)
 * - destroy     : hapus pengguna (hanya admin)
 */
class UserController extends Controller
{
    // ----------------------------------------------------------------
    // Daftar Pengguna
    // ----------------------------------------------------------------

    /**
     * Ambil semua pengguna (Hanya Admin)
     */
    public function index()
    {
        /** @var \App\Models\User|null $authUser */
        $authUser = Auth::user();

        if (!Auth::check() || !$authUser->isAdmin()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak: Hanya Administrator yang dapat melihat daftar pengguna.',
            ], 403);
        }

        $currentUsername = $authUser->user ?? $authUser->username ?? $authUser->name ?? '';

        $users = User::all()->map(function (User $u) use ($currentUsername) {
            $username = $u->user ?? $u->username ?? $u->name ?? 'User';
            $role     = strtolower(trim((string) ($u->role ?? 'user')));

            return [
                'username'   => $username,
                'role'       => $role,
                'is_admin'   => $role === 'admin',
                'is_current' => $username === $currentUsername,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $users,
        ]);
    }

    // ----------------------------------------------------------------
    // Ubah Role
    // ----------------------------------------------------------------

    /**
     * Jadikan admin atau turunkan ke user biasa (Hanya Admin)
     */
    public function updateRole(Request $request, string $username)
    {
        /** @var \App\Models\User|null $authUser */
        $authUser = Auth::user();

        if (!Auth::check() || !$authUser->isAdmin()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak: Hanya Administrator yang dapat mengubah role.',
            ], 403);
        }

        /** @var \App\Models\User|null $targetUser */
        $targetUser = User::where('user', $username)->first();

        if (!$targetUser) {
            return response()->json([
                'status'  => 'error',
                'message' => "Pengguna '{$username}' tidak ditemukan.",
            ], 404);
        }

        $newRole = strtolower(trim((string) $request->input('role', 'user')));

        if (!in_array($newRole, ['admin', 'user'], true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Role tidak valid. Pilih admin atau user.',
            ], 422);
        }

        // Lindungi: jangan biarkan satu-satunya admin menurunkan dirinya sendiri
        $authUsername   = $authUser->user   ?? $authUser->username   ?? '';
        $targetUsername = $targetUser->user ?? $targetUser->username ?? '';

        if ($targetUsername === $authUsername && $newRole === 'user') {
            $totalAdmins = User::whereRaw("LOWER(TRIM(role)) = 'admin'")->count();
            if ($totalAdmins <= 1) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak dapat mengubah role akun sendiri karena Anda adalah satu-satunya Admin tersisa!',
                ], 422);
            }
        }

        $targetUser->role = $newRole;
        $targetUser->save();

        $roleLabel = $newRole === 'admin' ? 'Administrator' : 'User Biasa';

        return response()->json([
            'status'  => 'success',
            'message' => "Pengguna '{$username}' sekarang berhasil dijadikan {$roleLabel}!",
            'data'    => [
                'username' => $targetUser->user ?? $targetUser->username,
                'role'     => $targetUser->role,
                'is_admin' => $targetUser->isAdmin(),
            ],
        ]);
    }

    // ----------------------------------------------------------------
    // Hapus Pengguna
    // ----------------------------------------------------------------

    /**
     * Hapus pengguna (Hanya Admin)
     */
    public function destroy(string $username)
    {
        /** @var \App\Models\User|null $authUser */
        $authUser = Auth::user();

        if (!Auth::check() || !$authUser->isAdmin()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak.',
            ], 403);
        }

        $authUsername = $authUser->user ?? $authUser->username ?? '';

        if ($username === $authUsername) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak dapat menghapus akun sendiri yang sedang aktif!',
            ], 422);
        }

        /** @var \App\Models\User|null $user */
        $user = User::where('user', $username)->first();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status'  => 'success',
            'message' => "Pengguna '{$username}' berhasil dihapus.",
        ]);
    }
}
