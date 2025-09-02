# Master of the Galaxy

A web-based 4X space strategy game inspired by Master of Orion 2: Battle at Antares, built with PHP and MySQL.

## Features

- **13 Unique Races** - Each with distinct traits and bonuses based on MOO2
- **Turn-Based Strategy** - Classic 4X gameplay (eXplore, eXpand, eXploit, eXterminate)
- **8 Technology Trees** - Research across Engineering, Physics, Chemistry, Biology, Computers, Sociology, Power, and Force Fields
- **Colony Management** - Population allocation, building construction, resource management
- **Ship Design System** - Create custom ship designs with various components
- **Diplomacy** - Interact with other empires through treaties and trade
- **Leader System** - Hire heroes to govern colonies and command fleets
- **Real-Time Web Interface** - Modern responsive design with space-themed styling

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher  
- Web server (Apache/Nginx)
- Modern web browser with JavaScript support

### Setup Instructions

1. **Clone or extract files** to your web server directory:
   ```
   C:\GOG Games\Master of the Galaxy\
   ```

2. **Configure Database**:
   - Edit `config/config.php` with your MySQL credentials
   - Default settings: host=localhost, database=master_of_galaxy, user=root, password=""

3. **Initialize Game**:
   - Navigate to `http://localhost/Master%20of%20the%20Galaxy/setup.php`
   - Follow the setup wizard to:
     - Initialize the database schema
     - Create your first game
     - Select your race and create your empire

4. **Start Playing**:
   - After setup, you'll be redirected to the main game interface
   - Begin exploring the galaxy and managing your empire!

## Game Mechanics

### Races

Choose from 13 distinct races, each with unique traits:

- **Humans** - Diplomatic traders with charisma bonuses
- **Psilons** - Creative researchers who can learn all technologies  
- **Silicoids** - Rock-based lifeforms immune to pollution
- **Sakkra** - Fast-breeding reptilians with population growth bonuses
- **Meklars** - Cybernetic industrialists with production bonuses
- **Klackons** - Hive-minded insects with uncreative research
- **Mrrshan** - Feline warriors with combat bonuses
- **And 6 more unique races...**

### Technology System

Research technologies across 8 categories:
- **Engineering** - Construction and manufacturing
- **Physics** - Beam weapons and communications  
- **Chemistry** - Materials, armor, and missiles
- **Biology** - Life support and terraforming
- **Computers** - Ship systems and research labs
- **Sociology** - Government and colonization
- **Power** - Propulsion and energy systems
- **Force Fields** - Shields and defensive systems

### Colony Management

- **Population Allocation** - Assign citizens as farmers, workers, or scientists
- **Building Construction** - Construct facilities to improve your colonies
- **Resource Balance** - Manage food production, industrial output, and research
- **Environmental Factors** - Deal with pollution and planetary conditions

### Turn Processing

- **Simultaneous Turns** - All players plan their moves simultaneously
- **Real-Time Interface** - See updates as they happen
- **Auto-Turn Options** - Set automated actions for faster gameplay

## File Structure

```
Master of the Galaxy/
├── assets/
│   ├── css/main.css          # Game styling
│   ├── js/main.js            # Client-side game logic
│   └── images/               # Game graphics
├── classes/
│   ├── Game.php              # Core game engine
│   ├── Colony.php            # Colony management
│   └── Research.php          # Technology system
├── config/
│   └── config.php            # Database and game configuration
├── database/
│   ├── schema.sql            # Database structure
│   └── seed_data.sql         # Initial game data
├── includes/
│   └── functions.php         # Helper functions
├── templates/                # HTML templates
├── api/                      # AJAX API endpoints
├── index.php                 # Main game interface
├── setup.php                 # Installation wizard
└── README.md                 # This file
```

## Development

### Adding New Features

The game is designed with modularity in mind:

- **New Races**: Add to the RACES constant in `config/config.php`
- **New Technologies**: Insert into the technologies table and update effects
- **New Buildings**: Add to buildings table with appropriate effects
- **New Game Screens**: Create new PHP files following the existing structure

### Database Schema

The game uses a relational database with tables for:
- Games and players
- Galaxy systems and planets  
- Colonies and buildings
- Technologies and research
- Ships and fleets
- Diplomacy and events
- Leaders and heroes

### API Endpoints

AJAX endpoints in the `api/` directory handle:
- Turn processing
- Colony updates
- Research selection
- Ship movement
- Diplomatic actions

## Credits

Inspired by **Master of Orion 2: Battle at Antares** (1996) by Simtex and MicroProse.

This is a fan-made tribute project created for educational and entertainment purposes.

## License

This project is for educational and non-commercial use only. Master of Orion 2 and related trademarks belong to their respective owners.

---

**May your empire span the galaxy!** 🚀✨