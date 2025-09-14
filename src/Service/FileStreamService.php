<?php

declare(strict_types=1);

namespace App\Service;

use App\Interface\FileStreamServiceInterface;

/**
 * Сервис для потоковой передачи файлов.
 */
class FileStreamService implements FileStreamServiceInterface
{
    private string $root;
    private PathService $paths;
    /** @var string[] */
    private array $imageExtensions;

    /**
     * Конструктор FileStreamService.
     *
     * @param string $root Абсолютный корневой путь для файлового менеджера.
     * @param PathService $paths Сервис для работы с путями.
     * @param string[] $imageExtensions Массив допустимых расширений изображений.
     */
    public function __construct(string $root, PathService $paths, array $imageExtensions)
    {
        $this->root = $root;
        $this->paths = $paths;
        $this->imageExtensions = $imageExtensions;
    }

    /**
     * Возвращает абсолютный путь к файлу, если он доступен для потоковой передачи.
     *
     * @param string $relFile Относительный путь к файлу.
     * @return string|null Абсолютный путь к файлу или null, если файл не найден или недопустим.
     */
    public function getStreamableFilePath(string $relFile): ?string
    {
        $rel = $this->paths->sanitizeRelPath($relFile);
        if ($rel === '') {
            return null;
        }

        $base = rtrim($this->root, '/');
        $candidate = $base . '/' . $rel;
        $path = realpath($candidate) ?: '';

        // Ensure the resolved path stays within base (exact or with separator)
        $inBase = ($path === $base) || str_starts_with($path, $base . '/');
        if ($path === '' || !$inBase || !is_file($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->imageExtensions, true)) {
            return null;
        }
        return $path;
    }

    /**
     * Осуществляет потоковую передачу файла в браузер.
     *
     * @param string $filePath Абсолютный путь к файлу для потоковой передачи.
     */
    public function streamFile(string $filePath): void
    {
        $mime = null;
        if (function_exists('finfo_open')) {
            $f = finfo_open(FILEINFO_MIME_TYPE);
            if ($f) {
                $mime = finfo_file($f, $filePath) ?: null;
                finfo_close($f);
            }
        }
        if ($mime === null) {
            $map = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'webp' => 'image/webp',
            ];
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mime = $map[$ext] ?? 'application/octet-stream';
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($filePath));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: public, max-age=31536000, immutable');
        readfile($filePath);
    }
}
