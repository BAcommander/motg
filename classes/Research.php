<?php

class Research 
{
    private $pdo;
    private $player_id;
    private $player_data;
    
    public function __construct($player_id)
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->player_id = $player_id;
        $this->loadPlayerData();
    }
    
    private function loadPlayerData()
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, r.traits, r.bonuses 
            FROM players p 
            JOIN races r ON p.race_id = r.race_id 
            WHERE p.player_id = ?
        ");
        $stmt->execute([$this->player_id]);
        $this->player_data = $stmt->fetch();
    }
    
    public function processTurn()
    {
        // Get total research points from all colonies
        $total_research = $this->getTotalResearchOutput();
        
        if ($total_research <= 0) return;
        
        // Get current research project
        $current_research = $this->getCurrentResearchProject();
        
        if (!$current_research) {
            // No research project - store research points
            $this->addResearchPoints($total_research);
            return;
        }
        
        // Apply research points to current project
        $new_invested = $current_research['invested_rp'] + $total_research;
        
        if ($new_invested >= $current_research['cost']) {
            // Technology completed!
            $this->completeTechnology($current_research);
            
            // Handle overflow research points
            $overflow = $new_invested - $current_research['cost'];
            if ($overflow > 0) {
                $this->addResearchPoints($overflow);
            }
        } else {
            // Update research progress
            $stmt = $this->pdo->prepare("
                UPDATE research_queue 
                SET invested_rp = ? 
                WHERE queue_id = ?
            ");
            $stmt->execute([$new_invested, $current_research['queue_id']]);
        }
    }
    
    private function getTotalResearchOutput()
    {
        $stmt = $this->pdo->prepare("
            SELECT SUM(research_output) as total_research 
            FROM colonies 
            WHERE player_id = ? AND status = 'active'
        ");
        $stmt->execute([$this->player_id]);
        $result = $stmt->fetch();
        return $result['total_research'] ?: 0;
    }
    
    private function getCurrentResearchProject()
    {
        $stmt = $this->pdo->prepare("
            SELECT rq.*, t.tech_name, t.description, t.category, t.level, t.effects
            FROM research_queue rq
            JOIN technologies t ON rq.tech_id = t.tech_id
            WHERE rq.player_id = ? AND rq.status = 'researching'
            ORDER BY rq.priority ASC
            LIMIT 1
        ");
        $stmt->execute([$this->player_id]);
        return $stmt->fetch();
    }
    
    private function completeTechnology($research_project)
    {
        try {
            $this->pdo->beginTransaction();
            
            // Mark technology as completed
            $stmt = $this->pdo->prepare("
                DELETE FROM research_queue WHERE queue_id = ?
            ");
            $stmt->execute([$research_project['queue_id']]);
            
            // Add technology to player's known technologies
            $stmt = $this->pdo->prepare("
                INSERT INTO player_technologies (player_id, tech_id, discovered_turn) 
                VALUES (?, ?, (SELECT current_turn FROM games WHERE game_id = ?))
            ");
            $stmt->execute([
                $this->player_id, 
                $research_project['tech_id'], 
                $this->player_data['game_id']
            ]);
            
            // Apply technology effects
            $this->applyTechnologyEffects($research_project);
            
            // Log the discovery
            $this->logTechnologyDiscovery($research_project);
            
            // Check for follow-up research options
            $this->updateAvailableTechnologies($research_project);
            
            $this->pdo->commit();
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    private function applyTechnologyEffects($tech)
    {
        $effects = json_decode($tech['effects'], true) ?: [];
        
        foreach ($effects as $effect_type => $effect_data) {
            switch ($effect_type) {
                case 'unlock_building':
                    $this->unlockBuilding($effect_data['building_id']);
                    break;
                    
                case 'unlock_ship_component':
                    $this->unlockShipComponent($effect_data['component_id']);
                    break;
                    
                case 'empire_bonus':
                    $this->applyEmpireBonus($effect_data);
                    break;
                    
                case 'unlock_government':
                    $this->unlockGovernment($effect_data['government_type']);
                    break;
                    
                case 'colonial_improvement':
                    $this->applyColonialImprovement($effect_data);
                    break;
            }
        }
    }
    
    private function unlockBuilding($building_id)
    {
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO player_buildings (player_id, building_id, unlocked_turn) 
            VALUES (?, ?, (SELECT current_turn FROM games WHERE game_id = ?))
        ");
        $stmt->execute([$this->player_id, $building_id, $this->player_data['game_id']]);
    }
    
    private function unlockShipComponent($component_id)
    {
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO player_ship_components (player_id, component_id, unlocked_turn) 
            VALUES (?, ?, (SELECT current_turn FROM games WHERE game_id = ?))
        ");
        $stmt->execute([$this->player_id, $component_id, $this->player_data['game_id']]);
    }
    
    private function applyEmpireBonus($bonus_data)
    {
        // Apply permanent empire-wide bonuses
        $current_bonuses = json_decode($this->player_data['empire_bonuses'], true) ?: [];
        
        foreach ($bonus_data as $bonus_type => $bonus_value) {
            if (!isset($current_bonuses[$bonus_type])) {
                $current_bonuses[$bonus_type] = 0;
            }
            $current_bonuses[$bonus_type] += $bonus_value;
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE players SET empire_bonuses = ? WHERE player_id = ?
        ");
        $stmt->execute([json_encode($current_bonuses), $this->player_id]);
    }
    
    private function unlockGovernment($government_type)
    {
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO player_governments (player_id, government_type, unlocked_turn) 
            VALUES (?, ?, (SELECT current_turn FROM games WHERE game_id = ?))
        ");
        $stmt->execute([$this->player_id, $government_type, $this->player_data['game_id']]);
    }
    
    private function applyColonialImprovement($improvement_data)
    {
        // Apply improvements to all existing colonies
        $stmt = $this->pdo->prepare("
            UPDATE colonies 
            SET colony_improvements = JSON_MERGE_PATCH(
                IFNULL(colony_improvements, '{}'), 
                ?
            )
            WHERE player_id = ? AND status = 'active'
        ");
        $stmt->execute([json_encode($improvement_data), $this->player_id]);
    }
    
    public function getAvailableTechnologies($category = null)
    {
        $where_clause = "WHERE t.tech_id NOT IN (SELECT tech_id FROM player_technologies WHERE player_id = ?)";
        $params = [$this->player_id];
        
        if ($category) {
            $where_clause .= " AND t.category = ?";
            $params[] = $category;
        }
        
        // Check prerequisites
        $where_clause .= " AND (t.prerequisites IS NULL OR t.prerequisites = '' OR 
                          JSON_LENGTH(t.prerequisites) = (
                              SELECT COUNT(*) FROM player_technologies pt 
                              WHERE pt.player_id = ? AND JSON_CONTAINS(t.prerequisites, CONCAT('\"', pt.tech_id, '\"'))
                          ))";
        $params[] = $this->player_id;
        
        $stmt = $this->pdo->prepare("
            SELECT t.*, 
                   CASE WHEN pt.tech_id IS NOT NULL THEN 1 ELSE 0 END as discovered
            FROM technologies t
            LEFT JOIN player_technologies pt ON t.tech_id = pt.tech_id AND pt.player_id = ?
            $where_clause
            ORDER BY t.category, t.level, t.tech_name
        ");
        
        $params[] = $this->player_id; // For the LEFT JOIN
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function startResearch($tech_id, $priority = 1)
    {
        // Validate technology is available
        $available_techs = $this->getAvailableTechnologies();
        $tech_available = false;
        $selected_tech = null;
        
        foreach ($available_techs as $tech) {
            if ($tech['tech_id'] == $tech_id) {
                $tech_available = true;
                $selected_tech = $tech;
                break;
            }
        }
        
        if (!$tech_available) {
            return false; // Technology not available
        }
        
        // Check if race is Creative (gets all tech choices) or Uncreative (random selection)
        $race_traits = json_decode($this->player_data['traits'], true) ?: [];
        
        if (in_array('uncreative', $race_traits)) {
            // Uncreative races get random tech selection
            $available_level_techs = array_filter($available_techs, function($tech) use ($selected_tech) {
                return $tech['level'] == $selected_tech['level'] && 
                       $tech['category'] == $selected_tech['category'];
            });
            
            if (count($available_level_techs) > 1) {
                $selected_tech = $available_level_techs[array_rand($available_level_techs)];
                $tech_id = $selected_tech['tech_id'];
            }
        }
        
        // Add to research queue
        $stmt = $this->pdo->prepare("
            INSERT INTO research_queue (player_id, tech_id, priority, status, cost, invested_rp) 
            VALUES (?, ?, ?, 'researching', ?, 0)
        ");
        $stmt->execute([$this->player_id, $tech_id, $priority, $selected_tech['cost']]);
        
        return true;
    }
    
    private function updateAvailableTechnologies($completed_tech)
    {
        // This would update what technologies become available after completing a tech
        // For now, just a placeholder
    }
    
    private function addResearchPoints($points)
    {
        $stmt = $this->pdo->prepare("
            UPDATE players SET research_points = research_points + ? WHERE player_id = ?
        ");
        $stmt->execute([$points, $this->player_id]);
    }
    
    private function logTechnologyDiscovery($tech)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO events (player_id, turn_occurred, event_type, message) 
            VALUES (?, (SELECT current_turn FROM games WHERE game_id = ?), 'technology', ?)
        ");
        $stmt->execute([
            $this->player_id, 
            $this->player_data['game_id'], 
            "Technology discovered: " . $tech['tech_name']
        ]);
    }
}
?>