<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Edwink\FilamentUserActivity\Traits\UserActivityTrait;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasRoles,UserActivityTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : "/asset/img/user05.png";
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }
}
