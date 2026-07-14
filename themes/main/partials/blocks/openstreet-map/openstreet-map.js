import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import 'leaflet.markercluster/dist/MarkerCluster.css';

export default function init(element) {
    let container, zoom, markersData, markerIcon;
    container = element.querySelector('.map-container');
    zoom = Number(element.dataset.zoom);
    markersData = JSON.parse(element.dataset.markers);
    markerIcon = createMarkerIcon("/themes/main/partials/blocks/openstreet-map/marker.svg");

    let tiles = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {});

    const map = L.map(container, {
        zoom: zoom,
        layers: [tiles],
        attributionControl: false,
        scrollWheelZoom: false,
        touchZoom: true,
        dragging: !L.Browser.mobile
    });

    const markers = L.markerClusterGroup();
    let markerBounds = [];

    markersData.forEach(markerData => {
        const [latitude, longitude] = markerData.coordinates.split(',').map(Number);
        const marker = L.marker([latitude, longitude], {
            id: markerData.id,
            icon: markerIcon
        });

        marker.bindPopup('<h2 style="font-weight: bold; font-size: 20px;">' + markerData.title + '</h2><p>' + markerData.text + '</p>');
        markers.addLayer(marker);

        markerBounds.push([latitude, longitude]);
    });

    map.addLayer(markers);

    if (markerBounds.length > 0) {
        map.fitBounds(markerBounds);
        map.setZoom(zoom);
    }

    function createMarkerIcon(iconPath) {
        return L.icon({
            iconUrl: iconPath,
            iconSize: [26, 39],
            iconAnchor: [13, 39]
        });
    }
}
