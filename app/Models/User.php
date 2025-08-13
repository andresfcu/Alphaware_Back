<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Atributos asignables en masa
     */
    protected $fillable = ['name','email','password','avatar'];
    protected $appends = ['avatar_url'];

    /**
     * Atributos ocultos en la serialización
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessor: URL pública del avatar
     * - Si en BD guardas "/storage/avatars/xxx.jpg" o "storage/avatars/xxx.jpg" -> devuelve APP_URL/... listo para <img src="...">
     * - Si ya guardas una URL http(s) -> la deja igual
     * - Si no hay avatar -> null
     */
    protected function avatarUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::get(function () {
            if (empty($this->avatar)) return null;
            $raw = ltrim((string) $this->avatar, '/');
            if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) return $this->avatar;
            return asset(str_starts_with($raw, 'storage/') ? $raw : 'storage/'.$raw);
        });
    }
}
