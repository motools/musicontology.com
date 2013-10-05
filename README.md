Music Ontology website
======================

This repository holds the new Music Ontology website.

A test build is available at: 

  http://raimond.me.uk/resources/musicontology.com


Generating the specification
----------------------------

Run the following to re-generate the specification from HEAD:

    $ rake

This will pull the latest version of the ontology and generate
the specification using EasyRDF's phpspecgen tool.

Apache configuration
--------------------

The website uses both mod\_negotiation and mod\_rewrite.
The http://purl.org/ontology/mo/ namespace should redirect
to /terms/.

The /terms/ location should have the following rules.

    RewriteEngine On
    RewriteBase /specification/
    RewriteRule ^$ /specification/ [R=303,L]
    RewriteRule ^(.+)$ /specification/#term-$1 [R=303,NE]

The /specification/ location should have the following index.

    DirectoryIndex index.var

And the handler for mod\_negotiation type maps need to be registered.

    AddHandler type-map .var
