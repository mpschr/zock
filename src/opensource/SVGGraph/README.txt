SVGGraph Library version 1.2.1
==============================

This library provides PHP classes and functions for easily creating SVG
graphs from data.

Here is a basic example:
 $graph = new SVGGraph(640, 480);
 $graph->colours = array('red','green','blue');
 $graph->Values(100, 200, 150);
 $graph->Links('/Tom/', '/Dick/', '/Harry/');
 $graph->Render('BarGraph');


Graph types
===========
At the moment there are four types of graph supported by SVGGraph:
 
 BarGraph   - vertical bars, optionally hyperlinked;

 LineGraph  - a line joining the data points, with optionally hyperlinked 
              markers at the data points;

 PieGraph   - a pie chart, with optionally hyperlinked slices and option to
              fade labels in/out when the pointer enters/leaves a slice;

 Bar3DGraph - a 3D-looking version of the BarGraph type.

Using SVGGraph
==============
The library consists of several class files which must be present. To use
SVGGraph, include or require the SVGGraph.php class file. The other classes
should be in the same directory as this main file to be loaded automatically.

Embedding SVG in a page
=======================
There are several ways to insert SVG graphics into a page. FireFox, Safari,
Chrome, Opera all support SVG, though Internet Explorer currently requires
the use of a plugin (supplied by Adobe).

For options 1-3, I'll assume you have a PHP script called "graph.php" which
contains the code to generate the SVG.

Option 1: the embed tag
 <embed src="graph.php" type="image/svg+xml" width="600" height="400"
  pluginspage="http://www.adobe.com/svg/viewer/install/" />

This method works in all browsers, though the embed tag is not part of the HTML
standard.

Option 2: the iframe tag
 <iframe src="graph.php" type="image/svg+xml" width="600" height="400"></iframe>

This method also works in all browsers, and the iframe tag is standard.

Option 3: the object tag
 <object data="graph.php" width="600" height="100" type="image/svg+xml" />

The object tag is standard, but this doesn't work in IE.

Option 4: using the svg namespace within an xhtml document

This option is more complicated, as it requires changing the doctype and
content type of the page being served. The SVG is generated as part of the
same page.
 <?php
  header('content-type: application/xhtml+xml; charset=UTF-8');
  // $graph = new SVGGraph(...);
  // $graph setup here!
 ?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN"
  "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">
 <html xmlns="http://www.w3.org/1999/xhtml"
  xmlns:svg="http://www.w3.org/2000/svg"
  xmlns:xlink="http://www.w3.org/1999/xlink" xml:lang="en">
 <head>
  <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
  <title>SVGGraph example</title>
 </head>
 <body>
  <h1>Example of SVG in XHTML</h1>
  <div>
  <?php echo $graph->Fetch('BarGraph', false); ?>
  </div>
 </body>
 </html>

This method allows you more control over how you use the SVG, though again it
doesn't work in IE.

Class Constructor
=================
The SVGGraph class constructor takes three arguments, the width and height 
of the SVG image in pixels and an optional array of settings to be passed to 
the rendering class.
 $graph = new SVGGraph($width, $height, $settings);

For more information on the $settings array, see the section below.

Data Values
===========
For simple graphs you may set the data to use by passing it into the Values
function:
 $graph->Values(1, 2, 3);

For more control over the data, and to assign labels, pass the values in as an
array:
 $data = array('first' => 1, 'second' => 2, 'third' => 3);
 $graph->Values($data);

Hyperlinks
==========
The graph bars and markers may be assigned hyperlinks - each value that requires
a link should have a URL assigned to it using the Links function:
 $graph->Links('/page1.html', NULL, '/page3.html');

The NULL is used here to specify that the second bar will not be linked to
anywhere.

As with the Values function, the list of links may be passed in as an array:
 $links = array('/page1.html', NULL, '/page3.html');

Using an associative array means that NULL values may be skipped.
 $links = array('first' => '/page1.html', 'third' => '/page3.html');
 $graphs->Links($links);

Rendering
=========
To generate and display the graph, call the Render function passing in the
type of graph to be rendered:
 $graph->Render('BarGraph');

This will send the correct content type header to the browser and output the
SVG graph.

The Render function takes two optional parameters in addition to the graph
type:
 $graph->Render($type, $header, $content_type);

Passing in FALSE for $header will prevent output of the XML declaration and
doctype. Passing in FALSE for $content_type will prevent the 'image/svg+xml'
content type being set in the response header.

