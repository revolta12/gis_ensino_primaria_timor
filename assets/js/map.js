/**
 * =============================================
 * MAP JAVASCRIPT - GIS ENSINO PRIMARIA TIMOR-LESTE
 * OPTIMIZED VERSION
 * =============================================
 */

// =============================================
// MAP CONFIGURATION
// =============================================
const DILI_CENTER = [-8.559, 125.579];
const DILI_ZOOM = 13;
const TIMOR_BOUNDS = [[-9.5, 124.0], [-8.1, 127.4]];

let mainMap = null;
let markersLayer = null;
let allSchoolsData = [];
let currentRouteLayer = null;
let routeInfo = null;

// =============================================
// INIT MAP
// =============================================
function initMap(containerId, options = {}) {
    if (mainMap) return mainMap;
    
    mainMap = L.map(containerId, {
        center: options.center || DILI_CENTER,
        zoom: options.zoom || DILI_ZOOM,
        zoomControl: true,
        maxBounds: TIMOR_BOUNDS,
        maxBoundsViscosity: 0.8
    });
    
    // FASTER TILE LAYER - CartoDB Voyager
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19,
        minZoom: 8
    }).addTo(mainMap);
    
    markersLayer = L.layerGroup().addTo(mainMap);
    
    return mainMap;
}

// =============================================
// CREATE MARKER ICON
// =============================================
function createMarkerIcon(featured = false, hasWater = false, hasElectricity = false) {
    let bgColor = '#2C3E50';
    let icon = 'fa-school';
    let iconColor = 'white';
    
    if (featured) {
        bgColor = '#F39C12';
        icon = 'fa-star';
    } else if (hasWater && hasElectricity) {
        bgColor = '#27AE60';
        icon = 'fa-check';
    } else if (!hasWater) {
        bgColor = '#E74C3C';
        icon = 'fa-exclamation-triangle';
    } else if (hasWater && !hasElectricity) {
        bgColor = '#F39C12';
        icon = 'fa-tint';
    }
    
    return L.divIcon({
        className: 'custom-marker',
        html: `<div style="background:${bgColor}; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid #FFD700; box-shadow:0 2px 8px rgba(0,0,0,0.3);">
                    <i class="fas ${icon}" style="color:white; font-size:16px;"></i>
                </div>`,
        iconSize: [36, 36],
        popupAnchor: [0, -18]
    });
}

// =============================================
// LOAD SCHOOLS DATA
// =============================================
async function loadSchoolsToMap(filters = {}) {
    try {
        let url = BASE_URL + '/api/geojson_escola.php';
        const params = [];
        
        if (filters.kategoria) params.push(`kategoria=${filters.kategoria}`);
        if (filters.bee_moos) params.push(`bee_moos=${filters.bee_moos}`);
        if (filters.municipio) params.push(`municipio=${encodeURIComponent(filters.municipio)}`);
        
        if (params.length) url += '?' + params.join('&');
        
        const response = await fetch(url);
        const geojson = await response.json();
        allSchoolsData = geojson.features || [];
        
        renderMarkersToMap();
        
        return allSchoolsData;
    } catch (error) {
        console.error('Error loading schools:', error);
        return [];
    }
}

// =============================================
// RENDER MARKERS TO MAP
// =============================================
function renderMarkersToMap() {
    if (!markersLayer) return;
    markersLayer.clearLayers();
    
    allSchoolsData.forEach(feature => {
        const p = feature.properties;
        const [lng, lat] = feature.geometry.coordinates;
        
        if (!lat || !lng || lat === 0 || lng === 0) return;
        
        const icon = createMarkerIcon(p.destakadu, p.iha_bee_moos, p.iha_eletrisidade);
        
        const popupContent = `
            <div style="min-width:260px; max-width:320px;">
                <img src="${p.foto}" style="width:100%; height:140px; object-fit:cover; border-radius:12px; margin-bottom:12px;" onerror="this.src='${BASE_URL}/assets/img/escola-placeholder.jpg'">
                <h6 style="margin:0 0 8px 0; font-weight:bold;">${escapeHtml(p.naran_escola)}</h6>
                <div style="display:flex; gap:8px; flex-wrap:wrap; margin:10px 0;">
                    ${p.iha_bee_moos ? '<span style="background:#27AE60; padding:2px 8px; border-radius:20px; font-size:11px; color:white;"><i class="fas fa-water"></i> Bee</span>' : ''}
                    ${p.iha_eletrisidade ? '<span style="background:#2C3E50; padding:2px 8px; border-radius:20px; font-size:11px; color:white;"><i class="fas fa-plug"></i> Luz</span>' : ''}
                    ${p.iha_toilet ? '<span style="background:#2980B9; padding:2px 8px; border-radius:20px; font-size:11px; color:white;"><i class="fas fa-toilet"></i> Toilet</span>' : ''}
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
                    <span><i class="fas fa-users"></i> ${formatNumber(p.total_estudante)}</span>
                    <a href="${p.url}" style="background:#F39C12; padding:6px 12px; border-radius:25px; color:#1A252F; text-decoration:none; font-size:12px; font-weight:bold;">Detallu <i class="fas fa-arrow-right"></i></a>
                </div>
                <button onclick="routeToSchoolFromPopup(${lat}, ${lng}, '${escapeHtml(p.naran_escola)}')" style="width:100%; margin-top:12px; background:#2C3E50; border:none; padding:8px; border-radius:25px; color:white; cursor:pointer;">
                    <i class="fas fa-route"></i> Rota ba eskola
                </button>
            </div>
        `;
        
        L.marker([lat, lng], { icon })
            .bindPopup(popupContent)
            .addTo(markersLayer);
    });
}

