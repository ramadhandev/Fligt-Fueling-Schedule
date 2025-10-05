<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class MobileProfileController extends Controller
{
    public function update(Request $request)
    {
        // Dapatkan user dengan model yang benar
        $user = Auth::user();
        
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'current_password' => 'sometimes|required_with:new_password',
            'new_password' => 'sometimes|min:6|confirmed',
        ]);

        try {
            // Pastikan $user adalah instance of User model
            if (!$user instanceof User) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Update data
            $user->name = $request->name;

            // Update password jika ada
            if ($request->filled('current_password') && $request->filled('new_password')) {
                // Verify current password
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Password saat ini salah'
                    ], 422);
                }

                // Update password
                $user->password = Hash::make($request->new_password);
            }

            // ✅ GUNAKAN update() ATAU save() YANG BENAR
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'phone_number' => $user->phone_number,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone_number' => $user->phone_number,
                ]
            ]
        ]);
    }
}