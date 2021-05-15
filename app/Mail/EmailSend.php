<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailSend extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    var $mailData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->mailData = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mailer = $this->from('example@example.com')
            ->view('emails.email-send');

        //If there's an attachment, assign.
        if (isset($this->mailData['attachment_file']) && file_exists($this->mailData['attachment_file'])) {
            $filename = $this->mailData['attachment_filename'] ? $this->mailData['attachment_filename'] : basename($this->mailData['attachment_file']);
            $mime = mime_content_type($this->mailData['attachment_file']);
            $mailer->attach($this->mailData['attachment_file'], ['as'=>$filename, 'mime'=>$mime]);
        }
    }
}
