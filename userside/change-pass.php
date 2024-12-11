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

$sqluser = $conn->prepare("SELECT * FROM users WHERE userID = ?");
$sqluser->bind_param("i", $userID);
$sqluser->execute();
$resultuser = $sqluser->get_result();
if ($resultuser) {
    $rowUser = $resultuser->fetch_assoc();
    $pword = $rowUser['pword'];
    $category = $rowUser['category'];
    
} else {
    echo "No results found.";
}


if(isset($_POST['update-acc'])){
    $oldpw = $_POST["oldpw"];
    $passw = $_POST["passw"];
    $hashedPassword = password_hash($passw, PASSWORD_DEFAULT);

    if(password_verify($oldpw,$pword)){
        $stmt = $conn->prepare("UPDATE users SET pword = ? WHERE userID = ?");
        $stmt->bind_param("si", $hashedPassword, $userID);
        if ($stmt->execute()) {
            echo '<script language="javascript">alert("Password successfully updated!");</script>';
            echo '<script language="javascript">window.location.href = "change-pass.php";</script>';
            
        } else {
            echo "Error: " . $stmt->error;
        }
    }else{
        echo '<script language="javascript">alert("Wrong old password!");</script>';
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/account.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
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
                    <a href="history.php">Purchase History</a>
                    <a href="change-pass.php" class="active">Change Password</a>
                </div>
                <div class="navbottom">
                    <form action="../logout.php" class="logout" method="post">
                        <input type="hidden" name="usern" value="<?php echo $username; ?>">
                        <button type="submit" name="logout">Logout</button>
                    </form>
                </div>
            </div>
            <div class="container-box">
                <form action="change-pass.php" method="post">
                    <div class="form-top">
                        <div class="form-left">
                            <div class="inputbox">
                                <label for="oldpw"> Old Password :</label>
                                <input type="password" name="oldpw" value="" required>
                            </div>
                        </div>
                        <div class="form-right">
                            <div class="inputbox">
                                <label for="passw">New Password :</label>
                                <input type="password" name="passw" value="" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-bot">
                        <button type="submit" name="update-acc"><img src="../icons/update.png" alt="">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>