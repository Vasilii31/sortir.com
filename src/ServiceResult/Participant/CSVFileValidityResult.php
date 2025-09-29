<?php

namespace App\ServiceResult\Participant;

enum CSVFileValidityResult : string
{
    case VALID = "Valid";
    case NO_MATCH_COLUMN = "Les en-têtes de colonnes ne correspondent pas.";
    case INCORRECT_COLUMN_NUMBER = "Le nombre de colonnes du fichier est incorrect.";
}