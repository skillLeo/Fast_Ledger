<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class InvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $client;
    public $customerFile;
    public $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, Client $client, $customerFile = null, $pdfPath = null)
    {
        $this->invoice = $invoice;
        $this->client = $client;
        $this->customerFile = $customerFile;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice {$this->invoice->invoice_no} from {$this->client->Business_Name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'admin.emails.invoice',
            with: [
                'invoiceNo' => $this->invoice->invoice_no,
                'invoiceDate' => $this->invoice->invoice_date->format('d M Y'),
                'dueDate' => $this->invoice->due_date ? $this->invoice->due_date->format('d M Y') : 'N/A',
                'totalAmount' => number_format($this->invoice->total_amount ?? 0, 2),
                'businessName' => $this->client->Business_Name,
                'customerName' => $this->customerFile ? trim("{$this->customerFile->First_Name} {$this->customerFile->Last_Name}") : 'Valued Customer',
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
        $attachments = [];

        // Attach PDF if provided
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $attachments[] = Attachment::fromPath($this->pdfPath)
                ->as("Invoice_{$this->invoice->invoice_no}.pdf")
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}