<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Модель для взаимодействия с файловой системой.
 * Отвечает за получение информации о файлах и директориях.
 */
class FileModel
{
    private string $root;
    /** @var string[] */
    private array $imageExtensions;

    /**
     * Конструктор FileModel.
     *
     * @param string $root Абсолютный корневой путь, откуда будут считываться файлы.
     * @param string[] $imageExtensions Массив расширений файлов, которые считаются изображениями.
     */
    public function __construct(string $root, array $imageExtensions)
    {
        $this->root = $root;
        $this->imageExtensions = $imageExtensions;
    }

    /**
     * Получает список файлов и директорий для указанного относительного пути.
     *
     * @param string $relPath Относительный путь к директории.
     * @return array<int, array{name: string, type: string}> Массив, содержащий информацию о файлах и директориях.
     */
    public function getFiles(string $relPath): array
    {
        $root = rtrim($this->root, '/');
        $candidate = $root.'/'.ltrim($relPath, '/');

        $path = realpath($candidate);
        if ($path === false || !is_dir($path)) {
            return [];
        }

        // Keep within root (exact or subpath)
        if (!($path === $root || str_starts_with($path, $root.'/'))) {
            $path = $root;
        }

        $entries = @scandir($path) ?: [];
        $items = [];
        foreach ($entries as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $full = $path . '/' . $file;

            if (is_dir($full)) {
                $items[] = ['name' => $file, 'type' => 'dir'];
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $this->imageExtensions, true)) {
                $items[] = ['name' => $file, 'type' => 'img'];
            }
        }
        usort($items, static function (array $a, array $b): int {
            if ($a['type'] === 'dir' && $b['type'] !== 'dir') {
                return -1;
            }
            if ($a['type'] !== 'dir' && $b['type'] === 'dir') {
                return 1;
            }
            return strnatcasecmp($a['name'], $b['name']);
        });
        return $items;
    }
}
