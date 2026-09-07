@props([
    'region' => '',
    'city' => '',
    'barangay' => '',
    'required' => false,
])

@php
    $selectedRegion = old('region', $region);
    $selectedCity = old('city', $city);
    $selectedBarangay = old('barangay', $barangay);
    $initialAddress = old('location_search', implode(', ', array_filter([$selectedBarangay, $selectedCity, $selectedRegion])));
    $mapsKey = config('services.google_maps.key');
@endphp

@if($mapsKey)
    <div class="ph-location-cascade" data-required="{{ $required ? '1' : '0' }}">
        <input type="hidden" name="region" class="ph-location-region-input" value="{{ $selectedRegion }}">
        <input type="hidden" name="city" class="ph-location-city-input" value="{{ $selectedCity }}">
        <input type="hidden" name="barangay" class="ph-location-barangay-input" value="{{ $selectedBarangay }}">

        <div class="mb-2">
            <input
                type="text"
                name="location_search"
                class="form-control ph-input ph-location-search"
                placeholder="Search your address in the Philippines"
                value="{{ $initialAddress }}"
                autocomplete="off"
                @if($required) required @endif
            >
        </div>
        <button type="button" class="btn btn-sm ph-btn-outline ph-location-geolocate">
            <i class="fas fa-location-crosshairs me-1"></i> Use my exact location
        </button>
        <div class="ph-location-status small text-muted mt-2"></div>
    </div>

    @once
        @push('styles')
            <style>
                .pac-container { z-index: 20000 !important; }
            </style>
        @endpush
        @push('scripts')
            <script>
            (function () {
                window.__phLocationPending = window.__phLocationPending || [];

                function parseAddressComponents(components) {
                    const find = (type) => {
                        const match = components.find((component) => component.types.includes(type));
                        return match ? match.long_name : '';
                    };

                    return {
                        region: find('administrative_area_level_1'),
                        city: find('locality') || find('administrative_area_level_2') || find('administrative_area_level_3'),
                        barangay: find('sublocality_level_1') || find('sublocality') || find('neighborhood'),
                    };
                }

                function isVisible(el) {
                    return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
                }

                function whenVisible(el, callback) {
                    if (isVisible(el)) {
                        callback();
                        return;
                    }

                    const observer = new MutationObserver(() => {
                        if (isVisible(el)) {
                            observer.disconnect();
                            callback();
                        }
                    });

                    let node = el;
                    while (node && node !== document.body) {
                        observer.observe(node, { attributes: true, attributeFilter: ['style', 'class', 'hidden'] });
                        node = node.parentElement;
                    }
                }

                function initCascade(root) {
                    if (root.dataset.phLocationReady === '1') {
                        return;
                    }

                    const searchInput = root.querySelector('.ph-location-search');
                    const regionInput = root.querySelector('.ph-location-region-input');
                    const cityInput = root.querySelector('.ph-location-city-input');
                    const barangayInput = root.querySelector('.ph-location-barangay-input');
                    const geoButton = root.querySelector('.ph-location-geolocate');
                    const statusEl = root.querySelector('.ph-location-status');
                    const isRequired = root.dataset.required === '1';
                    root.dataset.phLocationReady = '1';

                    function applyResult(components, formattedAddress) {
                        const parsed = parseAddressComponents(components);
                        regionInput.value = parsed.region;
                        cityInput.value = parsed.city;
                        barangayInput.value = parsed.barangay;
                        if (formattedAddress) {
                            searchInput.value = formattedAddress;
                        }
                    }

                    const autocomplete = new google.maps.places.Autocomplete(searchInput, {
                        componentRestrictions: { country: 'ph' },
                        fields: ['address_components', 'formatted_address'],
                    });

                    autocomplete.addListener('place_changed', () => {
                        const place = autocomplete.getPlace();

                        if (!place || !place.address_components) {
                            statusEl.textContent = 'Please choose an address from the suggestions.';
                            return;
                        }

                        applyResult(place.address_components, place.formatted_address);
                        statusEl.textContent = '';
                    });

                    // Manual edits invalidate the previously resolved address.
                    searchInput.addEventListener('input', () => {
                        regionInput.value = '';
                        cityInput.value = '';
                        barangayInput.value = '';
                    });

                    geoButton.addEventListener('click', () => {
                        if (!navigator.geolocation) {
                            statusEl.textContent = 'Geolocation is not supported by your browser.';
                            return;
                        }

                        statusEl.textContent = 'Locating you…';
                        geoButton.disabled = true;

                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                const geocoder = new google.maps.Geocoder();
                                const latLng = {
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude,
                                };

                                geocoder.geocode({ location: latLng }, (results, status) => {
                                    geoButton.disabled = false;

                                    if (status === 'OK' && results[0]) {
                                        applyResult(results[0].address_components, results[0].formatted_address);
                                        statusEl.textContent = 'Location detected ✓';
                                    } else {
                                        statusEl.textContent = 'Could not resolve an address for your location.';
                                    }
                                });
                            },
                            () => {
                                geoButton.disabled = false;
                                statusEl.textContent = 'Unable to get your location. Please allow location access and try again.';
                            },
                            { enableHighAccuracy: true, timeout: 10000 }
                        );
                    });

                    const form = root.closest('form');

                    if (form && isRequired) {
                        form.addEventListener('submit', (event) => {
                            if (!regionInput.value || !cityInput.value) {
                                event.preventDefault();
                                statusEl.textContent = 'Please choose an address from the suggestions, or tap "Use my exact location".';
                                searchInput.focus();
                            }
                        });
                    }
                }

                function bindWhenReady(root) {
                    whenVisible(root, () => {
                        if (window.google && window.google.maps && window.google.maps.places) {
                            initCascade(root);
                        } else {
                            window.__phLocationPending.push(root);
                        }
                    });
                }

                window.__initPhLocationCascade = bindWhenReady;

                document.querySelectorAll('.ph-location-cascade').forEach(bindWhenReady);

                window.gm_authFailure = function () {
                    document.querySelectorAll('.ph-location-status').forEach((el) => {
                        el.textContent = 'Google Maps rejected this API key. Enable Maps JavaScript API and Places API, and allow localhost in the key restrictions.';
                        el.classList.add('text-danger');
                    });
                };
            })();

            function __onGoogleMapsLoaded() {
                (window.__phLocationPending || []).forEach((root) => window.__initPhLocationCascade(root));
                window.__phLocationPending = [];
            }
            </script>
            <script src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places&callback=__onGoogleMapsLoaded" async defer></script>
        @endpush
    @endonce
@else
    <div class="ph-location-cascade">
        <div class="alert alert-warning small py-2 mb-2">
            Google Maps location search isn't configured yet — enter your address manually below.
        </div>
        <div class="mb-2">
            <input type="text" name="region" class="form-control ph-input" placeholder="Region / Province" value="{{ $selectedRegion }}" @if($required) required @endif>
        </div>
        <div class="mb-2">
            <input type="text" name="city" class="form-control ph-input" placeholder="City / Municipality" value="{{ $selectedCity }}" @if($required) required @endif>
        </div>
        <div class="mb-0">
            <input type="text" name="barangay" class="form-control ph-input" placeholder="Barangay (optional)" value="{{ $selectedBarangay }}">
        </div>
    </div>
@endif
