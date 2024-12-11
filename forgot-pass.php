<?php 
include "config.php";
session_start();
if (isset($_SESSION['error'])) {
    $login_err = $_SESSION['error'];
    unset($_SESSION['error']);
}

// email structure verificator
function isValidEmail($conn, $email) {
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
                        echo '<script language="javascript">window.location = "login.php";</script>';
                        exit;
                    }
                } else {
                    echo '<script language="javascript">alert("Invalid email format!");</script>';
                    echo '<script language="javascript">window.location = "login.php";</script>';
                    exit;
                }
            } else {
                echo '<script language="javascript">alert("Email already exists!");</script>';
                echo '<script language="javascript">window.location = "login.php";</script>';
                exit;
            }
        } else {
            echo "Something went wrong with email verification. Please try again.";
        }
        mysqli_stmt_close($statement);
    }
    return false;
}

//TO BE CONTINUED PAG HINANAP

if(isset($_POST['register'])){
    
    $email = $_POST['email'];
	$usern = $_POST['usern'];
	$pword = $_POST['pword'];

    if(isValidEmail($conn,$email)){
        if(empty(trim($_POST["usern"]))){ // check if tama username
            $username_err = "Please enter a username.";
        }else if(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["usern"]))){
            $username_err = "Username can only contain letters, numbers, and underscores.";
        }else{
            $sql = "SELECT userID FROM users WHERE uname = ?";
            if($statement = mysqli_prepare($conn, $sql)){
                mysqli_stmt_bind_param($statement, "s", $param_username);
                $param_username = trim($_POST["usern"]); // set parameter
                if(mysqli_stmt_execute($statement)){ // execute
                    mysqli_stmt_store_result($statement);
                    
                    if(mysqli_stmt_num_rows($statement) == 1){
                        $login_err = "This username is already taken.";
                    } else{
                        $usern = trim($_POST["usern"]);
                    }
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }
                mysqli_stmt_close($statement);
            }
        }
        if(empty(trim($_POST["pword"]))){// check if tama pw
            $password_err = "Please enter a password.";     
        }else if(strlen(trim($_POST["pword"])) < 8){
            $login_err = "Password must have atleast 8 characters.";
        }else{
            $pword = trim($_POST["pword"]);
        }
        if(empty($login_err)){//check input errors bago ipasok sa db
            $search = mysqli_query($conn, "SELECT * FROM users");
            if(mysqli_fetch_assoc($search) == 0){
                $sql = "INSERT INTO users (`email`, `uname`, `pword`, `category`, `status`) VALUES (?, ?, ?, ?, ?)";   
                if($statement = mysqli_prepare($conn, $sql)){
                    mysqli_stmt_bind_param($statement, "sssss", $param_email, $param_username, $param_password, $categ, $statusadd);

                    // set parameter
                    $categ = "admin";
                    $statusadd = "active";
                    $param_email = $email;
                    $param_username = $usern;
                    $param_password = password_hash($pword, PASSWORD_DEFAULT); // creates a password hash
                    if(mysqli_stmt_execute($statement)){// execute
                        // echo '<script language="javascript">alert("Successfully Registered!");</script>';
                        // echo '<script language="javascript">window.location = "login.php";</script>';
                    }else{
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                    mysqli_stmt_close($statement);// close
                }
            }else{
                $sql = "INSERT INTO users (`email`, `uname`, `pword`, `category`, `status`) VALUES (?, ?, ?, ?, ?)";   
                if($statement = mysqli_prepare($conn, $sql)){
                    mysqli_stmt_bind_param($statement, "sssss", $param_email, $param_username, $param_password, $categ, $statusadd);

                    // set parameter
                    $categ = "customer";
                    $statusadd = "active";
                    $param_email = $email;
                    $param_username = $usern;
                    $param_password = password_hash($pword, PASSWORD_DEFAULT); // creates a password hash
                    if(mysqli_stmt_execute($statement)){// execute
                        // echo '<script language="javascript">alert("Successfully Registered!");</script>';
                        // echo '<script language="javascript">window.location = "login.php";</script>';
                    }else{
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                    mysqli_stmt_close($statement);// close
                }
            }
        }
    }

    $prod = $conn->prepare("SELECT userID FROM users WHERE uname = ?");
    $prod->bind_param("s", $usern);
    $prod->execute();
    $prod->bind_result($userID);
    $prod->fetch();
    $prod->close();

    mysqli_close($conn);
}else if(isset($_POST['submit'])){
    if(!empty($_POST['userID']) || !empty($_POST['question']) || !empty($_POST['answer'])){
        $userID = $_POST['userID'];
        $ques = $_POST['question'];
        $answer = $_POST['answer'];

        $input = $conn->prepare("INSERT INTO `security` (`userID`, `question`, `answer`) VALUES (?, ?, ?)");
        $input->bind_param("iss", $userID, $ques, $answer);
        if($input->execute()){
            echo '<script language="javascript">alert("Successfully Registered!");</script>';
            echo '<script language="javascript">window.location = "login.php";</script>';
            $input->close();
        };
    }else{
        echo '<script language="javascript">alert("Please fulfill all details!");</script>';
        echo '<script language="javascript">window.location = "login.php";</script>';
    }

}else if(isset($_GET['forgotpass'])){
}else if(isset($_POST['finduser'])){
    $uname = $_POST['usern'];

    $pr = $conn->prepare("SELECT userID FROM `users` WHERE uname = ?");
    $pr->bind_param("s", $uname);
    $pr->execute();
    $pr->bind_result($userID);
    $pr->fetch();
    $pr->close();

    $prod = $conn->prepare("SELECT * FROM `security` WHERE userID = ?");
    $prod->bind_param("i", $userID);
    $prod->execute();
    $prod->bind_result($userID,$question,$answer);
    $prod->fetch();
    $prod->close();



}else if(isset($_POST['evaluate'])){
    if(!empty($_POST['userID']) || !empty($_POST['db_answer']) || !empty($_POST['answer'])){
        $userID = $_POST['userID'];
        $db_answer = $_POST['db_answer'];
        $answer = $_POST['answer'];

        if($answer === $db_answer){
            echo '<script language="javascript">window.location = "forgot-pass.php?id='.$userID.'";</script>';
        }else{
            echo '<script language="javascript">alert("Wrong answer, please try again.");</script>';
            echo '<script language="javascript">window.location = "login.php";</script>';
        }
    }
    

}else if(isset($_GET['id'])){
    $userID = $_GET['id'];
}else if(isset($_POST['changepass'])){
    $userID = $_POST['userID'];
    $newpass = $_POST['newpass'];
    $retype = $_POST['retype'];

    if($newpass === $retype){
        $hashedPass = password_hash($newpass, PASSWORD_DEFAULT);
        $input = $conn->prepare("UPDATE users SET `pword` = ? WHERE `userID` = ?");
        $input->bind_param("si", $hashedPass, $userID);
        if($input->execute()){
            echo '<script language="javascript">alert("Password updated successfully!");</script>';
            echo '<script language="javascript">window.location = "login.php";</script>';
            $input->close();
        };
    }else{
        echo '<script language="javascript">alert("Password do not match!");</script>';
        echo '<script language="javascript">window.location = "login.php";</script>';
    }

    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <link rel="stylesheet" type="text/css" href="css/forgot-pass.css">
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <div class="background-blur"></div>
    <div class="container" id="main">
        <div class="forgot-box" style="display: <?php echo isset($_POST['register']) ? 'flex' : 'none'; ?>;">
            <h1>Set up a Security Question</h1>
            <form action="forgot-pass.php" method="post">
                <input type="hidden" value="<?php echo $userID; ?>" name="userID">
                <select name="question" id="secu">
                    <option value="What was the first accessory you ever received as a gift?">What was the first accessory you ever received as a gift?</option>
                    <option value="What is your favorite color for accessories?">What is your favorite color for accessories?</option>
                    <option value="Where did you buy your favorite pair of earrings?">Where did you buy your favorite pair of earrings?</option>
                    <option value="What was the most expensive accessory you bought?">What was the most expensive accessory you bought?</option>
                    <option value="Do you prefer silver or gold accessories?">Do you prefer silver or gold accessories?</option>
                </select>
                <input type="text" name="answer" Placeholder="Enter answer here">
                <button name="submit" type="submit">Submit</button>
            </form>
        </div>
        <div class="forgot-box" style="display: <?php echo isset($_GET['forgotpass']) ? 'flex' : 'none'; ?>;">
            <h1>Find Username</h1>
            <form action="forgot-pass.php" method="post">
                <input type="text" name="usern" Placeholder="Enter username here" required>
                <button name="finduser" type="submit">Submit</button>
            </form>
        </div>
        <div class="forgot-box" style="display: <?php echo isset($_POST['finduser']) ? 'flex' : 'none'; ?>;">
            <h1>Answer Security Question</h1>
            <form action="forgot-pass.php" method="post">
                <input type="hidden" value="<?php echo $userID; ?>" name="userID">
                <input type="hidden" value="<?php echo $answer; ?>" name="db_answer">
                
                <input type="text" value="<?php echo $question; ?>" readonly>
                <input type="text" name="answer" Placeholder="Enter answer here" required>
                <button name="evaluate" type="submit">Submit</button>
            </form>
        </div>
        <div class="forgot-box" style="display: <?php echo isset($_GET['id']) ? 'flex' : 'none'; ?>;">
            <h1>New Password</h1>
            <form action="forgot-pass.php" method="post">
                <input type="hidden" value="<?php echo $userID; ?>" name="userID">
                <input type="text" name="newpass" Placeholder="Enter new password here" required>
                <input type="text" name="retype" Placeholder="Re-type password here" required>
                <button name="changepass" type="submit">Submit</button>
            </form>
        </div>
    </div>

    <script type="text/javascript">
        const signupButton = document.getElementById('lipat-left');
        const signinButton = document.getElementById('lipat-right');
        const main = document.getElementById('main');
        
        signupButton.addEventListener('click', () =>{
            main.classList.add("right-panel-active");
        });
        signinButton.addEventListener('click', () =>{
            main.classList.remove("right-panel-active");
        });
        document.getElementById('show-password').addEventListener('change', function() {
            const passwordInput = document.getElementById('pw');
            if (this.checked) {
                passwordInput.type = 'text'; // Show password
            } else {
                passwordInput.type = 'password'; // Hide password
            }
        });
        document.getElementById('show-passwordd').addEventListener('change', function() {
            const passwordInputt = document.getElementById('pword');
            if (this.checked) {
                passwordInputt.type = 'text'; // Show password
            } else {
                passwordInputt.type = 'password'; // Hide password
            }
        });
    </script>
</body>
</html>