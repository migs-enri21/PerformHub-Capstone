@props([
    'latitude' => null,
    'longitude' => null,
    'location' => null,
    'required' => false,
])

@php
    $selectedLatitude = old('latitude', $latitude);
    $selectedLongitude = old('longitude', $longitude);
    $selectedLocation = old('location', $location);
@endphp

<div class="ph-location-picker" data-latitude="{{ $selectedLatitude }}" data-longitude="{{ $selectedLongitude }}" data-location="{{ $selectedLocation }}">
    <div class="position-relative mb-2">
        <label class="visually-hidden" for="location-search">Search for a place</label>
        <input type="search" class="form-control ph-input ph-location-search" placeholder="Search for a place or address" autocomplete="off">
        <div class="ph-location-suggestions list-group position-absolute w-100 shadow-sm"></div>
    </div>
    <div class="ph-location-map" role="application" aria-label="Location map"></div>
    <input type="hidden" name="latitude" class="ph-location-latitude" value="{{ $selectedLatitude }}" @if($required) required @endif>
    <input type="hidden" name="longitude" class="ph-location-longitude" value="{{ $selectedLongitude }}" @if($required) required @endif>
    <input type="hidden" name="location" class="ph-location-address" value="{{ $selectedLocation }}">
    <small class="d-block text-muted mt-2 ph-location-address-label">{{ $selectedLocation ?: 'Choose a place from search or click the map.' }}</small>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
        <small class="text-muted ph-location-status">Click the map to place your location.</small>
        <button type="button" class="btn btn-sm ph-btn-outline ph-location-current">
            <i class="fas fa-location-crosshairs me-1"></i> Use my current location
        </button>
    </div>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
        <style>
            .ph-location-map { height: 300px; border: 1px solid #dee2e6; border-radius: 8px; z-index: 0; }
            .ph-location-suggestions { z-index: 1000; max-height: 220px; overflow-y: auto; }
            .ph-location-suggestions:empty { display: none; }
        </style>
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
        function initLocationPicker(root) {
            const mapElement = root.querySelector('.ph-location-map');
            const latitudeInput = root.querySelector('.ph-location-latitude');
            const longitudeInput = root.querySelector('.ph-location-longitude');
            const status = root.querySelector('.ph-location-status');
            const addressInput = root.querySelector('.ph-location-address');
            const addressLabel = root.querySelector('.ph-location-address-label');
            const searchInput = root.querySelector('.ph-location-search');
            const suggestions = root.querySelector('.ph-location-suggestions');
            const defaultCenter = [12.8797, 121.7740];
            const savedLatitude = Number.parseFloat(root.dataset.latitude);
            const savedLongitude = Number.parseFloat(root.dataset.longitude);
            const hasSavedLocation = root.dataset.latitude.trim() !== ''
                && root.dataset.longitude.trim() !== ''
                && Number.isFinite(savedLatitude)
                && Number.isFinite(savedLongitude);
            const map = L.map(mapElement).setView(hasSavedLocation ? [savedLatitude, savedLongitude] : defaultCenter, hasSavedLocation ? 15 : 6);
            let marker = null;

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            }).addTo(map);

            function setAddress(address) {
                addressInput.value = address || '';
                addressLabel.textContent = address || 'Address not found. Your map coordinates were saved.';
            }

            async function reverseGeocode(latitude, longitude) {
                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitude}&lon=${longitude}`);
                    if (!response.ok) throw new Error('Reverse geocoding failed');
                    const data = await response.json();
                    setAddress(data.display_name || '');
                } catch (error) {
                    setAddress('');
                }
            }

            function setLocation(latitude, longitude, address = '', lookupAddress = false) {
                latitudeInput.value = latitude.toFixed(7);
                longitudeInput.value = longitude.toFixed(7);
                status.textContent = `Location selected: ${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
                if (address) setAddress(address);
                if (lookupAddress) reverseGeocode(latitude, longitude);

                if (!marker) {
                    marker = L.marker([latitude, longitude], { draggable: true }).addTo(map);
                    marker.on('dragend', event => {
                        const position = event.target.getLatLng();
                        setLocation(position.lat, position.lng);
                    });
                } else {
                    marker.setLatLng([latitude, longitude]);
                }
            }

            if (hasSavedLocation) {
                setLocation(savedLatitude, savedLongitude, root.dataset.location);
            }

            map.on('click', event => setLocation(event.latlng.lat, event.latlng.lng, '', true));
            let searchTimer = null;
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                const query = searchInput.value.trim();
                suggestions.innerHTML = '';
                if (query.length < 3) return;
                searchTimer = setTimeout(async () => {
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&countrycodes=ph&limit=5&q=${encodeURIComponent(query)}`);
                        if (!response.ok) throw new Error('Search failed');
                        const results = await response.json();
                        results.forEach(result => {
                            const option = document.createElement('button');
                            option.type = 'button';
                            option.className = 'list-group-item list-group-item-action small text-start';
                            option.textContent = result.display_name;
                            option.addEventListener('click', () => {
                                const latitude = Number.parseFloat(result.lat);
                                const longitude = Number.parseFloat(result.lon);
                                searchInput.value = result.display_name;
                                suggestions.innerHTML = '';
                                map.setView([latitude, longitude], 16);
                                setLocation(latitude, longitude, result.display_name);
                            });
                            suggestions.appendChild(option);
                        });
                    } catch (error) {
                        suggestions.innerHTML = '';
                    }
                }, 350);
            });
            root.querySelector('.ph-location-current').addEventListener('click', () => {
                if (!navigator.geolocation) {
                    status.textContent = 'Current location is not available in this browser.';
                    return;
                }
                status.textContent = 'Finding your current location...';
                navigator.geolocation.getCurrentPosition(position => {
                    const { latitude, longitude } = position.coords;
                    map.setView([latitude, longitude], 16);
                    setLocation(latitude, longitude, '', true);
                }, () => {
                    status.textContent = 'Unable to find your location. Click the map instead.';
                });
            });
        }

        document.querySelectorAll('.ph-location-picker').forEach(initLocationPicker);
        </script>
    @endpush
