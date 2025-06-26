// Placeholder for JavaScript enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Debug mode - set to false for production
    const DEBUG_MODE = false;
    
    function debugLog(message, data = null) {
        if (DEBUG_MODE) {
            console.log('[RDL Debug]', message, data);
        }
    }

    // Enhanced confirmation for delete
    const deleteLinks = document.querySelectorAll('a[href*="delete_candidate.php"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            
            const candidateId = this.href.split('id=')[1];
            const confirmMessage = `Are you sure you want to delete candidate with ID: ${candidateId}?\n\nThis action cannot be undone.`;
            
            if (confirm(confirmMessage)) {
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                window.location.href = this.href;
            }
        });
    });
    
    // Form validation with enhanced error handling
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const errors = [];
            
            debugLog('Validating form:', form);
            
            // Validate National ID (if present)
            const nationalIdField = form.querySelector('input[name="candidateNationalId"]');
            if (nationalIdField && nationalIdField.value.trim() !== '') {
                const value = nationalIdField.value.trim();
                if (value.length !== 16) {
                    showValidationError(nationalIdField, 'National ID must be exactly 16 digits');
                    errors.push('Invalid National ID length');
                    isValid = false;
                } else if (!/^\d+$/.test(value)) {
                    showValidationError(nationalIdField, 'National ID must contain only numbers');
                    errors.push('Invalid National ID format');
                    isValid = false;
                } else {
                    clearValidationError(nationalIdField);
                }
            }
            
            // Validate Phone Number (if present)
            const phoneField = form.querySelector('input[name="phoneNumber"]');
            if (phoneField && phoneField.value.trim() !== '') {
                const value = phoneField.value.trim();
                if (!/^07\d{8}$/.test(value)) {
                    showValidationError(phoneField, 'Phone number must be in format 07XXXXXXXX (10 digits starting with 07)');
                    errors.push('Invalid phone number format');
                    isValid = false;
                } else {
                    clearValidationError(phoneField);
                }
            }
            
            // Validate Obtained Marks (if present)
            const marksField = form.querySelector('input[name="obtainedMarks"]');
            if (marksField && marksField.value.trim() !== '') {
                const value = parseInt(marksField.value);
                if (isNaN(value) || value < 0 || value > 20) {
                    showValidationError(marksField, 'Marks must be a number between 0 and 20');
                    errors.push('Invalid marks value');
                    isValid = false;
                } else {
                    clearValidationError(marksField);
                }
            }
            
            // Validate Date fields
            const dateFields = form.querySelectorAll('input[type="date"]');
            dateFields.forEach(dateField => {
                if (dateField.value) {
                    const selectedDate = new Date(dateField.value);
                    const today = new Date();
                    
                    if (dateField.name === 'dob') {
                        // Date of birth should be in the past and person should be at least 18
                        const eighteenYearsAgo = new Date();
                        eighteenYearsAgo.setFullYear(today.getFullYear() - 18);
                        
                        if (selectedDate > eighteenYearsAgo) {
                            showValidationError(dateField, 'Candidate must be at least 18 years old');
                            errors.push('Invalid date of birth');
                            isValid = false;
                        } else {
                            clearValidationError(dateField);
                        }
                    }
                    
                    if (dateField.name === 'examDate') {
                        // Exam date should not be too far in the future
                        const oneYearFromNow = new Date();
                        oneYearFromNow.setFullYear(today.getFullYear() + 1);
                        
                        if (selectedDate > oneYearFromNow) {
                            showValidationError(dateField, 'Exam date cannot be more than one year in the future');
                            errors.push('Invalid exam date');
                            isValid = false;
                        } else {
                            clearValidationError(dateField);
                        }
                    }
                }
            });
            
            // Enhanced password validation (if present)
            const passwordField = form.querySelector('input[name="password"]');
            if (passwordField && passwordField.value.trim() !== '') {
                const value = passwordField.value.trim();
                const passwordErrors = [];
                
                if (value.length < 8) {
                    passwordErrors.push('at least 8 characters');
                }
                if (!/[A-Z]/.test(value)) {
                    passwordErrors.push('one uppercase letter');
                }
                if (!/[a-z]/.test(value)) {
                    passwordErrors.push('one lowercase letter');
                }
                if (!/[0-9]/.test(value)) {
                    passwordErrors.push('one number');
                }
                if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)) {
                    passwordErrors.push('one special character');
                }
                
                if (passwordErrors.length > 0) {
                    showValidationError(passwordField, `Password must contain ${passwordErrors.join(', ')}`);
                    errors.push('Invalid password format');
                    isValid = false;
                } else {
                    clearValidationError(passwordField);
                }
            }
            
            // Confirm password validation
            const confirmPasswordField = form.querySelector('input[name="confirm_password"]');
            if (confirmPasswordField && passwordField) {
                if (confirmPasswordField.value !== passwordField.value) {
                    showValidationError(confirmPasswordField, 'Passwords do not match');
                    errors.push('Password mismatch');
                    isValid = false;
                } else {
                    clearValidationError(confirmPasswordField);
                }
            }
            
            debugLog('Form validation result:', { isValid, errors });
            
            if (!isValid) {
                event.preventDefault();
                
                // Show summary of errors
                if (errors.length > 0) {
                    window.showToast(`Please fix ${errors.length} validation error(s)`, 'error');
                }
                
                // Focus on first error field
                const firstErrorField = form.querySelector('.input-error');
                if (firstErrorField) {
                    firstErrorField.focus();
                }
            }
        });
    });
    
    // Table sorting functionality
    const tableSortHeaders = document.querySelectorAll('th[data-sort]');
    tableSortHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const table = header.closest('table');
            const columnIndex = Array.from(header.parentNode.children).indexOf(header);
            const sortDirection = header.getAttribute('data-sort-direction') === 'asc' ? 'desc' : 'asc';
            
            // Update sort direction
            tableSortHeaders.forEach(h => h.removeAttribute('data-sort-direction'));
            header.setAttribute('data-sort-direction', sortDirection);
            
            // Update visual indicators
            tableSortHeaders.forEach(h => h.classList.remove('sorted-asc', 'sorted-desc'));
            header.classList.add(sortDirection === 'asc' ? 'sorted-asc' : 'sorted-desc');
            
            // Sort the table
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            rows.sort((a, b) => {
                const aValue = a.children[columnIndex].textContent.trim();
                const bValue = b.children[columnIndex].textContent.trim();
                
                if (sortDirection === 'asc') {
                    return aValue.localeCompare(bValue);
                } else {
                    return bValue.localeCompare(aValue);
                }
            });
            
            // Update the DOM
            const tbody = table.querySelector('tbody');
            rows.forEach(row => tbody.appendChild(row));
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.querySelector('table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                let found = false;
                const cells = row.querySelectorAll('td');
                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                    }
                });
                
                row.style.display = found ? '' : 'none';
            });
        });
    }
    
    // Dashboard stats animation
    function animateCounters() {
        const counters = document.querySelectorAll('.stat-content h3');
        counters.forEach(counter => {
            const target = parseInt(counter.textContent);
            const increment = target / 100;
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                counter.textContent = Math.floor(current);
            }, 20);
        });
    }

    // Enhanced search functionality
    function setupAdvancedSearch() {
        const searchInput = document.querySelector('#searchInput');
        const categoryFilter = document.querySelector('#categoryFilter');
        const decisionFilter = document.querySelector('#decisionFilter');
        
        if (searchInput) {
            // Add search icon and clear button
            const searchGroup = searchInput.parentElement;
            searchGroup.classList.add('search-input-group');
            
            // Add clear button functionality
            searchInput.addEventListener('input', function() {
                const clearBtn = searchGroup.querySelector('.clear-search');
                if (this.value.length > 0 && !clearBtn) {
                    const clearButton = document.createElement('button');
                    clearButton.type = 'button';
                    clearButton.className = 'clear-search';
                    clearButton.innerHTML = '<i class="fas fa-times"></i>';
                    clearButton.onclick = () => {
                        searchInput.value = '';
                        clearButton.remove();
                        searchInput.focus();
                    };
                    searchGroup.appendChild(clearButton);
                } else if (this.value.length === 0 && clearBtn) {
                    clearBtn.remove();
                }
            });
        }
    }

    // Auto-hide alerts after 5 seconds
    function setupAutoHideAlerts() {
        const alerts = document.querySelectorAll('.alert-success, .alert-info');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease-out';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        });
    }

    // Enhanced table interactions
    function setupTableEnhancements() {
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            // Add loading state for delete operations
            const deleteLinks = table.querySelectorAll('a[onclick*="confirmDelete"]');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const originalText = this.innerHTML;
                    
                    if (confirm('Are you sure you want to delete this candidate? This action cannot be undone.')) {
                        this.innerHTML = '<span class="spinner"></span> Deleting...';
                        this.style.pointerEvents = 'none';
                        
                        // Proceed with deletion
                        window.location.href = this.getAttribute('onclick').match(/window\.location\.href='(.+?)'/)[1];
                    }
                });
            });
        });
    }

    // Form auto-save for drafts (optional enhancement)
    function setupFormAutoSave() {
        const forms = document.querySelectorAll('form[data-autosave]');
        forms.forEach(form => {
            const formId = form.getAttribute('data-autosave');
            const inputs = form.querySelectorAll('input, select, textarea');
            
            // Load saved data
            inputs.forEach(input => {
                const savedValue = localStorage.getItem(`${formId}_${input.name}`);
                if (savedValue && !input.value) {
                    input.value = savedValue;
                }
            });
            
            // Save data on change
            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    localStorage.setItem(`${formId}_${input.name}`, input.value);
                });
            });
            
            // Clear saved data on successful submission
            form.addEventListener('submit', () => {
                inputs.forEach(input => {
                    localStorage.removeItem(`${formId}_${input.name}`);
                });
            });
        });
    }

    // Registration form validation
    function setupRegistrationValidation() {
        const form = document.querySelector('form[action*="register.php"]');
        if (!form) return;

        const adminNameInput = form.querySelector('input[name="adminName"]');
        const passwordInput = form.querySelector('input[name="password"]');
        const confirmPasswordInput = form.querySelector('input[name="confirm_password"]');

        // Real-time validation for admin name
        if (adminNameInput) {
            adminNameInput.addEventListener('input', function() {
                const value = this.value.trim();
                const errorSpan = this.parentElement.querySelector('.error-message');
                
                // Remove existing error
                if (errorSpan) errorSpan.remove();
                this.classList.remove('input-error');
                
                if (value.length > 0 && value.length < 3) {
                    showFieldError(this, 'Admin name must be at least 3 characters long.');
                } else if (value.length > 0 && !/^[a-zA-Z0-9_]+$/.test(value)) {
                    showFieldError(this, 'Only letters, numbers, and underscores allowed.');
                }
            });
        }

        // Real-time validation for password
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const value = this.value;
                const errorSpan = this.parentElement.querySelector('.error-message');
                
                // Remove existing error
                if (errorSpan) errorSpan.remove();
                this.classList.remove('input-error');
                
                if (value.length > 0 && value.length < 8) {
                    showFieldError(this, 'Password must be at least 8 characters long.');
                } else if (value.length >= 8) {
                    let issues = [];
                    if (!/[A-Z]/.test(value)) issues.push('uppercase letter');
                    if (!/[a-z]/.test(value)) issues.push('lowercase letter');
                    if (!/[0-9]/.test(value)) issues.push('number');
                    
                    if (issues.length > 0) {
                        showFieldError(this, `Password must contain: ${issues.join(', ')}.`);
                    }
                }
                
                // Also validate confirm password if it has a value
                if (confirmPasswordInput && confirmPasswordInput.value) {
                    validateConfirmPassword();
                }
            });
        }

        // Real-time validation for confirm password
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', validateConfirmPassword);
        }

        function validateConfirmPassword() {
            const value = confirmPasswordInput.value;
            const passwordValue = passwordInput.value;
            const errorSpan = confirmPasswordInput.parentElement.querySelector('.error-message');
            
            // Remove existing error
            if (errorSpan) errorSpan.remove();
            confirmPasswordInput.classList.remove('input-error');
            
            if (value.length > 0 && value !== passwordValue) {
                showFieldError(confirmPasswordInput, 'Passwords do not match.');
            }
        }

        function showFieldError(field, message) {
            field.classList.add('input-error');
            const errorSpan = document.createElement('span');
            errorSpan.className = 'error-message';
            errorSpan.textContent = message;
            field.parentElement.appendChild(errorSpan);
        }

        // Form submission validation
        form.addEventListener('submit', function(e) {
            let hasErrors = false;
            const errorElements = form.querySelectorAll('.error-message');
            errorElements.forEach(el => el.remove());
            
            // Remove error classes
            const inputs = form.querySelectorAll('.input-error');
            inputs.forEach(input => input.classList.remove('input-error'));
            
            // Validate all fields
            if (adminNameInput.value.trim().length < 3) {
                showFieldError(adminNameInput, 'Admin name must be at least 3 characters long.');
                hasErrors = true;
            }
            
            if (passwordInput.value.length < 8) {
                showFieldError(passwordInput, 'Password must be at least 8 characters long.');
                hasErrors = true;
            }
            
            if (passwordInput.value !== confirmPasswordInput.value) {
                showFieldError(confirmPasswordInput, 'Passwords do not match.');
                hasErrors = true;
            }
            
            if (hasErrors) {
                e.preventDefault();
                adminNameInput.focus();
            }
        });
    }

    // Password strength indicator
    function setupPasswordStrengthIndicator() {
        const passwordInput = document.querySelector('input[name="password"]');
        if (!passwordInput) return;

        const strengthContainer = document.createElement('div');
        strengthContainer.className = 'password-strength';
        strengthContainer.innerHTML = `
            <div class="strength-bar">
                <div class="strength-fill"></div>
            </div>
            <div class="strength-text">Password strength: <span class="strength-level">Weak</span></div>
        `;
        
        passwordInput.parentElement.appendChild(strengthContainer);

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            const fill = strengthContainer.querySelector('.strength-fill');
            const level = strengthContainer.querySelector('.strength-level');
            
            fill.style.width = strength.percentage + '%';
            fill.className = `strength-fill strength-${strength.level}`;
            level.textContent = strength.text;
            level.className = `strength-level strength-${strength.level}`;
        });
    }

    function calculatePasswordStrength(password) {
        let score = 0;
        let level = 'weak';
        let text = 'Weak';
        
        // Length check
        if (password.length >= 8) score += 20;
        if (password.length >= 12) score += 10;
        
        // Character variety
        if (/[a-z]/.test(password)) score += 20;
        if (/[A-Z]/.test(password)) score += 20;
        if (/[0-9]/.test(password)) score += 15;
        if (/[^a-zA-Z0-9]/.test(password)) score += 15;
        
        if (score < 40) {
            level = 'weak';
            text = 'Weak';
        } else if (score < 70) {
            level = 'medium';
            text = 'Medium';
        } else {
            level = 'strong';
            text = 'Strong';
        }
        
        return {
            score: score,
            percentage: Math.min(score, 100),
            level: level,
            text: text
        };
    }

    // New enhancements
    if (document.querySelector('.stat-content h3')) {
        animateCounters();
    }
    
    setupAdvancedSearch();
    setupAutoHideAlerts();
    setupTableEnhancements();
    setupFormAutoSave();
    setupRegistrationValidation();
    setupPasswordStrengthIndicator();
    
    // Helper functions for form validation
    function showValidationError(field, message) {
        // Remove any existing error messages
        clearValidationError(field);
        
        // Add error class to input
        field.classList.add('input-error');
        
        // Create and append error message
        const errorSpan = document.createElement('span');
        errorSpan.className = 'error-message';
        errorSpan.textContent = message;
        field.parentNode.appendChild(errorSpan);
    }
    
    function clearValidationError(field) {
        field.classList.remove('input-error');
        const errorMessage = field.parentNode.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    }
    
    // Toast notification system
    window.showToast = function(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            const newToastContainer = document.createElement('div');
            newToastContainer.id = 'toast-container';
            document.body.appendChild(newToastContainer);
            newToastContainer.appendChild(toast);
        } else {
            toastContainer.appendChild(toast);
        }
        
        setTimeout(() => {
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }, 10);
    }
    
    // Show toast if there's a message in URL
    const urlParams = new URLSearchParams(window.location.search);
    const successMsg = urlParams.get('success');
    const errorMsg = urlParams.get('error');
    
    if (successMsg) {
        window.showToast(decodeURIComponent(successMsg), 'success');
    } else if (errorMsg) {
        window.showToast(decodeURIComponent(errorMsg), 'error');
    }
});
