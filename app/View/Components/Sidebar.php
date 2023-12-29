<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;
use Illuminate\View\View;

class Sidebar extends Component
{
    /**
     * @var array
     */
    public $user;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(){
        $user = auth()->user();
        $this->user = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles()->first()->name
        ];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render(){
        return view('components.sidebar');
    }

    public function activeNav(){
        $activeNav = Route::currentRouteName();
        $activeNav = explode('.', $activeNav);
        $activeNav = $activeNav[0];
        return $activeNav;
    }
}
