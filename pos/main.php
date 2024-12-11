<?php
include '../config.php';
session_start();
function isValidUser($conn, $username) {
    $stmt = $conn->prepare("SELECT uname FROM users WHERE uname = ? AND category = 'staff'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    return $stmt->num_rows > 0; // Returns true if the user exists
}
if (isset($_COOKIE['username'])) {
    $username = htmlspecialchars($_COOKIE['username']);

    // Validate the cookie against the database
    if (isValidUser($conn, $username)) {
        
    } else {
        setcookie("username", "", time() - 604800, "/");
        setcookie("category", "", time() - 604800, "/");
        setcookie("loggedin", "", time() - 604800, "/");
        setcookie("id", "", time() - 604800, "/");
        header("location: ../login.php");
    }
}

function fetchCategories($conn) { //fetch categories for the dropdown
    $categories = [];
    $query = "SELECT id, category FROM category";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}
$categories = fetchCategories($conn);

function fetchAvailableSizes($conn, $product_id) {
    $sizes = [];
    $stmt = $conn->prepare("SELECT * FROM product_sizes WHERE id = ? AND qty > 0");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sizes[] = $row;
        }
    }
    return $sizes;
}

$items = [];
if (isset($_POST['search-categ'])) { // Fetch items based on the selected category
    if($_POST['categ']){
        $category = $_POST['categ'];
        $items = fetchItemsByCategory($conn, $category); 
    }else{
        header("location: main.php");
    }
    
}else if(isset($_POST['addtoCart'])){
    $cartid = $_POST['item_id'];
    $cartprodname = $_POST['prodname'];
    $cartsize = $_POST['size'];
    $cartprice = $_POST['price'];
    $usercateg = $username;

    $stmt = $conn->prepare("SELECT * FROM cart WHERE prodname = ? AND size = ?");
    $stmt->bind_param("ss",$cartprodname, $cartsize);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        if($row = $result->fetch_assoc()) {
            $subqty = $row['qty'] + 1;
        }
        $stmt2 = $conn->prepare("UPDATE cart SET `qty` = ? WHERE `prodname` = ? AND `size` = ?");
        $stmt2->bind_param("iss", $subqty, $cartprodname, $cartsize);
        if ($stmt2->execute()) {
        } else {
            echo "Error: " . $stmt2->error;
        }
    }else{
        $qty = 1;
        $stmt = $conn->prepare("INSERT INTO cart (`prodname`, `size`, `price`,`qty`,`usercateg`) VALUES ( ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $cartprodname, $cartsize, $cartprice, $qty, $usercateg);
        if ($stmt->execute()) {
            echo '<script language="javascript">window.location.href = "main.php";</script>';
            
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}

if(isset($_GET['minus'])){
    $cartid = $_GET['minus'];
    $stmt = $conn->prepare("SELECT * FROM cart WHERE id = ?");
    $stmt->bind_param("i", $cartid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        if($row = $result->fetch_assoc()) {
            $subqty = $row['qty'] - 1;
            if($subqty <= 0){
                $del = $conn->prepare("DELETE FROM cart WHERE id = ?");
                $del->bind_param("i", $cartid);
                if ($del->execute()) {
                    header("location: main.php");
                } else {
                    echo "Error deleting record: " . $del->error;
                }
            }else{
                $updat = $conn->prepare("UPDATE cart SET `qty` = ? WHERE `id` = ?");
                $updat->bind_param("ii", $subqty, $cartid);
                if ($updat->execute()) {
                    header("location: main.php");
                } else {
                    echo "Error: " . $updat->error;
                }
            }
        }else{
            echo "Error getting rows record: " . $row->error;
        }
    }else{
        echo '<script language="javascript">window.location.href = "main.php";</script>';
    }
}else if(isset($_GET['plus'])){
    $cartid = $_GET['plus'];
    $stmt = $conn->prepare("SELECT * FROM cart WHERE id = ?");
    $stmt->bind_param("i", $cartid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        if($row = $result->fetch_assoc()) {
            $subqty = $row['qty'] + 1;
            $updat = $conn->prepare("UPDATE cart SET `qty` = ? WHERE `id` = ?");
            $updat->bind_param("ii", $subqty, $cartid);
            if ($updat->execute()) {
                header("location: main.php");
            } else {
                echo "Error: " . $updat->error;
            }
        }else{
            echo "Error getting rows record: " . $row->error;
        }
    }else{
        echo '<script language="javascript">window.location.href = "main.php";</script>';
    }
}
function fetchItemsByCategory($conn, $category) { // Fetch items based on the selected category
    $items = [];
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    return $items;
}
function fetchAllItems($conn) { 
    $allitems = [];
    $stmt = $conn->prepare("SELECT * FROM products");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $allitems[] = $row;
        }
    }
    return $allitems;
}
$allitems = fetchAllItems($conn);

function fetchCartItems($conn,$username) { 
    $cartItem = [];
    $stmt5 = $conn->prepare("SELECT * FROM cart WHERE usercateg = '$username'");
    $stmt5->execute();
    $result5 = $stmt5->get_result();
    if ($result5->num_rows > 0) {
        while ($row5 = $result5->fetch_assoc()) {
            $cartItem[] = $row5;
        }
    }
    return $cartItem;
}
$cartItems = fetchCartItems($conn,$username);



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <title>Edz FashionHauz</title>
    <script>
        function updatePrice(select, itemId) {
            const priceDisplay = document.getElementById(`price_label_${itemId}`);
            const selectedOption = select.options[select.selectedIndex];
            priceDisplay.value = selectedOption.dataset.price;;
        }
        
        
    </script>
