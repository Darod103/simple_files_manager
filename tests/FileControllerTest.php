<?php

declare(strict_types=1);

namespace Tests;

use App\Controller\FileController;
use App\Interface\FileStreamServiceInterface;
use App\Service\FileService;
use PHPUnit\Framework\TestCase;

class FileControllerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Service\FileService */
    private FileService $mockFileService;
    /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Interface\FileStreamServiceInterface */
    private FileStreamServiceInterface $mockFileStreamService;
    private FileController $fileController;

    protected function setUp(): void
    {
        $this->mockFileService = $this->createMock(FileService::class);
        $this->mockFileStreamService = $this->createMock(FileStreamServiceInterface::class);

        $this->fileController = new FileController(
            $this->mockFileService,
            $this->mockFileStreamService,
        );
    }

    public function testIndexDisplaysFilesTemplateWithData(): void
    {
        $expectedData = [
            'relPath' => '',
            'breadcrumbs' => [],
            'upUrl' => null,
            'items' => [
                ['name' => 'dir1', 'type' => 'dir', 'url' => '?p=dir1'],
                ['name' => 'image.png', 'type' => 'img', 'src' => '?r=stream&f=image.png'],
            ],
        ];

        $this->mockFileService->method('getDirectoryViewData')->with('')->willReturn($expectedData);

        // Mock the render method to capture its arguments
        $mockController = $this->getMockBuilder(FileController::class)
            ->setConstructorArgs([$this->mockFileService, $this->mockFileStreamService])
            ->onlyMethods(['render'])
            ->getMock();

        $mockController->expects($this->once())
            ->method('render')
            ->with('files.php', $expectedData);

        $mockController->index('');
    }

    public function testStreamStreamsFileIfPathIsValid(): void
    {
        $relFile = 'path/to/file.png';
        $filePath = '/abs/path/to/file.png';

        $this->mockFileStreamService->method('getStreamableFilePath')->with($relFile)->willReturn($filePath);
        $this->mockFileStreamService->expects($this->once())
            ->method('streamFile')
            ->with($filePath);

        $this->fileController->stream($relFile);
    }

    /**
     * @runInSeparateProcess
     */
    public function testStreamReturns404IfPathIsInvalid(): void
    {
        $relFile = 'path/to/nonexistent.png';

        $this->mockFileStreamService->method('getStreamableFilePath')->with($relFile)->willReturn(null);

        // Capture output and headers
        ob_start();
        $this->fileController->stream($relFile);
        $output = ob_get_clean();

        $this->assertEquals('Not Found', $output);
        $this->assertEquals(404, http_response_code());
    }

    /**
     * @runInSeparateProcess
     */
    public function testViewImageDisplaysImageTemplateWithData(): void
    {
        $relFile = 'path/to/image.png';
        $expectedData = [
            'fileName' => 'image.png',
            'imageUrl' => '?r=stream&f=path/to/image.png',
            'returnUrl' => '?p=path/to',
        ];

        $this->mockFileService->method('getImageDisplayData')->with($relFile)->willReturn($expectedData);

        // Mock the render method to capture its arguments
        $mockController = $this->getMockBuilder(FileController::class)
            ->setConstructorArgs([$this->mockFileService, $this->mockFileStreamService])
            ->onlyMethods(['render'])
            ->getMock();

        $mockController->expects($this->once())
            ->method('render')
            ->with('image.php', $expectedData);

        $mockController->viewImage($relFile);
    }

    /**
     * @runInSeparateProcess
     */
    public function testViewImageReturns404IfPathIsInvalid(): void
    {
        $relFile = 'path/to/nonexistent.png';

        $this->mockFileService->method('getImageDisplayData')->with($relFile)->willReturn(null);

        // Capture output and headers
        ob_start();
        $this->fileController->viewImage($relFile);
        $output = ob_get_clean();

        $this->assertEquals('Not Found', $output);
        $this->assertEquals(404, http_response_code());
    }
}
