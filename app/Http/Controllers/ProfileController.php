<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── SHOW ─────────────────────────────────────────────────────────────
    public function show()
    {
        $user = Auth::user()->load('branch')->loadCount([   // ← add ->load('branch')
            'assignedEduLeads',
            'assignedEduLeads as hot_leads_count'     => fn($q) => $q->where('interest_level', 'hot'),
            'assignedEduLeads as admitted_leads_count' => fn($q) => $q->where('final_status', 'admitted'),
            'createdEduLeads',
        ]);

        return view('profile.show', compact('user'));
    }

    // ── EDIT ─────────────────────────────────────────────────────────────
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    // ── UPDATE PROFILE ───────────────────────────────────────────────────
    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name'  => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
            ], [
                'name.required'  => 'Name is required',
                'name.max'       => 'Name cannot exceed 255 characters',
                'email.required' => 'Email is required',
                'email.email'    => 'Please enter a valid email address',
                'email.unique'   => 'This email is already in use by another account',
                'phone.max'      => 'Phone number cannot exceed 20 characters',
            ]);

            $user->update($validated);

            Log::info('Profile updated', [
                'user_id'        => $user->id,
                'updated_fields' => array_keys($validated),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Profile updated successfully!',
                    'name'     => $user->name,     // useful for navbar live-update
                    'redirect' => route('profile.show'),
                ]);
            }

            return redirect()->route('profile.show')
                ->with('success', 'Profile updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Update failed. Please try again.'], 500);
            }
            return back()->withInput()->with('error', 'An error occurred while updating your profile.');
        }
    }

    // ── UPDATE PASSWORD ──────────────────────────────────────────────────
    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => ['required', 'current_password'],
                'password'         => ['required', 'confirmed', Password::min(8)
                                            ->mixedCase()
                                            ->numbers()],
            ], [
                'current_password.required'         => 'Current password is required',
                'current_password.current_password' => 'The current password you entered is incorrect',
                'password.required'                 => 'New password is required',
                'password.confirmed'                => 'Password confirmation does not match',
                'password.min'                      => 'Password must be at least 8 characters',
            ]);

            Auth::user()->update(['password' => Hash::make($request->password)]);

            Log::info('Password updated', ['user_id' => Auth::id()]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Password updated successfully!']);
            }

            return back()->with('success', 'Password updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Password update error: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Password update failed.'], 500);
            }
            return back()->with('error', 'An error occurred while updating your password.');
        }
    }
}
