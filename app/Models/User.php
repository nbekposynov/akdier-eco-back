<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bin_company',
        'description',
        'moderator_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Проверка роли
    /**
     * Проверяет, имеет ли пользователь указанную роль
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Проверяет, является ли пользователь администратором
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Получить модератора пользователя (если пользователь - компания)
     */
    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    /**
     * Получить компании, связанные с модератором (если пользователь - модератор)
     */
    public function companies()
    {
        return $this->hasMany(User::class, 'moderator_id')
            ->where('role', 'company');
    }
}
