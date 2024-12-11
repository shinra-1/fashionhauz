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


if(isset($_GET['id'])){
    $prodID = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("s", $prodID);
    $stmt->execute();
    $result = $stmt->get_result();
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/item.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
    <script>
        function updatePrice() {
            const quantity = parseInt(document.querySelector('input[name="qty"]').value) || 1;
            const selectedSize = document.querySelector('input[name="size"]:checked');
            const price = selectedSize ? sizePrices[selectedSize.value] : 0;

            document.querySelector('.price').textContent = '₱' + (price * quantity).toFixed(2);
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Listen for changes on quantity input to update price accordingly
            document.querySelector('input[name="qty"]').addEventListener('input', updatePrice);
        });
    </script>
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <div class="container">
            <?php
                // Display the items for the selected category
                if ($result->num_rows > 0) {
                    if($row = $result->fetch_assoc()) {
                        $stmt2 = $conn->prepare("SELECT * FROM product_sizes WHERE id = ?");
                        $stmt2->bind_param("s", $prodID);
                        $stmt2->execute();
                            
            ?>
           <div class="left-part">
                <img src="../products/<?php echo $row['image']; ?>" alt="">
           </div>
           <div class="right-part">
                <form action="checkout.php" method="post">
                    <h1><?php echo $row['prodname']; ?></h1>
                    <input type="hidden"  name="prodname" value="<?php echo $row['prodname']; ?>">
                    <input type="hidden"  name="prodID" value="<?php echo $row['id']; ?>">
                    <span></span>
                    <p><?php echo $row['description']; ?></p>
                    <div class="box-categ">
                        <h2>Category :</h2>
                        <h2 class="categ"><?php echo $row['category']; ?></h2>
                    </div>
                    <div class="box-size">
                        <h2>Sizes :</h2>
                        <?php
                        $result2 = $stmt2->get_result();
                        if ($result2->num_rows > 0) {
                            $option = 0;
                            $sizePricesJS = [];
                            while($row2 = $result2->fetch_assoc()) {
                                $option += 1;
                                $size = $row2['size'];
                                $price = $row2['price'];
                                $availableqty = $row2['qty'];

                                $sizePricesJS[$size] = $price;
                                if($availableqty >0){
                                    echo '<div class="box-option">';
                                    echo '<input type="radio" id="'.$option.'" name="size" class="radio-button" onclick="updatePrice()" value="'.$size.'">';
                                    echo '<label for="'.$option.'" class="radio-label">'.$size.'</label>';
                                    echo '</div>';
                                }
                            }
                            echo "<script>let sizePrices = " . json_encode($sizePricesJS) . ";</script>";
                        } else {
                            echo '<div class="box-option">';
                            echo '<h2>There is no available size currently.</h2>';
                        }
                        ?>
                    </div>
                    <div class="box-qty">
                        <h2>Qty :</h2>
                        <input type="number" name="qty" min="1" value="1" oninput="updatePrice()">
                    </div>
                    <div class="box-price">
                        <h2>Price :</h2>
                        <h2 class="price">₱0.00</h2>
                    </div>
                    <div class="box-button">
                        <button type="submit" name="addcart"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M351.9 329.506H206.81l-3.072-12.56H368.16l26.63-116.019-217.23-26.04-9.952-58.09h-50.4v21.946h31.894l35.233 191.246a32.927 32.927 0 1 0 36.363 21.462h100.244a32.825 32.825 0 1 0 30.957-21.945zM181.427 197.45l186.51 22.358-17.258 75.195H198.917z" data-name="Shopping Cart"/></svg>
                        Add to Cart</button>
                        <input type="hidden" name="cart-out" value="false">
                        <button type="submit" name="buynow"><svg class="svg" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
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
                        </svg>
                        Checkout</button>
                    </div>
                </form>
                <?php }
                    } else {
                        echo "<p>No items found.</p>";
                    }
                    $stmt->close(); ?>
           </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>