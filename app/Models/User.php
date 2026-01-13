<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Notifications\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public function sendPasswordResetNotification($token): void
    {
        // 1. Generiamo l'URL che punta al pannello Filament
        // Se il tuo panel ha un ID diverso da 'admin', cambialo qui sotto
        $url = route('filament.admin.auth.password-reset.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ]);

        // 2. Usiamo la classe di notifica di Laravel, ma passando l'URL creato da noi
        $notification = new \Illuminate\Auth\Notifications\ResetPassword($token);

        // Questo trucco sovrascrive la generazione automatica dell'URL interna alla notifica
        $notification->createUrlUsing(fn($user, $token) => $url);

        $this->notify($notification);
    }

    public function socialiteAccounts(): HasMany
    {
        return $this->hasMany(SocialiteUser::class);
    }

    public function canResetPassword(): bool
    {
        return true;  // Abilita reset password
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'microsoft_id',
        'azure_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'microsoft_token',
        'microsoft_refresh_token',
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
}
