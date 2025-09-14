<?php

declare(strict_types=1);

namespace App\Service;

use App\Interface\FileStreamServiceInterface;
use App\Model\FileModel;

/**
 * Сервис для управления файлами и директориями.
 */
class FileService
{
    /**
     * Конструктор FileService.
     *
     * @param FileModel $model Модель для доступа к файловой системе.
     * @param PathService $paths Сервис для работы с путями.
     * @param FileStreamServiceInterface $streamService Сервис для потоковой передачи файлов.
     */
    public function __construct(
        private readonly FileModel $model,
        private readonly PathService $paths,
        private readonly FileStreamServiceInterface $streamService,
    ) {
    }

    /**
     * Получает данные для отображения содержимого директории.
     *
     * @param string $relPathRaw Неочищенный относительный путь к директории.
     * @return array<string, mixed> Массив данных для отображения.
     */
    public function getDirectoryViewData(string $relPathRaw): array
    {
        $relPath = $this->paths->sanitizeRelPath($relPathRaw);
        $items = $this->model->getFiles($relPath);

        $breadcrumbs = $this->buildBreadcrumbs($relPath);
        $upUrl = $this->buildUpUrl($relPath);

        $viewItems = [];
        foreach ($items as $it) {
            if ($it['type'] === 'dir') {
                $next = trim(($relPath !== '' ? $relPath.'/' : '').$it['name'], '/');
                $viewItems[] = [
                    'type' => 'dir',
                    'name' => $it['name'],
                    'url' => '?p=' . $this->paths->encodePath($next),
                ];
            } else {
                $img = trim(($relPath !== '' ? $relPath.'/' : '').$it['name'], '/');
                $viewItems[] = [
                    'type' => 'img',
                    'name' => $it['name'],
                    'src' => '?r=stream&f=' . $this->paths->encodePath($img),
                ];
            }
        }

        return [
            'relPath' => $relPath,
            'breadcrumbs' => $breadcrumbs,
            'upUrl' => $upUrl,
            'items' => $viewItems,
        ];
    }

    /**
     * Получает данные для отображения изображения.
     *
     * @param string $relFile Относительный путь к файлу изображения.
     * @return array{fileName: string, imageUrl: string, returnUrl: string}|null Массив данных для отображения изображения или null, если файл не найден.
     */
    public function getImageDisplayData(string $relFile): ?array
    {
        $filePath = $this->streamService->getStreamableFilePath($relFile);

        if ($filePath === null) {
            return null;
        }

        $fileName = basename($filePath);
        $parentDir = dirname($relFile);
        $returnUrl = '?p=' . $this->paths->encodePath($parentDir);
        $imageUrl = '?r=stream&f=' . $this->paths->encodePath($relFile);

        return [
            'fileName' => $fileName,
            'imageUrl' => $imageUrl,
            'returnUrl' => $returnUrl,
        ];
    }

    /**
     * Строит хлебные крошки для указанного относительного пути.
     *
     * @param string $relPath Относительный путь.
     * @return array<int, array{name: string, url: string}> Массив хлебных крошек.
     */
    private function buildBreadcrumbs(string $relPath): array
    {
        if ($relPath === '') {
            return [];
        }
        $acc = [];
        $out = [];
        foreach (explode('/', $relPath) as $seg) {
            if ($seg === '') {
                continue;
            }
            $acc[] = $seg;
            $out[] = [
                'name' => $seg,
                'url' => '?p=' . $this->paths->encodePath(implode('/', $acc)),
            ];
        }
        return $out;
    }

    /**
     * Строит URL для перехода на уровень выше.
     *
     * @param string $relPath Относительный путь.
     * @return string|null URL для перехода на уровень выше или null, если это корневая директория.
     */
    private function buildUpUrl(string $relPath): ?string
    {
        if ($relPath === '') {
            return null;
        }
        $parts = explode('/', $relPath);
        array_pop($parts);
        $up = implode('/', $parts);
        return '?p=' . $this->paths->encodePath($up);
    }
}
