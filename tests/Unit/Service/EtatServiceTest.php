<?php

namespace App\Tests\Unit\Service;

use App\Entity\Etat;
use App\Repository\EtatRepository;
use App\Service\EtatService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EtatServiceTest extends TestCase
{
    private EtatService $etatService;
    private MockObject $etatRepository;

    protected function setUp(): void
    {
        $this->etatRepository = $this->createMock(EtatRepository::class);
        $this->etatService = new EtatService($this->etatRepository);
    }

    public function testGetAllEtats(): void
    {
        // Arrange
        $etats = [new Etat(), new Etat()];

        $this->etatRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($etats);

        // Act
        $result = $this->etatService->getAllEtats();

        // Assert
        $this->assertSame($etats, $result);
        $this->assertCount(2, $result);
    }
}