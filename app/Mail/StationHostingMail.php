<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StationHostingMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $station;
    public $hostingStation;

    /**
     * Create a new message instance.
     */
    public function __construct($station, $hostingStation)
    {
        $this->station = $station;
        $this->hostingStation = $hostingStation;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject("RDomi - Acceso cPanel: {$this->station->name}")
                    ->view('emails.station-hosting')
                    ->with([
                        'station' => $this->station,
                        'hosting' => $this->hostingStation
                    ]);
    }
}
