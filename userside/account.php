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
    $fname = $rowUser['fname'];
    $mname = $rowUser['mname'];
    $lname = $rowUser['lname'];
    $age = $rowUser['age'];
    $bday = $rowUser['bday'];
    $gender = $rowUser['gender'];
    $cnumber = $rowUser['cnumber'];
    $email = $rowUser['email'];
    $uname = $rowUser['uname'];
    $pword = $rowUser['pword'];
    $category = $rowUser['category'];
    
} else {
    echo "No results found.";
}

function isValidEmail($conn,$email,$username) {
    $sql = "SELECT email FROM users WHERE email = ?";
    if($statement = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($statement, "s", $param_email);
        $param_email = trim($_POST["email"]); // set parameter
        if(mysqli_stmt_execute($statement)){ // execute
            mysqli_stmt_store_result($statement);
            if(mysqli_stmt_num_rows($statement) == 0){
                // Validate email format and domain
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $allowed_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'bing.com'];
                    $email_domain = substr(strrchr($email, "@"), 1); // Extract domain from email
                    
                    if (in_array($email_domain, $allowed_domains)) {
                        return true; // Valid email
                    } else {
                        echo '<script language="javascript">alert("Invalid email domain! Allowed domains: gmail.com, yahoo.com, outlook.com, bing.com.");</script>';
                        echo '<script language="javascript">window.location = "account.php";</script>';
                        exit;
                    }
                } else {
                    echo '<script language="javascript">alert("Invalid email format!");</script>';
                    echo '<script language="javascript">window.location = "account.php";</script>';
                    exit;
                }
            }else{
                $search = "SELECT email FROM users WHERE email = ? AND uname = ?";
                $search = $conn->prepare($search);
                $search->bind_param("ss", $email, $username);
                $search->execute();
                $searchres = $search->get_result();
                if ($searchres->num_rows > 0) {
                    // Validate email format and domain
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $allowed_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'bing.com'];
                        $email_domain = substr(strrchr($email, "@"), 1); // Extract domain from email
                        
                        if (in_array($email_domain, $allowed_domains)) {
                            return true; // Valid email
                        } else {
                            echo '<script language="javascript">alert("Invalid email domain! Allowed domains: gmail.com, yahoo.com, outlook.com, bing.com.");</script>';
                            echo '<script language="javascript">window.location = "account.php";</script>';
                            exit;
                        }
                    } else {
                        echo '<script language="javascript">alert("Invalid email format!");</script>';
                        echo '<script language="javascript">window.location = "account.php";</script>';
                        exit;
                    }
                } else {
                    echo '<script language="javascript">alert("Email already exists!");</script>';
                }
            }
        }else{
            echo "Something went wrong with email verification. Please try again.";
        }
        mysqli_stmt_close($statement);
    }
}


