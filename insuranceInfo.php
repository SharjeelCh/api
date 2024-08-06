<?php
require_once 'DbConnect.php';

function insertInsuranceInfo($conn) {
    // Get the input data
    $patient_id = $_POST['patient_id'];
    $relation = $_POST['relation'];
    $plan_name = $_POST['plan_name'];
    $group_policy_number = $_POST['group_policy_number'];
    $member_id = $_POST['member_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $sex = $_POST['sex'];
    $notetype = $_POST['notetype'];
    $insurance_type = $_POST['insurance_type'];

    // Handle file upload
    if (isset($_FILES['note_pdf']) && $_FILES['note_pdf']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/insurance/';
        $fileName = $patient_id . '_' . $insurance_type . '.pdf';
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['note_pdf']['tmp_name'], $filePath)) {
            // File path to be stored in the database
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File upload error']);
            return;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded or upload error']);
        return;
    }

    // Validate required fields
    $requiredFields = ['relation', 'plan_name', 'group_policy_number', 'member_id', 'first_name', 'last_name', 'dob', 'address', 'sex', 'insurance_type'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['status' => 'error', 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
            return;
        }
    }

    // Check if patient exists
    $sql = "SELECT id FROM patient WHERE id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo json_encode(['status' => 'error', 'message' => 'Patient not found']);
        return;
    }

    // Check if insurance info already exists for this patient
    $sql = "SELECT id FROM insurance WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $existingInsurance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingInsurance) {
        echo json_encode(['status' => 'error', 'message' => 'Insurance info already exists for this patient']);
        return;
    }

    // Begin transaction
    $conn->beginTransaction();

    try {
        // Insert insurance info
        $sql = "INSERT INTO insurance (patient_id, insurance_type, relation, plan_name, group_policy_number, member_id, first_name, last_name, middle_name, dob, address, sex) 
                VALUES (:patient_id, :insurance_type, :relation, :plan_name, :group_policy_number, :member_id, :first_name, :last_name, :middle_name, :dob, :address, :sex)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':insurance_type', $insurance_type);
        $stmt->bindParam(':relation', $relation);
        $stmt->bindParam(':plan_name', $plan_name);
        $stmt->bindParam(':group_policy_number', $group_policy_number);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':middle_name', $middle_name);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':sex', $sex);
        $stmt->execute();

        // Get the last inserted insurance id
        $insurance_id = $conn->lastInsertId();

        // Insert file path into PatientNotesPDF
        $sql = "INSERT INTO PatientNotesPDF (insurance_id, patient_id, note_pdf, notes_type) VALUES (:insurance_id, :patient_id, :note_pdf, :notetype)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':insurance_id', $insurance_id);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':note_pdf', $filePath); // Store file path in database
        $stmt->bindParam(':notetype', $notetype);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        echo json_encode(['status' => 'success', 'message' => 'Insurance info and PDF inserted successfully']);
    } catch (Exception $e) {
        // Rollback transaction if something went wrong
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert insurance info: ' . $e->getMessage()]);
    }
}

function insertInsuranceInfoS($conn) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Validate required fields
    $requiredFields = ['patient_id', 'relation', 'plan_name', 'group_policy_number', 'member_id', 'first_name', 'last_name', 'dob', 'address', 'sex', 'insurance_type'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['status' => 'error', 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
            return;
        }
    }

    $patient_id = $_POST['patient_id'];
    $relation = $_POST['relation'];
    $plan_name = $_POST['plan_name'];
    $group_policy_number = $_POST['group_policy_number'];
    $member_id = $_POST['member_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $sex = $_POST['sex'];
    $notetype = $_POST['notetype'];
    $insurance_type = $_POST['insurance_type'];

    // Handle file upload
    if (isset($_FILES['note_pdf']) && $_FILES['note_pdf']['error'] == UPLOAD_ERR_OK) {
        $fileContent = file_get_contents($_FILES['note_pdf']['tmp_name']);
    } else {
        $fileContent = null; // No file or upload error
    }

    // Check if patient exists
    $sql = "SELECT id FROM patient WHERE id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo json_encode(['status' => 'error', 'message' => 'Patient not found']);
        return;
    }

    // Check if secondary insurance info already exists for this patient
    $sql = "SELECT id FROM insurance WHERE patient_id = :patient_id AND insurance_type = 'Secondary'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $existingInsurance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingInsurance) {
        echo json_encode(['status' => 'error', 'message' => 'Secondary insurance info already exists for this patient']);
        return;
    }

    // Begin transaction
    $conn->beginTransaction();

    try {
        // Insert secondary insurance info
        $sql = "INSERT INTO insurance (patient_id, insurance_type, relation, plan_name, group_policy_number, member_id, first_name, last_name, middle_name, dob, address, sex) 
                VALUES (:patient_id, :insurance_type, :relation, :plan_name, :group_policy_number, :member_id, :first_name, :last_name, :middle_name, :dob, :address, :sex)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':insurance_type', $insurance_type);
        $stmt->bindParam(':relation', $relation);
        $stmt->bindParam(':plan_name', $plan_name);
        $stmt->bindParam(':group_policy_number', $group_policy_number);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':middle_name', $middle_name);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':sex', $sex);
        $stmt->execute();

        // Get the last inserted insurance id
        $insurance_id = $conn->lastInsertId();

        // Insert secondary insurance card PDF into PatientNotesPDF
        if ($fileContent) {
            $sql2 = "INSERT INTO PatientNotesPDF (insurance_id, patient_id, note_pdf, notes_type) VALUES (:insurance_id, :patient_id, :note_pdf, :notetype)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(':insurance_id', $insurance_id);
            $stmt2->bindParam(':patient_id', $patient_id);
            $stmt2->bindParam(':note_pdf', $fileContent, PDO::PARAM_LOB); // Use PDO::PARAM_LOB for large objects
            $stmt2->bindParam(':notetype', $notetype);
            $stmt2->execute();
        }

        // Commit transaction
        $conn->commit();

        echo json_encode(['status' => 'success', 'message' => 'Secondary insurance info and PDF inserted successfully']);
    } catch (Exception $e) {
        // Rollback transaction if something went wrong
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert secondary insurance info: ' . $e->getMessage()]);
    }
}




