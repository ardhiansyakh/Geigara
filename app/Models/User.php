<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

/**
 * Model User – Sistem Monitoring Radiasi
 *
 * @property string      $user
 * @property string      $username
 * @property string      $name
 * @property string      $email
 * @property string      $password
 * @property string      $role
 * @property string|null $remember_token
 * @property string      $role_label
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Nonaktifkan timestamps karena tabel user hanya memiliki 2 kolom (user & password)
     */
    public $timestamps = false;

    /**
     * Tentukan nama tabel secara fleksibel (user atau users)
     */
    public function getTable()
    {
        if (Schema::hasTable('user')) {
            return 'user';
        }
        return 'users';
    }

    /**
     * Tentukan primary key (kolom 'user' jika tidak ada 'id')
     */
    public function getKeyName()
    {
        if (Schema::hasTable('user')) {
            if (!Schema::hasColumn('user', 'id') && Schema::hasColumn('user', 'user')) {
                return 'user';
            }
        }
        if (Schema::hasTable('users')) {
            if (!Schema::hasColumn('users', 'id') && Schema::hasColumn('users', 'user')) {
                return 'user';
            }
            if (!Schema::hasColumn('users', 'id') && Schema::hasColumn('users', 'username')) {
                return 'username';
            }
        }
        return 'id';
    }

    /**
     * Nama kolom identifier untuk login auth di Laravel
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * Nonaktifkan auto-increment jika kolom primary key bertipe string (seperti 'user')
     */
    public function getIncrementing()
    {
        return $this->getKeyName() === 'id';
    }

    /**
     * Tipe data primary key
     */
    public function getKeyType()
    {
        return $this->getKeyName() === 'id' ? 'int' : 'string';
    }

    /**
     * Izinkan semua kolom diisi tanpa terhalang fillable
     */
    protected $guarded = [];

    /**
     * Atribut yang disembunyikan
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cek apakah pengguna memiliki hak akses Administrator
     */
    public function isAdmin(): bool
    {
        return strtolower(trim($this->role ?? 'user')) === 'admin';
    }

    /**
     * Cek apakah pengguna adalah User Biasa (hanya melihat)
     */
    public function isRegularUser(): bool
    {
        return !$this->isAdmin();
    }

    /**
     * Dapatkan label role pengguna
     */
    public function getRoleLabelAttribute(): string
    {
        return $this->isAdmin() ? 'Administrator' : 'User Biasa';
    }

    /**
     * Accessor fleksibel untuk kolom 'user' dan 'username'
     */
    public function getUserAttribute($value)
    {
        return $value ?? $this->attributes['username'] ?? $this->attributes['name'] ?? null;
    }

    public function getUsernameAttribute($value)
    {
        return $value ?? $this->attributes['user'] ?? $this->attributes['name'] ?? null;
    }
}
