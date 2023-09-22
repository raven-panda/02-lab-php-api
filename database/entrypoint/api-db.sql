SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- PHP access to the database : User privileges
-- -----------------------------------------------------
REVOKE ALL PRIVILEGES ON `api-db`.* FROM 'api'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `api-db`.* TO 'api'@'%';
FLUSH PRIVILEGES;

-- -----------------------------------------------------
-- Schema api-db
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `api-db` DEFAULT CHARACTER SET utf8 ;
USE `api-db` ;

-- -----------------------------------------------------
-- Table `api-db`.`categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `api-db`.`categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `api-db`.`technologies`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `api-db`.`technologies` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `icon` LONGBLOB NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `api-db`.`cat_tech`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `api-db`.`cat_tech` (
  `cat_id` INT NOT NULL,
  `tech_id` INT NOT NULL,
  INDEX `fk_cat_tech_cat_idx` (`cat_id` ASC) VISIBLE,
  INDEX `fk_cat_tech_tech1_idx` (`tech_id` ASC) VISIBLE,
  CONSTRAINT `fk_cat_tech_categories`
    FOREIGN KEY (`cat_id`)
    REFERENCES `api-db`.`categories` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_cat_tech_tech1`
    FOREIGN KEY (`tech_id`)
    REFERENCES `api-db`.`technologies` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;