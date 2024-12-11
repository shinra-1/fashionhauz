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

if(isset($_POST['add-address'])){
    if(!empty($_POST['name'])&&!empty($_POST['number'])&&!empty($_POST['region_post'])&&!empty($_POST['province_post'])&&!empty($_POST['city_post'])&&!empty($_POST['barangay'])&&!empty($_POST['street'])&&!empty($_POST['postal'])){
       $name = $_POST['name']; 
       $number = $_POST['number']; 
       $region = htmlspecialchars($_POST['region_post']); 
       $province = $_POST['province_post']; 
       $city = $_POST['city_post']; 
       $barangay = $_POST['barangay']; 
       $street = $_POST['street']; 
       $postal = $_POST['postal']; 

        if (!preg_match("/^09\d{9}$/", $number)) { // Validate if contact number is exactly 11 digits and starts with 09
            echo '<script language="javascript">alert("Invalid input. Please enter a valid 11-digit contact number.");</script>';
            echo '<script language="javascript">window.location.href = "main.php";</script>';
            exit;
        }  

        $slqinsert = $conn->prepare("INSERT INTO `address` (`userID`, `name`, `cnumber`, `street`, `barangay`, `city` , `province` , `region`, `postal`,`status`) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stats = "active";
        $slqinsert->bind_param("isssssssis", $userID, $name, $number, $street, $barangay, $city, $province, $region, $postal, $stats);
        if($slqinsert->execute()){
            echo '<script language="javascript">alert("Address added successfully.");</script>';
            echo '<script language="javascript">window.location.href = "addresses.php";</script>';
        }
    }else{
        echo '<script language="javascript">alert("Please fulfill all details correctly.");</script>';
        echo '<script language="javascript">window.location.href = "addresses.php";</script>';
    }
}else if(isset($_POST['update-address'])){
    if(!empty($_POST['name'])&&!empty($_POST['number'])&&!empty($_POST['region_post2'])&&!empty($_POST['province_post2'])&&!empty($_POST['city_post2'])&&!empty($_POST['barangay'])&&!empty($_POST['street'])&&!empty($_POST['postal'])){
       $id_address = $_POST['id_address']; 
       $name = $_POST['name']; 
       $number = $_POST['number']; 
       $region = htmlspecialchars($_POST['region_post2']); 
       $province = $_POST['province_post2']; 
       $city = $_POST['city_post2']; 
       $barangay = $_POST['barangay']; 
       $street = $_POST['street']; 
       $postal = $_POST['postal']; 

        if (!preg_match("/^09\d{9}$/", $number)) { // Validate if contact number is exactly 11 digits and starts with 09
            echo '<script language="javascript">alert("Invalid input. Please enter a valid 11-digit contact number.");</script>';
            echo '<script language="javascript">window.location.href = "addresses.php";</script>';
            exit;
        }    

        $slqupdate = $conn->prepare("UPDATE `address` SET `name` = ?, `cnumber` = ?, `street` = ?, `barangay` = ?, `city` = ?, `province` = ?, `region` = ?, `postal` = ? WHERE `id` = ?");
        $slqupdate->bind_param("sssssssii", $name, $number, $street, $barangay, $city, $province, $region, $postal, $id_address);
        if($slqupdate->execute()){
            echo '<script language="javascript">alert("Address updated successfully.");</script>';
            echo '<script language="javascript">window.location.href = "addresses.php";</script>';
        }
    }else{
        echo '<script language="javascript">alert("Please fulfill all details correctly.");</script>';
        echo '<script language="javascript">window.location.href = "addresses.php";</script>';
    }
}else if(isset($_POST['delete'])){
    $del_address = $_POST['address_id'];
    $sqldel = $conn->prepare("UPDATE `address` SET `status` = ? WHERE `id` = ?");
    $stats = "inactive";
    $sqldel->bind_param("si", $stats, $del_address);
    if($sqldel->execute()){
        echo '<script language="javascript">alert("Address deleted successfully.");</script>';
        echo '<script language="javascript">window.location.href = "addresses.php";</script>';
    }
}


$sqlfetch = $conn->prepare("SELECT * FROM `address` WHERE userID = ? AND `status` = ?");
$statfetch = "active";
$sqlfetch->bind_param("is", $userID, $statfetch);
$sqlfetch->execute();
$data_fetch = $sqlfetch->get_result();

if(isset($_POST['address_id'])){
    $address_id = $_POST['address_id'];

    $sqledit = $conn->prepare("SELECT * FROM `address` WHERE id = ?");
    $sqledit->bind_param("i", $address_id);
    $sqledit->execute();
    $data_edit = $sqledit->get_result();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/account.css">
    <link rel="stylesheet" type="text/css" href="css/address.css">
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
                    <a href="addresses.php" class="active">Addresses</a>
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
                <div class="addbox">
                    <button onclick="document.getElementById('newadd').style.display = 'flex';"><img src="../icons/create.png" alt="">Add</button>
                </div>
                <div class="newbox">
                    <?php
                        while($row=mysqli_fetch_assoc($data_fetch)){
                            $data_address_id = $row['id'];
                            $data_name = $row['name'];
                            $data_number = $row['cnumber'];
                            $data_street = $row['street'];
                            $data_barangay = $row['barangay'];
                            $data_city = $row['city'];
                            $data_province = $row['province'];
                            $data_region = $row['region'];
                            $data_postal = $row['postal'];
                    ?>
                    <div class="box-address">
                        <h3 class="name"><?php echo $data_name; ?></h3>
                        <h3 class="name"><?php echo $data_number; ?></h3>
                        <h3 class="name"><?php echo $data_street; ?></h3>
                        <h3 class="name"><?php echo $data_barangay; ?></h3>
                        <h3 class="name"><?php echo $data_city; ?></h3>
                        <h3 class="name"><?php echo $data_province; ?></h3>
                        <h3 class="name"><?php echo $data_region; ?></h3>
                        <h3 class="name"><?php echo $data_postal; ?></h3>
                        <form action="address-edit.php" method="post">
                            <input type="hidden" value="<?php echo $data_address_id; ?>" name="address_id">
                            <button type="submit"><img src="../icons/update.png" alt="">Edit</button>
                            <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this address?');"><img src="../icons/delete.png" alt="">Delete</button>
                        </form>
                    </div>
                    <?php };?>
                </div>
            </div>
        </div>
    </main>
    <div class="section" id="newadd" style="display: <?php echo isset($_POST['addaddress']) ? 'flex' : 'none'; ?>;">
        <div class="newaddress">
            <form action="addresses.php" method="post">
                <img src="../icons/close-bg.png" class="close" alt="asdasdas" onclick="document.getElementById('newadd').style.display = 'none';">
                <h2>New Address</h2>
                <div class="address-mid">
                    <div class="address-left">
                        <input type="text" name="name" placeholder="Name">
                        <input type="text" name="number" placeholder="Contact Number">
                        <select name="region" id="region" class="region" onchange="bagotext('region', 'region_post')">
                            <option value="" selected disabled hidden>Region</option>
                        </select>
                        <input type="hidden" name="region_post" id="region_post">
                        <select name="province" id="province" class="province" onchange="updateText('province', 'province_post')">
                            <option value="" selected disabled hidden>Province</option>
                        </select>
                        <input type="hidden" name="province_post" id="province_post">
                    </div>
                    <div class="address-right">
                        <select name="city" id="city" class="city" onchange="updateText('city', 'city_post')">
                            <option value="" selected disabled hidden>City/Municipality</option>
                        </select>
                        <input type="hidden" name="city_post" id="city_post">
                        <input type="text" name="barangay" placeholder="Barangay">
                        <input type="text" name="street" placeholder="Street Name">
                        <input type="text" name="postal" placeholder="Postal Code">
                    </div>
                </div>
                <button type="submit" name="add-address"><img src="../icons/create.png" alt="">Add</button>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script>
        function updateText(selectId, hiddenInputId) {
            const selectElement = document.getElementById(selectId);
            const hiddenInput = document.getElementById(hiddenInputId);
            hiddenInput.value = selectElement.options[selectElement.selectedIndex].textContent;
        }
        function bagotext(idone, idtwo) {
            const oneElement = document.getElementById(idone);
            const twoInput = document.getElementById(idtwo);
            twoInput.value = oneElement.value;
        }
        //new address
        // Fetch and populate regions in the new address section
        fetch("../newaddress/node_modules/philippines/regions.json")
        .then(response => response.json())
        .then(regions => {
            const regionSelect = document.querySelector("#region"); // New address region select
            regions.forEach(region => {
                const option = document.createElement("option");
                option.value = region.key;
                option.textContent = region.name;
                regionSelect.appendChild(option);
            });
        })
        .catch(error => console.error("Error fetching regions:", error));

        // Populate provinces when a region is selected (New Address Section)
        document.querySelector("#region").addEventListener("change", function() {
            const regionCode = this.value;
            fetch("../newaddress/node_modules/philippines/provinces.json")
                .then(response => response.json())
                .then(provinces => {
                    const provinceSelect = document.querySelector("#province"); // New address province select
                    provinceSelect.innerHTML = '<option value="" disabled selected hidden>Province</option>';
                    provinces
                        .filter(province => province.region === regionCode)
                        .forEach(province => {
                            const option = document.createElement("option");
                            option.value = province.key;
                            option.textContent = province.name;
                            provinceSelect.appendChild(option);
                        });
                });
        });

        // Populate cities when a province is selected (New Address Section)
        document.querySelector("#province").addEventListener("change", function() {
            const provinceCode = this.value;
            fetch("../newaddress/node_modules/philippines/cities.json")
                .then(response => response.json())
                .then(cities => {
                    const citySelect = document.querySelector("#city"); // New address city select
                    citySelect.innerHTML = '<option value="" disabled selected hidden>City</option>';
                    cities
                        .filter(city => city.province === provinceCode)
                        .forEach(city => {
                            const option = document.createElement("option");
                            option.value = city.key;
                            option.textContent = city.name;
                            citySelect.appendChild(option);
                        });
                });
        });
    </script>
</body>
</html>