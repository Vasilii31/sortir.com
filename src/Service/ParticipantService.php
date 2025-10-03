<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\ParticipantRepository;
use App\Repository\InscriptionRepository;
use App\Repository\SortieRepository;
use App\Repository\EtatRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantService
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly InscriptionRepository $inscriptionRepository,
        private readonly SortieRepository $sortieRepository,
        private readonly EtatRepository $etatRepository,
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

        $this->participantRepository->save($participant);

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

        $this->participantRepository->save($participant);

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

    public function find(int $id): ?Participant
    {
        return $this->participantRepository->find($id);
    }

    public function toggleAdmin(Participant $participant): void
    {
        $participant->setAdministrateur(!$participant->isAdministrateur());
        $this->participantRepository->save($participant);
    }

    public function deleteParticipant(Participant $participant): void
    {
        if ($participant->getPhotoProfil()) {
            $this->imageUploadService->delete($participant->getPhotoProfil());
        }
        $this->participantRepository->remove($participant);
    }

    public function toggleActif(Participant $participant): void
    {
        $wasActif = $participant->isActif();
        $participant->setActif(!$wasActif);

        // Si on désactive le participant, nettoyer ses engagements futurs
        if ($wasActif && !$participant->isActif()) {
            $this->cleanupParticipantEngagements($participant);
        }

        $this->participantRepository->save($participant);
    }

    public function cleanupParticipantEngagements(Participant $participant): void
    {
        $now = new \DateTime();

        // Supprimer les inscriptions aux sorties futures ou en cours
        $futureInscriptions = $this->inscriptionRepository->findFutureOrOngoingByParticipant($participant);
        if (!empty($futureInscriptions)) {
            $this->inscriptionRepository->removeInscriptions($futureInscriptions);
        }

        // Pour les sorties organisées : les supprimer si futures, les marquer comme annulées si en cours
        $organizedSorties = $this->sortieRepository->findFutureOrOngoingByOrganizer($participant);

        $sortiesASupprimer = [];
        foreach ($organizedSorties as $sortie) {
            // Si la sortie n'a pas encore commencé, on peut la supprimer
            if ($sortie->getDatedebut() > $now) {
                $sortiesASupprimer[] = $sortie;
            } else {
                // Si la sortie est en cours, la marquer comme annulée
                $etatAnnule = $this->etatRepository->findOneBy(['libelle' => 'Annulée']);
                if ($etatAnnule) {
                    $sortie->setEtat($etatAnnule);
                    $this->sortieRepository->save($sortie);
                }
            }
        }

        if (!empty($sortiesASupprimer)) {
            $this->sortieRepository->removeSorties($sortiesASupprimer);
        }
    }
}