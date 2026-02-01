<?php
session_start();
$alert = $_SESSION['alerts'] ?? [];
session_unset();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>register</title>
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
        background-image: url(img/FG-Distributes-Farm-Inputs-To-Cassava-Rice-Farmers-In-Nasarawa.jpg);
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
        <h1>signup</h1>
        <form action="auth_process.php" method="POST">
            <div>
        <label>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M200-246q54-53 125.5-83.5T480-360q83 0 154.5 30.5T760-246v-514H200v514Zm280-194q58 0 99-41t41-99q0-58-41-99t-99-41q-58 0-99 41t-41 99q0 58 41 99t99 41ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm69-80h422q-44-39-99.5-59.5T480-280q-56 0-112.5 20.5T269-200Zm211-320q-25 0-42.5-17.5T420-580q0-25 17.5-42.5T480-640q25 0 42.5 17.5T540-580q0 25-17.5 42.5T480-520Zm0 17Z"/></svg>
        </label>
                <input type="text" name="username" id="name" placeholder="Enter username" required>
            </div>
            <div><label for="email">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M440-520 120-720v400h400v80H120q-33 0-56.5-23.5T40-320v-480q0-33 23.5-56.5T120-880h640q33 0 56.5 23.5T840-800v200h-80v-120L440-520Zm0-80 320-200H120l320 200ZM760-80q-66 0-113-47t-47-113v-180q0-42 29-71t71-29q42 0 71 29t29 71v180h-80v-180q0-8-6-14t-14-6q-8 0-14 6t-6 14v180q0 33 23.5 56.5T760-160q33 0 56.5-23.5T840-240v-160h80v160q0 66-47 113T760-80ZM120-720v-80 480-400Z"/></svg>
            </label>
                <input type="email" name="email" id="email" placeholder="Enter your email" required>
            </div>
            <div>
                <label for="password">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm0-80h480v-400H240v400Zm240-120q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80ZM240-160v-400 400Z"/></svg>
                </label>
                <input type="password" name="password" id="psd" placeholder="Enter your password" required>
            </div>
            <div>
                <label for="password">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm0-80h480v-400H240v400Zm240-120q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80ZM240-160v-400 400Z"/></svg>
                </label>
                <input type="password" name="confirm-password" id="rpt-psd" placeholder="repeat password" required>
            </div>
            <div><button id="btn" name="register-btn">signup</button></div>
        </form>
       <p><b> already have an account? <a href="login.php">LOGIN</a></b></p>

    </div>
</body>
<script src="script.js" defer></script>
</html>