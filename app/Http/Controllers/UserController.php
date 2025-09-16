<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['unit', 'roles'])->get();
        $units = Unit::all();
        $roles = Role::all();

        return view('master.users', compact('users', 'units', 'roles'));
    }

    /**
     * Generate unique user code based on unit
     */
    private function generateUserCode($unitId)
    {
        $unit = Unit::find($unitId);
        if (!$unit) {
            return null;
        }

        // Create abbreviation from unit name (first letters of each word)
        $words = explode(' ', strtoupper($unit->name));
        $abbreviation = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $abbreviation .= substr($word, 0, 1);
            }
        }

        // If abbreviation is too short, use first 3 characters of unit name
        if (strlen($abbreviation) < 2) {
            $abbreviation = strtoupper(substr(str_replace(' ', '', $unit->name), 0, 3));
        }

        // Generate unique code
        do {
            $randomNumber = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $kodeUser = $abbreviation . $randomNumber;
        } while (User::where('kode_user', $kodeUser)->exists());

        return $kodeUser;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'unit_id' => 'required|exists:units,id',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate kode user automatically
            $kodeUser = $this->generateUserCode($request->unit_id);
            if (!$kodeUser) {
                throw new \Exception('Gagal generate kode user');
            }

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'kode_user' => $kodeUser,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'unit_id' => $request->unit_id,
                'is_active' => $request->is_active ?? true,
            ]);

            // Assign roles
            $user->assignRole($request->roles);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan dengan kode: ' . $kodeUser,
                'data' => $user->load(['unit', 'roles'])
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['unit', 'roles']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'unit_id' => 'required|exists:units,id',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Check if unit changed, if yes generate new kode_user
            $needNewCode = false;
            $kodeUser = $user->kode_user;

            if ($request->unit_id != $user->unit_id) {
                $kodeUser = $this->generateUserCode($request->unit_id);
                if (!$kodeUser) {
                    throw new \Exception('Gagal generate kode user baru');
                }
                $needNewCode = true;
            }

            $updateData = [
                'name' => $request->name,
                'username' => $request->username,
                'kode_user' => $kodeUser,
                'email' => $request->email,
                'unit_id' => $request->unit_id,
                'is_active' => $request->is_active ?? true,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Sync roles
            $user->syncRoles($request->roles);

            DB::commit();

            $message = 'User berhasil diupdate!';
            if ($needNewCode) {
                $message .= ' Kode user baru: ' . $kodeUser;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $user->load(['unit', 'roles'])
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Check if user has related data that prevents deletion
            if (
                $user->createdFolders()->exists() ||
                $user->createdDocuments()->exists() ||
                $user->documentAccessLogs()->exists()
            ) {

                return response()->json([
                    'success' => false,
                    'message' => 'User tidak dapat dihapus karena memiliki data terkait!'
                ], 400);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
