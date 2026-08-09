<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GovAgency;
use App\Models\Implementation;
use App\Models\Municipality;
use App\Models\RcspBarangay;
use App\Models\RcspForm;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Municipality name => LGU seal image in public/assets/LGUS. */
    private array $seals = [
        'Digos City' => 'digos.png', 'Digos' => 'digos.png',
        'Kiblawan' => 'kiblawan.png', 'Magsaysay' => 'magsaysay.png',
        'Malalag' => 'malalag.png', 'Hagonoy' => 'hagonoy.png',
        'Bansalan' => 'bansalan.png', 'Matanao' => 'matanao.png',
        'Padada' => 'padada.jpg', 'Sulop' => 'sulop.png',
        'Santa Cruz' => 'sta cruz.png', 'Sta. Cruz' => 'sta cruz.png', 'Sta Cruz' => 'sta cruz.png',
    ];

    public function index(): View
    {
        $stats = [
            'rcsp_barangays' => RcspBarangay::count(),
            'pending_forms' => RcspForm::where('status', 'submitted')->distinct('rcsp_barangay_id')->count('rcsp_barangay_id'),
            'for_verification' => Implementation::where('status', 'for verification')->count(),
            'agencies' => GovAgency::count(),
        ];

        // Recognized RCSP barangays (completed) + total per municipality, with seal.
        $municipalities = Municipality::orderBy('name')->get()->map(function ($m) {
            return [
                'name' => trim($m->name),
                'total' => RcspBarangay::where('municipality_id', $m->id)->count(),
                'recognized' => RcspBarangay::where('municipality_id', $m->id)->where('status', 'Completed')->count(),
                'seal' => $this->seals[trim($m->name)] ?? null,
            ];
        });

        return view('admin.dashboard', compact('stats', 'municipalities'));
    }
}
