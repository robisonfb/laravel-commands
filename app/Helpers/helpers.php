<?php

use Carbon\Carbon;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (!function_exists('store_files')) {
    /**
     * Armazena múltiplos arquivos e retorna os caminhos.
     *
     * @param \Illuminate\Http\Request|null $request Request contendo os arquivos
     * @param string $fieldName Nome do campo no request (pode ser único ou array)
     * @param string $folder Pasta onde os arquivos serão armazenados
     * @param string $disk Nome do disco de armazenamento (local, s3, etc.)
     * @param bool $returnUrl Retornar a URL completa em vez do caminho
     * @return array|string|null Caminho, URL ou array de caminhos/URLs para os arquivos armazenados
     */
    function store_files(?Request $request, string $fieldName, string $folder = 'uploads', string $disk = 'public', bool $returnUrl = false)
    {
        // Se não houver request ou arquivo, retornar null
        if (!$request || !$request->hasFile($fieldName)) {
            return null;
        }

        // Verifica se o campo contém múltiplos arquivos
        if (is_array($request->file($fieldName))) {
            return store_multiple_files($request->file($fieldName), $folder, $disk, $returnUrl);
        } else {
            return store_single_file($request->file($fieldName), $folder, $disk, $returnUrl);
        }
    }
}

if (!function_exists('store_uploaded_files')) {
    /**
     * Armazena diretamente um ou mais arquivos sem depender de um request.
     *
     * @param UploadedFile|array $files Um único arquivo ou array de arquivos
     * @param string $folder Pasta onde os arquivos serão armazenados
     * @param string $disk Nome do disco de armazenamento
     * @param bool $returnUrl Retornar a URL completa em vez do caminho
     * @return array|string|null Caminho, URL ou array de caminhos/URLs para os arquivos armazenados
     */
    function store_uploaded_files($files, string $folder = 'uploads', string $disk = 'public', bool $returnUrl = false)
    {
        if (is_array($files)) {
            return store_multiple_files($files, $folder, $disk, $returnUrl);
        } else {
            return store_single_file($files, $folder, $disk, $returnUrl);
        }
    }
}

if (!function_exists('store_multiple_files')) {
    /**
     * Armazena múltiplos arquivos.
     *
     * @param array $files Array de arquivos para armazenar
     * @param string $folder Pasta onde os arquivos serão armazenados
     * @param string $disk Nome do disco de armazenamento
     * @param bool $returnUrl Retornar a URL completa em vez do caminho
     * @return array Array de caminhos ou URLs
     */
    function store_multiple_files(array $files, string $folder, string $disk, bool $returnUrl): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $path = generate_file_path($file, $folder);

                // Armazena o arquivo no disco específico
                Storage::disk($disk)->put($path, file_get_contents($file));

                $paths[] = $returnUrl ? Storage::disk($disk)->url($path) : $path;
            }
        }

        return $paths;
    }
}

if (!function_exists('store_single_file')) {
    /**
     * Armazena um único arquivo.
     *
     * @param UploadedFile $file Arquivo para armazenar
     * @param string $folder Pasta onde o arquivo será armazenado
     * @param string $disk Nome do disco de armazenamento
     * @param bool $returnUrl Retornar a URL completa em vez do caminho
     * @return string|null Caminho ou URL do arquivo armazenado
     */
    function store_single_file(UploadedFile $file, string $folder, string $disk, bool $returnUrl): ?string
    {
        if ($file->isValid()) {
            $path = generate_file_path($file, $folder);

            // Armazena o arquivo no disco específico
            Storage::disk($disk)->put($path, file_get_contents($file));

            return $returnUrl ? Storage::disk($disk)->url($path) : $path;
        }

        return null;
    }
}

if (!function_exists('generate_file_path')) {
    /**
     * Gera um caminho de arquivo único.
     *
     * @param UploadedFile $file O arquivo enviado
     * @param string $folder A pasta de destino
     * @return string O caminho do arquivo gerado
     */
    function generate_file_path(UploadedFile $file, string $folder): string
    {
        // Gera um UUID para evitar colisões de nomes
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        return $folder . '/' . $filename;
    }
}

