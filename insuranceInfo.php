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
    $insurance_type = $_POST['insurance_type'];
    $notetype = $_POST['notetype'];

    // Handle file upload
    if (isset($_FILES['note_pdf']) && $_FILES['note_pdf']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/insurance/';
        $fileName = $patient_id . '_' . $insurance_type . '.pdf';
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['note_pdf']['tmp_name'], $filePath)) {
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

    $sql3 = "SELECT id FROM insurance WHERE patient_id = :patient_id AND insurance_type = 'Primary'";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bindParam(':patient_id', $patient_id);
    $stmt3->execute();
    $existingInsurance = $stmt3->fetch(PDO::FETCH_ASSOC);
    if(!$existingInsurance){
        echo json_encode(['status' => 'error', 'message' => 'Primary insurance info is must']);
        return;
    }
    
    $sql4 = "SELECT id FROM insurance WHERE patient_id = :patient_id AND insurance_type = 'Secondary'";
    $stmt4 = $conn->prepare($sql4);
    $stmt4->bindParam(':patient_id', $patient_id);
    $stmt4->execute();
    $existingInsuranceS = $stmt4->fetch(PDO::FETCH_ASSOC);
    if($existingInsuranceS){
        echo json_encode(['status' => 'error', 'message' => 'Secondary insurance info already exists for this patient']);
        return;
    }

    // Check if insurance info already exists for this patient
    $sql = "SELECT id FROM insurance WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $existingInsurance = $stmt->fetch(PDO::FETCH_ASSOC);

   

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
    $sql = "SELECT * FROM insurance WHERE patient_id = :patient_id and insurance_type = 'Primary'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $insuranceInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($insuranceInfo) {
        // Retrieve associated file path
        $sql = "SELECT note_pdf FROM PatientNotesPDF WHERE insurance_id = :insurance_id and notes_type = 'Primary Insurance'";
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
    $sql = "SELECT * FROM insurance WHERE patient_id = :patient_id and insurance_type = 'Secondary'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $insuranceInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($insuranceInfo) {
        // Retrieve associated file path
        $sql = "SELECT note_pdf FROM PatientNotesPDF WHERE insurance_id = :insurance_id and notes_type = 'Secondary Insurance'";
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


function updateInsuranceInfo($conn) {
    $insurance_id = $_POST['insurance_id'];
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
    $insurance_type = $_POST['insurance_type'];

    if (isset($_FILES['note_pdf']) && $_FILES['note_pdf']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/insurance/';
        $fileName = $insurance_id . '_' . $insurance_type . '.pdf';
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['note_pdf']['tmp_name'], $filePath)) {
            // File path to be stored in the database
            $pdfPath = $filePath;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File upload error']);
            return;
        }
    } else {
        // If no file was uploaded, keep the existing file path
        $pdfPath = $_POST['existing_note_pdf'];
    }

    // Update the insurance info in the database
    $query = "UPDATE insurance_info SET relation = ?, plan_name = ?, group_policy_number = ?, member_id = ?, first_name = ?, last_name = ?, middle_name = ?, dob = ?, address = ?, sex = ?, insurance_type = ?, note_pdf = ? 
              WHERE insurance_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssssssss", $relation, $plan_name, $group_policy_number, $member_id, $first_name, $last_name, $middle_name, $dob, $address, $sex, $insurance_type, $pdfPath, $insurance_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Insurance information updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
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




?>
