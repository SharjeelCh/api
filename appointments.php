<?php
require_once 'DbConnect.php';
function createAppointment($conn) {
    $appointment = json_decode(file_get_contents('php://input'), true);
    $user_id = $appointment['user_id'];

    // Check if user exists
    $sql = "SELECT is_veteran FROM patient WHERE id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        return;
    }

    $is_veteran = $user['is_veteran'];

    // Get the count of existing appointments
    $sql = "SELECT COUNT(*) AS appointment_count FROM appointments WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $appointment_count = $result['appointment_count'];

    // Appointment limit check
    if (($is_veteran && $appointment_count >= 3) || (!$is_veteran && $appointment_count >= 1)) {
        echo json_encode(['status' => 'error', 'message' => 'Appointment limit reached']);
        return;
    }

    // Determine appointment type and patient type
    $sql = "SELECT appointment_type, patient_type FROM appointments WHERE user_id = :user_id ORDER BY appointment_date DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $last_appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$last_appointment) {
        $appointment_type = 'New Patient';
        $patient_type = 'Other';
    } else {
        if ($last_appointment['appointment_type'] == 'New Patient') {
            $appointment_type = 'Regular Appt';
        } else {
            $appointment_type = $last_appointment['appointment_type'];
        }
        $patient_type = isset($appointment['patient_type']) ? $appointment['patient_type'] : $last_appointment['patient_type'];
    }

    // Insert new appointment
    $sql = "INSERT INTO appointments (user_id, phone, appointment_time, provider, appointment_type, patient_type, appointment_notes, address, appointment_date) 
            VALUES (:user_id, :phone, :appointment_time, :provider, :appointment_type, :patient_type, :appointment_notes, :address, :appointment_date)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':phone', $appointment['phone']);
    $stmt->bindParam(':appointment_time', $appointment['appointment_time']);
    $stmt->bindParam(':provider', $appointment['provider']);
    $stmt->bindParam(':appointment_type', $appointment_type);
    $stmt->bindParam(':patient_type', $patient_type);
    $stmt->bindParam(':appointment_notes', $appointment['appointment_notes']);
    $stmt->bindParam(':address', $appointment['address']);
    $stmt->bindParam(':appointment_date', $appointment['appointment_date']);

    if ($stmt->execute()) {
        // Update the user to veteran status if they have more than 1 appointment
        $sql = "UPDATE patient SET is_veteran = 1 WHERE id = :user_id AND (SELECT COUNT(*) FROM appointments WHERE user_id = :user_id) > 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'Appointment created successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create appointment']);
    }
}



function getAppointment($conn, $user_id) {
    $sql = "SELECT * FROM appointments WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($appointments) {
        echo json_encode(['status' => 'success', 'appointments' => $appointments]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Appointments not found']);
    }
}

function deleteAppointment($conn) {
    $input = file_get_contents('php://input');
    $user = json_decode($input);

    if (isset($user->user_id) && isset($user->id)) {
        $sql = "DELETE FROM appointments WHERE user_id = :user_id AND id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
        $stmt->bindParam(':id', $user->id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Appointment deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete appointment']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    }
}


function updateAppointment($conn, $id) {
    $appointment = json_decode(file_get_contents('php://input'), true);

    $sql = "UPDATE appointments SET 
            phone = :phone, 
            appointment_time = :appointment_time, 
            provider = :provider, 
            appointment_type = :appointment_type, 
            appointment_notes = :appointment_notes, 
            address = :address, 
            appointment_date = :appointment_date
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':phone', $appointment['phone']);
    $stmt->bindParam(':appointment_time', $appointment['appointment_time']);
    $stmt->bindParam(':provider', $appointment['provider']);
    $stmt->bindParam(':appointment_type', $appointment['appointment_type']);
    $stmt->bindParam(':appointment_notes', $appointment['appointment_notes']);
    $stmt->bindParam(':address', $appointment['address']);
    $stmt->bindParam(':appointment_date', $appointment['appointment_date']);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Appointment updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update appointment']);
    }
}
?>