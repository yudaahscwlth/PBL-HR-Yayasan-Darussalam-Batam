@push('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

@push('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    {{-- map checkin & checkout --}}
    <script>
        var latitudeInputs = document.getElementsByClassName('latitude');
        var longitudeInputs = document.getElementsByClassName('longitude');

        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            });
        }else{
            Swal.fire({
                text: 'Geolocation tidak didukung oleh browser ini.',
                icon: 'warning',
                confirmButtonText:'OK',
                showCloseButton: true,
                timer: 5000,
            })
        }

        function successCallback(position) {
            for (let i = 0; i < latitudeInputs.length; i++) {
                latitudeInputs[i].value = position.coords.latitude;
                longitudeInputs[i].value = position.coords.longitude;
            }

            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            const officeLat = {{ $dataProfile->profilePekerjaan->tempatKerja->latitude }};
            const officeLon = {{ $dataProfile->profilePekerjaan->tempatKerja->longitude }};

            function setupMap(containerId, modalId) {
                const map = L.map(containerId).setView([lat, lon], 15);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);

                L.marker([lat, lon]).addTo(map);

                L.circle([officeLat, officeLon], {
                    color: 'red',
                    fillColor: '#f03',
                    fillOpacity: 0.5,
                    radius: 500
                }).addTo(map);

                const modal = document.getElementById(modalId);
                modal.addEventListener('shown.bs.modal', function () {
                    map.invalidateSize();
                });

                return map;
            }

            // Setup maps
            setupMap('mapIn', 'checkInModal');
            setupMap('mapOut', 'checkOutModal');
        }


        function errorCallback(error){
            Swal.fire({
                title: 'Lokasi gagal didapatkan',
                html: 'Pastikan GPS Anda aktif dan browser mengizinkan akses lokasi. <br><a class="text-info" href="https://support.google.com/chrome/answer/142065" target="_blank">Cara mengaktifkan lokasi di browser</a>',
                icon: 'error',
                confirmButtonText:'OK',
                showCloseButton: true,
                // timer: 5000,
            }).then(() => {
                location.reload(); // Supaya bisa coba akses lokasi ulang
            });
        }
    </script>
@endpush

@can('rekap_absensi_pribadi.create')
    <div class="card recent-sales overflow-auto">
        <div class="card-title px-3">
            <p class="second-color fw-semibold">
                Absensi Kehadiran
            </p>
            <hr class="border border-3 opacity-100 shadow" style="border-color: #D5C584 !important;">
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-center align-items-center">
                <div class="btn-group btn-group-lg mb-3" role="group" aria-label="Basic mixed styles example">
                    <button type="button" class="btn btn-main" data-bs-toggle="modal" data-bs-target="#checkInModal">
                        <i class="bi bi-building-check"></i> Check In
                    </button>
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#checkOutModal">
                        <i class="bi bi-person-down"></i> Check Out
                    </button>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- checkin modal --}}
<div class="modal fade" id="checkInModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Check In</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('absensi.check.in') }}" method="post">
                    @csrf @method('post')
                    <div class="container-fluid">
                        <input type="hidden" name="latitude" class="latitude">
                        <input type="hidden" name="longitude" class="longitude">
                        <div id="mapIn" style="height: 200px;"></div>
                    </div>
                </div>
            <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-main">Check In</button>
                </form>
            </div>
    </div>
    </div>
</div>

{{-- checkout modal --}}
<div class="modal fade" id="checkOutModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Check Out</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('absensi.check.out') }}" method="post">
                    @csrf @method('put')
                    <div class="container-fluid">
                        <input type="hidden" name="latitude" class="latitude">
                        <input type="hidden" name="longitude" class="longitude">
                        <div id="mapOut" style="height: 200px;"></div>
                    </div>
                </div>
            <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-main">Check Out</button>
                </form>
            </div>
    </div>
    </div>
</div>
