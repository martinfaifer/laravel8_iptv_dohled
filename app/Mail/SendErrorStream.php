<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendErrorStream extends Mailable
{
    use Queueable, SerializesModels;


    public $stream;
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $stream, string $url)
    {
        $this->stream = $stream;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('sendErrorStream')
            ->subject('Nefunguje - ' . $this->stream);
    }
}
