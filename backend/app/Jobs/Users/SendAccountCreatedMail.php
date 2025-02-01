<?php

namespace App\Jobs\Users;

use App\Mail\Users\ActivationEmail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAccountCreatedMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User   $user,
        public string $token
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->getAttribute('email'))
            ->send(new ActivationEmail($this->user, $this->token));
    }
}
