<?php
require_once 'DbConnect.php';

function insertConsentForm($conn) {
    if (!isset($_POST['patient_id']) || !isset($_FILES['consentForm'])) {
        echo json_encode(['status' => 'error', 'message' => 'Patient ID or consent form is missing']);
        return;
    }

    $patient_id = $_POST['patient_id'];
    $consentForm = $_FILES['consentForm'];

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

    // Check if consentForm is uploaded
    if ($consentForm['error'] != UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload consent form']);
        return;
    }

    // Validate PDF file
    $fileType = mime_content_type($consentForm['tmp_name']);
    if ($fileType != 'application/pdf') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only PDF files are allowed']);
        return;
    }

    // Move the file to the uploads directory with patient_id in the file name
    $uploadDir = 'uploads/consent/';
    $fileName = $patient_id . '_' . basename($consentForm['name']);
    $filePath = $uploadDir . $fileName;
    if (!move_uploaded_file($consentForm['tmp_name'], $filePath)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save consent form']);
        return;
    }

    // Insert consent form path
    $sql = "UPDATE patient SET consentForm = :consentForm WHERE id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':consentForm', $filePath);
    $stmt->bindParam(':patient_id', $patient_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Consent form inserted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert consent form']);
    }
}

function getConsentForm($conn, $patient_id) {
    // Check if patient exists
    $sql = "SELECT id, consentForm FROM patient WHERE id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo json_encode(['status' => 'error', 'message' => 'Patient not found']);
        return;
    }

    // Get consent form
    if ($patient['consentForm']) {
        header('Content-Type: application/pdf');
        readfile($patient['consentForm']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Consent form not found']);
    }
}

function deleteConsentForm($conn, $patient_id) {
    // Check if patient exists
    $sql = "SELECT id, consentForm FROM patient WHERE id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo json_encode(['status' => 'error', 'message' => 'Patient not found']);
        return;
    }

    // Delete consent form
    if ($patient['consentForm'] && file_exists($patient['consentForm'])) {
        unlink($patient['consentForm']);
    }
    
    $sql = "UPDATE patient SET consentForm = NULL WHERE id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Consent form deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete consent form']);
    }
}

?>
