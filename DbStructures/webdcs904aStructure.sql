-- MySQL dump 10.14  Distrib 5.5.60-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: webdcs
-- ------------------------------------------------------
-- Server version	5.5.60-MariaDB

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
  `trolley` int(10) NOT NULL,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `daqini`
--

DROP TABLE IF EXISTS `daqini`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daqini` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `daqtype` varchar(255) DEFAULT NULL,
  `content` longtext NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `detectors`
--

DROP TABLE IF EXISTS `detectors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detectors` (
  `id` int(10) NOT NULL,
  `trolley` int(11) NOT NULL,
  `slot` int(10) NOT NULL,
  `mid` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `gap` varchar(255) NOT NULL,
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
  `chamberid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
  `chamberid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gasflow`
--

DROP TABLE IF EXISTS `gasflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gasflow` (
  `id` int(10) NOT NULL,
  `trolley` int(10) NOT NULL,
  `time` int(10) NOT NULL,
  `comments` longtext NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gasflow_gaps`
--

DROP TABLE IF EXISTS `gasflow_gaps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gasflow_gaps` (
  `id` int(10) NOT NULL,
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
  `id` int(11) NOT NULL,
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
  `daqtype` varchar(255) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hvscan_CURRENT`
--

DROP TABLE IF EXISTS `hvscan_CURRENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hvscan_CURRENT` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET latin1 NOT NULL,
  `measure_time` int(10) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
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
  `beam` int(10) NOT NULL DEFAULT '0',
  `daqtype` varchar(255) NOT NULL DEFAULT 'default',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hvscan_VOLTAGES`
--

DROP TABLE IF EXISTS `hvscan_VOLTAGES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hvscan_VOLTAGES` (
  `id` int(11) NOT NULL,
  `scanid` int(10) NOT NULL,
  `gapid` int(10) NOT NULL,
  `HVPoint` int(10) NOT NULL,
  `HV` int(10) NOT NULL,
  `masked` int(2) NOT NULL DEFAULT '0',
  `maxtriggers` int(11) DEFAULT NULL,
  `time_start` int(15) DEFAULT NULL,
  `time_end` int(15) DEFAULT NULL,
  `valid` int(1) NOT NULL DEFAULT '0'
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `testdb`
--

DROP TABLE IF EXISTS `testdb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testdb` (
  `id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trolley`
--

DROP TABLE IF EXISTS `trolley`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trolley` (
  `id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `lastaction` int(10) unsigned NOT NULL,
  `role` int(10) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-09-15 11:47:40
