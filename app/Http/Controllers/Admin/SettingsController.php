<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function signalGenerator()
    {
        return view('admin.settings.signal-generator');
    }

    public function system()
    {
        return view('admin.settings.system');
    }

    public function apiKeys()
    {
        return view('admin.api-keys.index');
    }

    public function analytics()
    {
        return view('admin.analytics.index');
    }
}
