<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Pengajuan;

class StatusBerkasMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;

    public function __construct(Pengajuan $pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }

    public function build()
    {
        // Subjek email dinamis menyesuaikan status
        $statusText = $this->pengajuan->status === 'acc' ? 'DITERIMA' : 'DITOLAK';
        
        return $this->subject("Status Pengajuan Berkas [{$statusText}] - {$this->pengajuan->nomor_sp}")
                    ->view('emails.status_berkas');
    }
}