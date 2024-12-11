<?php
include '../config.php';

if (isset($_POST['cartID']) && isset($_POST['qty'])) {
    $cartID = $_POST['cartID'];
    $qty = $_POST['qty'];

    // Validate inputs
    if (is_numeric($qty) && $qty > 0) {
        // Update the quantity in the database
        $stmt = $conn->prepare("UPDATE cart SET qty = ? WHERE id = ?");
        $stmt->bind_param("ii", $qty, $cartID);

        if ($stmt->execute()) {
            echo "Cart updated successfully";
        } else {
            echo "Error updating cart";
        }

        $stmt->close();
    } else {
        echo "Invalid quantity";
    }
}
?>