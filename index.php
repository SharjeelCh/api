<?php
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

include 'DbConnect.php';
include 'signup.php';
include 'login.php';
include 'forgetPassword.php';
include 'verify.php';
require_once 'sessionHandler.php';
include 'appointments.php';
include 'patientInfo.php';
include 'insuranceInfo.php';
include 'patientIntake.php';
include 'ConsentForm.php';

$db = new DbConnect();
$conn = $db->connect();
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database Connection Failed']);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

$endpoint = $uri[2];

switch ($endpoint) {
    case 'signup':
        handleSignup($conn);
        break;
    case 'login':
        handleLogin($conn);
        break;
    case 'logout':
        logoutUser();
        echo json_encode(['status' => 'success', 'message' => 'User logged out successfully']);
        break;        
    case 'forgetpassword':
        handleForgetPassword($conn);
        break;
    case 'verify':
        handleVerify($conn);
        break;
    case 'searchUserByEmail':
        searchUserByEmail($conn);
        break;    
    case 'createAppointment':
        createAppointment($conn);
        break;
   case 'getAppointment':
    if (isset($_GET['user_id'])) {
        getAppointment($conn, $_GET['user_id']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Appointment ID not provided']);
    }
    break;

    case 'deleteAppointment':
            deleteAppointment($conn);
    
        break;
    case 'insertPatientInfo':
            insertPatientInfo($conn);
            break;
    case 'getPatientInfo':
            if (isset($_GET['patient_id'])) {
                getPatientInfo($conn, $_GET['patient_id']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Patient ID not provided']);
            }
            break;    
    case 'updatePatientInfo':
            updatePatientInfo($conn);
                break;
    case 'insertEmergencyContact':
                    insertEmergencyContact($conn);
                    break;
     case 'getEmergencyContacts':
                if (isset($_GET['patient_id'])) {
                        getEmergencyInfo($conn, $_GET['patient_id']);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Patient ID not provided']);
                    }
                    break;    
    case 'updateEmergencyContact':
                updateEmergencyContact($conn);
                        break;
                
    case 'insertInsuranceInfo':
        insertInsuranceInfo($conn);
                            break;
    case 'insertInsuranceInfoS':
        insertInsuranceInfoS($conn);
                            break;
    case 'getInsuranceInfo':
                        if (isset($_GET['patient_id'])) {
                            getInsuranceInfo($conn, $_GET['patient_id']);
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'Patient ID not provided']);
                            }
                            break;
    case 'getInsuranceInfoS':
                        if (isset($_GET['patient_id'])) {
                            getInsuranceInfoS($conn, $_GET['patient_id']);
                             } else {
                            echo json_encode(['status' => 'error', 'message' => 'Patient ID not provided']);
                            }
                            break;                                
    case 'updateInsuranceInfo':
        updateInsuranceInfo($conn);
                                break;


                                case 'insertPatientIntake':
                                    insertPatientIntake($conn);
                                    break;
                     case 'getPatientIntake':
                                if (isset($_GET['patient_id'])) {
                                    getPatientIntake($conn, $_GET['patient_id']);
                                    } else {
                                        echo json_encode(['status' => 'error', 'message' => 'Patient ID not provided']);
                                    }
                                    break;    
                    case 'updatePatientIntake':
                        updatePatientIntake($conn);
                                        break;                             


                                        case 'insertConsentForm':
                                            if (isset($_FILES['consentForm']) && isset($_POST['patient_id'])) {
                                                insertConsentForm($conn, $_POST['patient_id'], $_FILES['consentForm']);
                                            } else {
                                                echo json_encode(['status' => 'error', 'message' => 'Patient ID or file not provided']);
                                            }
                                            break;
                                    
                                        case 'updateConsentForm':
                                            if (isset($_FILES['consentForm']) && isset($_POST['patient_id'])) {
                                                updateConsentForm($conn, $_POST['patient_id'], $_FILES['consentForm']);
                                            } else {
                                                echo json_encode(['status' => 'error', 'message' => 'Patient ID or file not provided']);
                                            }
                                            break;
                                        case 'getConsentForm':
                                            if(isset($_GET['patient_id'])) {
                                                getConsentForm($conn,$_GET['patient_id']);
                                            }else {
                                                echo json_encode(['status' => 'error', 'message' => 'Patient ID not provided']);
                                            }
                                            break;
                                        case 'deleteConsentForm':
                                            if (isset($_GET['patient_id'])) {
                                                deleteConsentForm($conn, $_GET['patient_id']);
                                            } else {
                                                echo json_encode(['status' => 'error', 'message' => 'Patient ID not provided']);
                                            }
                                            break;       
                                        



    case 'updateAppointment':
        if (isset($_GET['id'])) {
            updateAppointment($conn, $_GET['id']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Appointment ID not provided']);
        }
        break;    
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Endpoint']);
        break;
}
?>
