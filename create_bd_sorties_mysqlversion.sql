-- Script converti pour MySQL (InnoDB, utf8mb4)
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `ETATS` (
  `no_etat` INT NOT NULL,
  `libelle` VARCHAR(30) NOT NULL,
  PRIMARY KEY (`no_etat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `SITES` (
  `no_site` INT NOT NULL,
  `nom_site` VARCHAR(30) NOT NULL,
  PRIMARY KEY (`no_site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `VILLES` (
  `no_ville` INT NOT NULL,
  `nom_ville` VARCHAR(30) NOT NULL,
  `code_postal` VARCHAR(10) NOT NULL,
  PRIMARY KEY (`no_ville`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `LIEUX` (
  `no_lieu` INT NOT NULL,
  `nom_lieu` VARCHAR(30) NOT NULL,
  `rue` VARCHAR(30),
  `latitude` DOUBLE,
  `longitude` DOUBLE,
  `villes_no_ville` INT NOT NULL,
  PRIMARY KEY (`no_lieu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PARTICIPANTS` (
  `no_participant` INT NOT NULL,
  `pseudo` VARCHAR(30) NOT NULL,
  `nom` VARCHAR(30) NOT NULL,
  `prenom` VARCHAR(30) NOT NULL,
  `telephone` VARCHAR(15),
  `mail` VARCHAR(255) NOT NULL,
  `mot_de_passe` VARCHAR(255) NOT NULL,
  `administrateur` TINYINT(1) NOT NULL,
  `actif` TINYINT(1) NOT NULL,
  `sites_no_site` INT NOT NULL,
  PRIMARY KEY (`no_participant`),
  UNIQUE KEY `participants_pseudo_uk` (`pseudo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `SORTIES` (
  `no_sortie` INT NOT NULL,
  `nom` VARCHAR(30) NOT NULL,
  `datedebut` DATETIME NOT NULL,
  `duree` INT,
  `datecloture` DATETIME NOT NULL,
  `nbinscriptionsmax` INT NOT NULL,
  `descriptioninfos` VARCHAR(500),
  `etatsortie` INT,
  `urlPhoto` VARCHAR(250),
  `organisateur` INT NOT NULL,
  `lieux_no_lieu` INT NOT NULL,
  `etats_no_etat` INT NOT NULL,
  PRIMARY KEY (`no_sortie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `INSCRIPTIONS` (
  `date_inscription` DATETIME NOT NULL,
  `sorties_no_sortie` INT NOT NULL,
  `participants_no_participant` INT NOT NULL,
  PRIMARY KEY (`sorties_no_sortie`,`participants_no_participant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contraintes FK
ALTER TABLE `INSCRIPTIONS`
  ADD CONSTRAINT `inscriptions_participants_fk` FOREIGN KEY (`participants_no_participant`)
    REFERENCES `PARTICIPANTS` (`no_participant`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `inscriptions_sorties_fk` FOREIGN KEY (`sorties_no_sortie`)
    REFERENCES `SORTIES` (`no_sortie`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `LIEUX`
  ADD CONSTRAINT `lieux_villes_fk` FOREIGN KEY (`villes_no_ville`)
    REFERENCES `VILLES` (`no_ville`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `SORTIES`
  ADD CONSTRAINT `sorties_etats_fk` FOREIGN KEY (`etats_no_etat`)
    REFERENCES `ETATS` (`no_etat`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `sorties_lieux_fk` FOREIGN KEY (`lieux_no_lieu`)
    REFERENCES `LIEUX` (`no_lieu`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `sorties_participants_fk` FOREIGN KEY (`organisateur`)
    REFERENCES `PARTICIPANTS` (`no_participant`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `PARTICIPANTS`
  ADD CONSTRAINT `participants_sites_fk` FOREIGN KEY (`sites_no_site`)
    REFERENCES `SITES` (`no_site`) ON DELETE NO ACTION ON UPDATE NO ACTION;

SET FOREIGN_KEY_CHECKS = 1;
