<?php

include 'config.php';
function isValidUser($conn, $username) {
    $stmt = $conn->prepare("SELECT uname FROM users WHERE uname = ? AND category = 'admin'");
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
        echo "Invalid session. Please log in again.";
        // Optionally, delete the cookie if invalid
        setcookie("username", "", time() - 604800, "/");
        setcookie("category", "", time() - 604800, "/");
        setcookie("loggedin", "", time() - 604800, "/");
        setcookie("id", "", time() - 604800, "/");
        header("location: login.php");
    }
} else {
    header("location: login.php");
}

if (isset($_GET['size'])){ //for creating another size
    $id = $_GET['size'];
    $stmtt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmtt->bind_param("i", $id);
    $stmtt->execute();
    $sayses = $stmtt->get_result();
    if($row = $sayses->fetch_assoc()){
        $name = $row['prodname'];
    }
    
};

if (isset($_POST['addsizesir'])){ //for creating another size
    $id = $_POST['id'];
    $prodname = $_POST['prodname'];
    $size = $_POST['product_size'];
    $qty = $_POST['product_qty'];
    $price = $_POST['product_price'];

    $stmt = $conn->prepare("INSERT INTO product_sizes (`id`, `size`, `qty`, `price`) VALUES ( ?, ?, ?, ?)");
    $stmt->bind_param("isid", $id, $size, $qty, $price);
    if ($stmt->execute()) {
        $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
        $detail = 'New '.$size.' size for '.$prodname;
        $archives->bind_param("ss", $username, $detail);
        $archives->execute();

        echo '<script language="javascript">alert("Product size successfully added!");</script>';
        echo '<script language="javascript">window.location.href = "admin-products.php";</script>';
        
    } else {
        echo "Error: " . $stmt->error;
    }
    
};


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/admin-emplist.css">
    <link rel="stylesheet" type="text/css" href="css/admin-products.css">
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    <div class="container1" id="ewankoba">
        <div class="container2">
            <h1>Add Size</h1>
            <form action="admin-products-size.php" method="post">
            <div class="flex-box">
                <div class="left-boxx">
                    <input type="hidden"  name="id" value="<?php echo $id ?>" required>
                    <input type="text" value="<?php echo $name; ?>" name="prodname" readonly>
                    <input type="text" placeholder="Size" name="product_size" required>
                    <input type="number" placeholder="Quantity" name="product_qty" >
                    <input type="number" placeholder="Price" name="product_price" required>
                </div>
                <div class="right-box">
                </div>
            </div>
            <button type="submit" name="addsizesir"><img src="icons/create.png" alt="add">Add</button>
            </form>
        </div>
    </div>
    


    <script>
        
    </script>
</body>
</html>