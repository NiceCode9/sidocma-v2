<?php

namespace App\Http\Controllers;

use App\Models\DocumentCategory;
use Illuminate\Http\Request;

class DocumentCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DocumentCategory::all();
            return datatables()->of($data)->make(true);
        }
        return view('master.document-categories');
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
            'description' => 'required',
        ]);
        DocumentCategory::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Document category created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(DocumentCategory $documentCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentCategory $documentCategory)
    {
        return response()->json([
            'success' => true,
            'data' => $documentCategory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentCategory $documentCategory)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'is_active' => 'required',
        ]);
        $documentCategory->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Document category updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentCategory $documentCategory)
    {
        $documentCategory->delete();
        return response()->json([
            'success' => true,
            'message' => 'Document category deleted successfully',
        ]);
    }
}
