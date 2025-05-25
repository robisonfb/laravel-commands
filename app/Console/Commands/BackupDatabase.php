<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\{File};

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Cria um backup do banco de dados MySQL e armazena na pasta storage/app/private';

    public function handle()
    {
        $db = config('database.connections.mysql');

        $host     = $db['host'];
        $port     = $db['port'];
        $database = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $timestamp = now()->format('Ymd_His');
        $filename  = "backup_{$database}_{$timestamp}.sql";
        $filepath  = storage_path("app/private/{$filename}");
        $zipPath   = "{$filepath}.zip";

        // Garante que o diretório private existe
        File::ensureDirectoryExists(storage_path('app/private'));

        // Monta o comando mysqldump
        $dumpCommand = sprintf(
            'mysqldump -h%s -P%s -u%s -p%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        // Executa o dump
        $this->info("Executando backup do banco de dados: {$database}");
        $result = null;
        system($dumpCommand, $result);

        if ($result !== 0 || !File::exists($filepath)) {
            $this->error('Erro ao gerar o backup do banco.');

            return Command::FAILURE;
        }

        // Compacta o arquivo .sql em .zip
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            $zip->addFile($filepath, $filename);
            $zip->close();
            File::delete($filepath); // remove .sql original
            $this->info("Backup salvo e compactado em: {$zipPath}");
        } else {
            $this->warn('Falha ao compactar o backup, mantendo arquivo .sql');
        }

        return Command::SUCCESS;
    }
}
