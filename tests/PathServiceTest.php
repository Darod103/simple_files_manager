<?php

declare(strict_types=1);

namespace Tests;

use App\Service\PathService;
use PHPUnit\Framework\TestCase;

class PathServiceTest extends TestCase
{
    public function testSanitizeRelPath(): void
    {
        $pathService = new PathService();

        // Test with various valid and invalid paths
        $this->assertEquals('', $pathService->sanitizeRelPath(''));
        $this->assertEquals('folder/subfolder', $pathService->sanitizeRelPath('folder/./subfolder'));
        $this->assertEquals('folder/subfolder', $pathService->sanitizeRelPath('folder/../folder/subfolder'));
        $this->assertEquals('folder/subfolder', $pathService->sanitizeRelPath('/folder/subfolder/'));
        $this->assertEquals('folder/subfolder', $pathService->sanitizeRelPath('\\folder\\subfolder'));
        $this->assertEquals('a/b/c', $pathService->sanitizeRelPath('a/b/c'));
        $this->assertEquals('a/b/c', $pathService->sanitizeRelPath('./a/../a/b/c'));
    }

    public function testEncodePath(): void
    {
        $pathService = new PathService();

        $this->assertEquals('', $pathService->encodePath(''));
        $this->assertEquals('folder/image.png', $pathService->encodePath('folder/image.png'));
        $this->assertEquals('folder/%D0%B8%D0%B7%D0%BE%D0%B1%D1%80%D0%B0%D0%B6%D0%B5%D0%BD%D0%B8%D0%B5.jpg', $pathService->encodePath('folder/изображение.jpg'));
        $this->assertEquals('path%20with%20spaces', $pathService->encodePath('path with spaces'));
    }
}
