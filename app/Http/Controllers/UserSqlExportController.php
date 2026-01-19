<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use App\Models\User;
use App\Models\Client;
use App\Models\File;
use App\Models\Transaction;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationDetail;
use App\Models\AccountRef;
use App\Models\VatType;
use App\Models\Matter;
use App\Models\SubMatter;
use App\Models\BankAccount;


class UserSqlExportController extends Controller
{
    public function export()
    {
        $user = Auth::user();
        $client = $user->client;

        $sqlDump = "-- SQL Backup for User ID: {$user->User_ID}\n\n";

        // Export user
        $sqlDump .= $this->generateInsert('user', [$user->toArray()]) . "\n";

        // Export client
        if ($client) {
            $sqlDump .= $this->generateInsert('client', [$client->toArray()]) . "\n";
        }

        // Export files
        $files = File::where('Client_ID', $client->Client_ID)->get();
        $fileIds = $files->pluck('File_ID')->toArray();
        $sqlDump .= $this->generateInsert('file', $files->toArray()) . "\n";

        // Export transactions
        $transactions = Transaction::whereIn('File_ID', $fileIds)->get();
        $transactionIds = $transactions->pluck('Transaction_ID')->toArray();
        $sqlDump .= $this->generateInsert('transaction', $transactions->toArray()) . "\n";

        // Export related AccountRefs
        $accountRefIds = $transactions->pluck('Account_Ref_ID')->filter()->unique();
        $accountRefs = AccountRef::whereIn('Account_Ref_ID', $accountRefIds)->get();
        $sqlDump .= $this->generateInsert('accountref', $accountRefs->toArray()) . "\n";

        // Export related VatTypes
        $vatIds = $transactions->pluck('VAT_ID')->filter()->unique();
        $vatTypes = VatType::whereIn('VAT_ID', $vatIds)->get();
        $sqlDump .= $this->generateInsert('vattype', $vatTypes->toArray()) . "\n";

        // Export BankReconciliation
        $recons = BankReconciliation::where('Created_By', $user->User_ID)->get();
        $sqlDump .= $this->generateInsert('bankreconciliation', $recons->toArray()) . "\n";

        // Export BankReconciliationDetail
        $reconDetails = BankReconciliationDetail::whereIn('Transaction_ID', $transactionIds)->get();
        $sqlDump .= $this->generateInsert('bankreconciliationdetail', $reconDetails->toArray()) . "\n";

        // Fetch unique Matter names from files
        $matterNames = $files->pluck('Matter')->filter()->unique();
        $matters = Matter::whereIn('matter', $matterNames)->get();
        $sqlDump .= $this->generateInsert('matters', $matters->toArray()) . "\n";

        // Fetch Submatters linked to these Matters
        $matterIds = $matters->pluck('id');
        $submatters = SubMatter::whereIn('matter_id', $matterIds)->get();
        $sqlDump .= $this->generateInsert('submatters', $submatters->toArray()) . "\n";
        
        $bankAccounts = BankAccount::where('Client_ID', $client->Client_ID)->get();
        $sqlDump .= $this->generateInsert('bankaccount', $bankAccounts->toArray()) . "\n";


        // Download response
        $filename = 'user_backup_' . $user->User_ID . '_' . now()->format('Ymd_His') . '.sql';
        return Response::make($sqlDump, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    private function generateInsert($table, $rows)
    {
        if (empty($rows)) return "-- No data for table $table\n";

        $columns = array_keys($rows[0]);
        $columnList = '`' . implode('`, `', $columns) . '`';

        $sql = "INSERT INTO `$table` ($columnList) VALUES\n";

        $values = array_map(function ($row) {
            $escaped = array_map(function ($val) {
                if (is_array($val) || is_object($val)) {
                    // Convert array/object to JSON string (or skip with NULL)
                    $val = json_encode($val); // Optional: return "NULL" if you prefer
                }
                if ($val === null) return "NULL";
                return "'" . str_replace("'", "''", $val) . "'";
            }, $row);

            return '(' . implode(', ', $escaped) . ')';
        }, $rows);


        $sql .= implode(",\n", $values) . ";\n";
        return $sql;
    }
}
