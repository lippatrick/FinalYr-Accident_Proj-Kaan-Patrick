<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Incident Reporting</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <style>
        :root {
            --primary-color: #1f2c73;
            --success-color: #06d6a0;
            --danger-color: #ef476f;
        }

        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Rubik', sans-serif;
        }

        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: none;
        }

        #alertMessage {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            min-width: 300px;
            border-radius: 10px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        #alertMessage.show {
            opacity: 1;
            visibility: visible;
            top: 30px;
        }

        .alert-success {
            background: var(--success-color);
            color: white;
        }

        .alert-danger {
            background: var(--danger-color);
            color: white;
        }

        .section-divider {
            padding: 20px;
            position: relative;
            margin: 25px 0;
        }

        #map {
            height: 300px;
            width: 100%;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 12px 0;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }
    </style>
</head>

<body class="container py-2">
    <div class="justify-content-center" style="width: 100%;">
        <div class="col-lg-12">
            <h2 class="mb-4 text-center text-primary">Incident Reporting</h2>
            <div id="alertMessage" class="alert d-none alert-dismissible fade" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <span id="alertText"></span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <div class="card shadow-lg">
                <div class="card-body p-4">
                    <form id="incidentForm">
                        <div class="row">
                            <div class="row g-4 col-md-6">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="plate_no" class="form-label">Plate Number</label>
                                        <input type="text" class="form-control" id="plate_no" name="plate_no" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="vehicle" class="form-label">Vehicle</label>
                                        <input type="text" class="form-control" id="vehicle" name="vehicle" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="owner" class="form-label">Owner</label>
                                        <input type="text" class="form-control" id="owner" name="owner" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone_no" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="phone_no" name="phone_no" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="kin_phone_no" class="form-label">Next of Kin Phone</label>
                                        <input type="text" class="form-control" id="kin_phone_no" name="kin_phone_no">
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Incident Status</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="">Select Status</option>
                                            <option value="High">High</option>
                                            <option value="Medium">Medium</option>
                                            <option value="Low">Low</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="location" class="form-label">Incident Location</label>
                                <input type="text" class="form-control" id="location" name="location" readonly>
                                <div id="map" class="mt-3"></div>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" class="btn btn-primary mt-4 w-100">
                            <i class="fas fa-paper-plane me-2"></i>Submit Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function initMap() {
            var defaultCenter = [0.3476, 32.5825];
            var map = L.map('map').setView(defaultCenter, 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            var marker = L.marker(defaultCenter, {
                draggable: true
            }).addTo(map);
            marker.on('dragend', function(e) {
                var latlng = marker.getLatLng();
                $.get('https://nominatim.openstreetmap.org/reverse', {
                    lat: latlng.lat,
                    lon: latlng.lng,
                    format: 'json'
                }, function(data) {
                    if (data && data.display_name) {
                        $('#location').val(data.display_name);
                    } else {
                        $('#location').val("No address found.");
                    }
                });
            });
            L.Control.geocoder({
                    defaultMarkGeocode: false
                })
                .on('markgeocode', function(e) {
                    var center = e.geocode.center;
                    marker.setLatLng(center);
                    map.setView(center, 12);
                    $('#location').val(e.geocode.name);
                })
                .addTo(map);
        }
        $(document).ready(function() {
            initMap();
            $("#incidentForm").submit(function(e) {
                e.preventDefault();
                var phoneNo = $("#phone_no").val().trim();
                var kinPhoneNo = $("#kin_phone_no").val().trim();
                var phonePattern = /^256\d{9}$/;
                if (!phonePattern.test(phoneNo)) {
                    showAlert("Invalid Phone Number. Must be 256 followed by 9 digits.", "danger");
                    return;
                }
                if (kinPhoneNo !== "" && !phonePattern.test(kinPhoneNo)) {
                    showAlert("Invalid Next of Kin Phone Number. Must be 256 followed by 9 digits.", "danger");
                    return;
                }
                $("#submitBtn").prop("disabled", true).text("Reporting...");
                $.ajax({
                    url: "sendSMS.php",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.trim() === "Incident reported successfully.") {
                            showAlert(response, "success");
                            $("#incidentForm")[0].reset();
                        } else {
                            showAlert(response, "danger");
                        }
                        $("#submitBtn").prop("disabled", false).text("Submit Report");
                    },
                    error: function() {
                        showAlert("Error submitting report.", "danger");
                        $("#submitBtn").prop("disabled", false).text("Submit Report");
                    }
                });
            });

            function showAlert(message, type) {
                var alertBox = $("#alertMessage");
                $("#alertText").text(message);
                alertBox.removeClass("d-none alert-danger alert-success").addClass("alert-" + type).addClass("show");
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>