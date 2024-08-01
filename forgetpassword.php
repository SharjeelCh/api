<?php
function handleForgetPassword($conn) {
    $EMAIL_REGEX = '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/';
    $PASSWORD_REGEX = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    $user = json_decode(file_get_contents('php://input'));

    $validEmail = preg_match($EMAIL_REGEX, $user->email);
    $validPass = preg_match($PASSWORD_REGEX, $user->password);
    $response = [];

    if ($validEmail && $validPass) {
        $sql = "UPDATE user SET password = :password WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $user->email);
        $stmt->bindParam(':password', $user->password);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Password updated successfully'];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to update password'];
        }
    } else {
        if (!$validEmail) {
            $response = ['status' => 'fail', 'message' => 'Incorrect email'];
        } else if (!$validPass) {
            $response = ['status' => 'fail', 'message' => 'Invalid password. Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character.'];
        }
    }

    echo json_encode($response);
}


function searchUserByEmail($conn) {
    if (isset($_GET['email'])) {
        $email = $_GET['email'];
        $sql = "SELECT * FROM user WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $response = ['status' => 'success', 'user' => $user];
        } else {
            $response = ['status' => 'error', 'message' => 'User not found'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Invalid input'];
    }

    echo json_encode($response);
}

?>
