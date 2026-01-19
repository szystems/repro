/**
 * Cuestionario JavaScript Module
 * Maneja la navegación, auto-guardado y UX del sistema de cuestionarios
 */
class CuestionarioManager {
    constructor(options = {}) {
        this.token = options.token || null;
        this.currentSection = parseInt(options.currentSection) || 1;
        this.autoSaveInterval = options.autoSaveInterval || 30000; // 30 segundos
        this.maxSections = options.maxSections || 5;
        this.baseUrl = options.baseUrl || '/cuestionario';
        
        this.autoSaveTimer = null;
        this.isDirty = false;
        this.isSubmitting = false;
        this.validationErrors = {};
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupAutoSave();
        this.setupValidation();
        this.setupNavigation();
        this.setupSignaturePad();
        this.loadProgress();
        
        console.log('CuestionarioManager inicializado para sección:', this.currentSection);
    }
    
    setupEventListeners() {
        // Form change detection
        document.addEventListener('input', (e) => {
            if (e.target.closest('#cuestionarioForm')) {
                this.markDirty();
            }
        });
        
        document.addEventListener('change', (e) => {
            if (e.target.closest('#cuestionarioForm')) {
                this.markDirty();
                this.validateField(e.target);
            }
        });
        
        // Navigation buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-siguiente')) {
                e.preventDefault();
                this.nextSection();
            }
            
            if (e.target.matches('.btn-anterior')) {
                e.preventDefault();
                this.previousSection();
            }
            
            if (e.target.matches('.btn-guardar-borrador')) {
                e.preventDefault();
                this.saveAsDraft();
            }
            
