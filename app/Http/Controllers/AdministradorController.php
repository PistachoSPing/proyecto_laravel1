<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdministradorController extends Controller
{
    // Mostrar todos los administradores
    public function admin()
    {
        $administradores = Administrador::all();
        return view('administradores', compact('administradores'));
    }

    // Mostrar el formulario para crear un nuevo administrador
    public function crear_admin()
    {
        return view('administradores_alta');
    }

    // Registrar un nuevo administrador
    public function registrar_admin(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:155',
            'a_paterno' => 'required|string|max:155',
            'a_materno' => 'required|string|max:155',
            'email' => 'required|string|email|max:255|unique:administrador',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:15',
            'rol' => 'required|string|max:50',
        ]);

        $administrador = new Administrador($request->all());
        $administrador->password = Hash::make($request->password);
        $administrador->estado = true;
        $administrador->save();

        return redirect()->route('administradores')->with('success', 'Administrador creado exitosamente.');
    }

    // Mostrar detalles del administrador
    public function ver_admin($id)
    {
        $administrador = Administrador::findOrFail($id);
        return view('administradores_detalles', compact('administrador'));
    }

    // Mostrar formulario de edición del administrador
    public function editar($id)
    {
        $administrador = Administrador::findOrFail($id);
        return view('administradores_editar', compact('administrador'));
    }

    // Actualizar datos del administrador
    public function actualizar_admin(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:155',
            'a_paterno' => 'required|string|max:155',
            'a_materno' => 'nullable|string|max:155',
            'email' => 'required|string|email|max:255|unique:administrador,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:15',
            'rol' => 'required|string|max:50',
            'estado' => 'required|string|in:si,no',
        ]);

        $administrador = Administrador::findOrFail($id);
        $administrador->fill($request->except('password'));
        $administrador->estado = $request->estado === 'si';

        if ($request->filled('password')) {
            $administrador->password = Hash::make($request->password);
        }

        $administrador->save();
        return redirect()->route('administradores')->with('success', 'Administrador actualizado exitosamente.');
    }

    // Eliminar administrador
    public function eliminar_admin($id)
    {
        $administrador = Administrador::findOrFail($id);
        $administrador->delete();
        return redirect()->route('administradores')->with('success', 'Administrador eliminado exitosamente.');
    }

    // Mostrar formulario de inicio de sesión
    public function showLoginForm()
    {
        return view('auth.login_administrador');
    }

    // Iniciar sesión como administrador
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        $administrador = Administrador::where('email', $request->email)->first();

        if ($administrador && Hash::check($request->password, $administrador->password)) {
            Auth::login($administrador);
            return redirect()->route('administradores')->with('success', 'Has iniciado sesión como administrador.');
        }

        return back()->withErrors(['email' => 'Las credenciales proporcionadas son incorrectas.']);
    }

    // Cerrar sesión
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login_administrador')->with('success', 'Has cerrado sesión.');
    }
}
