<?php require_once('./config.php'); ?>
    
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin=""/>
        <title>Raid Map</title>
        <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>
        <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
        <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
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
        
            var defaultCentre = new L.LatLng(<?php echo(MAP_CENTRE); ?>); 
            var mapToken = '<?php echo(MAP_TOKEN); ?>'; 
            var autoLocate = <?php echo(MAP_AUTOLOCATE); ?>; 
        
            var map, tiles, darkTiles, outdoorsTiles, satelliteTiles, raids1, raids2, raids3, raids4, raids5, raidsX, gyms, gymsEX, questpoke, questitem, pokestop, rocketstop;
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
                    iconSize:     [60, 60],
                    iconAnchor:   [32, 32],
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
            
            var pokestopIcon = L.icon({
                iconSize:     [20, 20],
                iconAnchor:   [10, 17], 
                popupAnchor:  [-3, -10],    
                iconUrl: 'icons/quests/pokestop.png'
            });

            var rocketstopIcon = L.icon({
				iconSize:     [20, 40],
				iconAnchor:   [10, 17], 
				popupAnchor:  [-3, -10],	
				iconUrl: 'icons/rocketstop.png'
			});
			
            var questPokeIcon = L.Icon.extend({
                options: {
                    iconSize:     [70, 70],
                    iconAnchor:   [24, 24],
                    popupAnchor:  [0, -0],
                    shadowUrl: 'icons/quests/pokestop.png',
                    shadowSize:   [32, 32],
                    shadowAnchor: [18, 12]
                }
            });

            var questItemIcon = L.Icon.extend({
                options: {
                    iconSize:     [32, 32],
                    iconAnchor:   [24, 24],
                    popupAnchor:  [0, 0],
                    shadowUrl: 'icons/quests/pokestop.png',        
                    shadowSize:   [32, 32],
                    shadowAnchor: [24, 24]                    
                }
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
                questpoke = new L.FeatureGroup();
                questitem = new L.FeatureGroup();
                pokestop = new L.FeatureGroup();
				rocketstop = new L.FeatureGroup();
                
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
                    zoom: 14,
                    layers: [tiles, raidsX, raids1, raids2, raids3, raids4, raids5, questpoke, questitem, rocketstop],
                    fullscreenControl: true
                });
                
                if(autoLocate == true) {
                    map.locate({setView: true, maxZoom: 16});

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
                    <?php 
                        if (MAP_SHOW_QUESTS) {
                            echo('"Quests Pokemon": questpoke,
                                    "Quests Item": questitem,
                                    "Pokestop": pokestop,
							        "Team Rocket": rocketstop,
                                           '); 
                        }
                    
                        if (MAP_SHOW_GYMS) {
                            echo('"EX Gyms": gymsEX,
                                "Other Gyms": gyms');
                        }
                    ?>
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
                    <?php if(MAP_SHOW_GYMS) { echo ('getGyms(); getStop();'); } ?>
                }
                raids1.clearLayers();
                raids2.clearLayers();
                raids3.clearLayers();
                raids4.clearLayers();
                raids5.clearLayers();
                getRaids();
                questpoke.clearLayers();
                questitem.clearLayers();
                getQuestPoke();
				rocketstop.clearLayers();
				getRocketStop();
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
                        start_time = new Date((data[i].start_timeNOW).replace(/-/g,"/")),
                        end_time = new Date((data[i].end_timeNOW).replace(/-/g,"/")),
                        remaining = Math.floor(data[i].t_left / 60),
                        interest = data[i].interest,
                        raiders = parseInt(data[i].count),
                        extras = parseInt(data[i].total_extras);
                        
                    var gym_info = "<div style='font-size: 18px; color: #0078A8;'>"+ gym_name +"</div>";
                        gym_info += "<div style='font-size: 12px;'><a href='https://www.google.com/maps/search/?api=1&query=" + data[i].lat + "," + data[i].lon + "' target='_blank' title='Click to find " + gym_name + " on Google Maps'>" + address + "</a></div>&nbsp;<br />";
                    var pokemon = "<div style='font-size: 18px;'><strong>" + pokemon_name + "</strong></div>";
                    
                    var times = "<div style='font-size: 14px;" + ((remaining < 20) ? " color: red;" : "") + "'>";
                    times += (level == "X") ? "<strong>" + start_time.toLocaleDateString() + "</strong><br>" : "";
                    times += String.fromCodePoint(0x23F0) + start_time.getHours() + ":" + (start_time.getMinutes()<10?'0':'') + start_time.getMinutes() + " - " + end_time.getHours() + ":" + (end_time.getMinutes()<10?'0':'') + end_time.getMinutes();
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
                    
                    var raid_footer = "";
                    if (level == "X") {
                        raid_footer += "<div style='font-size: 12px;'><?php if (defined('MAP_EX_RAID_FOOTER') && !empty(MAP_EX_RAID_FOOTER)) { echo('<br>');echo(MAP_EX_RAID_FOOTER); } ?></div>";
                    } else {
                        raid_footer += "<div style='font-size: 12px;'><?php if (defined('MAP_RAID_FOOTER') && !empty(MAP_RAID_FOOTER)) { echo('<br>');echo(MAP_RAID_FOOTER); } ?></div>";
                    }

                    var raidID = "<div style='font-size: 10px;'><br/>[Raid ID: " + data[i].id + "]</div>";
                    
                    var details = "<div style='text-align: center; margin-left: auto; margin-right: auto;'>"+ gym_info + pokemon + stars + times + attending + raid_footer + raidID + "</div>";
                    
                    if (level == 5) {
                        if (pokedex_id == 9995) {
                            var marker = new L.Marker(location, {icon: new eggIcon({iconUrl: 'icons/egg_L5.png' })}, { title: name });
                        } else {
                            pokemonIcon[i] = new raidIcon({iconUrl: 'icons<?php echo("/" . MAP_ICONPACK); echo("/" . MAP_ICONPACK_PREFIX); ?>' + pokedex_id +'.png'})
                            var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
                        }
                        marker.bindPopup(details, {maxWidth: '400'});
                        raids5.addLayer(marker);
                    } else if (level == 4) {
                        if (pokedex_id == 9994) {
                            var marker = new L.Marker(location, {icon: new eggIcon({iconUrl: 'icons/egg_L4.png' })}, { title: name });    
                        } else {
                            pokemonIcon[i] = new raidIcon({iconUrl: 'icons<?php echo("/" . MAP_ICONPACK); echo("/" . MAP_ICONPACK_PREFIX); ?>' + pokedex_id +'.png'})
                            var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
                        }
                        marker.bindPopup(details, {maxWidth: '400'});
                        raids4.addLayer(marker);
                    } else if (level == 3) {
                        if (pokedex_id == 9993) {
                            var marker = new L.Marker(location, {icon: new eggIcon({iconUrl: 'icons/egg_L3.png' })}, { title: name });    
                        } else {
                            pokemonIcon[i] = new raidIcon({iconUrl: 'icons<?php echo("/" . MAP_ICONPACK); echo("/" . MAP_ICONPACK_PREFIX); ?>' + pokedex_id +'.png'})
                            var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
                        }
                        marker.bindPopup(details, {maxWidth: '400'});
                        raids3.addLayer(marker);
                    } else if (level == 2) {
                        if (pokedex_id == 9992) {
                            var marker = new L.Marker(location, {icon: new eggIcon({iconUrl: 'icons/egg_L2.png' })}, { title: name });    
                        } else {
                            pokemonIcon[i] = new raidIcon({iconUrl: 'icons<?php echo("/" . MAP_ICONPACK); echo("/" . MAP_ICONPACK_PREFIX); ?>' + pokedex_id +'.png'})
                            var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
                        }
                        marker.bindPopup(details, {maxWidth: '400'});
                        raids2.addLayer(marker);
                    } else if (level == 1){
                        if (pokedex_id == 9991) {
                            var marker = new L.Marker(location, {icon: new eggIcon({iconUrl: 'icons/egg_L1.png' })}, { title: name });    
                        } else {
                            pokemonIcon[i] = new raidIcon({iconUrl: 'icons<?php echo("/" . MAP_ICONPACK); echo("/" . MAP_ICONPACK_PREFIX); ?>' + pokedex_id +'.png'})
                            var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
                        }
                        marker.bindPopup(details, {maxWidth: '400'});
                        raids1.addLayer(marker);
                    } else {
                        //Level is X 
                        if (remaining > 44) {
                            var marker = new L.Marker(location, {icon: new eggIcon({iconUrl: 'icons/egg_X.png' })}, { title: name });    
                        } else {
                            pokemonIcon[i] = new raidIcon({iconUrl: 'icons<?php echo("/" . MAP_ICONPACK); echo("/" . MAP_ICONPACK_PREFIX); ?>' + pokedex_id +'.png'})
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
                            ex_gym = data[i].ex_gym,
                            gym_note = data[i].gym_note,
                            address = data[i].address;
                            
                        var gym_info = "<div style='font-size: 18px; color: #0078A8;'>"+ gym_name +"</div>";
                        gym_info += "<div style='font-size: 12px;'><a href='https://www.google.com/maps/search/?api=1&query=" + data[i].lat + "," + data[i].lon + "' target='_blank' title='Click to find " + gym_name + " on Google Maps'>" + address + "</a></div>&nbsp;<br />";
                        
                        var gym_footer = "<div style='font-size: 12px;'><?php if (defined('MAP_GYM_FOOTER') && !empty(MAP_GYM_FOOTER)) { echo(MAP_GYM_FOOTER); } ?></div>";
                        
                        if(gym_note == null) {
                        var details = "<div style='text-align: center; margin-left: auto; margin-right: auto;'>"+ gym_info + gym_footer + "</div>";
                        } else {
                        var details = "<div style='text-align: center; margin-left: auto; margin-right: auto;'>"+ gym_info + gym_footer + gym_note + "</div>";    
                        }
                        
                        if(ex_gym == 1) {
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
            
            function getStop() {
                $.getJSON("getpokestop.php", function (data) {
                    for (var i = 0; i < data.length; i++) {
                        var location = new L.LatLng(data[i].lat, data[i].lon),
                            pokestop_name = data[i].pokestop_name,
                            address = data[i].address;
                            
                        var pokestop_info = "<div style='font-size: 18px; color: #0078A8;'>"+ pokestop_name +"</div>";
                        pokestop_info += "<div style='font-size: 12px;'><a href='https://www.google.com/maps/search/?api=1&query=" + data[i].lat + "," + data[i].lon + "' target='_blank' title='Click to find " + pokestop_name + " on Google Maps'>" + address + "</a></div>&nbsp;<br />";
                        
                        var details = "<div style='text-align: center; margin-left: auto; margin-right: auto;'>"+ pokestop_info + "</div>";
                    
                        var marker = new L.Marker(location, {icon: pokestopIcon}, { title: name });
                            marker.bindPopup(details, {maxWidth: '400'});
                            pokestop.addLayer(marker);
                    
                    }
                });
            }            

			function getRocketStop() {
				$.getJSON("getrocketstop.php", function (data) {
					for (var i = 0; i < data.length; i++) {
						var location = new L.LatLng(data[i].lat, data[i].lon),
							pokestop_name = data[i].pokestop_name,
							address = data[i].address,
							comment = data[i].comment;
							
						
						var pokestop_info = "<div style='font-size: 18px; color: #0078A8;'>"+ pokestop_name +"</div>";
						pokestop_info += "<div style='font-size: 12px;'><a href='https://www.google.com/maps/search/?api=1&query=" + data[i].lat + "," + data[i].lon + "' target='_blank' title='Click to find " + pokestop_name + " on Google Maps'>" + address + "</a></div>&nbsp;<br />";
						if( comment )
							pokestop_info +=  "Info:" + comment;
						
						var details = "<div style='text-align: center; margin-left: auto; margin-right: auto;'>"+ pokestop_info + "</div>";
					
						var marker = new L.Marker(location, {icon: rocketstopIcon}, { title: name });
							marker.bindPopup(details, {maxWidth: '400'});
							rocketstop.addLayer(marker);
					
					}
				});
			}			

            
            function getQuestPoke() {
                
                $.getJSON("getquest.php", function (data) {
                    for (var i = 0; i < data['stops'].length; i++) {
                        var location = new L.LatLng(data['stops'][i].lat, data['stops'][i].lon),
                            pokestop_name = data['stops'][i].pokestop_name,
                            pokemon_name = data['stops'][i],
                            pokedex_id = data['stops'][i].pokedex_ids,
                            address = data['stops'][i].address,
                            quest_id = data['stops'][i].quest_id,
                            reward_id = data['stops'][i].reward_id,
                            quest_type = data['stops'][i].quest_type,
                            quest_quantity = data['stops'][i].quest_quantity,
                            quest_action = data['stops'][i].quest_action,
                            quest_poketypes = data['stops'][i].quest_poketypes.split(','),
                            quest_pokedex_ids = data['stops'][i].quest_pokedex_ids,
                            reward_pokedex_ids = data['stops'][i].pokedex_ids,
                            reward_type = data['stops'][i].reward_type,
                            reward_quantity = data['stops'][i].reward_quantity;
                            
                            if( pokedex_id ){
                                pokedex_id = pokedex_id.split(',');
                                pokedex_id = pokedex_id[0];
                            }

                            if( reward_pokedex_ids ){
                                reward_pokedex_ids = reward_pokedex_ids.split(',');
                            }

                            var quest_pokedex_ids_string = "",
                                quest_poketypes_string = "",
                                reward_pokedex_ids_string = "";
                            
                            if( typeof quest_pokedex_ids == "object" ){
                                for( var b = 0; quest_pokedex_ids.length >= b; b++){
                                    if( quest_pokedex_ids.length == 1 || b == 0 ){
                                        quest_pokedex_ids_string += data['translations'][quest_pokedex_ids[b] - 1]
                                    }else if( quest_pokedex_ids.length -1 == b ){
                                        quest_pokedex_ids_string += " / " + data['translations'][quest_pokedex_ids[b] - 1];
                                    }else{
                                        quest_pokedex_ids_string += ", ";
                                        quest_pokedex_ids_string += data['translations'][quest_pokedex_ids[b] - 1];
                                    }
                                }
                            }else if( quest_pokedex_ids != "0" ) {
                                quest_pokedex_ids_string += data['translations'][quest_pokedex_ids - 1];
                            }

                            if( typeof reward_pokedex_ids == "object" && reward_pokedex_ids != null ){
                                for( var c = 0; reward_pokedex_ids.length >= c; c++){
                                    if( reward_pokedex_ids[c] ){
                                        if( reward_pokedex_ids.length == 1 || c == 0 ){
                                            reward_pokedex_ids_string += data['translations'][reward_pokedex_ids[c] - 1]
                                        }else if( reward_pokedex_ids.length -1 == c ){
                                            reward_pokedex_ids_string += " / " + data['translations'][reward_pokedex_ids[c] - 1];
                                        }else{
                                            reward_pokedex_ids_string += ", ";
                                            reward_pokedex_ids_string += data['translations'][reward_pokedex_ids[c] - 1];
                                        }
                                    }
                                }
                            }else if( reward_pokedex_ids != "0" ){
                                reward_pokedex_ids_string += data['translations'][reward_pokedex_ids - 1];
                            }
                            
                            if( typeof quest_poketypes == "object" && quest_poketypes[0] != "0" ){
                                for( a = 0; quest_poketypes.length >= a; a++){
                                    if( quest_poketypes[a] ){
                                        if( quest_poketypes.length == 1 || a == 0 ) {
                                            quest_poketypes_string += data['translations']["pokemon_type_" + quest_poketypes[a]].EN;
                                        }else if( quest_poketypes.length -1 == a ){
                                            quest_poketypes_string += " / " + data['translations']["pokemon_type_" + quest_poketypes[a]].EN;
                                        }else{
                                            quest_poketypes_string +=  ", " + data['translations']["pokemon_type_" + quest_poketypes[a]].EN;
                                        }
                                    }
                                }
                            }
                            
                        var pokestop_info = "<div style='font-size: 18px; color: #0078A8;'>"+ pokestop_name +"</div>";

                        pokestop_info += "<div style='font-size: 12px;'><a href='https://www.google.com/maps/search/?api=1&query=" + data['stops'][i].lat + "," + data['stops'][i].lon + "' target='_blank' title='Click to find " + pokestop_name + " on Google Maps'>" + address + "</a></div>&nbsp;<br />";
                        var q_type      = data['translations']['quest_type_' + quest_type].<?php echo LANGUAGE; ?>,
                            dat_action  = ( quest_action != "0" ? data['translations']['quest_action_' + quest_action].<?php echo LANGUAGE; ?> : '' );
                            arr_action  = dat_action.split(':');
                            q_action    = ( ( quest_quantity == 1 ) ? arr_action[0] : arr_action[1] );
                            
                        var r_type      = data['translations']['reward_type_' + reward_type].<?php echo LANGUAGE; ?>;
                            dat_r_type  = r_type.split(':');
                            r_type      = ( ( reward_quantity == 1 ) ? dat_r_type[0] : dat_r_type[1] );
                        
                        var quest_info = "<div style='font-size: 14px;'>Quest: "+ q_type +
                                         ' ' + quest_quantity + 
                                         ' ' + ( q_action ? q_action : 
                                             ( quest_pokedex_ids_string != "" ? quest_pokedex_ids_string :
                                                 ( quest_poketypes_string != "" ? quest_poketypes_string : "" ) ) ) +
                                         "<br>Reward: " + ( r_type == "Pokemon" ? reward_pokedex_ids_string : reward_quantity + " " + r_type ) +
                                         "        </div>";
                        
                        var quest_footer = "<div style='font-size: 12px;'><?php if (defined('MAP_QUEST_FOOTER') && !empty(MAP_QUEST_FOOTER)) { echo('<br>');echo(MAP_QUEST_FOOTER); } ?></div>";

                        var questID = "<div style='font-size: 10px;'><br/>[Quest ID: " + quest_id + "]</div>";

                        var details = "<div style='text-align: center; margin-left: auto; margin-right: auto;'>"+ pokestop_info  + quest_info + quest_footer + questID + "</div>";
                        
                    if( pokedex_id && reward_type == 1 ){
                        
                                pokemonIcon[i] = new questPokeIcon({iconUrl: 'icons<?php echo("/" . MAP_ICONPACK); echo("/" . MAP_ICONPACK_PREFIX); ?>' + pokedex_id +'.png'})
                    var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
                        marker.bindPopup(details, {maxWidth: '400'});
                        questpoke.addLayer(marker);
                
                        }else{
                            
                    pokemonIcon[i] = new questItemIcon({iconUrl: 'icons/quests/reward_type_' + reward_type + '.png'});
                    var marker = new L.Marker(location, {icon: pokemonIcon[i] }, { title: name });
                        marker.bindPopup(details, {maxWidth: '400'});
                        questitem.addLayer(marker);
                        }
                        
                        

                    }
                });
            }
        </script>
    </body>
</html>
