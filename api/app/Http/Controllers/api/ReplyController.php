<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reply;
use App\Models\Email;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;


class ReplyController extends Controller
{

    private function replace_placeholders($string, $submission) {
        $placeholders = [];
        foreach($submission->form_elements as $form_element) {
            $placeholders["%INPUT.{$form_element->data['label']}%"] = json_decode($form_element->pivot->data);
        }
        foreach($submission->owner->toArray() as $key=>$value) {
            $placeholders["%OWNER.{$key}%"] = $value;
        }
        foreach($submission->form->toArray() as $key=>$value) {
            $placeholders["%FORM.{$key}%"] = $value;
        }
        foreach($placeholders as $placeholder=>$value) {
            $string = str_replace($placeholder, json_encode($value), $string);
        }
        return $string;
    }

    private function sendMailToSubmissionOwner($reply, $submission,$user) {
        $users = $submission->get_list_of_owners();
        $content = "
            <div style=\"border-bottom: 1px solid black;\">
            Dear {$submission->owner->name},<br>
            you have a new reply to a submission of the MACH-Portal form: <b>{$submission->form->name}</b>.<br>
            You can visit your submission under: <a href=\"https://www-3.mach.kit.edu/dist/#/submit/196/submit/$submission->id\">Submission $submission->id</a>.<br>
            The contents of the reply are also shown below.<br>
            Kind regards<br><br><br>
            </div>
            <div style=\"font-weight:bold;\">$reply->subject</div><br>
            $reply->content<br>
        ";
        $mail = new Email([
            'subject'=>$reply->subject,
            'body'=>$content,
            'files'=>$reply->files,
            'to_addresses'=>$users->pluck('email'),
            'from_address'=>'portal@mach.kit.edu',
            'from_alias'=>'MACH-Portal',
        ]);
        $mail->user()->associate($user);
        $mail->save();
        $mail->send();
    }

    public function replyToSubmission(Request $request, $submission_id) {
        $user = Auth::user();
        $submission = Submission::findOrFail($submission_id);


        if($request->get("reply_subject")===NULL || $request->get("reply_subject")==='') {
            abort(response()->json(
                ['elements'=>[
                    ['status'=>400,
                    'message'=>"Field required",
                    'id'=>'reply_subject',]
                ],
                'message'=>'Invalid inputs']
            ,400));
        }

        $attachments = $request->file('reply_attachments');
        $files = [];
        if($attachments != NULL) {
            foreach($attachments as $attachment) {
                $path = $attachment->store("replies/{$submission->form->id}", 'dfiles');
                $url = URL::signedRoute(
                    'file_hosting', ['fragment'=>$path]
                );
                $files[] = ["url"=>$url, 'disk'=>'dfiles', 'fragment'=>$path, "name"=>$attachment->getClientOriginalName()];
            }
        }

        $subject = $this->replace_placeholders($request->get('reply_subject'), $submission);
        $content = $this->replace_placeholders($request->get('reply_content'), $submission);
        $new_reply = new Reply([
            "subject"=>$subject,
            "content"=>$content,
            "files"=>$files,
            "seen"=>[$user->id],
        ]);
        $new_reply->user()->associate($user);
        $new_reply->submission()->associate($submission);
        $new_reply->save();
        if(filter_var($request->get("send_mail"), FILTER_VALIDATE_BOOLEAN)) {
            $this->sendMailToSubmissionOwner($new_reply,$submission,$user);
        }
        return response()->json($new_reply);

    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $submission = Submission::findOrFail($request->get('submission_id'));
        $attachments = $request->file('reply_attachments');

        $files = [];
        if($attachments != NULL) {
            foreach($attachments as $attachment) {
                $path = $attachment->store("replies/{$submission->form->id}", 'dfiles');
                $url = URL::signedRoute(
                    'file_hosting', ['fragment'=>$path]
                );
                $files[] = ["url"=>$url, 'disk'=>'dfiles', 'fragment'=>$path, "name"=>$attachment->getClientOriginalName()];
            }
        }

        if($request->get("reply_subject")===NULL || $request->get("reply_subject")==='') {
            abort(response()->json(
                ['elements'=>[
                    ['status'=>400,
                    'message'=>"Field required",
                    'id'=>'reply_subject',]
                ],
                'message'=>'Invalid inputs']
            ,400));
        }

        $subject = $this->replace_placeholders($request->get('reply_subject'), $submission);
        $content = $this->replace_placeholders($request->get('reply_content'), $submission);
        $new_reply = new Reply([
            "subject"=>$subject,
            "content"=>$content,
            "files"=>$files,
            "seen"=>[$user->id],
        ]);
        $new_reply->user()->associate($user);
        $new_reply->submission()->associate($submission);
        $new_reply->save();
        if(filter_var($request->get("send_mail"), FILTER_VALIDATE_BOOLEAN)) {
            $this->sendMailToSubmissionOwner($new_reply,$submission,$user);
        }
        return response()->json($new_reply);
    }
}

