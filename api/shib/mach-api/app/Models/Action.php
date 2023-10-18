<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'targets' => 'array',
        'created_at'=>'datetime:d.m.Y H:i',
    ];

    public function get_submission_url() {
        $submission = $this->submission;
        if(count($submission)<=0) {
            return '';
        }
        // $this->submission = '<a href="https://www-3.mach.kit.edu/dist/#/submit/'.$submission[0]->form->id.'">'."Submission {$submission[0]->id} of form {$submission[0]->form->name}".'</a>';
        return '<a href="https://www-3.mach.kit.edu/dist/#/submit/'.$submission[0]->form->id.'?submission_id='.$submission[0]->id.'">'."Submission of form: {$submission[0]->form->name}".'</a>';
    }
    public function get_replies() {
        $replies = $this->replies;
        if(count($replies)<=0) {
            return '';
        }
        $return = [];
        foreach($replies as $reply) {
            $return[] = '<a href="https://www-3.mach.kit.edu/dist/#/submit/'.$reply->submission->form->id.'?submission_id='.$reply->submission->id.'#replyid'.$reply->id.'">'."Reply to {$reply->submission->owner->get_name()}'s submission of form {$reply->submission->form->name}".'</a>';
        }
        return implode('<br>',$return);

    }
    public function get_emails() {
        $emails = $this->emails;
        if(count($emails)<=0) {
            return '';
        }
        $return = [];
        foreach($emails as $email) {
            $return[] = "To: {$email->to_address}; Subject: {$email->subject}";
        }
        return implode('<br>',$return);

    }
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function form() {
        return $this->morphedByMany(Form::class, 'actionable', 'actionable_action');
    }

    public function submission() {
        return $this->morphedByMany(Submission::class, 'actionable', 'actionable_action');
    }

    public function emails() {
        return $this->morphedByMany(Email::class, 'actionable', 'actionable_action');
    }

    public function replies() {
        return $this->morphedByMany(Reply::class, 'actionable', 'actionable_action');
    }

}
