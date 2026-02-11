<?php

namespace App\Http\Controllers;

use App\Mail\ContactSupportMessageMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:160'],
            'order_number' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:5000'],
            // Honeypot field - should stay empty for humans.
            'website' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->filled('website')) {
            return redirect()
                ->route('contact')
                ->with('success', 'Your message has been sent. Our support team will get back to you soon.');
        }

        $to = support_email();
        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Contact form submission blocked: invalid support email.', [
                'support_email' => $to,
            ]);

            return back()
                ->withInput()
                ->with('danger', 'Contact form is temporarily unavailable. Please try again later.');
        }

        try {
            Mail::to($to)->send(new ContactSupportMessageMail([
                'name' => (string) $validated['name'],
                'email' => (string) $validated['email'],
                'subject' => (string) $validated['subject'],
                'order_number' => isset($validated['order_number']) ? (string) $validated['order_number'] : null,
                'message' => (string) $validated['message'],
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'submitted_at' => now()->toDateTimeString(),
            ]));
        } catch (\Throwable $e) {
            Log::error('Contact form email failed to send.', [
                'error' => $e->getMessage(),
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
            ]);

            return back()
                ->withInput()
                ->with('danger', 'We could not send your message right now. Please try again shortly.');
        }

        return redirect()
            ->route('contact')
            ->with('success', 'Your message has been sent. Our support team will get back to you soon.');
    }
}
