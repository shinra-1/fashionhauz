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


$status = isset($_GET['status']) ? $_GET['status'] : null;
if ($status) {
    $orders = $conn->prepare("SELECT orderID, userID, total_qty, mop, total_price, address_id, status, tracking, DATE(date_created) AS date FROM orderlist WHERE userID = ? AND status = ?");
    $orders->bind_param("is", $userID, $status);
} else {
    $orders = $conn->prepare("SELECT orderID, userID, total_qty, mop, total_price, address_id, status, tracking, DATE(date_created) AS date FROM orderlist WHERE userID = ?");
    $orders->bind_param("i", $userID);
}
$orders->execute();
$ordersres = $orders->get_result();

if(isset($_GET['view'])){
    $viewID = $_GET['view'];
    $view = $conn->prepare("SELECT * FROM order_details WHERE orderID = ?");
    $view->bind_param("i", $viewID);
    $view->execute();
    $viewres = $view->get_result();
                   
}else if(isset($_GET['payment'])){
    $paymentID = $_GET['payment'];
    $pay = $conn->prepare("SELECT mop FROM orderlist WHERE orderID = ?");
    $pay->bind_param("i", $paymentID);
    $pay->execute();
    $payres = $pay->get_result();
    if ($payres->num_rows > 0) {
        if($payrow = $payres->fetch_assoc()) {
            $payImg = $payrow['mop'];
        }
    }
                   
}else if(isset($_GET['address'])){
    $address1 = $_GET['address'];
    $getadd = $conn->prepare("SELECT * FROM `address` WHERE id = ?");
    $getadd->bind_param("i", $address1);
    $getadd->execute();
    $addres2 = $getadd->get_result();
    if ($addres2->num_rows > 0) {
        if($addrow = $addres2->fetch_assoc()) {
            $add_name = $addrow['name']; 
            $cnumber = $addrow['cnumber']; 
            $street = $addrow['street']; 
            $barangay = $addrow['barangay']; 
            $city = $addrow['city']; 
            $province = $addrow['province']; 
            $region = $addrow['region']; 
            $postal = $addrow['postal'];
        }
    }
                   
}else if(isset($_GET['cancel'])){
    $cancelID = $_GET['cancel'];
    $cancel = $conn->prepare("UPDATE orderlist SET `status` = ? WHERE orderID = ?");
    $newstat = "Cancelled";
    $cancel->bind_param("si", $newstat, $cancelID);
    if ($cancel->execute()) {

        echo '<script language="javascript">alert("Order successfully cancelled.");</script>';
        echo '<script language="javascript">window.location.href = "history.php";</script>';
    } else {
        echo "Error: " . $cancel->error;
    }      
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/history.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
    <script>
        function filterByStatus(status) {
            window.location.href = 'history.php' + (status ? '?status=' + status : '');
        }
    </script>
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <div class="navright">
            <div class="navleft">
                <div class="navtop">
                    <h1><?php echo $username; ?></h1>
                    <h3>User</h3>
                </div>
                <div class="navmiddle">
                    <a href="account.php" >Account Info</a>
                    <a href="addresses.php">Addresses</a>
                    <a href="history.php" class="active">Purchase History</a>
                    <a href="change-pass.php" >Change Password</a>
                </div>
                <div class="navbottom">
                    <form action="../logout.php" class="logout" method="post">
                        <input type="hidden" name="usern" value="<?php echo $username; ?>">
                        <button type="submit" name="logout">Logout</button>
                    </form>
                </div>
            </div>
            <select name="status" class="status" onchange="filterByStatus(this.value)">
                <option value="" <?php echo !isset($_GET['status']) ? 'selected' : ''; ?>>All</option>
                <option value="Pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Accepted" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Accepted') ? 'selected' : ''; ?>>Accepted</option>
                <option value="Rejected" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                <option value="Cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                <option value="Refunded" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Refunded') ? 'selected' : ''; ?>>Refunded</option>
                <option value="Completed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
            </select>
            <div class="container-box">
                <table class="table">
                    <thead>
                        <th>Order ID</th>
                        <th>Item(s)</th>
                        <th>Qty</th>
                        <th>Total Price</th>
                        <th>Payment</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Tracking</th>
                        <th>Date</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                        <?php
                            if ($ordersres->num_rows > 0) {
                                while($orderrow = $ordersres->fetch_assoc()) {
                                    $orderID = $orderrow['orderID'];
                                    $totalQty = $orderrow['total_qty'];
                                    $totalPrice = $orderrow['total_price'];
                                    $addressID = $orderrow['address_id'];
                                    $status = $orderrow['status'];
                                    $date = $orderrow['date'];
                                    $formattedDate = date("M. d, Y", strtotime($date));
                                    $tracking = $orderrow['tracking'];
                              
                        ?>
                        <tr>
                            <td><?php echo $orderID;?></td>
                            <td>
                                <div class="action">
                                    <a href="history.php?view=<?php echo $orderID; ?>">View</a>
                                </div>
                            </td>
                            <td><?php echo $totalQty;?></td>
                            <td>₱<?php echo number_format($totalPrice,2);?></td>
                            <td>
                                <div class="action">
                                <a href="history.php?payment=<?php echo $orderID; ?>">View</a>
                                </div>
                            </td>
                            <td>
                                <div class="action">
                                <a href="history.php?address=<?php echo $addressID; ?>">View</a>
                                </div>
                            </td>
                            <td><?php echo $status;?></td>
                            <td><?php
                                if(!empty($tracking)){
                                    echo $tracking;
                                }else{
                                    echo 'N/A';
                                }
                            ?></td>
                            <td><?php echo $formattedDate;?></td>
                            <td>
                                <div class="action">
                                <?php if($status === "Pending"){
                                        echo '<a href="history.php?cancel='.$orderID.'" onclick="return confirm(\'Are you sure you want to cancel this order?\');">Cancel</a>';
                                    }else{
                                        echo 'N/A';
                                    }
                                ?>
                                
                                </div>
                            </td>
                        </tr>
                        <?php
                              }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- pop up for items -->
    <div class="create-part" id="items" style="display: <?php echo isset($viewID) ? 'flex' : 'none'; ?>;">
        <div class="box-column">
            <img src="../icons/close-bg.png" id="eks" alt="asdasdas" onclick="document.getElementById('items').style.display = 'none';">
            <h1>Item(s)</h1>
            <div class="flex-box">
               <table class="table2">
                    <thead>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Qty</th>
                        <th>Price</th>
                    </thead>
                    <tbody>
                    <?php
                        if ($viewres->num_rows > 0) {
                            while($viewrow = $viewres->fetch_assoc()) {
                                $prodid = $viewrow['prodID'];
                                $prodqty = $viewrow['qty'];
                                
                                $size_id = $viewrow['sizeID'];

                                $sizesql = $conn->prepare("SELECT * FROM product_sizes WHERE size_id = ?");
                                $sizesql->bind_param("i", $size_id);
                                $sizesql->execute();
                                $sizeres = $sizesql->get_result();
                                if ($sizeres->num_rows > 0){
                                while($rowsize = $sizeres->fetch_assoc()) {
                                    $prodsize = $rowsize['size'];
                                    $prodprice = $rowsize['price'];

                                    $prodsql = $conn->prepare("SELECT * FROM products WHERE id = ?");
                                    $prodsql->bind_param("i", $prodid);
                                    $prodsql->execute();
                                    $prodres = $prodsql->get_result();
                                    if ($prodres->num_rows > 0){
                                    while($rowItem = $prodres->fetch_assoc()) {
                                        $prodname = $rowItem['prodname'];
                                        $prodimage = $rowItem['image'];
                    ?>
                        <tr>
                            <td><img src="../products/<?php echo $prodimage; ?>" alt=""></td>
                            <td><?php echo $prodname; ?></td>
                            <td><?php echo $prodqty; ?></td>
                            <td>₱<?php echo $prodprice; ?></td>
                        </tr>
                       <?php  }}}}}};?>
                    </tbody>
               </table>
            </div>
        </div>
    </div>

    <div class="create-part cp2" id="payment" style="display: <?php echo isset($paymentID) ? 'flex' : 'none'; ?>;">
        <div class="box-column bx2">
            <img src="../icons/close-bg.png" id="eks" alt="asdasdas" onclick="document.getElementById('payment').style.display = 'none';">
            <h1>Payment</h1>
            <div class="flex-box">
               <img src="../payments/<?php echo isset($payImg) ? $payImg : 'none'; ?>" class="bayad" alt="aa">
            </div>
        </div>
    </div>

    <div class="create-part cp2" id="address3" style="display: <?php echo isset($address1) ? 'flex' : 'none'; ?>;">
        <div class="box-column bx2">
            <img src="../icons/close-bg.png" id="eks" alt="aaasd" onclick="document.getElementById('address3').style.display = 'none';">
            <h1>Address</h1>
            <div class="flex-box">
                <label for="">Name:</label>
                <input type="text" value="<?php echo $add_name; ?>" readonly>
                <label for="">Contact Number:</label>
                <input type="text" value="<?php echo $cnumber; ?>" readonly>
                <label for="">Street/House Number:</label>
                <input type="text" value="<?php echo $street; ?>" readonly>
                <label for="">Barangay:</label>
                <input type="text" value="<?php echo $barangay; ?>" readonly>
                <label for="">City:</label>
                <input type="text" value="<?php echo $city; ?>" readonly>
                <label for="">Province:</label>
                <input type="text" value="<?php echo $province; ?>" readonly>
                <label for="">Region:</label>
                <input type="text" value="<?php echo $region; ?>" readonly>
                <label for="">Postal Code:</label>
                <input type="text" value="<?php echo $postal; ?>" readonly>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>