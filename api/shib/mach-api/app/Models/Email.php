<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Storage;

class Email extends Model
{
    use HasFactory;


    protected $fillable = [
        'subject',
        'body',
        'files',
        'to_addresses',
        'from_address',
        'from_alias',
        'cc',
    ];

    protected $casts = [
        'files' => 'array',
        'cc' => 'array',
        'to_addresses' => 'array',
    ];


    public function send($html=true, $bcc = array()) {
        $mailer = new PHPMailer();
        $mailer->SetFrom($this->from_address, $this->from_alias);
        $mailer->Subject = utf8_decode($this->subject);
        $mailer->Body = utf8_decode($this->body);
        foreach($this->to_addresses as $to_address) {
            $mailer->AddAddress($to_address);
            // $mailer->AddAddress("merhart@web.de");
            // abort(response()->json($to_address,403));
        }
        if(is_array($this->files)) {
            foreach($this->files as $file) {
                $path = implode('\\',[Storage::disk($file['disk'])->path(''),$file['fragment']]);
                $mailer->AddAttachment($path,$file["name"]);
            }
        }
        if(count($bcc)>0) {
            foreach($bcc as $mail) {
                $mailer->addBCC($mail["address"], $mail["alias"]);
            }
        }
        if($html) {
            $mailer->IsHTML(true);
        }
        $mailer->Send();
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function actions()
    {
        return $this->morphToMany(Action::class, 'actionable', 'actionable_action');
    }

    // public function to_users() {
    //     return $this->morphedByMany(User::class, 'recipient', 'email_recipient');
    // }

    // public function to_groups() {
    //     return $this->morphedByMany(Group::class, 'recipient', 'email_recipient');
    // }

}
