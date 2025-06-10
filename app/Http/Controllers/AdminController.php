<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view("admin.admin.index", ["admins" => User::all()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|min:3|unique:users,nip|max:250',
            'name' => 'required|string|max:100',
            'password' => 'required|min:5|max:250',
        ]);

        User::create([
            'nip' => $request->input('nip'),
            'name' => $request->input('name'),
            'password' => bcrypt($request->input('password')),
        ]);

        return back()->with('success', 'Data user berhasil disimpan');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $nip)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $admin = User::findOrFail($nip);
        $admin->update([
            'name' => $request->input('name'),
        ]);

        return back()->with('success', 'Data user berhasil disimpan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $nip)
    {
        $admin = User::findOrFail($nip);
        $admin->delete();

        return back()->with('success', 'Data user berhasil disimpan');

    }
}
