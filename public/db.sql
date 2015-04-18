
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP DATABASE IF EXISTS `lakhelper`;
CREATE DATABASE IF NOT EXISTS `lakhelper` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `lakhelper`;

CREATE TABLE `alliance` (
  `allianceId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `points` int(11) NOT NULL,
  `versionId` int(11) NOT NULL,
  UNIQUE KEY `allianceid` (`allianceId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `alliance_history` (
  `allianceId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `points` int(11) NOT NULL,
  `versionId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `game` (
  `gameId` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `loginId` int(11) NOT NULL,
  `world` varchar(5) NOT NULL,
  `worldId` int(11) NOT NULL,
  `playerID` int(11) NOT NULL,
  `sessionID` varchar(255) NOT NULL,
  `playerHash` varchar(255) NOT NULL,
  PRIMARY KEY (`gameId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE `habitat` (
  `habitatId` int(11) NOT NULL,
  `playerId` int(11) NOT NULL,
  `mapX` int(11) NOT NULL,
  `mapY` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `points` int(11) NOT NULL,
  `versionId` int(11) NOT NULL,
  UNIQUE KEY `habitatId` (`habitatId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `habitat_history` (
  `habitatId` int(11) NOT NULL,
  `playerId` int(11) NOT NULL,
  `mapX` int(11) NOT NULL,
  `mapY` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `points` int(11) NOT NULL,
  `versionId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `player` (
  `playerId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `points` int(11) NOT NULL,
  `isOnVacation` tinyint(1) NOT NULL,
  `vacationStartDate` datetime DEFAULT NULL,
  `allianceId` int(11) NOT NULL,
  `alliancePermission` int(11) NOT NULL,
  `versionId` int(11) NOT NULL,
  UNIQUE KEY `playerId` (`playerId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `player_history` (
  `playerId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `points` int(11) NOT NULL,
  `isOnVacation` tinyint(1) NOT NULL,
  `vacationStartDate` datetime DEFAULT NULL,
  `allianceId` int(11) NOT NULL,
  `alliancePermission` int(11) NOT NULL,
  `versionId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `start_transit` (
  `transitId` int(11) NOT NULL AUTO_INCREMENT,
  `gameId` int(11) NOT NULL,
  `startDate` datetime NOT NULL,
  `sourceHabitatId` int(11) NOT NULL,
  `destinationHabitatId` int(11) NOT NULL,
  `resourceDictionary` text NOT NULL,
  `unitDictionary` text NOT NULL,
  `transitType` enum('2') NOT NULL,
  `errorCount` int(11) NOT NULL,
  PRIMARY KEY (`transitId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `version` (
  `versionId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `source` enum('alliance','player') NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`versionId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
