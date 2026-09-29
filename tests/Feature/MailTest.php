<?php

namespace Tests\Feature;

use App\Mail\WelcomeUserMail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailTest extends TestCase
{
    use DatabaseMigrations; 

    public function test_formulario_mail_se_muestra_correctamente(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);

        $response->assertSee('Formulario de Registro');
        $response->assertSee('Nombre Completo:');
        $response->assertSee('Correo Electrónico:');
        $response->assertSee('Contraseña:');
        $response->assertSee('Confirmar Contraseña:');
        $response->assertSee('Registrarse');
    }

    public function test_vista_bienvenida_se_muestra_correctamente(): void
    {
        // 1. Datos de prueba
        $datos = ['nombre' => 'Carlos'];

        // 2. Renderizamos la vista directamente
        $view = $this->view('emails.welcome', $datos);

        // 3. Verificamos palabras clave de forma independiente
        $view->assertSeeHtml('Hola');
        $view->assertSeeHtml('Carlos');
        $view->assertSeeHtml('Gracias por registrarte');
        $view->assertSeeHtml('Registro completado exitosamente');
    }

    public function test_envio_de_formulario_despacha_mail_de_bienvenida(): void
    {
        // 1. Activamos el 'Fake' de correos
        Mail::fake();

        $datosFormulario = [
            'name' => 'Juan Pérez',
            'email' => 'juan.perez.prueba@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 2. Ejecutamos la petición POST
        $response = $this->post('/register', $datosFormulario);

        $response->assertStatus(302);

        // 3. Validamos destinatario Y datos internos exactos de tu WelcomeUserMail
        Mail::assertQueued(WelcomeUserMail::class, function ($mail) use ($datosFormulario) {
            // Comprueba que va al destinatario correcto
            $destinatarioCorrecto = $mail->hasTo('juan.perez.prueba@gmail.com');

            // Comprueba que los datos que viajan dentro coincidan con el formulario
            $datosCorrectos = $mail->userData['name'] === $datosFormulario['name'] && 
                             $mail->userData['email'] === $datosFormulario['email'];

            return $destinatarioCorrecto && $datosCorrectos; 
        });
    }

    public function test_campos_del_formulario_son_obligatorios(): void
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'password'
        ]);
    }

    public function test_destinatario_debe_ser_un_email_valido(): void
    {
        $response = $this->post('/register', [
            'name' => 'Juan Pérez',
            'email' => 'correo-invalido',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_validacion_rechaza_datos_invalidos(): void
    {
        // Enviamos datos incorrectos basados en las reglas de tu controlador
        $response = $this->post('/register', [
            'name' => 'Ab', // Incorrecto: menor a 3 caracteres
            'email' => 'test@gmail.com', // Correcto
            'password' => '123', // Incorrecto: menor a 8 caracteres
            'password_confirmation' => '456' // Incorrecto: no coincide con password
        ]);

        // Verificamos que la sesión regrese con errores específicos para cada campo mal cargado
        $response->assertSessionHasErrors([
            'name',
            'password'
        ]);

        // Verificamos además que el mensaje personalizado de las contraseñas esté presente
        $response->assertSessionHasErrors([
            'password' => 'Las contraseñas ingresadas no coinciden.'
        ]);
    }
}