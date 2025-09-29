<?php

namespace App\ServiceResult\Participant;

enum ParticipantCSVValidityResult : string
{
    case SUCCESS = 'success';
    case INVALID_SITE_NAME = "Nom du site invalide";
    case USER_PSEUDO_TAKEN = "Nom d'utilisateur déjà utilisé";
    case USER_MAIL_TAKEN = "Adresse mail déjà utilisée";
    case PARTICIPANT_CREATION_ERROR = "Erreur à la création de l'utilisateur";
}