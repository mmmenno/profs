<?php

if(isset($_GET['field'])){
	$branch = $_GET['field'];
}else{
	$branch = "Q7991";
}

$sparqlQueryString = "SELECT * WHERE {
							wd:" . $branch . " rdfs:label ?label .
							FILTER (langMatches( lang(?label), \"NL\" ) )
						} 
						LIMIT 1";


$queryurl = "https://query.wikidata.org/#" . rawurlencode($sparqlQueryString);
$opts = [
		    'http' => [
		        'method' => 'GET',
		        'header' => [
		            'Accept: application/sparql-results+json'
		        ],
		    ],
		];
$context = stream_context_create($opts);
$endpointUrl = 'https://query.wikidata.org/sparql';
$url = $endpointUrl . '?query=' . urlencode($sparqlQueryString);
$response = file_get_contents($url, false, $context);
$data = json_decode($response, true);

$branchname = $data['results']['bindings'][0]['label']['value'];

?>
<!DOCTYPE html>
<html>
<head>
	
	<title>Hoogleraren in <?= $branchname ?></title>

	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link href="https://fonts.googleapis.com/css?family=Nunito:300,700" rel="stylesheet">

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.1.0/dist/leaflet.css" integrity="sha512-wcw6ts8Anuw10Mzh9Ytw4pylW8+NAD4ch3lqm9lzAsTxg0GFeJgoAtxuCLREZSC5lUXdVyo/7yfsqFjQ4S+aKw==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.1.0/dist/leaflet.js" integrity="sha512-mNqn2Wg7tSToJhvHcqfzLMU6J4mkOImSPTxVZAdo+lcPlk+GhZmYgACEe0x35K7YzW1zJ7XyJV/TT1MrdXvMcA==" crossorigin=""></script>

     <link rel="stylesheet" href="styles.css" />

	
	
</head>
<body>


<div class="container-fluid">
	<div class="col-md-12">
		<div id="vakgebieden">
			<a href="/?field=Q34749">sociale wetenschappen</a> |
			<a href="/?field=Q7991">natuurwetenschappen</a> |
			<a href="/?field=Q864928">levenswetenschappen</a> |
			<a href="/?field=Q816264">formele wetenschap</a> |
			<a href="/?field=Q80083">geesteswetenschappen</a>
		</div>
		<h1>Hoogleraren in de <?= $branchname ?></h2>
	</div>
</div>

<div class="container-fluid">
	<div class="col-md-3">
		<div id="uni">
			<h2>Over de data</h2>
		</div>
		<div id="profs">
			<p>
				De gebruikte data komt uit Wikidata, en op dit moment toont deze applicatie vooral bij welke hoogleraren zowel het vakgebied als werkgever zijn ingevoerd.
			</p>
		</div>

	</div>
	<div class="col-md-9">
		<div id="bigmap"></div>
	</div>
</div>

<script>

	

	

	$(document).ready(function(){

		console.log('dsdsf');
		createMap();

		refreshMap();


	});

	function createMap(){
		center = [52.369716,4.900029];
		zoomlevel = 8;
		
		map = L.map('bigmap', {
	        center: center,
	        zoom: zoomlevel,
	        minZoom: 6,
	        maxZoom: 20,
	        scrollWheelZoom: false
	    });

		L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/dark_nolabels/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
			id: 'CartoDB.DarkMatterNoLabels',
			minZoom: 0,
			maxZoom: 20,
			ext: 'png'
		}).addTo(map);

	
	}

	function refreshMap(){

		$('#straatinfo').append('<h2>Aan het laden ...</h2><div class="loader"></div>');

		$.ajax({
	        type: 'GET',
	        url: 'geojson.php?field=<?= $branch ?>',
	        dataType: 'json',
	        success: function(jsonData) {

	            if (typeof unis !== 'undefined') {
				    map.removeLayer(unis);
				}

	            unis = L.geoJson(null, {
	            	pointToLayer: function (feature, latlng) {                    
		                return new L.CircleMarker(latlng, {
		                    color: "#FC3272",
		                    radius:8,
		                    weight: 1,
		                    opacity: 1,
		                    fillOpacity: 0.7
		                });
		            },
				    style: function(feature) {
				        return {
				            radius: getSize(feature.properties.nr),
				            clickable: true
				        };
				    },
				    onEachFeature: function(feature, layer) {
						layer.on({
					        click: whenStreetClicked
					    });
				    }
				}).addTo(map);

	            unis.addData(jsonData).bringToFront();
			    

	            map.fitBounds(unis.getBounds());
	            
	            $('#straatinfo').html('');
	        },
	        error: function() {
	            console.log('Error loading data');
	        }
	    });
	}



	function getSize(nr) {
		var d = nr*2;
		if (d<5){
			d = 5;
		}
	    return d;
	}

	function whenStreetClicked(){
		var props = $(this)[0].feature.properties;
		var info = '<h2>' + props['unilabel'] + '</h2>';
		$('#uni').html(info);

		$('#profs').load('profs.php?uni=' + props['uni'] + '&field=<?= $branch ?>');
	}

</script>



</body>
</html>