To generate the graph without outputting it to the browser you may use the
Fetch function instead:
 $output = $graph->Fetch('BarGraph');

This function also takes an optional $header parameter:
 $output = $graph->Fetch($type, $header);

Passing in FALSE as $header will prevent the returned output from containing
the XML declaration and doctype. The Fetch function never outputs the content
type to the response header.

Colours
=======
The colours used may be overridden from the default random set by setting the
graph's "colours" array.
 $graph->colours = array('red', 'green', '#00ffff', 'rgb(100,200,100)',
    array('red','green'));

You may use any of the standard named colours, or hex notation, or RGB notation.
The final entry in the example array is an array of two colours, which specifies
a vertical gradient, the first colour (red) at the top and the second (green) at
the bottom.

For the bar graph, each bar is assigned the next colour in turn from the list
of colours, repeating when it reaches the end. For the line graph, the first
colour in the list is used to draw the line.

Settings
========
Many of the ways that things are displayed may be changed by passing in an array
of settings to the SVGGraph constructor:
 $settings = array('back_colour' => 'white');
 $graph = new Graph($w, $h, $settings);

The list of options and their defaults are shown below. Sizes are always in
pixels. Since this is a standard PHP array, numbers and boolean values should
be represented without quotes, everything else should be quoted with either
single or double quotes.
 title              NULL                Contents of title tag, or NULL for none
 description        NULL                Contents of desc tag, or NULL for none
 stroke_colour      'rgb(0,0,0)'        Colour of graph lines
 back_colour        'rgb(240,240,240)'  Background colour of graph
 back_round         0                   Radius of rounded background edge
 back_stroke_width  1                   Thickness of background edge
 back_stroke_colour 'rgb(0,0,0)'        Colour of background edge
 pad_top            10                  Space at top of graph
 pad_bottom         10                  Space at bottom of graph
 pad_left           10                  Space to left of graph
 pad_right          10                  Space to right of graph
 link_base          ''                  Prepended to all links
 link_target        '_blank'            Link target frame
 namespace          false               Option to use the svg: namespace prefix
 doctype            false               Option to output the DOCTYPE

These options are common to the Bar, Line and Bar3D graph types:
 show_grid          true                Grid on/off option
 show_axes          true                Axes on/off option
 show_divisions     true                Axis division points on/off
 show_label_h       true                Horizontal axis labelling on/off
 show_label_v       true                Vertical axis labelling on/off
 grid_colour        'rgb(220,220,220)'  Colour of grid lines
 axis_colour        'rgb(0,0,0)'        Colour of axis lines
 axis_font          'monospace'         Font for labels
 axis_font_size     10                  Label font size
 axis_font_adjust   0.6                 Approx ratio of font width to height
 axis_overlap       5                   Amount to extend axes past graph area
 label_colour       'rgb(0,0,0)'        Colour of label text
 division_size      3                   Length of division lines
 division_colour    NULL                Colour of division lines, or NULL to use axis colour

BarGraph options:
 bar_space          10                  Space between bars

LineGraph options:
 line_stroke_width  2                   Thickness of graph line
 marker_size        5                   Size of point markers
 marker_type        'circle'            Type of marker to use (circle, square, triangle)
 marker_colour      NULL                Colour of marker (NULL to use same as line)
 fill_under         false               If true, the area under the line is filled with colour #2

PieGraph options:
 aspect_ratio          1.0              Ratio of height/width (or 'auto' to fill area)
 sort                  true             Sorts the pie slices, largest first
 reverse               false            Slices are drawn anti-clockwise instead of clockwise
 show_labels           true             Slice labelling on/off option
 show_label_amount     false            Display slice value on/off option
 show_label_percent    false            Display slice percentage on/off option
 label_colour          'white'          Colour of label text
 label_back_colour     NULL             Label background colour
 label_font            'sans-serif'     Font for labels
 label_font_weight     'bold'           Label font weight
 label_font_size       18               Label font size
 label_fade_in_speed   0                Speed to fade in labels (try 1 - 10, 0 disables)
 label_fade_out_speed  0                Speed to fade out labels, if fading in enabled
 label_position        0.75             Distance of label from centre

Bar3DGraph options:
 bar_space          10                  Space between bars
 project_angle      30                  Angle between bar side edges and horizontal axis

Contact details
===============
For more information about this software please contact the author,
graham(at)goat1000.com or visit the website: http://www.goat1000.com/


