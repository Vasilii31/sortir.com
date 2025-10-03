<?php

namespace App\Tests\Unit\Service;

use App\Dto\SortieFullDTO;
use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\SortieRepository;
use App\Service\EtatService;
use App\Service\SortieService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SortieServiceTest extends TestCase
{
    private SortieService $sortieService;
    private MockObject $sortieRepository;
    private MockObject $etatService;

    protected function setUp(): void
    {
        $this->sortieRepository = $this->createMock(SortieRepository::class);
        $this->etatService = $this->createMock(EtatService::class);

        $this->sortieService = new SortieService(
            $this->sortieRepository,
            $this->etatService
        );
    }

    public function testSetEtatBasedOnButtonEnregistrer(): void
    {
        // Arrange
        $sortie = $this->createMock(Sortie::class);
        $etatCreee = $this->createMock(Etat::class);
        $etatCreee->method('getLibelle')->willReturn('Créée');

        $this->etatService->expects($this->once())
            ->method('getAllEtats')
            ->willReturn([$etatCreee]);

        $sortie->expects($this->once())
            ->method('setEtat')
            ->with($etatCreee);

        // Act
        $this->sortieService->setEtatBasedOnButton($sortie, 'enregistrer');

        // Assert - Test passes if no exception thrown
        $this->assertTrue(true);
    }

    public function testSetEtatBasedOnButtonPublier(): void
    {
        // Arrange
        $sortie = $this->createMock(Sortie::class);
        $etatOuverte = $this->createMock(Etat::class);
        $etatOuverte->method('getLibelle')->willReturn('Ouverte');

        $this->etatService->expects($this->once())
            ->method('getAllEtats')
            ->willReturn([$etatOuverte]);

        $sortie->expects($this->once())
            ->method('setEtat')
            ->with($etatOuverte);

        // Act
        $this->sortieService->setEtatBasedOnButton($sortie, 'publier');

        // Assert
        $this->assertTrue(true);
    }

    public function testSetEtatBasedOnButtonUnknown(): void
    {
        // Arrange
        $sortie = $this->createMock(Sortie::class);

        $this->etatService->expects($this->once())
            ->method('getAllEtats')
            ->willReturn([]);

        $sortie->expects($this->never())
            ->method('setEtat');

        // Act
        $this->sortieService->setEtatBasedOnButton($sortie, 'unknown_button');

        // Assert
        $this->assertTrue(true);
    }

    public function testFindFilteredSorties(): void
    {
        // Arrange
        $criteria = ['nom' => 'test'];
        $user = $this->createMock(Participant::class);
        $sortie = $this->createMock(Sortie::class);

        $rawResults = [[$sortie, 'nbInscrits' => 5]];

        $this->sortieRepository->expects($this->once())
            ->method('findByFilter')
            ->with($criteria, $user)
            ->willReturn($rawResults);

        $sortie->method('getInscriptions')
            ->willReturn(new \Doctrine\Common\Collections\ArrayCollection());

        // Act
        $result = $this->sortieService->findFilteredSorties($criteria, $user);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function testGetSortieWithParticipants(): void
    {
        // Arrange
        $sortieId = 123;
        $sortie = $this->createMock(Sortie::class);

        $sortie->method('getId')->willReturn($sortieId);
        $sortie->method('getNom')->willReturn('Test Sortie');
        $sortie->method('getDatedebut')->willReturn(new \DateTime());
        $sortie->method('getDatecloture')->willReturn(new \DateTime());
        $sortie->method('getNbInscriptionsMax')->willReturn(10);
        $sortie->method('getDescriptioninfos')->willReturn('Description');
        $sortie->method('getDuree')->willReturn(120);

        $etat = $this->createMock(Etat::class);
        $etat->method('getLibelle')->willReturn('Ouverte');
        $sortie->method('getEtat')->willReturn($etat);

        $organisateur = $this->createMock(Participant::class);
        $organisateur->method('getId')->willReturn(1);
        $organisateur->method('getNom')->willReturn('Doe');
        $organisateur->method('getPrenom')->willReturn('John');
        $organisateur->method('getPseudo')->willReturn('john_doe');
        $sortie->method('getOrganisateur')->willReturn($organisateur);

        $lieu = $this->createMock(\App\Entity\Lieu::class);
        $ville = $this->createMock(\App\Entity\Ville::class);
        $ville->method('getNomVille')->willReturn('Paris');
        $lieu->method('getVille')->willReturn($ville);
        $sortie->method('getLieu')->willReturn($lieu);

        $sortie->method('getInscriptions')
            ->willReturn(new \Doctrine\Common\Collections\ArrayCollection());

        $this->sortieRepository->expects($this->once())
            ->method('findWithParticipants')
            ->with($sortieId)
            ->willReturn($sortie);

        // Act
        $result = $this->sortieService->getSortieWithParticipants($sortieId);

        // Assert
        $this->assertInstanceOf(SortieFullDTO::class, $result);
        $this->assertEquals($sortieId, $result->id);
        $this->assertEquals('Test Sortie', $result->nom);
    }

    public function testFindAll(): void
    {
        // Arrange
        $sorties = [new Sortie(), new Sortie()];

        $this->sortieRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($sorties);

        // Act
        $result = $this->sortieService->findAll();

        // Assert
        $this->assertSame($sorties, $result);
    }

    public function testFindAllWithSubscribed(): void
    {
        // Arrange
        $user = $this->createMock(Participant::class);
        $sortie = $this->createMock(Sortie::class);

        $rawResults = [[$sortie, 'nbInscrits' => 3]];

        $this->sortieRepository->expects($this->once())
            ->method('findAllWithSubscribed')
            ->willReturn($rawResults);

        $sortie->method('getInscriptions')
            ->willReturn(new \Doctrine\Common\Collections\ArrayCollection());

        // Act
        $result = $this->sortieService->findAllWithSubscribed($user);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function testValidateDates(): void
    {
        // Arrange
        $sortie = $this->createMock(Sortie::class);
        $sortie->method('getDatedebut')->willReturn(new \DateTime('+1 day'));
        $sortie->method('getDatecloture')->willReturn(new \DateTime('+1 hour'));

        // Act
        $result = $this->sortieService->validateDates($sortie);

        // Assert
        $this->assertNull($result);
    }

    public function testValidateDatesWithPastDate(): void
    {
        // Arrange
        $sortie = $this->createMock(Sortie::class);
        $sortie->method('getDatedebut')->willReturn(new \DateTime('-1 day'));
        $sortie->method('getDatecloture')->willReturn(new \DateTime('-2 days'));

        // Act
        $result = $this->sortieService->validateDates($sortie);

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString('antérieure', $result);
    }
}