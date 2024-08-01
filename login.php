<?php
require_once 'sessionHandler.php';

function handleLogin($conn) {
    if (isset($_GET['email']) && isset($_GET['password'])) {
        $email = $_GET['email'];
        $password = $_GET['password'];

        $emailCheckSql = "SELECT * FROM user WHERE email = :email";
        $stmt = $conn->prepare($emailCheckSql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['password'] === $password) {
                if ($user['is_verified']) {
                    loginUser($user);
                    echo json_encode(['status' => 'success', 'user' => $user]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Email not verified. Please verify your email.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Incorrect password']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Email not found']);
        }
    } else {
        $sql = "SELECT * FROM user";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    }
}
?>
