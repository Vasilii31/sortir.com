<?php

namespace App\Service;

use App\Entity\Ville;
use App\Repository\VilleRepository;
use App\ServiceResult\Ville\DeleteVilleResult;
use App\ServiceResult\Ville\UpdateVilleResult;

class VilleService
{
    public function __construct(private readonly VilleRepository $villeRepository)
    {}

    /**
     * Retourne tous les lieux
     */
    public function getAllVilles(): array
    {
        return $this->villeRepository->findAll();
    }

    public function createVille(string $nomVille, string $codePostal) : Ville
    {
        $ville = new Ville();
        $ville->setNomVille($nomVille);
        $ville->setCodePostal($codePostal);

        $this->villeRepository->save($ville);

        return $ville;
    }

    public function deleteVIlle(Ville $ville) : DeleteVilleResult
    {
        if(count($ville->getLieux()) > 0) {
            return DeleteVilleResult::VILLE_IN_USE;
        }

        $this->villeRepository->remove($ville);

        return DeleteVilleResult::SUCCESS;
    }

    public function UpdateVille(Ville $ville, string $newNom, string $newCodePostal) : UpdateVilleResult
    {
        //logique métier


        $ville->setNomVille($newNom);
        $ville->setCodePostal($newCodePostal);
        $this->villeRepository->save($ville);

        return UpdateVilleResult::SUCCESS;
    }

//    public function findFilteredVilles(string $nomRecherche): array
//    {
//        return $this->villeRepository->FindByFilter($nomRecherche);
//    }

    public function searchByName(string $term): array
    {
        return $this->villeRepository->createQueryBuilder('s')
            ->where('s.nom_ville LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->getQuery()
            ->getResult();
    }
}