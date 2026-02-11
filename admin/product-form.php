<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

require_admin();

$product_id = $_GET['id'] ?? 0;
$product = null;
$action = 'add';

// Get categories for dropdown
$stmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

if ($product_id) {
    $action = 'edit';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        http_response_code(404);
        echo "Product not found";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action === 'add' ? 'Add' : 'Edit' ?> Product - Admin</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2d6a4f;
            box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #2d6a4f;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1d5a3f;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            display: none;
        }
        
        .alert.show {
            display: block;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <a href="../index.php" class="logo">Kilimo<span>Safi</span> Admin</a>
                <div class="nav-links">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="products.php">Products</a>
                    <a href="categories.php">Categories</a>
                    <a href="orders.php">Orders</a>
                    <a href="farmers.php">Farmers</a>
                    <a href="../api/logout.php" class="btn btn-outline">Logout</a>
                </div>
            </nav>
        </div>
    </header>
    
    <main class="form-container">
        <h1><?= $action === 'add' ? 'Add New Product' : 'Edit Product' ?></h1>
        
        <div class="alert" id="alert"></div>
        
        <form id="productForm">
            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" required 
                       value="<?= htmlspecialchars($product['name'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? 0) === $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" required><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="unit">Unit of Measurement *</label>
                <input type="text" id="unit" name="unit" placeholder="e.g., kg, liter, piece" required
                       value="<?= htmlspecialchars($product['unit'] ?? '') ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="buy_price">Buy Price (TSh) *</label>
                    <input type="number" id="buy_price" name="buy_price" step="0.01" min="0" required
                           value="<?= htmlspecialchars($product['buy_price'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="rent_price">Rent Price/Day (TSh)</label>
                    <input type="number" id="rent_price" name="rent_price" step="0.01" min="0"
                           value="<?= htmlspecialchars($product['rent_price'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="quantity_available">Quantity Available *</label>
                    <input type="number" id="quantity_available" name="quantity_available" min="0" required
                           value="<?= htmlspecialchars($product['quantity_available'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="min_rent_days">Min. Rent Days</label>
                    <input type="number" id="min_rent_days" name="min_rent_days" min="1"
                           value="<?= htmlspecialchars($product['min_rent_days'] ?? '1') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="image_url">Image URL</label>
                <input type="url" id="image_url" name="image_url"
                       value="<?= htmlspecialchars($product['image_url'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="is_available">
                    <input type="checkbox" id="is_available" name="is_available" 
                           <?= ($product['is_available'] ?? true) == 1 ? 'checked' : '' ?>>
                    Product is Available
                </label>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary"><?= $action === 'add' ? 'Add Product' : 'Save Changes' ?></button>
                <a href="products.php" class="btn btn-secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">Cancel</a>
            </div>
        </form>
    </main>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 KilimoSafi Admin Panel.</p>
        </div>
    </footer>
    
    <script>
        document.getElementById('productForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const alert = document.getElementById('alert');
            const formData = {
                name: document.getElementById('name').value,
                category_id: document.getElementById('category_id').value,
                description: document.getElementById('description').value,
                unit: document.getElementById('unit').value,
                buy_price: document.getElementById('buy_price').value,
                rent_price: document.getElementById('rent_price').value,
                quantity_available: document.getElementById('quantity_available').value,
                min_rent_days: document.getElementById('min_rent_days').value,
                image_url: document.getElementById('image_url').value,
                is_available: document.getElementById('is_available').checked ? 1 : 0
            };
            
            <?php if ($action === 'edit'): ?>
            formData.product_id = <?= $product_id ?>;
            <?php endif; ?>
            
            try {
                const response = await fetch('../api/admin/product-save.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                if (data.success) {
                    alert.classList.add('alert-success', 'show');
                    alert.textContent = 'Product saved successfully!';
                    setTimeout(() => {
                        window.location.href = 'products.php';
                    }, 1500);
                } else {
                    alert.classList.add('alert-error', 'show');
                    alert.textContent = data.message || 'Error saving product';
                }
            } catch (error) {
                alert.classList.add('alert-error', 'show');
                alert.textContent = 'Network error: ' + error;
            }
        });
    </script>
    </script>
</body>
</html>
