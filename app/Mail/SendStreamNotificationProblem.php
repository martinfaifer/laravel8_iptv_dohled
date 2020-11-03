<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendStreamNotificationProblem extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $streamId;
    public $streamName;
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $streamId, $streamName, $url)
    {
        $this->subject = $subject;
        $this->streamId = $streamId;
        $this->streamName = $streamName;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('sendStreamProblem')
            ->subject('Stream ' . $this->streamName . ' má problém s ' . $this->subject);
    }
}
