<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class BackupService
{
    /**
     * Jalankan mysqldump secara aman via Symfony Process.
     */
    public function runMysqldump(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        $filename = 'backup_angkringan_' . date('Y_m_d_His') . '.sql';
        $filepath = storage_path('app/' . $filename);

        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = (string) config('database.connections.mysql.port', '3306');
        $dbUser = config('database.connections.mysql.username', 'root');
        $dbPass = config('database.connections.mysql.password', '');
        $dbName = config('database.connections.mysql.database', 'angkringan_pos');

        $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (!file_exists($mysqldumpPath)) {
            $mysqldumpPath = 'mysqldump';
        }

        $command = array_filter([
            $mysqldumpPath,
            '-h', $dbHost,
            '-P', $dbPort,
            '-u', $dbUser,
            $dbPass ? '-p' . $dbPass : null,
            '--result-file=' . $filepath,
            $dbName,
        ]);

        $process = new Process($command);
        $process->setTimeout(120);
        $process->run();

        if ($process->isSuccessful() && file_exists($filepath)) {
            return response()->download($filepath)->deleteFileAfterSend(true);
        }

        return back()->withErrors(['msg' => 'Gagal membuat backup database. Error: ' . $process->getErrorOutput()]);
    }
}
