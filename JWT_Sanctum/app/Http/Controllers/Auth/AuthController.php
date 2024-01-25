<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Archivo;
//use App\Http\Controllers\Auth\Response;
use Illuminate\Support\Facades\Response;


class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:6|confirmed'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed during registration.', ['errors' => $validator->errors()]);
                return response()->json($validator->errors(), 400);
            }

            $user = User::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            Log::info('User registered successfully.', ['user_id' => $user->id]);

            return response()->json([
                'user' => $user,
                'message' => 'User registered successfully.'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Exception during user registration.', ['exception' => $e]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                Log::error('Invalid login credentials.', ['email' => $request->email]);
                return response()->json(['error' => 'Invalid Credentials'], 401);
            }

            Log::info('User logged in successfully.', ['email' => $request->email]);

            return response()->json([
                'token' => $token,
                'user' => auth()->user()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Exception during user login.', ['exception' => $e]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function me()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'No autenticado'], 401);
            }
            Log::info("Info del usuario devuelta: ", ['user' => $user]);
            return response()->json(['user' => $user], 200);
        } catch (\Exception $e) {
            Log::error('Exception during getting user information.', ['exception' => $e]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function logout()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                Log::error('No autenticado');
                return response()->json(['error' => 'No autenticado'], 401);
            }

            auth()->logout();
            Log::info('Logout exitoso');
            return response()->json(['message' => 'Logout exitoso'], 200);
        } catch (\Exception $e) {
            Log::error('Exception during user logout.', ['exception' => $e]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function guardarArchivo(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            Log::info('No autenticado');
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,txt',
        ]);

        $archivo = $request->file('archivo');
        $nombreArchivo = uniqid();

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
            $user = auth()->user();

            if (!$user) {
                Log::info('No autenticado');
                return response()->json(['error' => 'No autenticado'], 401);
            }

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

    /* public function buscarArchivo(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                Log::info('No autenticado');
                return response()->json(['error' => 'No autenticado'], 401);
            }

            $request->validate([
                'path' => 'required|string',
            ]);

            // Buscar y devolver contenido del archivo
            $nombreArchivo = $request->nombre;
            $rutaArchivo = 'valeria/' . $nombreArchivo;

            // Verificar si el archivo existe
            if (!Storage::disk('digitalocean')->exists($rutaArchivo)) {
                Log::info('Archivo no encontrado');
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }

            // Obtener el contenido del archivo como bytes (en lugar de texto)
            $contenido = Storage::disk('digitalocean')->get($rutaArchivo);

            Log::info('Contenido del archivo recuperado', ['nombreArchivo' => $nombreArchivo]);

            // Obtener la extensión del archivo
            $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);

            // Determinar el tipo de contenido según la extensión del archivo
            $tiposDeContenido = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'txt' => 'text/plain',
                // Agrega más tipos de contenido según sea necesario
            ];

            $tipoDeContenido = $tiposDeContenido[$extension] ?? 'application/octet-stream';

            // Devolver el contenido binario como una respuesta con el tipo de contenido adecuado
            return response($contenido)->header('Content-Type', $tipoDeContenido);
        } catch (\Exception $e) {
            Log::error('Error al buscar el archivo', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Error al buscar el archivo'], 500);
        }
    } */

    public function buscarArchivo(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                Log::info('No autenticado');
                return response()->json(['error' => 'No autenticado'], 401);
            }

            $request->validate([
                'path' => 'required|string',
            ]);

            $nombreArchivo = $request->nombre;

            if (!$nombreArchivo) {
                Log::info('Archivo no encontrado');
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }

            $ruta = 'valeria/' . $nombreArchivo;

            // Devolver el archivo como respuesta
            $file = Storage::disk('digitalocean')->get($ruta);

            return response($file, 200)->header('Content-Type', Storage::disk('digitalocean')->mimeType($ruta));
        } catch (\Exception $e) {
            Log::error('Error al mostrar el archivo');
            return response()->json(['error' => 'Error al mostrar el archivo'], 500);
        }
    }
}
