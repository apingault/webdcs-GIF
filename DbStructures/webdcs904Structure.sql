-- MySQL dump 10.14  Distrib 5.5.60-MariaDB, for Linux (x86_64)
--
-- Host: webdcs904    Database: webdcs
-- ------------------------------------------------------
-- Server version	5.5.52-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `DIP`
--

DROP TABLE IF EXISTS `DIP`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DIP` (
  `id` int(11) NOT NULL,
  `timestamp` int(15) NOT NULL,
  `Atmospheric_Pressure` float NOT NULL,
  `Temp_Inside_Bunker` float NOT NULL,
  `Temp_Outside_Bunker` float NOT NULL,
  `Humidity_Inside_Bunker` float NOT NULL,
  `Humidity_Outside_Bunker` float NOT NULL,
  `attDA` int(11) NOT NULL,
  `attDB` int(11) NOT NULL,
  `attDC` int(11) NOT NULL,
  `effD` int(11) NOT NULL,
  `attUA` int(11) NOT NULL,
  `attUB` int(11) NOT NULL,
  `attUC` int(11) NOT NULL,
  `effU` int(11) NOT NULL,
  `SourceON` tinyint(1) NOT NULL,
  `SourceOFF` tinyint(1) NOT NULL,
  `EmergencyStop` tinyint(1) NOT NULL,
  `Moving` tinyint(1) NOT NULL,
  `Siren` tinyint(1) NOT NULL,
  `Veto` tinyint(1) NOT NULL,
  `RPC_MFC_Humidity` float NOT NULL,
  `TGC_CO2` float NOT NULL,
  `iC4H10_BINOS1` float NOT NULL,
  `iC4H10_BINOS2` float NOT NULL,
  `C2H2F4` float NOT NULL,
  `iC4H10` float NOT NULL,
  `mixture_with_water` float NOT NULL,
  `mixture_without_water` float NOT NULL,
  `SF6` float NOT NULL,
  `Radmon1` float NOT NULL,
  `Radmon2` float NOT NULL,
  `Radmon3` float NOT NULL,
  `Radmon4` float NOT NULL,
  `Radmon5` float NOT NULL,
  `Radmon6` float NOT NULL,
  `Radmon7` float NOT NULL,
  `Radmon8` float NOT NULL,
  `ENV_201_P` float NOT NULL,
  `ENV_201_T` float NOT NULL,
  `ENV_201_RH` float NOT NULL,
  `ENV_202_P` float NOT NULL,
  `ENV_202_T` float NOT NULL,
  `ENV_202_RH` float NOT NULL,
  `GAS_102_P` float NOT NULL,
  `GAS_102_T` float NOT NULL,
  `GAS_102_RH` float NOT NULL,
  `GAS_105_P` float NOT NULL,
  `GAS_105_T` float NOT NULL,
  `GAS_105_RH` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `DIP_publications`
--

DROP TABLE IF EXISTS `DIP_publications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DIP_publications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topicname` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `HW_CAEN`
--

DROP TABLE IF EXISTS `HW_CAEN`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `HW_CAEN` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `ipaddress` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `enabled` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `HW_CHAMBER`
--

DROP TABLE IF EXISTS `HW_CHAMBER`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `HW_CHAMBER` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `HW_FEB`
--

DROP TABLE IF EXISTS `HW_FEB`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `HW_FEB` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `chamber_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `HW_GAP`
--

DROP TABLE IF EXISTS `HW_GAP`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `HW_GAP` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `chamber_id` int(11) NOT NULL,
  `caen_id` int(11) NOT NULL,
  `caen_slot` int(11) NOT NULL,
  `caen_channel` int(11) NOT NULL,
  `active_area` float NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `HW_TDC`
--