if (!function_exists('delete_files')) {
    /**
     * Exclui um ou mais arquivos do armazenamento.
     *
     * @param string|array $paths Caminho ou array de caminhos para excluir
     * @param string $disk Nome do disco de armazenamento
     * @return bool Se a exclusão foi bem-sucedida
     */
    function delete_files($paths, string $disk = 'public'): bool
    {
        if (is_array($paths)) {
            $result = true;

            foreach ($paths as $path) {
                if (!Storage::disk($disk)->delete($path)) {
                    $result = false;
                }
            }

            return $result;
        } else {
            return Storage::disk($disk)->delete($paths);
        }
    }
}

if (!function_exists('get_file_url')) {
    /**
     * Obtém a URL para um ou mais arquivos armazenados.
     *
     * @param string|array $paths Caminho ou array de caminhos
     * @param string $disk Nome do disco de armazenamento
     * @return string|array URL ou array de URLs
     */
    function get_file_url($paths, string $disk = 'public')
    {
        if (is_array($paths)) {
            return array_map(function ($path) use ($disk) {
                return Storage::disk($disk)->url($path);
            }, $paths);
        } else {
            return Storage::disk($disk)->url($paths);
        }
    }
}

if (!function_exists('parseDate')) {
    /**
     * Converte uma string ou DateTime para objeto Carbon.
     *
     * @param  mixed  $date
     * @return Carbon
     */
    function parseDate($date)
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        if ($date instanceof \DateTime) {
            return Carbon::instance($date);
        }

        return Carbon::parse($date);
    }
}

if (!function_exists('format_date')) {
    /**
     * Formatar data apenas com dia, mês e ano.
     *
     * @param  mixed       $date
     * @param  string      $format
     * @return string|null
     */
    function format_date($date)
    {
        if (!$date) {
            return null;
        }

        $format = env('DATE_FORMAT', 'd/m/Y');

        return parseDate($date)->format($format);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Formatar data com dia, mês, ano, hora e minuto.
     *
     * @param  mixed       $date
     * @param  string      $format
     * @return string|null
     */
    function format_datetime($date)
    {
        if (!$date) {
            return null;
        }

        $format = env('DATETIME_FORMAT', 'd/m/Y H:i');

        return parseDate($date)->format($format);
    }
}

if (!function_exists('format_time')) {
    /**
     * Formatar apenas hora e minuto.
     *
     * @param  mixed       $date
     * @param  string      $format
     * @return string|null
     */
    function format_time($date, $format = 'H:i')
    {
        if (!$date) {
            return null;
        }

        return parseDate($date)->format($format);
    }
}

if (!function_exists('format_human')) {
    /**
     * Formatar data em formato relativo (há X tempo).
     *
     * @param  mixed       $date
     * @return string|null
     */
    function format_human($date)
    {
        if (!$date) {
            return null;
        }

        return parseDate($date)->diffForHumans();
    }
}

if (!function_exists('format_date_localized')) {
    /**
     * Formatar data no formato local, como "16. März 2025".
     *
     * @param  mixed       $date
     * @param  string      $locale
     * @return string|null
     */
    function format_date_localized($date)
    {
        if (!$date) {
            return null;
        }

        $locale     = env('APP_LOCALE') . '.UTF-8';
        $carbonDate = parseDate($date);

        // Salvar o locale atual
        $currentLocale = setlocale(LC_TIME, 0);

        // Definir o locale desejado
        setlocale(LC_TIME, $locale);

        // Formatar a data
        $formattedDate = strftime('%d. %B %Y', $carbonDate->timestamp);

        // Restaurar o locale original
        setlocale(LC_TIME, $currentLocale);

        return $formattedDate;
    }
}
if (!function_exists('formatCurrency')) {
    /**
     * Formata um valor monetário com base nas configurações do ambiente.
     *
     * @param  float  $amount
     * @return string
     */

    function formatCurrency(float $amount): string
    {
        $symbol   = env('CURRENCY_SYMBOL', '€');
        $position = env('CURRENCY_POSITION', 'after');

        $formatted = number_format($amount, 2, ',', '.');

        return $position === 'before'
            ? "{$symbol} {$formatted}"
            : "{$formatted} {$symbol}";
    }
}
