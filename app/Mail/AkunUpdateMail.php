<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AkunUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $passwordBaru;

    public function __construct($user, $passwordBaru = null)
    {
        $this->user = $user;
        $this->passwordBaru = $passwordBaru;
    }

    public function build()
    {
        return $this->subject('Pemberitahuan Pembaruan Akun - Sistem Tagihan PATA')
                    ->view('emails.update_account');
    }
}