-- Master of the Galaxy Database Schema
-- Based on Master of Orion 2 game mechanics

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `master_of_galaxy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `master_of_galaxy`;

-- =============================================
-- CORE GAME TABLES
-- =============================================

-- Games table - stores individual game sessions
CREATE TABLE `games` (
  `game_id` int(11) NOT NULL AUTO_INCREMENT,
  `game_name` varchar(100) NOT NULL,
  `galaxy_size` enum('small','medium','large','huge') DEFAULT 'medium',
  `difficulty` enum('easy','normal','hard','impossible') DEFAULT 'normal',
  `max_players` int(11) DEFAULT 6,
  `current_turn` int(11) DEFAULT 1,
  `game_status` enum('setup','active','paused','finished') DEFAULT 'setup',
  `turn_timer` int(11) DEFAULT 86400,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Races table - defines all playable races
CREATE TABLE `races` (
  `race_id` int(11) NOT NULL AUTO_INCREMENT,
  `race_name` varchar(50) NOT NULL,
  `description` text,
  `traits` json DEFAULT NULL,
  `bonuses` json DEFAULT NULL,
  `government_type` varchar(30) DEFAULT NULL,
  `is_custom` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`race_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Players table - stores player information and empire data
CREATE TABLE `players` (
  `player_id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `empire_name` varchar(100) NOT NULL,
  `leader_name` varchar(100) NOT NULL,
  `race_id` int(11) NOT NULL,
  `color` varchar(7) DEFAULT '#FF0000',
  `credits` int(11) DEFAULT 1000,
  `research_points` int(11) DEFAULT 0,
  `empire_bonuses` json DEFAULT NULL,
  `government_type` varchar(30) DEFAULT 'dictatorship',
  `status` enum('active','eliminated','ai','observer') DEFAULT 'active',
  `turn_ready` tinyint(1) DEFAULT 0,
  `last_action_time` timestamp DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`player_id`),
  KEY `game_id` (`game_id`),
  KEY `race_id` (`race_id`),
  CONSTRAINT `players_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`),
  CONSTRAINT `players_ibfk_2` FOREIGN KEY (`race_id`) REFERENCES `races` (`race_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- GALAXY AND PLANETARY SYSTEM TABLES
-- =============================================

-- Star systems table
CREATE TABLE `systems` (
  `system_id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `x_coordinate` float NOT NULL,
  `y_coordinate` float NOT NULL,
  `star_type` enum('red','orange','yellow','white','blue','brown','neutron','black_hole') DEFAULT 'yellow',
  `special_feature` varchar(50) DEFAULT NULL,
  `wormhole_destination` int(11) DEFAULT NULL,
  PRIMARY KEY (`system_id`),
  KEY `game_id` (`game_id`),
  CONSTRAINT `systems_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Planets table
CREATE TABLE `planets` (
  `planet_id` int(11) NOT NULL AUTO_INCREMENT,
  `system_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `orbit_position` tinyint(4) NOT NULL,
  `planet_type` varchar(20) NOT NULL,
  `planet_size` enum('tiny','small','medium','large','huge') DEFAULT 'medium',
  `max_population` int(11) NOT NULL,
  `mineral_richness` enum('ultra_poor','poor','normal','rich','ultra_rich') DEFAULT 'normal',
  `special_features` json DEFAULT NULL,
  `gravity` enum('low','normal','high') DEFAULT 'normal',
  `climate` enum('toxic','radiated','barren','desert','tundra','ocean','swamp','terran','gaia') DEFAULT 'terran',
  PRIMARY KEY (`planet_id`),
  KEY `system_id` (`system_id`),
  CONSTRAINT `planets_ibfk_1` FOREIGN KEY (`system_id`) REFERENCES `systems` (`system_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Colonies table - represents colonized planets
CREATE TABLE `colonies` (
  `colony_id` int(11) NOT NULL AUTO_INCREMENT,
  `planet_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `population` int(11) DEFAULT 2500,
  `farmers` int(11) DEFAULT 1250,
  `workers` int(11) DEFAULT 1250,
  `scientists` int(11) DEFAULT 0,
  `food_surplus` int(11) DEFAULT 0,
  `production_output` int(11) DEFAULT 0,
  `research_output` int(11) DEFAULT 0,
  `pollution` int(11) DEFAULT 0,
  `unrest` int(11) DEFAULT 0,
  `colony_improvements` json DEFAULT NULL,
  `status` enum('active','blockaded','abandoned') DEFAULT 'active',
  `founded_turn` int(11) NOT NULL,
  `last_updated` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`colony_id`),
  KEY `planet_id` (`planet_id`),
  KEY `player_id` (`player_id`),
  CONSTRAINT `colonies_ibfk_1` FOREIGN KEY (`planet_id`) REFERENCES `planets` (`planet_id`),
  CONSTRAINT `colonies_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- TECHNOLOGY SYSTEM TABLES
-- =============================================

-- Technologies table - all available technologies
CREATE TABLE `technologies` (
  `tech_id` int(11) NOT NULL AUTO_INCREMENT,
  `tech_name` varchar(100) NOT NULL,
  `category` enum('engineering','physics','chemistry','biology','computers','sociology','power','force_fields') NOT NULL,
  `level` tinyint(4) NOT NULL,
  `cost` int(11) NOT NULL,
  `description` text,
  `prerequisites` json DEFAULT NULL,
  `effects` json DEFAULT NULL,
  `is_rare` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`tech_id`),
  UNIQUE KEY `tech_category_level` (`tech_name`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Player technologies - tracks discovered technologies per player
CREATE TABLE `player_technologies` (
  `player_tech_id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) NOT NULL,
  `tech_id` int(11) NOT NULL,
  `discovered_turn` int(11) NOT NULL,
  `source` enum('research','trade','espionage','conquest','event') DEFAULT 'research',
  PRIMARY KEY (`player_tech_id`),
  UNIQUE KEY `player_tech_unique` (`player_id`,`tech_id`),
  KEY `tech_id` (`tech_id`),
  CONSTRAINT `player_technologies_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`),
  CONSTRAINT `player_technologies_ibfk_2` FOREIGN KEY (`tech_id`) REFERENCES `technologies` (`tech_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Research queue - current research projects
CREATE TABLE `research_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) NOT NULL,
  `tech_id` int(11) NOT NULL,
  `priority` tinyint(4) DEFAULT 1,
  `status` enum('queued','researching','completed','cancelled') DEFAULT 'queued',
  `cost` int(11) NOT NULL,
  `invested_rp` int(11) DEFAULT 0,
  `started_turn` int(11) DEFAULT NULL,
  PRIMARY KEY (`queue_id`),
  KEY `player_id` (`player_id`),
  KEY `tech_id` (`tech_id`),
  CONSTRAINT `research_queue_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`),
  CONSTRAINT `research_queue_ibfk_2` FOREIGN KEY (`tech_id`) REFERENCES `technologies` (`tech_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- BUILDING AND CONSTRUCTION SYSTEM
-- =============================================

-- Buildings table - all available buildings
CREATE TABLE `buildings` (
  `building_id` int(11) NOT NULL AUTO_INCREMENT,
  `building_name` varchar(100) NOT NULL,
  `building_type` varchar(50) NOT NULL,
  `cost` int(11) NOT NULL,
  `maintenance` int(11) DEFAULT 0,
  `prerequisites` json DEFAULT NULL,
  `effects` json DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Colony buildings - buildings constructed in colonies
CREATE TABLE `colony_buildings` (
  `colony_building_id` int(11) NOT NULL AUTO_INCREMENT,
  `colony_id` int(11) NOT NULL,
  `building_id` int(11) NOT NULL,
  `built_turn` int(11) NOT NULL,
  `status` enum('active','damaged','destroyed') DEFAULT 'active',
  PRIMARY KEY (`colony_building_id`),
  KEY `colony_id` (`colony_id`),
  KEY `building_id` (`building_id`),
  CONSTRAINT `colony_buildings_ibfk_1` FOREIGN KEY (`colony_id`) REFERENCES `colonies` (`colony_id`),
  CONSTRAINT `colony_buildings_ibfk_2` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Build queue - construction queue for colonies
CREATE TABLE `build_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `colony_id` int(11) NOT NULL,
  `item_type` enum('building','ship','trade_goods') NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_name` varchar(100) NOT NULL,
  `queue_position` tinyint(4) NOT NULL,
  `total_cost` int(11) NOT NULL,
  `invested_production` int(11) DEFAULT 0,
  `repeat_build` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`queue_id`),
  KEY `colony_id` (`colony_id`),
  CONSTRAINT `build_queue_ibfk_1` FOREIGN KEY (`colony_id`) REFERENCES `colonies` (`colony_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- SHIP AND FLEET SYSTEM
-- =============================================

-- Ship designs table
CREATE TABLE `ship_designs` (
  `design_id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) NOT NULL,
  `design_name` varchar(100) NOT NULL,
  `ship_size` enum('fighter','frigate','destroyer','cruiser','battleship','titan') NOT NULL,
  `hull_type` varchar(50) DEFAULT 'standard',
  `components` json DEFAULT NULL,
  `cost` int(11) NOT NULL,
  `space_used` int(11) NOT NULL,
  `max_space` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_turn` int(11) NOT NULL,
  PRIMARY KEY (`design_id`),
  KEY `player_id` (`player_id`),
  CONSTRAINT `ship_designs_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ships table
CREATE TABLE `ships` (
  `ship_id` int(11) NOT NULL AUTO_INCREMENT,
  `fleet_id` int(11) DEFAULT NULL,
  `player_id` int(11) NOT NULL,
  `design_id` int(11) NOT NULL,
  `ship_name` varchar(100) NOT NULL,
  `current_system` int(11) DEFAULT NULL,
  `destination_system` int(11) DEFAULT NULL,
  `movement_points` int(11) DEFAULT 0,
  `max_movement` int(11) DEFAULT 1,
  `experience` int(11) DEFAULT 0,
  `damage` int(11) DEFAULT 0,
  `status` enum('active','damaged','destroyed','under_construction') DEFAULT 'active',
  `built_turn` int(11) NOT NULL,
  PRIMARY KEY (`ship_id`),
  KEY `player_id` (`player_id`),
  KEY `design_id` (`design_id`),
  KEY `current_system` (`current_system`),
  CONSTRAINT `ships_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`),
  CONSTRAINT `ships_ibfk_2` FOREIGN KEY (`design_id`) REFERENCES `ship_designs` (`design_id`),
  CONSTRAINT `ships_ibfk_3` FOREIGN KEY (`current_system`) REFERENCES `systems` (`system_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- DIPLOMACY AND EVENTS
-- =============================================

-- Diplomatic relations
CREATE TABLE `diplomacy` (
  `relation_id` int(11) NOT NULL AUTO_INCREMENT,
  `player1_id` int(11) NOT NULL,
  `player2_id` int(11) NOT NULL,
  `relation_type` enum('war','neutral','peace','alliance','trade','non_aggression') DEFAULT 'neutral',
  `trust_level` int(11) DEFAULT 0,
  `established_turn` int(11) NOT NULL,
  `expires_turn` int(11) DEFAULT NULL,
  PRIMARY KEY (`relation_id`),
  UNIQUE KEY `diplomatic_pair` (`player1_id`,`player2_id`),
  KEY `player2_id` (`player2_id`),
  CONSTRAINT `diplomacy_ibfk_1` FOREIGN KEY (`player1_id`) REFERENCES `players` (`player_id`),
  CONSTRAINT `diplomacy_ibfk_2` FOREIGN KEY (`player2_id`) REFERENCES `players` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Events and messages
CREATE TABLE `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) DEFAULT NULL,
  `turn_occurred` int(11) NOT NULL,
  `event_type` enum('colony','technology','diplomacy','combat','exploration','system','antaran') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `importance` enum('low','normal','high','critical') DEFAULT 'normal',
  `data` json DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`),
  KEY `player_id` (`player_id`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- LEADERS AND HEROES
-- =============================================

-- Leaders table
CREATE TABLE `leaders` (
  `leader_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `leader_type` enum('colony','ship','spy') NOT NULL,
  `skills` json DEFAULT NULL,
  `experience` int(11) DEFAULT 0,
  `level` tinyint(4) DEFAULT 1,
  `hire_cost` int(11) NOT NULL,
  `maintenance_cost` int(11) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`leader_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Player leaders - leaders hired by players
CREATE TABLE `player_leaders` (
  `player_leader_id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) NOT NULL,
  `leader_id` int(11) NOT NULL,
  `assignment_type` enum('pool','colony','ship','fleet') DEFAULT 'pool',
  `assignment_id` int(11) DEFAULT NULL,
  `hired_turn` int(11) NOT NULL,
  `status` enum('active','killed','retired') DEFAULT 'active',
  PRIMARY KEY (`player_leader_id`),
  KEY `player_id` (`player_id`),
  KEY `leader_id` (`leader_id`),
  CONSTRAINT `player_leaders_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`),
  CONSTRAINT `player_leaders_ibfk_2` FOREIGN KEY (`leader_id`) REFERENCES `leaders` (`leader_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User accounts (for multiplayer)
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;