<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $usernameChanged = $user->username !== $request->validated()['username'];

        $user->fill($request->validated());
        $user->save();

        // Jika username berubah, keluar dan minta login ulang dengan username baru
        if ($usernameChanged) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::route('login')->with('status', 'Username berhasil diubah. Silakan login kembali dengan username baru.');
        }

        return Redirect::route('profile.edit')->with('success', 'Profil berhasil disimpan');
    }
}
