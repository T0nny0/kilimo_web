<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - KilimoSafi</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">Kilimo<span>Safi</span></a>
                <div class="nav-links">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="products.php">Products</a>
                    <a href="orders.php">My Orders</a>
                    <a href="api/logout.php" class="btn btn-outline">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container" style="padding: var(--spacing-xl) 0; min-height: 60vh;">
        <h1>User Profile</h1>
        <div class="card" style="max-width: 600px;">
            <div class="card-body">
                <form method="POST" style="display: grid; gap: var(--spacing-lg);">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" placeholder="Your full name" disabled>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" placeholder="your@email.com" disabled>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" placeholder="+254712345678" disabled>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea placeholder="Your farm location" disabled></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" disabled>Update Profile</button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 KilimoSafi - Agricultural Inputs Platform. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
