<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $notificationType = 'all';
        $role = 'all';
        $query = $user->notifications();

        if ($isAdmin) {
            $typeLabels = [
                'category' => 'Category',
                'genre' => 'Genre',
                'event_type' => 'Event Type',
                'instrument' => 'Specialty',
            ];
            $requestedType = $request->query('notification_type', 'all');
            $notificationType = is_string($requestedType)
                && (array_key_exists($requestedType, $typeLabels) || $requestedType === 'all')
                    ? $requestedType
                    : 'all';

            if ($notificationType !== 'all') {
                $query->where('type', 'feature.requested')
                    ->where('title', "New {$typeLabels[$notificationType]} Request");
            }

            $requestedRole = $request->query('role', 'all');
            $role = in_array($requestedRole, ['performer', 'organizer'], true)
                ? $requestedRole
                : 'all';

            if ($role !== 'all') {
                $requestTitles = $role === 'performer'
                    ? ['New Genre Request', 'New Specialty Request']
                    : ['New Category Request', 'New Event Type Request'];
                $registrationTitle = $role === 'performer'
                    ? 'New Performer Registered'
                    : 'New Organizer Registered';

                $query->where(function ($notifications) use ($requestTitles, $registrationTitle): void {
                    $notifications
                        ->where('title', $registrationTitle)
                        ->orWhere(function ($requests) use ($requestTitles): void {
                            $requests->where('type', 'feature.requested')
                                ->whereIn('title', $requestTitles);
                        });
                });
            }
        }

        $query->latest();
        $notifications = $query->paginate(20);

        if ($isAdmin) {
            $notifications->withQueryString();
        }

        return view('notifications.index', compact('notifications', 'isAdmin', 'notificationType', 'role'));
    }

    public function markRead(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === Auth::id(), 403);
        $notification->update(['is_read' => true]);

        return $notification->link
        ? redirect($notification->link.'?from=notifications')
        : back();
    }

    public function markAllRead(): RedirectResponse
    {
        Auth::user()->notifications()->where('is_read', false)->update(['is_read' => true]);

        return back();
    }
}
