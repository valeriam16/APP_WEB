<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::all();
        return response()->json($user);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    public function getUsersForJWT()
    {
        try {
            // Obtener información del usuario para JWT
            $user = Auth::user();
            
            Log::info('Información del usuario por JWT', ['user_id' => $user->id]);

            return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            Log::error('Error en la autenticación de JWT', ['exception' => $e]);
            return response()->json(['error' => 'Internal Server Error'], 401);
        }
    }

    public function getUsersForSanctum()
    {
        try {
            // Obtener información del usuario para Sanctum
            $user = Auth::guard('sanctum')->user();

            Log::info('Información del usuario por Sanctum', ['user_id' => $user->id]);

            return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            Log::error('Error en la autenticación de Sanctum', ['exception' => $e]);
            return response()->json(['error' => 'Internal Server Error'], 401);
        }
    }
}
