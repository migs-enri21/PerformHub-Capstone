<?php

namespace App\Http\Controllers;

use App\Models\Interview;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InterviewController extends Controller
{
    public function join(Interview $interview): View
    {
        $user = Auth::user();
        abort_unless(
            in_array($user->id, [$interview->organizer_id, $interview->performer_id]) || $user->isAdmin(),
            403
        );

        $interview->load(['booking', 'organizer', 'performer']);

        return view('interviews.join', compact('interview'));
    }
}
