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

if(isset($_POST['addcart'])){
    $prodID = $_POST['prodID'];
    if(!empty($_POST['prodname'])&&!empty($_POST['size'])&&!empty($_POST['qty'])){
        $prodname = $_POST['prodname'];
        $size = $_POST['size'];
        $qty = $_POST['qty'];

        $sqlprice = $conn->prepare("SELECT price,qty FROM product_sizes WHERE id = ? AND size = ?");
        $sqlprice->bind_param("ss", $prodID, $size);
        $sqlprice->execute();
        $slqres = $sqlprice->get_result();
        if ($slqres->num_rows > 0) {
            if($rowres = $slqres->fetch_assoc()) {
                $price = $rowres['price'];

                $availableqty = $rowres['qty'];
                if($qty > $availableqty){
                    echo '<script language="javascript">alert("There is not enough quantity for this size, please try again.");</script>';
                    echo '<script language="javascript">window.location.href = "item.php?id='.$prodID.'";</script>';
                    exit;
                }
            }
        }

        $sqlsearch = $conn->prepare("SELECT * FROM cart WHERE prodname = ? AND size = ? AND usercateg = ?");
        $sqlsearch->bind_param("sss", $prodname, $size, $username);
        $sqlsearch->execute();
        $searchres = $sqlsearch->get_result();
        if ($searchres->num_rows > 0) {
            $sqlupdate = $conn->prepare("UPDATE cart SET qty = qty + ? WHERE prodname = ? AND size = ? AND usercateg = ?");
            $sqlupdate->bind_param("isss", $qty, $prodname, $size, $username);
            if($sqlupdate->execute()){
                echo '<script language="javascript">alert("Product quantity updated in cart.");</script>';
                echo '<script language="javascript">window.location.href = "../index.php";</script>';
            }
        }else{
            $sqlinput = $conn->prepare("INSERT INTO cart (prodname, size, price, qty, usercateg) VALUES (?,?,?,?,?)");
            $sqlinput->bind_param("ssdis", $prodname, $size, $price, $qty, $username);
            if($sqlinput->execute()){
                echo '<script language="javascript">alert("Product added to cart.");</script>';
                echo '<script language="javascript">window.location.href = "item.php?id='.$prodID.'";</script>';
            }
        }
    }else{
        echo '<script language="javascript">alert("Please fulfill all details correctly.");</script>';
        echo '<script language="javascript">window.location.href = "item.php?id='.$prodID.'";</script>';
    }
}else if(isset($_POST['buynow'])){
    $prodID = $_POST['prodID'];
    if(!empty($_POST['prodname'])&&!empty($_POST['prodID'])&&!empty($_POST['qty'])&&!empty($_POST['size'])){
        $prodname = $_POST['prodname'];
        $prodID = $_POST['prodID'];
        $prodQty = $_POST['qty'];
        $prodSize = $_POST['size'];
        $cartOut = "false";




        $sqlsearch2 = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $sqlsearch2->bind_param("i", $prodID);
        $sqlsearch2->execute();
        $searchres2 = $sqlsearch2->get_result();
        if ($searchres2->num_rows > 0) {
            if($rowres2 = $searchres2->fetch_assoc()) {
                $prodCateg = $rowres2['category'];
                $prodDesc = $rowres2['description'];
                $prodImg = $rowres2['image'];
            }
        }

        $sqlsearch = $conn->prepare("SELECT * FROM product_sizes WHERE id = ? AND size = ?");
        $sqlsearch->bind_param("is", $prodID, $prodSize);
        $sqlsearch->execute();
        $searchres = $sqlsearch->get_result();
        if ($searchres->num_rows > 0) {
            if($rowres = $searchres->fetch_assoc()) {
                $price = $rowres['price'];
                $subTotal = $price * $prodQty;
                echo "<script>let subtotal = $subTotal;</script>";

                $availableqty = $rowres['qty'];
                if($prodQty > $availableqty){
                    echo '<script language="javascript">alert("There is not enough quantity for this size, please try again.");</script>';
                    echo '<script language="javascript">window.location.href = "item.php?id='.$prodID.'";</script>';
                }
            }
        }

        $sqladdress = $conn->prepare("SELECT * FROM `address` WHERE userID = ? AND `status` = ?");
        $statfetch = "active";
        $sqladdress->bind_param("is", $userID, $statfetch);
        $sqladdress->execute();
        $searchadd = $sqladdress->get_result();
    }else{
        echo '<script language="javascript">alert("Please fulfill all details correctly.");</script>';
        echo '<script language="javascript">window.location.href = "item.php?id='.$prodID.'";</script>';
    }

    
}else if(isset($_POST['clear'])){
    $stmt3 = $conn->prepare("DELETE FROM cart WHERE usercateg = ?");
    $stmt3->bind_param("s", $username);
    
    if($stmt3->execute()){
        echo '<script language="javascript">alert("Cart cleared successfully.");</script>';
        echo '<script language="javascript">window.location.href = "cart.php";</script>';
    }
}else if(isset($_POST['cart-checkout'])){
    $sqladdress = $conn->prepare("SELECT * FROM `address` WHERE userID = ? AND `status` = ?");
    $statfetch = "active";
    $sqladdress->bind_param("is", $userID, $statfetch);
    $sqladdress->execute();
    $searchadd = $sqladdress->get_result();


    $cartOut = "true";

    $stmte = $conn->prepare("SELECT * FROM `cart` WHERE usercateg = ?");
    $stmte->bind_param("s", $username);
    $stmte->execute();
    $searchcart = $stmte->get_result();
    if ($searchcart->num_rows > 0) {
        $subTotal = 0;
        while($row2 = $searchcart->fetch_assoc()) {
            $price = $row2['price'];
            $qty = $row2['qty'];
            $cartprod = $row2['prodname'];
            $cartsize = $row2['size'];
            $subprice = $price * $qty;
            $subTotal += $subprice;

            $idsql = $conn->prepare("SELECT id FROM `products` WHERE prodname = ?");
            $idsql->bind_param("s", $cartprod);
            $idsql->execute();
            $idres = $idsql->get_result();
            if ($idres->num_rows > 0) {
                if($idrow = $idres->fetch_assoc()) {
                    $prodid2 = $idrow['id'];

                    $maxsql = $conn->prepare("SELECT * FROM product_sizes WHERE id = ? AND size = ?");
                    $maxsql->bind_param("is", $prodid2, $cartsize);
                    $maxsql->execute();
                    $maxresss = $maxsql->get_result();
                    if ($maxresss->num_rows > 0) {
                        if($maxrow2 = $maxresss->fetch_assoc()) {
                            $availableqty = $maxrow2['qty'];
                            if($qty > $availableqty){
                                echo '<script language="javascript">alert("The quantity exceeds the available stocks for size '.$cartsize.' of '.$cartprod.', please try again.");</script>';
                                echo '<script language="javascript">window.location.href = "cart.php";</script>';
                            }
                        }
                    }
                    
                }
                
            }

        }
        echo "<script>let subtotal = $subTotal;</script>";
        
    }
}else{
    echo '<script language="javascript">alert("Please checkout an item first.");</script>';
    echo '<script language="javascript">window.location.href = "../index.php";</script>';
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/item.css">
    <link rel="stylesheet" type="text/css" href="css/checkout.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <div class="ribbon">
            <h2 class="rib active-ribbon">Address</h2>
            <div class="span span1"></div>
            <h2 class="rib">Payment</h2>
            <div class="span span2"></div>
            <h2 class="rib">Order Complete</h2>
        </div>
        <form action="checkout-details.php" method="post" enctype="multipart/form-data">
            <?php 
                if(isset($_POST['buynow'])){
                    echo '<input type="hidden" name="prodID" value ="'.$prodID.'">';
                    echo '<input type="hidden" name="prodname" value ="'.$prodname.'">';
                    echo '<input type="hidden" name="prodDesc" value ="'.$prodDesc.'">';
                    echo '<input type="hidden" name="prodQty" value ="'.$prodQty.'">';
                    echo '<input type="hidden" name="prodSize" value ="'.$prodSize.'">';
                    echo '<input type="hidden" name="prodCateg" value ="'.$prodCateg.'">';
                    echo '<input type="hidden" name="prodImg" value ="'.$prodImg.'">';
                    echo '<input type="hidden" name="subTotal" value ="'.$subTotal.'">';
                }
            ?>

            <div class="container">
                <div class="parts">
                    <?php
                    if ($searchadd->num_rows > 0) {
                        $label = 0;
                        while($row1 = $searchadd->fetch_assoc()) {
                            $label += 1;
                            $addressID = $row1['id'];
                            $addressName = $row1['name'];
                            $addressContact = $row1['cnumber'];
                            $addressStreet = $row1['street'];
                            $addressBarangay = $row1['barangay'];
                            $addressCity = $row1['city'];
                            $addressProvince = $row1['province'];
                            $addressRegion = $row1['region'];
                            $addressPostal = $row1['postal'];
                       
                    
                    ?>
                    <label for="<?php echo $label ?>" class="choose">
                    <input type="radio" id="<?php echo $label ?>" name="address_id" value="<?php echo $addressID ?>" data-postal="<?php echo $addressPostal ?>">
                        <input type="hidden" name="addressID" value="<?php echo $addressID ?>">
                        <h3 class="name"><?php echo $addressName ?></h3>
                        <h3 class="name"><?php echo $addressContact ?></h3>
                        <h3 class="name"><?php echo $addressStreet ?></h3>
                        <h3 class="name"><?php echo $addressBarangay ?></h3>
                        <h3 class="name"><?php echo $addressCity ?></h3>
                        <h3 class="name"><?php echo $addressProvince ?></h3>
                        <h3 class="name"><?php echo $addressRegion ?></h3>
                        <h3 class="name"><?php echo $addressPostal ?></h3>
                    </label>
                    <?php
                            }
                        }else{
                            echo "There is no address available.";
                            if(isset($_POST['buynow'])){
                                echo '<script language="javascript">alert("Please add an adress first.");</script>';
                                echo '<script language="javascript">window.location.href = "item.php?id='.$prodID.'";</script>';
                                exit;
                            }else{
                                echo '<script language="javascript">alert("Please add an adress first.");</script>';
                                echo '<script language="javascript">window.location.href = "cart.php";</script>';
                                exit;
                            }
                        };
                    ?>
                </div>
                <h3 class="next" onclick="nextPage()">Next</h3>
            </div>


            <div class="two-container">
                <div class="parts-again">
                    <h1>Input Payment</h1>
                    <div class="payment-box">
                        <h3 class="shipping-fee">Shipping fee: P80</h3>
                        <input type="hidden" name="shippingfee" id="shippingFeeInput" value="80">
                        <h3>Subtotal: ₱<?php echo $subTotal; ?></h3>
                        <h3>------------------------</h3>
                        <h3 class="total-fee">Total:</h3>
                        
                    </div>
                    <img src="../images/payment.jpg" alt="qrgcash">
                    <input type="file"  name="payment" accept=".jpg,.jpeg,.png,.webp" class="paymentfile" required>
                </div>
                <input type="hidden" name="cart-out" value="<?php echo $cartOut; ?>">
                <button type="submit" class="next" name="checkout">Checkout</button>
            </div>
        </form>
    </main>
    <?php include 'footer.php'; ?>
    <script>
        const chooseAdd = document.querySelectorAll('.choose');

        chooseAdd.forEach(label => {
            label.addEventListener('click', () => {
                // Remove 'active-address' from all labels
                chooseAdd.forEach(label => label.classList.remove('active-address'));

                // Add 'active-address' to the clicked label
                label.classList.add('active-address');
            });
        });

        function nextPage() {
            const container = document.querySelector('.container');
            const secondCon = document.querySelector('.two-container');
            const secondRib = document.querySelectorAll('.rib')[1];
            const span1 = document.querySelector('.span1');

            secondRib.classList.add('active-ribbon');
            span1.classList.add('active-span');
            container.style.display = 'none';
            secondCon.style.display = 'flex';
        }

        function nextnextPage() {
            const container = document.querySelector('.two-container');
            const secondCon = document.querySelector('.three-container');
            const secondRib = document.querySelectorAll('.rib')[2];
            const span1 = document.querySelector('.span2');

            secondRib.classList.add('active-ribbon');
            span1.classList.add('active-span');
            container.style.display = 'none';
            secondCon.style.display = 'flex';
        }

        document.querySelectorAll('input[name="address_id"]').forEach((input) => {
            input.addEventListener('change', function() {
                const postalCode = this.getAttribute('data-postal');
                
                // AJAX call to fetch shipping fee based on postal code
                fetch(`shipping-fee.php?postal=${postalCode}`)
                    .then(response => response.json())
                    .then(data => {
                        document.querySelector('.shipping-fee').textContent = `Shipping fee: ₱${data.shipping_fee}`;
                        updateTotal(data.shipping_fee); // Update total cost
                        document.getElementById('shippingFeeInput').value = data.shipping_fee;
                    });
            });
        });

        function updateTotal(shippingFee) {
            const total = subtotal + shippingFee;
            document.querySelector('.total-fee').textContent = `Total: ₱${total}`;
        }
    </script>
</body>
</html>