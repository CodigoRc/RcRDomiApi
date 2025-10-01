<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StationTvMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $station;
    public $videoStreaming;
    public $embedCode;

    /**
     * Create a new message instance.
     */
    public function __construct($station, $videoStreaming)
    {
        $this->station = $station;
        $this->videoStreaming = $videoStreaming;
        $this->embedCode = $this->generateEmbedCode();
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject("RDomi - Acceso TV Streaming: {$this->station->name}")
                    ->view('emails.station-tv')
                    ->with([
                        'station' => $this->station,
                        'streaming' => $this->videoStreaming,
                        'embedCode' => $this->embedCode
                    ]);
    }

    /**
     * Generate the RDomi TV player embed code
     */
    private function generateEmbedCode(): string
    {
        return '<iframe src="https://rdomiplayer.com/tv/player/' . $this->station->id . '" 
                width="800" height="600" 
                frameborder="0" 
                allowfullscreen>
                </iframe>';
    }
}
