<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

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

    //model relation 
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


    public function hasRole(string $roleName): bool
    {
        $role = DB::table('roles')->where('id', $this->id_role)->first();

        return $role && $role->nom_role === $roleName;
    }


    // public  function getType()
    // {
    //     $user = Auth::user();
    //     $user = auth()->user()->user_type;
    //     if ($user == 1) {
    //         return 'bureau';
    //     } elseif ($user == 2) {
    //         return 'client ';
    //     } elseif ($user == 3) {
    //         return 'partener';
    //     }
    // }
}
