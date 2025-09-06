<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CertificateService
{
    private string $tempDir;
    private string $uniqueId;

    public function __construct()
    {
        $this->tempDir = sys_get_temp_dir();
    }

    public function process(UploadedFile $file, string $password): array
    {
        if (!$file->isValid()) {
            throw new Exception('Invalid file upload.');
        }

        $allowedExtensions = ['pfx', 'p12'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Only .pfx and .p12 are allowed.');
        }

        $this->uniqueId = uniqid();
        $tempPfxPath = $this->saveTemporaryFile($file);
        
        try {
            $publicKey = $this->extractPublicKey($tempPfxPath, $password);
            $privateKey = $this->extractPrivateKey($tempPfxPath, $password);
            $fullCert = $this->combineCertificates($publicKey, $privateKey);
            
            return $this->saveAndReturnData($publicKey, $privateKey, $fullCert);
        } finally {
            // Clean up temporary files
            @unlink($tempPfxPath);
        }
    }

    private function saveTemporaryFile(UploadedFile $file): string
    {
        $tempPath = $this->tempDir . '/' . $this->uniqueId . '.pfx';
        file_put_contents($tempPath, file_get_contents($file->getRealPath()));
        return $tempPath;
    }

    private function extractPublicKey(string $pfxPath, string $password): string
    {
        $outputPath = $this->tempDir . '/' . $this->uniqueId . '_public.pem';
        $command = sprintf(
            'openssl pkcs12 -provider legacy -provider default -in %s -out %s -clcerts -nokeys -nodes -passin pass:"%s"',
            escapeshellarg($pfxPath),
            escapeshellarg($outputPath),
            addslashes($password)
        );

        $this->executeCommand($command);
        $publicKey = file_get_contents($outputPath);
        @unlink($outputPath);
        return $publicKey;
    }

    private function extractPrivateKey(string $pfxPath, string $password): string
    {
        $outputPath = $this->tempDir . '/' . $this->uniqueId . '_private.pem';
        $command = sprintf(
            'openssl pkcs12 -provider legacy -provider default -in %s -out %s -nocerts -nodes -passin pass:"%s"',
            escapeshellarg($pfxPath),
            escapeshellarg($outputPath),
            addslashes($password)
        );

        $this->executeCommand($command);
        $privateKey = file_get_contents($outputPath);
        @unlink($outputPath);
        return $privateKey;
    }

    private function combineCertificates(string $publicKey, string $privateKey): string
    {
        return $publicKey . $privateKey;
    }

    private function executeCommand(string $command): void
    {
        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private function saveAndReturnData(string $publicKey, string $privateKey, string $fullCert): array
    {
        $pemDir = "pem/{$this->uniqueId}";
        Storage::disk('local')->makeDirectory($pemDir);

        Storage::disk('local')->put("{$pemDir}/certificate.pem", $fullCert);
        Storage::disk('local')->put("{$pemDir}/public.pem", $publicKey);
        Storage::disk('local')->put("{$pemDir}/private.pem", $privateKey);

        $tempCertFile = $this->tempDir . '/' . $this->uniqueId . '_temp_cert.pem';
        file_put_contents($tempCertFile, $publicKey);
        
        try {
            $certData = openssl_x509_parse($publicKey);
            if ($certData === false) {
                throw new Exception('Could not parse certificate data after extraction.');
            }
            $validTo = date('d/m/Y', $certData['validTo_time_t']);

            return [
                'id' => $this->uniqueId,
                'validTo' => $validTo,
                'details' => $certData,
            ];
        } finally {
            @unlink($tempCertFile);
        }
    }
}