            if (e.target.matches('.btn-finalizar')) {
                e.preventDefault();
                this.finalizeCuestionario();
            }
        });
        
        // Prevent accidental page leave
        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty && !this.isSubmitting) {
                e.preventDefault();
                e.returnValue = '¿Está seguro de salir? Los cambios no guardados se perderán.';
                return e.returnValue;
            }
        });
        
        // Progress indicators
        this.updateProgressIndicators();
    }
    
    setupAutoSave() {
        if (this.autoSaveInterval > 0) {
            this.autoSaveTimer = setInterval(() => {
                if (this.isDirty && !this.isSubmitting) {
                    this.autoSave();
                }
            }, this.autoSaveInterval);
        }
    }
    
    setupValidation() {
        // Real-time validation setup
        const form = document.getElementById('cuestionarioForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.validateAndSubmit();
            });
        }
    }
    
    setupNavigation() {
        // Section navigation setup
        this.updateNavigationButtons();
        this.highlightCurrentSection();
    }
    
    setupSignaturePad() {
        const canvas = document.getElementById('signatureCanvas');
        if (canvas && this.currentSection === 5) {
            this.initializeSignaturePad(canvas);
        }
    }
    
    initializeSignaturePad(canvas) {
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;
        
        // Set canvas size
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        
        // Set drawing properties
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        
        // Mouse events
        canvas.addEventListener('mousedown', (e) => {
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            lastX = e.clientX - rect.left;
            lastY = e.clientY - rect.top;
        });
        
        canvas.addEventListener('mousemove', (e) => {
            if (!isDrawing) return;
            
            const rect = canvas.getBoundingClientRect();
            const currentX = e.clientX - rect.left;
            const currentY = e.clientY - rect.top;
            
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(currentX, currentY);
            ctx.stroke();
            
            lastX = currentX;
            lastY = currentY;
            
            this.markDirty();
        });
        
        canvas.addEventListener('mouseup', () => {
            isDrawing = false;
        });
        
        canvas.addEventListener('mouseout', () => {
            isDrawing = false;
        });
        
        // Touch events for mobile
        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            lastX = touch.clientX - rect.left;
            lastY = touch.clientY - rect.top;
            isDrawing = true;
        });
        
        canvas.addEventListener('touchmove', (e) => {
            if (!isDrawing) return;
            e.preventDefault();
            
            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            const currentX = touch.clientX - rect.left;
            const currentY = touch.clientY - rect.top;
            
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(currentX, currentY);
            ctx.stroke();
            
            lastX = currentX;
            lastY = currentY;
            
            this.markDirty();
        });
        
        canvas.addEventListener('touchend', () => {
            isDrawing = false;
        });
        
        // Clear signature button
        const clearBtn = document.getElementById('clearSignature');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                this.markDirty();
            });
        }
    }
    
    markDirty() {
        this.isDirty = true;
        this.showUnsavedChangesIndicator();
    }
    
    markClean() {
        this.isDirty = false;
        this.hideUnsavedChangesIndicator();
    }
    
    showUnsavedChangesIndicator() {
        let indicator = document.getElementById('unsavedIndicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'unsavedIndicator';
            indicator.className = 'alert alert-warning position-fixed';
            indicator.style.cssText = 'top: 20px; right: 20px; z-index: 1050; width: auto;';
            indicator.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Cambios sin guardar';
            document.body.appendChild(indicator);
        }
        indicator.style.display = 'block';
    }
    
    hideUnsavedChangesIndicator() {
        const indicator = document.getElementById('unsavedIndicator');
        if (indicator) {
            indicator.style.display = 'none';
        }
    }
    
    async nextSection() {
        if (this.currentSection >= this.maxSections) {
            this.showNotification('Ya está en la última sección', 'info');
            return;
        }
        
        // Validate current section before proceeding
        if (await this.validateCurrentSection()) {
            await this.saveCurrentSection();
            window.location.href = `${this.baseUrl}/${this.token}/seccion/${this.currentSection + 1}`;
        }
    }
    
    async previousSection() {
        if (this.currentSection <= 1) {
            this.showNotification('Ya está en la primera sección', 'info');
            return;
        }
        
        await this.saveCurrentSection();
        window.location.href = `${this.baseUrl}/${this.token}/seccion/${this.currentSection - 1}`;
    }
    
    async validateCurrentSection() {
        const form = document.getElementById('cuestionarioForm');
        if (!form) return true;
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch(`${this.baseUrl}/${this.token}/validar-seccion/${this.currentSection}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const result = await response.json();
            
            if (!response.ok) {
                this.displayValidationErrors(result.errors || {});
                this.showNotification('Por favor, corrija los errores antes de continuar', 'error');
                return false;
            }
            
            this.clearValidationErrors();
            return true;
        } catch (error) {
            console.error('Error validating section:', error);
            this.showNotification('Error al validar la sección', 'error');
            return false;
        }
    }
    
    async saveCurrentSection() {
        const form = document.getElementById('cuestionarioForm');
        if (!form) return;
        
        const formData = new FormData(form);
        
        // Add signature if exists
        const canvas = document.getElementById('signatureCanvas');
        if (canvas && this.currentSection === 5) {
            const signatureData = canvas.toDataURL();
            formData.append('firma_digital', signatureData);
        }
        
        try {
            this.isSubmitting = true;
            
            const response = await fetch(`${this.baseUrl}/${this.token}/guardar-seccion/${this.currentSection}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });
            
            if (response.ok) {
                this.markClean();
                this.updateProgress();
            }
        } catch (error) {
            console.error('Error saving section:', error);
            this.showNotification('Error al guardar la sección', 'error');
        } finally {
            this.isSubmitting = false;
        }
    }
    
    async autoSave() {
        if (this.isSubmitting) return;
        
        try {
            await this.saveCurrentSection();
            this.showNotification('Guardado automático realizado', 'success', 2000);
        } catch (error) {
            console.error('Auto-save failed:', error);
        }
    }
    
    async saveAsDraft() {
        this.showLoader('Guardando borrador...');
        await this.saveCurrentSection();
        this.hideLoader();
        this.showNotification('Borrador guardado exitosamente', 'success');
    }
    
    async finalizeCuestionario() {
        if (!await this.validateCurrentSection()) {
            return;
        }
        
        // Verify all sections are complete
        if (!await this.verifyAllSectionsComplete()) {
            this.showNotification('Debe completar todas las secciones antes de finalizar', 'warning');
            return;
        }
        
        // Show confirmation dialog
        const confirmed = await this.showConfirmDialog(
            '¿Está seguro de finalizar el cuestionario?',
            'Una vez finalizado, no podrá realizar más cambios.'
        );
        
        if (!confirmed) return;
        
        this.showLoader('Finalizando cuestionario...');
        
        try {
            const response = await fetch(`${this.baseUrl}/${this.token}/finalizar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                window.location.href = `${this.baseUrl}/${this.token}/completado`;
            } else {
                throw new Error('Error al finalizar cuestionario');
            }
        } catch (error) {
            console.error('Error finalizing questionnaire:', error);
            this.showNotification('Error al finalizar el cuestionario', 'error');
        } finally {
            this.hideLoader();
        }
    }
    
    async verifyAllSectionsComplete() {
        try {
            const response = await fetch(`${this.baseUrl}/${this.token}/verificar-completitud`, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const result = await response.json();
            return result.complete || false;
        } catch (error) {
            console.error('Error verifying completeness:', error);
            return false;
        }
    }
    
    validateField(field) {
        // Clear previous errors for this field
        this.clearFieldError(field);
        
        // Basic validation
        if (field.hasAttribute('required') && !field.value.trim()) {
            this.showFieldError(field, 'Este campo es obligatorio');
            return false;
        }
        
        // Email validation
        if (field.type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                this.showFieldError(field, 'Ingrese un email válido');
                return false;
            }
        }
        
        // Number validation
        if (field.type === 'number' && field.value) {
            const min = field.getAttribute('min');
            const max = field.getAttribute('max');
            const value = parseFloat(field.value);
            
            if (min && value < parseFloat(min)) {
                this.showFieldError(field, `El valor mínimo es ${min}`);
                return false;
            }
            
            if (max && value > parseFloat(max)) {
                this.showFieldError(field, `El valor máximo es ${max}`);
                return false;
            }
        }
        
        return true;
    }
    
    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        
        feedback.textContent = message;
    }
    
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
    
    displayValidationErrors(errors) {
        Object.keys(errors).forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                this.showFieldError(field, errors[fieldName][0]);
            }
        });
    }
    
    clearValidationErrors() {
        document.querySelectorAll('.is-invalid').forEach(field => {
            this.clearFieldError(field);
        });
    }
    
    updateProgressIndicators() {
        // Update progress bar
        this.updateProgressBar();
        
        // Update section indicators
        this.updateSectionIndicators();
    }
    
    updateProgressBar() {
        const progressBar = document.querySelector('.progress-bar');
        if (progressBar) {
            const percentage = (this.currentSection / this.maxSections) * 100;
            progressBar.style.width = `${percentage}%`;
            progressBar.textContent = `${Math.round(percentage)}%`;
        }
    }
    
    updateSectionIndicators() {
        document.querySelectorAll('.section-indicator').forEach((indicator, index) => {
            const sectionNumber = index + 1;
            
            if (sectionNumber < this.currentSection) {
                indicator.classList.add('completed');
                indicator.classList.remove('active');
            } else if (sectionNumber === this.currentSection) {
                indicator.classList.add('active');
                indicator.classList.remove('completed');
            } else {
                indicator.classList.remove('active', 'completed');
            }
        });
    }
    
    updateNavigationButtons() {
        const btnAnterior = document.querySelector('.btn-anterior');
        const btnSiguiente = document.querySelector('.btn-siguiente');
        const btnFinalizar = document.querySelector('.btn-finalizar');
        
        if (btnAnterior) {
            btnAnterior.style.display = this.currentSection > 1 ? 'inline-block' : 'none';
        }
        
        if (btnSiguiente) {
            btnSiguiente.style.display = this.currentSection < this.maxSections ? 'inline-block' : 'none';
        }
        
        if (btnFinalizar) {
            btnFinalizar.style.display = this.currentSection === this.maxSections ? 'inline-block' : 'none';
        }
    }
    
    highlightCurrentSection() {
        document.querySelectorAll('.section-nav-item').forEach((item, index) => {
            if (index + 1 === this.currentSection) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
    
    async loadProgress() {
        try {
            const response = await fetch(`${this.baseUrl}/${this.token}/progreso`, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const progress = await response.json();
                this.updateProgressDisplay(progress);
            }
        } catch (error) {
            console.error('Error loading progress:', error);
        }
    }
    
    updateProgressDisplay(progress) {
        // Update progress indicators based on server data
        const progressBar = document.querySelector('.progress-bar');
        if (progressBar && progress.porcentaje) {
            progressBar.style.width = `${progress.porcentaje}%`;
            progressBar.textContent = `${progress.porcentaje}%`;
        }
        
        // Update section completion status
        if (progress.secciones) {
            Object.keys(progress.secciones).forEach(sectionKey => {
                const sectionNumber = parseInt(sectionKey);
                const isComplete = progress.secciones[sectionKey];
                
                const indicator = document.querySelector(`.section-indicator[data-section="${sectionNumber}"]`);
                if (indicator) {
                    if (isComplete) {
                        indicator.classList.add('completed');
                    } else {
                        indicator.classList.remove('completed');
                    }
                }
            });
        }
    }
    
    showNotification(message, type = 'info', duration = 5000) {
        // Use SweetAlert2 if available, otherwise use browser alert
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
    
    destroy() {
        if (this.autoSaveTimer) {
            clearInterval(this.autoSaveTimer);
        }
        
        // Remove event listeners if needed
        window.removeEventListener('beforeunload', this.beforeUnloadHandler);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-initialize if we're on a questionnaire page
    const cuestionarioContainer = document.getElementById('cuestionarioContainer');
    if (cuestionarioContainer) {
        const token = cuestionarioContainer.dataset.token;
        const currentSection = cuestionarioContainer.dataset.currentSection;
        const baseUrl = cuestionarioContainer.dataset.baseUrl || '/cuestionario';
        
        window.cuestionarioManager = new CuestionarioManager({
            token: token,
            currentSection: parseInt(currentSection),
            baseUrl: baseUrl
        });
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CuestionarioManager;
}