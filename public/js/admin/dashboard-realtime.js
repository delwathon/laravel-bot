/**
 * Admin Dashboard Real-Time Updates
 * Auto-refreshes key metrics without page reload
 */

class DashboardManager {
    constructor() {
        this.refreshInterval = 30000; // 30 seconds
        this.init();
    }

    init() {
        this.startAutoRefresh();
        this.setupEventListeners();
    }

    /**
     * Start auto-refresh for real-time stats
     */
    startAutoRefresh() {
        // Initial load
        this.updateRealtimeStats();

        // Set interval for periodic updates
        setInterval(() => {
            this.updateRealtimeStats();
        }, this.refreshInterval);
    }

    /**
     * Fetch and update real-time statistics
     */
    async updateRealtimeStats() {
        try {
            const response = await fetch('/admin/dashboard/realtime-stats', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch realtime stats');
            }

            const data = await response.json();
            this.updateDashboardElements(data);
            
        } catch (error) {
            console.error('Error updating realtime stats:', error);
        }
    }

    /**
     * Update dashboard elements with new data
     */
    updateDashboardElements(data) {
        // Update active trades count
        const activeTradesElement = document.querySelector('[data-stat="active-trades"]');
        if (activeTradesElement) {
            this.animateNumber(activeTradesElement, parseInt(activeTradesElement.textContent.replace(/,/g, '')), data.active_trades);
        }

        // Update active positions count
        const activePositionsElement = document.querySelector('[data-stat="active-positions"]');
        if (activePositionsElement) {
            this.animateNumber(activePositionsElement, parseInt(activePositionsElement.textContent.replace(/,/g, '')), data.active_positions);
        }

        // Update pending orders count
        const pendingOrdersElement = document.querySelector('[data-stat="pending-orders"]');
        if (pendingOrdersElement) {
            pendingOrdersElement.textContent = data.pending_orders;
        }

        // Update admin balance
        const adminBalanceElement = document.querySelector('[data-stat="admin-balance"]');
        if (adminBalanceElement && data.admin_balance) {
            const formattedBalance = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(data.admin_balance);
            adminBalanceElement.textContent = formattedBalance;
        }

        // Update system status indicator
        const systemStatusElement = document.querySelector('[data-stat="system-status"]');
        if (systemStatusElement) {
            const statusClass = data.system_status === 'operational' ? 'success' : 'danger';
            systemStatusElement.className = `badge bg-${statusClass} bg-opacity-10 text-${statusClass}`;
            systemStatusElement.textContent = data.system_status.charAt(0).toUpperCase() + data.system_status.slice(1);
        }

        // Add visual feedback
        this.showUpdateIndicator();
    }

    /**
     * Animate number changes
     */
    animateNumber(element, start, end) {
        const duration = 1000; // 1 second
        const range = end - start;
        const increment = range / (duration / 16); // 60fps
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            element.textContent = Math.round(current).toLocaleString();
        }, 16);
    }

    /**
     * Show update indicator
     */
    showUpdateIndicator() {
        const indicator = document.getElementById('update-indicator');
        if (indicator) {
            indicator.classList.add('show');
            setTimeout(() => {
                indicator.classList.remove('show');
            }, 2000);
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Refresh button
        const refreshBtn = document.getElementById('dashboard-refresh-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.updateRealtimeStats();
                
                // Add spinning animation
                const icon = refreshBtn.querySelector('i');
                if (icon) {
                    icon.classList.add('fa-spin');
                    setTimeout(() => {
                        icon.classList.remove('fa-spin');
                    }, 1000);
                }
            });
        }

        // Export data button (if needed in future)
        const exportBtn = document.getElementById('export-dashboard-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportDashboardData();
            });
        }
    }

    /**
     * Export dashboard data (future enhancement)
     */
    async exportDashboardData() {
        try {
            const response = await fetch('/admin/dashboard/export', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });

            if (!response.ok) {
                throw new Error('Failed to export data');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `dashboard-export-${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
        } catch (error) {
            console.error('Error exporting dashboard data:', error);
        }
    }
}

// Initialize dashboard manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    new DashboardManager();
});