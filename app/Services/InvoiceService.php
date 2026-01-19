<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\DraftInvoice;
use App\Models\DraftInvoiceItem;
use App\Models\InvoiceDocument;
use App\Models\InvoiceActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Create invoice with polymorphic customer support
     * 
     * @param array $invoiceData
     * @param string $customerModel - Full class name (e.g., 'App\Models\File')
     * @param int $customerId - The ID of the customer (File_ID)
     * @param array $items
     * @param bool $isIssued - true = issued invoice (items go to transactions), false = draft (items go to draft_invoice_items)
     * @return Invoice
     */
    public function createInvoice(
        array $invoiceData,
        string $customerModel,
        int $customerId,
        array $items = [],
        bool $isIssued = true
    ): Invoice {
        try {
            DB::beginTransaction();

            Log::info('=== INVOICE CREATION STARTED ===', [
                'is_issued' => $isIssued,
                'item_count' => count($items),
                'status' => $isIssued ? 'sent' : 'draft',
            ]);

            // Create invoice record
            $invoice = new Invoice();
            
            // ✅ Set polymorphic relationship
            $invoice->customer = $customerId;
            $invoice->customer_type = $customerModel;
            
            // Set other fields
            $invoice->invoice_date = $invoiceData['invoice_date'];
            $invoice->due_date = $invoiceData['due_date'] ?? null;
            $invoice->invoice_no = $invoiceData['invoice_no'];
            $invoice->invoice_ref = $invoiceData['invoice_ref'] ?? null;
            $invoice->status = $isIssued ? Invoice::STATUS_SENT : Invoice::STATUS_DRAFT;
            $invoice->notes = $invoiceData['notes'] ?? null;
            
            // Financial fields
            $invoice->net_amount = $invoiceData['net_amount'] ?? null;
            $invoice->vat_amount = $invoiceData['vat_amount'] ?? null;
            $invoice->total_amount = $invoiceData['total_amount'] ?? null;
            
            // Initialize payment tracking
            $invoice->paid = 0;
            $invoice->balance = $invoice->total_amount ?? 0;

             $invoice->company_id = $invoiceData['company_id'] ?? null;
            // Set issued/created metadata
            if ($isIssued) {
                $invoice->issued_at = now();
                $invoice->issued_by = auth()->id();
            }
            $invoice->created_by = auth()->id();
            
            if (!$invoice->save()) {
                throw new \Exception('Failed to save invoice');
            }

            Log::info('Invoice record created', [
                'invoice_id' => $invoice->id,
                'status' => $invoice->status,
            ]);

            // ✅ CRITICAL: Only create DraftInvoiceItem for DRAFT invoices
            // For ISSUED invoices, items will be stored in transactions table
            if (!$isIssued && !empty($items)) {
                $this->createInvoiceItems($invoice, $items);
                Log::info('✅ DraftInvoiceItem records created (DRAFT invoice)', [
                    'invoice_id' => $invoice->id,
                    'item_count' => count($items),
                    'table' => 'draft_invoice_items'
                ]);
            } elseif ($isIssued) {
                Log::info('⏭️ Skipping DraftInvoiceItem creation (ISSUED invoice - items will go to transactions)', [
                    'invoice_id' => $invoice->id,
                    'status' => 'sent'
                ]);
            }

            // ✅ Attach documents if provided
            if (!empty($invoiceData['documents'])) {
                $this->attachDocumentsToInvoice($invoice, $invoiceData['documents']);
            }

            // ✅ Log activity
            InvoiceActivityLog::log(
                $invoice->id,
                $isIssued ? 'created' : 'drafted',
                null,
                $invoiceData,
                $isIssued ? 'Invoice issued' : 'Invoice saved as draft'
            );

            DB::commit();

            Log::info('=== INVOICE CREATION COMPLETED ===', [
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'is_issued' => $isIssued,
                'items_in_draft_table' => !$isIssued,
                'company_id' => $invoice->company_id, 
                'items_will_go_to_transactions' => $isIssued,
            ]);

            return $invoice;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update existing invoice (for editing drafts)
     * Only updates draft_invoice_items for DRAFT invoices
     */
    public function updateInvoice(
        Invoice $invoice,
        array $invoiceData,
        array $items = []
    ): Invoice {
        try {
            DB::beginTransaction();

            Log::info('=== INVOICE UPDATE STARTED ===', [
                'invoice_id' => $invoice->id,
                'current_status' => $invoice->status,
                'item_count' => count($items),
            ]);

            // Update invoice fields
            $invoice->invoice_date = $invoiceData['invoice_date'];
            $invoice->due_date = $invoiceData['due_date'] ?? null;
            $invoice->invoice_no = $invoiceData['invoice_no'];
            $invoice->invoice_ref = $invoiceData['invoice_ref'] ?? null;
            $invoice->notes = $invoiceData['notes'] ?? null;
            
            $invoice->net_amount = $invoiceData['net_amount'] ?? null;
            $invoice->vat_amount = $invoiceData['vat_amount'] ?? null;
            $invoice->total_amount = $invoiceData['total_amount'] ?? null;
            $invoice->balance = $invoice->total_amount - $invoice->paid;

            $invoice->save();

            // ✅ Only update DraftInvoiceItem if this is a DRAFT invoice
            if ($invoice->isDraft()) {
                // Delete old items and create new ones
                $invoice->items()->delete();
                if (!empty($items)) {
                    $this->createInvoiceItems($invoice, $items);
                }
                Log::info('✅ DraftInvoiceItem records updated (DRAFT invoice)', [
                    'invoice_id' => $invoice->id,
                    'new_item_count' => count($items),
                ]);
            } else {
                Log::info('⏭️ Skipping DraftInvoiceItem update (ISSUED invoice)', [
                    'invoice_id' => $invoice->id,
                    'status' => $invoice->status
                ]);
            }

            // ✅ Update documents if provided
            if (isset($invoiceData['documents'])) {
                $this->attachDocumentsToInvoice($invoice, $invoiceData['documents']);
            }

            DB::commit();

            Log::info('=== INVOICE UPDATE COMPLETED ===', [
                'invoice_id' => $invoice->id,
            ]);

            return $invoice->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Issue a draft invoice (change status from draft to sent)
     * ✅ When issuing, items stay in draft_invoice_items until transactions are created
     */
    public function issueDraftInvoice(Invoice $invoice): Invoice
    {
        if (!$invoice->isDraft()) {
            throw new \Exception('Invoice is not a draft');
        }

        Log::info('=== ISSUING DRAFT INVOICE ===', [
            'invoice_id' => $invoice->id,
            'current_items_in_draft_table' => $invoice->items()->count(),
        ]);

        // ✅ Change status from draft to sent
        $invoice->status = Invoice::STATUS_SENT;
        $invoice->issued_at = now();
        $invoice->issued_by = auth()->id();
        $invoice->save();

        InvoiceActivityLog::log(
            $invoice->id,
            'issued',
            ['status' => Invoice::STATUS_DRAFT],
            ['status' => Invoice::STATUS_SENT],
            'Draft invoice issued'
        );

        Log::info('=== DRAFT INVOICE ISSUED ===', [
            'invoice_id' => $invoice->id,
            'note' => 'Items still in draft_invoice_items until transactions created in controller'
        ]);

        return $invoice;
    }

    /**
     * Create invoice items in draft_invoice_items table
     * ✅ Only called for DRAFT invoices
     */
    private function createInvoiceItems(Invoice $invoice, array $items): void
    {
        Log::info('Creating DraftInvoiceItem records', [
            'invoice_id' => $invoice->id,
            'invoice_status' => $invoice->status,
            'item_count' => count($items)
        ]);

        foreach ($items as $index => $item) {
            // ✅ Find product if item_code exists
            $productId = null;
            if (!empty($item['item_code'])) {
                $product = \App\Models\Product::where('item_code', $item['item_code'])
                    ->where('client_id', auth()->user()->Client_ID)
                    ->first();

                if ($product) {
                    $productId = $product->id;
                }
            }

            DraftInvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $productId,
                'item_code' => $item['item_code'] ?? null,
                'description' => $item['description'],
                'chart_of_account_id' => $item['ledger_id'],
                'ledger_ref' => $item['ledger_ref'] ?? null,
                'account_ref' => $item['account_ref'] ?? null,
                'unit_amount' => $item['unit_amount'],
                'vat_rate' => $item['vat_rate'] ?? 0,
                'vat_amount' => $item['vat_amount'] ?? 0,
                'net_amount' => $item['net_amount'],
                'vat_form_label_id' => $item['vat_form_label_id'] ?? null,
                'order_index' => $index,
            ]);
        }

        Log::info('DraftInvoiceItem records created successfully', [
            'invoice_id' => $invoice->id,
            'records_created' => count($items)
        ]);
    }

    /**
     * Attach documents to invoice
     */
    private function attachDocumentsToInvoice(Invoice $invoice, $documentsData): void
    {
        $documentsData = is_string($documentsData) ? json_decode($documentsData, true) : $documentsData;

        if (!is_array($documentsData)) {
            Log::warning('Invalid documents data format');
            return;
        }

        foreach ($documentsData as $docData) {
            // Move file from temp to invoice folder
            $finalPath = $this->moveDocumentToInvoiceFolder(
                $docData['file_path'],
                $invoice->customer,
                $invoice->invoice_no
            );

            // Create document record
            InvoiceDocument::create([
                'invoice_id' => $invoice->id,
                'document_path' => $finalPath,
                'document_name' => $docData['file_name'],
                'file_type' => $docData['file_type'] ?? pathinfo($docData['file_name'], PATHINFO_EXTENSION),
                'file_size' => $docData['file_size'] ?? 0,
                'created_by' => auth()->id()
            ]);
        }
    }

    /**
     * Move document from temp to invoice-specific folder
     */
    private function moveDocumentToInvoiceFolder(string $tempPath, string $customerId, string $invoiceNo): string
    {
        try {
            $filename = basename($tempPath);
            $newPath = "invoices/{$customerId}/{$invoiceNo}/documents";

            $oldFullPath = storage_path("app/public/{$tempPath}");
            $newFullPath = storage_path("app/public/{$newPath}/{$filename}");

            if (!Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->makeDirectory($newPath, 0755, true);
            }

            if (file_exists($oldFullPath)) {
                if (!rename($oldFullPath, $newFullPath)) {
                    Log::error('Failed to move file', [
                        'from' => $oldFullPath,
                        'to' => $newFullPath
                    ]);
                    return $tempPath;
                }
            }

            return "{$newPath}/{$filename}";
        } catch (\Exception $e) {
            Log::error('Move document error: ' . $e->getMessage());
            return $tempPath;
        }
    }

    /**
     * Get customer model instance by ID
     */
    public function getCustomerById(string $modelClass, int $id)
    {
        if (!class_exists($modelClass)) {
            throw new \Exception("Customer model class not found: {$modelClass}");
        }

        $customer = $modelClass::find($id);

        if (!$customer) {
            throw new \Exception("Customer not found with ID: {$id}");
        }

        return $customer;
    }
}