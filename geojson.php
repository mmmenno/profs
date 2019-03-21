<?php

if(isset($_GET['field'])){
	$branch = $_GET['field'];
}else{
	$branch = "Q7991";
}

$sparqlQueryString = "
SELECT DISTINCT ?uni ?uniLabel ?coords (COUNT(?hl) AS ?nr) WHERE {
  ?uni wdt:P17 wd:Q55.
  ?uni wdt:P31/wdt:P279* wd:Q3918.
  ?uni wdt:P625 ?coords . 
  ?hl wdt:P108 ?uni .
  ?hl wdt:P106 wd:Q1622272.
  ?hl wdt:P101/wdt:P279* wd:" . $branch . " .
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
GROUP BY ?uni ?uniLabel ?coords LIMIT 25
";


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


$geojson = array("type"=>"FeatureCollection");

foreach ($data['results']['bindings'] as $k => $v) {
	$coords = str_replace(array("Point(",")"),"",$v['coords']['value']);
	$coords = explode(" ", $coords);
	$geojson['features'][] = array(
		"type" => "Feature",
		"properties" => array(
			"nr" => $v['nr']['value'],
			"uni" => str_replace("http://www.wikidata.org/entity/", "", $v['uni']['value']),
			"unilabel" => $v['uniLabel']['value']
		),
		"geometry" => array(
			"type" => "Point",
			"coordinates" => array(
				(float)$coords[0],(float)$coords[1]
			)
		)
		
	);
}

//print_r($geojson);

header('Content-Type: application/json');

echo json_encode($geojson);


?>