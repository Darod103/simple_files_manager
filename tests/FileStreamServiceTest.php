<?php

declare(strict_types=1);

namespace Tests;

use App\Service\FileStreamService;
use App\Service\PathService;
use PHPUnit\Framework\TestCase;

class FileStreamServiceTest extends TestCase
{
    private string $testRoot;
    private PathService $pathService;
    private array $imageExtensions = ['jpg', 'png'];

    protected function setUp(): void
    {
        $this->testRoot = __DIR__ . '/temp_stream_root';
        if (!is_dir($this->testRoot)) {
            mkdir($this->testRoot, 0777, true);
        }

        $this->pathService = new PathService();

        file_put_contents($this->testRoot . '/test.png', 'image content');
        file_put_contents($this->testRoot . '/document.pdf', 'pdf content');
        mkdir($this->testRoot . '/subdir');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->testRoot);
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    public function testGetStreamableFilePathReturnsPathForValidImage(): void
    {
        $service = new FileStreamService($this->testRoot, $this->pathService, $this->imageExtensions);
        $filePath = $service->getStreamableFilePath('test.png');
        $this->assertEquals($this->testRoot . '/test.png', $filePath);
    }

    public function testGetStreamableFilePathReturnsNullForNonImageFile(): void
    {
        $service = new FileStreamService($this->testRoot, $this->pathService, $this->imageExtensions);
        $filePath = $service->getStreamableFilePath('document.pdf');
        $this->assertNull($filePath);
    }

    public function testGetStreamableFilePathReturnsNullForDirectory(): void
    {
        $service = new FileStreamService($this->testRoot, $this->pathService, $this->imageExtensions);
        $filePath = $service->getStreamableFilePath('subdir');
        $this->assertNull($filePath);
    }

    public function testGetStreamableFilePathReturnsNullForNonExistentFile(): void
    {
        $service = new FileStreamService($this->testRoot, $this->pathService, $this->imageExtensions);
        $filePath = $service->getStreamableFilePath('nonexistent.png');
        $this->assertNull($filePath);
    }

    public function testGetStreamableFilePathReturnsNullForPathOutsideRoot(): void
    {
        $service = new FileStreamService($this->testRoot, $this->pathService, $this->imageExtensions);
        $filePath = $service->getStreamableFilePath('../somefile.png');
        $this->assertNull($filePath);
    }

    // public function testStreamFileSetsCorrectHeadersAndOutputsFile(): void
    // {
    //     $service = new FileStreamService($this->testRoot, $this->pathService, $this->imageExtensions);
    //     $filePath = $this->testRoot . '/test.png';
    //
    //     // Mock header function to capture output
    //     $mockHeaders = [];
    //     $mockedHeaderFunction = function ($header) use (&$mockHeaders) {
    //         $mockHeaders[] = $header;
    //     };
    //     runkit_function_redefine('header', '$header', '$mockHeaders[] = $header;');
    //
    //     // Capture output from readfile
    //     ob_start();
    //     $service->streamFile($filePath);
    //     $output = ob_get_clean();
    //
    //     // Restore original header function
    //     runkit_function_redefine('header', '$header', 'header($header);');
    //
    //     $this->assertContains('Content-Type: image/png', $mockHeaders);
    //     $this->assertContains('Content-Length: ' . filesize($filePath), $mockHeaders);
    //     $this->assertEquals('image content', $output);
    // }
}
