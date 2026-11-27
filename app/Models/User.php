<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'created_by',
        'updated_by',
    ];

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
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Attempt user login with optional remember-me persistence.
     */
    public function doLogin(array $params): array
    {
        $user = $this::where('email', $params['email'])->first();
        $remember = (bool) ($params['remember'] ?? false);
        $data = [];

        if (! $user) {
            $data['status'] = false;
            $data['message'] = 'No account found for email '.$params['email'];
        } elseif ($user->email_verified_at === null) {
            $data['status'] = false;
            $data['message'] = 'Please verify your email';
        } elseif (auth()->attempt(['email' => $params['email'], 'password' => $params['password']], $remember)) {
            $data['status'] = true;
            $data['message'] = 'Login Success';
        } else {
            $data['status'] = false;
            $data['message'] = 'Email or Password is incorrect';
        }

        return $data;
    }
}
