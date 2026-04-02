<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdatePasswordRequest;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Http\Requests\Account\UpdateSettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $updateData = [
            'name' => $data['name'],
            'phone' => $data['phone'],
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $updateData['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($updateData);
        $user->save();
        $user->refresh()->load(['roles', 'filial']);

        return response()->json([
            'message' => 'Profil ma\'lumotlari muvaffaqiyatli yangilandi.',
            'user' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'login' => $user->login,
                'avatar_url' => $user->avatar_url,
                'role' => optional($user->roles->first())->name,
                'filial' => optional($user->filial)->name,
            ],
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($request->validated()['password']),
        ])->save();

        return response()->json([
            'message' => 'Parol muvaffaqiyatli yangilandi.',
        ]);
    }

    public function updateSettings(UpdateSettingsRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $settings = array_merge($user->settings ?? [], [
            'weather_city' => trim($validated['weather_city']),
            'reduced_motion' => (bool) $validated['reduced_motion'],
        ]);

        $user->forceFill([
            'settings' => $settings,
        ])->save();

        return response()->json([
            'message' => 'Sozlamalar saqlandi.',
            'settings' => $settings,
        ]);
    }
}
