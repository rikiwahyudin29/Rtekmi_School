<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserDeviceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $devices = UserDevice::with('user')
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                })
                ->orWhere('device_name', 'like', "%{$search}%")
                ->orWhere('device_id', 'like', "%{$search}%");
            })
            ->orderBy('last_login_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Hapus query allLocations dari sini karena dipindah ke method locations()

        return Inertia::render('Admin/Users/Devices', [
            'devices' => $devices,
            'filters' => $request->only('search', 'per_page')
        ]);
    }

    public function locations()
    {
        $allLocations = UserDevice::with('user:id,nama_lengkap,role,username')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'user_id', 'device_name', 'last_ip', 'latitude', 'longitude', 'last_login_at']);

        return Inertia::render('Admin/Users/Locations', [
            'locations' => $allLocations
        ]);
    }

    public function destroy($id)
    {
        $device = UserDevice::findOrFail($id);
        
        // Menghapus token Sanctum yang terikat dengan device ini jika ada
        if ($device->user) {
            $device->user->tokens()->where('name', $device->device_id)->delete();
        }
        
        $device->delete();

        return redirect()->back()->with('message', '✅ Perangkat berhasil dihapus / di-reset.');
    }
}
