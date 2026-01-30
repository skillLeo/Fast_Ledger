<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Country;
use App\Models\BankAccount;
use App\Models\BankAccountType;

use Illuminate\Http\Request;
use App\DataTables\ClientDataTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreClientRequest;
use App\Mail\AgentAdminCreatedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class ClientController extends Controller
{



public function index(Request $request, $type = 'active')
{
    
    
    $search = $request->input('search');
    $currentUser = auth()->user(); // Get the logged-in user
    $companyCount = $currentUser->companies()->count(); // Count companies for the authenticated user

    // If the user is an agent admin, only show the clients created by this agent
    if ($currentUser->User_Role == 3) {  // Check if the user is an Agent Admin (role 3)
        $clients = Client::with('users')
            ->where('agnt_admin_id', $currentUser->User_ID) // Filter clients created by the agent
            ->whereNull('deleted_on')
            ->when($type === 'archived', function ($query) {
                $query->where('is_archive', 1); // Show archived clients
            }, function ($query) {
                $query->where('is_archive', 0); // Show active clients
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('Client_Ref', 'like', "%$search%")
                        ->orWhere('Contact_Name', 'like', "%$search%")
                        ->orWhere('Business_Name', 'like', "%$search%");
                });
            })
            ->orderBy('client_ref')
            ->paginate(10)
            ->withQueryString(); // keeps search value in pagination
    } else {
        // For other users, show all clients (or modify as needed)
        $clients = Client::with('users')
            ->whereNull('deleted_on')
            ->when($type === 'archived', function ($query) {
                $query->where('is_archive', 1);
            }, function ($query) {
                $query->where('is_archive', 0);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('Client_Ref', 'like', "%$search%")
                        ->orWhere('Contact_Name', 'like', "%$search%")
                        ->orWhere('Business_Name', 'like', "%$search%");
                });
            })
            ->orderBy('client_ref')
            ->paginate(10)
            ->withQueryString();
    }

    // Determine which view to return based on the type (active or archived)
    $view = $type === 'archived' ? 'admin.clients.client_archieve' : 'admin.clients.client_active';

    return view($view, compact('clients', 'search', 'companyCount'));
}



    public function create()
    {
        $countries = Country::all();
        $companyTypesES  = [
    'sole_trader' => 'Sole Trader',
    'limited_company' => 'Limited Company',
];

        $companyTypesUK  = [
    'sole_trader' => 'Sole Trader',
    'limited_company' => 'Limited Company',
];

        $taxRegimes  = [
    'sole_trader' => 'Sole Trader',
    'limited_company' => 'Limited Company',
];
        // dd($countries);
        return view('admin.clients.client_create' , compact('countries','companyTypesES','companyTypesUK','taxRegimes'));
    }

      public function store(StoreClientRequest $request)
{
    // Check if the user is logged in
    if (!auth()->check()) { 
        return redirect()->route('login')->with('error', 'You must be logged in.');
    } 
    
    $agntAdminId = auth()->user()->User_ID; 
    
    // Check if the authenticated user has a valid ID
    if (is_null($agntAdminId)) { 
        return redirect()->route('login')->with('error', 'Authentication error. Please log in again.');
    }

    // Create a new client
    $client = new Client();
    $client->agnt_admin_id = $agntAdminId;
    $client->Client_Ref = $request->Client_Ref;
    
    
    $client->Company_Name = $request->Company_Name;
    $client->Trade_Name = $request->Trade_Name;
    $client->Country = $request->Country;
    $client->Company_Type_UK = $request->Company_Type_UK;
    $client->Company_Type_ES = $request->Company_Type_ES;
    $client->Tax_ID = $request->Tax_ID;
    $client->Country_Tax_Residence = $request->Country_Tax_Residence;
    $client->Tax_Regime = $request->Tax_Regime;
    $client->Street_Address = $request->Street_Address;
    $client->City = $request->City;
    $client->State = $request->State;
    $client->Postal_Code = $request->Postal_Code;
    $client->owner_Name = $request->owner_Name;
    $client->you_vat_reg = $request->you_vat_reg;
    $client->VAT_Registration_No = $request->VAT_Registration_No;
    $client->vat_scheme = $request->vat_scheme;
    $client->officially_start = $request->officially_start;
    $client->date_want_your_books = $request->date_want_your_books;
    $client->date_self_assessment_tax_ret = $request->date_self_assessment_tax_ret;
    $client->vat_return_due = $request->vat_return_due;  


    $client->Contact_Name = $request->Contact_Name;
    $client->Business_Name = $request->Business_Name;
    $client->Business_Type = $request->Business_Type;
    $client->Business_Category = $request->Business_Category;
    $client->Address1 = $request->Address1;
    $client->Address2 = $request->Address2;
    $client->Town = $request->Town;
    $client->Country_ID = $request->Country_ID;
    $client->Post_Code = $request->Post_Code;
    $client->Phone = $request->Phone;
    $client->Mobile = $request->Mobile;
    $client->Fax = $request->Fax;
    $client->Email = $request->Email;
    $client->Company_Reg_No = $request->Company_Reg_No; 
    $client->Contact_No = $request->Contact_No;
    // $client->Fee_Agreed = $request->Fee_Agreed;
    $client->snd_lgn_to_slctr = $request->has('snd_lgn_to_slctr') ? 1 : 0;
    $client->Is_Archive = 0; // 0 = not archived
    $client->Created_By = Auth::id();
    $client->date_lock = null; // ✅ correct
    $client->transaction_lock = null; // if the DB column allows null
    $client->Created_On = now();
    $client->save();

    // Store Admin User
    $adminUser = new User();
    $adminUser->Full_Name = $request->AdminUserName;
    $adminUser->User_Name = $request->AdminUserName; 

    $plainPassword = $request->AdminPassword;
     
    $adminUser->password = Hash::make($plainPassword);
    $adminUser->email = $request->Email ?? '';
    $adminUser->Is_Active = 0;
    $adminUser->Is_Archive = 0;
    $adminUser->User_Role = 2; // Client admin role
    $adminUser->Client_ID = $client->Client_ID;
    $adminUser->Created_By = Auth::id();
    $adminUser->Created_On = now();
    $adminUser->save();

    // Send the email with the plain password (not hashed)
    if ($request->snd_lgn_to_slctr == "true" && !empty($adminUser->email)) {
        Mail::to($adminUser->email)->send(new AgentAdminCreatedMail($adminUser, $plainPassword));
    }

    // Redirect back to the clients index page with a success message
    return redirect()->route('clients.index', 'active')
        ->with('success', 'Client and Admin User created successfully.');
}

    
    
   public function impersonate($id)
    {
        if (auth()->user()->User_Role != 1) { // Assuming 1 = superadmin
            abort(403, 'Unauthorized.');
        }

        $target = User::findOrFail($id);
        Session::put('impersonator_id', auth()->id());
        Auth::login($target);

        return redirect('/')->with('success', 'Now impersonating ' . $target->Full_Name);
    }
    
     // ADMIN LOGIN
    public function adminLoginAs($id)
    {
        $currentUser = auth()->user();
    
        if (!$currentUser || $currentUser->User_Role != 1) {
            abort(403, 'Unauthorized.');
        }
    
        $admin = User::findOrFail($id);
    
        if ($admin->User_Role != 2) {
            return redirect()->back()->with('error', 'Target user is not an admin.');
        }
    
        Session::put('impersonator_id', $currentUser->User_ID);
        Auth::login($admin);
    
        return redirect('/')->with('success', 'Logged in as Admin: ' . $admin->Full_Name);
    }

    
    
    
   public function showBanks(Request $request, $userId = null)
{
    $authUser = auth()->user();
    

    // Superadmin direct access
  if ($authUser->User_Role == 1 && $userId) {
    $user = User::findOrFail($userId);
    }
    elseif ($authUser->User_Role == 2 && ($userId === null || $authUser->User_ID == $userId)) {
        $user = $authUser;
    }
    else {
        abort(403, 'Unauthorized.');
    }


    if (!$user->Client_ID) {
        abort(400, 'This user is not linked to a client.');
    }

    $clientId = $user->Client_ID;
        // dd($clientId);

    $isInactive = $request->query('inactive', false);

    $banks = $this->getClientBanks(
        (int) $clientId,
        [
            config('constants.CLIENT_BANK_TYPE_ID'),
            config('constants.OFFICE_BANK_TYPE_ID')
        ],
        $isInactive
    );

    return view('admin.bank.banks', compact('user', 'banks', 'isInactive'));
}




    public function createBank($userId)
    {
        $user = User::findOrFail($userId);
        $bankTypes = BankAccountType::all();

        return view('admin.bank.create', compact('user', 'bankTypes'));
    }


    public function storeBank(Request $request)
    {

        $validated = $request->validate([
            'Client_ID' => 'required|exists:client,Client_ID',
            'Bank_Type_ID' => 'required|exists:bankaccounttype,Bank_Type_ID',
            'Bank_Name' => 'required|string|max:255',
            'Account_Name' => 'required|string|max:255',
            'Account_No' => 'required|string|max:50',
            'Sort_Code' => 'required|string|max:20',
        ]);
        BankAccount::create([
            'Client_ID'    => $validated['Client_ID'],
            'Bank_Type_ID' => $validated['Bank_Type_ID'],
            'Bank_Name'    => $validated['Bank_Name'],
            'Account_Name' => $validated['Account_Name'],
            'Account_No'   => $validated['Account_No'],
            'Sort_Code'    => $validated['Sort_Code'],
            'Is_Deleted'   => 0,
        ]);

        $clientUser = User::where('Client_ID', $validated['Client_ID'])
            ->where('User_Role', 2)
            ->first();

        if (!$clientUser) {
            return redirect()->route('clients.index')->with('error', 'Client user not found.');
        }

        return redirect()
            ->route('admin.users.banks', ['user' => $clientUser->User_ID])
            ->with('success', 'Bank account created successfully.');
    }


    public function getClientBanks(int $clientId, array|int|null $bankTypeId = null, bool $onlyInactive = false)
    {
        $query = BankAccount::query()
            ->join('bankaccounttype', 'bankaccount.Bank_Type_ID', '=', 'bankaccounttype.Bank_Type_ID')
            ->where('bankaccount.Client_ID', $clientId)
            ->where('bankaccount.Is_Deleted', $onlyInactive ? 1 : 0) // <-- NEW
            ->orderBy('bankaccount.Bank_Name');

        if (!is_null($bankTypeId)) {
            $query->whereIn('bankaccount.Bank_Type_ID', (array) $bankTypeId);
        }

        return $query->get([
            'bankaccount.Bank_Account_ID',
            'bankaccount.Bank_Name',
            'bankaccounttype.Bank_Type',
            'bankaccount.Bank_Type_ID',
            'bankaccount.Account_No',
            'bankaccount.Sort_Code',
        ])->map(fn($bank) => [
            'Bank_Account_ID'   => $bank->Bank_Account_ID,
            'Bank_Name'         => $bank->Bank_Name,
            'Bank_Type'         => $bank->Bank_Type,
            'Bank_Account_Name' => "{$bank->Bank_Name} ({$bank->Bank_Type})",
            'Bank_Type_ID'      => $bank->Bank_Type_ID,
            'Account_No'        => $bank->Account_No,
            'Sort_Code'         => $bank->Sort_Code,
        ]);
    }

    public function inactivateBanks(Request $request)
    {
        $bankIds = $request->input('bank_ids', []);
        // dd($bankIds);

        BankAccount::whereIn('Bank_Account_ID', $bankIds)
            ->update(['Is_Deleted' => 1]);

        return back()->with('success', 'Selected bank accounts marked as inactive.');
    }
    
    
      public function archive($id)
    {
        $client = Client::findOrFail($id);
        $client->Is_Archive = 1;
        $client->save();

        return back()->with('success', 'Client archived successfully.');
    }

    public function recover($id)
    {
        $client = Client::findOrFail($id);
        $client->Is_Archive = 0;
        $client->save();

        return back()->with('success', 'Client recovered successfully.');
    }
}





