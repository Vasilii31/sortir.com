<?php

namespace App\ServiceResult\Site;

enum UpdateSiteResult : string
{
    case SUCCESS = 'success';
    case NAME_ALREADY_USED = 'name_already_used';

}