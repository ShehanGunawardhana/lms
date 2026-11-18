<?php

namespace App\Jobs;

use App\Models\FileAsset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ScanFileAsset implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public FileAsset $fileAsset
    ) {}

    /**
     * Execute the scan job.
     */
    public function handle(): void
    {
        $disk = Storage::disk($this->fileAsset->disk);

        if (! $disk->exists($this->fileAsset->path)) {
            $this->fileAsset->update(['scan_status' => 'error']);

            return;
        }

        $contents = $disk->get($this->fileAsset->path);
        if ($contents === null) {
            $this->fileAsset->update(['scan_status' => 'error']);

            return;
        }

        // 1. Check for standard EICAR virus test signature
        $eicarSignature = 'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*';
        $isInfected = str_contains($contents, $eicarSignature);

        // 2. ClamAV daemon socket scan if available
        if (! $isInfected) {
            $clamHost = (string) config('portal.clamav.host', 'clamav');
            $clamPort = (int) config('portal.clamav.port', 3310);

            $socket = @fsockopen($clamHost, $clamPort, $errno, $errstr, 1);
            if ($socket) {
                fwrite($socket, "zINSTREAM\0");
                $streamLen = strlen($contents);
                fwrite($socket, pack('N', $streamLen));
                fwrite($socket, $contents);
                fwrite($socket, pack('N', 0));

                $response = trim((string) fgets($socket));
                fclose($socket);

                if (str_contains($response, 'FOUND')) {
                    $isInfected = true;
                }
            }
        }

        if ($isInfected) {
            // Quarantine the file
            $quarantinePath = "quarantine/{$this->fileAsset->uuid}.bin";
            $disk->move($this->fileAsset->path, $quarantinePath);

            $this->fileAsset->update([
                'scan_status' => 'infected',
                'path' => $quarantinePath,
            ]);

            audit(
                'security.file_infected',
                "Malware detected and quarantined: {$this->fileAsset->original_name}",
                $this->fileAsset,
                ['uuid' => $this->fileAsset->uuid]
            );
        } else {
            $this->fileAsset->update(['scan_status' => 'clean']);
        }
    }
}
