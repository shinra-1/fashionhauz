<?php

include 'config.php';
function isValidUser($conn, $username) {
    $stmt = $conn->prepare("SELECT uname FROM users WHERE uname = ?");
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
    }
} else {
    echo "Hello, Guest! Please log in.";
    header("location: index.php");//palitan to pag may login page na
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
    <section>
        <div class="container">
            <div class="block1">
                <h3 class="block-title">Total Sales</h3>
                <h1 class="block-content">₱25,000</h1>
                <h5>Weekly</h5>
            </div>
            <div class="block2">
                <h3 class="block-title">Pending Orders</h3>
                <h1 class="block-content">21</h1>
            </div>
            <div class="block3">
                <h3 class="block-title">Shipped Orders</h3>
                <h1 class="block-content">21</h1>
                <h5>Today</h5>
            </div>
            <div class="block4">
                <div class="scrollbar">
                    <h3 class="block-title space">Critical Inventories</h3>
                    <ul class="block-content">
                        <li class="space">Bracelets</li>
                        <li class="space">Necklaces</li>
                        <li class="space">Necklaces</li>
                        <li class="space">Necklaces</li>
                        <li class="space">Necklaces</li>
                        <li class="space">Necklaces</li>
                        <li class="space">Necklaces</li>
                    </ul>
                </div>
            </div>
            <div class="block5">
                <h1>Total Monthly Sales</h1>
                <canvas id="myChart" style="width:100%;max-width:100%;height:300px;"></canvas>
            </div>
        </div>
    </section>
    <?php include 'admin-footer.php'; ?>
<script>
    const xValues = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    const yValues = [1000,2000,3000,4000,5000,10000,20000,30000,40000,50000,10000,20000];

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
        yAxes: [{ticks: {min: 0, max:50000}}],
        }
    }
    });
</script>
</body>
</html>