<?php
require_once 'DbConnect.php';

function insertPatientInfo($conn) {
    $patientInfo = json_decode(file_get_contents('php://input'), true);
    $patient_id = $patientInfo['patient_id'];

    // Validate required fields
    $requiredFields = ['phone', 'sex', 'DOB', 'nick_name', 'address', 'state', 'city', 'zip'];
    foreach ($requiredFields as $field) {
        if (empty($patientInfo[$field])) {
            echo json_encode(['status' => 'error', 'message' => ucfirst($field) . ' is required']);
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

    // Check if patient info already exists
    $sql = "SELECT patient_id FROM patient_info WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $existingInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingInfo) {
        echo json_encode(['status' => 'error', 'message' => 'Patient info already exists']);
        return;
    }

    // Insert patient info
    $sql = "INSERT INTO patient_info (patient_id, phone, sex, DOB, nick_name, address, state, city, zip) 
            VALUES (:patient_id, :phone, :sex, :DOB, :nick_name, :address, :state, :city, :zip)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->bindParam(':phone', $patientInfo['phone']);
    $stmt->bindParam(':sex', $patientInfo['sex']);
    $stmt->bindParam(':DOB', $patientInfo['DOB']);
    $stmt->bindParam(':nick_name', $patientInfo['nick_name']);
    $stmt->bindParam(':address', $patientInfo['address']);
    $stmt->bindParam(':state', $patientInfo['state']);
    $stmt->bindParam(':city', $patientInfo['city']);
    $stmt->bindParam(':zip', $patientInfo['zip']);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Patient info inserted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert patient info']);
    }
}

function getPatientInfo($conn, $patient_id) {
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

    // Get patient info
    $sql = "SELECT * FROM patient_info WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $patientInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($patientInfo) {
        echo json_encode(['status' => 'success', 'data' => $patientInfo]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Patient info not found']);
    }
}

function updatePatientInfo($conn) {
    $patientInfo = json_decode(file_get_contents('php://input'), true);
    $patient_id = $patientInfo['patient_id'];

    // Validate required fields
    $requiredFields = ['phone', 'sex', 'DOB', 'nick_name', 'address', 'state', 'city', 'zip'];
    foreach ($requiredFields as $field) {
        if (empty($patientInfo[$field])) {
            echo json_encode(['status' => 'error', 'message' => ucfirst($field) . ' is required']);
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

    // Check if patient info exists
    $sql = "SELECT patient_id FROM patient_info WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $existingInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingInfo) {
        echo json_encode(['status' => 'error', 'message' => 'Patient info not found']);
        return;
    }

    // Update patient info
    $sql = "UPDATE patient_info SET phone = :phone, sex = :sex, DOB = :DOB, nick_name = :nick_name, address = :address, state = :state, city = :city, zip = :zip 
            WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->bindParam(':phone', $patientInfo['phone']);
    $stmt->bindParam(':sex', $patientInfo['sex']);
    $stmt->bindParam(':DOB', $patientInfo['DOB']);
    $stmt->bindParam(':nick_name', $patientInfo['nick_name']);
    $stmt->bindParam(':address', $patientInfo['address']);
    $stmt->bindParam(':state', $patientInfo['state']);
    $stmt->bindParam(':city', $patientInfo['city']);
    $stmt->bindParam(':zip', $patientInfo['zip']);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Patient info updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update patient info']);
    }
}


function insertEmergencyContact($conn) {
    $contactInfo = json_decode(file_get_contents('php://input'), true);
    $patient_id = $contactInfo['patient_id'];

    // Validate required fields
    $requiredFields = ['last_name', 'first_name', 'relationship', 'phone'];
    foreach ($requiredFields as $field) {
        if (empty($contactInfo[$field])) {
            echo json_encode(['status' => 'error', 'message' => ucfirst($field) . ' is required']);
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

    // Check if emergency contact already exists for this patient
    $sql = "SELECT contact_id FROM emergency_contact WHERE patient_id = :patient_id AND last_name = :last_name AND first_name = :first_name";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->bindParam(':last_name', $contactInfo['last_name']);
    $stmt->bindParam(':first_name', $contactInfo['first_name']);
    $stmt->execute();
    $existingContact = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingContact) {
        echo json_encode(['status' => 'error', 'message' => 'Emergency contact already exists for this patient']);
        return;
    }

    // Insert emergency contact
    $sql = "INSERT INTO emergency_contact (patient_id, last_name, first_name, relationship, phone) 
            VALUES (:patient_id, :last_name, :first_name, :relationship, :phone)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->bindParam(':last_name', $contactInfo['last_name']);
    $stmt->bindParam(':first_name', $contactInfo['first_name']);
    $stmt->bindParam(':relationship', $contactInfo['relationship']);
    $stmt->bindParam(':phone', $contactInfo['phone']);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Emergency contact inserted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert emergency contact']);
    }
}
function updateEmergencyContact($conn) {
    $contactInfo = json_decode(file_get_contents('php://input'), true);
    $patient_id = $contactInfo['patient_id'];

    // Validate required fields
    $requiredFields = ['last_name', 'first_name', 'relationship', 'phone'];
    foreach ($requiredFields as $field) {
        if (empty($contactInfo[$field])) {
            echo json_encode(['status' => 'error', 'message' => ucfirst($field) . ' is required']);
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

    // Check if emergency contact exists
    $sql = "SELECT contact_id FROM emergency_contact WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $existingContact = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingContact) {
        echo json_encode(['status' => 'error', 'message' => 'Emergency contact not found']);
        return;
    }

    // Update emergency contact
    $sql = "UPDATE emergency_contact SET last_name = :last_name, first_name = :first_name, relationship = :relationship, phone = :phone 
            WHERE  patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->bindParam(':last_name', $contactInfo['last_name']);
    $stmt->bindParam(':first_name', $contactInfo['first_name']);
    $stmt->bindParam(':relationship', $contactInfo['relationship']);
    $stmt->bindParam(':phone', $contactInfo['phone']);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Emergency contact updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update emergency contact']);
    }
}

function getEmergencyInfo($conn, $patient_id) {
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

    // Get patient info
    $sql = "SELECT * FROM emergency_contact WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $patientInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($patientInfo) {
        echo json_encode(['status' => 'success', 'data' => $patientInfo]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Patient info not found']);
    }
}

?>
