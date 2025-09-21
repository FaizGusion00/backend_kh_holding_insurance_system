<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\Clinic;
use Illuminate\Http\Request;

class AdminFacilityController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->route()->defaults['type'] ?? $request->get('type', 'hospital');
        $model = $type === 'clinic' ? Clinic::class : Hospital::class;
        $facilities = $model::paginate(15);
        
        return view('admin.facilities.index', compact('facilities', 'type'));
    }

    public function create(Request $request)
    {
        $type = $request->route()->defaults['type'] ?? $request->get('type', 'hospital');
        return view('admin.facilities.create', compact('type'));
    }

    public function store(Request $request)
    {
        $type = $request->route()->defaults['type'] ?? $request->get('type', 'hospital');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'is_panel' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $model = $type === 'clinic' ? Clinic::class : Hospital::class;
        $facility = $model::create($data);

        return redirect()->route('admin.facilities.' . $type . 's.index')
            ->with('success', ucfirst($type) . ' created successfully');
    }

    public function show($id, Request $request)
    {
        $type = $request->route()->defaults['type'] ?? $request->get('type', 'hospital');
        $model = $type === 'clinic' ? Clinic::class : Hospital::class;
        $facility = $model::findOrFail($id);
        
        return view('admin.facilities.show', compact('facility', 'type'));
    }

    public function edit($id, Request $request)
    {
        $type = $request->route()->defaults['type'] ?? $request->get('type', 'hospital');
        $model = $type === 'clinic' ? Clinic::class : Hospital::class;
        $facility = $model::findOrFail($id);
        
        return view('admin.facilities.edit', compact('facility', 'type'));
    }

    public function update(Request $request, $id)
    {
        $type = $request->route()->defaults['type'] ?? $request->get('type', 'hospital');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'is_panel' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $model = $type === 'clinic' ? Clinic::class : Hospital::class;
        $facility = $model::findOrFail($id);
        $facility->update($data);

        return redirect()->route('admin.facilities.' . $type . 's.index')
            ->with('success', ucfirst($type) . ' updated successfully');
    }

    public function destroy($id, Request $request)
    {
        $type = $request->route()->defaults['type'] ?? $request->get('type', 'hospital');
        $model = $type === 'clinic' ? Clinic::class : Hospital::class;
        $facility = $model::findOrFail($id);
        $facility->delete();

        return redirect()->route('admin.facilities.' . $type . 's.index')
            ->with('success', ucfirst($type) . ' deleted successfully');
    }
}
