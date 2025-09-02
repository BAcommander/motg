-- Master of the Galaxy - Seed Data
-- Populate database with initial game data based on MOO2

USE `master_of_galaxy`;

-- =============================================
-- RACES DATA (Based on MOO2)
-- =============================================

INSERT INTO `races` (`race_name`, `description`, `traits`, `bonuses`, `government_type`) VALUES
('Humans', 'Versatile and diplomatic race with strong trading abilities', '["charismatic", "lucky"]', '{"diplomacy": 25, "trade": 25}', 'democracy'),
('Psilons', 'Highly intelligent research-focused race with creative abilities', '["creative", "large_homeworld", "low_gravity"]', '{"research": 2, "all_tech_choices": true}', 'technocracy'),
('Silicoids', 'Silicon-based lifeforms immune to pollution with slow growth', '["lithovore", "tolerant", "repulsive"]', '{"pollution_immunity": true, "growth_penalty": -50}', 'dictatorship'),
('Sakkra', 'Fast-breeding reptilian race with subterranean adaptation', '["fast_breeding", "subterranean", "large_homeworld"]', '{"growth": 100, "agriculture": 1}', 'feudalism'),
('Meklars', 'Cybernetic race with enhanced industrial capabilities', '["cybernetic"]', '{"production": 2, "extra_factories": 2}', 'dictatorship'),
('Klackons', 'Insectoid hive-mind with industrial focus but uncreative', '["uncreative"]', '{"production": 1, "food": 1, "random_tech": true}', 'unification'),
('Mrrshan', 'Feline warriors with superior combat abilities', '["warlord", "rich_homeworld"]', '{"ship_attack": 50, "ground_combat": 25}', 'feudalism'),
('Alkari', 'Avian race with ship defense bonuses and artifacts', '["ship_defense", "artifacts_homeworld"]', '{"ship_defense": 25, "starting_tech": true}', 'dictatorship'),
('Bulrathi', 'Bear-like race with ground combat specialization', '["high_gravity", "subterranean"]', '{"ground_combat": 10, "ship_attack": 20}', 'dictatorship'),
('Trilarians', 'Aquatic race with dimensional manipulation abilities', '["aquatic", "trans_dimensional"]', '{"warp_mastery": true, "ocean_bonus": 50}', 'democracy'),
('Darloks', 'Shape-shifting spies with stealth capabilities', '["stealthy", "spying"]', '{"espionage": 30, "infiltration": true}', 'dictatorship'),
('Elerians', 'Telepathic race with omniscient abilities', '["telepathic", "omniscient"]', '{"ship_attack": 20, "ship_defense": 25, "see_all": true}', 'feudalism'),
('Gnolams', 'Small traders with financial bonuses', '["lucky", "fantastic_traders", "low_gravity"]', '{"trade": 1, "credits_bonus": 25}', 'democracy');

-- =============================================
-- TECHNOLOGY DATA
-- =============================================

-- Engineering Technologies
INSERT INTO `technologies` (`tech_name`, `category`, `level`, `cost`, `description`, `effects`) VALUES
('Advanced Engineering', 'engineering', 1, 80, 'Basic construction techniques', '{"unlock_building": {"building_id": 2}}'),
('Standard Fuel Cells', 'engineering', 1, 80, 'Basic ship fuel systems', '{"ship_range": 3}'),
('Anti-Grav Harness', 'engineering', 2, 150, 'Improved construction in high gravity', '{"construction_bonus": 25}'),
('Automated Factory', 'engineering', 2, 150, 'Pollution-free automated production', '{"unlock_building": {"building_id": 3}}'),
('Standard Armor', 'engineering', 3, 250, 'Basic ship armor plating', '{"unlock_component": {"component_id": 1}}'),
('Fighter Bays', 'engineering', 4, 450, 'Launch fighter squadrons', '{"unlock_component": {"component_id": 2}}'),
('Assault Shuttles', 'engineering', 5, 800, 'Ground invasion capability', '{"unlock_component": {"component_id": 3}}');

-- Physics Technologies  
INSERT INTO `technologies` (`tech_name`, `category`, `level`, `cost`, `description`, `effects`) VALUES
('Laser Cannon', 'physics', 1, 80, 'Basic beam weapon', '{"unlock_component": {"component_id": 10}}'),
('Fighter Interceptor', 'physics', 1, 80, 'Anti-fighter technology', '{"unlock_component": {"component_id": 11}}'),
('Battle Scanner', 'physics', 2, 150, 'Enhanced targeting systems', '{"combat_bonus": {"beam_accuracy": 50}}'),
('Laser Rifle', 'physics', 2, 150, 'Infantry beam weapons', '{"ground_combat_bonus": 10}'),
('Particle Beam', 'physics', 3, 250, 'Advanced beam weapon', '{"unlock_component": {"component_id": 12}}'),
('Graviton Beam', 'physics', 4, 450, 'Gravity-based weapon', '{"unlock_component": {"component_id": 13}}'),
('Tractor Beam', 'physics', 5, 800, 'Ship immobilization', '{"unlock_component": {"component_id": 14}}');

