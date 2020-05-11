<!DOCTYPE html>
<html lang='en'>

<head>
	<title>Leaflet - Radius_Geolocation</title>
	<meta charset='utf-8' />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta content='' name='description' />
	<meta content='' name='author' />

	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin="" />
	<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>

	<!-- Styles -->
	<link rel="stylesheet" href="css/bootstrap.min.css" />
	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
      <script type="text/javascript" src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>

<body>
	<div class='navbar navbar-default navbar-static-top'>
		<div class='container-fluid'>
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class='navbar-brand' href='index.php'>Simple GIS</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav navbar-left">
					<li class='active'><a href="index.php">Home</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li><a href="#">Login</a></li>
				</ul>
			</div>
			<!--/.nav-collapse -->
		</div>
	</div>
	<div class='container-fluid'>
		<div class='row'>
			<!-- start left -->
			<div class='col-md-4'>
				<div class='well'>
					<h4>
						Pencarian Lokasi
					</h4>
					<div class="form-group">
						<label>Cari Berdasarkan</label>
						<select id="pilihcari" name="pilihcari" class="form-control">
							<option value='Tampilkan Semua'>Tampilkan Semua</option>
							<option value='Radius'>Radius</option>
							<option value='Geolocation'>Geolocation</option>
						</select>
					</div>
					<input type="hidden" id="lat" name="lat" size="30" maxlength="30" value="<?php echo $lat; ?>" class="form-control" placeholder="Latitude">
					<input type="hidden" id="long" name="long" size="30" maxlength="30" value="<?php echo $lng; ?>" class="form-control" placeholder="Longitude">
					<div id="divradius" class="form-group">
						<label>
							Radius
							<select id='search_radius'>
								<option value='500'>1/2 km</option>
								<option value='1000'>1 km</option>
								<option value='2000'>2 km</option>
								<option value='3000'>3 km</option>
								<option value='4000'>4 km</option>
								<option value='5000'>5 km</option>
							</select>
						</label>
					</div>
					<br />
					<a class='btn btn-primary' id='search' href='#'>
						<i class='glyphicon glyphicon-search'></i>
						Search
					</a>
					<a class='btn btn-default' id='reset' onclick="resetMap()" href='#'>
						<i class='glyphicon glyphicon-repeat'></i>
						Reset
					</a>
				</div>
				<div class='alert alert-info' id='result_box'><strong id='result_count'></strong></div>
			</div>
			<!-- ----------------- -->

			<div class='col-md-8'>
				<noscript>
					<div class='alert alert-info'>
						<h4>Your JavaScript is disabled</h4>
						<p>Please enable JavaScript to view the map.</p>
					</div>
				</noscript>
				<div id="map_canvas"></div>
				<p class='pull-right'>
					<a href='http://derekeder.com/searchable_map_template/'>Map Template</a> by <a href='http://derekeder.com'>Derek Eder</a>.
				</p>
			</div>
		</div>
	</div>

	<script type="text/javascript" src="js/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type='text/javascript'>
		var map, myMarker;
		var theCircle;
		var Lmarkers = [];
		var bounds = new L.LatLngBounds();
		var customIcons = {
			hospital: {
				iconku: 'img/icon/hospital.png'
			},
			hotel: {
				iconku: 'img/icon/hotel.png'
			},
			university: {
				iconku: 'img/icon/university.png'
			}
		};

		$(window).resize(function() {
			var h = $(window).height(),
				offsetTop = 105; // Calculate the top offset

			$('#map_canvas').css('height', (h - offsetTop));
		}).resize();

		$(function() {
			initMap();

			$("#divradius").hide();
			$("#result_box").hide();
			$("select#pilihcari").change(function() {
				if ($("select#pilihcari").val() == "Tampilkan Semua") {
					resetMap();
				} else if ($("select#pilihcari").val() == "Geolocation") {
					$("#divradius").hide();
				} else if ($("select#pilihcari").val() == "Radius") {
					$("#divradius").show();
				}
			});
		});

		function createMap(elemId, centerLat, centerLng, zoom) {
			map = new L.map(elemId);

			// Data provider
			var osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
			var osmAttrib = 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors';

			// Layer
			var osmLayer = new L.tileLayer(osmUrl, {
				minZoom: 4,
				maxZoom: 20,
				attribution: osmAttrib
			});

			// Map
			map.setView(new L.LatLng(centerLat, centerLng), zoom);
			map.addLayer(osmLayer);
			return map;
		}

		//---> membuat marker dan push to gmarkers (array)
		function createMarker(latlng, name, icon, html) {

			//ref: https://stackoverflow.com/questions/17875438/leafletjs-markers-move-on-zoom
			var markerIcon = L.icon({
				iconUrl: icon.iconku,
				iconSize: [32, 37], // size of the icon
				//shadowSize:   [50, 64], // size of the shadow
				iconAnchor: [16, 37], // point of the icon which will correspond to marker's location
				//shadowAnchor: [4, 62],  // the same for the shadow
				popupAnchor: [-2, -37] // point from which the popup should open relative to the iconAnchor
			});

			var marker = L.marker(latlng, {
				title: name,
				icon: markerIcon
			});

			// Add pop up content to marker
			marker.bindPopup(html);
			Lmarkers.push(marker);
		}

		//---> mendefinisikan fungsi initMap()
		function initMap() {
			map = createMap('map_canvas', -8.676488, 115.211177, 15);

			// Bagian ini digunakan untuk mendapatkan data format JSON yang dibentuk dalam getmarker.php
			// berbasis Ajax
			$.ajax({
				url: "getmarker.php",
				type: "GET",
				dataType: "json",
				//cache: true,
				success: function(result) {
					for (i = 0; i < result.data.marker.length; i++) {
						var point = new L.LatLng(parseFloat(result.data.marker[i].lat), parseFloat(result.data.marker[i].lng));

						var content = '<h4>' + result.data.marker[i].name + '</h4>' +
							'<b>Lokasi</b><br/>' + result.data.marker[i].address +
							'<p>Lat: ' + result.data.marker[i].lat + '<br/>Lng: ' + result.data.marker[i].lng + '</p>';

						var type = result.data.marker[i].category;
						//membuat marker
						createMarker(point, result.data.marker[i].name, customIcons[type], content);
					}

					for (var i = 0; i < Lmarkers.length; i++) {
						bounds.extend(Lmarkers[i].getLatLng());
						Lmarkers[i].addTo(map);
					}

					//now fit the map to the newly inclusive bounds
					map.fitBounds(bounds);
					//map.setZoom(10);
				}
			});

			setDefaultMarker();

		} //akhir initMap()

		var myMarkerIcon = L.icon({
			iconUrl: 'img/icon/position.png',
			iconSize: [32, 37], // size of the icon
			//shadowSize:   [50, 64], // size of the shadow
			iconAnchor: [16, 37], // point of the icon which will correspond to marker's location
			//shadowAnchor: [4, 62],  // the same for the shadow
			popupAnchor: [-2, -37] // point from which the popup should open relative to the iconAnchor
		});

		//---> menset default marker
		function setDefaultMarker() {
			if (myMarker != null) {
				map.removeLayer(myMarker);
			}

			//set lat & lng first load marker
			$("#lat").val(-8.676156560668673);
			$("#long").val(115.20589841265871);

			myMarker = L.marker(new L.LatLng(-8.676156560668673, 115.20589841265871), {
				icon: myMarkerIcon,
				draggable: true
			});

			var contentD = '<h4>Posisi Saat Ini</h4>' +
				'<b>Lokasi</b><br/>' +
				'Lat: ' + myMarker.getLatLng().lat + '<br/>Lng: ' + myMarker.getLatLng().lng;
			myMarker.addTo(map);
			myMarker.bindPopup(contentD);

			// event drag marker
			myMarker.on('drag', function(e) {
				$("#lat").val(myMarker.getLatLng().lat);
				$("#long").val(myMarker.getLatLng().lng);
				var contentD = '<h4>Posisi Saat Ini</h4>' +
					'<b>Lokasi</b><br/>' +
					'Lat: ' + myMarker.getLatLng().lat + '<br/>Lng: ' + myMarker.getLatLng().lng;
				myMarker.bindPopup(contentD).openPopup();
			});
		}



		//---> me-reset peta
		function resetMap() {
			$("#divradius").hide();
			$("#pilihcari").val('Tampilkan Semua');
			$("#result_box").hide();

			if (Lmarkers.length > 0) {
				for (var i = 0; i < Lmarkers.length; i++) {
					bounds.extend(Lmarkers[i].getLatLng());
					Lmarkers[i].addTo(map);
				}
				//now fit the map to the newly inclusive bounds
				map.fitBounds(bounds);
			}

			if (theCircle != undefined) {
				map.removeLayer(theCircle);
			}

			setDefaultMarker();
		}

		function onLocationFound(e) {
			var radius = e.accuracy / 2;
			var location = e.latlng;

			if (myMarker != undefined) {
				map.removeLayer(myMarker);
			}

			myMarker = L.marker(location, {
				icon: myMarkerIcon,
				draggable: true
			});
			var contentD = '<h4>Posisi Saat Ini</h4>' +
				'<b>Lokasi</b><br/>' +
				'Lat: ' + myMarker.getLatLng().lat + '<br/>Lng: ' + myMarker.getLatLng().lng;

			document.getElementById('lat').value = myMarker.getLatLng().lat;
			document.getElementById('long').value = myMarker.getLatLng().lng;

			myMarker.addTo(map);
			myMarker.bindPopup(contentD).openPopup();
			myMarker.on('drag', function(e) {
				$("#lat").val(myMarker.getLatLng().lat);
				$("#long").val(myMarker.getLatLng().lng);
				var contentD = '<h4>Posisi Saat Ini</h4>' +
					'<b>Lokasi</b><br/>' +
					'Lat: ' + myMarker.getLatLng().lat + '<br/>Lng: ' + myMarker.getLatLng().lng;
				myMarker.bindPopup(contentD).openPopup();

			});


		}

		function onLocationError(e) {
			alert(e.message);
		}

		function getLocationLeaflet() {
			map.on('locationfound', onLocationFound);
			map.on('locationerror', onLocationError);
			if (theCircle != undefined) {
				map.removeLayer(theCircle);
			}

			$("#result_box").fadeOut(function() {
				$("#result_count").html("Jumlah marker lokasi yang ditemukan: 0");
			});
			$("#result_box").fadeIn();

			map.locate({
				setView: true,
				maxZoom: 12
			});
		}

		// menangani event click pada tombol Search
		jQuery(document).on('click', '#search', function(event) {
			if ($("select#pilihcari").val() == "Radius") {
				radiusSearch();
			} else if ($("select#pilihcari").val() == "Geolocation") {
				getLocationLeaflet()
			} else {
				alert("Silahkan pilih metode pencarian..");
			}
		});

		//---> pencarian marker dengan radius
		function radiusSearch() {
			var lat = document.getElementById('lat').value;
			var lang = document.getElementById('long').value;
			var searchLatLang = new L.LatLng(lat, lang);

			map.closePopup(); // menutup popup yang terbuka

			var radius = parseInt(document.getElementById('search_radius').value); // dalam meter
			var foundMarkers = 0;

			if (theCircle != undefined) {
				map.removeLayer(theCircle);
			}

			if (Lmarkers.length > 0) {
				for (var i = 0; i < Lmarkers.length; i++) {
					var layer_lat_long = Lmarkers[i].getLatLng();
					var distance_from_centerPoint = layer_lat_long.distanceTo(searchLatLang);
					//console.log(distance_from_centerPoint);

					map.removeLayer(Lmarkers[i]);

					// Cek jika masuk dalam radius
					if (distance_from_centerPoint <= radius) {
						Lmarkers[i].addTo(map);
						foundMarkers++;
					}
				}

				// draw circle to see the selection area
				theCircle = L.circle(searchLatLang, radius, { /// Number is in Meters
					color: '#1E90FF',
					fillOpacity: 0.25,
					fillColor: "#1E90FF",
					opacity: 0.5
				}).addTo(map);

				// menampilkan jumlah marker yang ditemukan
				$("#result_box").fadeOut(function() {
					$("#result_count").html("Jumlah marker lokasi yang ditemukan: " + foundMarkers);
				});
				$("#result_box").fadeIn();

				map.fitBounds(theCircle.getBounds());

			}
		}
	</script>
</body>

</html>