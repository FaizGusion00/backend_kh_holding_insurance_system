<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Hospital;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function hospitals(Request $request)
    {
        $q = Hospital::query();
        if ($s = $request->string('search')) {
            $q->where('name', 'like', "%{$s}%");
        }
        return response()->json(['status' => 'success', 'data' => $q->paginate(15)]);
    }

    public function clinics(Request $request)
    {
        $q = Clinic::query();
        if ($s = $request->string('search')) {
            $q->where('name', 'like', "%{$s}%");
        }
        return response()->json(['status' => 'success', 'data' => $q->paginate(15)]);
    }

    public function searchHospitals(Request $request)
    {
        $q = $request->string('q');
        $items = Hospital::where('name', 'like', "%{$q}%")->limit(20)->get();
        return response()->json(['status' => 'success', 'data' => ['hospitals' => $items]]);
    }

    public function searchClinics(Request $request)
    {
        $q = $request->string('q');
        $items = Clinic::where('name', 'like', "%{$q}%")->limit(20)->get();
        return response()->json(['status' => 'success', 'data' => ['clinics' => $items]]);
    }
}


