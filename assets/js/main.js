// Master of the Galaxy - Main JavaScript
// Game interface functionality

class MasterOfTheGalaxy {
    constructor() {
        this.init();
        this.setupEventListeners();
        this.startPeriodicUpdates();
    }
    
    init() {
        console.log('Master of the Galaxy - Initializing...');
        this.gameData = {};
        this.updateInterval = null;
        this.isProcessingTurn = false;
        
        // Load initial game state
        this.loadGameData();
    }
    
    setupEventListeners() {
        // End Turn Button
        const endTurnBtn = document.getElementById('end-turn-btn');
        if (endTurnBtn) {
            endTurnBtn.addEventListener('click', () => this.endTurn());
        }
        
        // Auto Turn Button  
        const autoTurnBtn = document.getElementById('auto-turn-btn');
        if (autoTurnBtn) {
            autoTurnBtn.addEventListener('click', () => this.toggleAutoTurn());
        }
        
        // Navigation tooltips
        this.setupTooltips();
        
        // Colony population sliders (if present)
        this.setupPopulationSliders();
        
        // Research selection
        this.setupResearchSelection();
        
        // Keyboard shortcuts
        this.setupKeyboardShortcuts();
    }
    
    setupTooltips() {
        // Add tooltip functionality
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });
            
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    }
    
    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'game-tooltip';
        tooltip.textContent = text;
        tooltip.style.position = 'absolute';
        tooltip.style.background = 'var(--accent-bg)';
        tooltip.style.color = 'var(--text-primary)';
        tooltip.style.padding = '8px 12px';
        tooltip.style.borderRadius = '4px';
        tooltip.style.border = '1px solid var(--border-color)';
        tooltip.style.zIndex = '1000';
        tooltip.style.pointerEvents = 'none';
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
    }
    
    hideTooltip() {
        const tooltip = document.querySelector('.game-tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }
    
    setupPopulationSliders() {
        const sliders = document.querySelectorAll('.population-slider');
        sliders.forEach(slider => {
            slider.addEventListener('input', (e) => {
                this.updatePopulationAllocation(e.target);
            });
        });
    }
    
    updatePopulationAllocation(slider) {
        const colonyId = slider.dataset.colonyId;
        const allocationType = slider.dataset.type;
        const value = parseInt(slider.value);
        
        // Update display
        const display = document.querySelector(`#${allocationType}-display-${colonyId}`);
        if (display) {
            display.textContent = value;
        }
        
        // Send AJAX update to server
        this.sendAjaxRequest('update_population.php', {
            colony_id: colonyId,
            allocation_type: allocationType,
            value: value
        });
    }
    
    setupResearchSelection() {
        const researchButtons = document.querySelectorAll('.research-option');
        researchButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const techId = e.target.dataset.techId;
                this.selectResearch(techId);
            });
        });
    }
    
    selectResearch(techId) {
        this.sendAjaxRequest('select_research.php', {
            tech_id: techId
        }, (response) => {
            if (response.success) {
                this.showNotification('Research selected: ' + response.tech_name, 'success');
                this.updateResearchDisplay(response.research_data);
            } else {
                this.showNotification('Failed to select research: ' + response.error, 'error');
            }
        });
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Don't trigger shortcuts if user is typing in input fields
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }
            
            switch(e.key.toLowerCase()) {
                case 'enter':
                case ' ':
                    e.preventDefault();
                    this.endTurn();
                    break;
                case 'g':
                    e.preventDefault();
                    window.location.href = 'galaxy.php';
                    break;
                case 'c':
                    e.preventDefault();
                    window.location.href = 'colonies.php';
                    break;
                case 's':
                    e.preventDefault();
                    window.location.href = 'ships.php';
                    break;
                case 'r':
                    e.preventDefault();
                    window.location.href = 'research.php';
                    break;
                case 'd':
                    e.preventDefault();
                    window.location.href = 'diplomacy.php';
                    break;
            }
        });
    }
    
    loadGameData() {
        this.sendAjaxRequest('api/game_state.php', {}, (data) => {
            this.gameData = data;
            this.updateInterface();
        });
    }
    
    updateInterface() {
        // Update resource displays
        this.updateResourceDisplays();
        
        // Update turn counter
        this.updateTurnDisplay();
        
        // Update research progress
        this.updateResearchProgress();
        
        // Update colony summaries
        this.updateColonySummaries();
    }
    
    updateResourceDisplays() {
        const creditsEl = document.querySelector('.bc');
        const researchEl = document.querySelector('.research');
        
        if (creditsEl && this.gameData.empire) {
            creditsEl.textContent = `BC: ${this.formatNumber(this.gameData.empire.credits)}`;
        }
        
        if (researchEl && this.gameData.empire) {
            researchEl.textContent = `Research: ${this.formatNumber(this.gameData.empire.research_points)}`;
        }
    }
    
    updateTurnDisplay() {
        const turnEl = document.querySelector('.turn-info');
        if (turnEl && this.gameData.current_turn) {
            turnEl.textContent = `Turn ${this.gameData.current_turn}`;
        }
    }
    
    updateResearchProgress() {
        if (this.gameData.current_research) {
            const progressBar = document.querySelector('.progress');
            const turnsEl = document.querySelector('.research-item span');
            
            if (progressBar) {
                progressBar.style.width = this.gameData.current_research.progress + '%';
            }
            
            if (turnsEl) {
                turnsEl.textContent = `${this.gameData.current_research.turns_remaining} turns remaining`;
            }
        }
    }
    
    updateColonySummaries() {
        // Update colony displays if present
        const colonyContainer = document.querySelector('.colony-summary');
        if (colonyContainer && this.gameData.colonies) {
            // Refresh colony data
            this.loadGameData();
        }
    }
    
    endTurn() {
        if (this.isProcessingTurn) {
            this.showNotification('Turn is already being processed...', 'warning');
            return;
        }
        
        this.isProcessingTurn = true;
        const endTurnBtn = document.getElementById('end-turn-btn');
        
        if (endTurnBtn) {
            endTurnBtn.disabled = true;
            endTurnBtn.innerHTML = '<span class="loading"></span> Processing Turn...';
        }
        
        this.sendAjaxRequest('api/end_turn.php', {}, (response) => {
            if (response.success) {
                this.showNotification('Turn ended successfully', 'success');
                
                if (response.turn_processed) {
                    this.showNotification('New turn has begun!', 'info');
                    // Reload page to show new turn data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    this.showNotification('Waiting for other players...', 'info');
                }
            } else {
                this.showNotification('Failed to end turn: ' + response.error, 'error');
            }
            
            this.isProcessingTurn = false;
            if (endTurnBtn) {
                endTurnBtn.disabled = false;
                endTurnBtn.innerHTML = 'End Turn';
            }
        });
    }
    
    toggleAutoTurn() {
        const autoTurnBtn = document.getElementById('auto-turn-btn');
        const isAutoEnabled = autoTurnBtn.classList.contains('auto-enabled');
        
        if (isAutoEnabled) {
            this.disableAutoTurn();
        } else {
            this.enableAutoTurn();
        }
    }
    
    enableAutoTurn() {
        const autoTurnBtn = document.getElementById('auto-turn-btn');
        autoTurnBtn.classList.add('auto-enabled');
        autoTurnBtn.textContent = 'Auto Turn: ON';
        
        this.autoTurnInterval = setInterval(() => {
            if (!this.isProcessingTurn) {
                this.endTurn();
            }
        }, 30000); // Auto-end turn every 30 seconds
        
        this.showNotification('Auto-turn enabled', 'info');
    }
    
    disableAutoTurn() {
        const autoTurnBtn = document.getElementById('auto-turn-btn');
        autoTurnBtn.classList.remove('auto-enabled');
        autoTurnBtn.textContent = 'Auto Turn';
        
        if (this.autoTurnInterval) {
            clearInterval(this.autoTurnInterval);
            this.autoTurnInterval = null;
        }
        
        this.showNotification('Auto-turn disabled', 'info');
    }
    
    startPeriodicUpdates() {
        // Update game state every 30 seconds
        this.updateInterval = setInterval(() => {
            this.loadGameData();
        }, 30000);
    }
    
    sendAjaxRequest(url, data, callback, errorCallback) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (callback) {
                callback(data);
            }
        })
        .catch(error => {
            console.error('AJAX Error:', error);
            if (errorCallback) {
                errorCallback(error);
            } else {
                this.showNotification('Network error occurred', 'error');
            }
        });
    }
    
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            z-index: 10000;
            min-width: 250px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease-out;
        `;
        
        // Set background color based on type
        const colors = {
            'success': '#40ff40',
            'error': '#ff4040', 
            'warning': '#ffff40',
            'info': '#4040ff'
        };
        notification.style.background = colors[type] || colors.info;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
    
    formatNumber(num) {
        if (num >= 1000000000) {
            return (num / 1000000000).toFixed(1) + 'B';
        } else if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toLocaleString();
    }
    
    destroy() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
        if (this.autoTurnInterval) {
            clearInterval(this.autoTurnInterval);
        }
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .auto-enabled {
        background: linear-gradient(135deg, var(--success-color), #20ff20) !important;
        color: var(--primary-bg) !important;
    }
`;
document.head.appendChild(style);

// Initialize the game when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.gameInstance = new MasterOfTheGalaxy();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.gameInstance) {
        window.gameInstance.destroy();
    }
});