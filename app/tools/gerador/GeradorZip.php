<?php

namespace app\tools\gerador;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class GeradorZip {
    
    public function compactarPasta(string $pastaOrigem, string $arquivoZipDestino): bool {
        if (!file_exists($pastaOrigem)) {
            return false;
        }

        if (file_exists($arquivoZipDestino)) {
            unlink($arquivoZipDestino);
        }

        if (class_exists('ZipArchive', false) || class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($arquivoZipDestino, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $realPath = realpath($pastaOrigem);
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($realPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($realPath) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }

                if ($zip->close()) {
                    return true;
                }
            }
        }

        // Fallback nativo: utiliza o utilitário nativo tar (bsdtar) do Windows ou PowerShell Compress-Archive
        return $this->compactarFallback($pastaOrigem, $arquivoZipDestino);
    }

    private function compactarFallback(string $pastaOrigem, string $arquivoZipDestino): bool {
        $realPath = realpath($pastaOrigem);
        if (!$realPath) {
            return false;
        }

        // 1. Tenta usar o comando tar nativo do Windows (disponível no Windows 10/11)
        $cmdTar = sprintf('tar -a -c -f %s -C %s .', escapeshellarg($arquivoZipDestino), escapeshellarg($realPath));
        @exec($cmdTar, $outputTar, $codeTar);
        if ($codeTar === 0 && file_exists($arquivoZipDestino) && filesize($arquivoZipDestino) > 0) {
            return true;
        }

        // 2. Tenta usar PowerShell Compress-Archive como segundo fallback seguro
        $cmdPs = sprintf(
            'powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Compress-Archive -Path \'%s\\*\' -DestinationPath \'%s\' -Force"',
            rtrim($realPath, '\\/'),
            $arquivoZipDestino
        );
        @exec($cmdPs, $outputPs, $codePs);
        if ($codePs === 0 && file_exists($arquivoZipDestino) && filesize($arquivoZipDestino) > 0) {
            return true;
        }

        return false;
    }

    public function enviarDownload(string $caminhoZip, string $nomeArquivoDownload = 'projeto_mvc.zip'): void {
        if (!file_exists($caminhoZip)) {
            http_response_code(404);
            echo "Arquivo para download não encontrado.";
            return;
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $nomeArquivoDownload . '"');
        header('Content-Length: ' . filesize($caminhoZip));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($caminhoZip);
        exit();
    }
}
