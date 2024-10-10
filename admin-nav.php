<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/admin-nav.css">
    <title>Document</title>
</head>
<body>
    <nav>
        <div class="navbg"></div>
        <div class="nav-section">
            <div class="titlebar">
                <img src="images/logo.png" alt="logo">
                <h1>Edz FashionHauz</h1>
            </div>
            <div class="menubar space" id="menubar">
                <ul class="margin-top">
                    <img src="images/logo.png" alt="logo" class="menu-logo">
                    <li><img src="icons/close.png" id="close" onclick=" alt="x"></li>
                    <li><a href="admin-db.php" class="down">Dashboard </a><img src="icons/arrow-down.png" class="dd" id="dd" alt="arrowdown">
                        <ul class="dropdown" id="dropdown">
                            <li><a href="#">Orders</a></li>
                            <li><a href="#">Products</a></li>
                            <li><a href="admin-employee.php">Employees</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Activity Logs</a></li>
                    <li><a href="#">Sales Archives</a></li>
                    <li><a href="#"><img src="icons/account.png" id="profile" alt="person"></a></li>
                </ul>
            </div>
            <img src="icons/burger.png" class="dd" id="burger" onclick="document.getElementById('menubar').style.display = 'block'" alt="arrowdown">
        </div>
    </nav>
    <script>
        document.getElementById("dd").onclick = function() {
            var text = document.getElementById("dropdown");
            text.style.display = "flex"; 
            text.style.height = "150px"; 
            text.style.transform = "translateY(0)";
            text.style.opacity = "1"; 
        }; 
        document.getElementById("close").onclick = function() {
            document.getElementById('menubar').style.display = 'none';
            var text = document.getElementById("dropdown");
            text.style.display = "none"; 
            text.style.height = "0px"; 
            text.style.transform = "translateY(-100px)";
            text.style.opacity = "0"; 
        };  
    </script>
</body>
</html>