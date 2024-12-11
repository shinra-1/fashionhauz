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

if(isset($_POST['checkout'])){
    $ordersql = $conn->query("SELECT MAX(orderID) AS max FROM orderlist");//kuhain max orderid para plus 1 sa order na to
    $row2 = $ordersql->fetch_assoc();
    $orderNumber = $row2['max'] ? $row2['max'] + 1 : 1;
    $curDate = date('MMM dd, YYYY');
    $cartOut = $_POST['cart-out'];


    if(!empty($_POST['addressID'])&&!empty($_FILES['payment'])){

        $addressID = $_POST['addressID'];
        $sqladdress = $conn->prepare("SELECT * FROM `address` WHERE id = ?");
        $sqladdress->bind_param("i", $addressID);
        $sqladdress->execute();
        $searchadd = $sqladdress->get_result();
        if ($searchadd->num_rows > 0) {
            if($row5 = $searchadd->fetch_assoc()) {
                $addressName = $row5['name'];
                $addressContact = $row5['cnumber'];
                $addressStreet = $row5['street'];
                $addressBarangay = $row5['barangay'];
                $addressCity = $row5['city'];
                $addressProvince = $row5['province'];
                $addressRegion = $row5['region'];
                $addressPostal = $row5['postal'];
            }
        }
        $status = "Pending";
        $shipping = $_POST['shippingfee'];
        
        //IF GALING SA CART PAGE YUNG PAG CHECKOUT
        if($cartOut === "true"){
            //search cart for qty and price for orderlist
            $addressID = $_POST['addressID'];
            $sqltotal = $conn->prepare("SELECT * FROM `cart` WHERE usercateg = ?");
            $sqltotal->bind_param("s", $username);
            $sqltotal->execute();
            $totalsql = $sqltotal->get_result();
            $total_qty =0;
            $total_price =0;
            if ($totalsql->num_rows > 0) {
                while($rowtotal = $totalsql->fetch_assoc()) {
                    $total_qty += $rowtotal['qty'];
                    $total_price += $rowtotal['price'] * $rowtotal['qty'];
                }
                $total_price += $shipping;
            }

            $targetDir = "../payments/";
            $paymentImg = basename($_FILES["payment"]["name"]);
            $targetFilePath = $targetDir . $paymentImg;
            if (move_uploaded_file($_FILES["payment"]["tmp_name"], $targetFilePath)) {
                $sqlorder = $conn->prepare("INSERT INTO `orderlist` (orderID, userID, total_qty, mop, total_price, address_id, status) VALUES (?,?,?,?,?,?,?)");
                $sqlorder->bind_param("iiisdis", $orderNumber, $userID, $total_qty, $paymentImg, $total_price, $addressID, $status);
                $sqlorder->execute();

                //since name of product and size naka labas sa cart, need mag search for their ids para ma-input sa order_details
                $sql2 = $conn->prepare("SELECT * FROM `cart` WHERE usercateg = ?");
                $sql2->bind_param("s", $username);
                $sql2->execute();
                $inputsql = $sql2->get_result();
                if ($inputsql->num_rows > 0) {
                    while($row3 = $inputsql->fetch_assoc()) {
                        $qty = $row3['qty'];
                        $price = $row3['price'] * $row3['qty'];
                        $sql2prodname = $row3['prodname'];
                        $sql2size = $row3['size'];

                        $sql3 = $conn->prepare("SELECT id FROM `products` WHERE prodname = ?");
                        $sql3->bind_param("s", $sql2prodname);
                        $sql3->execute();
                        $searchsql3 = $sql3->get_result();
                        if ($searchsql3->num_rows > 0){
                            if($roww = $searchsql3->fetch_assoc()){
                                $orderProdID = $roww['id'];

                                $sql4 = $conn->prepare("SELECT size_id FROM `product_sizes` WHERE id = ? AND size = ?");
                                $sql4->bind_param("is", $orderProdID, $sql2size);
                                $sql4->execute();
                                $searchsql4 = $sql4->get_result();
                                if ($searchsql4->num_rows > 0){
                                    if($roww2 = $searchsql4->fetch_assoc()){
                                        $orderSizeID = $roww2['size_id'];

                                        $order_details = $conn->prepare("INSERT INTO `order_details` (orderID, prodID, sizeID, qty, price) VALUES (?,?,?,?,?)");
                                        $order_details->bind_param("iiiid", $orderNumber, $orderProdID, $orderSizeID, $qty, $price);
                                        $order_details->execute();
                                    }
                                }
                            }
                        }
                    }
                }
                //delete items in cart
                $cartdel = $conn->prepare("DELETE FROM `cart` WHERE usercateg = ?");
                $cartdel->bind_param("s", $username);
                $cartdel->execute();

                //search for loop in the visuals in order complete page
                $sqlprod = $conn->prepare("SELECT * FROM `order_details` WHERE orderID = ?");
                $sqlprod->bind_param("i", $orderNumber);
                $sqlprod->execute();
                $searchcart = $sqlprod->get_result();
                
                $reminder = true;
                echo '<script language="javascript">window.alert("Order successfully made.");</script>';

            } else {
                echo '<script language="javascript">alert("Error uploading payment image.");</script>';
                exit;
            }

        //IF GALING SA DIRECT BUY YUNG PAG CHECKOUT
        }else if($cartOut === "false"){
            $prodID = $_POST['prodID'];
            $prodname = $_POST['prodname'];
            $prodDesc = $_POST['prodDesc'];
            $prodQty = $_POST['prodQty'];
            $prodSize = $_POST['prodSize'];
            $prodCateg = $_POST['prodCateg'];
            $prodImg = $_POST['prodImg'];
            
            $subTotal = $_POST['subTotal'];
            $shipping = $_POST['shippingfee'];
            $totalPrice = $subTotal + $shipping;

            $targetDir = "../payments/";
            $paymentImg = basename($_FILES["payment"]["name"]);
            $targetFilePath = $targetDir . $paymentImg;
            if (move_uploaded_file($_FILES["payment"]["tmp_name"], $targetFilePath)) {
                $sqlorder = $conn->prepare("INSERT INTO `orderlist` (orderID, userID, total_qty, mop, total_price, address_id, status) VALUES (?,?,?,?,?,?,?)");
                $sqlorder->bind_param("iiisdis", $orderNumber, $userID, $prodQty, $paymentImg, $totalPrice, $addressID, $status);
                $sqlorder->execute();

                $sql3 = $conn->prepare("SELECT size_id FROM `product_sizes` WHERE id = ? AND size = ?");
                $sql3->bind_param("is", $prodID, $prodSize);
                $sql3->execute();
                $searchsql3 = $sql3->get_result();
                if ($searchsql3->num_rows > 0){
                    if($roww = $searchsql3->fetch_assoc()){
                        $sizeID = $roww['size_id'];
                    }
                }
                $order_details = $conn->prepare("INSERT INTO `order_details` (orderID, prodID, sizeID, qty, price) VALUES (?,?,?,?,?)");
                $order_details->bind_param("iiiid", $orderNumber, $prodID, $sizeID, $prodQty, $subTotal);
                if ($order_details->execute()) {
                    $reminder = true;
                } else {
                    $reminder = false;
                }

                echo '<script language="javascript">window.alert("Order successfully made.");</script>';
            } else {
                echo '<script language="javascript">alert("Error uploading payment image.");</script>';
                exit;
            }


        
        }
    }else{
        echo '<script language="javascript">alert("Please fulfill all details correctly.");</script>';
        echo '<script language="javascript">window.location.href = "../index.php";</script>';
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/item.css">
    <link rel="stylesheet" type="text/css" href="css/checkout.css">
    <link rel="stylesheet" type="text/css" href="css/reminder.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <div class="ribbon">
            <h2 class="rib active-ribbon">Address</h2>
            <div class="span span1 active-span"></div>
            <h2 class="rib active-ribbon">Payment</h2>
            <div class="span span2 active-span"></div>
            <h2 class="rib active-ribbon">Order Complete</h2>
        </div>
        <div class="oldCon">
            <h2 class="ordernum">Order #<?php echo $orderNumber;?></h2>
            <h3 class="date">Date: Nov. 11, 2024</h3>
            <div class="items">
                <h2>Item(s)</h2>
            <?php
                if($cartOut === "true"){
                    if ($searchcart->num_rows > 0) {
                        $subTotal = 0;
                        while($row = $searchcart->fetch_assoc()) {
                            $prodID = $row['prodID'];
                            $sizeID = $row['sizeID'];
                            $cartPrice = $row['price'];
                            $cartQty = $row['qty'];
                            $subTotal += $cartPrice * $cartQty;
                            $totalPrice = $subTotal + $shipping;


                            $sqlsearch2 = $conn->prepare("SELECT * FROM products WHERE id = ?");
                            $sqlsearch2->bind_param("i", $prodID);
                            $sqlsearch2->execute();
                            $searchres2 = $sqlsearch2->get_result();
                            if ($searchres2->num_rows > 0) {
                                if($rowres2 = $searchres2->fetch_assoc()) {
                                    $prodname = $rowres2['prodname'];
                                    $prodCateg = $rowres2['category'];
                                    $prodDesc = $rowres2['description'];
                                    $prodImg = $rowres2['image'];
                                }
                            }

                            $sqlsearch3 = $conn->prepare("SELECT `size` FROM `product_sizes` WHERE size_id = ?");
                            $sqlsearch3->bind_param("i", $sizeID);
                            $sqlsearch3->execute();
                            $searchres3 = $sqlsearch3->get_result();
                            if ($searchres3->num_rows > 0) {
                                if($rowres3 = $searchres3->fetch_assoc()) {
                                    $sizename = $rowres3['size'];
                                }
                            }
                            echo '<div class="item-card">';
                                echo '<img src="../products/'.$prodImg.'" alt="product image">';
                                echo '<div class="item-left">';
                                    echo '<div class="item-up">';
                                        echo '<h2>'.$prodname.'</h2>';
                                        echo '<h3>'.$prodDesc.'</h3>';
                                    echo '</div>';
                                    echo '<div class="item-down">';
                                        echo '<div class="categ">';
                                            echo '<h3>Category: '.$prodCateg.'</h3>';
                                        echo '</div>';
                                        echo '<div class="size">';
                                            echo '<h3>Size: '.$sizename.'</h3>';
                                        echo '</div>';
                                        echo '<div class="qty">';
                                            echo '<h3>Qty : '.$cartQty.'</h3>';
                                        echo '</div>';
                                        echo '<div class="price">';
                                            echo '<h3>Price : P'.number_format($cartPrice, 2).'</h3>';
                                        echo '</div>';
                                    echo '</div>';
                                echo '</div>';
                            echo '</div>';
                        }
                    }
                }else if($cartOut === "false"){
                    echo '<div class="item-card">';
                        echo '<img src="../products/'.$prodImg.'" alt="product image">';
                        echo '<div class="item-left">';
                            echo '<div class="item-up">';
                                echo '<h2>'.$prodname.'</h2>';
                                echo '<h3>'.$prodDesc.'</h3>';
                            echo '</div>';
                            echo '<div class="item-down">';
                                echo '<div class="categ">';
                                    echo '<h3>Category: '.$prodCateg.'</h3>';
                                echo '</div>';
                                echo '<div class="size">';
                                    echo '<h3>Size: '.$prodSize.'</h3>';
                                echo '</div>';
                                echo '<div class="qty">';
                                    echo '<h3>Qty : '.$prodQty.'</h3>';
                                echo '</div>';
                                echo '<div class="price">';
                                    echo '<h3>Price : P'.number_format($subTotal, 2).'</h3>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }
            ?>  
            </div>
            <div class="address-box">
                <div class="address-left">
                    <h3>Name: <?php echo $addressName; ?></h3>
                    <h3>Contact: <?php echo $addressContact; ?></h3>
                    <h3>Street: <?php echo $addressStreet; ?></h3>
                    <h3>Barangay: <?php echo $addressBarangay; ?></h3>
                </div>
                <div class="address-right">
                    <h3>City: <?php echo $addressCity; ?></h3>
                    <h3>Province: <?php echo $addressProvince; ?></h3>
                    <h3>Region: <?php echo $addressRegion; ?></h3>
                    <h3>Postal: <?php echo $addressPostal; ?></h3>
                </div>
            </div>

            <div class="payment-card">
                <div class="paymentt">
                    <h3>Shipping fee: ₱<?php echo $shipping; ?></h3>
                    <h3>Subtotal: ₱<?php echo $subTotal; ?></h3>
                    <h3>------------------------</h3>
                    <h3>Total: ₱<?php echo $totalPrice; ?></h3>
                </div>
                <div class="paymentt2">
                    <h3>Your Payment</h3>
                    <img src="../payments/<?php echo $paymentImg; ?>" alt="">
                </div>

            </div>
        </div>
    </main>

    <div class="create-part cp2" id="reminder" style="display: <?php echo isset($reminder) ? 'flex' : 'none'; ?>;">
        <div class="box-column bx2">
            <img src="../icons/close-bg.png" id="eks" alt="asdasdas" onclick="document.getElementById('reminder').style.display = 'none';">
            <h1>Reminder</h1>
            <div class="flex-box">
                <h3>Please note that you will be able to view the tracking number in your purchase history once the order is shipped. The estimated time of delivery is based on the address and will probably take 3-5 business days after it was shipped. You can then track your order on the <a href="https://www.jtexpress.ph/trajectoryQuery?flag=1" target="_blank">J&T Tracking Page</a></h3>    
            </div>
        </div>
    </div>
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
    </script>
</body>
</html>