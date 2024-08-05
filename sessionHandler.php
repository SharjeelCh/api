<?php
session_start();

function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
}

function logoutUser() {
    session_unset();
    session_destroy();
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function getAuthenticatedUser() {
    if (isAuthenticated()) {
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name']
        ];
    }
    return null;
}
?>
