<?php

namespace Tests\Feature;

use App\Mail\EmailSend;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmailSendTest extends TestCase
{
    /**
     * Set of complete test fields to use for tests.
     *
     * @return array
     */
    static function getEmailFields()
    {
        $testFileName = 'emailSendTest.txt';
        $filePath = storage_path('app/tmp/'.$testFileName);
        Storage::disk('local')->put($filePath, 'This is a non-fancy text test attachment.');
        $fields = [
            'email_address'         =>  'text@example.com',
            'message'               =>  'Test message',
            'attachment_file'       =>  $filePath,
            'attachment_filename'   =>  $testFileName
        ];

        return $fields;
    }

    /**
     * Test the EmailSend Mailable
     *
     * @return void
     */
    public function test_send_mail()
    {

        $fields = static::getEmailFields();

        $mailer = new EmailSend($fields);
        $mailer->assertSeeInHtml($fields['message']);
        $mailer->assertSeeInHtml($fields['email_address']);

        Mail::fake();
        Mail::assertNothingQueued();
        Mail::to('example@example.com')->send($mailer);
        Mail::assertQueued(EmailSend::class);
        Mail::to('example@example.com')->send($mailer);
        Mail::assertQueued(EmailSend::class, 2);
    }

    /**
     * Test the endpoint to send an email
     *
     * @return void
     */
    public function test_send_http_success()
    {

        $fields = static::getEmailFields();
        //Base64 encode the file.
        $fields['attachment'] = base64_encode(file_get_contents($fields['attachment_file']));
        unset($fields['attachment_file']);

        $response = $this->postJson('/email-send', $fields);
        $response->assertStatus(200);

        //422 with missing field
        unset($fields['message']);
        $response = $this->postJson('/email-send', $fields);
        $response->assertStatus(422);

        //422 with illegal email address
        $fields['message'] = 'restoring message';
        $fields['email_address'] = 'notAnEmailAtAll';
        $response = $this->postJson('/email-send', $fields);
        $response->assertStatus(422);
    }
}
