<?php

namespace App\Tests\Unit\Service;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use App\Service\VilleService;
use App\ServiceResult\Ville\DeleteVilleResult;
use App\ServiceResult\Ville\UpdateVilleResult;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VilleServiceTest extends TestCase
{
    private VilleService $villeService;
    private MockObject $villeRepository;

    protected function setUp(): void
    {
        $this->villeRepository = $this->createMock(VilleRepository::class);

        $this->villeService = new VilleService(
            $this->villeRepository
        );
    }

    public function testGetAllVilles(): void
    {
        // Arrange
        $villes = [new Ville(), new Ville()];

        $this->villeRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($villes);

        // Act
        $result = $this->villeService->getAllVilles();

        // Assert
        $this->assertSame($villes, $result);
        $this->assertCount(2, $result);
    }

    public function testCreateVille(): void
    {
        // Arrange
        $nomVille = 'Paris';
        $codePostal = '75000';

        $this->villeRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Ville::class));

        // Act
        $result = $this->villeService->createVille($nomVille, $codePostal);

        // Assert
        $this->assertInstanceOf(Ville::class, $result);
        $this->assertEquals($nomVille, $result->getNomVille());
        $this->assertEquals($codePostal, $result->getCodePostal());
    }

    public function testDeleteVilleSuccess(): void
    {
        // Arrange
        $ville = $this->createMock(Ville::class);
        $ville->method('getLieux')
            ->willReturn(new ArrayCollection());

        $this->villeRepository->expects($this->once())
            ->method('remove')
            ->with($ville);

        // Act
        $result = $this->villeService->deleteVIlle($ville);

        // Assert
        $this->assertEquals(DeleteVilleResult::SUCCESS, $result);
    }

    public function testDeleteVilleInUse(): void
    {
        // Arrange
        $ville = $this->createMock(Ville::class);
        $lieu = $this->createMock(Lieu::class);

        $lieux = new ArrayCollection([$lieu]);
        $ville->method('getLieux')
            ->willReturn($lieux);

        $this->villeRepository->expects($this->never())
            ->method('remove');

        // Act
        $result = $this->villeService->deleteVIlle($ville);

        // Assert
        $this->assertEquals(DeleteVilleResult::VILLE_IN_USE, $result);
    }

    public function testUpdateVille(): void
    {
        // Arrange
        $ville = $this->createMock(Ville::class);
        $newNom = 'Lyon';
        $newCodePostal = '69000';

        $ville->expects($this->once())
            ->method('setNomVille')
            ->with($newNom);

        $ville->expects($this->once())
            ->method('setCodePostal')
            ->with($newCodePostal);

        $this->villeRepository->expects($this->once())
            ->method('save')
            ->with($ville);

        // Act
        $result = $this->villeService->UpdateVille($ville, $newNom, $newCodePostal);

        // Assert
        $this->assertEquals(UpdateVilleResult::SUCCESS, $result);
    }

    public function testSearchByName(): void
    {
        // Arrange
        $term = 'Paris';
        $expectedVilles = [new Ville(), new Ville()];

        $query = $this->createMock(Query::class);
        $query->method('getResult')
            ->willReturn($expectedVilles);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('where')
            ->with('s.nom_ville LIKE :term')
            ->willReturnSelf();
        $queryBuilder->method('setParameter')
            ->with('term', '%Paris%')
            ->willReturnSelf();
        $queryBuilder->method('getQuery')
            ->willReturn($query);

        $this->villeRepository->method('createQueryBuilder')
            ->with('s')
            ->willReturn($queryBuilder);

        // Act
        $result = $this->villeService->searchByName($term);

        // Assert
        $this->assertSame($expectedVilles, $result);
    }
}