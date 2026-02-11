/* 
 * KilimoSafi - Main JavaScript File
 * Handles product filtering, search, and interactive features
 */

// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all features
    initProductFiltering();
    initSearchFunctionality();
    initMobileMenu();
    initSmoothScrolling();
    initModals();
    initCart();
    
    console.log('KilimoSafi platform loaded successfully!');
});

// Product Filtering System
function initProductFiltering() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');
    
    if (!filterButtons.length || !productCards.length) return;
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            const category = this.dataset.category;
            
            // Filter products
            productCards.forEach(card => {
                if (category === 'all') {
                    card.style.display = 'block';
                } else {
                    const cardCategory = card.dataset.category;
                    if (cardCategory === category) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
            
            // Animate the grid
            const productsGrid = document.getElementById('productsGrid');
            if (productsGrid) {
                productsGrid.style.opacity = '0.5';
                setTimeout(() => {
                    productsGrid.style.opacity = '1';
                }, 300);
            }
        });
    });
}

// Search Functionality
function initSearchFunctionality() {
    const searchInput = document.getElementById('searchInput');
    const productCards = document.querySelectorAll('.product-card');
    
    if (!searchInput || !productCards.length) return;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        productCards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const description = card.querySelector('p').textContent.toLowerCase();
            const category = card.querySelector('.product-category').textContent.toLowerCase();
            
            if (title.includes(searchTerm) || 
                description.includes(searchTerm) || 
                category.includes(searchTerm) ||
                searchTerm === '') {
                card.style.display = 'block';
                card.classList.add('search-match');
                
                // Remove highlight after animation
                setTimeout(() => {
                    card.classList.remove('search-match');
                }, 500);
            } else {
                card.style.display = 'none';
                card.classList.remove('search-match');
            }
        });
    });
}

// Mobile Menu Toggle
function initMobileMenu() {
    const nav = document.querySelector('nav');
    if (!nav) return;
    
    const navLinks = document.querySelector('.nav-links');
    if (!navLinks) return;
    
    // Create hamburger menu button if it doesn't exist
    let menuToggle = document.querySelector('.menu-toggle');
    if (!menuToggle) {
        menuToggle = document.createElement('button');
        menuToggle.className = 'menu-toggle';
        menuToggle.innerHTML = '☰';
        menuToggle.setAttribute('aria-label', 'Toggle menu');
        nav.insertBefore(menuToggle, navLinks);
    }
    
    menuToggle.addEventListener('click', function() {
        navLinks.classList.toggle('show');
        menuToggle.innerHTML = navLinks.classList.contains('show') ? '✕' : '☰';
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!nav.contains(event.target) && window.innerWidth <= 768) {
            navLinks.classList.remove('show');
            menuToggle.innerHTML = '☰';
        }
    });
    
    // Close menu when clicking on a link
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                navLinks.classList.remove('show');
                menuToggle.innerHTML = '☰';
            }
        });
    });
}

// Smooth Scrolling for Anchor Links
function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Skip if href is just #
            if (href === '#') return;
            
            // Check if it's an anchor link on the same page
            if (href.startsWith('#') && document.querySelector(href)) {
                e.preventDefault();
                const target = document.querySelector(href);
                
                window.scrollTo({
                    top: target.offsetTop - 80,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const navLinks = document.querySelector('.nav-links');
                const menuToggle = document.querySelector('.menu-toggle');
                if (navLinks && navLinks.classList.contains('show')) {
                    navLinks.classList.remove('show');
                    menuToggle.innerHTML = '☰';
                }
            }
        });
    });
}

// Modal System
function initModals() {
    // Open modal buttons
    document.querySelectorAll('[data-modal-target]').forEach(button => {
        button.addEventListener('click', function() {
            const modalId = this.dataset.modalTarget;
            const modal = document.getElementById(modalId);
            if (modal) {
                openModal(modal);
            }
        });
    });
    
    // Close modal buttons
    document.querySelectorAll('.close-modal, .modal').forEach(element => {
        element.addEventListener('click', function(e) {
            if (e.target === this || e.target.classList.contains('close-modal')) {
                closeModal(this.closest('.modal') || this);
            }
        });
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                closeModal(openModal);
            }
        }
    });
    
    // Close modal when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this);
            }
        });
    });
}

function openModal(modal) {
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal(modal) {
    modal.classList.remove('show');
    document.body.style.overflow = '';
}

// Cart functionality
function initCart() {
    // Cart buttons
    document.querySelectorAll('[data-add-to-cart]').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const type = this.dataset.type || 'buy';
            
            // Open cart modal or directly add to cart
            if (typeof addToCart === 'function') {
                addToCart(productId, type);
            }
        });
    });
    
    // Update cart count
    updateCartCount();
}

