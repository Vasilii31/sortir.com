<?php

namespace App\Tests\Unit\Service;

use App\Entity\Etat;
use App\Repository\EtatRepository;
use App\Service\CleanupService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CleanupServiceTest extends TestCase
{
    private CleanupService $cleanupService;
    private MockObject $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->cleanupService = new CleanupService(
            $this->entityManager
        );
    }

    public function testCleanupFinishedSortiesForInactiveParticipants(): void
    {
        // Arrange
        $deletedInscriptions = 5;
        $deletedSorties = 3;

        // Mock des queries
        $inscriptionQuery = $this->createMock(Query::class);
        $inscriptionQuery->expects($this->once())
            ->method('setParameter')
            ->with('now', $this->isInstanceOf(\DateTime::class))
            ->willReturnSelf();
        $inscriptionQuery->expects($this->once())
            ->method('execute')
            ->willReturn($deletedInscriptions);

        $sortieQuery = $this->createMock(Query::class);
        $sortieQuery->expects($this->once())
            ->method('setParameter')
            ->with('now', $this->isInstanceOf(\DateTime::class))
            ->willReturnSelf();
        $sortieQuery->expects($this->once())
            ->method('execute')
            ->willReturn($deletedSorties);

        // Configuration du EntityManager pour retourner les bonnes queries
        $this->entityManager->expects($this->exactly(2))
            ->method('createQuery')
            ->willReturnCallback(function ($sql) use ($inscriptionQuery, $sortieQuery) {
                if (str_contains($sql, 'DELETE FROM App\Entity\Inscription')) {
                    return $inscriptionQuery;
                } elseif (str_contains($sql, 'DELETE FROM App\Entity\Sortie')) {
                    return $sortieQuery;
                }
                return null;
            });

        // Act
        $result = $this->cleanupService->cleanupFinishedSortiesForInactiveParticipants();

        // Assert
        $this->assertEquals($deletedInscriptions + $deletedSorties, $result);
        $this->assertEquals(8, $result);
    }

    public function testCancelOngoingSortiesForInactiveOrganizersSuccess(): void
    {
        // Arrange
        $updatedSorties = 4;
        $etatAnnule = $this->createMock(Etat::class);

        $etatRepository = $this->createMock(EtatRepository::class);
        $etatRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['libelle' => 'Annulée'])
            ->willReturn($etatAnnule);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with('App\Entity\Etat')
            ->willReturn($etatRepository);

        $query = $this->createMock(Query::class);
        $query->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                ['etatAnnule', $etatAnnule],
                ['now', $this->isInstanceOf(\DateTime::class)]
            )
            ->willReturnSelf();
        $query->expects($this->once())
            ->method('execute')
            ->willReturn($updatedSorties);

        $this->entityManager->expects($this->once())
            ->method('createQuery')
            ->with($this->stringContains('UPDATE App\Entity\Sortie'))
            ->willReturn($query);

        // Act
        $result = $this->cleanupService->cancelOngoingSortiesForInactiveOrganizers();

        // Assert
        $this->assertEquals($updatedSorties, $result);
    }

    public function testCancelOngoingSortiesForInactiveOrganizersNoEtatAnnule(): void
    {
        // Arrange
        $etatRepository = $this->createMock(EtatRepository::class);
        $etatRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['libelle' => 'Annulée'])
            ->willReturn(null);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with('App\Entity\Etat')
            ->willReturn($etatRepository);

        // Ne devrait pas créer de query
        $this->entityManager->expects($this->never())
            ->method('createQuery');

        // Act
        $result = $this->cleanupService->cancelOngoingSortiesForInactiveOrganizers();

        // Assert
        $this->assertEquals(0, $result);
    }

    public function testCleanupFinishedSortiesWithZeroResults(): void
    {
        // Arrange
        $deletedInscriptions = 0;
        $deletedSorties = 0;

        // Mock des queries
        $inscriptionQuery = $this->createMock(Query::class);
        $inscriptionQuery->expects($this->once())
            ->method('setParameter')
            ->with('now', $this->isInstanceOf(\DateTime::class))
            ->willReturnSelf();
        $inscriptionQuery->expects($this->once())
            ->method('execute')
            ->willReturn($deletedInscriptions);

        $sortieQuery = $this->createMock(Query::class);
        $sortieQuery->expects($this->once())
            ->method('setParameter')
            ->with('now', $this->isInstanceOf(\DateTime::class))
            ->willReturnSelf();
        $sortieQuery->expects($this->once())
            ->method('execute')
            ->willReturn($deletedSorties);

        // Configuration du EntityManager
        $this->entityManager->expects($this->exactly(2))
            ->method('createQuery')
            ->willReturnCallback(function ($sql) use ($inscriptionQuery, $sortieQuery) {
                if (str_contains($sql, 'DELETE FROM App\Entity\Inscription')) {
                    return $inscriptionQuery;
                } elseif (str_contains($sql, 'DELETE FROM App\Entity\Sortie')) {
                    return $sortieQuery;
                }
                return null;
            });

        // Act
        $result = $this->cleanupService->cleanupFinishedSortiesForInactiveParticipants();

        // Assert
        $this->assertEquals(0, $result);
    }
}