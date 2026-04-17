<?php

namespace App\Http\Controllers;

use App\Mail\FeedbackMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
            'screenshots' => ['nullable', 'array', 'max:5'],
            'screenshots.*' => ['file', 'image', 'max:5120'], // 5 MB each
        ]);

        $recipient = config('mail.feedback_email');
        if (!$recipient) {
            Log::warning('Feedback submitted but FEEDBACK_EMAIL is not configured.');
            return back()->with('error', 'Feedback inbox is not configured. Please contact an administrator.');
        }

        $attachments = [];
        foreach ((array) $request->file('screenshots', []) as $file) {
            if (!$file || !$file->isValid()) continue;
            $attachments[] = [
                'path' => $file->getRealPath(),
                'name' => $file->getClientOriginalName() ?: 'screenshot.png',
                'mime' => $file->getMimeType() ?: 'application/octet-stream',
            ];
        }

        $user = $request->user();

        Mail::to($recipient)->send(new FeedbackMail(
            message: $data['message'],
            submitterName: $data['name'] ?? null,
            submitterEmail: $data['email'] ?? null,
            userName: $user?->name,
            userEmail: $user?->email,
            userRole: $user?->role,
            pageUrl: $request->header('referer'),
            attachmentFiles: $attachments,
        ));

        return back()->with('success', 'Thanks for the feedback!');
    }
}
