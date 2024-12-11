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
        header("location: ../login.php");
    }
} else {
    header("location: ../login.php");
}
$id = 1;
$stmt = $conn->prepare("SELECT * FROM users WHERE userID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $row = $result->fetch_assoc();
    $fname = $row['fname'];
    $mname = $row['mname'];
    $lname = $row['lname'];
    $age = $row['age'];
    $bday = $row['bday'];
    $gender = $row['gender'];
    $cnumber = $row['cnumber'];
    $email = $row['email'];
    $uname = $row['uname'];
    $pword = $row['pword'];
    $category = $row['category'];
    
} else {
    echo "No results found.";
}

// email structure verificator
function isValidEmail($conn, $email,$username) {
    $sql = "SELECT email FROM users WHERE email = ?";
    if ($statement = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($statement, "s", $param_email);
        $param_email = trim($email); 
        if (mysqli_stmt_execute($statement)) { 
            mysqli_stmt_store_result($statement);
            if (mysqli_stmt_num_rows($statement) == 0) { // Check if email doesn't exist in DB
                // Validate email format and domain
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $allowed_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'bing.com'];
                    $email_domain = substr(strrchr($email, "@"), 1); // Extract domain from email
                    
                    if (in_array($email_domain, $allowed_domains)) {
                        return true; // Valid email
                    } else {
                        echo '<script language="javascript">alert("Invalid email domain! Allowed domains: gmail.com, yahoo.com, outlook.com, bing.com.");</script>';
                        echo '<script language="javascript">window.location = "admin-account-update.php";</script>';
                    }
                } else {
                    echo '<script language="javascript">alert("Invalid email format!");</script>';
                    echo '<script language="javascript">window.location = "admin-account-update.php";</script>';
                }
            } else {
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
                            echo '<script language="javascript">window.location = "admin-account-update.php";</script>';
                            exit;
                        }
                    } else {
                        echo '<script language="javascript">alert("Invalid email format!");</script>';
                        echo '<script language="javascript">window.location = "admin-account-update.php";</script>';
                        exit;
                    }
                } else {
                    echo '<script language="javascript">alert("Email already exists!");</script>';
                    echo '<script language="javascript">window.location = "admin-account-update.php";</script>';
                    exit;
                }
            }
        } else {
            echo "Something went wrong with email verification. Please try again.";
        }
        mysqli_stmt_close($statement);
    }
    return false;
}


if($_SERVER["REQUEST_METHOD"] == "POST"){
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
    $passw = $_POST["passw"];
    $hashedPassword = password_hash($passw, PASSWORD_DEFAULT);

    if (!preg_match("/^09\d{9}$/", $cnumber)) {
        echo '<script language="javascript">alert("Invalid contact number! It should start with 09 and be 11 digits long.");</script>';
        echo '<script language="javascript">window.location.href = "admin-account-update.php";</script>';
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
                            $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
                            $detail = $username.' updated their account information';
                            $archives->bind_param("ss", $usern, $detail);
                            $archives->execute();

                            $stmt = $conn->prepare("UPDATE users SET fname = ?, mname = ?, lname = ?, age = ?, bday = ?, gender = ?, 
                            cnumber = ?, email = ?, uname = ?, pword = ? WHERE userID = ?");
                            $stmt->bind_param("sssisssssss", $fname, $mname, $lname, $age, $bday, $gender, $cnumber, $email, $usern, $passw, $user_id);
                            if ($stmt->execute()) {
                                echo '<script language="javascript">alert("Account successfully updated!");</script>';
                                echo '<script language="javascript">window.location.href = "admin-account-update.php";</script>';
                                
                            } else {
                                echo "Error: " . $stmt->error;
                            }
                        }else{
                            echo '<script language="javascript">alert("Username already exists!");</script>';
                        }
                    }else{
                        $stmt = $conn->prepare("UPDATE users SET fname = ?, mname = ?, lname = ?, age = ?, bday = ?, gender = ?, 
                        cnumber = ?, email = ?, uname = ?, pword = ? WHERE userID = ?");
                        $stmt->bind_param("sssisssssss", $fname, $mname, $lname, $age, $bday, $gender, $cnumber, $email, $usern, $hashedPassword, $id);
                        if ($stmt->execute()) {
                            $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
                            $detail = $username.' updated their account information';
                            $archives->bind_param("ss", $usern, $detail);
                            $archives->execute();
                            
                            echo '<script language="javascript">alert("Account successfully updated!");</script>';
                            echo '<script language="javascript">window.location.href = "../admin-db.php";</script>';
                            
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
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/admin-update-account.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    <div class="create-part" id="changethis">
        <div class="box-column">
            <h1>Update Account</h1>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="flex-box">
                <div class="left-box">
                    <input type="text" placeholder="First Name" name="fname" value="<?php echo $fname; ?>" required>
                    <input type="text" placeholder="Middle Name" name="mname" value="<?php echo $mname; ?>" >
                    <input type="text" placeholder="Last Name" name="lname" value="<?php echo $lname; ?>" required>
                    <input type="number" placeholder="Age" name="age" value="<?php echo $age; ?>" required>
                    <input type="date" id="bday" placeholder="Birthday" name="bday" value="<?php echo $bday; ?>"  required>
                </div>
                <div class="right-box">
                <div class="sex-part">
                    <label for="sex">Sex: </label>
                    <label for="male">Male</label>
                    <input type="radio" id="male" name="sex" value="male"<?php if ($gender === 'male') echo 'checked'; ?>>
                    <label for="female">Female</label>
                    <input type="radio" id="female" name="sex" value="female"<?php if ($gender === 'female') echo 'checked'; ?>>
                    <label for="other">Other</label>
                    <input type="radio" id="other" name="sex" value="other" <?php if ($gender === 'other') echo 'checked'; ?>>
                </div>
                <input type="text" placeholder="Contact" name="cnumber" oninput="validateInput(event)" value="<?php echo htmlspecialchars($cnumber); ?>" required>
                <input type="email" placeholder="Email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <input type="text" placeholder="Username" name="userr" value="<?php echo htmlspecialchars($uname); ?>" required>
                <input type="password" placeholder="Old Password" name="oldpw" required>
                <input type="password" placeholder="New Password" name="passw" required>
                </div>
            </div>
            <button type="submit" name="submit"><img src="../icons/create.png" alt="add">Add</button>
            </form>
        </div>
    </div>
    
</body>
</html>