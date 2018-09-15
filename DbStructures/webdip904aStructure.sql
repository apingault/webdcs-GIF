-- MySQL dump 10.14  Distrib 5.5.60-MariaDB, for Linux (x86_64)
--
-- Host: webdcsdip    Database: dip
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
-- Table structure for table `RASPRPC`
--

DROP TABLE IF EXISTS `RASPRPC`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RASPRPC` (
  `timestamp` int(10) NOT NULL,
  `PRASP` float NOT NULL,
  `TRASP` float NOT NULL,
  `RHRASP` float NOT NULL,
  UNIQUE KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `RPC904`
--

DROP TABLE IF EXISTS `RPC904`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RPC904` (
  `timestamp` int(10) NOT NULL AUTO_INCREMENT,
  `GAS904_C2H2F4` float DEFAULT '0',
  `GAS904_iC4H10` float DEFAULT '0',
  `GAS904_SF6` float DEFAULT '0',
  `GAS904_TOTALFLOW` float DEFAULT '0',
  `GAS904_FLOW_WET` float DEFAULT '0',
  `GAS904_FLOW_DRY` float DEFAULT '0',
  `GAS904_MOISTURE` float DEFAULT '0',
  `GAS904_DEWPOINT` float DEFAULT '0',
  `GAS904_IR_iC4H10` float DEFAULT '0',
  `GAS904_INTERLOCK` int(11) DEFAULT '0',
  UNIQUE KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=1537004943 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `RPC904ENV`
--

DROP TABLE IF EXISTS `RPC904ENV`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RPC904ENV` (
  `timestamp` int(10) NOT NULL,
  `P904` float NOT NULL,
  `T0904` float NOT NULL,
  `RH0904` float NOT NULL,
  `T1904` float NOT NULL,
  `RH1904` float NOT NULL,
  `T2904` float NOT NULL,
  `RH2904` float NOT NULL,
  `T3904` float NOT NULL,
  `RH3904` float NOT NULL,
  `T4904` float NOT NULL,
  `RH4904` float NOT NULL,
  UNIQUE KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `attenuator`
--

DROP TABLE IF EXISTS `attenuator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attenuator` (
  `timestamp` int(11) NOT NULL,
  `AttDA` float DEFAULT NULL,
  `AttDB` float DEFAULT NULL,
  `AttDC` float DEFAULT NULL,
  `AttDEff` float DEFAULT NULL,
  `AttUA` float DEFAULT NULL,
  `AttUB` float DEFAULT NULL,
  `AttUC` float DEFAULT NULL,
  `AttUEff` float DEFAULT NULL,
  UNIQUE KEY `timestamp_2` (`timestamp`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `environmental`
--

DROP TABLE IF EXISTS `environmental`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `environmental` (
  `timestamp` int(11) NOT NULL,
  `P201` float DEFAULT NULL,
  `T201` float DEFAULT NULL,
  `RH201` float DEFAULT NULL,
  `P202` float DEFAULT NULL,
  `T202` float DEFAULT NULL,
  `RH202` float DEFAULT NULL,
  `P203` float DEFAULT NULL,
  `T203` float DEFAULT NULL,
  `RH203` float DEFAULT NULL,
  `P` float DEFAULT NULL,
  `TIN` float DEFAULT NULL,
  `TOUT` float DEFAULT NULL,
  `RHIN` float DEFAULT NULL,
  `RHOUT` float DEFAULT NULL,
  UNIQUE KEY `timestamp` (`timestamp`),
  KEY `timestamp_2` (`timestamp`),
  KEY `timestamp_3` (`timestamp`),
  KEY `timestamp_4` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gas`
--

DROP TABLE IF EXISTS `gas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gas` (
  `timestamp` int(11) NOT NULL,
  `P101` float DEFAULT NULL,
  `T101` float DEFAULT NULL,
  `RH101` float DEFAULT NULL,
  `P102` float DEFAULT NULL,
  `T102` float DEFAULT NULL,
  `RH102` float DEFAULT NULL,
  `P103` float DEFAULT NULL,
  `T103` float DEFAULT NULL,
  `RH103` float DEFAULT NULL,
  `P105` float DEFAULT NULL,
  `T105` float DEFAULT NULL,
  `RH105` float DEFAULT NULL,
  `P106` float DEFAULT NULL,
  `T106` float DEFAULT NULL,
  `RH106` float DEFAULT NULL,
  `RPC_MFC_Humidity` float DEFAULT NULL,
  `iC4H10_BINOS1` float DEFAULT NULL,
  `iC4H10_BINOS2` float DEFAULT NULL,
  `C2H2F4` float DEFAULT NULL,
  `iC4H10` float DEFAULT NULL,
  `mixture_with_water` float DEFAULT NULL,
  `mixture_without_water` float DEFAULT NULL,
  `SF6` float DEFAULT NULL,
  `RH_gas_room` float NOT NULL DEFAULT '0',
  `T_gas_room` float NOT NULL DEFAULT '0',
  UNIQUE KEY `timestamp` (`timestamp`),
  KEY `timestamp_2` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parameters`
--

DROP TABLE IF EXISTS `parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parameters` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `radmon`
--

DROP TABLE IF EXISTS `radmon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radmon` (
  `timestamp` int(11) NOT NULL,
  `D1` float DEFAULT NULL,
  `T1` float DEFAULT NULL,
  `D2` float DEFAULT NULL,
  `T2` float DEFAULT NULL,
  `D3` float DEFAULT NULL,
  `T3` float DEFAULT NULL,
  `D4` float DEFAULT NULL,
  `T4` float DEFAULT NULL,
  `D5` float DEFAULT NULL,
  `T5` float DEFAULT NULL,
  `D6` float DEFAULT NULL,
  `T6` float DEFAULT NULL,
  `D7` float DEFAULT NULL,
  `T7` float DEFAULT NULL,
  `D8` float DEFAULT NULL,
  `T8` float DEFAULT NULL,
  UNIQUE KEY `timestamp_2` (`timestamp`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `source`
--

DROP TABLE IF EXISTS `source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `source` (
  `timestamp` int(11) NOT NULL,
  `EmergencyStop` int(10) DEFAULT NULL,
  `Moving` int(10) DEFAULT NULL,
  `Siren` int(10) DEFAULT NULL,
  `SourceOFF` int(10) DEFAULT NULL,
  `SourceON` int(10) DEFAULT NULL,
  `Veto` int(10) DEFAULT NULL,
  UNIQUE KEY `timestamp_2` (`timestamp`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `dip_identifier` varchar(255) NOT NULL,
  `dip_subscription` varchar(255) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-09-15 11:49:33
