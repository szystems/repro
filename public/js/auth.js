/**
 * Funciones compartidas para vistas de autenticación
 */

/**
 * Toggle password visibility
 * @param {string} passwordId - The password input ID
 * @param {string} toggleIconId - The toggle icon ID
 */
function togglePasswordVisibility(passwordId, toggleIconId) {
    const passwordInput = document.getElementById(passwordId);
    const toggleIcon = document.getElementById(toggleIconId);

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
}

/**
 * Evaluate password strength
 * @param {string} password - The password to evaluate
 * @returns {Object} - Strength information
 */
function evaluatePasswordStrength(password) {
    let strength = 0;
    let feedback = '';

    // Check password length
    if (password.length >= 8) {
        strength += 25;
    }

    // Check for lowercase letters
    if (/[a-z]/.test(password)) {
        strength += 25;
    }

    // Check for uppercase letters
    if (/[A-Z]/.test(password)) {
        strength += 25;
    }

    // Check for numbers
    if (/\d/.test(password)) {
        strength += 25;
    }

    // Determine feedback text and color
    if (strength < 25) {
        feedback = 'Muy débil';
        color = 'danger';
    } else if (strength < 50) {
        feedback = 'Débil';
        color = 'warning';
    } else if (strength < 75) {
        feedback = 'Buena';
        color = 'info';
    } else {
        feedback = 'Fuerte';
        color = 'success';
    }

    return {
        strength: strength,
        feedback: feedback,
        color: color
    };
}

/**
 * Validate email format
 * @param {string} email - The email to validate
 * @returns {boolean} - True if valid
 */
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

/**
 * Initialize form with basic client-side validation
 * @param {string} formId - The form ID
 * @param {string} submitButtonId - The submit button ID
 * @param {Object} options - Configuration options
 */
function initFormValidation(formId, submitButtonId, options = {}) {
    const form = document.getElementById(formId);
    const submitButton = document.getElementById(submitButtonId);

    if (!form || !submitButton) return;

    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Validate all required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');

                // Email validation
                if (field.type === 'email' && !isValidEmail(field.value)) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            }
        });

        // Password confirmation validation
        if (options.passwordConfirmation) {
            const password = document.getElementById(options.passwordField);
            const confirmation = document.getElementById(options.confirmField);

            if (password && confirmation && password.value !== confirmation.value) {
                confirmation.classList.add('is-invalid');
                isValid = false;
            }
        }

        if (!isValid) {
            e.preventDefault();
        } else if (options.loadingText) {
            // Show loading state
            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${options.loadingText}`;
            submitButton.disabled = true;
        }
    });

    // Clear validation states on input
    form.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
}
