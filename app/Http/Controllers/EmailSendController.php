<?php

namespace App\Http\Controllers;

use App\Mail\EmailSend;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\assertTrue;

class EmailSendController extends Controller
{
    public function send(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        $rules =  [
            'email_address' => 'required|email',
            'message'       => 'required'
        ];

        $validator = Validator::make($data, $rules);

        if (!$validator->passes()) {
            abort(422, $validator->errors()->toJson());
        }

        $mailParams = [
            'email_address' => $data['email_address'],
            'message'       => $data['message'],
        ];

        if (!empty($data['attachment'])) {
            $tmpFileName = storage_path('app/tmp').'/'.uniqid().'.file';
            $fileContents = base64_decode($data['attachment']);
            file_put_contents($tmpFileName, $fileContents);
            $mailParams['attachment_file'] = $tmpFileName;
            if (!empty($data['attachment_filename'])) {
                $mailParams['attachment_filename'] = $data['attachment_filename'];
            }
        }

        Mail::to('example@example.com')->send(new EmailSend($mailParams));

        //Clean up tmp file
        if (!empty($mailParams['attachment_file'])) {
            unlink($mailParams['attachment_file']);
        }

        //Log it.
        EmailLog::create([
            'sender'            =>  $mailParams['email_address'],
            'message'           =>  $mailParams['message']
            ]);

        return response()->json(['status'=>'success']);
    }

    /**
     * endpoint for retrieving all emails sent
     */
    public function report()
    {
        return response()->json(EmailLog::all());
    }
}