DROP TABLE IF EXISTS `HW_TDC`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `HW_TDC` (
  `id` int(11) NOT NULL,
  `VME_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `physical_address` varchar(255) NOT NULL,
  `max_connectors` int(11) NOT NULL,
  `enabled` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `HW_VME`
--

DROP TABLE IF EXISTS `HW_VME`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `HW_VME` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `max_tdcs` int(11) NOT NULL,
  `enabled` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MONITORING`
--

DROP TABLE IF EXISTS `MONITORING`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MONITORING` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `script` varchar(255) NOT NULL,
  `arguments` varchar(255) NOT NULL,
  `notification_interval` int(10) NOT NULL,
  `notification_addresses` longtext NOT NULL,
  `status` int(1) NOT NULL,
  `status_change` int(10) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `PMON`
--

DROP TABLE IF EXISTS `PMON`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PMON` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `script` varchar(255) NOT NULL,
  `arguments` varchar(255) NOT NULL,
  `deadband` int(10) NOT NULL,
  `status` int(1) NOT NULL,
  `true_status` int(10) NOT NULL,
  `status_change` int(10) NOT NULL,
  `last_update` int(10) NOT NULL,
  `enabled` int(1) NOT NULL,
  `comment` longtext NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `PMON_LOG`
--

DROP TABLE IF EXISTS `PMON_LOG`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PMON_LOG` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pmon_id` int(10) NOT NULL,
  `time` int(10) NOT NULL,
  `message` longtext NOT NULL,
  `status` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `RES_OHMIC`
--

DROP TABLE IF EXISTS `RES_OHMIC`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RES_OHMIC` (
  `id` int(11) NOT NULL,
  `scanid` int(11) NOT NULL,
  `gapid` int(11) NOT NULL,
  `offset` float NOT NULL,
  `offset_err` float NOT NULL,
  `slope` float NOT NULL,
  `slope_err` float NOT NULL,
  `plot` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `RES_QINT`
--

DROP TABLE IF EXISTS `RES_QINT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RES_QINT` (
  `id` int(11) NOT NULL,
  `time` int(15) NOT NULL,
  `qint_caen_raw` float NOT NULL,
  `qint_caen_corr` float NOT NULL,
  `qint_adc_raw` float NOT NULL,
  `qint_adc_corr` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `RES_RESISTIVITY`
--

DROP TABLE IF EXISTS `RES_RESISTIVITY`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RES_RESISTIVITY` (
  `id` int(11) NOT NULL,
  `scan_id` int(11) NOT NULL,
  `gap_id` int(11) NOT NULL,
  `onset` float NOT NULL,
  `onset_err` float NOT NULL,
  `offset` float NOT NULL,
  `offset_err` float NOT NULL,
  `slope` float NOT NULL,
  `slope_err` float NOT NULL,
  `plot` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chambers`
--

DROP TABLE IF EXISTS `chambers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chambers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `trolley` int(10) NOT NULL DEFAULT '0',
  `slot` int(10) NOT NULL,
  `gaps` int(10) NOT NULL,
  `partitions` int(10) NOT NULL,
  `strips` int(10) NOT NULL,
  `area` float NOT NULL,
  `dimensions` longtext NOT NULL,
  `mapping` longtext NOT NULL,
  `daq_type` varchar(255) NOT NULL DEFAULT 'DEFAULT',
  `HV_WP` float NOT NULL,
  `HV_STBY` float NOT NULL,
  `status` int(10) NOT NULL,
  `enabled` int(10) NOT NULL,
  `comments` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `daqini`
--

DROP TABLE IF EXISTS `daqini`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daqini` (
  `id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `daqtype` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `detectors`
--

DROP TABLE IF EXISTS `detectors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detectors` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `mid` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `CAEN_channel` int(10) NOT NULL,
  `CAEN_slot` int(10) NOT NULL,
  `comments` longtext NOT NULL,
  `process` int(10) NOT NULL DEFAULT '0',
  `DAQ` int(2) DEFAULT NULL,
  `RCURR` int(2) NOT NULL DEFAULT '0',
  `ADC_channel` int(10) DEFAULT NULL,
  `ADC_slot` int(10) DEFAULT NULL,
  `ADC_resistor` varchar(255) DEFAULT NULL,
  `chamber` varchar(255) NOT NULL,
  `enabled` int(2) NOT NULL DEFAULT '1',
  `stability` int(2) DEFAULT NULL,
  `i0` float NOT NULL DEFAULT '99',
  `area` float DEFAULT NULL,
  `status` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gaps`
--

DROP TABLE IF EXISTS `gaps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gaps` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `CAEN_channel` int(10) NOT NULL,
  `CAEN_slot` int(10) NOT NULL,
  `comments` longtext NOT NULL,
  `process` int(10) NOT NULL DEFAULT '0',
  `enabled` int(2) NOT NULL DEFAULT '1',
  `i0` float NOT NULL DEFAULT '99',
  `area` float DEFAULT NULL,
  `status` int(10) NOT NULL DEFAULT '0',
  `chamberid` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gapsOLD`
--

DROP TABLE IF EXISTS `gapsOLD`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gapsOLD` (
  `id` int(10) NOT NULL,
  `trolley` int(11) NOT NULL,
  `slot` int(10) NOT NULL,
  `mid` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `CAEN_channel` int(10) NOT NULL,
  `CAEN_slot` int(10) NOT NULL,
  `comments` longtext NOT NULL,
  `process` int(10) NOT NULL DEFAULT '0',
  `DAQ` int(2) NOT NULL,
  `RCURR` int(2) NOT NULL DEFAULT '0',
  `ADC_channel` int(10) DEFAULT NULL,
  `ADC_slot` int(10) DEFAULT NULL,
  `ADC_resistor` varchar(255) DEFAULT NULL,
  `chamber` varchar(255) NOT NULL,
  `enabled` int(2) NOT NULL DEFAULT '1',
  `stability` int(2) NOT NULL,
  `i0` float NOT NULL DEFAULT '99',
  `area` float DEFAULT NULL,
  `status` int(10) NOT NULL DEFAULT '0',
  `hv_wp` int(10) NOT NULL,
  `hv_standby` int(10) NOT NULL,
  `chamberid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gasflow`
--

DROP TABLE IF EXISTS `gasflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gasflow` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `time` int(10) NOT NULL,
  `comments` longtext NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gasflow_gaps`
--

DROP TABLE IF EXISTS `gasflow_gaps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gasflow_gaps` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `gasflowid` int(10) NOT NULL,
  `detectorid` int(10) NOT NULL,
  `gasflow` float NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gasparameters`
--

DROP TABLE IF EXISTS `gasparameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gasparameters` (
  `id` int(11) NOT NULL,
  `flow` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hvscan`
--

DROP TABLE IF EXISTS `hvscan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hvscan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_start` int(11) NOT NULL,
  `time_end` int(11) DEFAULT NULL,
  `type` varchar(255) CHARACTER SET latin1 NOT NULL,
  `waiting_time` int(10) NOT NULL,
  `measure_time` int(10) NOT NULL DEFAULT '5',
  `measure_intval` int(10) NOT NULL DEFAULT '5',
  `comments` longtext CHARACTER SET latin1,
  `maxHVPoints` int(10) NOT NULL,
  `status` int(10) NOT NULL,
  `RPC_mode` varchar(255) NOT NULL,
  `lastHV` int(10) NOT NULL DEFAULT '0',
  `label` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=983 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hvscan_CURRENT`
--

DROP TABLE IF EXISTS `hvscan_CURRENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hvscan_CURRENT` (
  `id` int(11) NOT NULL,
  `type` varchar(255) CHARACTER SET latin1 NOT NULL,
  `measure_time` int(10) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hvscan_DAQ`
--

DROP TABLE IF EXISTS `hvscan_DAQ`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hvscan_DAQ` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET latin1 NOT NULL,
  `trigger_mode` varchar(255) DEFAULT NULL,
  `min_time` int(10) NOT NULL DEFAULT '0',
  `daqtype` varchar(255) NOT NULL DEFAULT 'default',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=981 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hvscan_VOLTAGES`
--

DROP TABLE IF EXISTS `hvscan_VOLTAGES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hvscan_VOLTAGES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scanid` int(10) NOT NULL,
  `gapid` int(10) NOT NULL,
  `HVPoint` int(10) NOT NULL,
  `HV` int(10) NOT NULL,
  `masked` int(2) NOT NULL DEFAULT '0',
  `maxtriggers` int(11) DEFAULT NULL,
  `time_start` int(15) DEFAULT NULL,
  `time_end` int(15) DEFAULT NULL,
  `valid` int(10) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `scanid` (`scanid`),
  KEY `detectorid` (`gapid`)
) ENGINE=InnoDB AUTO_INCREMENT=31396 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hvscan_config`
--

DROP TABLE IF EXISTS `hvscan_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hvscan_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `start1` int(10) NOT NULL DEFAULT '1000',
  `step1` int(10) NOT NULL DEFAULT '500',
  `stop1` int(10) NOT NULL DEFAULT '8000',
  `step2` int(10) NOT NULL DEFAULT '100',
  `stop2` int(10) NOT NULL DEFAULT '9000',
  `step3` int(10) NOT NULL DEFAULT '100',
  `stop3` int(10) NOT NULL DEFAULT '1000',
  `mtime` int(10) NOT NULL DEFAULT '60',
  `wtime` int(10) NOT NULL DEFAULT '600',
  `step` int(10) NOT NULL DEFAULT '5',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `comments` longtext NOT NULL,
  `meteo_file` varchar(255) NOT NULL,
  `ref_pressure` int(11) NOT NULL,
  `hvscan_pid` int(10) DEFAULT NULL,
  `ref_temperature` double DEFAULT '20',
  `meteoLastPoint` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `physics_flags`
--

DROP TABLE IF EXISTS `physics_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physics_flags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flagname` varchar(255) NOT NULL,
  `runids` varchar(255) NOT NULL,
  `comments` longtext NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `position`
--

DROP TABLE IF EXISTS `position`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `position` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `trolley_id` int(5) NOT NULL,
  `time` varchar(255) NOT NULL,
  `position` varchar(200) NOT NULL,
  `coordinate_x` varchar(255) NOT NULL,
  `coordinate_z` varchar(255) NOT NULL,
  `comment` varchar(1000) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `setting` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stability`
--

DROP TABLE IF EXISTS `stability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stability` (
  `id` int(10) NOT NULL,
  `time_start` int(10) NOT NULL,
  `time_end` int(10) DEFAULT NULL,
  `comments` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `RPC_mode` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stability_VOLTAGES`
--

DROP TABLE IF EXISTS `stability_VOLTAGES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stability_VOLTAGES` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `stabilityid` int(10) NOT NULL,
  `detectorid` int(10) NOT NULL,
  `HV` int(10) NOT NULL,
  `cfgKEY` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `testdb`
--

DROP TABLE IF EXISTS `testdb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testdb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trolley`
--

DROP TABLE IF EXISTS `trolley`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trolley` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `lastaction` int(10) unsigned NOT NULL,
  `role` int(10) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voltages`
--

DROP TABLE IF EXISTS `voltages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voltages` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `hvscan_id` int(10) NOT NULL,
  `detector_id` int(10) NOT NULL,
  `HV1` int(10) NOT NULL,
  `HV2` int(10) DEFAULT NULL,
  `HV3` int(10) DEFAULT NULL,
  `HV4` int(10) DEFAULT NULL,
  `HV5` int(10) DEFAULT NULL,
  `HV6` int(10) DEFAULT NULL,
  `HV7` int(10) DEFAULT NULL,
  `HV8` int(10) DEFAULT NULL,
  `HV9` int(10) DEFAULT NULL,
  `HV10` int(10) DEFAULT NULL,
  `HV11` int(10) DEFAULT NULL,
  `HV12` int(10) DEFAULT NULL,
  `HV13` int(10) DEFAULT NULL,
  `HV14` int(10) DEFAULT NULL,
  `HV15` int(10) DEFAULT NULL,
  `HV16` int(10) DEFAULT NULL,
  `HV17` int(10) DEFAULT NULL,
  `HV18` int(10) DEFAULT NULL,
  `HV19` int(10) DEFAULT NULL,
  `HV20` int(10) DEFAULT NULL,
  `threshold` int(10) NOT NULL,
  `HV1_mask` int(2) DEFAULT '0',
  `HV2_mask` int(2) DEFAULT '0',
  `HV3_mask` int(2) DEFAULT '0',
  `HV4_mask` int(2) DEFAULT '0',
  `HV5_mask` int(2) DEFAULT '0',
  `HV6_mask` int(2) DEFAULT '0',
  `HV7_mask` int(2) DEFAULT '0',
  `HV8_mask` int(2) DEFAULT '0',
  `HV9_mask` int(2) DEFAULT '0',
  `HV10_mask` int(2) DEFAULT '0',
  `HV11_mask` int(2) DEFAULT '0',
  `HV12_mask` int(2) DEFAULT '0',
  `HV13_mask` int(2) DEFAULT '0',
  `HV14_mask` int(2) DEFAULT '0',
  `HV15_mask` int(2) DEFAULT '0',
  `HV16_mask` int(2) DEFAULT '0',
  `HV17_mask` int(2) DEFAULT '0',
  `HV18_mask` int(2) DEFAULT '0',
  `HV19_mask` int(2) DEFAULT '0',
  `HV20_mask` int(2) DEFAULT '0',
  `masked` int(2) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1777 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voltagesBCK`
--

DROP TABLE IF EXISTS `voltagesBCK`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voltagesBCK` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `hvscan_id` int(10) NOT NULL,
  `detector_id` int(10) NOT NULL,
  `HV1` int(10) NOT NULL,
  `HV2` int(10) DEFAULT NULL,
  `HV3` int(10) DEFAULT NULL,
  `HV4` int(10) DEFAULT NULL,
  `HV5` int(10) DEFAULT NULL,
  `HV6` int(10) DEFAULT NULL,
  `HV7` int(10) DEFAULT NULL,
  `HV8` int(10) DEFAULT NULL,
  `HV9` int(10) DEFAULT NULL,
  `HV10` int(10) DEFAULT NULL,
  `HV11` int(10) DEFAULT NULL,
  `HV12` int(10) DEFAULT NULL,
  `HV13` int(10) DEFAULT NULL,
  `HV14` int(10) DEFAULT NULL,
  `HV15` int(10) DEFAULT NULL,
  `HV16` int(10) DEFAULT NULL,
  `HV17` int(10) DEFAULT NULL,
  `HV18` int(10) DEFAULT NULL,
  `HV19` int(10) DEFAULT NULL,
  `HV20` int(10) DEFAULT NULL,
  `threshold` int(10) NOT NULL,
  `HV1_mask` int(2) DEFAULT '0',
  `HV2_mask` int(2) DEFAULT '0',
  `HV3_mask` int(2) DEFAULT '0',
  `HV4_mask` int(2) DEFAULT '0',
  `HV5_mask` int(2) DEFAULT '0',
  `HV6_mask` int(2) DEFAULT '0',
  `HV7_mask` int(2) DEFAULT '0',
  `HV8_mask` int(2) DEFAULT '0',
  `HV9_mask` int(2) DEFAULT '0',
  `HV10_mask` int(2) DEFAULT '0',
  `HV11_mask` int(2) DEFAULT '0',
  `HV12_mask` int(2) DEFAULT '0',
  `HV13_mask` int(2) DEFAULT '0',
  `HV14_mask` int(2) DEFAULT '0',
  `HV15_mask` int(2) DEFAULT '0',
  `HV16_mask` int(2) DEFAULT '0',
  `HV17_mask` int(2) DEFAULT '0',
  `HV18_mask` int(2) DEFAULT '0',
  `HV19_mask` int(2) DEFAULT '0',
  `HV20_mask` int(2) DEFAULT '0',
  `masked` int(2) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1777 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-09-15 11:48:47
