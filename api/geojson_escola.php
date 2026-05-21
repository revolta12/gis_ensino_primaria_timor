<?php
// =============================================
// API GeoJSON - GIS Ensino Primaria Timor-Leste
// =============================================
// Endpoint ba exporta dadus escola iha format GeoJSON
// Uza ba Leaflet/OpenLayers map

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';
require_once '../includes/functions.php';

$db = getDB();

// Optional filter parameters
$kategoria_id = isset($_GET['kategoria']) ? (int)$_GET['kategoria'] : 0;
$iha_bee_moos = isset($_GET['bee_moos']) ? (int)$_GET['bee_moos'] : 0;
$municipio = isset($_GET['municipio']) ? sanitize($_GET['municipio']) : '';

// Build query with filters
$where = ["e.aktivo = 1"];
$params = [];

if ($kategoria_id > 0) {
    $where[] = "e.kategoria_id = ?";
    $params[] = $kategoria_id;
}

if ($iha_bee_moos == 1) {
    $where[] = "e.iha_bee_moos = 1";
} elseif ($iha_bee_moos == 2) {
    $where[] = "e.iha_bee_moos = 0";
}

if (!empty($municipio)) {
    $where[] = "e.municipio = ?";
    $params[] = $municipio;
}

$where_sql = "WHERE " . implode(" AND ", $where);

$query = "
    SELECT e.id, e.naran_escola, e.slug, e.enderesu, e.suku, e.postu_administrativu, e.municipio,
           e.latitude, e.longitude, e.total_estudante, e.total_profesor,
           e.avaliasaun, e.total_avaliasaun, e.foto_prinsipal, e.destakadu,
           e.iha_bee_moos, e.iha_eletrisidade, e.iha_toilet,
           k.naran_kategoria
    FROM escola e
    LEFT JOIN kategoria_escola k ON e.kategoria_id = k.id
    $where_sql
    ORDER BY e.destakadu DESC, e.total_estudante DESC, e.naran_escola ASC
";

$stmt = $db->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$features = [];

foreach ($rows as $e) {
    // Determine marker color based on facilities
    $marker_color = '#2C3E50'; // default blue
    if (!$e['iha_bee_moos']) {
        $marker_color = '#E74C3C'; // red for no water
    } elseif ($e['iha_eletrisidade'] && $e['iha_toilet']) {
        $marker_color = '#27AE60'; // green for complete facilities
    } elseif ($e['destakadu']) {
        $marker_color = '#F39C12'; // orange for featured
    }
    
    // Generate marker icon HTML for Leaflet
    $marker_html = '<div style="background:' . $marker_color . ';width:32px;height:32px;border-radius:50%;border:2px solid #FFD700;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.3);">
                        <i class="fas fa-school" style="color:#fff;font-size:14px;"></i>
                    </div>';
    
    $features[] = [
        'type' => 'Feature',
        'geometry' => [
            'type' => 'Point',
            'coordinates' => [(float)$e['longitude'], (float)$e['latitude']]
        ],
        'properties' => [
            'id' => $e['id'],
            'naran_escola' => $e['naran_escola'],
            'slug' => $e['slug'],
            'enderesu' => $e['enderesu'],
            'suku' => $e['suku'],
            'postu_administrativu' => $e['postu_administrativu'],
            'municipio' => $e['municipio'],
            'total_estudante' => (int)$e['total_estudante'],
            'total_profesor' => (int)$e['total_profesor'],
            'avaliasaun' => (float)$e['avaliasaun'],
            'total_avaliasaun' => (int)$e['total_avaliasaun'],
            'foto' => $e['foto_prinsipal'] ? BASE_URL . '/' . $e['foto_prinsipal'] : BASE_URL . '/assets/img/escola-placeholder.jpg',
            'kategoria' => $e['naran_kategoria'],
            'destakadu' => (bool)$e['destakadu'],
            'iha_bee_moos' => (bool)$e['iha_bee_moos'],
            'iha_eletrisidade' => (bool)$e['iha_eletrisidade'],
            'iha_toilet' => (bool)$e['iha_toilet'],
            'url' => BASE_URL . '/escola/' . $e['slug'],
            'marker_color' => $marker_color,
            'marker_html' => $marker_html,
            // Additional info for popup
            'popup_content' => '
                <div style="min-width:200px;">
                    <div style="font-weight:700;font-size:14px;margin-bottom:5px;">' . htmlspecialchars($e['naran_escola']) . '</div>
                    <div style="font-size:12px;color:#666;margin-bottom:5px;">
                        <i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($e['suku'] ?: $e['postu_administrativu'] ?: $e['municipio']) . '
                    </div>
                    <div style="font-size:12px;margin-bottom:5px;">
                        <i class="fas fa-users"></i> Estudante: ' . number_format($e['total_estudante']) . '
                    </div>
                    <div style="font-size:12px;margin-bottom:5px;">
                        <i class="fas fa-chalkboard-user"></i> Profesor: ' . number_format($e['total_profesor']) . '
                    </div>
                    <div style="display:flex;gap:8px;margin-bottom:8px;">
                        ' . ($e['iha_bee_moos'] ? '<span style="background:#27AE60;color:#fff;padding:2px 6px;border-radius:4px;font-size:10px;"><i class="fas fa-water"></i> Bee</span>' : '<span style="background:#E74C3C;color:#fff;padding:2px 6px;border-radius:4px;font-size:10px;">La iha bee</span>') . '
                        ' . ($e['iha_eletrisidade'] ? '<span style="background:#27AE60;color:#fff;padding:2px 6px;border-radius:4px;font-size:10px;"><i class="fas fa-plug"></i> Luz</span>' : '') . '
                        ' . ($e['iha_toilet'] ? '<span style="background:#27AE60;color:#fff;padding:2px 6px;border-radius:4px;font-size:10px;"><i class="fas fa-toilet"></i> Toilet</span>' : '') . '
                    </div>
                    <a href="' . BASE_URL . '/escola/' . $e['slug'] . '" style="display:inline-block;background:#2C3E50;color:#fff;padding:5px 12px;border-radius:6px;font-size:12px;text-decoration:none;">Haree Detalhe <i class="fas fa-arrow-right"></i></a>
                </div>
            '
        ]
    ];
}

// Return GeoJSON FeatureCollection
echo json_encode([
    'type' => 'FeatureCollection',
    'total' => count($features),
    'generated_at' => date('Y-m-d H:i:s'),
    'features' => $features
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>