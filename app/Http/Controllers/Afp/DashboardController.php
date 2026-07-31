<?php

namespace App\Http\Controllers\Afp;

use App\Http\Controllers\Controller;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\RcspBarangay;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Municipality name => LGU seal image in public/assets/LGUS. */
    private array $seals = [
        'Bansalan' => 'bansalan.png', 'Digos' => 'digos.png', 'Digos City' => 'digos.png',
        'Hagonoy' => 'hagonoy.png',
        'Kiblawan' => 'kiblawan.png', 'Magsaysay' => 'magsaysay.png', 'Malalag' => 'malalag.png',
        'Matanao' => 'matanao.png', 'Padada' => 'padada.jpg', 'Santa Cruz' => 'sta cruz.png',
        'Sulop' => 'sulop.png',
    ];

    public function index(): View
    {
        $municipalities = Municipality::orderBy('name')->get()->map(fn ($m) => [
            'name' => trim($m->name),
            'recognized' => RcspBarangay::where('municipality_id', $m->id)->where('status', 'Completed')->count(),
            'total' => RcspBarangay::where('municipality_id', $m->id)->count(),
            'seal' => $this->seals[trim($m->name)] ?? null,
        ]);

        $frPoints = FormerRebel::whereNotNull('latitude')->whereNotNull('longitude')
            ->get(['firstname', 'lastname', 'placement_address', 'latitude', 'longitude', 'status'])
            ->map(fn ($fr) => [
                'name' => trim("{$fr->firstname} {$fr->lastname}"),
                'address' => $fr->placement_address,
                'lat' => (float) $fr->latitude,
                'lng' => (float) $fr->longitude,
                'status' => $fr->status,
            ]);

        return view('afp.dashboard', compact('municipalities', 'frPoints'));
    }
}
