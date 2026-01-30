<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgentAdminCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $adminUser;
    public $plainPassword; // Add the plainPassword property

    /**
     * Create a new message instance.
     */
    public function __construct(User $adminUser, $plainPassword)
    {
        $this->adminUser = $adminUser;  // Assign the passed admin user data to the class property
        $this->plainPassword = $plainPassword; // Store the plain password
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Agent Admin Created: {$this->adminUser->Full_Name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'admin.emails.adminUserCreated', // Correct view path
            with: [
                'adminUserName' => $this->adminUser->email, // Passing admin user name
                'adminPassword' => $this->plainPassword, // Passing the plain password
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
