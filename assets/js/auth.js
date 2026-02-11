// Authentication JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form validation
    initFormValidation();
    
    // Initialize password toggle
    initPasswordToggle();
    
    // Initialize auto-hide alerts
    initAutoHideAlerts();
});

function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Real-time validation
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', validateInput);
            input.addEventListener('input', clearInputError);
        });
        
        // Form submission
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Password strength checker
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordStrength);
    }
    
    // Confirm password validation
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', validateConfirmPassword);
    }
}

function validateInput(e) {
    const input = e.target;
    const value = input.value.trim();
    const formGroup = input.closest('.form-group');
    let error = '';
    
    // Clear previous error
    clearInputError({ target: input });
    
    // Required field validation
    if (input.hasAttribute('required') && !value) {
        error = 'This field is required.';
    }
    
    // Email validation
    else if (input.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            error = 'Please enter a valid email address.';
        }
    }
    
    // Phone validation
    else if (input.type === 'tel' && value) {
        const phoneRegex = /^[0-9+\-\s()]{10,}$/;
        if (!phoneRegex.test(value)) {
            error = 'Please enter a valid phone number.';
        }
    }
    
    // Username validation
    else if (input.name === 'username' && value) {
        const usernameRegex = /^[a-zA-Z0-9_]{3,50}$/;
        if (!usernameRegex.test(value)) {
            error = 'Username must be 3-50 characters (letters, numbers, underscores only).';
        }
    }
    
    // Display error
    if (error) {
        showInputError(input, error);
        return false;
    }
    
    return true;
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        const event = new Event('blur');
        input.dispatchEvent(event);
        
        if (input.closest('.form-group').classList.contains('invalid')) {
            isValid = false;
            if (!isValid) {
                input.focus();
            }
        }
    });
    
    return isValid;
}

function showInputError(input, message) {
    const formGroup = input.closest('.form-group');
    formGroup.classList.add('invalid');
    
    let errorElement = formGroup.querySelector('.error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error';
        formGroup.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
    
    // Add animation
    errorElement.style.animation = 'none';
    setTimeout(() => {
        errorElement.style.animation = 'slideIn 0.3s ease';
    }, 10);
}

function clearInputError(e) {
    const input = e.target;
    const formGroup = input.closest('.form-group');
    
    if (formGroup) {
        formGroup.classList.remove('invalid');
        const errorElement = formGroup.querySelector('.error');
        if (errorElement) {
            errorElement.textContent = '';
        }
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthMeter = document.getElementById('password-strength');
    
    if (!strengthMeter) return;
    
    let strength = 0;
    let tips = '';
    
    // Check password length
    if (password.length >= 8) {
        strength += 1;
    } else {
        tips += 'Make the password at least 8 characters. ';
    }
    
    // Check for mixed case
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) {
        strength += 1;
    } else {
        tips += 'Use both lowercase and uppercase letters. ';
    }
    
    // Check for numbers
    if (password.match(/\d/)) {
        strength += 1;
    } else {
        tips += 'Include at least one number. ';
    }
    
    // Check for special characters
    if (password.match(/[^a-zA-Z\d]/)) {
        strength += 1;
    } else {
        tips += 'Include at least one special character. ';
    }
    
    // Update strength meter
    const strengthText = ['Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'];
    const strengthColors = ['#dc3545', '#ffc107', '#ffc107', '#28a745', '#28a745'];
    
    strengthMeter.textContent = strengthText[strength];
    strengthMeter.style.color = strengthColors[strength];
}

function validateConfirmPassword() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (!password || !confirmPassword) return;
    
    if (password.value !== confirmPassword.value) {
        showInputError(confirmPassword, 'Passwords do not match.');
        return false;
    }
    
    clearInputError({ target: confirmPassword });
    return true;
}

function initPasswordToggle() {
    // Add password toggle buttons
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    
    passwordInputs.forEach(input => {
        const wrapper = document.createElement('div');
        wrapper.className = 'password-wrapper';
        wrapper.style.position = 'relative';
        
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'password-toggle';
        toggleBtn.innerHTML = '👁️‍🗨️';
        toggleBtn.style.position = 'absolute';
        toggleBtn.style.right = '10px';
        toggleBtn.style.top = '50%';
        toggleBtn.style.transform = 'translateY(-50%)';
        toggleBtn.style.background = 'none';
        toggleBtn.style.border = 'none';
        toggleBtn.style.cursor = 'pointer';
        toggleBtn.style.fontSize = '1.2rem';
        
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        wrapper.appendChild(toggleBtn);
        
        toggleBtn.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                toggleBtn.innerHTML = '👁️';
            } else {
                input.type = 'password';
                toggleBtn.innerHTML = '👁️‍🗨️';
            }
        });
    });
}

function initAutoHideAlerts() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 300);
        }, 5000);
    });
}

// Real-time username/email availability check
function checkAvailability(type, value) {
    if (value.length < 3) return;
    
    fetch(`api/auth/check_${type}.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            [type]: value
        })
    })
    .then(response => response.json())
    .then(data => {
        const input = document.getElementById(type);
        const formGroup = input.closest('.form-group');
        
        if (data.available) {
            formGroup.classList.remove('invalid');
            formGroup.classList.add('valid');
        } else {
            showInputError(input, `${type.charAt(0).toUpperCase() + type.slice(1)} already taken.`);
        }
    })
    .catch(console.error);
}

// Debounce function for availability checks
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize availability checks with debounce
const debouncedCheckUsername = debounce((value) => checkAvailability('username', value), 500);
const debouncedCheckEmail = debounce((value) => checkAvailability('email', value), 500);

document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            debouncedCheckUsername(this.value);
        });
    }
    
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            debouncedCheckEmail(this.value);
        });
    }
});
