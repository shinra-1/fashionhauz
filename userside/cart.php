<?php
include '../config.php';
function isValidUser($conn, $username) {
    $stmt = $conn->prepare("SELECT uname FROM users WHERE uname = ? AND category = 'customer'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0; // Returns true if the user exists
}
if (isset($_COOKIE['username'])) {
    $username = htmlspecialchars($_COOKIE['username']);
    $userID = $_COOKIE['id'];


    // Validate the cookie against the database
    if (isValidUser($conn, $username)) {
        
    } else {
        echo "Invalid session. Please log in again.";
        // Optionally, delete the cookie if invalid
        setcookie("username", "", time() - 604800, "/");
        setcookie("category", "", time() - 604800, "/");
        setcookie("loggedin", "", time() - 604800, "/");
        setcookie("id", "", time() - 604800, "/");
        header("location: ../login.php");
    }
} else {
    echo '<script language="javascript">alert("Please login first.");</script>';
    echo '<script language="javascript">window.location.href = "../login.php";</script>';
}


$stmt = $conn->prepare("SELECT * FROM cart WHERE usercateg = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if(isset($_POST['clear'])){
    $cartID = $_POST['cartID'];
    $stmt3 = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $stmt3->bind_param("s", $cartID);
    
    if($stmt3->execute()){
        echo '<script language="javascript">alert("Item deleted from cart.");</script>';
        echo '<script language="javascript">window.location.href = "cart.php";</script>';
    }
}





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/cart.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
    <script>
        function updatePrice(cartID, price) {
            var qty = document.getElementById("qty-" + cartID).value;
            var totalPrice = qty * price;

            // Send updated qty to the server using AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_cart.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Optionally, update the total price on the page as well
                    document.getElementById("total-price-" + cartID).innerText = "₱" + totalPrice.toFixed(2);
                    updateSubtotal();
                }
            };

            xhr.send("cartID=" + cartID + "&qty=" + qty);
        }
        function updateSubtotal() {
            var subtotal = 0;
            document.querySelectorAll('[id^="total-price-"]').forEach(function(item) {
                subtotal += parseFloat(item.innerText.replace("₱", "").replace(",", ""));
            });
            document.getElementById("subtotal").innerText = "₱" + subtotal.toFixed(2);
        }
    </script>
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <div class="container">
            <h1>My Cart</h1>
            <?php
            $subtotal = 0;
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $cartID = $row['id'];
                    $prodname = $row['prodname'];
                    $price = $row['price'];
                    $qty = $row['qty'];
                    $size = $row['size'];
                    

                    $stmt2 = $conn->prepare("SELECT * FROM products WHERE prodname = ?");
                    $stmt2->bind_param("s", $prodname);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();
                    if ($result2->num_rows > 0) {
                        if($row2 = $result2->fetch_assoc()) {
                            $prodID = $row2['id'];
                            $image = $row2['image'];
                            $desc = $row2['description'];
                            $category = $row2['category'];
                            $total = $price * $qty;
                            $subtotal += $total;

                            //search for maximum available qty for size of product
                            $sqlmax = $conn->prepare("SELECT qty FROM product_sizes WHERE id = ? AND size = ?");
                            $sqlmax->bind_param("is", $prodID, $size);
                            $sqlmax->execute();
                            $maxres = $sqlmax->get_result();
                            if ($maxres->num_rows > 0) {
                                if($rowmax = $maxres->fetch_assoc()) {
                                    $max = $rowmax['qty'];

                      
            ?>
            <div class="cart-container">
                <img src="../products/<?php echo $image; ?>" alt="picture">
                <div class="middle">
                    <h2 class="prodname"><?php echo $prodname; ?></h2>
                    <input type="hidden" value="<?php echo $prodname; ?>" name="prodname">
                    <input type="hidden" value="<?php echo $size; ?>" name="size">
                    <h4><?php echo $desc; ?></h4>
                    <div class="con">
                        <div class="box-categ">
                            <h2>Category:</h2>
                            <h2 class="categ"><?php echo $category; ?></h2>
                        </div>
                        <div class="box-size">
                            <h2>Size:</h2>
                            <h2 class="size"><?php echo $size; ?></h2>
                        </div>
                        <div class="box-price">
                            <h2>Price:</h2>
                            <h2 class="price">₱<?php echo $price; ?></h2>
                        </div>
                        <div class="box-qty">
                            <h2>Qty:</h2>
                            <input type="number" id="qty-<?php echo $row['id']; ?>" name="qty" min="1" max="<?php echo $max; ?>" value="<?php echo $qty; ?>" oninput="updatePrice(<?php echo $row['id']; ?>, <?php echo $price; ?>)">
                        </div>
                    </div>
                </div>
                <div class="right">
                    <h2>Item Price:</h2>
                    <h2 id="total-price-<?php echo $row['id']; ?>">₱<?php echo number_format($total, 2); ?></h2>
                    <form action="cart.php" method="post">
                        <input type="hidden" name="cartID" value="<?php echo $cartID;?>">
                        <button type="submit" name="clear" onclick="return confirm('Do you want to delete this from your cart?');"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 25 25"><defs></defs><g id="trash"><path class="cls-1" d="M20.5 4h-3.64l-.69-2.06a1.37 1.37 0 0 0-1.3-.94h-4.74a1.37 1.37 0 0 0-1.3.94L8.14 4H4.5a.5.5 0 0 0 0 1h.34l1 17.59A1.45 1.45 0 0 0 7.2 24h10.6a1.45 1.45 0 0 0 1.41-1.41L20.16 5h.34a.5.5 0 0 0 0-1zM9.77 2.26a.38.38 0 0 1 .36-.26h4.74a.38.38 0 0 1 .36.26L15.81 4H9.19zm8.44 20.27a.45.45 0 0 1-.41.47H7.2a.45.45 0 0 1-.41-.47L5.84 5h13.32z"/><path class="cls-1" d="M9.5 10a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 1 0v-7a.5.5 0 0 0-.5-.5zM12.5 9a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 1 0v-9a.5.5 0 0 0-.5-.5zM15.5 10a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 1 0v-7a.5.5 0 0 0-.5-.5z"/></g></svg>Delete</button>
                    </form>
                </div>
            </div>
            <?php
                  }}}}}}
            ?>
        </div>

        <div class="checkout-form">
            <form action="checkout.php" method="post">
                <div class="box-est">
                    <h3>Subtotal :</h3>
                    <h2  id="subtotal">₱<?php echo number_format($subtotal,2); ?></h2>
                </div>
                <div class="box-button">
                    <button type="submit" name="clear" onclick="return confirm('Are you sure you want to clear your cart?');"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 25 25"><defs></defs><g id="trash"><path class="cls-1" d="M20.5 4h-3.64l-.69-2.06a1.37 1.37 0 0 0-1.3-.94h-4.74a1.37 1.37 0 0 0-1.3.94L8.14 4H4.5a.5.5 0 0 0 0 1h.34l1 17.59A1.45 1.45 0 0 0 7.2 24h10.6a1.45 1.45 0 0 0 1.41-1.41L20.16 5h.34a.5.5 0 0 0 0-1zM9.77 2.26a.38.38 0 0 1 .36-.26h4.74a.38.38 0 0 1 .36.26L15.81 4H9.19zm8.44 20.27a.45.45 0 0 1-.41.47H7.2a.45.45 0 0 1-.41-.47L5.84 5h13.32z"/><path class="cls-1" d="M9.5 10a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 1 0v-7a.5.5 0 0 0-.5-.5zM12.5 9a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 1 0v-9a.5.5 0 0 0-.5-.5zM15.5 10a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 1 0v-7a.5.5 0 0 0-.5-.5z"/></g></svg>Clear</button>
                    <input type="hidden" name="cart-out" value="true">
                    <button type="submit" name="cart-checkout"><svg height="30px" width="30px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
                        viewBox="0 0 236.764 236.764" xml:space="preserve">
                    <g>
                        <path d="M110.035,151.039c0.399,3.858,3.655,6.73,7.451,6.73c0.258,0,0.518-0.013,0.78-0.04c4.12-0.426,7.115-4.111,6.689-8.231
                            l-3.458-33.468c-0.426-4.121-4.11-7.114-8.231-6.689c-4.12,0.426-7.115,4.111-6.689,8.231L110.035,151.039z"/>
                        <path d="M156.971,157.729c0.262,0.027,0.522,0.04,0.78,0.04c3.795,0,7.052-2.872,7.451-6.73l3.458-33.468
                            c0.426-4.121-2.569-7.806-6.689-8.231c-4.121-0.419-7.806,2.569-8.231,6.689l-3.458,33.468
                            C149.855,153.618,152.85,157.303,156.971,157.729z"/>
                        <path d="M98.898,190.329c-12.801,0-23.215,10.414-23.215,23.215c0,12.804,10.414,23.221,23.215,23.221
                            c12.801,0,23.216-10.417,23.216-23.221C122.114,200.743,111.699,190.329,98.898,190.329z M98.898,221.764
                            c-4.53,0-8.215-3.688-8.215-8.221c0-4.53,3.685-8.215,8.215-8.215c4.53,0,8.216,3.685,8.216,8.215
                            C107.114,218.076,103.428,221.764,98.898,221.764z"/>
                        <path d="M176.339,190.329c-12.801,0-23.216,10.414-23.216,23.215c0,12.804,10.415,23.221,23.216,23.221
                            c12.802,0,23.218-10.417,23.218-23.221C199.557,200.743,189.141,190.329,176.339,190.329z M176.339,221.764
                            c-4.53,0-8.216-3.688-8.216-8.221c0-4.53,3.686-8.215,8.216-8.215c4.531,0,8.218,3.685,8.218,8.215
                            C184.557,218.076,180.87,221.764,176.339,221.764z"/>
                        <path d="M221.201,84.322c-1.42-1.837-3.611-2.913-5.933-2.913H65.773l-6.277-24.141c-0.86-3.305-3.844-5.612-7.259-5.612h-30.74
                            c-4.142,0-7.5,3.358-7.5,7.5s3.358,7.5,7.5,7.5h24.941l6.221,23.922c0.034,0.15,0.073,0.299,0.116,0.446l23.15,89.022
                            c0.86,3.305,3.844,5.612,7.259,5.612h108.874c3.415,0,6.399-2.307,7.259-5.612l23.211-89.25
                            C223.111,88.55,222.621,86.158,221.201,84.322z M186.258,170.659H88.982l-19.309-74.25h135.894L186.258,170.659z"/>
                        <path d="M106.603,39.269l43.925,0.002L139.06,50.74c-2.929,2.929-2.929,7.678,0,10.606c1.464,1.464,3.384,2.197,5.303,2.197
                            c1.919,0,3.839-0.732,5.303-2.197l24.263-24.263c2.929-2.929,2.929-7.678,0-10.606l-24.28-24.28c-2.929-2.929-7.678-2.929-10.607,0
                            c-2.929,2.929-2.929,7.678,0,10.607l11.468,11.468l-43.907-0.002h0c-4.142,0-7.5,3.358-7.5,7.5
                            C99.104,35.911,102.461,39.269,106.603,39.269z"/>
                    </g>
                    </svg>Checkout</button>
                </div>
            </form>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>