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

    class Phpspecgen_Individual extends Phpspecgen_Term
    {
        public function termType() {
            return 'Individual';
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
                if ($term instanceof Phpspecgen_Class) {
                    $html .= "<tr>";
                    $html .= "<td>".$term->termLink()."</td>";
                    $html .= "<td>".$term->termType()."</td>";
                    $html .= "<td>".$term->getLiteral('rdfs:label')."</td>";
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
                $html .= "<section id=\"term-$name\">\n";
                $html .= "<h3>$name</h3>\n";
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
                if ($term instanceof Phpspecgen_Individual) {
                    $html .= $term->propertyRow("Type", "rdf:type");
                }
                $html .= $term->propertyRow("Range", "rdfs:range");
                $html .= $term->propertyRow("Domain", "rdfs:domain");
                $html .= $term->propertyRow("See Also", "rdfs:seeAlso");
                $html .= "</table>\n";
                $html .= "</section>\n";
            }
            return $html;
        }

    }

    # Extra namespaces we use
    EasyRdf_Namespace::set('vann', 'http://purl.org/vocab/vann/');
    EasyRdf_Namespace::set('vs', 'http://www.w3.org/2003/06/sw-vocab-status/ns#');
    EasyRdf_Namespace::set('mo', 'http://purl.org/ontology/mo/');

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
    EasyRdf_TypeMapper::set('mo:ReleaseType', 'Phpspecgen_Individual');
    EasyRdf_TypeMapper::set('mo:ReleaseStatus', 'Phpspecgen_Individual');
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Music Ontology specification</title>
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
    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        
  ga('create', 'UA-44632233-1', 'musicontology.com');
  ga('send', 'pageview');
    </script>
</head>
<body onload="prettyPrint()">
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="..">Music Ontology</a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
              <li><a href="..">Home</a></li>
              <li><a href="../docs/getting-started.html">Getting started</a></li>
              <li class="active"><a href="../specification">Specification</a></li>
              <li><a href="../docs/faq.html">FAQ</a></li>
              <li><a href="http://wiki.musicontology.com/">Wiki</a></li>
              <li><a href="https://github.com/motools">Github</a></li>
              <li><a href="https://groups.google.com/forum/#!forum/music-ontology-specification-group">Mailing list</a></li>
            </ul>
        </div>
      </div>
    </div>

    <div class="container">
<?php

    // Parse the document
    $uri = 'http://purl.org/ontology/mo/';
    $graph = new EasyRdf_Graph($uri);
    $graph->parseFile('../mo/rdf/musicontology.n3', 'turtle', $uri);

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
        print $vocab->htmlTerms('Phpspecgen_Individual', 'Individuals');
    }
?>
</div>
</body>
</html>
