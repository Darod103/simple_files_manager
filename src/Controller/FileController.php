<?php

declare(strict_types=1);

namespace App\Controller;

use App\Interface\FileStreamServiceInterface;
use App\Service\FileService;

/**
 * Контроллер для обработки запросов, связанных с файлами и директориями.
 */
class FileController
{
    private FileService $service;
    private FileStreamServiceInterface $streamService;

    /**
     * Конструктор FileController.
     *
     * @param FileService $service Сервис для работы с файлами.
     * @param FileStreamServiceInterface $streamService Сервис для потоковой передачи файлов.
     */
    public function __construct(FileService $service, FileStreamServiceInterface $streamService)
    {
        $this->service = $service;
        $this->streamService = $streamService;
    }

    /**
     * Отображает содержимое указанной директории.
     *
     * @param string $relPath Относительный путь к директории.
     */
    public function index(string $relPath): void
    {
        $data = $this->service->getDirectoryViewData($relPath);
        $this->render('files.php', $data);
    }

    /**
     * Рендерит указанный шаблон с предоставленными данными.
     *
     * @param string $template Имя файла шаблона.
     * @param array<string, mixed> $data Данные, передаваемые в шаблон.
     */
    protected function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../../views/' . $template;
    }

    /**
     * Потоково передает указанный файл в браузер.
     *
     * @param string $relFile Относительный путь к файлу.
     */
    public function stream(string $relFile): void
    {
        $filePath = $this->streamService->getStreamableFilePath($relFile);

        if ($filePath === null) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        $this->streamService->streamFile($filePath);
    }

    /**
     * Отображает изображение по указанному относительному пути.
     *
     * @param string $relFile Относительный путь к файлу изображения.
     */
    public function viewImage(string $relFile): void
    {
        $data = $this->service->getImageDisplayData($relFile);

        if ($data === null) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        $this->render('image.php', $data);
    }
}