-- Chemistry Technologies
INSERT INTO `technologies` (`tech_name`, `category`, `level`, `cost`, `description`, `effects`) VALUES
('Chemistry', 'chemistry', 1, 80, 'Basic chemical processes', '{"unlock_building": {"building_id": 4}}'),
('Standard Missile', 'chemistry', 1, 80, 'Basic missile systems', '{"unlock_component": {"component_id": 20}}'),
('Anti-Missile Rockets', 'chemistry', 2, 150, 'Point defense systems', '{"unlock_component": {"component_id": 21}}'),
('Deuterium Fuel Cells', 'chemistry', 2, 150, 'Extended ship range', '{"ship_range": 4}'),
('Titanium Armor', 'chemistry', 3, 250, 'Improved armor plating', '{"unlock_component": {"component_id": 22}}'),
('Nuclear Missile', 'chemistry', 4, 450, 'Nuclear warheads', '{"unlock_component": {"component_id": 23}}'),
('Pollution Processor', 'chemistry', 5, 800, 'Environmental cleanup', '{"unlock_building": {"building_id": 5}}');

-- Biology Technologies
INSERT INTO `technologies` (`tech_name`, `category`, `level`, `cost`, `description`, `effects`) VALUES
('Bioadaptation', 'biology', 1, 80, 'Environmental adaptation', '{"colonization_bonus": 25}'),
('Anti-Plague', 'biology', 1, 80, 'Disease resistance', '{"population_growth": 10}'),
('Hydroponic Farm', 'biology', 2, 150, 'Advanced agriculture', '{"unlock_building": {"building_id": 6}}'),
('Cloning Center', 'biology', 3, 250, 'Accelerated population growth', '{"unlock_building": {"building_id": 7}}'),
('Terraforming', 'biology', 4, 450, 'Planet transformation', '{"terraform_ability": true}'),
('Gaia Transformation', 'biology', 5, 800, 'Ultimate planet enhancement', '{"gaia_transform": true}');

-- Computer Technologies
INSERT INTO `technologies` (`tech_name`, `category`, `level`, `cost`, `description`, `effects`) VALUES
('Electronics', 'computers', 1, 80, 'Basic computer systems', '{"unlock_component": {"component_id": 30}}'),
('Dauntless Guidance System', 'computers', 1, 80, 'Missile guidance', '{"missile_accuracy": 25}'),
('Anti-Missile ECM', 'computers', 2, 150, 'Electronic countermeasures', '{"unlock_component": {"component_id": 31}}'),
('Standard Computer', 'computers', 3, 250, 'Ship computer systems', '{"unlock_component": {"component_id": 32}}'),
('Research Laboratory', 'computers', 2, 150, 'Advanced research facility', '{"unlock_building": {"building_id": 8}}'),
('Neural Interface', 'computers', 4, 450, 'Brain-computer link', '{"research_bonus": 25}'),
('Positronic Computer', 'computers', 5, 800, 'Advanced AI systems', '{"unlock_component": {"component_id": 33}}');

-- Sociology Technologies
INSERT INTO `technologies` (`tech_name`, `category`, `level`, `cost`, `description`, `effects`) VALUES
('Colony Ship', 'sociology', 1, 80, 'Interstellar colonization', '{"unlock_ship_component": {"component_id": 40}}'),
('Outpost Ship', 'sociology', 1, 80, 'System monitoring posts', '{"unlock_ship_component": {"component_id": 41}}'),
('Anti-Personnel Pods', 'sociology', 2, 150, 'Orbital bombardment', '{"unlock_component": {"component_id": 42}}'),
('Colony Base', 'sociology', 2, 150, 'Improved colonial infrastructure', '{"unlock_building": {"building_id": 9}}'),
('Planetary Radiation Shielding', 'sociology', 3, 250, 'Radiation protection', '{"colonization_bonus": 50}'),
('Astro University', 'sociology', 4, 450, 'Higher education facility', '{"unlock_building": {"building_id": 10}}');

-- Power Technologies
INSERT INTO `technologies` (`tech_name`, `category`, `level`, `cost`, `description`, `effects`) VALUES
('Nuclear Drive', 'power', 1, 80, 'Basic ship propulsion', '{"unlock_component": {"component_id": 50}}'),
('Nuclear Reactor', 'power', 1, 80, 'Ship power systems', '{"unlock_component": {"component_id": 51}}'),
('Ion Drive', 'power', 2, 150, 'Improved propulsion', '{"unlock_component": {"component_id": 52}}'),
('Fusion Reactor', 'power', 3, 250, 'Advanced power generation', '{"unlock_component": {"component_id": 53}}'),
('Anti-Matter Drive', 'power', 4, 450, 'High-speed propulsion', '{"unlock_component": {"component_id": 54}}'),
('Interphased Drive', 'power', 5, 800, 'Dimensional propulsion', '{"unlock_component": {"component_id": 55}}');

