<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends Notification
{
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $logoUrl = app()->environment('production')
            ? 'https://aeddi-front.onrender.com/aeddi.png'
            : url('aeddi.png');
        return (new MailMessage)
            ->subject('Réinitialisation du mot de passe')
            ->view('emails.reset-password', [
                'url' => $this->url,
                'logoUrl' => $logoUrl,
            ]);
    }
} 