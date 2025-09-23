<?php

namespace App\ServiceResult\Site;

enum DeleteSiteResult : string
{
    case SUCCESS = 'success';
    case SITE_IN_USE = 'site_in_use';
}