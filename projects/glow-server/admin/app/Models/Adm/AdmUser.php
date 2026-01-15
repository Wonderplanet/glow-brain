<?php

namespace App\Models\Adm;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class AdmUser extends Authenticatable implements FilamentUser
{
    use HasRoles;

    protected $table = 'adm_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'first_name',
        'last_name',
        'avatar',
        'active',
        'slack_id',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMention(): string
    {
        return $this->slack_id ? "<@$this->slack_id>" : $this->name;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function createUserByGoogle($googleUser)
    {
        return self::create([
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'password' => Hash::make(uniqid()),
            'avatar' => $googleUser->avatar,
            'active'   => 1,
        ]);
    }

    public function findByEmail(string $email)
    {
        return self::query()->where('email', $email)->first();
    }
}
