<?php

include '../config.php';
function isValidUser($conn, $username) {
    $stmt = $conn->prepare("SELECT uname FROM users WHERE uname = ? AND category = 'staff'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0; // Returns true if the user exists
}
if (isset($_COOKIE['username'])) {
    $username = htmlspecialchars($_COOKIE['username']);
    $user_id = $_COOKIE['id'];

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
    header("location: ../login.php");
}
if (isset($_POST['item_id'])) {
    $orderID = $_POST['item_id'];
    $stmt = $conn->prepare("SELECT * FROM orderlist WHERE orderID = ?");
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    $itemresult = $stmt->get_result();
};


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/admin-emplist.css">
    <link rel="stylesheet" type="text/css" href="../css/admin-orderlist.css">
    <link rel="stylesheet" type="text/css" href="../css/admin-archives.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'pos-nav.php'; ?>
    <div class="upper margin">
        <form action="pos-archives.php" method="post">
            <label for="date" class="labeldate"><img src="../icons/calendar.png" alt=""></label><input type="date" name="date" class="inputdate">
            <button class="buttondate" name="search"><img src="../icons/search.png" alt=""></button>
        </form>
    </div>
    <h1 class="content-title">Sales Logs</h1>
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
                    <th>Status</th>
                    <th>Date</th>
                </thead>
                <tbody>
                <?php 
                // Display the fetched activity logs
                if (isset($_POST['search'])) {
                    if(!empty($_POST["date"])){
                        $date = $_POST["date"];
                        $select = mysqli_query($conn,"SELECT * FROM orderlist WHERE DATE(date_created) = '$date' AND `userID` = '$user_id' ORDER BY orderID DESC");
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
                                $totalprice += $row3['price'];
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
                            echo '<form action="pos-archives.php" method="post">';
                            echo '<input type="hidden" name="item_id" value="'.$row['orderID'].'">';
                            echo '<button type="submit" id="op1" class="op1">View</button>';
                            echo '</form>';
                            echo '</div>';
                            echo '</td>';
                            echo '<td>'.$quantity. '</td>';
                            echo '<td>'.$row['mop'].'</td>';
                            echo '<td>₱'.$totalprice.'</td>';
                            echo '<td>'.$row['status'].'</td>';
                            echo '<td>'.$row['date_created'].'</td>';
                            echo '<td>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
                        $detail ='Viewed '.$date.' sales history.';
                        $archives->bind_param("ss", $detail, $username);
                        $archives->execute();

                    }else{
                        echo '<script language="javascript">alert("Please select a date.");</script>';
                        echo '<script language="javascript">window.location.href = "pos-archives.php";</script>';
                    }
                } else {
                    $select = mysqli_query($conn,"SELECT * FROM orderlist WHERE `userID` = '$user_id' ORDER BY orderID DESC");
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
                        echo '<form action="pos-archives.php" method="post">';
                        echo '<input type="hidden" name="item_id" value="'.$row['orderID'].'">';
                        echo '<button type="submit" id="op1" class="op1">View</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</td>';
                        echo '<td>'.$quantity. '</td>';
                        echo '<td>'.$row['mop'].'</td>';
                        echo '<td>₱'.$totalprice.'</td>';
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
    
    <div class="create-part" id="newitem" style="display: <?php echo isset($itemresult) ? 'flex' : 'none'; ?>;">
        <div class="box-column">
            <img src="../icons/close-bg.png" id="close-item" alt="asdasdas" onclick="document.getElementById('newitem').style.display = 'none';">
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
                            $ordernumber = $row['orderID'];
                            $findid =  mysqli_query($conn,"SELECT * FROM `order_details` WHERE `orderID` = $ordernumber");
                            
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
                        <td><img src="../products/<?php echo $prodimage; ?>" alt="image" class="prodimgg" ></td>
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