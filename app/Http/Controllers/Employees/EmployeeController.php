<?php

namespace App\Http\Controllers\Employees;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Employees\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Employees\StoreEmployeeRequest;
use App\Http\Requests\Employees\UpdateEmployeeRequest;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees for the authenticated user's client.
     */
    public function index()
    {
        $user = Auth::user();

        // Get only employees from user's client
        $employees = Employee::with(['client', 'creator'])
            ->where('client_id', $user->Client_ID)
            ->where('is_archive', false)
            ->orderBy('surname')
            ->orderBy('first_name')
            ->paginate(25);

        return view('admin.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        return view('admin.employees.create');
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // Add client_id and created_by
            $validated['client_id'] = $user->Client_ID;
            $validated['created_by'] = $user->User_ID;

            // Extra debug: log the Employee model fillable/guarded
            Log::info('Employee model fillable/guarded', [
                'fillable' => (new Employee)->getFillable(),
                'guarded' => (new Employee)->getGuarded(),
            ]);

            // Try creating — wrap so we can log exceptions specifically for create
            try {
                $employee = Employee::create($validated);

               
            } catch (\Throwable $e) {
                // Very important: log full exception details
                Log::error('Employee::create failed', [
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                    'validated' => $validated,
                ]);
                // rethrow to trigger outer catch and rollback
                throw $e;
            }

            DB::commit();

            Log::info('Transaction committed for employee create', [
                'employee_id' => $employee->getKey() ?? null
            ]);

            // ========================================
            // AJAX/JSON Response
            // ========================================
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee created successfully!',
                    'employee' => [
                        'id' => $employee->getKey(),
                        'name' => $employee->first_name . ' ' . $employee->surname,
                        'email' => $employee->email,
                    ],
                    'redirect' => route('files.index')
                ], 201);
            }

            // ========================================
            // Regular Form Redirect
            // ========================================
            return redirect()
                ->route('files.index')
                ->with('success', 'Employee created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the outer exception too (so it shows in storage/logs/laravel.log)
            Log::error('store(StoreEmployeeRequest) exception', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            // ========================================
            // AJAX/JSON Error Response
            // ========================================
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create employee: ' . $e->getMessage(),
                    'error' => $e->getMessage()
                ], 500);
            }

            // ========================================
            // Regular Form Error Redirect
            // ========================================
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create employee: ' . $e->getMessage());
        }
    }


    /**
     * Display the specified employee.
     */
    public function show($id)
    {
        $user = Auth::user();

        $employee = Employee::with(['client', 'creator', 'updater'])
            ->where('id', $id)
            ->where('client_id', $user->Client_ID)
            ->firstOrFail();

        return view('admin.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($id)
    {
        $user = Auth::user();

        $employee = Employee::where('id', $id)
            ->where('client_id', $user->Client_ID)
            ->firstOrFail();

        return view('admin.employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(UpdateEmployeeRequest $request, $id)
    {
        $user = Auth::user();

        // Find employee and check authorization
        $employee = Employee::where('id', $id)
            ->where('client_id', $user->Client_ID)
            ->firstOrFail();

        // Get validated data from Request class
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // Add updated_by
            $validated['updated_by'] = $user->User_ID;

            // Update the employee
            $employee->update($validated);

            DB::commit();

            return redirect()
                ->route('employee.show', $employee->id)
                ->with('success', 'Employee updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update employee: ' . $e->getMessage());
        }
    }

    /**
     * Archive the specified employee.
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $employee = Employee::where('id', $id)
            ->where('client_id', $user->Client_ID)
            ->firstOrFail();

        try {
            $employee->update([
                'is_archive' => true,
                'updated_by' => $user->User_ID,
            ]);

            return redirect()
                ->route('employee.index')
                ->with('success', 'Employee archived successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to archive employee: ' . $e->getMessage());
        }
    }

    /**
     * Restore an archived employee.
     */
    public function restore($id)
    {
        $user = Auth::user();

        $employee = Employee::where('id', $id)
            ->where('client_id', $user->Client_ID)
            ->firstOrFail();

        try {
            $employee->update([
                'is_archive' => false,
                'updated_by' => $user->User_ID,
            ]);

            return redirect()
                ->route('employee.index')
                ->with('success', 'Employee restored successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to restore employee: ' . $e->getMessage());
        }
    }

    /**
     * Display archived employees.
     */
    public function archived()
    {
        $user = Auth::user();

        $employees = Employee::with(['client', 'creator'])
            ->where('client_id', $user->Client_ID)
            ->where('is_archive', true)
            ->orderBy('surname')
            ->orderBy('first_name')
            ->paginate(25);

        return view('admin.employees.archived', compact('employees'));
    }

    /**
     * Search employees.
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');

        $employees = Employee::where('client_id', $user->Client_ID)
            ->where('is_archive', false)
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('surname', 'LIKE', "%{$search}%")
                    ->orWhere('ni_number', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('works_number', 'LIKE', "%{$search}%");
            })
            ->orderBy('surname')
            ->orderBy('first_name')
            ->paginate(25);

        return view('admin.employees.index', compact('employees', 'search'));
    }
}
