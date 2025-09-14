<?php

declare(strict_types=1);

namespace App\Interface;

interface FileStreamServiceInterface
{
    public function getStreamableFilePath(string $relFile): ?string;
    public function streamFile(string $filePath): void;
}
