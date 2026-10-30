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

        $zip = new ZipArchive();
        if ($zip->open($arquivoZipDestino, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

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

        return $zip->close();
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
