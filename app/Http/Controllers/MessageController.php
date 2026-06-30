<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();
        $conversations = Message::query()
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver'])
            ->latest()
            ->get()
            ->groupBy(fn ($m) => $m->sender_id === $userId ? $m->receiver_id : $m->sender_id);

        return view('messages.index', compact('conversations'));
    }

    public function show(User $user): View
    {
        $messages = Message::query()
            ->where(function ($q) use ($user) {
                $q->where('sender_id', Auth::id())->where('receiver_id', $user->id);
            })
            ->orWhere(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->where('receiver_id', Auth::id());
            })
            ->with(['sender', 'receiver'])
            ->oldest()
            ->get();

        Message::where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->update(['is_read' => true]);

        return view('messages.show', compact('user', 'messages'));
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
        ]);

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $user->id,
            'booking_id' => $validated['booking_id'] ?? null,
            'message' => $validated['message'],
        ]);

        Notification::send(
            $user,
            'message',
            'New Message',
            Auth::user()->name.' sent you a message',
            route('messages.show', Auth::user())
        );

        return back()->with('success', 'Message sent.');
    }
}
