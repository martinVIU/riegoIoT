-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.27-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para sensorestfg
DROP DATABASE IF EXISTS `sensorestfg`;
CREATE DATABASE IF NOT EXISTS `sensorestfg` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `sensorestfg`;

-- Volcando estructura para tabla sensorestfg.cameras
DROP TABLE IF EXISTS `cameras`;
CREATE TABLE IF NOT EXISTS `cameras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idSensor` varchar(10) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `file` varchar(50) DEFAULT NULL,
  `idPlot` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sensorestfg.plots
DROP TABLE IF EXISTS `plots`;
CREATE TABLE IF NOT EXISTS `plots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idPlot` varchar(20) NOT NULL,
  `geolocation` varchar(60) NOT NULL,
  `idUser` int(11) DEFAULT NULL,
  `idINE` varchar(50) DEFAULT NULL,
  `lastreading` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sensorestfg.readings
DROP TABLE IF EXISTS `readings`;
CREATE TABLE IF NOT EXISTS `readings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idSensor` varchar(20) NOT NULL,
  `value1` varchar(10) DEFAULT NULL,
  `value2` float DEFAULT NULL,
  `idPlot` int(11) NOT NULL DEFAULT 0,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1741 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='valor1=humedad/encendido-apagado; valor2=humedad2/flujo';

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sensorestfg.sensortypes
DROP TABLE IF EXISTS `sensortypes`;
CREATE TABLE IF NOT EXISTS `sensortypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `letter` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `dsc` char(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sensorestfg.statevalves
DROP TABLE IF EXISTS `statevalves`;
CREATE TABLE IF NOT EXISTS `statevalves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idPlot` varchar(50) DEFAULT NULL,
  `idSensor` varchar(50) DEFAULT NULL,
  `status` varchar(1) DEFAULT NULL,
  `tActiv` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sensorestfg.timer
DROP TABLE IF EXISTS `timer`;
CREATE TABLE IF NOT EXISTS `timer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idSensor` varchar(50) NOT NULL,
  `idPlot` int(11) NOT NULL DEFAULT 0,
  `0` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `10` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `20` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `30` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `40` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `50` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `60` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `70` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `80` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `90` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `100` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `110` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `120` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `130` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `140` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `150` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `160` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `170` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `180` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `190` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `200` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `210` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `220` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `230` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `240` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `250` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `260` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `270` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `280` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `290` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `300` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `310` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `320` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `330` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `340` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `350` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `360` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `370` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `380` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `390` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `400` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `410` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `420` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `430` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `440` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `450` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `460` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `470` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `480` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `490` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `500` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `510` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `520` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `530` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `540` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `550` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `560` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `570` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `580` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `590` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `600` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `610` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `620` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `630` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `640` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `650` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `660` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `670` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `680` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `690` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `700` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `710` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `720` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `730` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `740` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `750` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `760` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `770` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `780` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `790` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `800` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `810` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `820` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `830` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `840` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `850` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `860` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `870` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `880` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `890` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `900` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `910` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `920` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `930` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `940` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `950` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `960` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `970` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `980` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `990` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1000` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1010` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1020` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1030` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1040` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1050` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1060` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1070` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1080` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1090` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1100` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1110` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1120` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1130` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1140` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1150` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1160` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1170` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1180` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1190` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1200` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1210` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1220` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1230` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1240` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1250` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1260` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1270` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1280` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1290` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1300` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1310` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1320` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1330` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1340` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1350` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1360` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1370` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1380` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1390` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1400` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1410` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1420` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1430` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  `1440` int(1) unsigned zerofill NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sensorestfg.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `level` int(11) NOT NULL,
  `mail` varchar(50) NOT NULL,
  `pass` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
