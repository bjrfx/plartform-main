<?php

namespace App\Mail\Users;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreatedEmail extends Mailable
{
    use Queueable, SerializesModels;


    public function __construct(
        public User   $user,
        public string $token,
    )
    {
    }

    public function build(): CreatedEmail
    {
        /**
         * if account created without password - email that account was created and link to set password (like password reset)
         * if account created with password - email that an account was created with password that shared during creation, if you don't remember that password you can click on password reset
         *
         * Need to make a blade template and subject accordingly
         */
        return $this->subject('Activate Your Account')
            ->view('emails.users.activation')
            ->with([
                'setPasswordUrl' => url("/set-password/{$this->token}"),
            ]);
    }
}
