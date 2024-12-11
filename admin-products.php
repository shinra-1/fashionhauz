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

if(isset($_POST['size_id'])){// showing list of sizes
    $id = $_POST['size_id'];
    $stmt = $conn->prepare("SELECT * FROM product_sizes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $sizeresult = $stmt->get_result();
    
};

if(isset($_GET['update'])){// showing list of sizes
    $id = $_GET['update'];
    $stmt = $conn->prepare("SELECT * FROM product_sizes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $sizeresult = $stmt->get_result();
};

if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo '<script language="javascript">alert("Product deleted successfully.");</script>';
        echo '<script language="javascript">window.location.href = "admin-products.php";</script>';
        
    } else {
        echo "Error deleting record: " . $stmt->error;
    }
    $stmt->exit();
    

    $prod = $conn->prepare("SELECT prodname FROM products WHERE id = ?");
    $prod->bind_param("i", $id);
    $prod->execute();
    $prod->bind_result($prodname);
    $prod->fetch();
    $prod->exit();
    


    $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
    $detail = 'Deleted product: '.$prodname;
    $archives->bind_param("ss", $username, $detail);
    $archives->execute();
    $archives->exit();
    
    

    
};


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/admin-emplist.css">
    <link rel="stylesheet" type="text/css" href="css/admin-products.css">
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    <div class="upper margin">
        <button id="addpd" onclick="document.getElementById('newprod').style.display = 'flex';"><img src="icons/create.png" alt="create" >Create</button>
        <a href="form-actions/category.php"><button id="categori"><img src="icons/update.png" alt="create" >Category</button></a>
    </div>
    <h1 class="content-title">Product List</h1>
    <section>
        <div class="table-bg">
            <table>
                <thead>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Size</th>
                    <th>Category</th>
                    <th>Creation Date</th>
                    <th>Action</th>
                </thead>
                <tbody>
                <?php $select = mysqli_query($conn,"SELECT * FROM products");
                    while($row=mysqli_fetch_assoc($select)){         
                ?>
                    <tr>
                        <td><img src="products/<?php echo $row['image']; ?>" alt="aaa" class="prodimg"></td>
                        <td><?php echo $row['prodname']; ?></td>
                        <td><?php echo $row['description']; ?></td>
                        <td><div class="option">
                                <form action="admin-products.php" method="post">
                                    <input type="hidden" name="size_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" id="op1" class="op1">View</button>
                                </form>
                            </div></td>
                        <td><?php echo $row['category']; ?></td>
                        <td><?php echo $row['date_created']; ?></td>
                        <td>
                            <div class="option">
                                <a href="admin-products.php?update=<?php echo $row['id']; ?>" class="op1"> Update</a>
                                <a href="admin-products.php?delete=<?php echo $row['id']; ?>" class="op2" onclick="return confirm('Are you sure you want to delete this record?');"> Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php   };  ?>
                </tbody>
            </table>
        </div>
    </section>
    <div class="create-part" id="newprod">
        <div class="box-column">
            <img src="icons/close-bg.png" id="ekis" alt="asdasdas" onclick="document.getElementById('newprod').style.display = 'none';">
            <h1>Add Product</h1>
            <form action="form-actions/addproduct.php" method="post" enctype="multipart/form-data">
            <div class="flex-box">
                <div class="left-box">
                    <input type="hidden" name="usern" value="<?php echo $username; ?>">
                    <input type="text" placeholder="Name" name="prodname" required>
                    <input type="text" placeholder="Description" name="desc" >
                </div>
                <div class="right-box">
                    <select name="cate" required>
                        <option value="" disabled selected>Select a category</option>
                        <?php 
                        $categories = [];
                        $query = "SELECT category FROM category";
                        $result = $conn->query($query);
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $categories[] = $row;
                            }
                        }
                        
                        foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category']; ?>"><?php echo $category['category']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="file" placeholder="Image" accept=".jpg,.jpeg,.png,.webp" name="prodimg" required>
                </div>
            </div>
            <button type="submit" name="submit"><img src="icons/create.png" alt="add">Add</button>
            </form>
        </div>
    </div>
    
    <div class="size-part" id="newitem" style="display: <?php echo isset($_POST['size_id']) || isset($_GET['update']) ? 'flex' : 'none'; ?>;">
        <div class="size-column">
            <img src="icons/close-bg.png" id="close-item" alt="qwer" onclick="document.getElementById('newitem').style.display = 'none';">
            <h1>Sizes</h1>
            <div class="size-create">
                <a href="admin-products-size.php?size=<?php echo $id; ?>" id="addsize"><img src="icons/create.png" alt="create" >Add</a>
            </div>
            <table>
                <thead>
                    <th>Size</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <?php
                        if(isset($_POST['size_id'])){
                            echo '<th>Date Created</th>';
                        }else if(isset($_GET['update'])){
                            echo '<th>Action</th>';
                        }else{
                            echo 'error in getting post or get';
                        }
                    ?>
                </thead>
                <tbody>
                    <?php
                        while ($row = $sizeresult->fetch_assoc()) {
                            $size = $row['size'];
                            $qty = $row['qty'];
                            $price = $row['price'];
                            $date_created = $row['date_created'];
                    ?>  
                    <tr>
                        <td><?php echo $size; ?></td>
                        <td><?php echo $qty; ?></td>
                        <td>₱<?php echo $price; ?></td>
                        <td><?php
                            if(isset($_POST['size_id'])){
                                echo $date_created;
                            }else if(isset($_GET['update'])){
                                echo '<div class="option">';
                                echo '<a href="form-actions/product-update.php?edit='.$row['size_id'].'" class="op1">Select</a>';
                                echo '</div>';
                            }else{
                                echo 'error in geting id';
                            }
                        
                             ?></td>
                    </tr>
                    <?php  };?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>