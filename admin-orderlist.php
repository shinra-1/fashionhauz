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




if (isset($_POST['update_id'])) {
    $update_id = $_POST['update_id'];
    $stmt = $conn->prepare("SELECT * FROM orderlist WHERE orderID = ?");
    $stmt->bind_param("i", $update_id);
    $stmt->execute();
    $result1 = $stmt->get_result();
    
    if ($row1 = $result1->fetch_assoc()) {
        $id = $row1['orderID'];
        $status = $row1['status'];
    } else {
        echo "Status not found.";
    }
};
if (isset($_POST['address_id'])) {
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

};
if(isset($_POST['submit'])){
    $status = $_POST["status"];
    $id = $_POST["id"];
    
    if($status === "Shipped"){
        echo '<script language="javascript">window.location.href = "admin-orderlist-payment.php?ship='.$id.'";</script>';
    }else if($status === "Accepted"){
        $get = $conn->prepare("SELECT * FROM `order_details` WHERE orderID = ?");
        $get->bind_param("i", $id);
        $get->execute();
        $getres = $get->get_result();
        if ($getres->num_rows > 0) {
            while($getrow = $getres->fetch_assoc()) {
                $sizeID = $getrow['sizeID'];
                $minusqty = $getrow['qty'];
                
                $maxsql = $conn->prepare("SELECT * FROM product_sizes WHERE size_id = ?");
                $maxsql->bind_param("i", $sizeID);
                $maxsql->execute();
                $maxresss = $maxsql->get_result();
                if ($maxresss->num_rows > 0) {
                    if($maxrow2 = $maxresss->fetch_assoc()) {
                        $availableqty = $maxrow2['qty'];
                        $sizename = $maxrow2['size'];
                        if($minusqty > $availableqty){
                            $exceed = 1; 
                            echo '<script language="javascript">alert("The quantity exceeds the available stocks for size '.$sizename.', please try again.");</script>';
                            echo '<script language="javascript">window.location.href = "admin-orderlist.php";</script>';
                        }else{
                            $exceed = 0; 
                            // Update query to subtract $minusqty from qty column in product_sizes
                            $update = $conn->prepare("UPDATE `product_sizes` SET qty = qty - ? WHERE size_id = ?");
                            $update->bind_param("ii", $minusqty, $sizeID);
                            $update->execute();
                        }
                    }
                }
            }
            $stmt = $conn->prepare("UPDATE orderlist SET `status` = ? WHERE orderID = ?");
            $stmt->bind_param("si", $status, $id);
            if ($exceed === 0) {
                $stmt->execute();
                $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
                $detail = 'Updated the status of order #'.$id.' into '.$status;
                $archives->bind_param("ss", $username, $detail);
                $archives->execute();


                echo '<script language="javascript">alert("Status successfully updated!");</script>';
                echo '<script language="javascript">window.location.href = "admin-orderlist.php";</script>';
                
            }else if($exceed === 1){
                echo '<script language="javascript">window.location.href = "admin-orderlist.php";</script>';
            }else{
                echo "Error: " . $stmt->error;
            }
                
        }
    }else{
        $stmt = $conn->prepare("UPDATE orderlist SET `status` = ? WHERE orderID = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
            $detail = 'Updated the status of order #'.$id.' into '.$status;
            $archives->bind_param("ss", $username, $detail);
            $archives->execute();


            echo '<script language="javascript">alert("Status successfully updated!");</script>';
            echo '<script language="javascript">window.location.href = "admin-orderlist.php";</script>';
            
        } else {
            echo "Error: " . $stmt->error;
        }
    }
};
if (isset($_POST['item_id'])) {
    $itemID = $_POST['item_id'];
    $stmt = $conn->prepare("SELECT * FROM orderlist WHERE orderID = ?");
    $stmt->bind_param("i", $itemID);
    $stmt->execute();
    $itemresult = $stmt->get_result();
};



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/admin-emplist.css">
    <link rel="stylesheet" type="text/css" href="css/admin-orderlist.css">
    <link rel="stylesheet" type="text/css" href="css/status-box.css">
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    <div class="upper margin">
        <form action="admin-orderlist.php" method="post">
            <label for="status" class="labeldate aa"><img src="icons/status.png" alt=""></label>
            <select id="combo-box" class="combobox" name="status">
                <option value='' disabled selected hidden>Status</option>
                <option value='Pending'>Pending</option>
                <option value='Accepted'>Accepted</option>
                <option value='Rejected'>Rejected</option>
                <option value='Shipped'>Shipped</option>
                <option value='Completed'>Completed</option>
            </select>
            <button class="buttondate" name="search"><img src="icons/search.png" alt=""></button>
        </form>
    </div>
    <h1 class="content-title">Order List</h1>
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
                    <th>Tracking</th>
                    <th>Creation Date</th>
                    <th>Action</th>
                </thead>
                <tbody>
                    <?php
                        if (isset($_POST['search'])) {
                            $statuslist = isset($_POST['status']) ? $_POST['status'] : null;
                            $mop = "Cash";
                            $query = "SELECT * FROM orderlist WHERE `mop` != ?  AND `status` != 'Refunded' AND `status` != 'Completed'";
                            $params = [$mop];
                            // Add status condition if set
                            if ($statuslist) {
                                $query .= " AND `status` = ?";
                                $params[] = $statuslist;
                            }
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param(str_repeat("s", count($params)), ...$params); // Binds all parameter in a string
                            $stmt->execute();
                            $select = $stmt->get_result();
                        } else {
                            $select = $conn->query("SELECT * FROM orderlist WHERE `mop` != 'Cash'  AND `status` != 'Refunded' AND `status` != 'Completed'");
                        }
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
                    ?>
                    <tr>
                        <td><?php echo $row['orderID']; ?></td>
                        <td><?php echo $userName; ?></td>
                        <td>
                            <div class="option">
                                <form action="admin-orderlist.php" method="post">
                                    <input type="hidden" name="item_id" value="<?php echo $row['orderID']; ?>">
                                    <button type="submit" id="op1" class="op1">View</button>
                                </form>
                            </div>
                        </td>
                        <td><?php echo $quantity; ?></td>
                        <td><div class="option">
                                <form action="admin-orderlist-payment.php" method="post">
                                    <input type="hidden" name="payment_id" value="<?php echo $row['orderID']; ?>">
                                    <button type="submit" id="op1" class="op1" name="payment">View</button>
                                </form>
                            </div></td>
                        <td>₱<?php echo $row['total_price']; ?></td>
                        <td>
                            <div class="option">
                                <form action="admin-orderlist.php" method="post">
                                    <input type="hidden" name="address_id" value="<?php echo $row['orderID']; ?>">
                                    <button type="submit" id="op1"  class="op1">View</button>
                                </form>
                            </div>
                        </td>
                        <td><?php  echo $row['status']; ?></td>
                        <td><?php
                            if(!empty($row['tracking'])){
                                echo $row['tracking'];
                            }else{
                                echo 'N/A';
                            }?>
                        </td>
                        <td><?php echo $row['date_created']; ?></td>
                        <td>
                        <div class="option">
                                <form action="admin-orderlist.php" method="post">
                                    <input type="hidden" name="update_id" value="<?php echo $row['orderID']; ?>">
                                    <button type="submit" id="op1" class="op1">Update</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php   };  ?>
                </tbody>
            </table>
        </div>
    </section>
    
    <div class="create-part cp3" id="newcat" style="display: <?php echo isset($status) ? 'flex' : 'none'; ?>;">
        <div class="box-column">
            <img src="icons/close-bg.png" id="close-status" alt="asdasdas" onclick="document.getElementById('newcat').style.display = 'none';">
            <h1>Update Status</h1>
            <form action="admin-orderlist.php" method="post" enctype="multipart/form-data">
            <div class="flex-box">
                <div class="left-box">
                </div>
                <div class="right-box">
                    <input type="hidden" name="id" value="<?php echo isset($id) ? $id : ''; ?>">
                    <select name="status" required style="width: 250px; margin-left: -20px; font-size: 1.5rem;">
                        <option value="" disabled selected><?php echo $status; ?></option>
                        <option value="Accepted">Accepted</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Completed">Completed</option>
                        <option value="Refunded">Refunded</option>
                    </select>
                </div>
            </div>
            <button type="submit" name="submit"><img src="icons/update.png" alt="add">Update</button>
            </form>
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
            <img src="icons/close-bg.png" id="close-itemm" alt="asdasdas" onclick="document.getElementById('newitem').style.display = 'none';">
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
                        if ($itemresult->num_rows > 0) {
                            while ($row = $itemresult->fetch_assoc()) {
                                $userID = $row['userID'];
                                
                                $findid =  mysqli_query($conn, "SELECT * FROM `order_details` WHERE `orderID` = $itemID");
                                
                                while ($rowAdd = mysqli_fetch_assoc($findid)) {
                                    $prodid = $rowAdd['prodID'];
                                    $prodqty = $rowAdd['qty'];
                                    $prodprice = $rowAdd['price'];
                                    $size_id = $rowAdd['sizeID'];
                        
                                    $findsize = mysqli_query($conn, "SELECT * FROM `product_sizes` WHERE `size_id` = $size_id");
                                    while ($rowsize = mysqli_fetch_assoc($findsize)) {
                                        $prodsize = $rowsize['size'];
                        
                                        $findItems = mysqli_query($conn, "SELECT * FROM `products` WHERE `id` = $prodid");
                                        while ($rowItem = mysqli_fetch_assoc($findItems)) {
                                            $prodname = $rowItem['prodname'];
                                            $prodimage = $rowItem['image'];
                        ?>
                        <tr>
                            <td><img src="products/<?php echo $prodimage; ?>" alt="image" class="prodimgg"></td>
                            <td><?php echo $prodname; ?></td>
                            <td><?php echo $prodsize; ?></td>
                            <td><?php echo $prodqty; ?></td>
                            <td>₱<?php echo $prodprice; ?></td>
                        </tr>
                        <?php 
                                        }
                                    }
                                }
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>


</body>
</html>