// =============================================
// ROUTE TO SCHOOL FROM POPUP
// =============================================
async function routeToSchoolFromPopup(schoolLat, schoolLng, schoolName) {
    if (!navigator.geolocation) {
        showToast('Geolocation la suporta iha navegador ne\'e.', 'error');
        return;
    }
    
    showToast('Buka ita nia lokasaun...', 'info');
    
    navigator.geolocation.getCurrentPosition(async (position) => {
        const userLat = position.coords.latitude;
        const userLng = position.coords.longitude;
        
        showToast('Kalkula rota...', 'info');
        
        const route = await getRouteToSchool(userLat, userLng, schoolLat, schoolLng, mainMap);
        
        if (route) {
            showToast(`Rota hetan! Distánsia: ${route.distance} km, ${route.duration} minutu`, 'success');
        } else {
            showToast('La bele hetan rota ba eskola ne\'e.', 'error');
        }
    }, (error) => {
        let msg = 'La bele asesu lokasaun. ';
        if (error.code === 1) msg += 'Favor ativa GPS.';
        else msg += 'Tenta fali depois.';
        showToast(msg, 'error');
    }, {
        enableHighAccuracy: true,
        timeout: 10000
    });
}

// =============================================
// GET ROUTE TO SCHOOL (OPTIMIZED)
// =============================================
async function getRouteToSchool(userLat, userLng, schoolLat, schoolLng, map) {
    const cacheKey = `${userLat.toFixed(4)},${userLng.toFixed(4)}|${schoolLat},${schoolLng}`;
    
    // Check cache (5 minutes)
    if (routeCache && routeCache[cacheKey]) {
        displayRoute(routeCache[cacheKey], map);
        return routeCache[cacheKey];
    }
    
    const url = `https://router.project-osrm.org/route/v1/driving/${userLng},${userLat};${schoolLng},${schoolLat}?overview=full&geometries=geojson`;
    
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 8000);
    
    try {
        const response = await fetch(url, { signal: controller.signal });
        clearTimeout(timeoutId);
        
        const data = await response.json();
        
        if (data.routes && data.routes[0]) {
            const route = data.routes[0];
            const result = {
                distance: (route.distance / 1000).toFixed(1),
                duration: Math.round(route.duration / 60),
                geometry: route.geometry
            };
            
            // Store in cache
            if (!routeCache) window.routeCache = {};
            routeCache[cacheKey] = result;
            
            if (map) {
                // Remove old route
                if (currentRouteLayer && map) map.removeLayer(currentRouteLayer);
                
                currentRouteLayer = L.geoJSON(route.geometry, {
                    style: { color: '#F39C12', weight: 6, opacity: 0.9 }
                }).addTo(map);
                
                const bounds = L.latLngBounds([[userLat, userLng], [schoolLat, schoolLng]]);
                map.fitBounds(bounds, { padding: [50, 50] });
            }
            
            return result;
        }
        return null;
    } catch (error) {
        clearTimeout(timeoutId);
        console.error('Route error:', error);
        return null;
    }
}

// =============================================
// DISPLAY ROUTE INFO
// =============================================
function displayRoute(route, map) {
    if (!route) return;
    
    if (currentRouteLayer && map) map.removeLayer(currentRouteLayer);
    
    currentRouteLayer = L.geoJSON(route.geometry, {
        style: { color: '#F39C12', weight: 6, opacity: 0.9 }
    }).addTo(map);
    
    const bounds = L.latLngBounds(route.geometry.coordinates.map(c => [c[1], c[0]]));
    map.fitBounds(bounds, { padding: [50, 50] });
}

// =============================================
// CLEAR ROUTE
// =============================================
function clearRoute(map) {
    if (currentRouteLayer && map) {
        map.removeLayer(currentRouteLayer);
        currentRouteLayer = null;
    }
}

// =============================================
// RESET MAP VIEW
// =============================================
function resetMapView() {
    if (mainMap) {
        mainMap.setView(DILI_CENTER, DILI_ZOOM);
        clearRoute(mainMap);
    }
}

// =============================================
// SHOW TOAST NOTIFICATION
// =============================================
function showToast(message, type = 'info') {
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = 'position:fixed; bottom:20px; right:20px; z-index:9999;';
        document.body.appendChild(toastContainer);
    }
    
    const toast = document.createElement('div');
    let bgColor = '#2C3E50';
    if (type === 'success') bgColor = '#27AE60';
    if (type === 'error') bgColor = '#E74C3C';
    if (type === 'info') bgColor = '#F39C12';
    
    toast.style.cssText = `background:${bgColor}; color:white; padding:12px 20px; border-radius:8px; margin-top:8px; box-shadow:0 4px 12px rgba(0,0,0,0.2); font-size:14px; animation:fadeIn 0.3s ease;`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`;
    
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// =============================================
// HELPER FUNCTIONS
// =============================================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatNumber(num) {
    if (!num) return '0';
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// =============================================
// INITIALIZATION
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('schoolMap')) {
        initMap('schoolMap');
    }
});

// Make functions global
window.initMap = initMap;
window.loadSchoolsToMap = loadSchoolsToMap;
window.getRouteToSchool = getRouteToSchool;
window.clearRoute = clearRoute;
window.resetMapView = resetMapView;
window.routeToSchoolFromPopup = routeToSchoolFromPopup;