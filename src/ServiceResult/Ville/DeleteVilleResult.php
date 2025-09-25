<?php

namespace App\ServiceResult\Ville;
enum DeleteVilleResult : string
{
    case SUCCESS = 'success';
    case VILLE_IN_USE = 'ville_in_use';
}