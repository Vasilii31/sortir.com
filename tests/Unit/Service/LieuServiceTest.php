<?php

namespace App\Tests\Unit\Service;

use App\Entity\Lieu;
use App\Repository\LieuRepository;
use App\Service\LieuService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LieuServiceTest extends TestCase
{
    private LieuService $lieuService;
    private MockObject $lieuRepository;

    protected function setUp(): void
    {
        $this->lieuRepository = $this->createMock(LieuRepository::class);

        $this->lieuService = new LieuService(
            $this->lieuRepository
        );
    }

    public function testGetAllLieux(): void
    {
        // Arrange
        $lieux = [new Lieu(), new Lieu()];

        $this->lieuRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($lieux);

        // Act
        $result = $this->lieuService->getAllLieux();

        // Assert
        $this->assertSame($lieux, $result);
        $this->assertCount(2, $result);
    }
}