-- Force Fields Technologies
INSERT INTO `technologies` (`tech_name`, `category`, `level`, `cost`, `description`, `effects`) VALUES
('Class I Shield', 'force_fields', 1, 80, 'Basic energy shields', '{"unlock_component": {"component_id": 60}}'),
('ECM Jammer', 'force_fields', 1, 80, 'Electronic warfare', '{"unlock_component": {"component_id": 61}}'),
('Class III Shield', 'force_fields', 2, 150, 'Improved energy shields', '{"unlock_component": {"component_id": 62}}'),
('Planetary Shields', 'force_fields', 3, 250, 'Colony protection', '{"unlock_building": {"building_id": 11}}'),
('Class V Shield', 'force_fields', 4, 450, 'Advanced energy shields', '{"unlock_component": {"component_id": 63}}'),
('Phase Shifter', 'force_fields', 5, 800, 'Dimensional shields', '{"unlock_component": {"component_id": 64}}');

-- =============================================
-- BUILDINGS DATA  
-- =============================================

INSERT INTO `buildings` (`building_name`, `building_type`, `cost`, `maintenance`, `description`, `effects`) VALUES
('Colony Base', 'infrastructure', 60, 0, 'Basic colony structure required for all colonies', '{"required": true, "population_cap": 1000}'),
('Housing', 'infrastructure', 20, 1, 'Increases population capacity', '{"population_cap": 200}'),
('Factory', 'production', 60, 2, 'Increases industrial production', '{"production_bonus": 3}'),
('Automated Factory', 'production', 120, 1, 'Pollution-free production facility', '{"production_bonus": 3, "pollution": 0}'),
('Research Laboratory', 'science', 60, 1, 'Increases research output', '{"research_bonus": 3}'),
('Hydroponic Farm', 'agriculture', 60, 2, 'Increases food production', '{"food_bonus": 2}'),
('Cloning Center', 'population', 100, 2, 'Accelerates population growth', '{"growth_bonus": 100000}'),
('Pollution Processor', 'environment', 80, 1, 'Reduces pollution', '{"pollution_reduction": 3}'),
('Planetary Shields', 'defense', 200, 5, 'Protects colony from bombardment', '{"shield_strength": 10}'),
('Astro University', 'science', 150, 3, 'Advanced research facility', '{"research_bonus": 5, "leader_bonus": true}'),
('Trade Goods', 'economic', 0, 0, 'Converts production to credits', '{"credits_conversion": true}');

-- =============================================
-- SAMPLE GAME DATA
-- =============================================

-- Create a sample game
INSERT INTO `games` (`game_name`, `galaxy_size`, `difficulty`, `max_players`, `current_turn`) VALUES
('Test Galaxy', 'medium', 'normal', 6, 1);

-- Create sample star systems
INSERT INTO `systems` (`game_id`, `name`, `x_coordinate`, `y_coordinate`, `star_type`) VALUES
(1, 'Sol', 0, 0, 'yellow'),
(1, 'Alpha Centauri', 50, 30, 'orange'),
(1, 'Vega', -40, 60, 'white'),
(1, 'Sirius', 80, -20, 'blue'),
(1, 'Proxima', -30, -50, 'red'),
(1, 'Arcturus', 60, 70, 'orange');

-- Create sample planets
INSERT INTO `planets` (`system_id`, `name`, `orbit_position`, `planet_type`, `planet_size`, `max_population`, `mineral_richness`, `climate`) VALUES
(1, 'Earth', 3, 'terran', 'large', 10, 'normal', 'terran'),
(1, 'Mars', 4, 'desert', 'medium', 6, 'rich', 'desert'),
(2, 'Centauri Prime', 2, 'terran', 'huge', 12, 'ultra_rich', 'terran'),
(3, 'Vega II', 2, 'ocean', 'large', 8, 'normal', 'ocean'),
(4, 'Sirius Alpha', 1, 'toxic', 'small', 4, 'ultra_poor', 'toxic'),
(5, 'Proxima B', 1, 'tundra', 'medium', 5, 'poor', 'tundra');

-- Sample leaders
INSERT INTO `leaders` (`name`, `leader_type`, `skills`, `experience`, `hire_cost`, `maintenance_cost`) VALUES
('Admiral Korax', 'ship', '{"navigation": 2, "tactics": 3}', 0, 100, 5),
('Governor Chen', 'colony', '{"engineering": 2, "farming": 1}', 0, 80, 3),
('Spy Master Vale', 'spy', '{"espionage": 3, "infiltration": 2}', 0, 120, 4),
('Captain Torres', 'ship', '{"weapons": 2, "leadership": 1}', 0, 90, 4),
('Administrator Kim', 'colony', '{"research": 2, "economics": 2}', 0, 85, 3);

COMMIT;