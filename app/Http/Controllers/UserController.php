<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginFormRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $user;

    /**
     * UserController constructor.
     * @param User $user
     */
    public function __construct(User $user){
        $this->user = $user;
    }

    /**
     * @param LoginFormRequest $request
     * @return RedirectResponse
     */
    public function doLogin(LoginFormRequest $request){
        $fields = $request->validated();
        $data = $this->user->doLogin($fields);

        if ($data['status']){
            return redirect()->route('dashboard');
        }

        return redirect()->back();
    }

}
