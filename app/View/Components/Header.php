<?php

namespace App\View\Components;

use App\Utils\AppUtils;
use Illuminate\View\Component;
use Illuminate\View\View;

class Header extends Component
{
    public array $user;

    public function __construct()
    {
        $user = auth()->user();
        $role = $user ? $user->roles()->first() : null;
        $name = $user ? $user->name : '';

        $this->user = [
            'name' => $name,
            'email' => $user ? $user->email : '',
            'role' => $role ? $role->name : 'user',
            'initials' => AppUtils::initials($name),
        ];
    }

    public function render(): View|string
    {
        return view('components.header');
    }
}
