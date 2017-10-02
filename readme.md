# PHPBB main Extension on sc.fr


**needs to be in ext/scfr/main**

CREATE TABLE `starcitisql`.`scfr_partners_group` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `group_id` MEDIUMINT(6) UNSIGNED NULL,
  `forum_id` MEDIUMINT(6) NULL,
  `twitch` VARCHAR(45) NULL,
  `youtube` VARCHAR(45) NULL,
  `web` VARCHAR(255) NULL,
  `discord` VARCHAR(255) NULL,
  `org` VARCHAR(45) NULL,

  PRIMARY KEY (`id`),
  UNIQUE INDEX `group_id_UNIQUE` (`group_id` ASC))
ENGINE = InnoDB;
