SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP SCHEMA IF EXISTS `captures` ;
CREATE SCHEMA IF NOT EXISTS `captures` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `captures` ;

-- -----------------------------------------------------
-- Table `captures`.`wlan`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `captures`.`wlan` ;

CREATE TABLE IF NOT EXISTS `captures`.`wlan` (
  `ssid` VARCHAR(45) NOT NULL COMMENT 'wlan_mgt.ssid',
  `bssid` VARCHAR(45) NULL COMMENT 'wlan.bssid',
  `supported_rates` VARCHAR(125) NULL COMMENT 'wlan_mgt.supported_rates',
  `encryption` VARCHAR(45) NULL COMMENT 'wlan_mgt.rsn.akms.type==2 (wep/psk)\nOR\npacket.protocol=eapol (wpa/wpa2)',
  `ap_address` VARCHAR(45) NULL COMMENT 'the hw_address of the device\nif it sends beacons',
  PRIMARY KEY (`ssid`),
  INDEX `fk_wlan_device1_idx` (`ap_address` ASC),
  CONSTRAINT `fk_wlan_device1`
    FOREIGN KEY (`ap_address`)
    REFERENCES `captures`.`device` (`hw_address`)
    ON DELETE SET NULL
    ON UPDATE SET NULL)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `captures`.`device`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `captures`.`device` ;

CREATE TABLE IF NOT EXISTS `captures`.`device` (
  `hw_address` VARCHAR(45) NOT NULL,
  `hw_addr_res` VARCHAR(45) NULL COMMENT 'eth.addr_resolved (show)',
  `ip_address` VARCHAR(45) NULL COMMENT 'ip.src/ip.dst (set through hw-addr)',
  `is_router` TINYINT(1) NULL DEFAULT FALSE COMMENT 'if dest=ff.ff.ff ...\n(mark as router if has sent beacon)',
  `wlan_assoc` VARCHAR(45) NULL COMMENT 'determine the ssid of the wlan thrpugh the wlan_mgt field',
  PRIMARY KEY (`hw_address`),
  INDEX `fk_device_wlan1_idx` (`wlan_assoc` ASC),
  CONSTRAINT `fk_device_wlan1`
    FOREIGN KEY (`wlan_assoc`)
    REFERENCES `captures`.`wlan` (`ssid`)
    ON DELETE SET NULL
    ON UPDATE SET NULL)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `captures`.`packet`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `captures`.`packet` ;

CREATE TABLE IF NOT EXISTS `captures`.`packet` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `time_captured` VARCHAR(45) NOT NULL COMMENT 'timestamp',
  `num` INT NULL COMMENT 'num',
  `packet_size` INT NULL COMMENT 'frame.len',
  `protocols` VARCHAR(45) NULL COMMENT 'frame.protocols',
  `signal_strength` INT NULL COMMENT 'wlan_radio.signal_dbm',
  `rate` INT NULL COMMENT 'wlan_radio.data_rate',
  `channel` INT(11) NULL COMMENT 'wlan_radio.channel',
  `type` VARCHAR(45) NULL COMMENT 'wlan.fc.type_subtype',
  `source_hw_address` VARCHAR(45) NULL COMMENT 'wlan.sa',
  `dest_hw_address` VARCHAR(45) NULL COMMENT 'wlan.da ',
  `ssid` VARCHAR(45) NULL COMMENT 'wlan_mgt.ssid',
  `unprotected` TINYINT(1) NULL COMMENT 'wlan.fc.type=2 && wlan.fc.protected=0',
  `src_port` INT NULL COMMENT 'tcp.srcport',
  `dst_port` INT NULL COMMENT 'tcp.dstport',
  `tcp_window_size` INT NULL COMMENT 'tcp.window_size',
  `tcp_lost_prev_segment` TINYINT(1) NULL COMMENT 'if exists (tcp.analysis.lost_segment)',
  `tcp_bytes_in_flight` INT NULL COMMENT 'tcp.analysis.bytes_in_flight',
  `http_response_dt` VARCHAR(45) NULL COMMENT 'http.time',
  PRIMARY KEY (`id`),
  INDEX `fk_packet_device_idx` (`source_hw_address` ASC),
  INDEX `fk_packet_device1_idx` (`dest_hw_address` ASC),
  INDEX `fk_packet_wlan1_idx` (`ssid` ASC),
  UNIQUE INDEX `time_captured_UNIQUE` (`time_captured` ASC),
  CONSTRAINT `fk_packet_device`
    FOREIGN KEY (`source_hw_address`)
    REFERENCES `captures`.`device` (`hw_address`)
    ON DELETE SET NULL
    ON UPDATE SET NULL,
  CONSTRAINT `fk_packet_device1`
    FOREIGN KEY (`dest_hw_address`)
    REFERENCES `captures`.`device` (`hw_address`)
    ON DELETE SET NULL
    ON UPDATE SET NULL,
  CONSTRAINT `fk_packet_wlan1`
    FOREIGN KEY (`ssid`)
    REFERENCES `captures`.`wlan` (`ssid`)
    ON DELETE SET NULL
    ON UPDATE SET NULL)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
