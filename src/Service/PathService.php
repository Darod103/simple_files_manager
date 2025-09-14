<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Сервис для обработки путей файлов.
 */
class PathService
{
    /**
     * Очищает относительный путь, удаляя "./", "../" и двойные слэши.
     *
     * @param string $relPath Относительный путь для очистки.
     * @return string Очищенный относительный путь.
     */
    public function sanitizeRelPath(string $relPath): string
    {
        $relPath = str_replace('\\', '/', $relPath);
        $parts = explode('/', $relPath);
        $stack = [];
        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($stack);
            } else {
                $stack[] = $part;
            }
        }
        return implode('/', $stack);
    }

    /**
     * Кодирует сегменты относительного пути для использования в URL.
     *
     * @param string $relPath Относительный путь для кодирования.
     * @return string Кодированный относительный путь.
     */
    public function encodePath(string $relPath): string
    {
        if ($relPath === '') {
            return '';
        }
        $segments = explode('/', $relPath);
        return implode('/', array_map('rawurlencode', $segments));
    }
}
