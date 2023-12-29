<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Header extends Component {

    public $user;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct() {
        $user = auth()->user();
        $this->user = [
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render() {
        return view('components.header');
    }

}
