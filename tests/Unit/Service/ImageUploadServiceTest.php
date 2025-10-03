<?php

namespace App\Tests\Unit\Service;

use App\Service\ImageUploadService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class ImageUploadServiceTest extends TestCase
{
    private ImageUploadService $imageUploadService;
    private SluggerInterface $slugger;
    private string $uploadDirectory;

    protected function setUp(): void
    {
        $this->uploadDirectory = '/tmp/test_uploads';
        $this->slugger = $this->createMock(SluggerInterface::class);

        $this->imageUploadService = new ImageUploadService(
            $this->uploadDirectory,
            $this->slugger
        );
    }

    public function testGetUploadDirectory(): void
    {
        // Act
        $result = $this->imageUploadService->getUploadDirectory();

        // Assert
        $this->assertEquals($this->uploadDirectory, $result);
    }

    public function testIsValidImageFileWithValidJpeg(): void
    {
        // Arrange
        $file = $this->createMock(UploadedFile::class);
        $file->method('getSize')->willReturn(1000000); // 1MB
        $file->method('getMimeType')->willReturn('image/jpeg');

        // Act
        $result = $this->imageUploadService->isValidImageFile($file);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsValidImageFileWithValidPng(): void
    {
        // Arrange
        $file = $this->createMock(UploadedFile::class);
        $file->method('getSize')->willReturn(1500000); // 1.5MB
        $file->method('getMimeType')->willReturn('image/png');

        // Act
        $result = $this->imageUploadService->isValidImageFile($file);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsValidImageFileWithValidWebp(): void
    {
        // Arrange
        $file = $this->createMock(UploadedFile::class);
        $file->method('getSize')->willReturn(500000); // 0.5MB
        $file->method('getMimeType')->willReturn('image/webp');

        // Act
        $result = $this->imageUploadService->isValidImageFile($file);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsValidImageFileWithTooLargeFile(): void
    {
        // Arrange
        $file = $this->createMock(UploadedFile::class);
        $file->method('getSize')->willReturn(3000000); // 3MB (> 2MB limit)
        $file->method('getMimeType')->willReturn('image/jpeg');

        // Act
        $result = $this->imageUploadService->isValidImageFile($file);

        // Assert
        $this->assertFalse($result);
    }

    public function testIsValidImageFileWithInvalidMimeType(): void
    {
        // Arrange
        $file = $this->createMock(UploadedFile::class);
        $file->method('getSize')->willReturn(1000000); // 1MB
        $file->method('getMimeType')->willReturn('application/pdf');

        // Act
        $result = $this->imageUploadService->isValidImageFile($file);

        // Assert
        $this->assertFalse($result);
    }

    public function testIsValidImageFileWithTextFile(): void
    {
        // Arrange
        $file = $this->createMock(UploadedFile::class);
        $file->method('getSize')->willReturn(1000); // Small file
        $file->method('getMimeType')->willReturn('text/plain');

        // Act
        $result = $this->imageUploadService->isValidImageFile($file);

        // Assert
        $this->assertFalse($result);
    }

    public function testUploadFileException(): void
    {
        // Arrange
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('test-image.jpg');
        $file->method('guessExtension')->willReturn('jpg');

        // Simuler une exception lors du déplacement du fichier
        $file->method('move')
            ->willThrowException(new \Symfony\Component\HttpFoundation\File\Exception\FileException('Move failed'));

        $slugResult = $this->createMock(UnicodeString::class);
        $slugResult->method('__toString')->willReturn('test-image');
        $this->slugger->method('slug')->willReturn($slugResult);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erreur lors du téléchargement de l\'image');

        $this->imageUploadService->upload($file);
    }

    public function testUploadSuccess(): void
    {
        // Arrange
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('test-image.jpg');
        $file->method('guessExtension')->willReturn('jpg');
        $file->expects($this->once())
            ->method('move')
            ->with($this->uploadDirectory, $this->matchesRegularExpression('/test-image-\w+\.jpg/'));

        $slugResult = $this->createMock(UnicodeString::class);
        $slugResult->method('__toString')->willReturn('test-image');
        $this->slugger->method('slug')->willReturn($slugResult);

        // Act
        $result = $this->imageUploadService->upload($file);

        // Assert
        $this->assertStringStartsWith('test-image-', $result);
        $this->assertStringEndsWith('.jpg', $result);
    }

    public function testUploadWithOldFilenameDeletion(): void
    {
        // Arrange
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('new-image.png');
        $file->method('guessExtension')->willReturn('png');
        $file->expects($this->once())
            ->method('move')
            ->with($this->uploadDirectory, $this->matchesRegularExpression('/new-image-\w+\.png/'));

        $slugResult = $this->createMock(UnicodeString::class);
        $slugResult->method('__toString')->willReturn('new-image');
        $this->slugger->method('slug')->willReturn($slugResult);

        // Créer un service avec une méthode delete mockée
        $imageUploadService = $this->getMockBuilder(ImageUploadService::class)
            ->setConstructorArgs([$this->uploadDirectory, $this->slugger])
            ->onlyMethods(['delete'])
            ->getMock();

        $imageUploadService->expects($this->once())
            ->method('delete')
            ->with('old-image.jpg');

        // Act
        $result = $imageUploadService->upload($file, 'old-image.jpg');

        // Assert
        $this->assertStringStartsWith('new-image-', $result);
        $this->assertStringEndsWith('.png', $result);
    }

    public function testDeleteWithNullFilename(): void
    {
        // Act - Should not throw exception
        $this->imageUploadService->delete(null);

        // Assert - Test passes if no exception thrown
        $this->assertTrue(true);
    }

    public function testDeleteWithEmptyFilename(): void
    {
        // Act - Should not throw exception
        $this->imageUploadService->delete('');

        // Assert - Test passes if no exception thrown
        $this->assertTrue(true);
    }

    public function testDeleteWithNonExistentFile(): void
    {
        // Act - Should not throw exception even if file doesn't exist
        $this->imageUploadService->delete('non-existent-file.jpg');

        // Assert - Test passes if no exception thrown
        $this->assertTrue(true);
    }
}