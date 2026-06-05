<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminVoidLogController extends Controller
{
    public function index()
    {
        $logs = DB::table('void_logs')
            ->join('users', 'void_logs.kasir_id', '=', 'users.id')
            ->select('void_logs.*', 'users.name as kasir_name')
            ->orderBy('void_logs.created_at', 'desc')
            ->paginate(15);

        return view('admin.void_logs.index', compact('logs'));
    }
}
