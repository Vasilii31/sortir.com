<?php

namespace App\Service;

use App\ServiceResult\Participant\ParticipantCSVValidityResult;
use App\ServiceResult\Participant\CSVFileValidityResult;

class UserImportService
{
    public function __construct(private readonly ParticipantService $participantService,
        private readonly SiteService $siteService)
    {
    }

    public function CreateParticipantCSV($data): ParticipantCSVValidityResult
    {
        // Colonnes : pseudo, nom, prenom, telephone, mail, mot_de_passe, administrateur, actif, nom_du_site
        [$pseudo, $nom, $prenom, $telephone, $mail, $plainPassword, $administrateur, $actif, $site_name] = $data;

        if ($site_name) {
            $sites = $this->siteService->searchByName($site_name);
            if (count($sites) > 0) {
                //on prend le premier site trouvé
                $site = $sites[0];
            }
            else {return ParticipantCSVValidityResult::INVALID_SITE_NAME;}
        }
        else{
            return  ParticipantCSVValidityResult::INVALID_SITE_NAME;
        }

        $res = $this->participantService->createParticipant($nom, $prenom, $pseudo, $mail, $plainPassword, $telephone, $site);

        if($res == null) { return ParticipantCSVValidityResult::PARTICIPANT_CREATION_ERROR;}

        return ParticipantCSVValidityResult::SUCCESS;
    }

    public function CheckCsvValidity($data) : CSVFileValidityResult
    {
        return CSVFileValidityResult::VALID;
    }

    public function CheckParticipantValidity($data) : ParticipantCSVValidityResult
    {
        [$pseudo, $nom, $prenom, $telephone, $mail, $plainPassword, $administrateur, $actif, $site_name] = $data;

        //Verification du pseudo
        if($pseudo)
        {
            $pseudo = trim($pseudo, ' ');
            $match = $this->participantService->findByPseudo($pseudo);
            if($match != null)
            {
                return ParticipantCSVValidityResult::USER_PSEUDO_TAKEN;
            }
        }

        //verification du mail
        if($mail)
        {
            $mail = trim($mail, ' ');
            $match = $this->participantService->findByMail($mail);
            if($match != null)
            {
                return ParticipantCSVValidityResult::USER_MAIL_TAKEN;
            }
        }

        //Verification du site
        if ($site_name) {
            $sites = $this->siteService->searchByName($site_name);
            if (count($sites) > 0) {
                //on prend le premier site trouvé
                $site = $sites[0];
            }
            else {return ParticipantCSVValidityResult::INVALID_SITE_NAME;}
        }
        else{
            return  ParticipantCSVValidityResult::INVALID_SITE_NAME;
        }

        return ParticipantCSVValidityResult::SUCCESS;
    }
}