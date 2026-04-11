<?php

namespace App\Observers;

use App\Mail\StudentInboxMail;
use App\Models\Inbox;
use Illuminate\Support\Facades\Mail;

class InboxObserver
{
    public function created(Inbox $inbox): void
    {
        $inbox->loadMissing('student');

        $email = $inbox->student?->email;

        if ($email) {
            Mail::to($email)->send(new StudentInboxMail($inbox));
        }
    }
}
