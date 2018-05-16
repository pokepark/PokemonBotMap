<html>
	<head>
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css"
			integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin=""/>
   
		<title>Raid Map</title>
      
    	<script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"
			integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>
   
		<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
		<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
   
		<style type="text/css">
		<style type="text/css">
            html { height: 100% }
            body { height: 100%; margin: 0; padding: 0;}
            #map { height: 100% }
        </style>

	</head>
	
	<body>

		<div id="map"></div>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
		<script type="text/javascript">
		
			//Configure these variables for your setup
			var defaultCentre = new L.LatLng(51.9204595, 4.3406484); //Default starting location of map.
			var mapToken = 'mapbox_token_here'; //Your MapBox token
			var autoLocate = false; //Automatically centre map on user's location (can give error on some browsers if not using https)
			var exIdentifier = '[EX'; //Something added to the gym name to identify gyms as EX Gyms
		
			var map, tiles, darkTiles, outdoorsTiles, satelliteTiles, raids1, raids2, raids3, raids4, raids5, raidsX, gyms, gymsEX;
			var firstLoad=true;
			var pokemonIcon = [];
			
			var eggIcon = L.Icon.extend({
				options: {
					iconSize:     [32, 40],
					iconAnchor:   [16, 20], 
					popupAnchor:  [0, -10] 
				}
			});
			
			var raidIcon = L.Icon.extend({
				options: {
					iconSize:     [48, 48],
					iconAnchor:   [24, 24],
					popupAnchor:  [-3, -10] 			
				}
			});
			
			var gymIcon = L.icon({
				iconSize:     [20, 20], 
				iconAnchor:   [10, 17],
				popupAnchor:  [-3, -10],		
				iconUrl: 'icons/gym.png'
			});
	
			var exGymIcon = L.icon({
				iconSize:     [20, 20],
				iconAnchor:   [10, 17], 
				popupAnchor:  [-3, -10],	
				iconUrl: 'icons/gymEX.png'
			});			
			
		
			(function () {
				//Separate layers for raid levels to allow toggle on/off of levels
				gyms = new L.FeatureGroup();
				gymsEX = new L.FeatureGroup();
				raids1 = new L.FeatureGroup();
				raids2 = new L.FeatureGroup();
				raids3 = new L.FeatureGroup();
				raids4 = new L.FeatureGroup();
				raids5 = new L.FeatureGroup();
				raidsX = new L.FeatureGroup();
				
				tiles = new L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
				 attribution: '<a href="http://openstreetmap.org">OpenStreetMap</a> | <a href="http://mapbox.com">Mapbox</a>',
				 maxZoom: 20,
					id: 'mapbox.streets',
					accessToken: mapToken
				});
				
				darkTiles = new L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
					attribution: '<a href="http://openstreetmap.org">OpenStreetMap</a> | <a href="http://mapbox.com">Mapbox</a>',
					maxZoom: 20,
					id: 'mapbox.dark',
					accessToken: mapToken
				});
				
				outdoorsTiles = new L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
					attribution: '<a href="http://openstreetmap.org">OpenStreetMap</a> | <a href="http://mapbox.com">Mapbox</a>',
					maxZoom: 20,
					id: 'mapbox.outdoors',
					accessToken: mapToken
				});
				
				satelliteTiles = new L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
					attribution: '<a href="http://openstreetmap.org">OpenStreetMap</a> | <a href="http://mapbox.com">Mapbox</a>',
					maxZoom: 20,
					id: 'mapbox.satellite',
					accessToken: mapToken
				});		
				
				map = L.map('map', {
					center: defaultCentre, 
					zoom: 13,
					layers: [tiles, raids1, raids2, raids3, raids4, raids5, raidsX],
					fullscreenControl: true
				});
				
				if(autoLocate == true) {
					map.locate({setView: true, maxZoom: 14});

				}
		
				var baseMap = {
					"Light Map": tiles,
					"Dark Map": darkTiles,
					"Outdoors" : outdoorsTiles,
					"Satellite" : satelliteTiles
				};
				
				var overlayMaps = {
					"EX Raids": raidsX,
					"Level 5": raids5,
					"Level 4": raids4,
					"Level 3": raids3,
					"Level 2": raids2,
					"Level 1": raids1,
					"EX Gyms": gymsEX,
					"Other Gyms": gyms
				};
				
				L.control.layers(baseMap, overlayMaps, {hideSingleBase: true}).addTo(map);
				map.addControl(new L.Control.Scale());
					
			})();
			
				
			$(document).ready(function() {
			  $.ajaxSetup({cache:false});
			 
				updateRaids();
			});
			
			function updateRaids() {
				//Clear map, get latest data and set timer to update again in 60 seconds.
				if(firstLoad) {
					firstLoad = false;
					getGyms();
				}
				raids1.clearLayers();
				raids2.clearLayers();
				raids3.clearLayers();
				raids4.clearLayers();
				raids5.clearLayers();
				getRaids();
				timeOut=setTimeout("updateRaids()",60000);
			}
					
			function getRaids() {
				$.getJSON("getraids.php", function (data) {
				  for (var i = 0; i < data.length; i++) {
					//Get vars from JSON data
					var location = new L.LatLng(data[i].lat, data[i].lon),
						gym_name = data[i].gym_name,
						address = data[i].address,
						pokemon_name = data[i].pokemon_name,
						pokedex_id = data[i].pokedex_id,
						level = data[i].raid_level,
						start_time = new Date((data[i].start_time).replace(/-/g,"/")),
						end_time = new Date((data[i].end_time).replace(/-/g,"/")),
						remaining = Math.floor(data[i].t_left / 60),
						interest = data[i].interest,
						raiders = parseInt(data[i].count),
						extras = parseInt(data[i].total_extras);
						
					var gym_info = "<div style='font-size: 18px; color: #0078A8;'>"+ gym_name +"</div>";
						gym_info += "<div style='font-size: 12px;'><a href='https://www.google.com/maps/search/?api=1&query=" + data[i].lat + "," + data[i].lon + "' target='_blank' title='Click to find " + gym_name + " on Google Maps'>" + address + "</a></div>&nbsp;<br />";
					var pokemon = "<div style='font-size: 18px;'><strong>" + pokemon_name + "</strong></div>";
					
					var times = "<div style='font-size: 14px;" + ((remaining < 20) ? " color: red;" : "") + "'>" + String.fromCodePoint(0x23F0) + start_time.getHours() + ":" + (start_time.getMinutes()<10?'0':'') + start_time.getMinutes() + " - " + end_time.getHours() + ":" + (end_time.getMinutes()<10?'0':'') + end_time.getMinutes();
						times += ((remaining < 45) ? " (" + remaining + "m left)</div>" : "</div>");
					
					if (level > 0) {
						var stars = "<div style='font-size: 16px;'>";
						for (var j =0; j < level; j++) {
							//stars += String.fromCodePoint(0x2B50) + " "; //Use star emoji for level
							stars += "<img src='icons/level.png'> "; //Use Rhydon head
						}
						stars += "</div>";
					} else {
						stars = "";
					}
					
					var attending = "";
					if (interest) { 
						attending += "<div style='font-size: 14px;'>" + String.fromCodePoint(0x1f465) + " Interested: ";
						attending += ((extras) ? (raiders + extras) : raiders);
						attending += "</div>";
					}
					
					var raidID = "<div style='font-size: 10px;'><br/>[Raid ID: " + data[i].id + "]</div>";
					
					var details = "<div style='text-align: center; margin-left: auto; margin-right: auto;'>"+ gym_info + pokemon + stars + times + attending + raidID + "</div>";
					
					if (level == 5) {
						if (pokedex_id == 9995) {
							var marker = new L.Marker(location, {icon: new eggIcon({iconUrl: 'icons/egg_L5.png' })}, { title: name });
						} else {
							pokemonIcon[i] = new raidIcon({iconUrl: 'icons/id_' + pokedex_id +'.png'})
							var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
						}
						marker.bindPopup(details, {maxWidth: '400'});
						raids5.addLayer(marker);
					} else if (level == 4) {
						if (pokedex_id == 9994) {
							var marker = new L.Marker(location, {icon: new eggIcon({iconUrl: 'icons/egg_L4.png' })}, { title: name });	
						} else {
							pokemonIcon[i] = new raidIcon({iconUrl: 'icons/id_' + pokedex_id +'.png'})
							var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
						}
						marker.bindPopup(details, {maxWidth: '400'});
						raids4.addLayer(marker);
					} else if (level == 3) {
						if (pokedex_id == 9993) {
							var marker = new L.Marker(location, {icon: new eggIcon({iconUrl: 'icons/egg_L3.png' })}, { title: name });	
						} else {
							pokemonIcon[i] = new raidIcon({iconUrl: 'icons/id_' + pokedex_id +'.png'})
							var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
						}
						marker.bindPopup(details, {maxWidth: '400'});
						raids3.addLayer(marker);
					} else if (level == 2) {
						if (pokedex_id == 9992) {
							var marker = new L.Marker(location, {icon: new eggIcon({iconUrl: 'icons/egg_L2.png' })}, { title: name });	
						} else {
							pokemonIcon[i] = new raidIcon({iconUrl: 'icons/id_' + pokedex_id +'.png'})
							var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
						}
						marker.bindPopup(details, {maxWidth: '400'});
						raids2.addLayer(marker);
					} else if (level == 1){
						if (pokedex_id == 9991) {
							var marker = new L.Marker(location, {icon: new eggIcon({iconUrl: 'icons/egg_L1.png' })}, { title: name });	
						} else {
							pokemonIcon[i] = new raidIcon({iconUrl: 'icons/id_' + pokedex_id +'.png'})
							var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
						}
						marker.bindPopup(details, {maxWidth: '400'});
						raids1.addLayer(marker);
					} else {
						//Level is X 
						if (remaining > 44) {
							var marker = new L.Marker(location, {icon: new eggIcon({iconUrl: 'icons/egg_X.png' })}, { title: name });	
						} else {
							pokemonIcon[i] = new raidIcon({iconUrl: 'icons/id_' + pokedex_id +'.png'})
							var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
						}
						marker.bindPopup(details, {maxWidth: '400'});
						raidsX.addLayer(marker);
					}
					
				  }
				});
			}		
			
			function getGyms() {
				$.getJSON("getgyms.php", function (data) {
					for (var i = 0; i < data.length; i++) {
						var location = new L.LatLng(data[i].lat, data[i].lon),
							gym_name = data[i].gym_name,
							address = data[i].address;
							if(gym_name.indexOf('[EX') !== -1) {
								//Is EX Gym
								var EX=true;
							} else { 
								var EX=false; 
							}
							
						var gym_info = "<div style='font-size: 18px; color: #0078A8;'>"+ gym_name +"</div>";
						gym_info += "<div style='font-size: 12px;'><a href='https://www.google.com/maps/search/?api=1&query=" + data[i].lat + "," + data[i].lon + "' target='_blank' title='Click to find " + gym_name + " on Google Maps'>" + address + "</a></div>&nbsp;<br />";
						
						var no_raids = "<div style='font-size: 12px;'>No known raid at this gym<br/>If you can see one, please send details <br/>to <?php echo(BOT_NAME); ?> on Telegram.</div>";
						
						var details = "<div style='text-align: center; margin-left: auto; margin-right: auto;'>"+ gym_info + no_raids + "</div>";
						
						if(EX) {
							var marker = new L.Marker(location, {icon: exGymIcon}, { title: name });
							marker.bindPopup(details, {maxWidth: '400'});
							gymsEX.addLayer(marker);
						} else {
							var marker = new L.Marker(location, {icon: gymIcon}, { title: name });	
							marker.bindPopup(details, {maxWidth: '400'});
							gyms.addLayer(marker);							
						}
						
	
					}
				});
			}
		</script>
	</body>
</html>
