<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:customer']);
    }

    public function dashboard()
    {
        // tampilkan view resources/views/customer/dashboard.blade.php
        return view('customer.dashboard');
    }

    // contoh method tambahan
    public function profile()
    {
        $user = auth()->user();
        return view('customer.profile', compact('user'));
    }
}
