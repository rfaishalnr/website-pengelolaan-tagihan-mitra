<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AkunBaruMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userBaru;
    public $rawPassword;

    // Fungsi untuk menangkap data user dari form
    public function __construct($userBaru, $rawPassword)
    {
        $this->userBaru = $userBaru; 
        $this->rawPassword = $rawPassword;
    }

    // Fungsi untuk merakit email
    public function build()
    {
        return $this->subject('Selamat Datang di Website Pengelolaan Tagihan Mitra Telkom Akses')
                    ->view('emails.new_account'); // Ini alamat file desain emailnya
    }
}