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
                    <li><img src="icons/close.png" id="close" alt=""></li>
                    <li><a href="admin-db.php" class="down no" >Dashboard </a><img src="icons/arrow-down.png" class="dd no" id="dd" alt="arrowdown">
                        <ul class="dropdown" id="dropdown">
                            <li><a href="admin-orderlist.php" class="no">Orders</a></li>
                            <li><a href="admin-products.php" class="no">Products</a></li>
                            <li><a href="admin-employee.php" class="no">Employees</a></li>
                            <li><a href="admin-subs.php" class="no">Subscribers</a></li>
                        </ul>
                    </li>
                    <li><a href="admin-logs.php" class="no">Activity Logs</a></li>
                    <li><a href="admin-archives.php" class="no">Sales Archives</a></li>
                    <li><a href="form-actions/admin-account-update.php"><img src="icons/account.png" id="profile" alt="person"></a></li>
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