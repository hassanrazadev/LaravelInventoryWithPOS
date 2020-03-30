<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles, SoftDeletes;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @param array $params
     * @return array
     */
    public function doLogin(array $params){
        $user = $this::where('email', $params['email'])->first();
        $data = [];
        if (!$user){
            $data['status'] = false;
            $data['message'] = "No account found for email " . $params['email'];
        } elseif ($user->email_verified_at == null){
            $data['status'] = false;
            $data['message'] = "Please verify your email";
        } elseif (auth()->attempt(['email' => $params['email'], 'password' => $params['password']])){
            $data['status'] = true;
            $data['message'] = "Login Success";
        } else{
            $data['status'] = false;
            $data['message'] = "Email or Password is incorrect";
        }
        return $data;
    }
}
