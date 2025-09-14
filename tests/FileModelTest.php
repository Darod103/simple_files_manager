<?php

declare(strict_types=1);

namespace Tests;

use App\Model\FileModel;
use PHPUnit\Framework\TestCase;

class FileModelTest extends TestCase
{
    private string $testRoot;

    protected function setUp(): void
    {
        $this->testRoot = __DIR__ . '/temp_test_root';
        if (!is_dir($this->testRoot)) {
            mkdir($this->testRoot, 0777, true);
        }

        // Create some dummy files and directories
        mkdir($this->testRoot . '/dir1');
        file_put_contents($this->testRoot . '/file1.txt', 'content');
        file_put_contents($this->testRoot . '/image.png', 'image_content');
        mkdir($this->testRoot . '/dir2');
        file_put_contents($this->testRoot . '/dir2/subfile.jpg', 'sub_image');
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

    public function testGetFilesReturnsCorrectStructureForRoot(): void
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $fileModel = new FileModel($this->testRoot, $imageExtensions);

        $files = $fileModel->getFiles('');

        $this->assertCount(3, $files);

        $this->assertEquals('dir1', $files[0]['name']);
        $this->assertEquals('dir', $files[0]['type']);

        $this->assertEquals('dir2', $files[1]['name']);
        $this->assertEquals('dir', $files[1]['type']);

        $this->assertEquals('image.png', $files[2]['name']);
        $this->assertEquals('img', $files[2]['type']);
    }

    public function testGetFilesReturnsCorrectStructureForSubdirectory(): void
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $fileModel = new FileModel($this->testRoot, $imageExtensions);

        $files = $fileModel->getFiles('dir2');

        $this->assertCount(1, $files);
        $this->assertEquals('subfile.jpg', $files[0]['name']);
        $this->assertEquals('img', $files[0]['type']);
    }

    public function testGetFilesExcludesNonImageFiles(): void
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $fileModel = new FileModel($this->testRoot, $imageExtensions);

        $files = $fileModel->getFiles('');

        // file1.txt should be excluded
        foreach ($files as $file) {
            $this->assertNotEquals('file1.txt', $file['name']);
        }
    }

    public function testGetFilesWithInvalidPathReturnsEmptyArray(): void
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $fileModel = new FileModel($this->testRoot, $imageExtensions);

        $files = $fileModel->getFiles('non_existent_dir');
        $this->assertEmpty($files);
    }
}
