<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StationRadioMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $station;
    public $radioStreaming;
    public $embedCode;
    public $panelUrl;
    public $streamUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($station, $radioStreaming)
    {
        $this->station = $station;
        $this->radioStreaming = $radioStreaming;
        $this->embedCode = $this->generateEmbedCode();
        $this->panelUrl = "https://{$radioStreaming->host}/cp/log.php";
        $this->streamUrl = "https://{$radioStreaming->host}/{$radioStreaming->port}/stream";
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject("RDomi - Acceso Radio Streaming: {$this->station->name}")
                    ->view('emails.station-radio')
                    ->with([
                        'station' => $this->station,
                        'streaming' => $this->radioStreaming,
                        'embedCode' => $this->embedCode,
                        'panelUrl' => $this->panelUrl,
                        'streamUrl' => $this->streamUrl
                    ]);
    }

    /**
     * Generate the RDomi player embed code
     */
    private function generateEmbedCode(): string
    {
        return '<iframe src="https://rdomiplayer.com/embed/radio/' . $this->station->id . '/2" 
                style="border: none; border-radius: 12px; overflow: hidden;" 
                width="100%" height="111" 
                allow="autoplay">
                </iframe>';
    }
}
