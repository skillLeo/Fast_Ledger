<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FeeEarnersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(FeeEarnerDataTable $dataTable)
    // {
    //     return $dataTable->render('admin.fee_earner.FeeEarner');
    // }
    public function index()
    {
        $userClientId = auth()->user()->Client_ID;
        $user = User::where('User_Role', 3)->where('Client_ID', $userClientId)->get();
        
        return view('admin.fee_earner.FeeEarner', compact('user'));
    }
    public function create()
    {
       return view('admin.fee_earner.createfeeearner');
    }
    public function store(Request $request)
{
    // Validate input data
    $request->validate([
        'Full_Name' => 'required|string|max:255',
        'User_Name' => 'required|string|max:255|unique:user,User_Name',
        'email' => 'required|email|unique:user,email',
        'Is_Active' => 'required|in:0,1',
        'password' => 'required|string|min:6|confirmed',
    ]);
    $userClientId = auth()->user()->Client_ID;
    
    // Create a new Fee Earner
    $User = new User();
    $User->Full_Name = $request->Full_Name;
    $User->User_Name = $request->User_Name;
    $User->Client_ID = $userClientId;
    $User->User_Role = 3;
 
    $User->email = $request->email;
    $User->Is_Active = $request->Is_Active;
    $User->password = Hash::make($request->password); 
    $User->save();

    // Redirect back with success message
    return redirect()->route('fee.earners')->with('success', 'Fee Earner added successfully.');
}
    
    public function checkactive()
    {
        $userClientId = auth()->user()->Client_ID;
        $user = User::where('User_Role', 3)->where('Client_ID', $userClientId)->where('Is_Active','=',0)->get();
        
        return view('admin.fee_earner.FeeEarner', compact('user'));
    }
    public function updatefeeernerstatus(Request $request)
{
    $userIds = $request->user_ids;

    if (!empty($userIds)) {
        User::whereIn('User_ID', $userIds)->update(['Is_Active' => 1]); // 1 = Inactive
        return response()->json(['success' => true, 'message' => 'Updated successfully']);

    }

    return response()->json(['message' => 'No users selected.'], 400);
}

    public function checkinactive()
    {
        $userClientId = auth()->user()->Client_ID;
        $user = User::where('User_Role', 3)->where('Client_ID', $userClientId)->where('Is_Active','=',1)->get();
        
        return view('admin.fee_earner.FeeEarner', compact('user'));
    }
    
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.fee_earner.editFeeEarner', compact('user'));
    }
    public function update(Request $request, $id)
{
    $request->validate([
        'Full_Name' => 'required|string|max:255',
        'User_Name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'Is_Active' => 'required|in:0,1',
        'password' => 'nullable|min:6|confirmed',
    ]);

    $user = User::findOrFail($id);
    $user->Full_Name = $request->Full_Name;
    $user->User_Name = $request->User_Name;
    $user->email = $request->email;
    $user->Is_Active = $request->Is_Active;

    if ($request->filled('password')) {
        $user->password = bcrypt($request->password);
    }

    $user->save();

    return redirect()->route('fee.earners')->with('success', 'Fee Earner updated successfully!');
}

        
}