function addToCart(productId, type) {
    const modal = document.getElementById('cartModal');
    if (modal) {
        const productIdInput = document.getElementById('productId');
        const actionTypeInput = document.getElementById('actionType');
        const rentDaysGroup = document.getElementById('rentDaysGroup');
        
        if (productIdInput) productIdInput.value = productId;
        if (actionTypeInput) actionTypeInput.value = type;
        
        // Show/hide rent days field
        if (rentDaysGroup) {
            if (type === 'rent') {
                rentDaysGroup.classList.remove('hidden');
            } else {
                rentDaysGroup.classList.add('hidden');
            }
        }
        
        openModal(modal);
    } else {
        // Direct AJAX call if no modal
        addToCartAjax(productId, type, 1, type === 'rent' ? 1 : null);
    }
}

function addToCartAjax(productId, type, quantity, rentDays = null) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="csrf_token"]')?.value;
    
    fetch('api/cart/add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken || ''
        },
        body: JSON.stringify({
            product_id: productId,
            type: type,
            quantity: quantity,
            rent_days: rentDays,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || 'Item added to cart successfully!');
            updateCartCount();
        } else {
            showAlert('error', data.message || 'Failed to add item to cart.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred. Please try again.');
    });
}

function updateCartCount() {
    fetch('api/cart/get.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartCount = data.data.count || 0;
                const cartBadge = document.getElementById('cartCount');
                
                if (cartBadge) {
                    cartBadge.textContent = cartCount;
                    cartBadge.style.display = cartCount > 0 ? 'inline-block' : 'none';
                }
            }
        })
        .catch(console.error);
}

// Alert System
function showAlert(type, message, duration = 5000) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    });
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    
    // Add to page (before the first child of body)
    document.body.insertBefore(alert, document.body.firstChild);
    
    // Show alert with animation
    setTimeout(() => {
        alert.classList.add('show');
    }, 10);
    
    // Remove after duration
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 300);
    }, duration);
}

// Price Formatting Utility
function formatPrice(amount, currency = 'TSh') {
    return `${currency} ${parseFloat(amount).toLocaleString('en-US')}`;
}

// Form Validation
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const formGroup = field.closest('.form-group');
    
    if (!formGroup) return true;
    
    // Clear previous errors
    clearFieldError(field);
    
    // Check required fields
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Password validation
    if (field.type === 'password' && value) {
        if (value.length < 6) {
            showFieldError(field, 'Password must be at least 6 characters');
            return false;
        }
    }
    
    // Phone validation
    if (field.type === 'tel' && value) {
        const phoneRegex = /^[0-9+\-\s()]{10,}$/;
        if (!phoneRegex.test(value)) {
            showFieldError(field, 'Please enter a valid phone number');
            return false;
        }
    }
    
    // Number validation
    if (field.type === 'number' && value) {
        if (field.hasAttribute('min') && parseFloat(value) < parseFloat(field.getAttribute('min'))) {
            showFieldError(field, `Minimum value is ${field.getAttribute('min')}`);
            return false;
        }
        
        if (field.hasAttribute('max') && parseFloat(value) > parseFloat(field.getAttribute('max'))) {
            showFieldError(field, `Maximum value is ${field.getAttribute('max')}`);
            return false;
        }
    }
    
    return true;
}

function showFieldError(field, message) {
    const formGroup = field.closest('.form-group');
    if (!formGroup) return;
    
    formGroup.classList.add('invalid');
    
    let errorElement = formGroup.querySelector('.error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error';
        formGroup.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
}

function clearFieldError(field) {
    const formGroup = field.closest('.form-group');
    if (formGroup) {
        formGroup.classList.remove('invalid');
        const errorElement = formGroup.querySelector('.error');
        if (errorElement) {
            errorElement.textContent = '';
        }
    }
}

// Loading states for buttons
document.addEventListener('click', function(e) {
    const button = e.target.closest('.btn');
    if (button && !button.classList.contains('btn-loading')) {
        // Don't add loading to navigation buttons
        if (button.hasAttribute('href') && !button.hasAttribute('data-async')) {
            return;
        }
        
        if (button.type === 'submit' || button.hasAttribute('data-async')) {
            button.classList.add('btn-loading');
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="loading"></span> Loading...';
            button.disabled = true;
            
            // Reset after 10 seconds max
            setTimeout(() => {
                button.classList.remove('btn-loading');
                button.innerHTML = originalText;
                button.disabled = false;
            }, 10000);
        }
    }
});

// Export functions for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showAlert,
        formatPrice,
        validateForm,
        addToCart,
        updateCartCount
    };
}