</head>
<body>
    <?php include 'pos-nav.php'; ?>
    <section>
        <div class="item-container">
            <div class="cate-box">
                <form action="main.php" method="post">
                    <select name="categ" id="categ">
                        <option value="" selected disabled hidden>Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['category']) ?>">
                                <?= htmlspecialchars($category['category']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button name="search-categ" type="submit"><img src="../icons/search.png" alt="aa"></button>
                </form>
            </div>
            <div class="item-cards">
                <?php
                    if(isset($_POST['search-categ'])){
                        foreach ($items as $item){
                            $counter = 0;
                            $availableSizes = fetchAvailableSizes($conn, $item["id"]);
                            $defaultPrice = count($availableSizes) > 0 ? $availableSizes[0]['price'] : 0.00;
                            echo "<div class='item'>";
                            echo '<form action="main.php" method="post">';
                            echo '<input type="hidden" name="item_id" value="'.htmlspecialchars($item["id"]).'">';
                            echo '<img src="../products/'.$item["image"].'" alt="#" class="pic width">';
                            echo '<input name="prodname" class="width" value="'.htmlspecialchars($item["prodname"]).'" readonly>';
                            echo '<div class="size-box" class="width">';
                            echo '<select name="size" id="size_'.$item['id'].'" onchange="updatePrice(this, '.$item['id'].')">';
                            $availableSizes = fetchAvailableSizes($conn, $item["id"]);
                            foreach ($availableSizes as $size) {
                                $counter += 1;
                                echo '<option value="'.htmlspecialchars($size['size']).'" data-price="'.htmlspecialchars($size['price']).'">'.htmlspecialchars($size['size']).'</option>';
                            }
                            echo '</select></div>';
                            echo '<input id="price_label_'.$item['id'].'" class="width" name="price" value="₱'.number_format($defaultPrice, 2).'" readonly>';
                            if($counter>0){
                                echo '<button type="submit" name="addtoCart" class="width but">';
                                echo '<img src="../icons/create.png" alt="#">Add</button>';
                            }
                            echo '<img src="#" alt=""></button></form></div>';
                        }
                    }else{
                        foreach ($allitems as $all){
                            $counter = 0;
                            $availableSizes = fetchAvailableSizes($conn, $all["id"]);
                            $defaultPrice = count($availableSizes) > 0 ? $availableSizes[0]['price'] : 0.00;
                            echo "<div class='item'>";
                            echo '<form action="main.php" method="post">';
                            echo '<input type="hidden" name="item_id" value="'.htmlspecialchars($all["id"]).'">';
                            echo '<img src="../products/'.$all["image"].'" alt="#" class="pic width">';
                            echo '<input name="prodname" class="width" value="'.htmlspecialchars($all["prodname"]).'" readonly>';
                            echo '<div class="size-box" class="width">';
                            echo '<select name="size" id="size_'.$all['id'].'" onchange="updatePrice(this, '.$all['id'].')">';
                            $availableSizes = fetchAvailableSizes($conn, $all["id"]);
                            foreach ($availableSizes as $sizes) {
                                $counter += 1;
                                echo '<option value="'.htmlspecialchars($sizes['size']).'" data-price="'.htmlspecialchars($sizes['price']).'">'.htmlspecialchars($sizes['size']).'</option>';
                            }
                            echo '</select></div>';
                            echo '<input id="price_label_'.$all['id'].'" class="width" name="price" value="'.number_format($defaultPrice, 2).'" readonly>';
                            if($counter>0){
                                echo '<button type="submit" name="addtoCart" class="width but">';
                                echo '<img src="../icons/create.png" alt="#">Add</button>';
                            }
                            echo '</form></div>';
                        }
                    }
                ?>
            </div>
        </div>
        <div class="cart-container">
            <div class="container2">
                <div class="header">Order</div>
                <form action="receipt.php" method="post" class="checkout-form">
                    <div class="cart-items">
                        <?php $totalprice=0;  foreach ($cartItems as $cart){ ?>
                        <div class="cart-item">
                            <div class="left">
                                <label for="none"><?php echo $cart['prodname']; ?></label>
                                <label for="none">Size: <?php echo $cart['size']; ?></label>
                            </div>
                            <div class="middle">
                                ₱<?php echo $cart['price']; ?>
                            </div>
                            <div class="right">
                                <a href="main.php?minus=<?php echo $cart['id']; ?>"><</a>
                                <label for="span"><?php echo $cart['qty']; ?></label>
                                <a href="main.php?plus=<?php echo $cart['id']; ?>">></a>
                            </div>
                        </div>
                        <?php 
                            $totalprice += $cart['price'] * $cart['qty'];
                        }; ?>
                    </div>
                    <span></span>
                    <div class="print-box">
                        <h2>Total: ₱<?php echo $totalprice; ?></h2>
                        <input type="number" step="0.01" min="0" id="price" name="cash" class="cash">
                        <input type="hidden" id="price" name="totalprice" value="<?php echo $totalprice; ?>">
                        <button name="checkout" type="submit"><img src="../icons/checkout.png" alt="">Checkout</button>
                    </div>
                </form>
            </div>
            <form action="../logout.php" class="logout" method="post">
                <input type="hidden" name="usern" value="<?php echo $username; ?>">
                <button type="submit" name="logout"><img src="../icons/logout.png" alt="">Logout</button>
            </form>
        </div>
    </section>
    
    
</body>
</html>