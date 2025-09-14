<?php

declare(strict_types=1);

namespace Tests;

use App\Interface\FileStreamServiceInterface;
use App\Model\FileModel;
use App\Service\FileService;
use App\Service\PathService;
use PHPUnit\Framework\TestCase;

class FileServiceTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Model\FileModel */
    private FileModel $mockFileModel;
    /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Service\PathService */
    private PathService $mockPathService;
    /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Interface\FileStreamServiceInterface */
    private FileStreamServiceInterface $mockFileStreamService;
    private FileService $fileService;

    protected function setUp(): void
    {
        $this->mockFileModel = $this->createMock(FileModel::class);
        $this->mockPathService = $this->createMock(PathService::class);
        $this->mockFileStreamService = $this->createMock(FileStreamServiceInterface::class);

        $this->fileService = new FileService(
            $this->mockFileModel,
            $this->mockPathService,
            $this->mockFileStreamService,
        );
    }

    public function testGetDirectoryViewDataForRoot(): void
    {
        $this->mockPathService->method('sanitizeRelPath')->willReturn('');
        $this->mockFileModel->method('getFiles')->willReturn([
            ['name' => 'dir1', 'type' => 'dir'],
            ['name' => 'image.png', 'type' => 'img'],
        ]);
        $this->mockPathService->method('encodePath')->will(
            $this->returnCallback(function (string $path) {
                $segments = explode('/', $path);
                return implode('/', array_map('rawurlencode', $segments));
            }),
        );

        $data = $this->fileService->getDirectoryViewData('');

        $this->assertArrayHasKey('relPath', $data);
        $this->assertEquals('', $data['relPath']);

        $this->assertArrayHasKey('breadcrumbs', $data);
        $this->assertEmpty($data['breadcrumbs']);

        $this->assertArrayHasKey('upUrl', $data);
        $this->assertNull($data['upUrl']);

        $this->assertArrayHasKey('items', $data);
        $this->assertCount(2, $data['items']);

        $this->assertEquals('dir1', $data['items'][0]['name']);
        $this->assertEquals('dir', $data['items'][0]['type']);
        $this->assertEquals('?p=dir1', $data['items'][0]['url']);

        $this->assertEquals('image.png', $data['items'][1]['name']);
        $this->assertEquals('img', $data['items'][1]['type']);
        $this->assertEquals('?r=stream&f=image.png', $data['items'][1]['src']);
    }

    public function testGetDirectoryViewDataForSubdirectory(): void
    {
        $this->mockPathService->method('sanitizeRelPath')->willReturn('subdir');
        $this->mockFileModel->method('getFiles')->willReturn([
            ['name' => 'sub_image.jpg', 'type' => 'img'],
        ]);
        $this->mockPathService->method('encodePath')->will(
            $this->returnCallback(function (string $path) {
                $segments = explode('/', $path);
                return implode('/', array_map('rawurlencode', $segments));
            }),
        );

        $data = $this->fileService->getDirectoryViewData('subdir');

        $this->assertArrayHasKey('relPath', $data);
        $this->assertEquals('subdir', $data['relPath']);

        $this->assertArrayHasKey('breadcrumbs', $data);
        $this->assertCount(1, $data['breadcrumbs']);
        $this->assertEquals('subdir', $data['breadcrumbs'][0]['name']);
        $this->assertEquals('?p=subdir', $data['breadcrumbs'][0]['url']);

        $this->assertArrayHasKey('upUrl', $data);
        $this->assertEquals('?p=', $data['upUrl']); // Parent of 'subdir' is root

        $this->assertArrayHasKey('items', $data);
        $this->assertCount(1, $data['items']);
        $this->assertEquals('sub_image.jpg', $data['items'][0]['name']);
        $this->assertEquals('img', $data['items'][0]['type']);
        $this->assertEquals('?r=stream&f=subdir/sub_image.jpg', $data['items'][0]['src']);
    }

    public function testGetImageDisplayDataReturnsCorrectData(): void
    {
        $relFile = 'path/to/image.png';
        $filePath = '/abs/path/to/image.png';

        $this->mockFileStreamService->method('getStreamableFilePath')->with($relFile)->willReturn($filePath);
        $this->mockPathService->method('encodePath')->will(
            $this->returnCallback(function (string $path) {
                $segments = explode('/', $path);
                return implode('/', array_map('rawurlencode', $segments));
            }),
        );

        $data = $this->fileService->getImageDisplayData($relFile);

        $this->assertNotNull($data);
        $this->assertEquals('image.png', $data['fileName']);
        $this->assertEquals('?r=stream&f=path/to/image.png', $data['imageUrl']);
        $this->assertEquals('?p=path/to', $data['returnUrl']);
    }

    public function testGetImageDisplayDataReturnsNullForInvalidFile(): void
    {
        $relFile = 'path/to/nonexistent.jpg';
        $this->mockFileStreamService->method('getStreamableFilePath')->with($relFile)->willReturn(null);

        $data = $this->fileService->getImageDisplayData($relFile);
        $this->assertNull($data);
    }
}
