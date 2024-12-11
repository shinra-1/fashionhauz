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

$block1 =  mysqli_query($conn,"SELECT SUM(total_price) as total_price FROM `orderlist` WHERE mop !='Cash' AND `status` != 'Refunded' AND `status` != 'Pending' AND `status` != 'Rejected' AND `status` != 'Cancelled' AND WEEK(CURDATE())");
if ($rowB1=mysqli_fetch_assoc($block1)){
    if($rowB1['total_price']>0){
        $sales = $rowB1['total_price'];
    }else{
        $sales = 0;
    }
}

$block2 =  mysqli_query($conn,"SELECT COUNT(orderID) as orderID FROM `orderlist` WHERE `status` = 'Pending' AND DATE(date_created)");
if ($rowB2=mysqli_fetch_assoc($block2)){
    $stats = $rowB2['orderID'];
}

$block3 = mysqli_query($conn, "SELECT COUNT(orderID) as orderID FROM `orderlist` WHERE `status` = 'Shipped' AND DATE(`date_created`) = CURDATE()");
if ($rowB3 = mysqli_fetch_assoc($block3)) {
    $ship = $rowB3['orderID'];
}

$block6 =  mysqli_query($conn,"SELECT SUM(total_price) as total_price FROM `orderlist` WHERE mop ='Cash' AND `status` != 'Refunded' AND MONTH(CURDATE())");
if ($rowB6=mysqli_fetch_assoc($block6)){
    $pos = $rowB6['total_price'];
    if($pos <=0){
        $pos = 0;
    }
}

$block7 =  mysqli_query($conn,"SELECT SUM(total_price) as total_price FROM `orderlist` WHERE `status` != 'Refunded' AND `status` != 'Pending' AND `status` != 'Rejected' AND `status` != 'Cancelled' AND MONTH(CURDATE())");
if ($rowB7=mysqli_fetch_assoc($block7)){
    $overall = $rowB7['total_price'];
    if ($overall <=0){
        $overall = 0;
    }
}

$block8 =  mysqli_query($conn,"SELECT COUNT(orderID) as orderID FROM `orderlist` WHERE `status` = 'Completed' AND MONTH(CURDATE())");
if ($rowB8=mysqli_fetch_assoc($block8)){
    $comp = $rowB8['orderID'];
}


function critical($conn) {
    $critical = [];
    $block4 =  "SELECT ps.*, p.prodname 
        FROM product_sizes ps
        JOIN products p ON ps.id = p.id
        WHERE ps.qty < 6";
    $block4res = $conn->query($block4);
    
    if ($block4res && $block4res->num_rows > 0) {
        while ($rowB4 = $block4res->fetch_assoc()) {
            $critical[] = $rowB4; // Add each user to the array
        }
    }
    
    return $critical;
}
$criticals = critical($conn);

function bests($conn) {
    $bestseller = [];
    $orderQuery = "SELECT orderID 
                   FROM orderlist 
                   WHERE status NOT IN ('pending', 'refunded', 'cancelled')";
    $orderResult = $conn->query($orderQuery);
    $validOrderIDs = [];
    if ($orderResult && $orderResult->num_rows > 0) {
        while ($row = $orderResult->fetch_assoc()) {
            $validOrderIDs[] = $row['orderID'];
        }
    }
    // If there are valid orderIDs, proceed with the second query
    if (!empty($validOrderIDs)) {
        $orderIDList = implode(',', $validOrderIDs); // Convert array to comma-separated string
        $block9 = "SELECT p.prodname, SUM(od.qty) AS order_count
                   FROM products p
                   LEFT JOIN order_details od ON p.id = od.prodID
                   WHERE od.orderID IN ($orderIDList)
                   GROUP BY p.id
                   ORDER BY order_count DESC";
        $block9res = $conn->query($block9);
        if ($block9res && $block9res->num_rows > 0) {
            while ($rowB9 = $block9res->fetch_assoc()) {
                $bestseller[] = $rowB9;
            }
        }
    }
    return $bestseller;
}
$bestsellers = bests($conn);

$monthly_sales_query = "
    SELECT MONTH(date_created) AS month, SUM(total_price) AS total_sales 
    FROM `orderlist` 
    WHERE `status` != 'Refunded'
    AND `status` != 'Cancelled'
    AND `status` != 'Pending'
    AND `mop` != 'Cash' 
    GROUP BY YEAR(date_created), MONTH(date_created)
    ORDER BY MONTH(date_created)";
    
$monthly_sales_result = $conn->query($monthly_sales_query);

$monthly_sales_data = [];
while ($row = $monthly_sales_result->fetch_assoc()) {
    $monthly_sales_data[(int)$row['month']] = $row['total_sales'];
}
$monthly_totals = array_fill(1, 12, 0);
foreach ($monthly_sales_data as $month => $total) {
    $monthly_totals[$month] = $total;
}