function getInsuranceInfo($conn) {
    $patient_id = $_GET['patient_id'];

    // Check if patient exists
    $sql = "SELECT id FROM patient WHERE id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo json_encode(['status' => 'error', 'message' => 'Patient not found']);
        return;
    }

    // Retrieve insurance info
    $sql = "SELECT * FROM insurance WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $insuranceInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($insuranceInfo) {
        // Retrieve associated file path
        $sql = "SELECT note_pdf FROM PatientNotesPDF WHERE insurance_id = :insurance_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':insurance_id', $insuranceInfo['id']);
        $stmt->execute();
        $pdfInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        $insuranceInfo['note_pdf'] = $pdfInfo ? $pdfInfo['note_pdf'] : null;

        echo json_encode(['status' => 'success', 'data' => $insuranceInfo]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Insurance info not found']);
    }
}


function getInsuranceInfoS($conn) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $patient_id = $_GET['patient_id'];

    if (empty($patient_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Patient ID is required']);
        return;
    }

    $sql = "SELECT i.*, p.note_pdf 
            FROM insurance i
            LEFT JOIN PatientNotesPDF p ON i.id = p.insurance_id
            WHERE i.patient_id = :patient_id AND i.insurance_type = 'Secondary'";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $insuranceInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log(print_r($insuranceInfo, true)); // Log the fetched data for debugging

            if ($insuranceInfo) {
                // Convert the blob data to base64 for easier handling in JSON
                foreach ($insuranceInfo as &$info) {
                    if (isset($info['note_pdf'])) {
                        $info['note_pdf'] = base64_encode($info['note_pdf']);
                    }
                }

                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'data' => $insuranceInfo]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'No secondary insurance info found for this patient']);
            }
        } else {
            $errorInfo = $stmt->errorInfo();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Query execution failed', 'error' => $errorInfo]);
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}


