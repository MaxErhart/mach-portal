<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use App\Models\Email;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class EmailController extends Controller
{

    public function store(Request $request) {
        $request->validate([
            "mail_subject"=>"required|max:255",
            "mail_body"=>"required",
        ]);
        $owner = Auth::user();
        $users = json_decode($request->get('users'), true);
        $cc = json_decode($request->get('cc'), true);
        $subject = $request->get('mail_subject');
        $body = $request->get('mail_body');
        $attachments = $request->file('attachments');

        $from = $request->get('mail_from');
        $alias = $request->get('mail_from_alias');

        $email_model = new Email();

        if(!$from) {
            $from = 'portal@mach.kit.edu';
            $alias = 'MACH-Portal';
        }
        $email_model->from_address = $from;
        $email_model->from_alias = $alias;

        $email_model->subject = $subject;

        $email_model->body = $body;

        $to_addresses = [];
        $attachments_url = [];
        foreach($users as $user) {
            $to_addresses[] = $user['email'];
            $email = new PHPMailer();
            $email->SetFrom($from,$alias);
            $email->Subject = utf8_decode($subject);
            $email->Body = utf8_decode($body);
            $email->AddAddress($user['email']);
            if($attachments) {
                foreach($attachments as $attachment) {
                    $rel_path = $attachment->store('mail_attachments');
                    $client_name = $attachment->getClientOriginalName();
                    $url = Storage::url($rel_path);
                    $attachments_url[] = ["url"=>$url,"client_name"=>$client_name];
                    $base_path = storage_path('app');
                    // return response()->json($base_path.$rel_path);

                    $email->AddAttachment($base_path."\\".$rel_path,$client_name);
                }
            }


            // $email->IsHTML(true);
            $email->addBcc("portal@mach.kit.edu");
            $email->Send();
        }

        $email_model->to_addresses = $to_addresses;
        $email_model->files = $attachments_url;
        $email_model->user()->associate($owner);
        $email_model->save();
        return response()->json($email_model);

    }

}
