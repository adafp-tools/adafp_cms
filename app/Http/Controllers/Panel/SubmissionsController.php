<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionsController extends Controller
{
    public function index(Request $r): View
    {
        $this->authorizeSubmissions();

        $query = FormSubmission::query()->orderByDesc('created_at');

        if ($r->input('type') && in_array($r->input('type'), ['contact', 'join'])) {
            $query->where('form_type', $r->input('type'));
        }

        if ($r->input('read_status') === 'unread') {
            $query->unread();
        } elseif ($r->input('read_status') === 'read') {
            $query->read();
        }

        $submissions = $query->paginate(25)->withQueryString();
        $unreadCount = FormSubmission::unread()->count();

        return view('panel.submissions.index', compact('submissions', 'unreadCount'));
    }

    public function show(int $id): View
    {
        $this->authorizeSubmissions();

        $submission = FormSubmission::findOrFail($id);

        if (! $submission->read_at) {
            $submission->update(['read_at' => now()]);
        }

        return view('panel.submissions.show', compact('submission'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeSubmissions();

        $submission = FormSubmission::findOrFail($id);
        $submission->delete();

        return redirect()->route('panel.submissions.index')
            ->with('success', __('panel.submission_deleted'));
    }

    public function markRead(int $id): RedirectResponse
    {
        $this->authorizeSubmissions();

        $submission = FormSubmission::findOrFail($id);
        $submission->update(['read_at' => now()]);

        return back()->with('success', __('panel.submission_marked_read'));
    }

    public function export(Request $r): StreamedResponse
    {
        $this->authorizeSubmissions();

        $query = FormSubmission::query()->orderByDesc('created_at')->limit(5000);

        if ($r->input('type') && in_array($r->input('type'), ['contact', 'join'])) {
            $query->where('form_type', $r->input('type'));
        }

        $submissions = $query->get();

        return response()->streamDownload(function () use ($submissions) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'id', 'form_type', 'name', 'email', 'phone',
                'subject', 'message', 'party_role', 'dob', 'address',
                'consent', 'consent_at', 'locale', 'created_at',
            ]);

            foreach ($submissions as $s) {
                fputcsv($handle, [
                    $s->id,
                    $s->form_type,
                    $s->name,
                    $s->email,
                    $s->phone,
                    $s->subject,
                    $s->message,
                    $s->party_role,
                    $s->dob?->format('Y-m-d'),
                    $s->address,
                    $s->consent ? '1' : '0',
                    $s->consent_at?->toDateTimeString(),
                    $s->locale,
                    $s->created_at->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, 'submissions-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function purge(Request $r): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->role->canExportBackup()) {
            abort(403);
        }

        $count = FormSubmission::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(90))
            ->forceDelete();

        return redirect()->route('panel.submissions.index')
            ->with('success', __('panel.submission_purged', ['count' => $count]));
    }

    private function authorizeSubmissions(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->role->canViewSubmissions()) {
            abort(403);
        }
    }
}
