<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendSystemWarningAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $partOfSystem;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($partOfSystem)
    {
        $this->partOfSystem = $partOfSystem;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('sendSystemWarning')
            ->subject('Warning - ' . $this->partOfSystem);
    }
}
