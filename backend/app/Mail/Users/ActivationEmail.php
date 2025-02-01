<?php

namespace App\Mail\Users;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivationEmail extends Mailable
{
    use Queueable, SerializesModels;


    public function __construct(
        public User   $user,
        public string $token,
    )
    {
    }

    public function build(): ActivationEmail
    {
        return $this->subject('Activate Your Account')
            ->view('emails.users.activation')
            ->with([
                'activationUrl' => url("/activate/{$this->token}"),
            ]);
    }
}
