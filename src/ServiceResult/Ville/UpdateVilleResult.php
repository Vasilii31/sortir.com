<?php

namespace App\ServiceResult\Ville;

enum UpdateVilleResult : string
{
    case SUCCESS = 'success';
    //case NAME_ALREADY_USED = 'name_already_used';
    case INVALID_POSTCODE = 'invalid_post_code';
}