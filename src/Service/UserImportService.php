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
        $expectedHeaders = ['pseudo', 'nom', 'prenom', 'telephone', 'mail', 'mot_de_passe', 'administrateur', 'actif', 'nom_du_site'];
        if ($data !== $expectedHeaders) {
            return CSVFileValidityResult::NO_MATCH_COLUMN;
        }

        if(count($data) !== count($expectedHeaders))
        {
            return CSVFileValidityResult::INCORRECT_COLUMN_NUMBER;
        }


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
        else{ return ParticipantCSVValidityResult::MISSING_USERNAME;}

        if(!$nom)
        {
            return ParticipantCSVValidityResult::MISSING_NOM;
        }

        if(!$prenom)
        {
            return ParticipantCSVValidityResult::MISSING_PRENOM;
        }

        if($plainPassword)
        {
            if(strlen($plainPassword) < 6)
            {
                return ParticipantCSVValidityResult::INVALID_PASSWORD_LENGTH;
            }
        }else{ return ParticipantCSVValidityResult::MISSING_PASSWORD;}

        if($administrateur !== null && $administrateur !== '')
        {
            if ($administrateur !== '0' && $administrateur !== '1') {
                return ParticipantCSVValidityResult::INVALID_ADMIN_FIELD;
            }
        }else{
            return ParticipantCSVValidityResult::MISSING_ADMIN_FIELD;
        }

        if($actif !== null && $actif !== '')
        {
            if ($actif !== '0' && $actif !== '1') {
                return ParticipantCSVValidityResult::INVALID_ACTIF_FIELD;
            }
        }else{
            return ParticipantCSVValidityResult::MISSING_ACTIF_FIELD;
        }

        //verification du mail
        if($mail)
        {
            $mail = trim($mail, ' ');

            if (!filter_var($mail, FILTER_VALIDATE_EMAIL))
            {
                return ParticipantCSVValidityResult::INVALID_MAIL_FORMAT;
            }

            $match = $this->participantService->findByMail($mail);
            if($match != null)
            {
                return ParticipantCSVValidityResult::USER_MAIL_TAKEN;
            }
        }
        else{return ParticipantCSVValidityResult::MISSING_EMAIL;}

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
            return  ParticipantCSVValidityResult::MISSING_SITE_NAME;
        }

        return ParticipantCSVValidityResult::SUCCESS;
    }
}