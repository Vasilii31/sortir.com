<?php

namespace App\ServiceResult\Inscription;

enum CreateInscriptionResult : string
{
    case SUCCESS = 'success';
    case ALREADY_SUBSCRIBED = 'Utilisateur déjà inscrit';
}