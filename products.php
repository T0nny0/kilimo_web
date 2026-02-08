<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require authentication
require_auth();

$user_id = $_SESSION['user_id'];

// Get categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Filter parameters
$search = sanitize_input($_GET['search'] ?? '');
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$query = "SELECT * FROM products WHERE is_available = 1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_id > 0) {
    $query .= " AND category_id = ?";
    $params[] = $category_id;
}

// Count total
$count_stmt = $pdo->prepare(str_replace("SELECT *", "SELECT COUNT(*) as count", $query));
$count_stmt->execute($params);
$total_products = $count_stmt->fetch()['count'];
$total_pages = ceil($total_products / $per_page);

// Get products with limit
$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Products - KilimoSafi</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">Kilimo<span>Safi</span></a>
                <div class="nav-links">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="products.php">Products</a>
                    <a href="orders.php">My Orders</a>
                    <a href="cart.php" class="btn btn-outline">Cart</a>
                    <div class="user-menu">
                        <button class="user-menu-toggle" onclick="toggleUserMenu()">👤 Menu</button>
                        <div class="dropdown-menu" id="userMenu">
                            <a href="profile.php">Profile</a>
                            <a href="notifications.php">Notifications</a>
                            <a href="api/logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Products Section -->
    <main class="container" style="padding: var(--spacing-xl) 0;">
        <div class="products-header">
            <h2>Agricultural Inputs</h2>
            <div class="search-filters">
                <form method="GET" class="search-box" style="max-width: 500px;">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search products..."
                        value="<?= htmlspecialchars($search) ?>"
                    >
                    <button type="submit">Search</button>
                </form>
            </div>
        </div>

        <!-- Category Filter -->
        <div style="margin-bottom: var(--spacing-lg);">
            <form method="GET" style="display: flex; gap: var(--spacing-sm); flex-wrap: wrap;">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" name="category" value="0" class="category-btn <?= $category_id == 0 ? 'active' : '' ?>">
                    All Categories
                </button>
                <?php foreach ($categories as $cat): ?>
                    <button 
                        type="submit" 
                        name="category" 
                        value="<?= $cat['id'] ?>" 
                        class="category-btn <?= $category_id == $cat['id'] ? 'active' : '' ?>"
                    >
                        <?= htmlspecialchars($cat['name']) ?> <?= $cat['icon'] ?>
                    </button>
                <?php endforeach; ?>
            </form>
        </div>

        <!-- Products Grid -->
        <?php if (!empty($products)): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                🌾
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <?php 
                            $stmt = $pdo->prepare("SELECT name, icon FROM categories WHERE id = ?");
                            $stmt->execute([$product['category_id']]);
                            $category = $stmt->fetch();
                            ?>
                            <span class="product-category"><?= $category['icon'] ?? '📦' ?> <?= htmlspecialchars($category['name'] ?? '') ?></span>
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                            <p class="product-unit">Unit: <?= htmlspecialchars($product['unit']) ?></p>
                            
                            <div class="product-price">
                                <?php if (!empty($product['buy_price'])): ?>
                                    <div>
                                        <span class="badge">Buy: </span>
                                        <span class="price-tag"><?= format_price($product['buy_price']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($product['rent_price'])): ?>
                                    <div>
                                        <span class="badge">Rent: </span>
                                        <span class="price-tag"><?= format_price($product['rent_price']) ?>/day</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <span class="availability <?= $product['quantity_available'] > 0 ? 'in-stock' : 'out-stock' ?>">
                                <?= $product['quantity_available'] > 0 ? '✓ In Stock' : '✗ Out of Stock' ?>
                            </span>

                            <div class="product-actions" style="margin-top: var(--spacing-md);">
                                <?php if (!empty($product['buy_price'])): ?>
                                    <button 
                                        onclick="addToCart(<?= $product['id'] ?>, 'buy')" 
                                        class="btn-buy"
                                        <?= $product['quantity_available'] <= 0 ? 'disabled' : '' ?>
                                    >
                                        Buy Now
                                    </button>
                                <?php endif; ?>
                                <?php if (!empty($product['rent_price'])): ?>
                                    <button 
                                        onclick="addToCart(<?= $product['id'] ?>, 'rent')" 
                                        class="btn-rent"
                                        <?= $product['quantity_available'] <= 0 ? 'disabled' : '' ?>
                                    >
                                        Rent
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?search=<?= urlencode($search) ?>&category=<?= $category_id ?>&page=1">« First</a>
                        <a href="?search=<?= urlencode($search) ?>&category=<?= $category_id ?>&page=<?= $page - 1 ?>">‹ Previous</a>
                    <?php endif; ?>

                    <?php 
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($start > 1): ?>
                        <span>...</span>
                    <?php endif;
                    
                    for ($i = $start; $i <= $end; $i++): ?>
                        <a href="?search=<?= urlencode($search) ?>&category=<?= $category_id ?>&page=<?= $i ?>" 
                           class="<?= $i == $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor;
                    
                    if ($end < $total_pages): ?>
                        <span>...</span>
                    <?php endif;
                    
                    if ($page < $total_pages): ?>
                        <a href="?search=<?= urlencode($search) ?>&category=<?= $category_id ?>&page=<?= $page + 1 ?>">Next ›</a>
                        <a href="?search=<?= urlencode($search) ?>&category=<?= $category_id ?>&page=<?= $total_pages ?>">Last »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="text-align: center; padding: var(--spacing-2xl);">
                <h3>No products found</h3>
                <p>Try adjusting your filters or search terms</p>
                <a href="products.php" class="btn btn-primary">View All Products</a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 KilimoSafi - Agricultural Inputs Platform. All rights reserved.</p>
        </div>
    </footer>

    <!-- Modal for Cart -->
    <div class="modal" id="cartModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add to Cart</h2>
                <button class="modal-close" onclick="closeModal('cartModal')">×</button>
            </div>
            <div style="padding: var(--spacing-lg);">
                <form id="cartForm">
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" required>
                    </div>
                    <div class="form-group" id="rent_days_group" style="display: none;">
                        <label for="rent_days">Number of Days to Rent</label>
                        <input type="number" id="rent_days" name="rent_days" value="1" min="1">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Add to Cart</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentProductId = 0;
        let currentCartType = 'buy';

        function addToCart(productId, type) {
            currentProductId = productId;
            currentCartType = type;
            
            const rentGroup = document.getElementById('rent_days_group');
            if (type === 'rent') {
                rentGroup.style.display = 'block';
            } else {
                rentGroup.style.display = 'none';
            }
            
            document.getElementById('cartForm').reset();
            document.getElementById('cartModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        document.getElementById('cartForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const quantity = document.getElementById('quantity').value;
            const rentDays = currentCartType === 'rent' ? document.getElementById('rent_days').value : null;
            
            try {
                const response = await fetch('api/cart/add.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        product_id: currentProductId,
                        quantity: quantity,
                        type: currentCartType,
                        rent_days: rentDays
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Added to cart successfully!');
                    closeModal('cartModal');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error adding to cart: ' + error.message);
            }
        });

        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('active');
        }
    </script>
</body>
</html>
