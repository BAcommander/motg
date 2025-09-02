-- Master of the Galaxy Database Schema - SQLite Version

-- Enable foreign key support
PRAGMA foreign_keys = ON;

-- =============================================
-- CORE GAME TABLES
-- =============================================

-- Games table - stores individual game sessions
CREATE TABLE IF NOT EXISTS games (
    game_id INTEGER PRIMARY KEY AUTOINCREMENT,
    game_name TEXT NOT NULL,
    galaxy_size TEXT DEFAULT 'medium' CHECK(galaxy_size IN ('small','medium','large','huge')),
    difficulty TEXT DEFAULT 'normal' CHECK(difficulty IN ('easy','normal','hard','impossible')),
    max_players INTEGER DEFAULT 6,
    current_turn INTEGER DEFAULT 1,
    game_status TEXT DEFAULT 'setup' CHECK(game_status IN ('setup','active','paused','finished')),
    turn_timer INTEGER DEFAULT 86400,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Races table - defines all playable races
CREATE TABLE IF NOT EXISTS races (
    race_id INTEGER PRIMARY KEY AUTOINCREMENT,
    race_name TEXT NOT NULL,
    description TEXT,
    traits TEXT, -- JSON
    bonuses TEXT, -- JSON
    government_type TEXT DEFAULT NULL,
    is_custom INTEGER DEFAULT 0,
    created_by INTEGER DEFAULT NULL
);

-- Players table - stores player information and empire data
CREATE TABLE IF NOT EXISTS players (
    player_id INTEGER PRIMARY KEY AUTOINCREMENT,
    game_id INTEGER NOT NULL,
    user_id INTEGER DEFAULT NULL,
    empire_name TEXT NOT NULL,
    leader_name TEXT NOT NULL,
    race_id INTEGER NOT NULL,
    color TEXT DEFAULT '#FF0000',
    credits INTEGER DEFAULT 1000,
    research_points INTEGER DEFAULT 0,
    empire_bonuses TEXT, -- JSON
    government_type TEXT DEFAULT 'dictatorship',
    status TEXT DEFAULT 'active' CHECK(status IN ('active','eliminated','ai','observer')),
    turn_ready INTEGER DEFAULT 0,
    last_action_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(game_id),
    FOREIGN KEY (race_id) REFERENCES races(race_id)
);

-- Star systems table
CREATE TABLE IF NOT EXISTS systems (
    system_id INTEGER PRIMARY KEY AUTOINCREMENT,
    game_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    x_coordinate REAL NOT NULL,
    y_coordinate REAL NOT NULL,
    star_type TEXT DEFAULT 'yellow' CHECK(star_type IN ('red','orange','yellow','white','blue','brown','neutron','black_hole')),
    special_feature TEXT DEFAULT NULL,
    wormhole_destination INTEGER DEFAULT NULL,
    FOREIGN KEY (game_id) REFERENCES games(game_id)
);

-- Planets table
CREATE TABLE IF NOT EXISTS planets (
    planet_id INTEGER PRIMARY KEY AUTOINCREMENT,
    system_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    orbit_position INTEGER NOT NULL,
    planet_type TEXT NOT NULL,
    planet_size TEXT DEFAULT 'medium' CHECK(planet_size IN ('tiny','small','medium','large','huge')),
    max_population INTEGER NOT NULL,
    mineral_richness TEXT DEFAULT 'normal' CHECK(mineral_richness IN ('ultra_poor','poor','normal','rich','ultra_rich')),
    special_features TEXT, -- JSON
    gravity TEXT DEFAULT 'normal' CHECK(gravity IN ('low','normal','high')),
    climate TEXT DEFAULT 'terran',
    FOREIGN KEY (system_id) REFERENCES systems(system_id)
);

-- Colonies table - represents colonized planets
CREATE TABLE IF NOT EXISTS colonies (
    colony_id INTEGER PRIMARY KEY AUTOINCREMENT,
    planet_id INTEGER NOT NULL,
    player_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    population INTEGER DEFAULT 2500,
    farmers INTEGER DEFAULT 1250,
    workers INTEGER DEFAULT 1250,
    scientists INTEGER DEFAULT 0,
    food_surplus INTEGER DEFAULT 0,
    production_output INTEGER DEFAULT 0,
    research_output INTEGER DEFAULT 0,
    pollution INTEGER DEFAULT 0,
    unrest INTEGER DEFAULT 0,
    colony_improvements TEXT, -- JSON
    status TEXT DEFAULT 'active' CHECK(status IN ('active','blockaded','abandoned')),
    founded_turn INTEGER NOT NULL,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (planet_id) REFERENCES planets(planet_id),
    FOREIGN KEY (player_id) REFERENCES players(player_id)
);

-- Technologies table - all available technologies
CREATE TABLE IF NOT EXISTS technologies (
    tech_id INTEGER PRIMARY KEY AUTOINCREMENT,
    tech_name TEXT NOT NULL,
    category TEXT NOT NULL CHECK(category IN ('engineering','physics','chemistry','biology','computers','sociology','power','force_fields')),
    level INTEGER NOT NULL,
    cost INTEGER NOT NULL,
    description TEXT,
    prerequisites TEXT, -- JSON
    effects TEXT, -- JSON
    is_rare INTEGER DEFAULT 0,
    UNIQUE(tech_name, category)
);

-- Player technologies - tracks discovered technologies per player
CREATE TABLE IF NOT EXISTS player_technologies (
    player_tech_id INTEGER PRIMARY KEY AUTOINCREMENT,
    player_id INTEGER NOT NULL,
    tech_id INTEGER NOT NULL,
    discovered_turn INTEGER NOT NULL,
    source TEXT DEFAULT 'research' CHECK(source IN ('research','trade','espionage','conquest','event')),
    UNIQUE(player_id, tech_id),
    FOREIGN KEY (player_id) REFERENCES players(player_id),
    FOREIGN KEY (tech_id) REFERENCES technologies(tech_id)
);

-- Research queue - current research projects
CREATE TABLE IF NOT EXISTS research_queue (
    queue_id INTEGER PRIMARY KEY AUTOINCREMENT,
    player_id INTEGER NOT NULL,
    tech_id INTEGER NOT NULL,
    priority INTEGER DEFAULT 1,
    status TEXT DEFAULT 'queued' CHECK(status IN ('queued','researching','completed','cancelled')),
    cost INTEGER NOT NULL,
    invested_rp INTEGER DEFAULT 0,
    started_turn INTEGER DEFAULT NULL,
    FOREIGN KEY (player_id) REFERENCES players(player_id),
    FOREIGN KEY (tech_id) REFERENCES technologies(tech_id)
);

-- Buildings table - all available buildings
CREATE TABLE IF NOT EXISTS buildings (
    building_id INTEGER PRIMARY KEY AUTOINCREMENT,
    building_name TEXT NOT NULL,
    building_type TEXT NOT NULL,
    cost INTEGER NOT NULL,
    maintenance INTEGER DEFAULT 0,
    prerequisites TEXT, -- JSON
    effects TEXT, -- JSON
    description TEXT
);

-- Colony buildings - buildings constructed in colonies
CREATE TABLE IF NOT EXISTS colony_buildings (
    colony_building_id INTEGER PRIMARY KEY AUTOINCREMENT,
    colony_id INTEGER NOT NULL,
    building_id INTEGER NOT NULL,
    built_turn INTEGER NOT NULL,
    status TEXT DEFAULT 'active' CHECK(status IN ('active','damaged','destroyed')),
    FOREIGN KEY (colony_id) REFERENCES colonies(colony_id),
    FOREIGN KEY (building_id) REFERENCES buildings(building_id)
);

-- Build queue - construction queue for colonies
CREATE TABLE IF NOT EXISTS build_queue (
    queue_id INTEGER PRIMARY KEY AUTOINCREMENT,
    colony_id INTEGER NOT NULL,
    item_type TEXT NOT NULL CHECK(item_type IN ('building','ship','trade_goods')),
    item_id INTEGER DEFAULT NULL,
    item_name TEXT NOT NULL,
    queue_position INTEGER NOT NULL,
    total_cost INTEGER NOT NULL,
    invested_production INTEGER DEFAULT 0,
    repeat_build INTEGER DEFAULT 0,
    FOREIGN KEY (colony_id) REFERENCES colonies(colony_id)
);

-- Events and messages
CREATE TABLE IF NOT EXISTS events (
    event_id INTEGER PRIMARY KEY AUTOINCREMENT,
    player_id INTEGER DEFAULT NULL,
    turn_occurred INTEGER NOT NULL,
    event_type TEXT NOT NULL CHECK(event_type IN ('colony','technology','diplomacy','combat','exploration','system','antaran')),
    message TEXT NOT NULL,
    is_read INTEGER DEFAULT 0,
    importance TEXT DEFAULT 'normal' CHECK(importance IN ('low','normal','high','critical')),
    data TEXT, -- JSON
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES players(player_id)
);

-- User accounts (for multiplayer)
CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL,
    is_active INTEGER DEFAULT 1
);