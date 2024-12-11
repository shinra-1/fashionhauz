<?php
include '../config.php';
if (isset($_COOKIE['username'])) {
    $username = htmlspecialchars($_COOKIE['username']);
    $userID = $_COOKIE['id'];

} else {
    echo '<script language="javascript">alert("Please login first.");</script>';
    echo '<script language="javascript">window.location.href = "../login.php";</script>';
}
$sqlcateg = "SELECT * FROM category";
$resultcateg = $conn->query($sqlcateg);
$categ = [];
if ($resultcateg->num_rows > 0) {
    while ($rowcateg = $resultcateg->fetch_assoc()) {
        $categs = [
            'id' => $rowcateg['id'],
            'category' => $rowcateg['category'],
        ];
        $categ[] = $categs; // Add to the products array
    }
} else {
    echo "No categories found.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/nav.css">
    <title>Document</title>
</head>
<body>
    <nav>
        <div class="navbg"></div>
        <div class="nav-section">
            <div class="titlebar">
                <img src="../images/logo.png" alt="logo">
                <a href="../index.php" class="no">Edz FashionHauz</a>
            </div>
            <div class="menubar space" id="menubar">
                <ul class="margin-top">
                    <img src="../images/logo.png" alt="logo" class="menu-logo">
                    <li><img src="../icons/close.png" id="close" alt=""></li>
                    <li><a href="../index.php" class="no">Home</a></li>
                    <li><a href="all-items.php" class="no">All Items</a></li>
                    <li><a href="all-items.php" class="down no">Categories </a><img src="../icons/arrow-down.png" class="dd" id="dd" alt="arrowdown">
                        <ul class="dropdown" id="dropdown">
                            <?php
                                foreach ($categ as $categs) {
                                    echo '<li><a href="all-items.php?category='.$categs['category'].'" class="no">'.$categs['category'].'</a></li>';
                                }
                            ?>
                        </ul>
                    </li>
                    <li><a href="cart.php"><img src="../icons/cart.png" id="profile" alt="person">
                        <?php
                            $fetchcart = "SELECT COUNT(id) as count FROM cart WHERE usercateg = '$username'";
                            $resultcart = $conn->query($fetchcart);
                            if ($resultcart->num_rows >= 1) {
                                if($rowcart = $resultcart->fetch_assoc()) {
                                    $count = $rowcart['count'];
                                }
                                if($count >0){
                                    echo '<span>'.$count.'</span>';
                                }
                            }
                        ?>
                    </a></li>
                    <li><a href="account.php"><img src="../icons/account.png" id="profile" alt="person"></a></li>
                </ul>
            </div>
            <img src="../icons/burger.png" class="dd" id="burger" onclick="document.getElementById('menubar').style.display = 'block'" alt="arrowdown">
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