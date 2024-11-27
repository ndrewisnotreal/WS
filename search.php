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
<body style="background-color: #b3bddb;">
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
        </ul>
      </div>
    </div>
  </nav>
  <!-- Navbar -->
    
    
    <div id="gradient"></div>
<form class="searchbox" role="form" method="POST">
        <input type="search" name="lake" placeholder="Search">
        <button type="submit" name="submit" value="Search">&nbsp;</button>
    </form>


        <?php
        if (isset($_POST['lake'])) {
            $result = $sparql->query(
                'SELECT * WHERE {'.
                '  ?lake rdf:type dbo:Lake .'.
                '  ?lake rdfs:label ?label .'.
                '  ?lake dbo:location dbr:North_Sumatra .'.
                '  FILTER ( lang(?label) = "en" ) .'.
                '  FILTER regex(?label, "' .$_POST['lake']. '", "i") .'.
                '} ORDER BY ?label'
            );
        ?>
        
        <div class="container">
                    <div class="row">
            <table class="table table-dark table-striped" id="DataTables">  
                <thead style='background-color:#19afe8;color:white;'>
                    <tr>
                        <th style='text-align:center;'>No</th>
                        <th style='text-align:center;'>Danau</th>
                        <th style='text-align:center;'>Link</th>
                        <th style='text-align:center;'>Informasi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                foreach ($result as $row) {
                    echo"
                            <tr>  
                                <td>" . $no++ . "</td>
                                <td>" . $row->label . "</td>
                                <td>" . $row->lake . "</td>
                                <td>
                                <form method='POST' action='detail.php'>
                                    <input type='hidden' value='" . $row->lake . "' name='lake'/>
                                    <button type='submit' name='airportName' class='btn btn-outline-primary'>Detail</button>
                                </form>
                                </td>
                            </tr>
                        ";
                }?>
                </tbody>
            </table>
        <?php } ?>
    </div>
        </div>
            </div>
</body>
</html>
