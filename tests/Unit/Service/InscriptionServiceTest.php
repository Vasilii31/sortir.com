<?php

namespace App\Tests\Unit\Service;

use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\InscriptionRepository;
use App\Repository\SortieRepository;
use App\Service\InscriptionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InscriptionServiceTest extends TestCase
{
    private InscriptionService $inscriptionService;
    private MockObject $inscriptionRepository;
    private MockObject $etatRepository;
    private MockObject $sortieRepository;

    protected function setUp(): void
    {
        $this->inscriptionRepository = $this->createMock(InscriptionRepository::class);
        $this->etatRepository = $this->createMock(EtatRepository::class);
        $this->sortieRepository = $this->createMock(SortieRepository::class);

        $this->inscriptionService = new InscriptionService(
            $this->inscriptionRepository,
            $this->etatRepository,
            $this->sortieRepository
        );
    }

    public function testRegisterParticipantSuccess(): void
    {
        // Arrange
        $participant = $this->createMock(Participant::class);
        $sortie = $this->createMock(Sortie::class);

        $etat = $this->createMock(Etat::class);
        $etat->method('getLibelle')->willReturn('Ouverte');

        $sortie->method('getEtat')->willReturn($etat);
        $sortie->method('getOrganisateur')->willReturn(null);
        $sortie->method('getInscriptions')->willReturn(new \Doctrine\Common\Collections\ArrayCollection());
        $sortie->method('getNbInscriptionsMax')->willReturn(10);

        $this->inscriptionRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Inscription::class));

        // Act & Assert - pas d'exception levée
        $this->inscriptionService->registerParticipant($sortie, $participant);
        $this->assertTrue(true); // Test passes if no exception thrown
    }

    public function testRegisterParticipantWhenSortieNotOpen(): void
    {
        // Arrange
        $participant = $this->createMock(Participant::class);
        $sortie = $this->createMock(Sortie::class);

        $etat = $this->createMock(Etat::class);
        $etat->method('getLibelle')->willReturn('Fermée');

        $sortie->method('getEtat')->willReturn($etat);
        $sortie->method('getOrganisateur')->willReturn(null);

        // Act & Assert
        $this->expectException(\DomainException::class);

        $this->inscriptionService->registerParticipant($sortie, $participant);
    }

    public function testRegisterParticipantWhenAlreadyRegistered(): void
    {
        // Arrange
        $participant = $this->createMock(Participant::class);
        $sortie = $this->createMock(Sortie::class);

        $etat = $this->createMock(Etat::class);
        $etat->method('getLibelle')->willReturn('Ouverte');

        $existingInscription = $this->createMock(Inscription::class);
        $existingInscription->method('getParticipant')->willReturn($participant);

        $inscriptions = new \Doctrine\Common\Collections\ArrayCollection([$existingInscription]);

        $sortie->method('getEtat')->willReturn($etat);
        $sortie->method('getOrganisateur')->willReturn(null);
        $sortie->method('getInscriptions')->willReturn($inscriptions);

        // Act - devrait retourner sans erreur (déjà inscrit)
        $this->inscriptionService->registerParticipant($sortie, $participant);

        // Assert - si on arrive ici, c'est que ça marche
        $this->assertTrue(true);
    }

    public function testUnregisterParticipantSuccess(): void
    {
        // Arrange
        $participant = $this->createMock(Participant::class);
        $sortie = $this->createMock(Sortie::class);

        $inscription = $this->createMock(Inscription::class);
        $inscription->method('getParticipant')->willReturn($participant);

        $inscriptions = new \Doctrine\Common\Collections\ArrayCollection([$inscription]);

        $sortie->method('getInscriptions')->willReturn($inscriptions);
        $sortie->method('getNbInscriptionsMax')->willReturn(10);
        $sortie->method('getDateCloture')->willReturn(new \DateTime('+1 day'));

        $etatOuverte = $this->createMock(Etat::class);

        $this->etatRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['libelle' => 'Ouverte'])
            ->willReturn($etatOuverte);

        $this->inscriptionRepository->expects($this->once())
            ->method('remove')
            ->with($inscription);

        // Act
        $this->inscriptionService->unregisterParticipant($sortie, $participant);

        // Assert
        $this->assertTrue(true); // Test passes if no exception thrown
    }

    public function testUnregisterParticipantWhenNotRegistered(): void
    {
        // Arrange
        $participant = $this->createMock(Participant::class);
        $sortie = $this->createMock(Sortie::class);

        // Collection vide d'inscriptions
        $sortie->method('getInscriptions')->willReturn(new \Doctrine\Common\Collections\ArrayCollection());

        // Act - devrait retourner sans erreur (pas inscrit)
        $this->inscriptionService->unregisterParticipant($sortie, $participant);

        // Assert
        $this->assertTrue(true); // Test passes if no exception thrown
    }
}