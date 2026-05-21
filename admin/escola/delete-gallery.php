<?php
// =============================================
// Delete Foto Galeri Escola - GIS Ensino Primaria Timor-Leste
// =============================================

require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

checkAdminLogin();

$id = (int)$_GET['id'];
$escola_id = (int)$_GET['escola_id'];

if ($id > 0) {
    $db = getDB();
    
    // Optional: delete physical file from server
    $stmt = $db->prepare("SELECT naran_fail FROM foto_escola WHERE id = ?");
    $stmt->execute([$id]);
    $foto = $stmt->fetch();
    
    if ($foto && !empty($foto['naran_fail']) && file_exists('../../' . $foto['naran_fail'])) {
        unlink('../../' . $foto['naran_fail']);
    }
    
    $stmt = $db->prepare("DELETE FROM foto_escola WHERE id = ?");
    $stmt->execute([$id]);
    setFlashMessage('success', 'Foto galeri escola delete ona!');
}

redirect('/admin/escola/edit.php?id=' . $escola_id);
exit();
?>