<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{

public function me(Request $request)
{
    $u = $request->user();

    // Normaliza la URL del avatar
    $avatar = null;
    if (!empty($u->avatar)) {
        $raw = ltrim((string) $u->avatar, '/'); // "storage/avatars/..." o "avatars/..." o "http..."
        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            $avatar = $u->avatar; // ya es URL completa
        } elseif (str_starts_with($raw, 'storage/')) {
            $avatar = asset($raw); // http://APP_URL/storage/avatars/...
        } else {
            // Guardado como "avatars/archivo.jpg" -> servir desde /storage
            $avatar = asset('storage/' . $raw);
        }
    }

    return response()->json([
        'user' => [
            'id'     => $u->id,
            'name'   => $u->name,
            'email'  => $u->email,
            'avatar' => $avatar,
        ],
        'roles'  => method_exists($u, 'roles') ? $u->roles->pluck('name')->all() : [], // 👈 array plano
        'tenant' => $u->tenant ?? null,
    ]);
}

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $user = $request->user();
        $user->update($data);

        return response()->json(['message' => 'Perfil actualizado']);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password'            => 'required',
            'new_password'                => ['required', 'confirmed', Password::defaults()],
            // recuerda enviar new_password_confirmation desde el front
        ]);

        if (!Hash::check($data['current_password'], $request->user()->password)) {
            return response()->json(['message' => 'Contraseña actual incorrecta'], 422);
        }

        $request->user()->update([
            'password' => Hash::make($data['new_password']),
        ]);

        return response()->json(['message' => 'Contraseña actualizada']);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $user = $request->user();

        // 1) Borrar avatar anterior si está en el disco 'public'
        if (!empty($user->avatar)) {
            // Puede venir como '/storage/avatars/...' o 'avatars/...'
            $old = ltrim($user->avatar, '/');
            // Normalizar a ruta relativa del disco 'public'
            if (str_starts_with($old, 'storage/')) {
                $old = substr($old, strlen('storage/')); // => 'avatars/...'
            }
            if (str_starts_with($old, 'avatars/')) {
                Storage::disk('public')->delete($old); // ignora si no existe
            }
        }

        // 2) Guardar nuevo
        $path = $request->file('avatar')->store('avatars', 'public'); // 'avatars/archivo.png'
        $publicUrl = Storage::url($path); // '/storage/avatars/archivo.png'

        $user->avatar = $publicUrl; // guardamos la ruta pública (más fácil para el front)
        $user->save();

        return response()->json(['avatar' => asset(ltrim($publicUrl, '/'))]);
    }
}
