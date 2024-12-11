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
};
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM category WHERE id = ?");
    $stmt->bind_param("i", $id);

    $prod = $conn->prepare("SELECT category FROM category WHERE id = ?");
    $prod->bind_param("i", $id);
    $prod->execute();
    $prod->bind_result($categ);
    $prod->fetch();

    if ($stmt->execute()) {
        $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
        $detail = 'Deleted category: '.$categ;
        $archives->bind_param("ss", $username, $detail);
        $archives->execute();

        echo '<script language="javascript">alert("Category deleted successfully.");</script>';
        echo '<script language="javascript">window.location.href = "category.php";</script>';
    } else {
        echo "Error deleting record: " . $stmt->error;
    }

    
}else if (isset($_POST['update_id'])) {
    $id = $_POST['update_id'];
    $stmt = $conn->prepare("SELECT * FROM category WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $cateid = $row['id'];
        $catename = $row['category'];
    } else {
        echo "Category not found.";
    }
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/admin-emplist.css">
    <link rel="stylesheet" type="text/css" href="../css/admin-category.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    <div class="upper margin">
        <button id="addpd"><img src="../icons/create.png" alt="create" >Create</button>
    </div>
    <h1 class="content-title">Category List</h1>
    <section>
        <div class="table-bg">
            <table>
                <thead>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Creation Date</th>
                    <th>Action</th>
                </thead>
                <tbody>
                <?php $select = mysqli_query($conn,"SELECT * FROM category");
                    while($row=mysqli_fetch_assoc($select)){         
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td><?php echo $row['date_created']; ?></td>
                        <td>
                            <div class="option">
                                <!-- <a href="category.php?edit=" id="op1" class="op1"> Update</a> -->
                                <form action="category.php" method="post">
                                    <input type="hidden" name="update_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" id="op1" class="op1">Update</button>
                                </form>
                                <a href="category.php?delete=<?php echo $row['id']; ?>" class="op2" onclick="return confirm('Are you sure you want to delete this record?');"> Delete</a>
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
            <img src="../icons/close-bg.png" id="ekis" alt="asdasdas">
            <h1>Add Category</h1>
            <form action="addcategory.php" method="post" enctype="multipart/form-data">
            <div class="flex-box">
                <div class="left-box">
                    <input type="hidden" name="usern" value="<?php echo $username; ?>">
                    <input type="text" placeholder="Name" name="cate"  required>
                </div>
                <div class="right-box">
                </div>
            </div>
            <button type="submit" name="submit"><img src="../icons/create.png" alt="add">Add</button>
            </form>
        </div>
    </div>
    <div class="create-part" id="newcat" style="display: <?php echo isset($catename) ? 'flex' : 'none'; ?>;">
        <div class="box-column">
            <img src="../icons/close-bg.png" id="ekiss" alt="asdasdas">
            <h1>Update Category</h1>
            <form action="addcategory.php" method="post" enctype="multipart/form-data">
            <div class="flex-box">
                <div class="left-box">
                    <input type="hidden" name="usern" value="<?php echo $username; ?>">
                    <input type="hidden" name="id" value="<?php echo isset($id) ? $id : ''; ?>">
                    <input type="text" placeholder="Name" value="<?php echo isset($catename) ? $catename : ''; ?>" name="cate"  required>
                </div>
                <div class="right-box">
                </div>
            </div>
            <button type="submit" name="update"><img src="../icons/update.png" alt="add">Update</button>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var texts = document.getElementById("newprod");
            
            document.getElementById("addpd").onclick = function() {
                texts.style.display = "flex"; 
            }; 
            document.getElementById("ekis").onclick = function() {
                texts.style.display = "none";
            }; 
        });
        document.addEventListener("DOMContentLoaded", function() {
            var texts = document.getElementById("newcat");
            
            document.getElementById("ekiss").onclick = function() {
                texts.style.display = "none";
            }; 
        });
    </script>
</body>
</html>