<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KilimoSafi - Agricultural Inputs Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<style> 
</style>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <a href="index.html" class="logo">Kilimo<span>Safi</span></a>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#products">Products</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="login.php" class="btn btn-outline">Login</a></li>
                    <li><a href="register.php" class="btn btn-primary">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Empowering Farmers with Quality Agricultural Inputs</h1>
            <p>Rent or buy seeds, fertilizers, pesticides, and farming tools at affordable prices. Join thousands of farmers improving their yields.</p>
            <a href="register.php" class="btn btn-primary">Get Started Today</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2>Why Choose KilimoSafi?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🌱</div>
                    <h3>Quality Products</h3>
                    <p>Access to high-quality seeds, fertilizers, and farming tools from trusted suppliers</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Rent or Buy</h3>
                    <p>Choose to rent expensive equipment or buy inputs based on your needs and budget</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">�</div>
                    <h3>Easy Ordering</h3>
                    <p>Simple online ordering process with real-time order tracking and status updates</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🚚</div>
                    <h3>Fast Delivery</h3>
                    <p>Quick and reliable delivery of agricultural inputs right to your farm</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3>Expert Support</h3>
                    <p>Get agricultural advice and support from our team of experts</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Track Your Orders</h3>
                    <p>Monitor your order status and receive notifications on every update</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Preview Section -->
    <section class="products-section" id="products">
        <div class="container">
            <div class="section-header">
                <h2>Featured Agricultural Inputs</h2>
                <p>Explore our wide range of quality agricultural products</p>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search for products...">
                </div>
                <div class="category-filter">
                    <button class="filter-btn active" data-category="all">All</button>
                    <button class="filter-btn" data-category="Seeds">Seeds</button>
                    <button class="filter-btn" data-category="Fertilizers">Fertilizers</button>
                    <button class="filter-btn" data-category="Pesticides">Pesticides</button>
                    <button class="filter-btn" data-category="Tools">Tools</button>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid" id="productsGrid">
                <div class="product-card">
                    <img src="assets/images/maize-seeds.jpg" alt="Hybrid Maize Seeds" class="product-image">
                    <div class="product-content">
                        <span class="product-category">Seeds</span>
                        <h3>Hybrid Maize Seeds</h3>
                        <p>High-yield hybrid maize seeds suitable for various climates</p>
                        <div class="product-price">
                            <div class="price-item">
                                <span class="price-label">Buy</span>
                                <span class="price-value">TSh 21,500</span>
                            </div>
                        </div>
                        <div class="product-actions">
                            <a href="login.php" class="btn btn-primary">Login to Order</a>
                        </div>
                    </div>
                </div>

                <div class="product-card">
                    <img src="assets/images/npk-fertilizer.jpg" alt="NPK Fertilizer" class="product-image">
                    <div class="product-content">
                        <span class="product-category">Fertilizers</span>
                        <h3>NPK Fertilizer</h3>
                        <p>Balanced NPK fertilizer for all crops</p>
                        <div class="product-price">
                            <div class="price-item">
                                <span class="price-label">Buy</span>
                                <span class="price-value">TSh 60,500</span>
                            </div>
                        </div>
                        <div class="product-actions">
                            <a href="login.php" class="btn btn-primary">Login to Order</a>
                        </div>
                    </div>
                </div>

                <div class="product-card">
                    <img src="assets/images/tractor.jpg" alt="Hand Tractor" class="product-image">
                    <div class="product-content">
                        <span class="product-category">Tools</span>
                        <h3>Hand Tractor</h3>
                        <p>Motorized hand tractor for small farms</p>
                        <div class="product-price">
                            <div class="price-item">
                                <span class="price-label">Rent</span>
                                <span class="price-value">TSh 100,000/day</span>
                            </div>
                            <div class="price-item">
                                <span class="price-label">Buy</span>
                                <span class="price-value">TSh 5,250,000</span>
                            </div>
                        </div>
                        <div class="product-actions">
                            <a href="login.php" class="btn btn-primary">Login to Order</a>
                        </div>
                    </div>
                </div>

                <div class="product-card">
                    <img src="assets/images/insecticide.jpg" alt="Insecticide Spray" class="product-image">
                    <div class="product-content">
                        <span class="product-category">Pesticides</span>
                        <h3>Insecticide Spray</h3>
                        <p>Effective insecticide for crop protection</p>
                        <div class="product-price">
                            <div class="price-item">
                                <span class="price-label">Buy</span>
                                <span class="price-value">TSh 60,200</span>
                            </div>
                        </div>
                        <div class="product-actions">
                            <a href="login.php" class="btn btn-primary">Login to Order</a>
                        </div>
                    </div>
                </div>

                <div class="product-card">
                    <img src="assets/images/pump.jpg" alt="Irrigation Pump" class="product-image">
                    <div class="product-content">
                        <span class="product-category">Tools</span>
                        <h3>Irrigation Pump</h3>
                        <p>Electric water pump for irrigation</p>
                        <div class="product-price">
                            <div class="price-item">
                                <span class="price-label">Rent</span>
                                <span class="price-value">TSh 60,000/day</span>
                            </div>
                            <div class="price-item">
                                <span class="price-label">Buy</span>
                                <span class="price-value">TSh 900,000</span>
                            </div>
                        </div>
                        <div class="product-actions">
                            <a href="login.php" class="btn btn-primary">Login to Order</a>
                        </div>
                    </div>
                </div>

                <div class="product-card">
                    <img src="assets/images/drip-kit.jpg" alt="Drip Irrigation Kit" class="product-image">
                    <div class="product-content">
                        <span class="product-category">Irrigation</span>
                        <h3>Drip Irrigation Kit</h3>
                        <p>Complete drip irrigation system</p>
                        <div class="product-price">
                            <div class="price-item">
                                <span class="price-label">Rent</span>
                                <span class="price-value">TSh 40,000/day</span>
                            </div>
                            <div class="price-item">
                                <span class="price-label">Buy</span>
                                <span class="price-value">TSh 300,000</span>
                            </div>
                        </div>
                        <div class="product-actions">
                            <a href="login.php" class="btn btn-primary">Login to Order</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-2">
                <a href="register.php" class="btn btn-primary">Register to View All Products</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2026 KilimoSafi. All rights reserved.</p>
            <p>Empowering farmers with quality agricultural inputs</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