if(isset($_POST['change'])){
    $change = 'true';
}else if(isset($_POST['submitthis'])){
    $bannerimg = $_FILES["bannerpic"]["name"];
    $bannerimg_tmp_name = $_FILES["bannerpic"]["tmp_name"];
    $bannerimg_folder = 'images/'.$bannerimg;

    if (move_uploaded_file($bannerimg_tmp_name, $bannerimg_folder)) {
        $stmt = $conn->prepare("UPDATE banner SET `banner` = ? WHERE `id` = 1");
        $stmt->bind_param("s", $bannerimg);
        
        $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
        $detail = 'New Banner: '.$bannerimg;
        $archives->bind_param("ss", $username, $detail);
        $archives->execute();
        $archives->close();

        if ($stmt->execute()) {
            echo '<script language="javascript">alert("Banner successfully updated!");</script>';
            echo '<script language="javascript">window.location.href = "admin-db.php";</script>';
            
        } else {
            echo "Error: " . $stmt->error;
        }
    }else{
        echo '<script language="javascript">alert("Error uploading the image file.");</script>';
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/db.css">
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    <form action="admin-db.php" class="banner" method="post">
        <input type="hidden" name="change" value="true">
        <button type="submit" name="submitchange"><img src="icons/edit2.png" alt="">Change Banner Picture</button>
    </form>
    <form action="logout.php" class="logout" method="post">
        <input type="hidden" name="usern" value="<?php echo $username; ?>">
        <button type="submit" name="logout"><img src="icons/logout.png" alt="">Logout</button>
    </form>
    <section>
        <div class="container">
            <div class="block1">
                <h3 class="block-title">Total Sales</h3>
                <h1 class="block-content">₱<?php echo $sales; ?></h1>
                <h5>Weekly</h5>
            </div>
            <div class="block2">
                <h3 class="block-title">Pending Orders</h3>
                <h1 class="block-content"><?php echo $stats; ?></h1>
            </div>
            <div class="block3">
                <h3 class="block-title">Shipped Orders</h3>
                <h1 class="block-content"><?php echo $ship; ?></h1>
                <h5>Today</h5>
            </div>
            <div class="block4">
                <div class="scrollbar">
                    <h3 class="block-title space">Critical Inventories</h3>
                    <ul class="block-content">
                        <?php
                            if (!empty($criticals)){
                                foreach ($criticals as $item) {
                                    if($item['qty'] <= 5){
                                        echo "<li class='space'>".htmlspecialchars($item['prodname'])." - ".$item['size']."</li>";
                                    }
                                }
                            }else {
                                echo "<li class='space'>No critical level inventory item</li>";
                            }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="block6">
                <h3 class="block-title">Total POS Sales</h3>
                <h1 class="block-content">₱<?php echo $pos; ?></h1>
                <h5>Month</h5>
            </div>
            <div class="block7">
                <h3 class="block-title">Overall Sales</h3>
                <h1 class="block-content">₱<?php echo $overall; ?></h1>
                <h5>Monthly POS and Online </h5>
            </div>
            <div class="block8">
                <h3 class="block-title">Orders Completed</h3>
                <h1 class="block-content"><?php echo $comp; ?></h1>
                <h5>Month</h5>
            </div>
            <div class="block9">
                <div class="scrollbar">
                    <h3 class="block-title space">Best Sellers</h3>
                    <ul class="block-content">
                        <?php
                            if (!empty($bestsellers)){
                                foreach ($bestsellers as $bests) {
                                    if($bests['order_count'] > 0){
                                        echo "<li class='space'>".htmlspecialchars($bests['prodname'])." - ".$bests['order_count']."</li>";
                                    }
                                }
                            }else {
                                echo "<li class='space'>No best sellers</li>";
                            }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="block5">
                <h1>Total Online Monthly Sales</h1>
                <canvas id="myChart" style="width:100%;max-width:100%;height:300px;"></canvas>
            </div>
        </div>
    </section>
    
    <div class="create-part cp2" id="changethis" style="display: <?php echo isset($_POST['change']) ? 'flex' : 'none'; ?>;">
        <div class="box-column">
            <img src="icons/close-bg.png" id="eks" alt="asdasdas" onclick="document.getElementById('changethis').style.display = 'none';">
            <h1>Update Banner Picture</h1>
            <form action="admin-db.php" method="post" enctype="multipart/form-data">
                <div class="flex-box">
                    <input type="file" name="bannerpic" required>
                </div>
            <button type="submit" name="submitthis"><img src="icons/edit2.png" alt="add">Update</button>
            </form>
        </div>
    </div>
<script>
    const xValues = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    const yValues = <?php echo json_encode(array_values($monthly_totals)); ?>;

    new Chart("myChart", {
    type: "line",
    data: {
        labels: xValues,
        datasets: [{
        fill: false,
        lineTension: 0,
        backgroundColor: "rgba(240, 131, 157, 1)",
        borderColor: "rgba(240, 131, 157, 0.4)",
        data: yValues
        }]
    },
    options: {
        legend: {display: false},
        scales: {
        yAxes: [{ticks: {min: 0, max: Math.max(...yValues) + 1000}}],
        }
    }
    });
</script>
</body>
</html>