<?php

namespace App\Services\Concerns;

use App\Models\File;
use App\Models\Supplier;

trait HasCustomerContext
{
    /**
     * ✅ NEW: Detect if payment type is Purchase-related
     */
    protected function isPurchaseType(?string $paymentType): bool
    {
        return in_array($paymentType, ['purchase', 'purchase_credit']);
    }

    /**
     * ✅ UPDATED: Return Supplier for Purchase, otherwise File (or Customer in company module)
     */
    protected function getCustomerModelClass(?string $paymentType = null): string
    {
        // ✅ For Purchase transactions → Always use Supplier
        if ($this->isPurchaseType($paymentType)) {
            return Supplier::class;
        }

        // ✅ For Sales transactions → Use File (main app) or override in CompanyModule controller
        return File::class;
    }

    /**
     * ✅ UPDATED: Return correct ID field based on model
     */
    protected function getCustomerIdField(?string $paymentType = null): string
    {
        $modelClass = $this->getCustomerModelClass($paymentType);

        // Supplier and Customer use 'id', File uses 'File_ID'
        return $modelClass === File::class ? 'File_ID' : 'id';
    }

    /**
     * Validate and get customer instance
     */
    protected function validateCustomer(int $customerId, ?string $paymentType = null)
    {
        $modelClass = $this->getCustomerModelClass($paymentType);
        $idField = $this->getCustomerIdField($paymentType);

        $customer = $modelClass::where($idField, $customerId)->first();

        if (!$customer) {
            $entityType = $this->isPurchaseType($paymentType) ? 'Supplier' : 'Customer';
            throw new \Exception("{$entityType} not found");
        }

        return $customer;
    }

    /**
     * ✅ UPDATED: Pass payment type to get correct context
     */
    protected function getCustomerContextData(int $customerId, ?string $paymentType = null): array
    {
        return [
            'customer_model' => $this->getCustomerModelClass($paymentType),
            'customer_id' => $customerId,
            'customer_id_field' => $this->getCustomerIdField($paymentType),
        ];
    }
}