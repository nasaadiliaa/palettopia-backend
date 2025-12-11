<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function dashboard()
    {
        // tampilkan view resources/views/admin/dashboard.blade.php
        return view('admin.dashboard');
    }

    // contoh method tambahan
    public function indexUsers()
    {
        $users = \App\Models\User::paginate(20);
        return view('admin.users.index', compact('users'));
    }
}
