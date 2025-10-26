<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }

    public function edit($id)
    {
        return view('admin.users.edit', compact('id'));
    }

    public function show($id)
    {
        return view('admin.users.show', compact('id'));
    }
}
