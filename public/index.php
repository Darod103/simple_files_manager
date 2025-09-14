<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Controller\FileController;
use App\Model\FileModel;
use App\Service\FileService;
use App\Service\FileStreamService;
use App\Service\PathService;

$absoluteRoot = realpath(ROOT);
if ($absoluteRoot === false || !is_dir($absoluteRoot)) {
    throw new InvalidArgumentException('Предоставлена недопустимая корневая директория: ' . ROOT);
}

$pathService = new PathService();
$fileModel = new FileModel($absoluteRoot, IMAGE_EXTENSIONS);
$fileStreamService = new FileStreamService($absoluteRoot, $pathService, IMAGE_EXTENSIONS);
$fileService = new FileService($fileModel, $pathService, $fileStreamService);

$controller = new FileController($fileService, $fileStreamService);
$action = filter_input(INPUT_GET, 'r', FILTER_UNSAFE_RAW) ?? '';

if ($action === 'stream') {
    $file = filter_input(INPUT_GET, 'f', FILTER_UNSAFE_RAW) ?? '';
    $controller->stream($file);
    return;
} elseif ($action === 'viewImage') {
    $file = filter_input(INPUT_GET, 'f', FILTER_UNSAFE_RAW) ?? '';
    $controller->viewImage($file);
    return;
}

$relPath = filter_input(INPUT_GET, 'p', FILTER_UNSAFE_RAW) ?? '';
$controller->index($relPath);