if(isset($_POST['update-acc'])){
    $fname = trim($_POST["fname"]);
    $mname = trim($_POST["mname"]);
    $lname = trim($_POST["lname"]);
    $age = $_POST["age"];
    $bday = $_POST["bday"];
    $gender = $_POST["sex"];
    $cnumber = $_POST["cnumber"];
    $email = $_POST["email"];
    $usern = $_POST["userr"];
    $oldpw = $_POST["oldpw"];

    if (!is_numeric($age)) {  
        echo '<script language="javascript">alert("Invalid input. Please enter valid numbers.");</script>';
        echo '<script language="javascript">window.location.href = "account.php";</script>';
        exit;
    }
    if (!preg_match("/^09\d{9}$/", $cnumber)) {
        echo '<script language="javascript">alert("Invalid contact number! It should start with 09 and be 11 digits long.");</script>';
        echo '<script language="javascript">window.location.href = "account.php";</script>';
        exit;
    }

    if(password_verify($oldpw,$pword)){
        if(isValidEmail($conn,$email,$username)){
            $findUsername = "SELECT userID FROM users WHERE uname = ?";
            if($statement = mysqli_prepare($conn, $findUsername)){
                mysqli_stmt_bind_param($statement, "s", $param_username);
                $param_username = trim($_POST["userr"]); // set parameter
                if(mysqli_stmt_execute($statement)){ // execute
                    mysqli_stmt_store_result($statement);
                    if(mysqli_stmt_num_rows($statement) == 1){
                        if($usern === $username){
                            $stmt = $conn->prepare("UPDATE users SET fname = ?, mname = ?, lname = ?, age = ?, bday = ?, gender = ?, 
                            cnumber = ?, email = ?, uname = ? WHERE userID = ?");
                            $stmt->bind_param("sssissssss", $fname, $mname, $lname, $age, $bday, $gender, $cnumber, $email, $usern, $userID);
                            if ($stmt->execute()) {
                                echo '<script language="javascript">alert("Account successfully updated!");</script>';
                                echo '<script language="javascript">window.location.href = "account.php";</script>';
                                
                            } else {
                                echo "Error: " . $stmt->error;
                            }
                        }else{
                            echo '<script language="javascript">alert("Username already exists!");</script>';
                            echo '<script language="javascript">window.location.href = "account.php";</script>';
                            exit;
                        }
                    }else{
                        $stmt = $conn->prepare("UPDATE users SET fname = ?, mname = ?, lname = ?, age = ?, bday = ?, gender = ?, 
                        cnumber = ?, email = ?, uname = ? WHERE userID = ?");
                        $stmt->bind_param("sssissssss", $fname, $mname, $lname, $age, $bday, $gender, $cnumber, $email, $usern, $userID);
                        if ($stmt->execute()) {
                            echo '<script language="javascript">alert("Account successfully updated!");</script>';
                            echo '<script language="javascript">window.location.href = "account.php";</script>';
                            
                        } else {
                            echo "Error: " . $stmt->error;
                        }
                    }
                } else{
                    echo "Oops! Something went wrong with finding username. Please try again later.";
                }
                mysqli_stmt_close($statement);
            }
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
                    <a href="account.php" class="active">Account Info</a>
                    <a href="addresses.php">Addresses</a>
                    <a href="history.php">Purchase History</a>
                    <a href="change-pass.php" >Change Password</a>
                </div>
                <div class="navbottom">
                    <form action="../logout.php" class="logout" method="post">
                        <input type="hidden" name="usern" value="<?php echo $username; ?>">
                        <button type="submit" name="logout">Logout</button>
                    </form>
                </div>
            </div>
            <div class="container-box">
                <form action="account.php" method="post">
                    <div class="form-top">
                        <div class="form-left">
                            <div class="inputbox">
                                <label for="fname">First Name :</label>
                                <input type="text" name="fname" value="<?php echo $fname; ?>" class="a1" required>
                            </div>
                            <div class="inputbox">
                                <label for="mname">Middle Name :</label>
                                <input type="text" name="mname" value="<?php echo $mname; ?>">
                            </div>
                            <div class="inputbox">
                                <label for="lname">Last Name :</label>
                                <input type="text" name="lname" value="<?php echo $lname; ?>" class="a1" required>
                            </div>
                            <div class="inputbox">
                                <label for="age">Age :</label>
                                <input type="number" name="age" value="<?php echo $age; ?>" class="a2" required>
                            </div>
                            <div class="inputbox">
                                <label for="bday">Birthday :</label>
                                <input type="date" name="bday" value="<?php echo $bday; ?>"class="a3" required>
                            </div>
                        </div>
                        <div class="form-right">
                            <div class="inputbox gender">
                                <label for="fname">Gender :</label>
                                <div class="parts">
                                    <input type="radio" id="male" name="sex" value="male" <?php if ($gender === 'male') echo 'checked'; ?>>
                                    <label for="male">Male</label>
                                    <input type="radio" id="female" name="sex" value="female" <?php if ($gender === 'female') echo 'checked'; ?>>
                                    <label for="female">Female</label>
                                    <input type="radio" id="other" name="sex" value="other" <?php if ($gender === 'other') echo 'checked'; ?>> 
                                    <label for="other">Other</label>
                                </div>
                            </div>
                            <div class="inputbox">
                                <label for="cnumber">Contact :</label>
                                <input type="text" name="cnumber" value="<?php echo $cnumber; ?>" class="a4" required>
                            </div>
                            <div class="inputbox">
                                <label for="email">Email :</label>
                                <input type="email" name="email" value="<?php echo $email; ?>" class="a5" required>
                            </div>
                            <div class="inputbox">
                                <label for="uname">Username :</label>
                                <input type="text" name="userr" value="<?php echo $uname; ?>" class="a7" required>
                            </div>
                            <div class="inputbox">
                                <label for="oldpw">Password :</label>
                                <input type="password" name="oldpw" value="" class="a6" required>
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