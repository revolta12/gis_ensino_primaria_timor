<?php
// =============================================
// API Avaliasaun Escola - GIS Ensino Primaria Timor-Leste
// =============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';
require_once '../includes/functions.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $escola_id = (int)($data['escola_id'] ?? 0);
    $naran = sanitize($data['naran'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $rating = (int)($data['rating'] ?? 0);
    $komentar = sanitize($data['komentar'] ?? '');
    
    // Validasaun
    if ($escola_id <= 0 || empty($naran) || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Dadus la kompletu. Favor prense naran no rating 1-5.']);
        exit();
    }
    
    // Verifika se escola existente no ativu
    $stmt = $db->prepare("SELECT id, naran_escola FROM escola WHERE id = ? AND aktivo = 1");
    $stmt->execute([$escola_id]);
    $escola = $stmt->fetch();
    
    if (!$escola) {
        echo json_encode(['success' => false, 'message' => 'Escola la hetan ka la ativu.']);
        exit();
    }
    
    // Insert avaliasaun
    $stmt = $db->prepare("
        INSERT INTO avaliasaun_escola (escola_id, naran_avaliador, email_avaliador, pontuasaun, komentariu, aprovadu) 
        VALUES (?, ?, ?, ?, ?, 0)
    ");
    $result = $stmt->execute([$escola_id, $naran, $email, $rating, $komentar]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Obrigado! Avaliasaun ba "' . htmlspecialchars($escola['naran_escola']) . '" hetan ona. Sei hola parte liu husi inspeksaun.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Falha iha guarda avaliasaun. Favor halo di\'ak tan.']);
    }
} else {
    // Method GET - optional: return info
    echo json_encode([
        'success' => false, 
        'message' => 'Method GET la suporta. Uza POST atu submite avaliasaun.',
        'required_fields' => ['escola_id', 'naran', 'email', 'rating', 'komentar']
    ]);
}
?>