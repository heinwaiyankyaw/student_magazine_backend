<?php

namespace App\Http\Helpers;

use App\Models\TransactionLog;
use Illuminate\Support\Facades\Auth;

class TransactionLogger
{
    /**
     * Log transactions for auditing.
     */
    public static function log(string $table, string $action, bool $success, string $message)
    {
        $ipAddress = request()->header('X-Forwarded-For') ?? request()->ip();

        TransactionLog::create([
            'table_name' => $table,
            'action_type' => $action,
            'user_id' => Auth::id() ?? null,
            'ip_address' => $ipAddress,
            'user_agent' => request()->header('User-Agent'),
            'success' => $success,
            'message' => $message,
        ]);
    }
}
