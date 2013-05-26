<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../../easyrdf/lib/');
    require_once "EasyRdf.php";

    class Phpspecgen_Term extends EasyRdf_Resource
    {
        public function termLink() {
            $name = htmlspecialchars($this->localName());
            return "<a href=\"#term-$name\">$name</a>";
        }

        public function propertyList($property) {
            $items = array();
            foreach ($this->all($property) as $value) {
                if ($value instanceof Phpspecgen_Term) {
                    array_push($items, $value->termLink());
                } else if ($value instanceof EasyRdf_Resource) {
                    array_push($items, $value->htmlLink($value->shorten()));
                } else {
                    array_push($items, strval($value));
                }
            }
            return $items;
        }

        public function propertyRow($title, $property) {
            $items = $this->propertyList($property);
            if (count($items) > 0) {
                return "<tr><th>$title:</th> <td>".implode(', ', $items)."</td></tr>\n";
            } else {
                return '';
            }
        }
    }

    class Phpspecgen_Class extends Phpspecgen_Term
    {
        public function termType() {
            return 'Class';
        }

        public function inheritedProperties() {
            $properties = array();
            foreach ($this->allParents() as $parent) {
                foreach($parent->all('^rdfs:domain') as $property) {
                    if ($property instanceof Phpspecgen_Term) {
                        array_push($properties, $property->termLink());
                    }
                }
            }
            return $properties;
        }

        protected function allParents($depth=0) {
            $parents = array();
            foreach ($this->all('rdfs:subClassOf') as $parent) {
                if (!$parent instanceof Phpspecgen_Class)
                    continue;
                array_push($parents, $parent);
                if ($depth < 10)
                    $parents = array_merge($parents, $parent->allParents($depth));
            }
            return $parents;
        }
    }

    class Phpspecgen_Property extends Phpspecgen_Term
    {
        public function termType() {
            return 'Property';
        }
    }

    class Phpspecgen_Vocab extends EasyRdf_Resource
    {
        protected function propertyDefinition($title, $property) {
            $values = $this->all($property);
            if (count($values) < 1)
                return '';

            $html = array();
            foreach($values as $value) {
                if ($value instanceof EasyRdf_Literal) {
                    array_push($html, htmlspecialchars($value));
                } else if ($value->get('foaf:homepage')) {
                    array_push($html, $value->get('foaf:homepage')->htmlLink( $value->label() ));
                } else {
                    if ($value->isBnode()) {
                        array_push($html, htmlspecialchars($value->label()) );
                    } else {
                        array_push($html, $value->htmlLink($value->label()) );
                    }
                }
            }

            return "<dt>$title</dt><dd>".implode(', ', array_unique($html))."</dd>\n";
        }

        public function htmlHeader() {
            $html = "<h1>".htmlspecialchars($this->label())."</h1>\n";
            $html .= "<em>".htmlspecialchars($this->get('dc:description|dc11:description|rdfs:comment'))."</em>\n";

            $html .= "<dl>\n";
            $html .= "<dt>Latest Version</dt><dd>".$this->htmlLink()."</dd>\n";
            $html .= $this->propertyDefinition('Created', 'dc:created|dc11:created');
            $html .= $this->propertyDefinition('Date', 'dc:date|dc11:date');
            $html .= $this->propertyDefinition('Revision', 'owl:versionInfo');
            $html .= $this->propertyDefinition('Authors', 'foaf:maker|dc:creator|dc11:creator');
            $html .= $this->propertyDefinition('Contributors', 'dc:contributor|dc11:contributor');
            $html .= "</dl>\n";
            return $html;
        }

        public function htmlSummaryOfTerms() {
            $html = "<h2 id=\"sec-summary\">Summary of Terms</h2>\n";
            $classCount = 0;
            $properyCount = 0;
            foreach($this->all("^rdfs:isDefinedBy") as $term) {
                if ($term instanceof Phpspecgen_Class)
                    $classCount++;
                if ($term instanceof Phpspecgen_Property)
                    $properyCount++;
            }
            $html .= "<p>This vocabulary defines";
            if ($classCount == 0) {
                $html .= " no classes";
            } else if ($classCount == 1) {
                $html .= " one class";
            } else {
                $html .= " $classCount classes";
            }
            if ($properyCount == 0) {
                $html .= " and no properties.";
            } else if ($properyCount == 1) {
                $html .= " and one property.";
            } else {
                $html .= " and $properyCount properties.";
            }
            $html .= "</p>\n";

            $html .= '<table class="table table-hover">'."\n";
            $html .= "<tr><th>Term Name</th><th>Type</th><th>Definition</th></tr>\n";
            foreach($this->all("^rdfs:isDefinedBy") as $term) {
                if ($term instanceof Phpspecgen_Term) {
                    $html .= "<tr>";
                    $html .= "<td>".$term->termLink()."</td>";
                    $html .= "<td>".$term->termType()."</td>";
                    $html .= "<td>".$term->getLiteral('rdfs:comment|rdfs:label')."</td>";
                    $html .= "</tr>\n";
                }
            }
            $html .= "</table>\n";
            return $html;
        }

        public function htmlTerms($type, $title) {
            $html = '';
            $id = strtolower(str_replace(' ','-',$title));
            $html .= "<h2 id=\"sec-$id\">$title</h2>\n";
            foreach($this->all("^rdfs:isDefinedBy") as $term) {
                if (!$term instanceof $type)
                    continue;

                $name = htmlspecialchars($term->localName());
                $html .= "<h3 id=\"term-$name\">$name</h3\n";
                $html .= "<p>".htmlspecialchars($term->get('rdfs:comment'))."</p>\n";
                $html .= '<table class="table table-hover">'."\n";
                $html .= "  <tr><th>URI:</th> <td>".$term->htmlLink()."</td></tr>\n";
                $html .= $term->propertyRow("Label", "rdfs:label");
                $html .= $term->propertyRow("Status", "vs:term_status");
                $html .= $term->propertyRow("Subclasses", "^rdfs:subClassOf");
                $html .= $term->propertyRow("Parent Class", "rdfs:subClassOf");
                $html .= $term->propertyRow("Properties", "^rdfs:domain");
                if ($term instanceof Phpspecgen_Class) {
                    $properties = $term->inheritedProperties();
                    if (count($properties) > 0)
                        $html .= "  <tr><th>Inherited Properties:</th> ".
                                 "<td>".join(', ', $properties)."</td></tr>\n";
                }
                $html .= $term->propertyRow("Range", "rdfs:range");
                $html .= $term->propertyRow("Domain", "rdfs:domain");
                $html .= $term->propertyRow("See Also", "rdfs:seeAlso");
                $html .= "</table>\n";
            }
            return $html;
        }

    }

    # Extra namespaces we use
    EasyRdf_Namespace::set('vann', 'http://purl.org/vocab/vann/');
    EasyRdf_Namespace::set('vs', 'http://www.w3.org/2003/06/sw-vocab-status/ns#');

    ## Add mappings
    EasyRdf_TypeMapper::set('owl:Ontology', 'Phpspecgen_Vocab');
    EasyRdf_TypeMapper::set('owl:Class', 'Phpspecgen_Class');
    EasyRdf_TypeMapper::set('rdfs:Class', 'Phpspecgen_Class');
    EasyRdf_TypeMapper::set('owl:Property', 'Phpspecgen_Property');
    EasyRdf_TypeMapper::set('owl:DatatypeProperty', 'Phpspecgen_Property');
    EasyRdf_TypeMapper::set('owl:ObjectProperty', 'Phpspecgen_Property');
    EasyRdf_TypeMapper::set('owl:InverseFunctionalProperty', 'Phpspecgen_Property');
    EasyRdf_TypeMapper::set('owl:FunctionalProperty', 'Phpspecgen_Property');
    EasyRdf_TypeMapper::set('rdf:Property', 'Phpspecgen_Property');
