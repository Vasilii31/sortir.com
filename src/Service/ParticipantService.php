<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantService
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ImageUploadService $imageUploadService
    ) {
    }

    public function createParticipant(
        string $nom,
        string $prenom,
        string $pseudo,
        string $mail,
        string $plainPassword,
        ?string $telephone,
        Site $site,
        ?UploadedFile $photoFile = null
    ): Participant {
        $participant = new Participant();
        $participant->setNom($nom);
        $participant->setPrenom($prenom);
        $participant->setPseudo($pseudo);
        $participant->setMail($mail);
        $participant->setTelephone($telephone);
        $participant->setSite($site);
        $participant->setActif(true);
        $participant->setAdministrateur(false);

        $hashedPassword = $this->passwordHasher->hashPassword($participant, $plainPassword);
        $participant->setMotDePasse($hashedPassword);

        if ($photoFile) {
            $photoFilename = $this->imageUploadService->upload($photoFile);
            $participant->setPhotoProfil($photoFilename);
        }

        $this->entityManager->persist($participant);
        $this->entityManager->flush();

        return $participant;
    }

    public function updateParticipant(
        Participant $participant,
        string $nom,
        string $prenom,
        string $pseudo,
        string $mail,
        ?string $telephone,
        Site $site,
        ?string $plainPassword = null,
        ?UploadedFile $photoFile = null,
        bool $deletePhoto = false
    ): Participant {
        $participant->setNom($nom);
        $participant->setPrenom($prenom);
        $participant->setPseudo($pseudo);
        $participant->setMail($mail);
        $participant->setTelephone($telephone);
        $participant->setSite($site);

        if ($plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($participant, $plainPassword);
            $participant->setMotDePasse($hashedPassword);
        }

        if ($deletePhoto && $participant->getPhotoProfil()) {
            $this->imageUploadService->delete($participant->getPhotoProfil());
            $participant->setPhotoProfil(null);
        }

        if ($photoFile) {
            $oldPhoto = $participant->getPhotoProfil();
            $photoFilename = $this->imageUploadService->upload($photoFile, $oldPhoto);
            $participant->setPhotoProfil($photoFilename);
        }

        $this->entityManager->flush();

        return $participant;
    }

    public function findByPseudo(string $pseudo): ?Participant
    {
        return $this->participantRepository->findOneBy(['pseudo' => $pseudo]);
    }

    public function findByMail(string $mail): ?Participant
    {
        return $this->participantRepository->findOneBy(['mail' => $mail]);
    }

    public function checkUniqueFields(string $pseudo, string $mail, ?int $excludeId = null): array
    {
        $errors = [];

        $existingPseudo = $this->findByPseudo($pseudo);
        if ($existingPseudo && $existingPseudo->getId() !== $excludeId) {
            $errors[] = 'Ce pseudo est déjà utilisé';
        }

        $existingMail = $this->findByMail($mail);
        if ($existingMail && $existingMail->getId() !== $excludeId) {
            $errors[] = 'Cette adresse email est déjà utilisée';
        }

        return $errors;
    }

    public function getAllParticipants(): array
    {
        return $this->participantRepository->findAll();
    }

    public function toggleAdmin(Participant $participant): void
    {
        $participant->setAdministrateur(!$participant->isAdministrateur());
        $this->entityManager->flush();
    }

    public function deleteParticipant(Participant $participant): void
    {
        if ($participant->getPhotoProfil()) {
            $this->imageUploadService->delete($participant->getPhotoProfil());
        }

        $this->entityManager->remove($participant);
        $this->entityManager->flush();
    }
}