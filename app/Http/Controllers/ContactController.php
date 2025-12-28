<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewContactRequest;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|max:150|unique:contact_requests,email',
            'phone'         => 'required|string|max:20',
            'service'       => 'nullable|string|max:100',
            'date'          => 'nullable|date',
            'message'       => 'nullable|string|max:500',
        ]);

        // Create record
        $contact = ContactRequest::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'phone'         => $validated['phone'],
            'service'       => $validated['service'] ?? null,
            'preferred_date' => $validated['date'] ?? null,
            'message'       => $validated['message'] ?? null,
        ]);

        // Send email to admin/owner
        try {
            Mail::to(['itsroy2885@gmail.com'])
                ->send(new NewContactRequest($contact));
        } catch (\Exception $e) {
            // Log error but don't fail the request for user
            \Log::error('Failed to send contact email: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your request has been received. We will contact you soon.'
        ], 201);
    }
}