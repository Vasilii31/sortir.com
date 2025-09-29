<?php

namespace App\ServiceResult\Participant;

enum ParticipantCSVValidityResult : string
{
    case SUCCESS = 'success';
    case INVALID_SITE_NAME = "Nom du site invalide";
    case INVALID_ADMIN_FIELD = "Le champ Administrateur ne peut prendre que la valeur 0 ou 1";
    case INVALID_ACTIF_FIELD = "Le champ Actif ne peut prendre que la valeur 0 ou 1";
    case USER_PSEUDO_TAKEN = "Nom d'utilisateur déjà utilisé";
    case USER_MAIL_TAKEN = "Adresse mail déjà utilisée";
    case PARTICIPANT_CREATION_ERROR = "Erreur à la création de l'utilisateur";
    case MISSING_USERNAME = "Nom d'utilisateur non-renseigné.";
    case MISSING_EMAIL = "Email non-renseigné.";
    case MISSING_NOM = "Nom non-renseigné.";
    case MISSING_PRENOM = "Prénom non-renseigné.";
    case MISSING_PASSWORD = "Mot de passe non-renseigné.";
    case MISSING_SITE_NAME = "Nom du site non-renseigné.";
    case MISSING_ADMIN_FIELD = "Le champ Administrateur n'est pas renseigné.";
    case MISSING_ACTIF_FIELD = "Le champ Actif n'est pas renseigné.";
    case INVALID_PASSWORD_LENGTH = "Le mot de passe doit faire minimum 6 caractères.";
    case INVALID_MAIL_FORMAT = "Le format de l'adresse email est incorrect.";
}