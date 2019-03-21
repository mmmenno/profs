<?php

$sparqlQueryString = "
SELECT DISTINCT ?hl ?hlLabel ?wv ?wvLabel  WHERE {
  ?hl wdt:P108 wd:" . $_GET['uni'] . " .
  ?hl wdt:P106 wd:Q1622272.
  ?hl wdt:P101/wdt:P279* wd:" . $_GET['field'] . " .
  ?hl wdt:P101 ?wv .
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
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

$profs = array();

foreach ($data['results']['bindings'] as $k => $v) {
	$profid = str_replace("http://www.wikidata.org/entity/", "", $v['hl']['value']);
	$fieldid = str_replace("http://www.wikidata.org/entity/", "", $v['wv']['value']);

	if(!isset($profs[$profid])){
		$profs[$profid] = array(
			"id" => $profid,
			"name" => $v['hlLabel']['value'],
			"fields" => array($fieldid => $v['wvLabel']['value'])
		);
	}else{
		$profs[$profid]['fields'][$fieldid] = $v['wvLabel']['value'];
	}
}

//print_r($profs);

?>

<table class="table">
	<?php foreach ($profs as $k => $v) { 
		$fieldlinks = array();
		foreach ($v['fields'] as $fk => $fv) { 
			$fieldlinks[] = '<a href="/?field=' . $fk . '">' . $fv . '</a>';
		}
		$fieldlinks = implode(" | ", $fieldlinks);
		?>
		<tr>
			<td>
				<a href="http://www.wikidata.org/entity/<?= $v['id'] ?>" target="_blank">
					<?= $v['name'] ?>
				</a><br />
				<span class="smaller"><?= $fieldlinks ?></span>
			</td>
		</tr>
	<?php } ?>
</table>