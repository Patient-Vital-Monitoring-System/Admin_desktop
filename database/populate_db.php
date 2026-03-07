<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "vitalwear";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Populating VitalWear Database...</h2>";

/* =========================
   MANAGEMENT
========================= */
$conn->query("
INSERT INTO management (mgmt_name, mgmt_email, mgmt_password) VALUES
('Operations Manager', 'ops@vitalwear.com', MD5('manager123')),
('Field Manager', 'field@vitalwear.com', MD5('manager123'))
");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('System','admin','create_user','management','Management accounts created')
");

/* =========================
   ADMIN
========================= */
$conn->query("
INSERT INTO admin (admin_name, admin_email, admin_password) VALUES
('System Admin', 'admin1@vitalwear.com', MD5('admin123')),
('Audit Admin', 'admin2@vitalwear.com', MD5('admin123'))
");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('System','admin','create_user','admin','Admin accounts created')
");

/* =========================
   RESPONDERS
========================= */
$conn->query("
INSERT INTO responder (resp_name, resp_email, resp_password, resp_contact) VALUES
('Juan Dela Cruz', 'juan@responder.com', MD5('resp123'), '09123456789'),
('Mark Villanueva', 'mark@responder.com', MD5('resp123'), '09112223344'),
('Leo Ramirez', 'leo@responder.com', MD5('resp123'), '09223334455')
");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('System Admin','admin','create_user','responder','Responder accounts created')
");

/* =========================
   RESCUERS
========================= */
$conn->query("
INSERT INTO rescuer (resc_name, resc_email, resc_password, resc_contact) VALUES
('Maria Santos', 'maria@rescuer.com', MD5('resc123'), '09987654321'),
('Ana Lopez', 'ana@rescuer.com', MD5('resc123'), '09887776655')
");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('System Admin','admin','create_user','rescuer','Rescuer accounts created')
");

/* =========================
   DEVICES
========================= */
$conn->query("
INSERT INTO device (dev_serial) VALUES
('DEV-001'),
('DEV-002'),
('DEV-003'),
('DEV-004')
");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('Inventory System','admin','register_device','device','New monitoring devices registered')
");

/* =========================
   DEVICE ASSIGNMENTS
========================= */
$conn->query("
INSERT INTO device_log (dev_id, resp_id, mgmt_id) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 2)
");

$conn->query("UPDATE device SET dev_status='assigned' WHERE dev_id IN (1,2,3)");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('Operations Manager','management','assign_device','device','Devices assigned to responders')
");

/* =========================
   PATIENTS
========================= */
$conn->query("
INSERT INTO patient (pat_name, birthdate, contact_number) VALUES
('Pedro Reyes', '1995-06-15', '09001112222'),
('Carlos Mendoza', '1988-02-10', '09110002233'),
('Ramon Torres', '1975-09-25', '09224445566')
");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('Responder','responder','register_patient','patient','Patient profiles created during incidents')
");

/* =========================
   INCIDENTS
========================= */
$conn->query("
INSERT INTO incident (log_id, pat_id, resp_id) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3)
");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('Responders','responder','create_incident','incident','New emergency incidents recorded')
");

/* =========================
   RESPONDER VITALS
========================= */
$conn->query("
INSERT INTO vitalstat 
(incident_id, recorded_by, bp_systolic, bp_diastolic, heart_rate, oxygen_level) VALUES
(1, 'responder', 120, 80, 75, 98),
(2, 'responder', 135, 85, 88, 96),
(3, 'responder', 140, 90, 95, 94)
");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('Responder','responder','record_vitals','vital_monitoring','Patient vital signs recorded by responders')
");

/* =========================
   TRANSFER TO RESCUER
========================= */
$conn->query("
UPDATE incident SET resc_id = 1, status = 'transferred' WHERE incident_id = 1
");

$conn->query("
UPDATE incident SET resc_id = 2, status = 'transferred' WHERE incident_id = 2
");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('Responder','responder','transfer_incident','incident','Incidents transferred to rescuers')
");

/* =========================
   RESCUER VITALS
========================= */
$conn->query("
INSERT INTO vitalstat 
(incident_id, recorded_by, bp_systolic, bp_diastolic, heart_rate, oxygen_level) VALUES
(1, 'rescuer', 118, 78, 72, 99),
(2, 'rescuer', 130, 82, 85, 97)
");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('Rescuer','rescuer','update_vitals','vital_monitoring','Rescuers updated patient vital signs')
");

/* =========================
   COMPLETE INCIDENTS
========================= */
$conn->query("
UPDATE incident 
SET status='completed', end_time=NOW()
WHERE incident_id IN (1,2)
");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('Rescuer','rescuer','complete_incident','incident','Emergency incidents successfully completed')
");

/* =========================
   DEVICE RETURNS
========================= */
$conn->query("
UPDATE device_log 
SET date_returned=NOW(), verified_return=TRUE
WHERE log_id IN (1,2)
");

$conn->query("
UPDATE device 
SET dev_status='available'
WHERE dev_id IN (1,2)
");

$conn->query("
INSERT INTO activity_log (user_name,user_role,action_type,module,description) VALUES
('Operations Manager','management','return_device','device','Devices returned and verified')
");

echo "<h3 style='color:green;'>Database successfully populated!</h3>";

$conn->close();
?>