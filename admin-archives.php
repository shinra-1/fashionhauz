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
    echo "Hello, Guest! Please log in.";
    header("location: login.php");
}

if (isset($_POST['item_id'])) {
    $itemID = $_POST['item_id'];
    $stmt = $conn->prepare("SELECT * FROM orderlist WHERE orderID = ?");
    $stmt->bind_param("i", $itemID);
    $stmt->execute();
    $itemresult = $stmt->get_result();
}else if (isset($_POST['address_id'])) {
    $address_id = $_POST['address_id'];
    $stmt = $conn->prepare("SELECT * FROM orderlist WHERE orderID = ?");
    $stmt->bind_param("i", $address_id);
    $stmt->execute();
    $result2 = $stmt->get_result();
    
    if ($row2 = $result2->fetch_assoc()) {
        $id = $row2['userID'];
        $address = $row2['address_id'];
    } else {
        echo "Status not found.";
    }
    $findAddress =  mysqli_query($conn,"SELECT * FROM `address` WHERE `userID` = $id AND `id` = $address");
    if ($rowAdd=mysqli_fetch_assoc($findAddress)){
        $name = $rowAdd['name']; 
        $cnumber = $rowAdd['cnumber']; 
        $street = $rowAdd['street']; 
        $barangay = $rowAdd['barangay']; 
        $city = $rowAdd['city']; 
        $province = $rowAdd['province']; 
        $region = $rowAdd['region']; 
        $postal = $rowAdd['postal']; 
    }

}else if(isset($_POST['payment_id'])){
    $paymentOrder = $_POST['payment_id'];
    
    $paysql = $conn->prepare("SELECT * FROM orderlist WHERE orderID = ?");
    $paysql->bind_param("i", $paymentOrder);
    $paysql->execute();
    $payresult = $paysql->get_result();
    if ($payrow = $payresult->fetch_assoc()) {
        $paymentImg = $payrow['mop'];
    } else {
        echo "Payment not found.";
    }


}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/admin-emplist.css">
    <link rel="stylesheet" type="text/css" href="css/admin-orderlist.css">
    <link rel="stylesheet" type="text/css" href="css/admin-archives.css">
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    <div class="upper margin">
        <form action="admin-archives.php" method="post">
            <label for="date" class="labeldate"><img src="icons/calendar.png" alt=""></label><input type="date" name="date" class="inputdate">
            <label for="date" class="labeldate aa"><img src="icons/status.png" alt=""></label>
            <select id="combo-box" class="combobox" name="status">
                <option value='' disabled selected hidden>Status</option>
                <option value='Pending'>Pending</option>
                <option value='Accepted'>Accepted</option>
                <option value='Rejected'>Rejected</option>
                <option value='Completed'>Completed</option>
                <option value='Refunded'>Refunded</option>
            </select>
            <button class="buttondate" name="search"><img src="icons/search.png" alt=""></button>
        </form>
    </div>
    <h1 class="content-title">Sales Archives</h1>
    <section>
        <div class="table-bg">
            <table>
                <thead>
                    <th>Order ID</th>
                    <th>Username</th>
                    <th>Item(s)</th>
                    <th>Qty</th>
                    <th>Payment</th>
                    <th>Total Price</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Date</th>
                </thead>
                <tbody>
                <?php 
                // Display the fetched activity logs
                if (isset($_POST['search'])) {
                    if(!empty($_POST["status"])&&!empty($_POST["date"])){
                        $date = $_POST["date"];
                        $status = $_POST["status"];
                    
                        $select = mysqli_query($conn, "SELECT * FROM orderlist WHERE `status` = '$status' AND DATE(`date_created`) = '$date'");
                        while($row=mysqli_fetch_assoc($select)){ 
                            $userID = $row['userID'];
                            $orderID = $row['orderID'];
                            $selectUser = mysqli_query($conn,"SELECT uname FROM users WHERE `userID` = $userID"); 
                            if ($rowUser=mysqli_fetch_assoc($selectUser)){
                                $userName= $rowUser['uname']; 
                            }
                            $products = [];
                            $totalprice = 0;
                            $quantity = 0;
                            $find = mysqli_query($conn,"SELECT * FROM order_details WHERE `orderID` = $orderID");
                            while($row3=mysqli_fetch_assoc($find)){
                                $prodID= $row3['prodID'];
                                $totalprice += $row3['price'] * $row3['qty'];
                                $quantity += $row3['qty'];
                                $find2 = mysqli_query($conn,"SELECT prodname FROM products WHERE `id` = $prodID");
                                if($row4=mysqli_fetch_assoc($find2)){
                                    $products[]= $row4['prodname']; 
                                }
                            }
                            echo '<tr>';
                            echo '<td>' .$row['orderID'].'</td>';
                            echo '<td>' .$userName. '</td>';
                            echo '<td>';
                            echo ' <div class="option">';
                            echo '<form action="admin-archives.php" method="post">';
                            echo '<input type="hidden" name="item_id" value="'.$row['orderID'].'">';
                            echo '<button type="submit" id="op1" class="op1">View</button>';
                            echo '</form>';
                            echo '</div>';
                            echo '</td>';
                            echo '<td>'.$quantity. '</td>';
                            echo '<td><div class="option"><form action="admin-archives.php" method="post"><input type="hidden" name="payment_id" value="'.$row['orderID'].'"><button type="submit" id="op1" class="op1" name="payment">View</button></form></div></td>';
                            echo '<td>₱'.$totalprice.'</td>';
                            echo '<td>';
                            echo '<div class="option">';
                            echo '<form action="admin-archives.php" method="post">';
                            echo '<input type="hidden" name="address_id" value="'.$row['orderID'].'">';
                            echo '<button type="submit" id="op1" class="op1">View</button>';
                            echo '</form>';
                            echo '</div>';
                            echo '</td>';
                            echo '<td>'.$row['status'].'</td>';
                            echo '<td>'.$row['date_created'].'</td>';
                            echo '<td>';
                            echo '</td>';
                            echo '</tr>';
                        };
                    }else{
                        $select = mysqli_query($conn,"SELECT * FROM orderlist WHERE `address_id` != 0");
                        while($row=mysqli_fetch_assoc($select)){ 
                            $userID = $row['userID'];
                            $orderID = $row['orderID'];
                            $selectUser = mysqli_query($conn,"SELECT uname FROM users WHERE `userID` = $userID"); 
                            if ($rowUser=mysqli_fetch_assoc($selectUser)){
                                $userName= $rowUser['uname']; 
                            }
                            $products = [];
                            $totalprice = 0;
                            $quantity = 0;
                            $find = mysqli_query($conn,"SELECT * FROM order_details WHERE `orderID` = $orderID");
                            while($row3=mysqli_fetch_assoc($find)){
                                $prodID= $row3['prodID'];
                                $totalprice += $row3['price'] * $row3['qty'];
                                $quantity += $row3['qty'];
                                $find2 = mysqli_query($conn,"SELECT prodname FROM products WHERE `id` = $prodID");
                                if($row4=mysqli_fetch_assoc($find2)){
                                    $products[]= $row4['prodname']; 
                                }
                            }
                            echo '<tr>';
                            echo '<td>' .$row['orderID'].'</td>';
                            echo '<td>' .$userName. '</td>';
                            echo '<td>';
                            echo ' <div class="option">';
                            echo '<form action="admin-archives.php" method="post">';
                            echo '<input type="hidden" name="item_id" value="'.$row['orderID'].'">';
                            echo '<button type="submit" id="op1" class="op1">View</button>';
                            echo '</form>';
                            echo '</div>';
                            echo '</td>';
                            echo '<td>'.$quantity. '</td>';
                            echo '<td><div class="option"><form action="admin-archives.php" method="post"><input type="hidden" name="payment_id" value="'.$row['orderID'].'"><button type="submit" id="op1" class="op1" name="payment">View</button></form></div></td>';
                            echo '<td>₱'.$totalprice.'</td>';
                            echo '<td>';
                            echo '<div class="option">';
                            echo '<form action="admin-archives.php" method="post">';
                            echo '<input type="hidden" name="address_id" value="'.$row['orderID'].'">';
                            echo '<button type="submit" id="op1" class="op1">View</button>';
                            echo '</form>';
                            echo '</div>';
                            echo '</td>';
                            echo '<td>'.$row['status'].'</td>';
                            echo '<td>'.$row['date_created'].'</td>';
                            echo '<td>';
                            echo '</td>';
                            echo '</tr>';
                        };
                    }
                } else {
                    $select = mysqli_query($conn,"SELECT * FROM orderlist WHERE `address_id` != 0");
                    while($row=mysqli_fetch_assoc($select)){ 
                        $userID = $row['userID'];
                        $orderID = $row['orderID'];
                        $selectUser = mysqli_query($conn,"SELECT uname FROM users WHERE `userID` = $userID"); 
                        if ($rowUser=mysqli_fetch_assoc($selectUser)){
                            $userName= $rowUser['uname']; 
                        }
                        $products = [];
                        $totalprice = 0;
                        $quantity = 0;
                        $find = mysqli_query($conn,"SELECT * FROM order_details WHERE `orderID` = $orderID");
                        while($row3=mysqli_fetch_assoc($find)){
                            $prodID= $row3['prodID'];
                            $totalprice += $row3['price'] * $row3['qty'];
                            $quantity += $row3['qty'];
                            $find2 = mysqli_query($conn,"SELECT prodname FROM products WHERE `id` = $prodID");
                            if($row4=mysqli_fetch_assoc($find2)){
                                $products[]= $row4['prodname']; 
                            }
                        }
                        echo '<tr>';
                        echo '<td>' .$row['orderID'].'</td>';
                        echo '<td>' .$userName. '</td>';
                        echo '<td>';
                        echo ' <div class="option">';
                        echo '<form action="admin-archives.php" method="post">';
                        echo '<input type="hidden" name="item_id" value="'.$row['orderID'].'">';
                        echo '<button type="submit" id="op1" class="op1">View</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</td>';
                        echo '<td>'.$quantity. '</td>';
                        echo '<td><div class="option"><form action="admin-archives.php" method="post"><input type="hidden" name="payment_id" value="'.$row['orderID'].'"><button type="submit" id="op1" class="op1" name="payment">View</button></form></div></td>';
                        echo '<td>₱'.$totalprice.'</td>';
                        echo '<td>';
                        echo '<div class="option">';
                        echo '<form action="admin-archives.php" method="post">';
                        echo '<input type="hidden" name="address_id" value="'.$row['orderID'].'">';
                        echo '<button type="submit" id="op1" class="op1">View</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</td>';
                        echo '<td>'.$row['status'].'</td>';
                        echo '<td>'.$row['date_created'].'</td>';
                        echo '<td>';
                        echo '</td>';
                        echo '</tr>';
                    };
                }
                ?>
                </tbody>
            </table>
        </div>
    </section>
    <div class="payment-container cp4" id="newpayment" style="display: <?php echo isset($_POST['payment_id']) ? 'flex' : 'none'; ?>;">
        <div class="payment-box">
            <img src="icons/close-bg.png" id="close-payment" alt="asdasdas" onclick="document.getElementById('newpayment').style.display = 'none';">
            <h1>Payment</h1>
            <div class="picreceipt">
                <img src="payments/<?php echo $paymentImg; ?>" alt="#">
            </div>
        </div>
    </div>
    <div class="create-part cp2" id="newaddress" style="display: <?php echo isset($name) ? 'flex' : 'none'; ?>;">
        <div class="box-column">
            <img src="icons/close-bg.png" id="close-address" alt="asdasdas" onclick="document.getElementById('newaddress').style.display = 'none';">
            <h1>Address</h1>
            <div class="flex-box">
                <div class="left-box">
                    <label for="name">Name</label>
                    <input type="text" value="<?php echo $name ?>" name="name" readonly>
                    <label for="cnumber">Contact Number</label>
                    <input type="text" value="<?php echo $cnumber ?>" name="cnumber" readonly>
                    <label for="street">Street</label>
                    <input type="text" value="<?php echo $street ?>" name="street" readonly>
                    <label for="barangay">Barangay</label>
                    <input type="text" value="<?php echo $barangay ?>" name="barangay" readonly>
                </div>
                <div class="right-box">
                    <label for="city">City</label>
                    <input type="text" value="<?php echo $city ?>" name="city" readonly>
                    <label for="province">Province</label>
                    <input type="text" value="<?php echo $province ?>" name="province" readonly>
                    <label for="region">Region</label>
                    <input type="text" value="<?php echo $region ?>" name="region" readonly>
                    <label for="postal">Postal Code</label>
                    <input type="text" value="<?php echo $postal ?>" name="postal" readonly>
                </div>
            </div>
        </div>
    </div>
    <div class="create-part" id="newitem" style="display: <?php echo isset($itemresult) ? 'flex' : 'none'; ?>;">
        <div class="box-column">
            <img src="icons/close-bg.png" id="close-item" alt="asdasdas" onclick="document.getElementById('newitem').style.display = 'none';">
            <h1>Items</h1>
            <table>
                <thead>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Qty</th>
                    <th>Price</th>
                </thead>
                <tbody>
                    <?php
                        if ($row = $itemresult->fetch_assoc()) {
                            $userID = $row['userID'];
                            
                            
                            $findid =  mysqli_query($conn,"SELECT * FROM `order_details` WHERE `orderID` = $itemID");
                            
                            while ($rowAdd=mysqli_fetch_assoc($findid)){
                                $prodid = $rowAdd['prodID'];
                                $prodqty = $rowAdd['qty'];
                                $prodprice = $rowAdd['price'];
                                $size_id = $rowAdd['sizeID'];

                                $findsize =  mysqli_query($conn,"SELECT * FROM `product_sizes` WHERE `size_id` = $size_id");
                                while ($rowsize=mysqli_fetch_assoc($findsize)){
                                    $prodsize = $rowsize['size'];

                                $findItems =  mysqli_query($conn,"SELECT * FROM `products` WHERE `id` = $prodid");
                                while ($rowItem=mysqli_fetch_assoc($findItems)){
                                    $prodname = $rowItem['prodname'];
                                    $prodimage = $rowItem['image'];
                    ?>  
                    <tr>
                        <td><img src="products/<?php echo $prodimage; ?>" alt="image" class="prodimgg" ></td>
                        <td><?php echo $prodname; ?></td>
                        <td><?php echo $prodsize; ?></td>
                        <td><?php echo $prodqty; ?></td>
                        <td>₱<?php echo $prodprice; ?></td>
                    </tr>
                    <?php  }}}};?>
                </tbody>
            </table>
        </div>
    </div>
    
</body>
</html>