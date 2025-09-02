<?php

class Game
{
    private $pdo;
    private $player_id;
    private $game_id;
    
    public function __construct($player_id) 
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->player_id = $player_id;
        $this->game_id = $this->getActiveGameId();
    }
    
    private function getActiveGameId()
    {
        $stmt = $this->pdo->prepare("
            SELECT game_id FROM players 
            WHERE player_id = ? AND status = 'active'
        ");
        $stmt->execute([$this->player_id]);
        $result = $stmt->fetch();
        return $result ? $result['game_id'] : null;
    }
    
    public function getEmpire()
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, r.race_name, r.traits, r.bonuses 
            FROM players p 
            JOIN races r ON p.race_id = r.race_id 
            WHERE p.player_id = ?
        ");
        $stmt->execute([$this->player_id]);
        return $stmt->fetch();
    }
    
    public function getCurrentTurn()
    {
        $stmt = $this->pdo->prepare("
            SELECT current_turn FROM games WHERE game_id = ?
        ");
        $stmt->execute([$this->game_id]);
        $result = $stmt->fetch();
        return $result ? $result['current_turn'] : 1;
    }
    
    public function getPlayerColonies()
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, p.name as planet_name, s.name as system_name
            FROM colonies c
            JOIN planets p ON c.planet_id = p.planet_id
            JOIN systems s ON p.system_id = s.system_id
            WHERE c.player_id = ? AND c.status = 'active'
            ORDER BY c.founded_turn DESC
        ");
        $stmt->execute([$this->player_id]);
        return $stmt->fetchAll();
    }
    
    public function getCurrentResearch()
    {
        $stmt = $this->pdo->prepare("
            SELECT r.*, t.tech_name, t.description, t.cost
            FROM research_queue r
            JOIN technologies t ON r.tech_id = t.tech_id
            WHERE r.player_id = ? AND r.status = 'researching'
            ORDER BY r.priority ASC
            LIMIT 1
        ");
        $stmt->execute([$this->player_id]);
        $research = $stmt->fetch();
        
        if ($research) {
            $progress = ($research['invested_rp'] / $research['cost']) * 100;
            $empire = $this->getEmpire();
            $research_per_turn = $this->calculateResearchPerTurn();
            $remaining_rp = $research['cost'] - $research['invested_rp'];
            $turns_remaining = ceil($remaining_rp / max(1, $research_per_turn));
            
            $research['progress'] = min(100, $progress);
            $research['turns_remaining'] = $turns_remaining;
        }
        
        return $research ?: ['tech_name' => 'None', 'progress' => 0, 'turns_remaining' => 0];
    }
    
    public function getRecentEvents()
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM events 
            WHERE player_id = ? 
            ORDER BY turn_occurred DESC, event_id DESC 
            LIMIT 5
        ");
        $stmt->execute([$this->player_id]);
        return $stmt->fetchAll();
    }
    
    public function calculateResearchPerTurn()
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
    
    public function endTurn()
    {
        try {
            $this->pdo->beginTransaction();
            
            // Mark player as ready for next turn
            $stmt = $this->pdo->prepare("
                UPDATE players 
                SET turn_ready = 1, last_action_time = NOW() 
                WHERE player_id = ?
            ");
            $stmt->execute([$this->player_id]);
            
            // Check if all players are ready
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as ready_count,
                       (SELECT COUNT(*) FROM players WHERE game_id = ? AND status = 'active') as total_count
                FROM players 
                WHERE game_id = ? AND status = 'active' AND turn_ready = 1
            ");
            $stmt->execute([$this->game_id, $this->game_id]);
            $counts = $stmt->fetch();
            
            if ($counts['ready_count'] >= $counts['total_count']) {
                $this->processTurn();
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
    
    private function processTurn()
    {
        // Process all colonies
        $this->processColonies();
        
        // Process research
        $this->processResearch();
        
        // Process ships and movement
        $this->processShips();
        
        // Process diplomacy
        $this->processDiplomacy();
        
        // Advance turn
        $stmt = $this->pdo->prepare("
            UPDATE games SET current_turn = current_turn + 1 WHERE game_id = ?
        ");
        $stmt->execute([$this->game_id]);
        
        // Reset player turn flags
        $stmt = $this->pdo->prepare("
            UPDATE players SET turn_ready = 0 WHERE game_id = ?
        ");
        $stmt->execute([$this->game_id]);
        
        // Generate turn events
        $this->generateTurnEvents();
    }
    
    private function processColonies()
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM colonies WHERE game_id = ? AND status = 'active'
        ");
        $stmt->execute([$this->game_id]);
        $colonies = $stmt->fetchAll();
        
        foreach ($colonies as $colony) {
            $colony_processor = new Colony($colony['colony_id']);
            $colony_processor->processTurn();
        }
    }
    
    private function processResearch()
    {
        $stmt = $this->pdo->prepare("
            SELECT player_id FROM players WHERE game_id = ? AND status = 'active'
        ");
        $stmt->execute([$this->game_id]);
        $players = $stmt->fetchAll();
        
        foreach ($players as $player) {
            $research_processor = new Research($player['player_id']);
            $research_processor->processTurn();
        }
    }
    
    private function processShips()
    {
        // Process ship movement, combat, etc.
        // This would be implemented based on specific game mechanics
    }
    
    private function processDiplomacy()
    {
        // Process diplomatic actions and treaties
        // This would be implemented based on specific game mechanics
    }
    
    private function generateTurnEvents()
    {
        // Generate random events, discoveries, etc.
        // This would be implemented based on specific game mechanics
    }
}
?>