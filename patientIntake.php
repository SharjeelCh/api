<?php
require_once 'DbConnect.php';

function insertPatientIntake($conn) {
    $intakeInfo = json_decode(file_get_contents('php://input'), true);
    $patient_id = $intakeInfo['patient_id'];

    // Validate required fields
    $requiredFields = ['chiefComplaint', 'conditionDuration', 'painScale', 'painType', 'siteOfPain'];
    foreach ($requiredFields as $field) {
        if (empty($intakeInfo[$field])) {
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

    // Check if intake info already exists
    $sql = "SELECT patient_id FROM patientIntake WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $existingInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingInfo) {
        echo json_encode(['status' => 'error', 'message' => 'Patient intake info already exists']);
        return;
    }

    // Serialize arrays to JSON strings
    $painBetter = isset($intakeInfo['painBetter']) ? json_encode($intakeInfo['painBetter']) : null;
    $painType = isset($intakeInfo['painType']) ? json_encode($intakeInfo['painType']) : null;

    // Insert patient intake info
    $sql = "INSERT INTO patientIntake (patient_id, chiefComplaint, conditionDuration, painScale, painType, siteOfPain, painBetter, currentMedicalConditions, pastMedicalConditions, familyHistory, currentInjuries, pastInjuries, allergies, icd) 
            VALUES (:patient_id, :chiefComplaint, :conditionDuration, :painScale, :painType, :siteOfPain, :painBetter, :currentMedicalConditions, :pastMedicalConditions, :familyHistory, :currentInjuries, :pastInjuries, :allergies, :icd)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->bindParam(':chiefComplaint', $intakeInfo['chiefComplaint']);
    $stmt->bindParam(':conditionDuration', $intakeInfo['conditionDuration']);
    $stmt->bindParam(':painScale', $intakeInfo['painScale']);
    $stmt->bindParam(':painType', $painType);
    $stmt->bindParam(':siteOfPain', $intakeInfo['siteOfPain']);
    $stmt->bindParam(':painBetter', $painBetter);
    $stmt->bindParam(':currentMedicalConditions', $intakeInfo['currentMedicalConditions']);
    $stmt->bindParam(':pastMedicalConditions', $intakeInfo['pastMedicalConditions']);
    $stmt->bindParam(':familyHistory', $intakeInfo['familyHistory']);
    $stmt->bindParam(':currentInjuries', $intakeInfo['currentInjuries']);
    $stmt->bindParam(':pastInjuries', $intakeInfo['pastInjuries']);
    $stmt->bindParam(':allergies', $intakeInfo['allergies']);
    $stmt->bindParam(':icd', $intakeInfo['icd']);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Patient intake info inserted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert patient intake info']);
    }
}

function getPatientIntake($conn, $patient_id) {
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

    // Get patient intake info
    $sql = "SELECT * FROM patientIntake WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $intakeInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($intakeInfo) {
        // Decode JSON strings
        $intakeInfo['painBetter'] = json_decode($intakeInfo['painBetter'], true);
        $intakeInfo['painType'] = json_decode($intakeInfo['painType'], true);

        echo json_encode(['status' => 'success', 'data' => $intakeInfo]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Patient intake info not found']);
    }
}

function updatePatientIntake($conn) {
    $intakeInfo = json_decode(file_get_contents('php://input'), true);
    $patient_id = $intakeInfo['patient_id'];

    // Validate required fields
    $requiredFields = ['chiefComplaint', 'conditionDuration', 'painScale', 'painType', 'siteOfPain'];
    foreach ($requiredFields as $field) {
        if (empty($intakeInfo[$field])) {
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

    // Check if patient intake info exists
    $sql = "SELECT patient_id FROM patientIntake WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $existingInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingInfo) {
        echo json_encode(['status' => 'error', 'message' => 'Patient intake info not found']);
        return;
    }

    // Serialize arrays to JSON strings
    $painBetter = isset($intakeInfo['painBetter']) ? json_encode($intakeInfo['painBetter']) : null;
    $painType = isset($intakeInfo['painType']) ? json_encode($intakeInfo['painType']) : null;

    // Update patient intake info
    $sql = "UPDATE patientIntake SET chiefComplaint = :chiefComplaint, conditionDuration = :conditionDuration, painScale = :painScale, painType = :painType, siteOfPain = :siteOfPain, painBetter = :painBetter, currentMedicalConditions = :currentMedicalConditions, pastMedicalConditions = :pastMedicalConditions, familyHistory = :familyHistory, currentInjuries = :currentInjuries, pastInjuries = :pastInjuries, allergies = :allergies, icd = :icd 
            WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->bindParam(':chiefComplaint', $intakeInfo['chiefComplaint']);
    $stmt->bindParam(':conditionDuration', $intakeInfo['conditionDuration']);
    $stmt->bindParam(':painScale', $intakeInfo['painScale']);
    $stmt->bindParam(':painType', $painType);
    $stmt->bindParam(':siteOfPain', $intakeInfo['siteOfPain']);
    $stmt->bindParam(':painBetter', $painBetter);
    $stmt->bindParam(':currentMedicalConditions', $intakeInfo['currentMedicalConditions']);
    $stmt->bindParam(':pastMedicalConditions', $intakeInfo['pastMedicalConditions']);
    $stmt->bindParam(':familyHistory', $intakeInfo['familyHistory']);
    $stmt->bindParam(':currentInjuries', $intakeInfo['currentInjuries']);
    $stmt->bindParam(':pastInjuries', $intakeInfo['pastInjuries']);
    $stmt->bindParam(':allergies', $intakeInfo['allergies']);
    $stmt->bindParam(':icd', $intakeInfo['icd']);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Patient intake info updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update patient intake info']);
    }
}
?>
