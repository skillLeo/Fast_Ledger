<?php

namespace App\Models\CompanyModule;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'User_ID',
        'Company_ID',
        'Customer_Type',
        'Legal_Name_Company_Name',
        'Tax_ID_Type',
        'Tax_ID_Number',
        'Street_Address',
        'City',
        'Postal_Code',
        'Province',
        'Country',
        'Email',
        'Phone',
        'Contact_Person_Name',
        'Has_VAT',
        'VAT_Rate',
        'Has_IRPF',
        'IRPF_Rate',
        'Payment_Method',
        'IBAN',
        'Bank_Name',
    ];

    protected $casts = [
        'Has_VAT' => 'boolean',
        'Has_IRPF' => 'boolean',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    // Add relationship
    public function company()
    {
        return $this->belongsTo(\App\Models\CompanyModule\Company::class, 'Company_ID', 'id');
    }

    // Helper method to get VAT percentage
    public function getVATPercentage()
    {
        return match ($this->VAT_Rate) {
            'Standard_21' => '21%',
            'Reduced_10' => '10%',
            'Super_Reduced_4' => '4%',
            'Exempt_0' => '0%',
            'Intra_EU' => 'Reverse Charge',
            'Export' => 'Outside EU',
            default => null,
        };
    }

    // Helper method to get IRPF percentage
    public function getIRPFPercentage()
    {
        return $this->IRPF_Rate ? $this->IRPF_Rate . '%' : null;
    }
}
