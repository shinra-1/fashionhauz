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
if(isset($_GET['delete'])){
    $id = $_GET['delete'];

    $prod = $conn->prepare("SELECT uname FROM users WHERE userID = ?");
    $prod->bind_param("i", $id);
    $prod->execute();
    $prod->bind_result($usern);
    $prod->fetch();
    $prod->close();

    $archivess = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
    $detail = 'Deleted user: '.$usern;
    $archivess->bind_param("ss", $username, $detail);
    $archivess->execute();
    $archivess->close();

    $stmt = $conn->prepare("UPDATE users SET `status` = ? WHERE userID = ?");
    $statdel = "inactive";
    $stmt->bind_param("si", $statdel, $id);
    if ($stmt->execute()) {
        header("location: admin-employee.php");
    } else {
        echo "Error deleting record: " . $stmt->error;
    }
    $stmt->close();
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/admin-emplist.css">
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    <div class="upper margin">
        <button id="create"><img src="icons/create.png" alt="create" >Create</button>
    </div>
    <h1 class="content-title">Employee List</h1>
    <section>
        <div class="table-bg">
            <table>
                <thead>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Bday</th>
                    <th>Gender</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Creation Date</th>
                    <th>Action</th>
                </thead>
                <tbody>
                <?php $select = mysqli_query($conn,"SELECT * FROM users WHERE category != '' AND category != 'customer'AND category != 'admin' AND status != 'inactive'");
                    while($row=mysqli_fetch_assoc($select)){      
                ?>
                    <tr>
                        <td><?php echo $row['userID']; ?></td>
                        <?php echo '<td>'.$row['fname'].' '.$row['lname'].'</td>'; ?>
                        <td><?php echo $row['age']; ?></td>
                        <td><?php echo $row['bday']; ?></td>
                        <td><?php echo $row['gender']; ?></td>
                        <td><?php echo $row['cnumber']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['uname']; ?></td>
                        <td><?php echo $row['pword']; ?></td>
                        <td><?php echo $row['date_created']; ?></td>
                        <td>
                            <div class="option">
                                <a href="form-actions/emp-account-update.php?edit=<?php echo $row['userID']; ?>" class="op1"> Update</a>
                                <a href="admin-employee.php?delete=<?php echo $row['userID']; ?>" class="op2" onclick="return confirm('Are you sure you want to delete this account?');"> Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php   };  ?>
                </tbody>
            </table>
        </div>
    </section>
    <div class="create-part cp2" id="changethis">
        <div class="box-column">
            <img src="icons/close-bg.png" id="eks" alt="asdasdas">
            <h1>Add Account</h1>
            <form action="form-actions/addEmp.php" method="post">
            <div class="flex-box">
                <div class="left-box">
                    <input type="hidden" name="userto" value="<?php echo $username; ?>">
                    <input type="text" placeholder="First Name" name="fname" required>
                    <input type="text" placeholder="Middle Name" name="mname" >
                    <input type="text" placeholder="Last Name" name="lname" required>
                    <input type="number" placeholder="Age" name="age" required>
                    <input type="date" id="bday" placeholder="Birthday" name="bday" onfocus="(this.type='date')" onblur="(this.type='text')" required>
                </div>
                <div class="right-box">
                <div class="sex-part">
                    <label for="sex">Sex: </label>
                    <label for="male">Male</label>
                    <input type="radio" id="male" name="sex" value="male">
                    <label for="female">Female</label>
                    <input type="radio" id="female" name="sex" value="female">
                    <label for="other">Other</label>
                    <input type="radio" id="other" name="sex" value="other">
                </div>
                <input type="text" placeholder="Contact" name="cnumber" oninput="validateInput(event)" required>
                <input type="email" placeholder="Email" name="email" required>
                <input type="text" placeholder="Username" name="userr" required>
                <input type="text" placeholder="Password" name="passw" required>
                </div>
            </div>
            <button type="submit" name="submit"><img src="icons/create.png" alt="add">Add</button>
            </form>
        </div>
    </div>
    <script>
        const dateInput = document.getElementById('bday');
        dateInput.type = 'text';

        document.getElementById("create").onclick = function() {
            var text = document.getElementById("changethis");
            text.style.display = "flex"; 
        }; 
        document.getElementById("eks").onclick = function() {
            var text = document.getElementById("changethis");
            text.style.display = "none";
        };  
        function validateInput(event) {
            const value = event.target.value;
            if (!/^\d*$/.test(value)) {
                event.target.value = value.slice(0, -1);
            }
        }
    </script>
</body>
</html>