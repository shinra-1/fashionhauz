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

if(isset($_GET['ship'])){
    $ship_id = $_GET['ship'];
}

if(isset($_POST['updatetrack'])){
    $tracknumber = $_POST["tracknumber"];
    $id = $_POST["id"];
    $status = "Shipped";
    $stmt9 = $conn->prepare("UPDATE orderlist SET `tracking` = ?, `status` = ? WHERE orderID = ?");
    $stmt9->bind_param("ssi", $tracknumber, $status ,$id);

    if ($stmt9->execute()) {
        echo '<script language="javascript">alert("Tracking successfully updated!");</script>';
        echo '<script language="javascript">window.location.href = "admin-orderlist.php";</script>';
        
    } else {
        echo "Error: " . $stmt->error;
    }
};

if(isset($_POST['payment'])){
    $mop_id = $_POST['payment_id'];
    $findmop = $conn->prepare("SELECT * FROM orderlist WHERE orderID = ?");
    $findmop->bind_param("i", $mop_id);
    $findmop->execute();
    $mopres = $findmop->get_result();
    if ($moprow = $mopres->fetch_assoc()){
        $payment = $moprow['mop'];
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
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    <div class="upper margin">
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
                    <th>Creation Date</th>
                    <th>Action</th>
                </thead>
                <tbody>
                <?php $select = mysqli_query($conn,"SELECT * FROM orderlist WHERE `mop` != 'Cash' AND `status` != 'Rejected'AND `status` != 'Refunded'");
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
                            $totalprice = $row3['price'] * $row3['qty'];
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
                        <td>₱<?php echo $totalprice; ?></td>
                        <td>
                            <div class="option">
                                <form action="admin-orderlist.php" method="post">
                                    <input type="hidden" name="address_id" value="<?php echo $row['orderID']; ?>">
                                    <button type="submit" id="op1"  class="op1">View</button>
                                </form>
                            </div>
                        </td>
                        <td>
                            <?php 
                                if ($row['status'] === "Shipped"){
                                    echo '<div class="option"><form action="admin-orderlist-payment.php" method="post">';
                                    echo '<input type="hidden" name="trackingnum" value="'.$row['orderID'].'">';
                                    echo '<button type="submit" id="op1"  class="op1">Input</button></form></div>';
                                }else{
                                    echo $row['status'];
                                }
                            ?>
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
    <div class="create-part cp2" id="newaddress" style="display: <?php echo isset($ship_id) ? 'flex' : 'none'; ?>;">
        <div class="box-column">
            <img src="icons/close-bg.png" id="close-address" alt="asdasdas" onclick="window.location.href = 'admin-orderlist.php'">
            <h1>Tracking Number</h1>
            <form action="admin-orderlist-payment.php" method="post" enctype="multipart/form-data">
                <div class="flex-box">
                    <div class="left-box">
                    </div>
                    <div class="right-box">
                        <input type="hidden" name="id" value="<?php echo isset($ship_id) ? $ship_id : ''; ?>">
                        <input type="text" name="tracknumber" placeholder="Enter tracking number...">
                    </div>
                </div>
                <button type="submit" name="updatetrack"><img src="icons/update.png" alt="add">Update</button>
            </form>
        </div>
    </div>
    <div class="create-part cp4" id="newitem" style="display: <?php echo isset($payment) ? 'flex' : 'none'; ?>;">
        <div class="payment-box">
            <img src="icons/close-bg.png" id="close-payment" alt="free" onclick="prevpage()">
            <h1>Payment Receipt</h1>
            <img src="payments/<?php echo $payment; ?>" alt="aa" class="picreceipt">
        </div>
    </div>
    <script>
        function prevpage(event) {
            window.location.href = "admin-orderlist.php";
        }
        document.getElementById("close-address").onclick = function() {
            window.location.href = "admin-orderlist.php";
        }; 
    </script>
</body>
</html>