?>
<html>
<head>
  <title>Music Ontology specification</title>
  <link rel="stylesheet" type="text/css" href="style.css" />
      <!-- Bootstrap -->
    <link href="../css/bootstrap.min.css" rel="stylesheet" media="screen"/>
    <link href="../css/prettify.css" rel="stylesheet" media="screen"/>
    <link href='http://fonts.googleapis.com/css?family=Damion' rel='stylesheet' type='text/css'/>
    <link href="../css/style.css" rel="stylesheet" media="screen"/>
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    </style>
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/prettify.js"></script>
    <script src="../js/mo.js"></script>
</head>
<body onload="prettyPrint()">
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="../index.html">Music Ontology</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li><a href="../index.html">Home</a></li>
              <li><a href="getting-started.html">Getting started</a></li>
              <li class="active"><a href="specification.html">Specification</a></li>
              <li><a href="faq.html">FAQ</a></li>
              <li><a href="https://github.com/motools">Github</a></li>
              <li><a href="https://groups.google.com/forum/#!forum/music-ontology-specification-group">Mailing list</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
    <div class="container">

<?php

    $uri = 'http://purl.org/ontology/mo/';

    if (!empty($uri)) {

      // Parse the document
      $graph = new EasyRdf_Graph($uri);
      $graph->load($uri);

      // Get the first ontology in the document
      $vocab = $graph->get('owl:Ontology', '^rdf:type');
      if (!isset($vocab)) {
          print "<p>Error: No OWL ontologies defined at that URL.</p>\n";
      } else {
          // FIXME: register the preferredNamespacePrefix

          print $vocab->htmlHeader();
          print $vocab->htmlSummaryOfTerms();
          print $vocab->htmlTerms('Phpspecgen_Class', 'Classes');
          print $vocab->htmlTerms('Phpspecgen_Property', 'Properties');
      }

    } 
?>
    </div>
</body>
</html>
