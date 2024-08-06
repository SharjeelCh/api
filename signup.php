<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

function sendVerificationEmail($email, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = 'sharjeelh6451@gmail.com';                 
        $mail->Password   = 'jvwp imvf anco bekf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        
        $mail->Port       = 587;    

        $mail->SMTPDebug = 2; 
        $mail->Debugoutput = 'html';

        $mail->setFrom('sharjeelh6451@gmail.com', 'MDOS Billing Pro');
        $mail->addAddress($email);     

        $mail->isHTML(true);                                  
        $mail->Subject = 'Email Verification';
        $mail->Body    = "Please verify your email by clicking on the link: 
                        <a href='http://localhost/api/verify?token=$token'>Verify Email</a>";
        $mail->AltBody = "Please verify your email by visiting the following link: 
                        http://localhost/api/verify?token=$token";

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function handleSignup($conn) {
    $EMAIL_REGEX = '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/';
    $PASSWORD_REGEX = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    $user = json_decode(file_get_contents('php://input'));

    // Check if email is already registered
    $emailCheckSQL = "SELECT COUNT(*) FROM patient WHERE email = :email";
    $emailCheckStmt = $conn->prepare($emailCheckSQL);
    $emailCheckStmt->bindParam(':email', $user->email);
    $emailCheckStmt->execute();
    $emailCount = $emailCheckStmt->fetchColumn();

    if ($emailCount > 0) {
        echo json_encode(['status' => 'fail', 'message' => 'Email already registered']);
        return;
    }

    $sql = "INSERT INTO patient (id, first_name, last_name, email, password, created_at, verification_token, is_verified, is_veteran) 
            VALUES (null, :first_name, :last_name, :email, :password, :created_at, :verification_token, 0, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':first_name', $user->first_name);
    $stmt->bindParam(':last_name', $user->last_name);
    $stmt->bindParam(':email', $user->email);
    $stmt->bindParam(':password', $user->password);

    $currentTime = date('Y-m-d H:i:s');
    $stmt->bindParam(':created_at', $currentTime);

    $verification_token = bin2hex(random_bytes(16));
    $stmt->bindParam(':verification_token', $verification_token);

    $validEmail = preg_match($EMAIL_REGEX, $user->email);
    $validPass = preg_match($PASSWORD_REGEX, $user->password);
    $response = [];

    if ($validEmail && $validPass && $stmt->execute()) {
        sendVerificationEmail($user->email, $verification_token);
        $response = ['status' => 'success', 'message' => 'User Registered Successfully. Please verify your email.'];
    } else {
        if (!$validEmail) {
            $response = ['status' => 'fail', 'message' => 'Incorrect email'];
        } else if (!$validPass) {
            $response = ['status' => 'fail', 'message' => 'Invalid password. Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character.'];
        } else {
            $response = ['status' => 'fail', 'message' => 'Email already registered'];
        }
    }

    echo json_encode($response);
}

?>
