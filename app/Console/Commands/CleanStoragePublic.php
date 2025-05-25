<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanStoragePublic extends Command
{
    protected $signature = 'deploy:clean-storage-public';

    protected $description = 'Limpa a pasta storage/app/public preservando arquivos e diretórios definidos';

    public function handle()
    {
        $basePath = storage_path('app/public');

        // Itens a preservar (nomes relativos ao diretório public/)
        $preservar = [
            '.gitignore',
            'demo',
        ];

        if (!File::exists($basePath)) {
            $this->warn("Diretório não encontrado: $basePath");

            return Command::SUCCESS;
        }

        $this->info("Limpando conteúdo de: $basePath");

        // Apagar arquivos não preservados
        foreach (File::files($basePath) as $file) {
            $filename = $file->getFilename();

            if (!in_array($filename, $preservar)) {
                File::delete($file->getRealPath());
                $this->line("Arquivo removido: $filename");
            }
        }

        // Apagar diretórios não preservados
        foreach (File::directories($basePath) as $dir) {
            $dirname = basename($dir);

            if (!in_array($dirname, $preservar)) {
                File::deleteDirectory($dir);
                $this->line("Diretório removido: $dirname");
            }
        }

        $this->info('Limpeza finalizada com sucesso.');

        return Command::SUCCESS;
    }
}
