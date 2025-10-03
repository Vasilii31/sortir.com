<?php

namespace App\Tests\Unit\Service;

use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\ParticipantRepository;
use App\Repository\InscriptionRepository;
use App\Repository\SortieRepository;
use App\Repository\EtatRepository;
use App\Service\ImageUploadService;
use App\Service\ParticipantService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantServiceTest extends TestCase
{
    private ParticipantService $participantService;
    private MockObject $participantRepository;
    private MockObject $inscriptionRepository;
    private MockObject $sortieRepository;
    private MockObject $etatRepository;
    private MockObject $passwordHasher;
    private MockObject $imageUploadService;

    protected function setUp(): void
    {
        $this->participantRepository = $this->createMock(ParticipantRepository::class);
        $this->inscriptionRepository = $this->createMock(InscriptionRepository::class);
        $this->sortieRepository = $this->createMock(SortieRepository::class);
        $this->etatRepository = $this->createMock(EtatRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->imageUploadService = $this->createMock(ImageUploadService::class);

        $this->participantService = new ParticipantService(
            $this->participantRepository,
            $this->inscriptionRepository,
            $this->sortieRepository,
            $this->etatRepository,
            $this->passwordHasher,
            $this->imageUploadService
        );
    }

    public function testCreateParticipant(): void
    {
        // Arrange
        $site = $this->createMock(Site::class);
        $hashedPassword = 'hashed_password';

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn($hashedPassword);

        $this->participantRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Participant::class));

        // Act
        $result = $this->participantService->createParticipant(
            'Doe',
            'John',
            'john_doe',
            'john@example.com',
            'plain_password',
            '1234567890',
            $site
        );

        // Assert
        $this->assertInstanceOf(Participant::class, $result);
        $this->assertEquals('Doe', $result->getNom());
        $this->assertEquals('John', $result->getPrenom());
        $this->assertEquals('john_doe', $result->getPseudo());
        $this->assertEquals('john@example.com', $result->getMail());
        $this->assertTrue($result->isActif());
        $this->assertFalse($result->isAdministrateur());
    }

    public function testUpdateParticipant(): void
    {
        // Arrange
        $participant = new Participant();
        $site = $this->createMock(Site::class);

        $this->participantRepository->expects($this->once())
            ->method('save')
            ->with($participant);

        // Act
        $result = $this->participantService->updateParticipant(
            $participant,
            'Smith',
            'Jane',
            'jane_smith',
            'jane@example.com',
            '0987654321',
            $site
        );

        // Assert
        $this->assertSame($participant, $result);
        $this->assertEquals('Smith', $result->getNom());
        $this->assertEquals('Jane', $result->getPrenom());
    }

    public function testFindByPseudo(): void
    {
        // Arrange
        $participant = new Participant();

        $this->participantRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['pseudo' => 'test_user'])
            ->willReturn($participant);

        // Act
        $result = $this->participantService->findByPseudo('test_user');

        // Assert
        $this->assertSame($participant, $result);
    }

    public function testFindByMail(): void
    {
        // Arrange
        $participant = new Participant();

        $this->participantRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mail' => 'test@example.com'])
            ->willReturn($participant);

        // Act
        $result = $this->participantService->findByMail('test@example.com');

        // Assert
        $this->assertSame($participant, $result);
    }

    public function testCheckUniqueFieldsWithNoConflicts(): void
    {
        // Arrange
        $this->participantRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(null);

        // Act
        $result = $this->participantService->checkUniqueFields('new_user', 'new@example.com');

        // Assert
        $this->assertEmpty($result);
    }

    public function testCheckUniqueFieldsWithConflicts(): void
    {
        // Arrange
        $existingParticipant1 = $this->createMock(Participant::class);
        $existingParticipant1->method('getId')->willReturn(1);

        $existingParticipant2 = $this->createMock(Participant::class);
        $existingParticipant2->method('getId')->willReturn(2);

        $this->participantRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->withConsecutive(
                [['pseudo' => 'existing_user']],
                [['mail' => 'existing@example.com']]
            )
            ->willReturn($existingParticipant1, $existingParticipant2);

        // Act
        $result = $this->participantService->checkUniqueFields('existing_user', 'existing@example.com');

        // Assert
        $this->assertCount(2, $result);
        $this->assertContains('Ce pseudo est déjà utilisé', $result);
        $this->assertContains('Cette adresse email est déjà utilisée', $result);
    }

    public function testGetAllParticipants(): void
    {
        // Arrange
        $participants = [new Participant(), new Participant()];

        $this->participantRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($participants);

        // Act
        $result = $this->participantService->getAllParticipants();

        // Assert
        $this->assertSame($participants, $result);
    }

    public function testToggleAdmin(): void
    {
        // Arrange
        $participant = new Participant();
        $participant->setAdministrateur(false);

        $this->participantRepository->expects($this->once())
            ->method('save')
            ->with($participant);

        // Act
        $this->participantService->toggleAdmin($participant);

        // Assert
        $this->assertTrue($participant->isAdministrateur());
    }

    public function testDeleteParticipant(): void
    {
        // Arrange
        $participant = new Participant();

        $this->participantRepository->expects($this->once())
            ->method('remove')
            ->with($participant);

        // Act
        $this->participantService->deleteParticipant($participant);

        // Assert - Test passes if no exception thrown
        $this->assertTrue(true);
    }

    public function testToggleActif(): void
    {
        // Arrange
        $participant = new Participant();
        $participant->setActif(true);

        $this->participantRepository->expects($this->once())
            ->method('save')
            ->with($participant);

        // Act
        $this->participantService->toggleActif($participant);

        // Assert
        $this->assertFalse($participant->isActif());
    }
}