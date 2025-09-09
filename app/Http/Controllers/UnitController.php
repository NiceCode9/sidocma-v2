<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Unit::all();
            return datatables()->of($data)->make(true);
        }
        return view('master.units');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required',
            'description' => 'required',
        ]);
        Unit::create($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Unit created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        return response()->json([
            'status' => 'success',
            'data' => $unit,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:units,code,' . $unit->id,
            'description' => 'required',
        ]);
        $unit->update($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Unit updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        $unit->delete();
        return response()->json([
            'success' => true,
            'message' => 'Unit deleted successfully',
        ]);
    }
}
