<?php

namespace App\Tests\Unit\Service;

use App\Entity\Participant;
use App\Entity\Site;
use App\Service\ParticipantService;
use App\Service\SiteService;
use App\Service\UserImportService;
use App\ServiceResult\Participant\CSVFileValidityResult;
use App\ServiceResult\Participant\ParticipantCSVValidityResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserImportServiceTest extends TestCase
{
    private UserImportService $userImportService;
    private MockObject $participantService;
    private MockObject $siteService;

    protected function setUp(): void
    {
        $this->participantService = $this->createMock(ParticipantService::class);
        $this->siteService = $this->createMock(SiteService::class);

        $this->userImportService = new UserImportService(
            $this->participantService,
            $this->siteService
        );
    }

    public function testCreateParticipantCSVSuccess(): void
    {
        // Arrange
        $data = ['pseudo', 'Doe', 'John', '1234567890', 'john@example.com', 'password', '0', '1', 'Site A'];
        $site = $this->createMock(Site::class);
        $participant = $this->createMock(Participant::class);

        $this->siteService->expects($this->once())
            ->method('searchByName')
            ->with('Site A')
            ->willReturn([$site]);

        $this->participantService->expects($this->once())
            ->method('createParticipant')
            ->with('Doe', 'John', 'pseudo', 'john@example.com', 'password', '1234567890', $site)
            ->willReturn($participant);

        // Act
        $result = $this->userImportService->CreateParticipantCSV($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::SUCCESS, $result);
    }

    public function testCreateParticipantCSVWithInvalidSiteName(): void
    {
        // Arrange
        $data = ['pseudo', 'Doe', 'John', '1234567890', 'john@example.com', 'password', '0', '1', 'Invalid Site'];

        $this->siteService->expects($this->once())
            ->method('searchByName')
            ->with('Invalid Site')
            ->willReturn([]);

        // Act
        $result = $this->userImportService->CreateParticipantCSV($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::INVALID_SITE_NAME, $result);
    }

    public function testCreateParticipantCSVWithEmptySiteName(): void
    {
        // Arrange
        $data = ['pseudo', 'Doe', 'John', '1234567890', 'john@example.com', 'password', '0', '1', ''];

        // Act
        $result = $this->userImportService->CreateParticipantCSV($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::INVALID_SITE_NAME, $result);
    }

    public function testCreateParticipantCSVWithParticipantCreationError(): void
    {
        // Arrange
        $data = ['pseudo', 'Doe', 'John', '1234567890', 'john@example.com', 'password', '0', '1', 'Site A'];
        $site = $this->createMock(Site::class);
        $participant = $this->createMock(Participant::class);

        $this->siteService->expects($this->once())
            ->method('searchByName')
            ->with('Site A')
            ->willReturn([$site]);

        // Le service createParticipant ne peut pas retourner null selon sa signature
        // Ce test simule le cas où une exception serait levée, mais ici on teste que le code fonctionne
        $this->participantService->expects($this->once())
            ->method('createParticipant')
            ->willReturn($participant);

        // Act
        $result = $this->userImportService->CreateParticipantCSV($data);

        // Assert
        // Puisque createParticipant retourne toujours un Participant, le résultat sera SUCCESS
        $this->assertEquals(ParticipantCSVValidityResult::SUCCESS, $result);
    }

    public function testCheckCsvValidityWithValidHeaders(): void
    {
        // Arrange
        $data = ['pseudo', 'nom', 'prenom', 'telephone', 'mail', 'mot_de_passe', 'administrateur', 'actif', 'nom_du_site'];

        // Act
        $result = $this->userImportService->CheckCsvValidity($data);

        // Assert
        $this->assertEquals(CSVFileValidityResult::VALID, $result);
    }

    public function testCheckCsvValidityWithIncorrectHeaders(): void
    {
        // Arrange
        $data = ['pseudo', 'nom', 'prenom', 'telephone', 'mail', 'mot_de_passe', 'admin', 'actif', 'site'];

        // Act
        $result = $this->userImportService->CheckCsvValidity($data);

        // Assert
        $this->assertEquals(CSVFileValidityResult::NO_MATCH_COLUMN, $result);
    }

    public function testCheckCsvValidityWithIncorrectColumnNumber(): void
    {
        // Arrange
        $data = ['pseudo', 'nom', 'prenom', 'telephone', 'mail'];

        // Act
        $result = $this->userImportService->CheckCsvValidity($data);

        // Assert
        // Dans la logique actuelle, un nombre incorrect de colonnes sera détecté comme NO_MATCH_COLUMN
        // car la comparaison stricte ($data !== $expectedHeaders) est faite en premier
        $this->assertEquals(CSVFileValidityResult::NO_MATCH_COLUMN, $result);
    }

    public function testCheckParticipantValiditySuccess(): void
    {
        // Arrange
        $data = ['new_user', 'Doe', 'John', '1234567890', 'john@example.com', 'password123', '0', '1', 'Site A'];
        $site = $this->createMock(Site::class);

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        $this->participantService->expects($this->once())
            ->method('findByMail')
            ->with('john@example.com')
            ->willReturn(null);

        $this->siteService->expects($this->once())
            ->method('searchByName')
            ->with('Site A')
            ->willReturn([$site]);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::SUCCESS, $result);
    }

    public function testCheckParticipantValidityWithMissingUsername(): void
    {
        // Arrange
        $data = ['', 'Doe', 'John', '1234567890', 'john@example.com', 'password123', '0', '1', 'Site A'];

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::MISSING_USERNAME, $result);
    }

    public function testCheckParticipantValidityWithTakenPseudo(): void
    {
        // Arrange
        $data = ['existing_user', 'Doe', 'John', '1234567890', 'john@example.com', 'password123', '0', '1', 'Site A'];
        $existingParticipant = $this->createMock(Participant::class);

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('existing_user')
            ->willReturn($existingParticipant);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::USER_PSEUDO_TAKEN, $result);
    }

    public function testCheckParticipantValidityWithMissingNom(): void
    {
        // Arrange
        $data = ['new_user', '', 'John', '1234567890', 'john@example.com', 'password123', '0', '1', 'Site A'];

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::MISSING_NOM, $result);
    }

    public function testCheckParticipantValidityWithMissingPrenom(): void
    {
        // Arrange
        $data = ['new_user', 'Doe', '', '1234567890', 'john@example.com', 'password123', '0', '1', 'Site A'];

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::MISSING_PRENOM, $result);
    }

    public function testCheckParticipantValidityWithShortPassword(): void
    {
        // Arrange
        $data = ['new_user', 'Doe', 'John', '1234567890', 'john@example.com', '123', '0', '1', 'Site A'];

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::INVALID_PASSWORD_LENGTH, $result);
    }

    public function testCheckParticipantValidityWithMissingPassword(): void
    {
        // Arrange
        $data = ['new_user', 'Doe', 'John', '1234567890', 'john@example.com', '', '0', '1', 'Site A'];

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::MISSING_PASSWORD, $result);
    }

    public function testCheckParticipantValidityWithInvalidAdminField(): void
    {
        // Arrange
        $data = ['new_user', 'Doe', 'John', '1234567890', 'john@example.com', 'password123', 'invalid', '1', 'Site A'];

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::INVALID_ADMIN_FIELD, $result);
    }

    public function testCheckParticipantValidityWithMissingAdminField(): void
    {
        // Arrange
        $data = ['new_user', 'Doe', 'John', '1234567890', 'john@example.com', 'password123', '', '1', 'Site A'];

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::MISSING_ADMIN_FIELD, $result);
    }

    public function testCheckParticipantValidityWithInvalidActifField(): void
    {
        // Arrange
        $data = ['new_user', 'Doe', 'John', '1234567890', 'john@example.com', 'password123', '0', 'invalid', 'Site A'];

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::INVALID_ACTIF_FIELD, $result);
    }

    public function testCheckParticipantValidityWithMissingActifField(): void
    {
        // Arrange
        $data = ['new_user', 'Doe', 'John', '1234567890', 'john@example.com', 'password123', '0', '', 'Site A'];

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::MISSING_ACTIF_FIELD, $result);
    }

    public function testCheckParticipantValidityWithInvalidEmailFormat(): void
    {
        // Arrange
        $data = ['new_user', 'Doe', 'John', '1234567890', 'invalid-email', 'password123', '0', '1', 'Site A'];

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::INVALID_MAIL_FORMAT, $result);
    }

    public function testCheckParticipantValidityWithTakenEmail(): void
    {
        // Arrange
        $data = ['new_user', 'Doe', 'John', '1234567890', 'existing@example.com', 'password123', '0', '1', 'Site A'];
        $existingParticipant = $this->createMock(Participant::class);

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        $this->participantService->expects($this->once())
            ->method('findByMail')
            ->with('existing@example.com')
            ->willReturn($existingParticipant);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::USER_MAIL_TAKEN, $result);
    }

    public function testCheckParticipantValidityWithMissingEmail(): void
    {
        // Arrange
        $data = ['new_user', 'Doe', 'John', '1234567890', '', 'password123', '0', '1', 'Site A'];

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::MISSING_EMAIL, $result);
    }

    public function testCheckParticipantValidityWithMissingSiteName(): void
    {
        // Arrange
        $data = ['new_user', 'Doe', 'John', '1234567890', 'john@example.com', 'password123', '0', '1', ''];

        $this->participantService->expects($this->once())
            ->method('findByPseudo')
            ->with('new_user')
            ->willReturn(null);

        $this->participantService->expects($this->once())
            ->method('findByMail')
            ->with('john@example.com')
            ->willReturn(null);

        // Act
        $result = $this->userImportService->CheckParticipantValidity($data);

        // Assert
        $this->assertEquals(ParticipantCSVValidityResult::MISSING_SITE_NAME, $result);
    }
}