<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'File_ID' => 'required|exists:file,File_ID', 
             'File_Date' => 'required|date',
            'Ledger_Ref' => 'required|string|max:20',
            'Matter' => 'required|string|max:20',
            'Sub_Matter' => 'nullable|string|max:20',
            'Fee_Earner' => 'nullable|string|max:50',
            'Fee_Agreed' => 'nullable|numeric',
            'Referral_Name' => 'nullable|string|max:50',
            'Referral_Fee' => 'nullable|numeric',
            'First_Name' => 'required|string|max:50',
            'Last_Name' => 'required|string|max:50',
            'Address1' => 'required|string|max:255',
            'Address2' => 'nullable|string|max:255',
            'Town' => 'required|string|max:50',
            'Country_ID' => 'required|integer|exists:country,Country_ID',
            'Post_Code' => 'required|string|max:150',
            'Phone' => 'nullable|string|max:20',
            'Mobile' => 'nullable|string|max:20',
            'Email' => 'nullable|email|max:150',
            'Date_Of_Birth' => 'nullable|date',
            'NIC_No' => 'nullable|string|max:20',
            'Key_Date' => 'nullable|date',
            'Special_Note' => 'nullable|string|max:500',
            'Status' => 'required|string|in:L,C,A,I',
            'Created_By' => 'nullable|integer|exists:client,Client_ID',
            'Created_On' => 'nullable|date',
            'Modified_By' => 'nullable|integer|exists:client,Client_ID',
            'Modified_On' => 'nullable|date',
            'Deleted_By' => 'nullable|integer|exists:client,Client_ID',
            'Deleted_On' => 'nullable|date'
        ];
    }
}
