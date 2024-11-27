<?php
require 'vendor/autoload.php';
require_once __DIR__ . "/html_tag_helpers.php";

    $uri_rdf = 'http://localhost/WS/lake.rdf';
    $data = \EasyRdf\Graph::newAndLoad($uri_rdf);
    $doc = $data->primaryTopic();

    // echo $doc->get('foaf:name');

    $lake_uri = [];
	foreach ($doc->all('owl:sameAs') as $akun) {
        array_push($lake_uri, $akun->get('foaf:homepage'));
	}

    \EasyRdf\RdfNamespace::set('geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
	\EasyRdf\RdfNamespace::set('foaf', 'http://xmlns.com/foaf/0.1/');
	\EasyRdf\RdfNamespace::set('dbp', 'http://dbpedia.org/property/');
	\EasyRdf\RdfNamespace::set('dbo', 'http://dbpedia.org/ontology/');
	\EasyRdf\RdfNamespace::set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	\EasyRdf\RdfNamespace::set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');

    $sparql_endpoint = 'https://dbpedia.org/sparql';
	$sparql = new \EasyRdf\Sparql\Client($sparql_endpoint);

    $results = [];
    foreach ($lake_uri as $uri) {
		$sparql_query = '
            SELECT distinct * WHERE {
                <' . $uri . '> rdfs:label ?label ;
                    rdfs:comment ?info ;
                    foaf:isPrimaryTopicOf ?wiki ;
                    geo:lat ?lat ;
                    geo:long ?long .
                    FILTER ( lang(?info) = "en" )
            }
        ';
        array_push($results, $sparql->query($sparql_query));
	}

	$lake = [];
	$detail = [];
	foreach ($results as $result) {
        foreach ($result as $row) {
            $detail = [
                'lake' => $row->label,
                'info' => $row->info,
                'lat' => $row->lat,
                'long' => $row->long,
                'wiki' => $row->wiki,
            ];
            array_push($lake, $detail);
    
            break;
        }
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>RDF</title>
</head>
<body style="background-color: #f0f4ff;">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-md bg-dark navbar-dark fixed-top">
    <div class="container-fluid">
      <button
        class="navbar-toggler"
        type="button"
        data-mdb-toggle="collapse"
        data-mdb-target="#navbarExample01"
        aria-controls="navbarExample01"
        aria-expanded="false"
        aria-label="Toggle navigation" >
        <i class="fas fa-bars"></i>
      </button>
      <div class="collapse navbar-collapse" id="navbarExample01">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item active">
            <a class="nav-link" aria-current="page" href="index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="search.php">Search</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- Navbar -->

  <!-- Background image -->
  <div
    class="p-5 text-center bg-image"
    style="
      background-image: url('ih.jpg');
      height: 600px;
    "
  >
  <center>
    <div class="mask">
      <div class="d-flex justify-content-center align-items-center h-150">
        <div class="text-black" style="margin-top:180px;">
          <h1 class="mb-3">Tempat Wisata di Indonesia</h1>
          <a class="btn btn-outline-light" href="search.php" role="button"><div class="text-black">Search</div></a
          >
        </div>
      </div>
    </div>
  </center>
  </div>
  <!-- Background image -->


  <div class="container">
  <h1 style="margin-top: 50px;margin-bottom: 30px;font-family: 'Lora', serif;font-size: 60px;"><?= $doc->get('foaf:name') ?></h1>
    <div class="p">
    <?php 
        foreach ($lake as $detail) {
            echo "<p>".$detail['lake']."</p>";
            echo "<p>".$detail['info']."</p>";
            echo "<p>".$detail['lat']."</p>";
            echo "<p>".$detail['long']."</p>";
            \EasyRdf\RdfNamespace::setDefault('og');
            
			$wiki = \EasyRdf\Graph::newAndLoad($detail['wiki']);
			$foto_url = $wiki->image;
            echo "<img src=". $foto_url ." width="."10px"." />";
            echo "<br>";
        }
    ?>
    <!-- <p><?= $detail['lake']; ?></p>
    <p><?= $detail['info']; ?></p>
    <p><?= $detail['lat']; ?></p>
    <p><?= $detail['long']; ?></p> -->
  </div>
  </div>

<div class="list">
<hr>
    <center><h2>List of lakes</h2>
    <ul>
    <?php
        $result = $sparql->query(
            'SELECT * WHERE {'.
            '  ?lake rdf:type dbo:Lake .'.
            '  ?lake rdfs:label ?label .'.
            '  ?lake dbo:location dbr:North_Sumatra .'.
            '  FILTER ( lang(?label) = "en" )'.
            '} ORDER BY ?label'
        );
        foreach ($result as $row) {
            echo "<li>".$row->label." || ".$row->lake."</li>\n";
        }
    ?>
    </ul>
    <p>Total number of lakes: <?= $result->numRows() ?></p>
    <hr>
</div>
</center>
    
    
    
   

</body>
</html>
