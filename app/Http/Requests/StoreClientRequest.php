<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
    public function rules()
    {
        return [
            'Client_Ref' => 'required|string|max:255',
            'Contact_Name' => 'required|string|max:255',
            'Business_Name' => 'required|string|max:255',
            'Address1' => 'required|string|max:255',
            'Address2' => 'nullable|string|max:255',
            'Town' => 'required|string|max:255',
            'Country_ID' => 'required|integer|exists:country,Country_ID',
            'Post_Code' => 'required|string|max:255',
            'Phone' => 'required|string|max:20',
            'Mobile' => 'nullable|string|max:20',
            'Fax' => 'nullable|string|max:20',
            'Email' => 'nullable|email|max:255',
            'Company_Reg_No' => 'nullable|string|max:255',
            'VAT_Registration_No' => 'nullable|string|max:255',
            'Contact_No' => 'nullable|string|max:255',
            'Fee_Agreed' => 'nullable|numeric',
            'date_lock' => 'nullable|date',
            'transaction_lock' => 'nullable|date',


            'AdminUserName' => 'required|string|max:255',
            'AdminPassword' => 'required|string|min:6',
        ];
    }


    public function messages()
    {
        return [
            'Client_Ref.required' => 'The Client Ref# is required.',
            'Country_ID.required' => 'The Country is required.',
            'Post_Code.required' => 'The Post Code is required.',
            'AdminUserName.required' => 'The Admin User Name is required.',
            'AdminPassword.required' => 'The Admin Password is required.',
        ];
    }
}
