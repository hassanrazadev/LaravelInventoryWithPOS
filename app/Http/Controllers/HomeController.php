<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class HomeController extends Controller{
    private $user;

    /**
     * HomeController constructor.
     * @param User $user
     */
    public function __construct(User $user){
        $this->user = $user;
    }

    public function index(){

//        $permission = Permission::create([
//            'name' => 'create category'
//        ]);
//
//        auth()->user()->givePermissionTo($permission);

        return view('dashboard');
    }
}
