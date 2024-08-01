<?php
function handleVerify($conn) {
    $token = $_GET['token'];

    if (!$token) {
        echo json_encode(['status' => 'fail', 'message' => 'Verification token missing']);
        return;
    }

    $sql = "UPDATE user SET is_verified = 1 WHERE verification_token = :token";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':token', $token);

    if ($stmt->execute() && $stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Email verified successfully']);
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'Invalid or expired verification token']);
    }
}
?>
