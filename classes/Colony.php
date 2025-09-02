<?php

class Colony
{
    private $pdo;
    private $colony_id;
    private $colony_data;
    
    public function __construct($colony_id)
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->colony_id = $colony_id;
        $this->loadColonyData();
    }
    
    private function loadColonyData()
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, p.planet_type, p.mineral_richness, p.max_population as planet_max_pop,
                   s.name as system_name, pl.race_id, r.traits, r.bonuses
            FROM colonies c
            JOIN planets p ON c.planet_id = p.planet_id
            JOIN systems s ON p.system_id = s.system_id
            JOIN players pl ON c.player_id = pl.player_id
            JOIN races r ON pl.race_id = r.race_id
            WHERE c.colony_id = ?
        ");
        $stmt->execute([$this->colony_id]);
        $this->colony_data = $stmt->fetch();
    }
    
    public function processTurn()
    {
        if (!$this->colony_data) return false;
        
        // Calculate population growth
        $this->processPopulationGrowth();
        
        // Process production and construction
        $this->processProduction();
        
        // Process research allocation
        $this->processResearch();
        
        // Update pollution
        $this->processPollution();
        
        // Save changes
        $this->saveColonyData();
        
        return true;
    }
    
    private function processPopulationGrowth()
    {
        $current_pop = $this->colony_data['population'];
        $max_pop = $this->getEffectiveMaxPopulation();
        
        if ($current_pop >= $max_pop) return;
        
        // Base growth rate (typically 0.1 per turn = 10%)
        $base_growth_rate = 0.1;
        
        // Apply racial bonuses
        $race_bonuses = json_decode($this->colony_data['bonuses'], true) ?: [];
        $growth_modifier = 1.0;
        
        if (isset($race_bonuses['growth'])) {
            $growth_modifier += $race_bonuses['growth'] / 100;
        }
        
        // Apply planet type modifier
        $planet_types = PLANET_TYPES;
        $planet_bonus = $planet_types[$this->colony_data['planet_type']]['growth_bonus'] ?? 0;
        $growth_modifier += $planet_bonus / 100;
        
        // Calculate food surplus effect
        $food_surplus = $this->colony_data['food_surplus'];
        if ($food_surplus > 0) {
            $growth_modifier += 0.02; // +2% per surplus food
        } elseif ($food_surplus < 0) {
            $growth_modifier -= 0.05; // -5% per food deficit
        }
        
        // Apply overcrowding penalty
        $overcrowding_factor = $current_pop / $max_pop;
        if ($overcrowding_factor > 0.8) {
            $growth_modifier *= (1.2 - $overcrowding_factor);
        }
        
        $growth = $current_pop * $base_growth_rate * $growth_modifier;
        $growth = max(0, $growth); // Can't have negative growth from this calculation
        
        // Apply growth with minimum of 1 per turn if conditions are good
        if ($growth < 1 && $food_surplus >= 0 && $current_pop < $max_pop) {
            $growth = 1;
        }
        
        $new_population = min($max_pop, $current_pop + floor($growth));
        $this->colony_data['population'] = $new_population;
    }
    
    private function getEffectiveMaxPopulation()
    {
        $base_max = $this->colony_data['planet_max_pop'];
        $housing_bonus = $this->getBuilding('housing') * 2; // Each housing adds 2 max pop
        
        // Apply racial bonuses for tolerant species
        $race_traits = json_decode($this->colony_data['traits'], true) ?: [];
        if (in_array('tolerant', $race_traits)) {
            $base_max += 2; // Tolerant species get +2 max pop on all worlds
        }
        
        return $base_max + $housing_bonus;
    }
    
    private function processProduction()
    {
        $population = $this->colony_data['population'];
        $farmers = $this->colony_data['farmers'];
        $workers = $this->colony_data['workers'];
        
        // Calculate food production
        $base_food_per_farmer = 2;
        $race_bonuses = json_decode($this->colony_data['bonuses'], true) ?: [];
        
        if (isset($race_bonuses['food'])) {
            $base_food_per_farmer += $race_bonuses['food'];
        }
        
        $food_produced = $farmers * $base_food_per_farmer;
        $food_consumed = $population * 2; // Each pop consumes 2 food
        $this->colony_data['food_surplus'] = $food_produced - $food_consumed;
        
        // Calculate industrial production
        $base_production_per_worker = 1;
        if (isset($race_bonuses['production'])) {
            $base_production_per_worker += $race_bonuses['production'];
        }
        
        $factory_bonus = $this->getBuilding('factory') * 3; // Each factory adds 3 production
        $mineral_bonus = $this->getMineralBonus();
        
        $production = ($workers * $base_production_per_worker + $factory_bonus) * $mineral_bonus;
        
        // Apply pollution penalty
        $pollution_penalty = $this->getPollutionPenalty();
        $production *= $pollution_penalty;
        
        $this->colony_data['production_output'] = floor($production);
        
        // Process build queue
        $this->processBuildQueue();
    }
    
    private function processResearch()
    {
        $scientists = $this->colony_data['scientists'];
        $research_labs = $this->getBuilding('research_lab');
        
        $base_research_per_scientist = 1;
        $race_bonuses = json_decode($this->colony_data['bonuses'], true) ?: [];
        
        if (isset($race_bonuses['research'])) {
            $base_research_per_scientist += $race_bonuses['research'];
        }
        
        $lab_bonus = $research_labs * 3; // Each lab adds 3 research
        $research_output = $scientists * $base_research_per_scientist + $lab_bonus;
        
        $this->colony_data['research_output'] = $research_output;
    }
    
    private function processPollution()
    {
        $factories = $this->getBuilding('factory');
        $population = $this->colony_data['population'];
        
        // Base pollution from factories and population
        $pollution_generated = $factories * 2 + floor($population / 1000);
        
        // Apply racial immunity (e.g., Silicoids)
        $race_traits = json_decode($this->colony_data['traits'], true) ?: [];
        if (in_array('lithovore', $race_traits)) {
            $pollution_generated = 0;
        }
        
        // Reduce pollution with atmospheric processors
        $pollution_control = $this->getBuilding('atmospheric_processor') * 3;
        
        $net_pollution = max(0, $pollution_generated - $pollution_control);
        $this->colony_data['pollution'] = $net_pollution;
    }
    
    private function processBuildQueue()
    {
        $available_production = $this->colony_data['production_output'];
        
        // Get current build item
        $stmt = $this->pdo->prepare("
            SELECT * FROM build_queue 
            WHERE colony_id = ? 
            ORDER BY queue_position ASC 
            LIMIT 1
        ");
        $stmt->execute([$this->colony_id]);
        $build_item = $stmt->fetch();
        
        if (!$build_item) {
            // No build queue, convert production to trade goods (credits)
            $this->addCredits($available_production);
            return;
        }
        
        // Add production to current item
        $new_invested = $build_item['invested_production'] + $available_production;
        
        if ($new_invested >= $build_item['total_cost']) {
            // Item completed
            $this->completeConstruction($build_item);
            
            // Remove from queue
            $stmt = $this->pdo->prepare("
                DELETE FROM build_queue WHERE queue_id = ?
            ");
            $stmt->execute([$build_item['queue_id']]);
            
            // Process overflow production into next item or credits
            $overflow = $new_invested - $build_item['total_cost'];
            if ($overflow > 0) {
                $this->colony_data['production_output'] = $overflow;
                $this->processBuildQueue(); // Recursive call for overflow
            }
        } else {
            // Update progress
            $stmt = $this->pdo->prepare("
                UPDATE build_queue 
                SET invested_production = ? 
                WHERE queue_id = ?
            ");
            $stmt->execute([$new_invested, $build_item['queue_id']]);
        }
    }
    
    private function completeConstruction($build_item)
    {
        switch ($build_item['item_type']) {
            case 'building':
                $this->addBuilding($build_item['item_id']);
                break;
            case 'ship':
                $this->createShip($build_item['item_id']);
                break;
            case 'trade_goods':
                $this->addCredits($build_item['total_cost']);
                break;
        }
        
        // Log construction completion
        $this->logEvent("Construction completed: " . $build_item['item_name']);
    }
    
    private function getBuilding($building_type)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM colony_buildings cb
            JOIN buildings b ON cb.building_id = b.building_id
            WHERE cb.colony_id = ? AND b.building_type = ?
        ");
        $stmt->execute([$this->colony_id, $building_type]);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getMineralBonus()
    {
        switch ($this->colony_data['mineral_richness']) {
            case 'ultra_poor': return 0.5;
            case 'poor': return 0.75;
            case 'normal': return 1.0;
            case 'rich': return 1.25;
            case 'ultra_rich': return 1.5;
            default: return 1.0;
        }
    }
    
    private function getPollutionPenalty()
    {
        $pollution = $this->colony_data['pollution'];
        if ($pollution <= 0) return 1.0;
        if ($pollution <= 5) return 0.95;
        if ($pollution <= 10) return 0.85;
        if ($pollution <= 20) return 0.7;
        return 0.5; // Heavy pollution
    }
    
    private function addBuilding($building_id)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO colony_buildings (colony_id, building_id, built_turn) 
            VALUES (?, ?, (SELECT current_turn FROM games WHERE game_id = ?))
        ");
        $stmt->execute([$this->colony_id, $building_id, $this->colony_data['game_id']]);
    }
    
    private function addCredits($amount)
    {
        $stmt = $this->pdo->prepare("
            UPDATE players 
            SET credits = credits + ? 
            WHERE player_id = ?
        ");
        $stmt->execute([$amount, $this->colony_data['player_id']]);
    }
    
    private function logEvent($message)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO events (player_id, turn_occurred, event_type, message) 
            VALUES (?, (SELECT current_turn FROM games WHERE game_id = ?), 'colony', ?)
        ");
        $stmt->execute([
            $this->colony_data['player_id'], 
            $this->colony_data['game_id'], 
            $message
        ]);
    }
    
    private function saveColonyData()
    {
        $stmt = $this->pdo->prepare("
            UPDATE colonies 
            SET population = ?, food_surplus = ?, production_output = ?, 
                research_output = ?, pollution = ?, last_updated = NOW()
            WHERE colony_id = ?
        ");
        $stmt->execute([
            $this->colony_data['population'],
            $this->colony_data['food_surplus'],
            $this->colony_data['production_output'],
            $this->colony_data['research_output'],
            $this->colony_data['pollution'],
            $this->colony_id
        ]);
    }
}
?>