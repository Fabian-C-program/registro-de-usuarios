<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Mail\WelcomeUserMail;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        // 1. Validaciones extra completas
        
        $validated = $request->validate([
            'name'=> ['required', 'string', 'min:3', 'max:255'],
            'email'=> ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => 'Este correo electrónico ya se encuentra registrado.',
            'password.confirmed' => 'Las contraseñas ingresadas no coinciden.',
        ]);

        // 2. Guardar usuario en la Base de Datos
    
        $user = User::create([
            'name'=> $validated['name'],
            'email'=> $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // 3. Enviar correo de bienvenida mediante Brevo SMTP

        Mail::to($user->email)->send(new WelcomeUserMail($validated));

        // 4. Retornar respuesta exitosa

        return back()->with('success', '¡Usuario registrado con éxito! Correo de bienvenida enviado.');
    }
}