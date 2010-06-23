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


$curves = split(':',$data['curves']);
array_pop($curves);
$min = min($curves);

include('../src/opensource/SVGGraph/SVGGraph.php');
$settings = array('show_label_v' => true, 
			'back_colour' => 'white', 
			'title' => $data['title'], 
			'description' => $data['description'],
			'show_divisions' => false,
			'show_label_h' => false,
			'neg_correction' => abs($min));
$graph = new SVGGraph(450, 250,$settings);

$graph->colours = array('red','green','blue');
foreach ($curves as $i => $c) $curves[$i] = $c+abs($min);
$graph->Values($curves);
$graph->Links('/Tom/', '/Dick/', '/Harry/');
$graph->Render('LineGraph');

?>
