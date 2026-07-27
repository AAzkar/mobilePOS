<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('users.form', ['user' => new User()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:admin,cashier'],
            'pin' => ['required', 'digits_between:4,6'],
        ]);

        User::query()->create([
            'name' => $data['name'],
            'role' => $data['role'],
            'pin_hash' => Hash::make($data['pin']),
            'is_active' => true,
        ]);

        return redirect()->route('users.index')->with('status', "User \"{$data['name']}\" created.");
    }

    public function edit(User $user): View
    {
        return view('users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:admin,cashier'],
            'pin' => ['nullable', 'digits_between:4,6'],
        ]);

        $user->name = $data['name'];
        $user->role = $data['role'];
        $user->is_active = $request->boolean('is_active');

        if (! empty($data['pin'])) {
            $user->pin_hash = Hash::make($data['pin']);
        }

        $user->save();

        return redirect()->route('users.index')->with('status', "User \"{$user->name}\" updated.");
    }
}
