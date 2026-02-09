<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('products.php');
    }
}

$alert = [];
if (isset($_SESSION['success'])) {
    $alert[] = ['type' => 'success', 'message' => $_SESSION['success']];
    unset($_SESSION['success']);
} elseif (isset($_GET['success_message'])) {
    // Handle success message passed via GET parameter after logout
    // Sanitize the message to prevent XSS
    $alert[] = ['type' => 'success', 'message' => sanitize_input($_GET['success_message'])];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-btn'])) {
    $username = sanitize_input($_POST['username'] ?? $_POST['email'] ?? '');
    $password = $_POST['password'];

    // --- TEMPORARY ADMIN LOGIN FIX ---
    // This block provides a guaranteed way for the admin to log in and resets the password hash.
    // It should be removed after a successful admin login.
    if ($username === 'admin' && $password === 'KilimoAdmin2026!') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
        $stmt->execute();
        $user = $stmt->fetch();
        // Also, let's fix the password hash in the database to match this password
        $hash = password_hash('KilimoAdmin2026!', PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $updateStmt->execute([$hash]);
    } else {
        $user = validate_credentials($username, $password);
    }
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_login'] = time();
        
        // Update last login
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        if ($user['role'] === 'admin') {
            redirect('admin/dashboard.php');
        } else {
            redirect('products.php');
        }
    } else {
        $alert[] = ['type' => 'error', 'message' => 'Invalid email or password'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
</head>
<style>
    :root{
        --accent-color: green;
        --base-color: white;
        --text-color: #2E2841;
        --input-color: #F30FF;
    }
    *{
        margin: 0;
        padding: 0;
    }
    html{
        font-family: poppins,'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 12px;
        color: var(--text-color);
    }
    body{
        min-height: 100vh;
        background-image: url(assets/img/FG-Distributes-Farm-Inputs-To-Cassava-Rice-Farmers-In-Nasarawa.jpg);
        background-size: cover;
        overflow: hidden;
    }
    .wrapper{
        background-color: transparent;
        height: 100vh;
        backdrop-filter: blur(2px);
        padding: 10px;
        border-radius: 0 20px 20px 0;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
        h1{
            font-size: 3rem;
            font-weight: 900;
            text-transform: uppercase;
        }
    form{
        width: min(400px, 100%);
        margin-top: 2opx;
        margin-bottom: 50px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    form>div{
        width: 100%;
        display: flex;
        justify-content: center;
    }
    form label{
        flex-shrink: 0;
        height: 50px;
        width: 50px;
        background-color: var(--accent-color);
        fill: var(--base-color);
        color: var(--base-color);
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.5rem;
        font-weight: 500;
    }
    form inputs{
        box-sizing: border-box;
        flex-grow: 1;
        min-width: 0;
        height: 50px;
        padding: 1em;
        font: inherit;
        border-radius: 0 10px 10px 0;
         border: 2px solid var(--input-color);
        background-color: var(--input-color);
        transition: 150ms ease;
        border-left: none;

    }
    form input:hover{
        border-color: var(--accent-color);
    }
    form input:focus{
        outline: none;
        border-color:var(--text-color)
    }
    div:has(input:focus) >label{
        background-color: var(--text-color);
    }
    form input::placeholder{
        color: var(--text-color);
    }
    form button{
        margin-top: 10px;
        border: none;
        border-radius: 1000px;
        padding: .85em 4em;
        background-color: var(--accent-color);
        color: var(--base-color);
        font: inherit;
        font-weight: 600;
        text-transform: uppercase;
        cursor: pointer;
        transition: 150ms ease;
    }
    form button:hover{
        background-color: var(--text-color);
    }
    form button:focus{
        outline: none;
        background-color: var(--text-color);
    }
    a{
        text-decoration: none;
        color: var(--text-color);
    }
    a:hover{
        text-decoration: underline;
    }
    @media(max-width: 1100px){
        .wrapper{
            width: min(600px, 100%);
            border-radius: 0;
        }
    }
    .error {
        color: #721c24;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 10px;
        border-radius: 5px;
        width: 100%;
        text-align: center;
        margin-bottom: 10px;
    }
    .success {
        color: #155724;
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        padding: 10px;
        border-radius: 5px;
        width: 100%;
        text-align: center;
        margin-bottom: 10px;
    }

</style>
<body>
    
    <div class="wrapper">
        <div class="alert">
            <?php if(!empty($alert)): ?>
                <?php foreach($alert as $msg): ?>
                    <p class="<?= $msg['type'] ?>"><?= $msg['message'] ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <h1>login</h1>
        <form action="" method="post">
            <div><label for="username">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M440-520 120-720v400h400v80H120q-33 0-56.5-23.5T40-320v-480q0-33 23.5-56.5T120-880h640q33 0 56.5 23.5T840-800v200h-80v-120L440-520Zm0-80 320-200H120l320 200ZM760-80q-66 0-113-47t-47-113v-180q0-42 29-71t71-29q42 0 71 29t29 71v180h-80v-180q0-8-6-14t-14-6q-8 0-14 6t-6 14v180q0 33 23.5 56.5T760-160q33 0 56.5-23.5T840-240v-160h80v160q0 66-47 113T760-80ZM120-720v-80 480-400Z"/></svg>
            </label>
                <input type="text" name="username" id="username" placeholder="Username or Email" required>
            </div>
            <div>
                <label for="password">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm0-80h480v-400H240v400Zm240-120q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80ZM240-160v-400 400Z"/></svg>
                </label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
            </div>
            <div><button id="btn" name="login-btn">login</button></div>
        </form>
       <p><b> don't have an account? <a href="register.php">SIGNUP</a></b></p>

    </div>
</body>
<script defer src="script.js"></script>
</html>
