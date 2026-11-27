<?php

namespace App\View\Components;

use App\Utils\AppUtils;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;
use Illuminate\View\View;

class Sidebar extends Component
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
        return view('components.sidebar');
    }

    public function activeNav(): string
    {
        $activeNav = Route::currentRouteName() ?? '';
        if ($activeNav === 'orders.create') {
            return 'pos';
        }

        $parts = explode('.', $activeNav);

        return $parts[0] ?? '';
    }

    public function isNavActive(string $nav): bool
    {
        if ($nav === 'pos') {
            return request()->routeIs('orders.create');
        }

        if ($nav === 'orders') {
            return request()->routeIs('orders.*') && ! request()->routeIs('orders.create');
        }

        if ($nav === 'dashboard') {
            return request()->routeIs('dashboard');
        }

        return request()->routeIs("{$nav}.*");
    }
}
