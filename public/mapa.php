<?php
// =============================================
// Mapa Interativu - GIS Ensino Primaria Timor-Leste
// =============================================
// Página mapa fullscreen ba eskola primaria

require_once '../config/database.php';
require_once '../includes/functions.php';

$includeMap = true;
$db = getDB();

// Get all categories for filter
$categories = $db->query("SELECT DISTINCT naran_kategoria FROM kategoria_escola ORDER BY naran_kategoria")->fetchAll();

include_once '../includes/header.php';
?>

<style>
    body {
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
    
    #fullscreenMap {
        position: fixed;
        top: 76px;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
    }
    
    /* Control Panel */
    .map-control-panel {
        position: absolute;
        top: 90px;
        left: 20px;
        z-index: 1000;
        background: white;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        width: 340px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
        backdrop-filter: blur(0px);
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .map-control-panel .panel-header {
        padding: 15px 20px;
        background: linear-gradient(135deg, #2C3E50, #1A252F);
        color: white;
        border-radius: 16px 16px 0 0;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .map-control-panel .panel-header:hover {
        background: linear-gradient(135deg, #1A252F, #0f1a24);
    }
    
    .map-control-panel .panel-body {
        padding: 20px;
        max-height: 450px;
        overflow-y: auto;
    }
    
    .map-control-panel .panel-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .map-control-panel .panel-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .map-control-panel .panel-body::-webkit-scrollbar-thumb {
        background: #F39C12;
        border-radius: 10px;
    }
    
    /* Search Box */
    .search-box-map {
        position: relative;
        margin-bottom: 20px;
    }
    
    .search-box-map i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        z-index: 1;
    }
    
    .search-box-map input {
        padding-left: 40px;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        padding: 12px 15px 12px 40px;
        width: 100%;
        transition: all 0.3s ease;
    }
    
    .search-box-map input:focus {
        border-color: #F39C12;
        box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1);
        outline: none;
    }
    
    /* Category Filters */
    .category-group {
        margin-bottom: 20px;
    }
    
    .category-group strong {
        display: block;
        margin-bottom: 12px;
        color: #333;
        font-size: 14px;
    }
    
    .filter-checkbox {
        margin-bottom: 10px;
    }
    
    .filter-checkbox input {
        accent-color: #F39C12;
        margin-right: 8px;
    }
    
    .filter-checkbox label {
        font-size: 13px;
        color: #555;
        cursor: pointer;
    }
    
    /* School List */
    .school-list-title {
        font-weight: 700;
        margin-bottom: 12px;
        color: #333;
        font-size: 14px;
        display: block;
    }
    
    .school-list-item-map {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 10px;
    }
    
    .school-list-item-map:hover {
        background: rgba(243, 156, 18, 0.1);
        transform: translateX(5px);
    }
    
    .school-list-item-map img {
        width: 55px;
        height: 55px;
        object-fit: cover;
        border-radius: 10px;
    }
    
    .school-list-item-map .school-info {
        flex: 1;
    }
    
    .school-list-item-map .school-name {
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 4px;
        color: #1a1a1a;
    }
    
    .school-list-item-map .school-location {
        font-size: 11px;
        color: #999;
    }
    
    .school-list-item-map .school-rating {
        margin-top: 4px;
        font-size: 11px;
    }
    
    .school-list-item-map .water-badge {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 8px;
    }
    
    .water-badge.yes {
        background: rgba(39, 174, 96, 0.1);
        color: #27AE60;
    }
    
    .water-badge.no {
        background: rgba(231, 76, 60, 0.1);
        color: #E74C3C;
    }
    
    /* My Location Button */
    .btn-my-location {
        position: absolute;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        background: white;
        border: none;
        border-radius: 50%;
        width: 55px;
        height: 55px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2C3E50;
        font-size: 22px;
    }
    
    .btn-my-location:hover {
        background: #2C3E50;
        color: #F39C12;
        transform: scale(1.05);
    }

    .info-card-panel {
        position: absolute;
        top: 90px;
        right: 20px;
        z-index: 1000;
        width: 320px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
        background: rgba(255,255,255,0.95);
        border-radius: 18px;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 18px 40px rgba(0,0,0,0.18);
        padding: 0;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .info-card-panel.hidden {
        opacity: 0;
        transform: translateX(20px);
        pointer-events: none;
    }

    .info-card-panel .info-header {
        padding: 18px 20px;
        background: linear-gradient(135deg, #2C3E50, #1A252F);
        color: white;
        border-radius: 18px 18px 0 0;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .info-card-panel .info-header .close-info {
        cursor: pointer;
        color: rgba(255,255,255,0.8);
        font-size: 14px;
    }

    .info-card-panel .info-body {
        padding: 18px 20px 22px;
    }

    .info-card-panel .info-body img {
        width: 100%;
        height: 170px;
        object-fit: cover;
        border-radius: 14px;
        margin-bottom: 14px;
    }

    .info-card-panel .info-body h5 {
        margin-bottom: 8px;
        font-size: 17px;
        color: #1a1a1a;
    }

    .info-card-panel .info-body p {
        margin-bottom: 10px;
        color: #555;
        font-size: 14px;
        line-height: 1.5;
    }

    .info-card-panel .info-body .badge {
        margin-right: 6px;
        margin-bottom: 8px;
    }

    .info-card-panel .info-body .facility-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
    }

    .info-card-panel .info-body .facility-badge span {
        background: #eef7ff;
        color: #1a4d8f;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
    }

    .info-card-panel .info-actions {
        display: grid;
        gap: 10px;
        margin-top: 14px;
    }

    .info-card-panel .info-actions .btn {
        width: 100%;
    }
    
    /* Leaflet Custom */
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .map-control-panel {
            width: 300px;
            top: 80px;
            left: 10px;
            max-height: 60vh;
        }
        .map-control-panel.collapsed .panel-body {
            display: none;
        }
        #fullscreenMap {
            top: 70px;
        }
        .btn-my-location {
            width: 45px;
            height: 45px;
            font-size: 18px;
            bottom: 15px;
            right: 15px;
        }
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
</style>

<div id="fullscreenMap"></div>

<!-- Control Panel -->
<div class="map-control-panel" id="controlPanel">
    <div class="panel-header" onclick="togglePanel()">
        <i class="fas fa-sliders-h me-2"></i> Filtru Eskola
        <i class="fas fa-chevron-up float-end" id="panelToggleIcon"></i>
    </div>
    <div class="panel-body" id="panelBody">
        <div class="search-box-map">
            <i class="fas fa-search"></i>
            <input type="text" id="searchSchool" class="form-control" placeholder="Buka eskola...">
        </div>
        
        <div class="category-group">
            <strong><i class="fas fa-tag me-1"></i> Kategoria:</strong>
            <div id="categoryFilters">
                <div class="filter-checkbox">
                    <input type="checkbox" class="form-check-input" id="cat_all" value="all" checked onchange="filterSchools()">
                    <label class="form-check-label">Hotu</label>
                </div>
                <?php foreach ($categories as $cat): ?>
                <div class="filter-checkbox">
                    <input type="checkbox" class="form-check-input category-filter" value="<?= htmlspecialchars($cat['naran_kategoria']) ?>" onchange="filterSchools()">
                    <label class="form-check-label"><?= htmlspecialchars($cat['naran_kategoria']) ?></label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="category-group">
            <strong><i class="fas fa-water me-1"></i> Fasilidade:</strong>
            <div class="filter-checkbox">
                <input type="checkbox" id="filter_water" onchange="filterSchools()">
                <label>Hanya eskola ne'ebé iha bee moos</label>
            </div>
            <div class="filter-checkbox">
                <input type="checkbox" id="filter_electricity" onchange="filterSchools()">
                <label>Hanya eskola ne'ebé iha eletrisidade</label>
            </div>
        </div>
        
        <hr>
        
        <div>
            <strong class="school-list-title"><i class="fas fa-school me-1"></i> Lista Eskola:</strong>
            <div id="schoolListPanel" style="max-height: 350px; overflow-y: auto;">
                <div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Carrega dadus...</div>
            </div>
        </div>
    </div>
</div>
<button class="btn-my-location" onclick="goToMyLocation()" title="Ha'u nia fatin">
    <i class="fas fa-location-dot"></i>
</button>

<script>
    let fullMap;
    let markersCluster;
    let allSchools = [];
    let schoolMarkers = [];
    let currentRouteLayer = null;
    
    function jsStringEscape(str) {
        return String(str)
            .replace(/\\/g, '\\\\')
            .replace(/'/g, "\\'")
            .replace(/"/g, '\\"')
            .replace(/\n/g, '\\n')
            .replace(/\r/g, '\\r');
    }
    
    // Initialize fullscreen map
    function initFullMap() {
        fullMap = L.map('fullscreenMap').setView([-8.559, 125.579], 15);
        
        // Base layers
        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        });
        
        const satelliteLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '© Google Satellite'
        });
        
        osmLayer.addTo(fullMap);
        
        // Layer control
        const baseMaps = {
            "🗺️ Mapa Rua": osmLayer,
            "🛰️ Satélite": satelliteLayer
        };
        L.control.layers(baseMaps).addTo(fullMap);
        
        // Zoom control
        L.control.zoom({ position: 'topright' }).addTo(fullMap);
        
        // Scale bar
        L.control.scale({ metric: true, imperial: false, position: 'bottomleft' }).addTo(fullMap);
        
        // Custom marker cluster
        markersCluster = L.markerClusterGroup({
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            iconCreateFunction: function(cluster) {
                const count = cluster.getChildCount();
                let color = '#2C3E50';
                if (count > 30) color = '#E74C3C';
                else if (count > 15) color = '#F39C12';
                else color = '#27AE60';
                
                return L.divIcon({
                    html: '<div style="background:' + color + '; color:white; border-radius:50%; width:40px; height:40px; display:flex; align-items:center; justify-content:center; border:2px solid #F39C12; font-weight:bold;">' + count + '</div>',
                    iconSize: [40, 40],
                    className: 'custom-cluster'
                });
            }
        });
        fullMap.addLayer(markersCluster);
        
        loadAllSchools();
    }
    
    async function loadAllSchools() {
        try {
            const response = await fetch(BASE_URL + '/api/geojson_escola.php');
            const data = await response.json();
            allSchools = data.features || [];
            renderAllMarkers();
            updateSchoolList();
        } catch (error) {
            console.error('Error loading schools:', error);
            document.getElementById('schoolListPanel').innerHTML = '<div class="text-center text-danger py-3"><i class="fas fa-exclamation-circle"></i> Error carrega dadus eskola</div>';
        }
    }
    
    function renderAllMarkers() {
        markersCluster.clearLayers();
        
        const selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked')).map(cb => cb.value);
        const allSelected = document.getElementById('cat_all').checked;
        const searchTerm = document.getElementById('searchSchool').value.toLowerCase();
        const filterWater = document.getElementById('filter_water').checked;
        const filterElectricity = document.getElementById('filter_electricity').checked;
        
        let filtered = allSchools;
        
        // Filter by category
        if (!allSelected && selectedCategories.length > 0) {
            filtered = filtered.filter(school => selectedCategories.includes(school.properties.kategoria));
        }
        
        // Filter by search
        if (searchTerm) {
            filtered = filtered.filter(school => 
                school.properties.naran_escola.toLowerCase().includes(searchTerm) ||
                (school.properties.suku && school.properties.suku.toLowerCase().includes(searchTerm)) ||
                (school.properties.municipio && school.properties.municipio.toLowerCase().includes(searchTerm))
            );
        }
        
        // Filter by facilities
        if (filterWater) {
            filtered = filtered.filter(school => school.properties.iha_bee_moos === true);
        }
        if (filterElectricity) {
            filtered = filtered.filter(school => school.properties.iha_eletrisidade === true);
        }
        
        filtered.forEach(school => {
            const [lng, lat] = school.geometry.coordinates;
            const p = school.properties;
            
            // Determine marker color based on facilities
            let markerColor = '#2C3E50';
            let icon = 'fa-school';
            
            if (!p.iha_bee_moos) {
                markerColor = '#E74C3C';
                icon = 'fa-exclamation-triangle';
            } else if (p.iha_eletrisidade && p.iha_toilet) {
                markerColor = '#27AE60';
                icon = 'fa-check-circle';
            } else if (p.destakadu) {
                markerColor = '#F39C12';
                icon = 'fa-star';
            }
            
            const popupContent = `
                <div style="width: 300px;">
                    <img src="${p.foto}" style="width:100%; height:140px; object-fit:cover; border-radius:12px 12px 0 0" onerror="this.src='${BASE_URL}/assets/img/escola-placeholder.jpg'">
                    <div class="p-3">
                        <h6 class="fw-bold mb-1" style="color:#2C3E50;">${escapeHtml(p.naran_escola)}</h6>
                        <span class="badge bg-secondary mb-2">${escapeHtml(p.kategoria || 'Eskola')}</span>
                        <p class="small mb-2"><i class="fas fa-map-marker-alt text-danger"></i> ${escapeHtml(p.suku || p.postu_administrativu || p.municipio)}</p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="fas fa-users"></i> ${formatNumber(p.total_estudante)} estudante</span>
                            <span><i class="fas fa-chalkboard-user"></i> ${formatNumber(p.total_profesor)} professor</span>
                        </div>
                        <div class="mb-2">
                            ${p.iha_bee_moos ? '<span class="badge bg-success me-1"><i class="fas fa-water"></i> Bee</span>' : '<span class="badge bg-danger me-1"><i class="fas fa-times"></i> La iha bee</span>'}
                            ${p.iha_eletrisidade ? '<span class="badge bg-success me-1"><i class="fas fa-plug"></i> Luz</span>' : ''}
                            ${p.iha_toilet ? '<span class="badge bg-success me-1"><i class="fas fa-toilet"></i> Toilet</span>' : ''}
                        </div>
                        <div class="d-flex gap-2">
                            <a href="${p.url}" class="btn btn-sm btn-primary flex-grow-1" target="_blank">Haree Detallu</a>
                            <button class="btn btn-sm btn-outline-primary" onclick="openGoogleMapsFromGeo(${lat}, ${lng}); return false;" title="Buka iha Google Maps">
                                <i class="fab fa-google"></i> Google Maps
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            const customIcon = L.divIcon({
                html: `<div style="background:${markerColor}; width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid #F39C12; box-shadow:0 2px 8px rgba(0,0,0,0.2);"><i class="fas ${icon}" style="color:white; font-size:16px;"></i></div>`,
                iconSize: [38, 38],
                popupAnchor: [0, -19],
                className: 'custom-marker'
            });
            
            const marker = L.marker([lat, lng], { icon: customIcon });
            marker.bindPopup(popupContent);
            marker.on('click', function() {
                showSchoolInfoCard(school);
            });
            markersCluster.addLayer(marker);
            schoolMarkers.push({ lat, lng, marker, school });
        });
    }
    
    function showSchoolInfoCard(school) {
        const p = school.properties;
        const infoCard = document.getElementById('infoCardBody');
        infoCard.innerHTML = `
            <img src="${p.foto}" alt="${escapeHtml(p.naran_escola)}" onerror="this.src='${BASE_URL}/assets/img/escola-placeholder.jpg'">
            <h5>${escapeHtml(p.naran_escola)}</h5>
            <span class="badge bg-secondary">${escapeHtml(p.kategoria || 'Eskola')}</span>
            <p><i class="fas fa-map-marker-alt"></i> ${escapeHtml(p.suku || p.postu_administrativu || p.municipio)}</p>
            <p><strong>Estudante:</strong> ${formatNumber(p.total_estudante)}<br><strong>Professor:</strong> ${formatNumber(p.total_profesor)}</p>
            <div class="facility-badge">
                <span>${p.iha_bee_moos ? '<i class="fas fa-water"></i> Bee' : '<i class="fas fa-times"></i> La iha bee'}</span>
            </div>
            <div class="facility-badge">
                <span>${p.iha_eletrisidade ? '<i class="fas fa-plug"></i> Luz' : '<i class="fas fa-times"></i> La iha luz'}</span>
            </div>
            ${p.iha_toilet ? '<div class="facility-badge"><span><i class="fas fa-toilet"></i> Toilet</span></div>' : ''}
            <div class="info-actions">
                <a href="${p.url}" class="btn btn-primary" target="_blank">Haree Detallu</a>
            </div>
        `;
        document.getElementById('infoCardPanel').classList.remove('hidden');
    }

    function hideSchoolInfoCard() {
        document.getElementById('infoCardPanel').classList.add('hidden');
    }

    function updateSchoolList() {
        const container = document.getElementById('schoolListPanel');
        const selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked')).map(cb => cb.value);
        const allSelected = document.getElementById('cat_all').checked;
        const searchTerm = document.getElementById('searchSchool').value.toLowerCase();
        const filterWater = document.getElementById('filter_water').checked;
        const filterElectricity = document.getElementById('filter_electricity').checked;
        
        let filtered = allSchools;
        
        if (!allSelected && selectedCategories.length > 0) {
            filtered = filtered.filter(school => selectedCategories.includes(school.properties.kategoria));
        }
        
        if (searchTerm) {
            filtered = filtered.filter(school => 
                school.properties.naran_escola.toLowerCase().includes(searchTerm) ||
                (school.properties.suku && school.properties.suku.toLowerCase().includes(searchTerm)) ||
                (school.properties.municipio && school.properties.municipio.toLowerCase().includes(searchTerm))
            );
        }
        
        if (filterWater) {
            filtered = filtered.filter(school => school.properties.iha_bee_moos === true);
        }
        if (filterElectricity) {
            filtered = filtered.filter(school => school.properties.iha_eletrisidade === true);
        }
        
        if (filtered.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-school fa-2x mb-2 d-block"></i>La iha eskola ne\'ebé kombina ho filter</div>';
            return;
        }
        
        container.innerHTML = filtered.map(school => {
            const [lng, lat] = school.geometry.coordinates;
            const p = school.properties;
            const stars = renderStarsText(p.avaliasaun);
            const waterBadge = p.iha_bee_moos ? 
                '<span class="water-badge yes"><i class="fas fa-check-circle"></i> Bee</span>' : 
                '<span class="water-badge no"><i class="fas fa-times-circle"></i> La iha bee</span>';
            
            return `
                <div class="school-list-item-map" onclick="zoomToSchool(${lat}, ${lng})">
                    <img src="${p.foto}" alt="${escapeHtml(p.naran_escola)}" loading="lazy" onerror="this.src='${BASE_URL}/assets/img/escola-placeholder.jpg'">
                    <div class="school-info">
                        <div class="school-name">${escapeHtml(p.naran_escola)} ${waterBadge}</div>
                        <div class="school-location"><i class="fas fa-map-marker-alt"></i> ${escapeHtml(p.suku || p.postu_administrativu || p.municipio)}</div>
                        <div class="school-rating">${stars} <span class="text-muted">(${p.total_avaliasaun})</span></div>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    function renderStarsText(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += i <= rating ? '<i class="fas fa-star" style="color:#F39C12; font-size:11px;"></i>' : '<i class="far fa-star" style="color:#F39C12; font-size:11px;"></i>';
        }
        return stars;
    }
    
    function formatNumber(num) {
        if (!num) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function zoomToSchool(lat, lng) {
        fullMap.setView([lat, lng], 18);
        let matched = false;
        schoolMarkers.forEach(entry => {
            if (entry.lat === lat && entry.lng === lng) {
                entry.marker.openPopup();
                showSchoolInfoCard(entry.school);
                matched = true;
            }
        });
        if (!matched) {
            markersCluster.eachLayer(layer => {
                if (layer.getLatLng && layer.getLatLng().lat === lat && layer.getLatLng().lng === lng) {
                    layer.openPopup();
                }
            });
        }
    }
    
    function filterSchools() {
        const allCheckbox = document.getElementById('cat_all');
        const categoryChecks = document.querySelectorAll('.category-filter');
        
        if (allCheckbox.checked) {
            categoryChecks.forEach(cb => {
                cb.checked = false;
                cb.disabled = true;
            });
        } else {
            categoryChecks.forEach(cb => cb.disabled = false);
        }
        
        renderAllMarkers();
        updateSchoolList();
    }
    
    document.getElementById('searchSchool').addEventListener('input', function() {
        renderAllMarkers();
        updateSchoolList();
    });
    
    document.getElementById('filter_water').addEventListener('change', function() {
        renderAllMarkers();
        updateSchoolList();
    });
    
    document.getElementById('filter_electricity').addEventListener('change', function() {
        renderAllMarkers();
        updateSchoolList();
    });
    
    function goToMyLocation() {
        if (navigator.geolocation) {
            const btn = document.querySelector('.btn-my-location');
            btn.style.animation = 'pulse 0.5s ease';
            setTimeout(() => { btn.style.animation = ''; }, 500);
            
            navigator.geolocation.getCurrentPosition(position => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                fullMap.setView([lat, lng], 15);
                
                const customIcon = L.divIcon({
                    html: '<div style="background:#3498DB; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid white; box-shadow:0 2px 5px rgba(0,0,0,0.2);"><i class="fas fa-location-dot" style="color:white; font-size:14px;"></i></div>',
                    iconSize: [30, 30]
                });
                
                const userMarker = L.marker([lat, lng], { icon: customIcon }).addTo(fullMap);
                userMarker.bindPopup('<strong>Ha\'u nia fatin</strong>').openPopup();
                
                // Remove marker after 5 seconds
                setTimeout(() => { fullMap.removeLayer(userMarker); }, 5000);
            }, error => {
                let message = 'La bele asesu ita nia lokasaun.';
                if (error.code === 1) message = 'Favor ativa lokasaun iha navegador.';
                alert(message);
            });
        } else {
            alert('Geolocation la suporta iha navegador ne\'e.');
        }
    }
    
    function openGoogleMapsFromGeo(destLat, destLng) {
        const destination = `${destLat},${destLng}`;
        const url = `https://www.google.com/maps/dir/?api=1&destination=${destination}&travelmode=driving`;
        window.open(url, '_blank');
    }

    function openGoogleMapsFromUser(originLat, originLng, destLat, destLng) {
        const origin = `${originLat},${originLng}`;
        const destination = `${destLat},${destLng}`;
        const url = `https://www.google.com/maps/dir/?api=1&origin=${origin}&destination=${destination}&travelmode=driving`;
        window.open(url, '_blank');
    }

    async function getRouteToSchool(destLat, destLng, schoolName) {
        if (!navigator.geolocation) {
            alert('Geolocation la suporta.');
            return;
        }

        navigator.geolocation.getCurrentPosition(async position => {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            const accuracy = position.coords.accuracy;

            // Warn if GPS accuracy is poor
            if (typeof accuracy === 'number' && accuracy > 100) {
                const proceed = confirm(`GPS la akuradu (akurasi ~${Math.round(accuracy)} m). Continue ho rutu?`);
                if (!proceed) return;
            }

            if (currentRouteLayer) {
                fullMap.removeLayer(currentRouteLayer);
            }

            // Use AbortController to avoid long waits
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 10000);

            try {
                const url = `https://router.project-osrm.org/route/v1/driving/${userLng},${userLat};${destLng},${destLat}?overview=full&geometries=geojson`;
                const response = await fetch(url, { signal: controller.signal });
                clearTimeout(timeout);

                if (!response.ok) {
                    alert(`OSRM servisu responde HTTP ${response.status}.`);
                    return;
                }

                const data = await response.json();

                if (data.routes && data.routes.length > 0) {
                    const route = data.routes[0];
                    const distance = (route.distance / 1000).toFixed(1);
                    const duration = Math.round(route.duration / 60);

                    currentRouteLayer = L.geoJSON(route.geometry, {
                        style: { color: '#F39C12', weight: 5, opacity: 0.9, dashArray: '5, 10' }
                    }).addTo(fullMap);

                    try { fullMap.fitBounds(currentRouteLayer.getBounds()); } catch (e) {}

                    const popupContent = `
                        <div style="padding:8px;">
                            <strong><i class="fas fa-route"></i> Rota ba ${escapeHtml(schoolName)}</strong><br>
                            <i class="fas fa-road"></i> Distánsia: ${distance} km<br>
                            <i class="fas fa-clock"></i> Estimativa: ${duration} minutu
                        </div>
                    `;
                    L.popup()
                        .setLatLng([userLat, userLng])
                        .setContent(popupContent)
                        .openOn(fullMap);
                } else {
                    alert('La bele hetan rota ba eskola ne\'e.');
                }
            } catch (error) {
                if (error.name === 'AbortError') {
                    alert('Permutu timeout husi servisu rota. Tenta liof liu.');
                } else {
                    console.error('Route error:', error);
                    alert('Error bainhira kalkula rota: ' + (error.message || error));
                }
                // Offer fallback to open Google Maps
                if (confirm('La bele kalkula rota internal. Abre Google Maps istadu?')) {
                    const dest = `${destLat},${destLng}`;
                    window.open(`https://www.google.com/maps/dir/?api=1&destination=${dest}&travelmode=driving`, '_blank');
                }
            }
        }, error => {
            let message = 'La bele asesu ita nia lokasaun.';
            if (error.code === 1) message = 'Favor ativa lokasaun iha navegador.';
            else if (error.code === 2) message = 'Pozisaun la disponivel.';
            else if (error.code === 3) message = 'Timeout atu buka lokasaun.';
            alert(message);

            if (confirm('La bele hetan ita nia lokasaun. Buka Google Maps hodi buka rota?')) {
                const dest = `${destLat},${destLng}`;
                window.open(`https://www.google.com/maps/dir/?api=1&destination=${dest}&travelmode=driving`, '_blank');
            }
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
    }
    
    let panelCollapsed = false;
    function togglePanel() {
        panelCollapsed = !panelCollapsed;
        const panelBody = document.getElementById('panelBody');
        const icon = document.getElementById('panelToggleIcon');
        if (panelCollapsed) {
            panelBody.style.display = 'none';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        } else {
            panelBody.style.display = 'block';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        initFullMap();
    });
</script>

<?php include_once '../includes/footer.php'; ?>