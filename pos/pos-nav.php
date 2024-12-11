<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/pos-nav.css">
    <title>Document</title>
</head>
<body>
    <nav>
        <div class="navbg"></div>
        <div class="nav-section">
            <div class="titlebar">
                <img src="../images/logo.png" alt="logo">
                <h1>Edz FashionHauz</h1>
            </div>
            <div class="menubar space" id="menubar">
                <ul class="margin-top">
                    <img src="../images/logo.png" alt="logo" class="menu-logo">
                    <li><img src="../icons/close.png" id="close" alt="" onclick="closee()"></li>
                    <li><a href="main.php" class="down">POS </a></li>
                    <li><a href="pos-archives.php">Sales Logs</a></li>
                    <li><a href="account-update.php">Account Settings</a></li>
                </ul>
            </div>
            <img src="../icons/burger.png" class="dd" id="burger" onclick="openn()" alt="arrowdown">
        </div>
    </nav>
    
    
    <script>
        const closee = () => {
            const menubar = document.getElementById('menubar');
            menubar.classList.toggle('open');
        }
        const openn = () => {
            const menubar = document.getElementById('menubar');
            menubar.classList.toggle('open');
        }
        
    </script>
</body>
</html>