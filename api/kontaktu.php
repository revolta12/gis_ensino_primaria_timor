<?php
// =============================================
// API Kontaktu Mensajen - GIS Ensino Primaria Timor-Leste
// =============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';
require_once '../includes/functions.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $naran = sanitize($data['naran'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $asuntu = sanitize($data['asuntu'] ?? '');
    $pesan = sanitize($data['mensajen'] ?? '');
    
    // Validasaun
    if (empty($naran) || empty($email) || empty($pesan)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Favor prense naran, email, no mensajen.',
            'required_fields' => ['naran', 'email', 'mensajen']
        ]);
        exit();
    }
    
    // Validasaun email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Email format la validu. Uza ezemplu: naran@domain.com'
        ]);
        exit();
    }
    
    // Insert mensajen
    $stmt = $db->prepare("
        INSERT INTO kontaktu_mensajen (naran_ema, email_ema, asuntu, mensajen, lee_ona) 
        VALUES (?, ?, ?, ?, 0)
    ");
    $result = $stmt->execute([$naran, $email, $asuntu, $pesan]);
    
    if ($result) {
        // Optional: send email notification to admin
        // mail($admin_email, "Nova mensajen kontaktu - GIS Ensino Primaria", "Husi: $naran ($email)\nAsuntu: $asuntu\nMensajen: $pesan");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Mensajen kontaktu konsege haruka! Obrigado barak. Ita sei resposta lalais liu.'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Falha haruka mensajen. Favor halo di\'ak tan ka kontaktu diretamente liu telefone.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Method GET la suporta. Uza POST atu haruka mensajen kontaktu.',
        'allowed_methods' => ['POST'],
        'required_fields' => ['naran', 'email', 'mensajen'],
        'optional_fields' => ['asuntu']
    ]);
}
?>