<?php
/*
=================================== 
zock! 
 
Developed by 
------------ 
* Michael Schroeder: 
   michael.p.schroeder@gmail.com  
* 
* 
 
http://zock.sf.net 
 
zock! is a free software licensed under GPL (General public license) v3 
      more information look in the root folder for "LICENSE". 
=================================== 
*/
$data = $_REQUEST;

$curves = array();
$curvesArray = split(';',$data['curves']);
array_pop($curvesArray);
foreach ($curvesArray as $c) {
    $curve = split(':',$c);
    array_pop($curve);
    $curves[] = $curve;
}
#$curves = split(':',$curves[0]);
$min = min(min($curves));
foreach ($curves as $i => $c) 
    foreach ($c as $j => $p) $curves[$i][$j] = $p+abs($min);
#print_r($curves);

include('../src/opensource/SVGGraph/SVGGraph.php');
$settings = array('show_label_v' => true, 
			'back_colour' => 'white', 
			'show_divisions' => false,
       		'show_label_h' => false,
			'neg_correction' => abs($min),
            'colours' => array('blue'),
            'marker_colour' => 'blue');
$graph = new SVGGraph(450, 250,$settings);
#$graph->colours = array('blue');
$graph->Values($curves[0]);
$graph->Render('LineGraph');

?>
