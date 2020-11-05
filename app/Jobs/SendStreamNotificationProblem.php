<?php

namespace App\Jobs;

use App\Mail\SendStreamNotificationProblem as MailSendStreamNotificationProblem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendStreamNotificationProblem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subject;
    protected $streamId;
    protected $streamName;
    protected $url;

    /**
     * Create a new job instance.
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return [];
    }
}