function updateInsuranceInfoS($conn) {
    $insuranceInfo = json_decode(file_get_contents('php://input'), true);
    $patient_id = $insuranceInfo['patient_id'];

    // Validate required fields
    $requiredFields = ['relation', 'plan_name', 'group_policy_number', 'member_id', 'first_name', 'last_name', 'dob', 'address', 'sex'];
    foreach ($requiredFields as $field) {
        if (empty($insuranceInfo[$field])) {
            echo json_encode(['status' => 'error', 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
            return;
        }
    }

    // Check if patient exists
    $sql = "SELECT id FROM patient WHERE id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo json_encode(['status' => 'error', 'message' => 'Patient not found']);
        return;
    }

    // Check if secondary insurance info exists
    $sql = "SELECT id FROM insurance WHERE patient_id = :patient_id AND insurance_type = 'Secondary'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $existingInsurance = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingInsurance) {
        echo json_encode(['status' => 'error', 'message' => 'Secondary insurance info not found']);
        return;
    }

    // Begin transaction
    $conn->beginTransaction();

    try {
        // Update secondary insurance info
        $sql = "UPDATE insurance SET relation = :relation, plan_name = :plan_name, group_policy_number = :group_policy_number, member_id = :member_id, 
                first_name = :first_name, last_name = :last_name, middle_name = :middle_name, dob = :dob, address = :address, sex = :sex 
                WHERE id = :insurance_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':relation', $insuranceInfo['relation']);
        $stmt->bindParam(':plan_name', $insuranceInfo['plan_name']);
        $stmt->bindParam(':group_policy_number', $insuranceInfo['group_policy_number']);
        $stmt->bindParam(':member_id', $insuranceInfo['member_id']);
        $stmt->bindParam(':first_name', $insuranceInfo['first_name']);
        $stmt->bindParam(':last_name', $insuranceInfo['last_name']);
        $stmt->bindParam(':middle_name', $insuranceInfo['middle_name']);
        $stmt->bindParam(':dob', $insuranceInfo['dob']);
        $stmt->bindParam(':address', $insuranceInfo['address']);
        $stmt->bindParam(':sex', $insuranceInfo['sex']);
        $stmt->bindParam(':insurance_id', $existingInsurance['id']);
        $stmt->execute();

        // Handle file upload
        if (isset($_FILES['note_pdf']) && $_FILES['note_pdf']['error'] == UPLOAD_ERR_OK) {
            $fileContent = file_get_contents($_FILES['note_pdf']['tmp_name']);
            $sql2 = "INSERT INTO PatientNotesPDF (insurance_id, patient_id, note_pdf, notes_type) VALUES (:insurance_id, :patient_id, :note_pdf, :notetype)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(':insurance_id', $existingInsurance['id']);
            $stmt2->bindParam(':patient_id', $patient_id);
            $stmt2->bindParam(':note_pdf', $fileContent, PDO::PARAM_LOB);
            $stmt2->bindParam(':notetype', $insuranceInfo['notetype']);
            $stmt2->execute();
        }

        // Commit transaction
        $conn->commit();

        echo json_encode(['status' => 'success', 'message' => 'Secondary insurance info updated successfully']);
    } catch (Exception $e) {
        // Rollback transaction if something went wrong
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update secondary insurance info: ' . $e->getMessage()]);
    }
}


function updateInsuranceInfo($conn) {
    $insuranceInfo = json_decode(file_get_contents('php://input'), true);
    $patient_id = $insuranceInfo['patient_id'];

    // Validate required fields
    $requiredFields = ['relation', 'plan_name', 'group_policy_number', 'member_id', 'first_name', 'last_name', 'dob', 'address', 'sex'];
    foreach ($requiredFields as $field) {
        if (empty($insuranceInfo[$field])) {
            echo json_encode(['status' => 'error', 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
            return;
        }
    }

    // Check if patient exists
    $sql = "SELECT id FROM patient WHERE id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo json_encode(['status' => 'error', 'message' => 'Patient not found']);
        return;
    }

    // Check if insurance info exists
    $sql = "SELECT id FROM insurance WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $existingInsurance = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingInsurance) {
        echo json_encode(['status' => 'error', 'message' => 'Insurance info not found']);
        return;
    }

    // Begin transaction
    $conn->beginTransaction();

    try {
        // Update insurance info
        $sql = "UPDATE insurance SET relation = :relation, plan_name = :plan_name, group_policy_number = :group_policy_number, member_id = :member_id, 
                first_name = :first_name, last_name = :last_name, middle_name = :middle_name, dob = :dob, address = :address, sex = :sex 
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':relation', $insuranceInfo['relation']);
        $stmt->bindParam(':plan_name', $insuranceInfo['plan_name']);
        $stmt->bindParam(':group_policy_number', $insuranceInfo['group_policy_number']);
        $stmt->bindParam(':member_id', $insuranceInfo['member_id']);
        $stmt->bindParam(':first_name', $insuranceInfo['first_name']);
        $stmt->bindParam(':last_name', $insuranceInfo['last_name']);
        $stmt->bindParam(':middle_name', $insuranceInfo['middle_name']);
        $stmt->bindParam(':dob', $insuranceInfo['dob']);
        $stmt->bindParam(':address', $insuranceInfo['address']);
        $stmt->bindParam(':sex', $insuranceInfo['sex']);
        $stmt->bindParam(':id', $existingInsurance['id']);
        $stmt->execute();

        // Update insurance card PDF in PatientNotesPDF
        if (isset($_FILES['note_pdf']) && $_FILES['note_pdf']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/insurance/';
            $fileName = $patient_id . '_' . $insuranceInfo['insurance_type'] . '.pdf';
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['note_pdf']['tmp_name'], $filePath)) {
                $sql = "UPDATE PatientNotesPDF SET note_pdf = :note_pdf, notetype = :notetype WHERE insurance_id = :insurance_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':note_pdf', $filePath); // Update file path
                $stmt->bindParam(':notetype', $insuranceInfo['notetype']);
                $stmt->bindParam(':insurance_id', $existingInsurance['id']);
                $stmt->execute();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'File upload error']);
                return;
            }
        }

        // Commit transaction
        $conn->commit();

        echo json_encode(['status' => 'success', 'message' => 'Insurance info and PDF updated successfully']);
    } catch (Exception $e) {
        // Rollback transaction if something went wrong
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update insurance info: ' . $e->getMessage()]);
    }
}

?>
