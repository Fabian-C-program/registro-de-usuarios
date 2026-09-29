<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // 1. Importar la interfaz oficial
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable implements ShouldQueue // 2. Implementar ShouldQueue
{

    use Queueable, SerializesModels;

    public array $userData;

    // Número de reintentos en caso de error en el SMTP

    public $tries = 3;

    public function __construct(array $userData)
    {
        $this->userData = $userData;
    }

    public function build(): self
    {
        return $this->subject('¡Bienvenido a nuestra plataforma!')
                    ->view('emails.welcome')
                    ->with(['nombre' => $this->userData['name']]);
    }
}