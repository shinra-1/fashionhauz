<?php

include 'config.php';
session_start();
function isValidUser($conn, $username) {
    $stmt = $conn->prepare("SELECT uname FROM users WHERE uname = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    return $stmt->num_rows > 0; // Returns true if the user exists
}
if (isset($_COOKIE['username'])) {
    $username = htmlspecialchars($_COOKIE['username']);
    $categ = htmlspecialchars($_COOKIE['category']);

    // Validate the cookie against the database
    if (isValidUser($conn, $username)) {
        if ($categ === "admin") {
            header("Location: admin-db.php");
        } elseif ($categ === "staff") {
            header("Location: pos/main.php");
        } elseif ($categ !== "customer") {
            foreach (["username", "category", "loggedin", "id"] as $cookie) {
                setcookie($cookie, "", time() - 604800, "/");
            }
            header("Location: login.php");
        }
    } else {
        setcookie("username", "", time() - 604800, "/");
        setcookie("category", "", time() - 604800, "/");
        setcookie("loggedin", "", time() - 604800, "/");
        setcookie("id", "", time() - 604800, "/");
        header("location: login.php");
    }
}

if(isset($_POST['submit_sub'])){
    $emailsub = $_POST['email_sub'];

    $emailget = $conn->prepare("SELECT email FROM subscription WHERE email = ?");
    $emailget->bind_param("i", $emailsub);
    $emailget->execute();
    $emailget->bind_result($emailgets);
    $emailget->fetch();
    $emailget->close();

    if($emailgets === $emailsub){
        echo '<script language="javascript">alert("This is email is already subscribed, try a different email.");</script>';
        echo '<script language="javascript">window.location.href = "index.php";</script>';
    }else{
        $sqlsub = $conn->prepare("INSERT INTO subscription (email) VALUES (?)");
        $sqlsub->bind_param("s", $emailsub);
        if ($sqlsub->execute()) {
            echo '<script language="javascript">alert("Thank you for subscribing! Wait for the latest news!");</script>';
            echo '<script language="javascript">window.location.href = "index.php";</script>';
        } else {
            echo "Error deleting record: " . $stmt->error;
        }
    }
}

$sqlarrival = "SELECT id, prodname, description, category, image, date_created
        FROM products
        ORDER BY date_created DESC
        LIMIT 4";
$resultarrival = $conn->query($sqlarrival);
$newarrival = [];
if ($resultarrival->num_rows > 0) {
    while ($rowArrival = $resultarrival->fetch_assoc()) {
        $product = [
            'id' => $rowArrival['id'],
            'prodname' => $rowArrival['prodname'],
            'description' => $rowArrival['description'],
            'category' => $rowArrival['category'],
            'image' => $rowArrival['image'],
            'date_created' => $rowArrival['date_created']
        ];
        $newarrival[] = $product; // Add to the products array
    }
} else {
    echo "No products found.";
}

$sqlall = "SELECT *
        FROM products
        ORDER BY date_created ASC
        LIMIT 3";
$resultAll = $conn->query($sqlall);
$allitems = [];
if ($resultAll->num_rows > 0) {
    while ($rowall = $resultAll->fetch_assoc()) {
        $products = [
            'id' => $rowall['id'],
            'prodname' => $rowall['prodname'],
            'description' => $rowall['description'],
            'category' => $rowall['category'],
            'image' => $rowall['image'],
            'date_created' => $rowall['date_created']
        ];
        $allitems[] = $products; // Add to the products array
    }
} else {
    echo "No products found.";
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
    <link rel="stylesheet" type="text/css" href="userside/css/nav.css">
    <link rel="stylesheet" type="text/css" href="userside/css/index.css">
    <link rel="stylesheet" type="text/css" href="userside/css/sliding.css">
    <link rel="stylesheet" type="text/css" href="userside/css/footer.css">
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <title>Edz FashionHauz</title>
</head>
<body>
    <nav>
        <div class="navbg"></div>
        <div class="nav-section">
            <div class="titlebar">
                <img src="images/logo.png" alt="logo">
                <a href="index.php" class="no">Edz FashionHauz</a>
            </div>
            <div class="menubar space" id="menubar">
                <ul class="margin-top">
                    <img src="images/logo.png" alt="logo" class="menu-logo">
                    <li><img src="icons/close.png" id="close" alt=""></li>
                    <li><a href="index.php" class="no">Home</a></li>
                    <li><a href="userside/all-items.php" class="no">All Items</a></li>
                    <li><a href="userside/all-items.php" class="down no">Categories </a><img src="icons/arrow-down.png" class="dd" id="dd" alt="arrowdown">
                        <ul class="dropdown" id="dropdown">
                            <?php
                                foreach ($categ as $categs) {
                                    echo '<li><a href="userside/all-items.php?category='.$categs['category'].'" class="no">'.$categs['category'].'</a></li>';
                                }
                            ?>
                        </ul>
                    </li>
                    <li><a href="userside/cart.php"><img src="icons/cart.png" id="profile" alt="person">
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
                    <li><a href="userside/account.php"><img src="icons/account.png" id="profile" alt="person"></a></li>
                </ul>
            </div>
            <img src="icons/burger.png" class="dd" id="burger" onclick="document.getElementById('menubar').style.display = 'block'" alt="arrowdown">
        </div>
    </nav>

    <main>
        <div class="banner">
            <div class="banner-left">
                <h1>
                Shop all your trendy <br> essentials here!
                <img src="icons/slay.png" alt="">
                <img src="icons/spark.png" alt="">
                </h1>
                <a href="userside/all-items.php"><button>Shop Now!</button></a>
            </div>
            <div class="banner-right">
                <?php 
                    $fetchbanner = "SELECT * FROM banner";
                    $resultban = $conn->query($fetchbanner);
                    if ($resultban->num_rows > 0) {
                        if($rowbanner = $resultban->fetch_assoc()) {
                            $banner = $rowbanner['banner'];
                        }
                        echo '<img src="images/'.$banner.'" alt="">';
                    } else {
                        echo "No banner found.";
                    }
                ?>
                

                
            </div>
        </div>

        <div class="stock-ticker first">
            <ul>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
            </ul>
            <ul aria-hidden="true">
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
                <li><span class="all">New Arrivals</span></li>
            </ul>
        </div>

        <div class="flex-cards">
            <?php
                foreach ($newarrival as $product) {
                    echo '<div class="card">';
                    echo "<img src=products/".$product['image']." alt=''>";
                    echo '<h2>'.$product['prodname'].'</h2>';
                    echo '<p>'.$product['description'].'</p>';
                    echo '<a href="userside/item.php?id='.$product['id'].'">Buy</a>';
                    echo '</div>';
                }
            ?>
        </div>

        <div class="stock-ticker second">
            <ul>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
            </ul>
            <ul aria-hidden="true">
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
                <li><span class="all">All Items</span></li>
            </ul>
        </div>

        <div class="flex-cards">
            <?php
                foreach ($allitems as $products) {
                    echo '<div class="card">';
                    echo "<img src=products/".$products['image']." alt=''>";
                    echo '<h2>'.$products['prodname'].'</h2>';
                    echo '<p>'.$products['description'].'</p>';
                    echo '<a href="userside/item.php?id='.$products['id'].'">Buy</a>';
                    echo '</div>';
                }
            ?>
            <div class="card see-more"> 
                <img src="images/collage.jpg" alt="#">
                <h2>All Items</h2>
                <a href="userside/all-items.php" class="seemore">See More</a>
            </div>
        </div>
    </main>
    <footer>
        <div class="footer-left">
            <div class="sub-left">
                <h3>Pages</h3>
                <div class="deets">
                    <a href="index.php">Home</a>
                    <a href="userside/all-items.php">All Items</a>
                    <a href="userside/cart.php">Cart</a>
                    <a href="userside/account.php">Account</a>
                </div>
            </div>
            <div class="sub-left">
                <h3>Business</h3>
                <div class="deets">
                    <a href="userside/about-us.php">About Us</a>
                    <a href="https://maps.app.goo.gl/as5gGNhoykXwh87ZA">Plaridel Crossing (Besides China Bank Savings)</a>
                    <p>09123456789</p>
                    <a href="userside/terms.php">Terms and Condition</a>
                </div>
            </div>
            <div class="sub-left">
                <h3>Socials</h3>
                <div class="deets">
                    <a href="https://www.facebook.com/edzshoppingstore/">Facebook</a>
                    <a href="https://www.instagram.com/edzfashion_hauz/">Instagram</a>
                    <a href="https://www.tiktok.com/@edzfashionhauz">Tiktok</a>
                </div>
            </div>
        </div>
        <div class="footer-right">
            <div class="sub-right">
                <h3>Edz FashionHauz</h3>
                <div class="deets">
                    <h4>Get the latest news about our products!</h4>
                    <form action="index.php" method="post">
                        <input type="email" placeholder="Email here..." name="email_sub">
                        <button name="submit_sub" type="submit">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </footer>


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