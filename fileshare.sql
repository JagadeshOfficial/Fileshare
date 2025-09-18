-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: fileshare
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_password_resets`
--

DROP TABLE IF EXISTS `admin_password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_password_resets`
--

LOCK TABLES `admin_password_resets` WRITE;
/*!40000 ALTER TABLE `admin_password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `aadhaar` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_picture` varchar(255) DEFAULT 'default.jpg',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (2,'Anu radha a','anuradha123@gmail.com','8790055638','375747770709','$2y$12$eZ1n.yC2Nf8Ksss9pkLD7u7CD/GvZ280392nV200pAcdblrcwIvEu','2025-04-25 13:25:02','Uploads/2_Signature.jpg'),(3,'Jagadeswararao','jagadeswararaovana@gmail.com','8790055638','123223432345','$2y$12$hBsDLM0a4HPA/7tAlDE2r.vS.7RWM0q7qU8tzLp.DIpENxigO1Leu','2025-04-28 04:43:05','Uploads/3_1746606674_JAGADESH.jpg'),(6,'Jagadeswararao','jagadeshvanaoffical@gmail.com','8790055638','123234345433','$2y$12$i.a8tjyeDpvFtbTdd.gVuei3dLoBdW54LF8Dmy52F6aLLah.1gYq6','2025-04-28 05:03:48','default.jpg');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `admin_id` int NOT NULL,
  `user_id` int NOT NULL,
  `uploaded_at` datetime NOT NULL,
  `is_deleted` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`),
  CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES (20,'Balakrishna_Resume.pdf',NULL,2,2,'2025-09-15 14:33:54',0);
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `file_size` int NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
INSERT INTO `files` VALUES (12,'Prasad_Resume.pdf',49325,'pdf','2025-05-06 07:06:31',1),(13,'Jagadesh-Resume.pdf',130805,'pdf','2025-05-07 06:12:38',1),(14,'JagadeshVanaDA_Resume.pdf',127736,'pdf','2025-09-15 08:07:15',1),(15,'Balakrishna_Resume.pdf',524615,'pdf','2025-09-15 09:03:01',1),(16,'AnuradhaResume.pdf',151201,'pdf','2025-09-17 04:02:02',1),(17,'AppointmentReceipt.pdf',192148,'pdf','2025-09-17 04:07:45',1),(18,'AnuradhaResume.pdf',151201,'pdf','2025-09-17 04:13:44',1),(19,'Jagadesh_Resume.pdf',115837,'pdf','2025-09-17 04:27:01',0);
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `token_2` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (18,'jagadeswararaovana@gmail.com','926508bb33acd288629652590569add044be2eb137165c1c97ea6a4e4e0f6a9ed1c850cde23d285cb13ba1a601bd37e5a636','2025-05-27 12:00:29'),(19,'jagadeswararaovana@gmail.com','763d823b86df63919bd5d4c79b828ad86bd5605f5d11252ba82dd2ee616efeebbedbd36ef4cd0b34535b0bec3998da01923d','2025-05-28 05:01:57');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_activities`
--

DROP TABLE IF EXISTS `user_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_activities`
--

LOCK TABLES `user_activities` WRITE;
/*!40000 ALTER TABLE `user_activities` DISABLE KEYS */;
INSERT INTO `user_activities` VALUES (1,1,'Downloaded','Jagadesh-Resume.pdf','2025-04-29 11:09:46'),(2,1,'Downloaded','Jagadesh-Resume.pdf','2025-04-29 11:17:06'),(3,1,'Downloaded','Jagadesh-Resume.pdf','2025-04-30 10:42:16'),(10,2,'Downloaded','Prasad_Resume.pdf','2025-09-15 13:29:55'),(11,2,'Downloaded','Prasad_Resume.pdf','2025-09-15 13:32:46'),(12,2,'Downloaded','Prasad_Resume.pdf','2025-09-15 13:34:36'),(13,2,'Downloaded','Prasad_Resume.pdf','2025-09-15 13:35:43'),(14,2,'Downloaded','Prasad_Resume.pdf','2025-09-15 13:35:56'),(15,2,'Downloaded','JagadeshVanaDA_Resume.pdf','2025-09-15 13:37:40'),(16,2,'Downloaded','JagadeshVanaDA_Resume.pdf','2025-09-15 13:38:42'),(17,2,'Downloaded','Jagadesh-Resume.pdf','2025-09-15 13:43:33'),(18,2,'Downloaded','JagadeshVanaDA_Resume.pdf','2025-09-15 13:43:39'),(19,2,'Downloaded','JagadeshVanaDA_Resume.pdf','2025-09-15 14:16:42'),(20,2,'Downloaded','JagadeshVanaDA_Resume.pdf','2025-09-15 14:22:37'),(21,2,'Downloaded','Jagadesh-Resume.pdf','2025-09-15 14:22:44'),(22,2,'Downloaded','JagadeshVanaDA_Resume.pdf','2025-09-15 14:23:31'),(23,2,'Downloaded','JagadeshVanaDA_Resume.pdf','2025-09-15 14:24:59'),(24,2,'Downloaded','Jagadesh-Resume.pdf','2025-09-15 14:29:11'),(25,2,'Downloaded','JagadeshVanaDA_Resume.pdf','2025-09-15 14:29:18'),(26,2,'Downloaded','Jagadesh-Resume.pdf','2025-09-15 14:29:29'),(27,2,'Deleted','Prasad_Resume.pdf','2025-09-15 14:30:46'),(28,2,'Downloaded','Balakrishna_Resume.pdf','2025-09-15 14:34:06'),(29,2,'Downloaded','Balakrishna_Resume.pdf','2025-09-15 14:38:24'),(31,2,'Downloaded','Balakrishna_Resume.pdf','2025-09-18 10:51:21'),(32,2,'Downloaded','Balakrishna_Resume.pdf','2025-09-18 10:51:24');
/*!40000 ALTER TABLE `user_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `aadhaar` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_image` varchar(255) DEFAULT NULL,
  `address` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Jagadesh Vana','jagadeswararaovana@gmail.com','8790055638','123456781234','$2y$12$tXdk0PStIUdMUS5d2zrWL.oSdF7QHrPyMaQa7pSJPZA0qxJDdjq.G','2025-04-25 06:18:51','profile_68c9376a34c142.66631902.jpg','wersdjfvdju'),(2,'Jagadeswararao Vana','jagguma9573@gmail.com','8790055638','123456781234','$2y$12$GxxeF5k7QA6wHT9ILUEFh.H6Wu1IJl5hZZVE/IlHEMJsbCdWepTTW','2025-09-15 07:54:22','profile_68c7c64f2c3596.80075544.jpg','1-58, YELAMANCHILI VILLAGE AND POST JALUMURU MANDAL SRIKAKULAM DISTRICT');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-18 10:53:23
