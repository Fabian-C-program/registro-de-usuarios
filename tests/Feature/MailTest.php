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
        // 2. Activamos el 'Fake' de correos para interceptarlos
        Mail::fake();

        // 3. Simulamos los datos que el usuario escribiría en tu formulario
        $datosFormulario = [
            'name' => 'Juan Pérez',
            'email' => 'juan.perez.prueba@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $datosFormulario);

        $response->assertStatus(302);

        Mail::assertQueued(WelcomeUserMail::class, function ($mail) {
            return $mail->hasTo('juan.perez.prueba@gmail.com');
        });
    }
}
