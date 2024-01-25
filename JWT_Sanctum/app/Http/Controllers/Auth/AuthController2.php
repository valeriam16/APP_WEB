<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use \stdClass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Archivo;


class AuthController2 extends Controller
{
    //
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|min:6|string'
            ]);

            if ($validator->fails()) {
                Log::error('Error de validación.', ['errors' => $validator->errors()]);
                return response()->json($validator->errors(), 400);
            }

            $user = User::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            Log::info('User registered successfully.', ['user_id' => $user->email]);

            return response()->json(['data' => $user, 'message' => 'User registered successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Exception during user registration.', ['exception' => $e]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                Log::error('Invalid login credentials.', ['email' => $request->email]);
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user = User::where('email', $request['email'])->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('User logged in successfully.', ['email' => $request->email]);

            return response()->json(['data' => $user, 'access_token' => $token, 'token_type' => 'Bearer'], 200);
        } catch (\Exception $e) {
            Log::error('Exception during user login.', ['exception' => $e]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json(['data' => $user], 200);
        } catch (\Exception $e) {
            Log::error('Exception during fetching user profile.', ['exception' => $e]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            Log::info('User logged out successfully.', ['user_id' => $request->user()->id]);
            return response()->json(['message' => 'Logged out'], 200);
        } catch (\Exception $e) {
            Log::error('Exception during user logout.', ['exception' => $e]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function guardarArchivo(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file',
            'nombre' => 'required|string',
        ]);

        $archivo = $request->file('archivo');
        $nombrePersonalizado = $request->input('nombre');

        // Si el usuario proporciona un nombre personalizado, úsalo; de lo contrario, usa el nombre original
        $nombreArchivo = $nombrePersonalizado ?: $archivo->getClientOriginalName();

        $path = Storage::disk('digitalocean')->put('valeria', $archivo, 'public');

        // Crear una nueva entrada en la base de datos
        $archivoModel = new Archivo();
        $archivoModel->nombre = $nombreArchivo;
        $archivoModel->ruta = $path;
        $archivoModel->save();

        Log::info('Archivo guardado con éxito', ['path' => $path]);
        return response()->json(['message' => 'Archivo guardado con éxito', 'path' => $path], 200);
    }

    public function listarArchivos()
    {
        try {
            $files = Storage::disk('digitalocean')->files('valeria');

            if (empty($files)) {
                Log::info('La carpeta "valeria" se encuentra vacía.');
                return response()->json(['message' => 'La carpeta se encuentra vacía'], 200);
            }

            Log::info('Archivos: ', ['archivos' => $files]);
            return response()->json(['archivos' => $files], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener la lista de archivos');
            return response()->json(['error' => 'Error al obtener la lista de archivos'], 500);
        }
    }
}
