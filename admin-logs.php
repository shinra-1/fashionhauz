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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(!empty($_POST["date"])&&!empty($_POST["uname"])){
        $date = $_POST["date"];
        $uname = $_POST["uname"];
    }else{
        header("location: admin-logs.php");
    }
    
    // Prepare the statement for fetching activity logs based on the submitted date
    $stmt = $conn->prepare("SELECT * FROM activitylogs WHERE DATE(date_created) = ? AND uname = ? ORDER BY id DESC");
    $stmt->bind_param("ss", $date, $uname);
    $stmt->execute();
    $results = $stmt->get_result();

    if ($results->num_rows > 0) {
        $activityLogs = $results->fetch_all(MYSQLI_ASSOC); // Fetch all results into an array
    } else {
        $activityLogs = []; // No logs found for the selected date
    }
}else{
    $stmt = $conn->prepare("SELECT * FROM activitylogs ORDER BY id DESC");
    $stmt->execute();
    $results = $stmt->get_result();

    if ($results->num_rows > 0) {
        $activityLogs = $results->fetch_all(MYSQLI_ASSOC); 
    } else {
        $activityLogs = []; 
    }
}

function fetchStaffUsers($conn) {
    $users = [];
    $sql = "SELECT userID, uname FROM users WHERE category != 'customer'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row; // Add each user to the array
        }
    }
    
    return $users;
}

$staffUsers = fetchStaffUsers($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/admin-emplist.css">
    <link rel="stylesheet" type="text/css" href="css/admin-logs.css">
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    <div class="upper margin">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="date" class="labeldate"><img src="icons/calendar.png" alt=""></label><input type="date" name="date" class="inputdate">
            <label for="date" class="labeldate aa aaa"><img src="icons/person.png" alt=""></label>
            <select id="combo-box" class="combobox" name="uname">
                <option value="" disabled selected hidden>User</option>
            <?php

            if (!empty($staffUsers)) {
                foreach ($staffUsers as $user) {
                    echo "<option value='" . htmlspecialchars($user['uname']) . "'>" . htmlspecialchars($user['uname']) . "</option>";
                }
            } else {
                echo "<option value=''>No users found</option>";
            }
            ?>
            </select>
            
            <button class="buttondate" name="submit"><img src="icons/search.png" alt=""></button>
        </form>
    </div>
    <h1 class="content-title">Activity Logs</h1>
    <section>
        <div class="table-bg">
            <table>
                <thead>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Description</th>
                    <th>Date</th>
                </thead>
                <tbody>
                <?php 
                // Display the fetched activity logs
                if (isset($activityLogs) && !empty($activityLogs)) {
                    foreach ($activityLogs as $row) {         
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['uname']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_created']); ?></td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='4'>No activity logs found for the selected date.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </section>
</body>
</html>