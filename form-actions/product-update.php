<?php
include '../config.php';
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
    }
} else {
    echo "Hello, Guest! Please log in.";
    header("location: ../login.php");
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $lastname = $_POST["lastname"];
    $productid = $_POST["productid"];
    $size_id = $_POST["size_id"];
    $prodname = $_POST["prodname"];
    $desc = $_POST["desc"];
    $prodsize = $_POST["prodsize"];
    $cate = $_POST["cate"];
    $qty = $_POST["qty"];
    $price = $_POST["price"];
    $prodimg = $_FILES["prodimg"]["name"];
    $prodimg_tmp_name = $_FILES["prodimg"]["tmp_name"];
    $prodimg_folder = '../products/'.$prodimg;
    
    if (!preg_match("/^[1-9]\d*$/", $qty)) {
        echo '<script language="javascript">alert("Invalid quantity format! It should be a positive whole number.");</script>';
        exit;
    }

    if (move_uploaded_file($prodimg_tmp_name, $prodimg_folder)) {
        $stmt = $conn->prepare("UPDATE products SET `prodname` = ?, `description` = ?, `category` = ?, `image` = ? WHERE `id` = ?");
        $stmt->bind_param("ssssi", $prodname, $desc, $cate, $prodimg, $productid);
        if ($stmt->execute()) {
            $stmt2 = $conn->prepare("UPDATE product_sizes SET `size` = ?, `qty` = ?, `price` = ? WHERE `size_id` = ?");
            $stmt2->bind_param("sidi", $prodsize, $qty, $price, $size_id);
            if($stmt2->execute()){
                $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
                $detail = 'Updated product: '.$prodname.' in size '.$prodsize;
                $archives->bind_param("ss", $username, $detail);
                $archives->execute();

                echo '<script language="javascript">alert("Product successfully updated!");</script>';
                echo '<script language="javascript">window.location.href = "../admin-products.php";</script>';
            }else{
                echo "Error: " . $stmt2->error;
            }
        } else {
            echo "Error: " . $stmt->error;
        }
    }else{
        echo '<script language="javascript">alert("Error uploading the image file.");</script>';
    }
}

if(isset($_GET['edit'])){
    $id = $_GET['edit'];
    $size_id = (int)$id;
    $stmt = $conn->prepare("SELECT * FROM product_sizes WHERE size_id = ?");
    $stmt->bind_param("i", $size_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        $productid = $row['id'];
        $size = $row['size'];
        $qty = $row['qty'];
        $price = $row['price'];
        
        $stmt2 = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt2->bind_param("i", $productid);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        if($result2){
            $row2 = $result2->fetch_assoc();
            $prodname = $row2['prodname'];
            $category = $row2['category'];
            $desc = $row2['description'];
            $image = $row2['image'];
        }

    } else {
        echo "No results found.";
    }
    
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/admin-update-account.css">
    <link rel="stylesheet" type="text/css" href="../css/product-update.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    <div class="create-part" id="changethis">
        <div class="box-column">
            <h1>Update Product</h1>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="flex-box">
                <div class="left-box">
                    <input type="hidden" name="size_id" value="<?php echo $size_id; ?>">
                    <input type="hidden" name="productid" value="<?php echo $productid; ?>">
                    <input type="text" placeholder="Name" name="prodname" value="<?php echo $prodname ?>" required>
                    <input type="hidden" name="lastname" value="<?php echo $prodname ?>">
                    <input type="text" placeholder="Description" name="desc" value="<?php echo $desc ?>" >
                    <input type="text" placeholder="Size" name="prodsize" value="<?php echo $size ?>" required>
                </div>
                <div class="right-box">
                    <input type="text" placeholder="Category" name="cate" value="<?php echo $category ?>"  required>
                    <input type="text" placeholder="Quantity" name="qty" value="<?php echo $qty ?>" required>
                    <input type="text" placeholder="Price" name="price" value="<?php echo $price ?>" required>
                    <?php if (!empty($image)): ?>
                    <div>
                        <img src="../products/<?php echo $image; ?>" alt="Current Image" style="max-width: 100px; max-height: 100px;">
                        <p>Current Image: <?php echo htmlspecialchars($image); ?></p>
                    </div>
                    <?php endif; ?>
                    <input type="file" placeholder="Image" accept=".jpg,.jpeg,.png,.webp" name="prodimg" required>
                </div>
            </div>
            <button type="submit" name="submit"><img src="../icons/update.png" alt="add">Update</button>
            </form>
        </div>
    </div>
    
</body>
</html>