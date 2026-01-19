/**
 * Admin Cuestionarios JavaScript Module
 * Maneja las funcionalidades administrativas del sistema de cuestionarios
 */
class AdminCuestionarios {
    constructor() {
        this.filters = {
            estado: '',
            empresa: '',
            fecha_inicio: '',
            fecha_fin: '',
            busqueda: ''
        };
        
        this.sortConfig = {
            column: '',
            direction: 'asc'
        };
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupFilters();
        this.setupSorting();
        this.setupBulkActions();
        this.setupDataTables();
        this.loadStatistics();
        
        console.log('AdminCuestionarios inicializado');
    }
    
    setupEventListeners() {
        // Filter controls
        document.addEventListener('change', (e) => {
            if (e.target.matches('.filter-control')) {
                this.handleFilterChange(e.target);
            }
        });
        
        // Search input with debounce
        const searchInput = document.getElementById('busqueda');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.handleSearch(e.target.value);
                }, 500);
            });
        }
        
        // Bulk action buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-bulk-action')) {
                e.preventDefault();
                this.handleBulkAction(e.target.dataset.action);
            }
            
            if (e.target.matches('.btn-export')) {
                e.preventDefault();
                this.handleExport(e.target.dataset.format);
            }
            
            if (e.target.matches('.btn-delete-cuestionario')) {
                e.preventDefault();
                this.handleDelete(e.target.dataset.id);
            }
            
            if (e.target.matches('.btn-duplicate-cuestionario')) {
                e.preventDefault();
                this.handleDuplicate(e.target.dataset.id);
            }
        });
        
        // Table row selection
        document.addEventListener('change', (e) => {
            if (e.target.matches('.row-checkbox')) {
                this.handleRowSelection();
            }
            
            if (e.target.matches('#selectAll')) {
                this.handleSelectAll(e.target.checked);
            }
        });
        
        // Real-time updates
        this.setupRealTimeUpdates();
    }
    
    setupFilters() {
        // Initialize filter state from URL params
        const urlParams = new URLSearchParams(window.location.search);
        
        Object.keys(this.filters).forEach(key => {
            const value = urlParams.get(key);
            if (value) {
                this.filters[key] = value;
                const filterElement = document.getElementById(key);
                if (filterElement) {
                    filterElement.value = value;
                }
            }
        });
        
        // Setup date range picker
        this.setupDateRangePicker();
        
        // Setup advanced filters modal
        this.setupAdvancedFilters();
    }
    
    setupDateRangePicker() {
        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaFin = document.getElementById('fecha_fin');
        
        if (fechaInicio && fechaFin) {
            // Basic date validation
            fechaInicio.addEventListener('change', () => {
                if (fechaFin.value && fechaInicio.value > fechaFin.value) {
                    fechaFin.value = fechaInicio.value;
                }
                fechaFin.min = fechaInicio.value;
            });
            
            fechaFin.addEventListener('change', () => {
                if (fechaInicio.value && fechaFin.value < fechaInicio.value) {
                    fechaInicio.value = fechaFin.value;
                }
                fechaInicio.max = fechaFin.value;
            });
        }
    }
    
    setupAdvancedFilters() {
        const advancedFiltersBtn = document.getElementById('btnAdvancedFilters');
        const advancedFiltersModal = document.getElementById('advancedFiltersModal');
        
        if (advancedFiltersBtn && advancedFiltersModal) {
            advancedFiltersBtn.addEventListener('click', () => {
                const modal = new bootstrap.Modal(advancedFiltersModal);
                modal.show();
            });
        }
    }
    
    setupSorting() {
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', () => {
                const column = header.dataset.column;
                this.handleSort(column);
            });
        });
    }
    
    setupBulkActions() {
        const bulkActionsContainer = document.getElementById('bulkActions');
        if (bulkActionsContainer) {
            // Initially hidden
            bulkActionsContainer.style.display = 'none';
        }
    }
    
    setupDataTables() {
        const table = document.getElementById('cuestionariosTable');
        if (table && typeof $ !== 'undefined' && $.fn.DataTable) {
            // Enhanced DataTable setup
            this.dataTable = $(table).DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/admin/cuestionarios/data',
                    data: (d) => {
                        // Add custom filters
                        Object.assign(d, this.filters);
                    }
                },
                columns: [
                    { data: 'select', orderable: false, searchable: false },
                    { data: 'id', name: 'id' },
                    { data: 'evaluado', name: 'evaluadoOrden.nombre' },
                    { data: 'empresa', name: 'evaluadoOrden.orden.empresa.nombre' },
                    { data: 'estado', name: 'estado' },
                    { data: 'progreso', name: 'progreso', orderable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'updated_at', name: 'updated_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']],
                pageLength: 25,
                responsive: true,
                language: {
                    url: '/js/datatables-es.json'
                }
            });
        }
    }
    
    setupRealTimeUpdates() {
        // Poll for updates every 30 seconds
        setInterval(() => {
            this.refreshStatistics();
            this.refreshTable();
        }, 30000);
    }
    
    handleFilterChange(filterElement) {
        const filterName = filterElement.id || filterElement.name;
        const filterValue = filterElement.value;
        
        this.filters[filterName] = filterValue;
        this.applyFilters();
    }
    
    handleSearch(searchTerm) {
        this.filters.busqueda = searchTerm;
        this.applyFilters();
    }
    
    handleSort(column) {
        if (this.sortConfig.column === column) {
            // Toggle direction
            this.sortConfig.direction = this.sortConfig.direction === 'asc' ? 'desc' : 'asc';
        } else {
            // New column
            this.sortConfig.column = column;
            this.sortConfig.direction = 'asc';
        }
        
        this.applySorting();
        this.updateSortIndicators();
    }
    
    handleRowSelection() {
        const selectedRows = document.querySelectorAll('.row-checkbox:checked');
        const bulkActionsContainer = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        if (bulkActionsContainer) {
            if (selectedRows.length > 0) {
                bulkActionsContainer.style.display = 'block';
                if (selectedCount) {
                    selectedCount.textContent = selectedRows.length;
                }
            } else {
                bulkActionsContainer.style.display = 'none';
            }
        }
        
        // Update select all checkbox
        const selectAll = document.getElementById('selectAll');
        const totalCheckboxes = document.querySelectorAll('.row-checkbox');
        
        if (selectAll && totalCheckboxes.length > 0) {
            selectAll.indeterminate = selectedRows.length > 0 && selectedRows.length < totalCheckboxes.length;
            selectAll.checked = selectedRows.length === totalCheckboxes.length;
        }
    }
    
    handleSelectAll(checked) {
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.checked = checked;
        });
        this.handleRowSelection();
    }
    
    async handleBulkAction(action) {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked'))
            .map(checkbox => checkbox.value);
        
        if (selectedIds.length === 0) {
            this.showNotification('Seleccione al menos un cuestionario', 'warning');
            return;
        }
        
        const confirmMessage = this.getBulkActionConfirmMessage(action, selectedIds.length);
        if (!await this.showConfirmDialog('Confirmar acción', confirmMessage)) {
            return;
        }
        
        this.showLoader(`Procesando ${selectedIds.length} cuestionarios...`);
        
        try {
            const response = await fetch('/admin/cuestionarios/bulk-action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    action: action,
                    ids: selectedIds
                })
            });
            
            const result = await response.json();
            
            if (response.ok) {
                this.showNotification(result.message, 'success');
                this.refreshTable();
                this.refreshStatistics();
                
                // Clear selections
                this.handleSelectAll(false);
            } else {
                throw new Error(result.message || 'Error en la acción masiva');
            }
        } catch (error) {
            console.error('Bulk action error:', error);
            this.showNotification('Error al procesar la acción masiva', 'error');
        } finally {
            this.hideLoader();
        }
    }
    
    getBulkActionConfirmMessage(action, count) {
        const messages = {
            'delete': `¿Está seguro de eliminar ${count} cuestionario(s)? Esta acción no se puede deshacer.`,
            'export': `¿Desea exportar ${count} cuestionario(s) a Excel?`,
            'change_status': `¿Desea cambiar el estado de ${count} cuestionario(s)?`,
            'send_reminder': `¿Desea enviar recordatorio a ${count} evaluado(s)?`
        };
        
        return messages[action] || `¿Está seguro de realizar esta acción en ${count} cuestionario(s)?`;
    }
    
    async handleExport(format) {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked'))
            .map(checkbox => checkbox.value);
        
        this.showLoader('Generando exportación...');
        
        try {
            const params = new URLSearchParams({
                format: format,
                ...this.filters
            });
            
            if (selectedIds.length > 0) {
                params.append('ids', selectedIds.join(','));
            }
            
            const url = `/admin/cuestionarios/export?${params.toString()}`;
            
            // Create download link
            const link = document.createElement('a');
            link.href = url;
            link.download = `cuestionarios_${new Date().toISOString().split('T')[0]}.${format}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            this.showNotification('Exportación iniciada', 'success');
        } catch (error) {
            console.error('Export error:', error);
            this.showNotification('Error al exportar', 'error');
        } finally {
            this.hideLoader();
        }
    }
    
    async handleDelete(id) {
        if (!await this.showConfirmDialog(
            'Confirmar eliminación',
            '¿Está seguro de eliminar este cuestionario? Esta acción no se puede deshacer.'
        )) {
            return;
        }
        
        this.showLoader('Eliminando cuestionario...');
        
        try {
            const response = await fetch(`/admin/cuestionarios/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                this.showNotification('Cuestionario eliminado exitosamente', 'success');
                this.refreshTable();
                this.refreshStatistics();
            } else {
                throw new Error('Error al eliminar');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showNotification('Error al eliminar el cuestionario', 'error');
        } finally {
            this.hideLoader();
        }
    }
    
    async handleDuplicate(id) {
        this.showLoader('Duplicando cuestionario...');
        
        try {
            const response = await fetch(`/admin/cuestionarios/${id}/duplicate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (response.ok) {
                this.showNotification('Cuestionario duplicado exitosamente', 'success');
                this.refreshTable();
                this.refreshStatistics();
                
                // Optionally redirect to edit the new questionnaire
                if (result.id) {
                    window.location.href = `/admin/cuestionarios/${result.id}/edit`;
                }
            } else {
                throw new Error(result.message || 'Error al duplicar');
            }
        } catch (error) {
            console.error('Duplicate error:', error);
            this.showNotification('Error al duplicar el cuestionario', 'error');
        } finally {
            this.hideLoader();
        }
    }
    
    applyFilters() {
        if (this.dataTable) {
            this.dataTable.ajax.reload();
        } else {
            this.reloadPage();
        }
        
        this.updateFilterIndicators();
        this.updateUrl();
    }
    
    applySorting() {
        if (this.dataTable) {
            const columnIndex = this.getColumnIndex(this.sortConfig.column);
            if (columnIndex !== -1) {
                this.dataTable.order([columnIndex, this.sortConfig.direction]).draw();
            }
        } else {
            this.reloadPage();
        }
    }
    
    getColumnIndex(columnName) {
        const columnMap = {
            'id': 1,
            'evaluado': 2,
            'empresa': 3,
            'estado': 4,
            'created_at': 6,
            'updated_at': 7
        };
        
        return columnMap[columnName] || -1;
    }
    
    updateSortIndicators() {
        document.querySelectorAll('.sortable').forEach(header => {
            const column = header.dataset.column;
            const icon = header.querySelector('.sort-icon');
            
            if (icon) {
                icon.className = 'sort-icon fas';
                
                if (column === this.sortConfig.column) {
                    icon.classList.add(this.sortConfig.direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
                } else {
                    icon.classList.add('fa-sort');
                }
            }
        });
    }
    
    updateFilterIndicators() {
        const activeFilters = Object.values(this.filters).filter(value => value && value.toString().trim() !== '').length;
        const filterIndicator = document.getElementById('filterIndicator');
        
        if (filterIndicator) {
            if (activeFilters > 0) {
                filterIndicator.textContent = activeFilters;
                filterIndicator.style.display = 'inline-block';
            } else {
                filterIndicator.style.display = 'none';
            }
        }
    }
    
    updateUrl() {
        const params = new URLSearchParams();
        
        Object.keys(this.filters).forEach(key => {
            if (this.filters[key] && this.filters[key].toString().trim() !== '') {
                params.set(key, this.filters[key]);
            }
        });
        
        const newUrl = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
        history.replaceState(null, '', newUrl);
    }
    
    reloadPage() {
        this.updateUrl();
        window.location.reload();
    }
    
    refreshTable() {
        if (this.dataTable) {
            this.dataTable.ajax.reload(null, false);
        }
    }
    
    async loadStatistics() {
        try {
            const response = await fetch('/admin/cuestionarios/statistics', {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const stats = await response.json();
                this.updateStatisticsDisplay(stats);
            }
        } catch (error) {
            console.error('Error loading statistics:', error);
        }
    }
    
    async refreshStatistics() {
        await this.loadStatistics();
    }
    
    updateStatisticsDisplay(stats) {
        // Update statistics cards
        const elements = {
            'totalCuestionarios': stats.total || 0,
            'pendientes': stats.pendientes || 0,
            'enProgreso': stats.en_progreso || 0,
            'completados': stats.completados || 0
        };
        
        Object.keys(elements).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                const countElement = element.querySelector('.stat-number') || element;
                countElement.textContent = elements[key];
                
                // Add animation
                countElement.classList.add('stat-updated');
                setTimeout(() => countElement.classList.remove('stat-updated'), 1000);
            }
        });
        
        // Update progress chart if exists
        this.updateProgressChart(stats);
    }
    
    updateProgressChart(stats) {
        const chartElement = document.getElementById('progressChart');
        if (chartElement && typeof Chart !== 'undefined') {
            // Update or create chart
            this.createProgressChart(chartElement, stats);
        }
    }
    
    createProgressChart(element, stats) {
        const ctx = element.getContext('2d');
        
        if (this.progressChart) {
            this.progressChart.destroy();
        }
        
        this.progressChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'En Progreso', 'Completados'],
                datasets: [{
                    data: [stats.pendientes || 0, stats.en_progreso || 0, stats.completados || 0],
                    backgroundColor: ['#ffc107', '#17a2b8', '#28a745'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    showNotification(message, type = 'info', duration = 5000) {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true
            });
            
            Toast.fire({
                icon: type === 'error' ? 'error' : type === 'warning' ? 'warning' : type === 'success' ? 'success' : 'info',
                title: message
            });
        } else {
            alert(message);
        }
    }
    
    async showConfirmDialog(title, text) {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            });
            
            return result.isConfirmed;
        } else {
            return confirm(`${title}\n\n${text}`);
        }
    }
    
    showLoader(message = 'Cargando...') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    }
    
    hideLoader() {
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-initialize if we're on admin cuestionarios page
    if (document.body.classList.contains('admin-cuestionarios') || 
        window.location.pathname.includes('/admin/cuestionarios')) {
        window.adminCuestionarios = new AdminCuestionarios();
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminCuestionarios;
}