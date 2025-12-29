/**
 * HMSI Finance API Service
 * 
 * JavaScript service untuk mengakses REST API endpoints
 * dengan session-based authentication.
 * 
 * @version 1.0.0
 */

class ApiService {
    constructor() {
        this.baseUrl = '/api';
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
    }

    /**
     * Generic fetch wrapper with error handling
     */
    async fetch(url, options = {}) {
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    ...this.defaultHeaders,
                    ...options.headers
                },
                credentials: 'same-origin' // Include cookies for session auth
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // =================== KATEGORI API ===================

    /**
     * Get all categories
     * @param {Object} params - Query parameters (jenis, search, per_page)
     * @returns {Promise<Object>}
     */
    async getKategori(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = `${this.baseUrl}/kategori${queryString ? '?' + queryString : ''}`;
        return await this.fetch(url);
    }

    /**
     * Get category by ID
     * @param {number} id - Category ID
     * @returns {Promise<Object>}
     */
    async getKategoriById(id) {
        return await this.fetch(`${this.baseUrl}/kategori/${id}`);
    }

    // =================== TRANSAKSI API ===================

    /**
     * Get all transactions
     * @param {Object} params - Query parameters (start_date, end_date, category_id, jenis, per_page)
     * @returns {Promise<Object>}
     */
    async getTransaksi(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = `${this.baseUrl}/transaksi${queryString ? '?' + queryString : ''}`;
        return await this.fetch(url);
    }

    /**
     * Get transaction by ID
     * @param {number} id - Transaction ID
     * @returns {Promise<Object>}
     */
    async getTransaksiById(id) {
        return await this.fetch(`${this.baseUrl}/transaksi/${id}`);
    }

    // =================== SALDO API ===================

    /**
     * Get all saldo periods
     * @param {Object} params - Query parameters (status, per_page)
     * @returns {Promise<Object>}
     */
    async getSaldo(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = `${this.baseUrl}/saldo${queryString ? '?' + queryString : ''}`;
        return await this.fetch(url);
    }

    /**
     * Get saldo period by ID
     * @param {number} id - Saldo period ID
     * @returns {Promise<Object>}
     */
    async getSaldoById(id) {
        return await this.fetch(`${this.baseUrl}/saldo/${id}`);
    }

    /**
     * Get chart data for saldo visualization
     * @returns {Promise<Object>}
     */
    async getSaldoChart() {
        return await this.fetch(`${this.baseUrl}/saldo/chart`);
    }

    // =================== LAPORAN API ===================

    /**
     * Get all reports
     * @param {Object} params - Query parameters (status, year, per_page)
     * @returns {Promise<Object>}
     */
    async getLaporan(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = `${this.baseUrl}/laporan${queryString ? '?' + queryString : ''}`;
        return await this.fetch(url);
    }

    /**
     * Get report by ID
     * @param {number} id - Report ID
     * @returns {Promise<Object>}
     */
    async getLaporanById(id) {
        return await this.fetch(`${this.baseUrl}/laporan/${id}`);
    }

    // =================== USERS API ===================

    /**
     * Get all users (Admin only)
     * @param {Object} params - Query parameters (role_id, search, per_page)
     * @returns {Promise<Object>}
     */
    async getUsers(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = `${this.baseUrl}/users${queryString ? '?' + queryString : ''}`;
        return await this.fetch(url);
    }

    /**
     * Get user by ID (Admin only)
     * @param {number} id - User ID
     * @returns {Promise<Object>}
     */
    async getUserById(id) {
        return await this.fetch(`${this.baseUrl}/users/${id}`);
    }

    // =================== HELPER METHODS ===================

    /**
     * Show loading state
     * @param {string} elementId - Element ID to show loading
     */
    showLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data...</p>
                </div>
            `;
        }
    }

    /**
     * Show error message
     * @param {string} elementId - Element ID to show error
     * @param {string} message - Error message
     */
    showError(elementId, message) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    ${message}
                </div>
            `;
        }
    }

    /**
     * Format currency to IDR
     * @param {number} amount - Amount to format
     * @returns {string}
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    /**
     * Format date to Indonesian format
     * @param {string} dateString - Date string
     * @returns {string}
     */
    formatDate(dateString) {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        }).format(date);
    }
}

// Create global instance
window.apiService = new ApiService();

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ApiService;
}
