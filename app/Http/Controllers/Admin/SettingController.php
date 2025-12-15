<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:settings_manage');
    }

    public function edit()
    {
        $precio = Setting::get('cita_precio', '0.00');
        return view('admin.settings.cita_precio', compact('precio'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'cita_precio' => 'required|numeric|min:0',
        ]);

        Setting::set('cita_precio', number_format($data['cita_precio'], 2, '.', ''));

        return redirect()->route('admin.settings.cita_precio.edit')->with('success', 'Precio de cita actualizado.');
    }
}
