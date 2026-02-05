<?php

namespace App\Models;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'mot_de_passe',
        'id_role',
        'id_site',
        'statut'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }




    public function r_user_role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role');
    }

    public function r_user_site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'id_site');
    }

    public function r_user_permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission', 'id_user', 'id_Permission');
    }

    public function r_user_roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role', 'id_user', 'id_role');
    }

    public function r_user_dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class, 'id_user');
    }

    public function r_user_transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'id_user');
    }
}
