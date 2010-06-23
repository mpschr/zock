<?php
/**
 * Copyright (C) 2009 Graham Breach
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * For more information, please contact <graham@goat1000.com>
 */

define('SVGGRAPH_VERSION', 'SVGGraph 1.1');
define('SVGGRAPH_SVN', '$Id: SVGGraph.php 62 2009-03-27 10:11:35Z grahambreach $');

class SVGGraph {

	var $width = 100;
	var $height = 100;
	var $settings = array();
	var $values = array();
	var $links = NULL;
	var $colours = NULL;

	function SVGGraph($w, $h, $settings = NULL)
	{
		$this->width = $w;
		$this->height = $h;
		if(is_array($settings))
			$this->settings = $settings;
	}

	function Values()
	{
		$this->values = func_get_args();
	}
	function Links()
	{
		$this->links = func_get_args();
	}
	function Colours()
	{
		$this->colours = func_get_args();
	}


	/**
	 * Instantiate the correct class
	 */
	function Setup($class)
	{
		// load the relevant class file
		require_once('SVGGraph' . $class . '.php');

		$g = new $class($this->width, $this->height, $this->settings);
		$g->Values($this->values);
		$g->Links($this->links);
		if(!is_null($this->colours))
			$g->colours = $this->colours;
		return $g;
	}

	/**
	 * Fetch the content
	 */
	function Fetch($class, $header = TRUE)
	{
		$g = $this->Setup($class);
		return $g->Fetch($header);
	}

	/**
	 * Pass in the type of graph to display
	 */
	function Render($class, $header = TRUE, $content_type = TRUE)
	{
		$g = $this->Setup($class);
		return $g->Render($header, $content_type);
	}
}

class Graph {
	var $precision = 5;
	var $back_colour = 'rgb(240,240,240)';
	var $back_round = 0;
	var $back_stroke_width = 1;
	var $back_stroke_colour = 'rgb(0,0,0)';
	var $stroke_colour = 'rgb(0,0,0)';

	var $pad_top = 10;
	var $pad_bottom = 10;
	var $pad_left = 10;
	var $pad_right = 10;

	var $values = array();
	var $link_base = '';
	var $link_target = '_blank';
	var $links = NULL;

	var $defs = array();
	var $functions = array();
	var $show_version = FALSE;
	var $title;
	var $description;
	var $namespace = FALSE;
	var $doctype = FALSE;

	var $neg_correction = 0;

	function Graph($w, $h, $settings = NULL)
	{
		$this->width = $w;
		$this->height = $h;
		if(is_array($settings))
			$this->Settings($settings);

		// set default colours
		$this->colours = explode(' ', $this->svg_colours);
		shuffle($this->colours);
	}

	/**
	 * Sets the options
	 */
	function Settings(&$settings)
	{
		foreach($settings as $key => $value)
			$this->{$key} = $value;
	}

	/**
	 * Sets the graph values
	 */
	function Values()
	{
		$args = func_get_args();
		if(is_array($args[0]))
			$this->values = $args[0];
		else
			$this->values = $args;
	}

	/**
	 * Returns a row of values
	 */
	function GetValues($row = 0)
	{
		if(is_array($this->values[$row]))
			return $this->values[$row];
		
		return $this->values;
	}

	/**
	 * Returns the key value for an index, if associative
	 */
	function GetKey($index, $row = 0)
	{
		$vals = (is_array($this->values[$row]) ? $this->values[$row] : $this->values);
		$k = array_keys($vals);

		// this works around a strange bug - if you just return the key at $index,
		// for a non-associative array it repeats some!
		if(is_int($k[0]) && $k[0] == 0)
			return $index;
		if(isset($k[$index]))
			return $k[$index];
		return NULL;
	}

	/**
	 * Returns the maximum value
	 */
	function GetMaxValue()
	{
		if(is_array($this->values[0]))
			return max($this->values[0]);
		return max($this->values);
	}

	/**
	 * Sets the links from each item
	 */
	function Links()
	{
		$args = func_get_args();
		if(is_array($args[0]))
			$this->links = $args[0];
		else
			$this->links = $args;
	}

	/**
	 * Draws the selected graph
	 */
	function DrawGraph()
	{
		$canvas = $this->Canvas();
		$body = $this->Element('g', array('clip-path' => "url(#canvas)"), NULL, $this->Draw());
		return $canvas . $body;
	}

	/**
	 * This should be overridden by subclass!
	 */
	function Draw()
	{
		return $this->Element('text', array('stroke' => $this->stroke_colour), NULL,
			'Draw() must be overridden by class');
	}

	/**
	 * Displays the background
	 */
	function Canvas()
	{
		$canvas = array(
			'width' => '100%', 'height' => '100%',
			'fill' => $this->back_colour,
			'stroke-width' => 0
		);
		if($this->back_round)
			$canvas['rx'] = $canvas['ry'] = $this->back_round;
		if($this->back_stroke_width) {
			$canvas['stroke-width'] = $this->back_stroke_width;
			$canvas['stroke'] = $this->back_stroke_colour;
		}
		$c_el = $this->Element('rect', $canvas);
		$this->defs[] = $this->Element('clipPath', array('id' => 'canvas'), NULL, $c_el);
		return $c_el;
	}

	/**
	 * Fits text to a box - text will be bottom-aligned
	 */
	function TextFit($text, $x, $y, $w, $h, $attribs = NULL, $styles = NULL)
	{
		$pos = array('onload' => "textFit(evt,$x,$y,$w,$h)");
		if(is_array($attribs))
			$pos = array_merge($attribs, $pos);
		$txt = $this->Element('text', $pos, $styles, $text);

		/** Uncomment to see the box
		$rect = array('x' => $x, 'y' => $y, 'width' => $w, 'height' => $h, 'fill' => 'none', 'stroke' => 'black');
		$txt .= $this->Element('rect', $rect);
		**/
		$this->AddFunction('textFit');
		return $txt;
	}

	/**
	 * Displays readable (hopefully) error message
	 */
	function ErrorText($error)
	{
		$text = array('x' => $this->pad_left, 'y' => $this->height - 3);
		$style = array(
			'font-family' => 'monospace',
			'font-size' => '12px',
			'font-weight' => 'bold',
		);
		
		$e = $this->ContrastText($text['x'], $text['y'], $error, 'blue',
			'white', $style);
		return $e;
	}

	/**
	 * Displays high-contrast text
	 */
	function ContrastText($x, $y, $text, $fcolour = 'black', $bcolour = 'white',
		$properties = NULL, $styles = NULL)
	{
		$props = array('transform' => 'translate(' . $x . ',' . $y . ')', 'fill' => $fcolour);
		if(is_array($properties))
			$props = array_merge($properties, $props);

		$bg = $this->Element('text', array('stroke-width' => '2px', 'stroke' => $bcolour), NULL, $text);
		$fg = $this->Element('text', NULL, NULL, $text);
		return $this->Element('g', $props, $styles, $bg . $fg);
	}
 
	/**
	 * Formats lines of text
	 */
	function TextLines($text, $x, $line_spacing)
	{
		$start_pos = - (count($text) - 1) / 2 * $line_spacing;
		$dy = $start_pos;

		$string = '';
		foreach($text as $line) {
			$string .= $this->Element('tspan', array('x' => $x, 'dy' => $dy), NULL, $line);
			if($dy == $start_pos)
				$dy = $line_spacing;
		}

		return $string;
	}

	/**
	 * Draws an element
	 */
	function Element($name, $attribs = NULL, $styles = NULL, $content = NULL)
	{
		// these properties require units to work well
		$require_units = array('stroke-width', 'stroke-dashoffset',
			'font-size', 'baseline-shift', 'kerning', 'letter-spacing',
			'word-spacing');

		if($this->namespace && strpos($name, ':') === FALSE)
			$name = 'svg:' . $name;
		$element = '<' . $name;
		if(is_array($attribs))
			foreach($attribs as $attr => $val) {

				// if units required, add px
				if(array_search($attr, $require_units) !== FALSE && preg_match('/^\d+$/', $val))
					$val .= 'px';
				$element .= ' ' . $attr . '="' . htmlspecialchars($val) . '"';
			}

		if(is_array($styles)) {
			$element .= ' style="';
			foreach($styles as $attr => $val) {
				// check units again
				if(array_search($attr, $require_units) !== FALSE && preg_match('/^\d+$/', $val))
					$val .= 'px';
				$element .= $attr . ':' . htmlspecialchars($val) . ';';
			}
			$element .= '"';
		}

		if(is_null($content))
			$element .= "/>\n";
		else
			$element .= ">" . $content . "</" . $name . ">\n";

		return $element;
	}

	/**
	 * Retrieves a link
	 */
	function GetLink($key, $content)
	{
		if(!is_array($this->links))
			return $content;

		if(is_array($this->links[0]))
			$links =& $this->links[0];
		else
			$links =& $this->links;

		if(!isset($links[$key]))
			return $content;

		// check for absolute links
		if(strpos($links[$key],'//') !== FALSE)
			$link = $links[$key];
		else
			$link = $this->link_base . $links[$key];

		$link_attr = array('xlink:href' => $link, 'target' => $this->link_target);
		return $this->Element('a', $link_attr, NULL, $content);
	}

	/**
	 * Returns a colour reference
	 */
	function GetColour($key, $no_gradient = FALSE)
	{
		if(!isset($this->colours[$key]))
			return 'none';
		if(is_array($this->colours[$key]))
			if($no_gradient) // sometimes gradients look awful
				return $this->colours[$key][0];
			else
				return 'url(#gradient' . $key . ')';
		return $this->colours[$key];
	}

	/**
	 * Adds one of more javascript functions
	 */
	function AddFunction($name)
	{
		$fns = func_get_args();
		if(count($fns) > 1) {
			foreach($fns as $fn)
				$this->AddFunction($fn);
			return;
		}
		if(isset($this->functions[$name]))
			return true;

		switch($name)
		{
		case 'setAttr' :
			$fn = 'function setAttr(i,a,v) { i.setAttribute(a,v); }';
			break;

		case 'textFit' :
			$fn = <<<JAVASCRIPT
function textFit(evt,x,y,w,h) {
	var t = evt.target;
	var aw = t.getBBox().width;
	var ah = t.getBBox().height;
	var trans = '';
	var s = 1.0;
	if(aw > w)
		s = w / aw;
	if(s * ah > h)
		s = h / ah;
	if(s != 1.0)
		trans = 'scale(' + s + ') ';
	trans += 'translate(' + (x / s) + ',' + ((y + h) / s) +  ')';
	t.setAttribute('transform', trans);
}
JAVASCRIPT;
			break;

		// fadeIn, fadeOut are shortcuts to fader function
		case 'fadeIn' : $name = 'fader';
		case 'fadeOut' : $name = 'fader';
		case 'fader' :
			$fn = <<<JAVASCRIPT
var faders_ = { };
var fader_itimer;
function fadeIn(evt,i,s) { fader(evt,i,0.0,1.0,s); }
function fadeOut(evt,i,s) { fader(evt,i,1.0,0.0,s); }
function fader(evt,i,o1,o2,s) {
	var f = { id: i, o_start: o1, o_end: o2, step: (o1 < o2 ? s : -s) };
	faders_[i] = f;
	if(!fader_itimer)
		fader_itimer = setInterval(fade,50);
}
function fade()
{
	for(f in faders_) {
		var ff = faders_[f];
		var t = document.getElementById(ff.id);
		var o = (t.style.opacity == '' ? ff.o_start : t.style.opacity * 1.0);
		var r1 = (o <= ff.o_end);
		o += ff.step;
		t.style.opacity = Math.min(Math.max(o, 0.0), 1.0);
		var r2 = (o <= ff.o_end);
		if(r2 != r1)
			delete faders_[f];
	}
}
JAVASCRIPT;
			break;

		default :
			return false;
		}

		$this->functions[$name] = $fn;
		return true;
	}

	/**
	 * Creates a linear gradient element
	 */
	function MakeLinearGradient($id, $colours)
	{
		$stops = '';
		$gradient = array('id' => $id, 'x1' => 0, 'x2' => 0, 'y1' => 0, 'y2' => '100%');

		$stop = array('offset' => '0%', 'stop-color' => $colours[0]);
		$stops .= $this->Element('stop', $stop);
		$stop = array('offset' => '100%', 'stop-color' => $colours[1]);
		$stops .= $this->Element('stop', $stop);

		return $this->Element('linearGradient', $gradient, NULL, $stops);
	}

	/**
	 * Returns TRUE if the code contains the specified gradient
	 */
	function ContainsGradient(&$code, $gradient)
	{
		return strpos($code, 'gradient' . $gradient) !== FALSE;
	}

	/**
	 * Returns the SVG document
	 */
	function Fetch($header = TRUE)
	{
		$content = '';
		if($header) {
			// '>' is with \n so as not to confuse syntax highlighting
			$content .= '<?xml version="1.0" standalone="no"?' . ">\n";
			if($this->doctype)
				$content .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" ' .
				'"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . "\n";
		}

		// set the precision - PHP default is 14 digits!
		$old_precision = ini_set('precision', $this->precision);

		// display title and description if available
		$heading = '';
		if($this->title)
			$heading .= $this->Element('title', NULL, NULL, $this->title);
		if($this->description)
			$heading .= $this->Element('desc', NULL, NULL, $this->description);

		if(!count($this->values)) {
			$body = $this->ErrorText('No values to draw!');
		} else {
			// get the body content from the subclass
			$body = $this->DrawGraph();
		}

		// insert selected Javascript functions
		if(count($this->functions)) {
			$script = array('type' => 'text/javascript');
			$heading .= $this->Element('script', $script, NULL, "<![CDATA[\n" .
				implode('', $this->functions) . "\n// ]]>");
		}

		// insert any gradients that are used
		foreach($this->colours as $key => $c)
			if(is_array($c) && $this->ContainsGradient($body, $key))
				$this->defs[] = $this->MakeLinearGradient('gradient' . $key, $c);

		// show defs and body content
		$heading .= $this->Element('defs', NULL, NULL, implode('', $this->defs));
		$svg = array(
			'width' => $this->width, 'height' => $this->height, 
			'version' => '1.1', 
			'xmlns:xlink' => 'http://www.w3.org/1999/xlink'
		);
		if($this->namespace)
			$svg['xmlns:svg'] = "http://www.w3.org/2000/svg";
		else
			$svg['xmlns'] = "http://www.w3.org/2000/svg";
		$content .= $this->Element('svg', $svg, NULL, $heading . $body);

		// display version string
		if($this->show_version) {
			$text = array('x' => $this->pad_left, 'y' => $this->height - 3);
			$style = array(
				'font-family' => 'monospace', 'font-size' => '12px',
				'font-weight' => 'bold',
			);
			$content .= $this->ContrastText($text['x'], $text['y'], SVGGRAPH_VERSION,
				'blue', 'white', $style);
		}

		// replace PHP's precision
		ini_set('precision', $old_precision);

		return $content;
	}

	/**
	 * Renders the SVG document
	 */
	function Render($header = TRUE, $content_type = TRUE)
	{
		if($content_type)
			header('Content-type: image/svg+xml');
		echo $this->Fetch($header);
	}

	var $svg_colours = "aliceblue antiquewhite aqua aquamarine azure beige bisque black blanchedalmond blue blueviolet brown burlywood cadetblue chartreuse chocolate coral cornflowerblue cornsilk crimson cyan darkblue darkcyan darkgoldenrod darkgray darkgreen darkgrey darkkhaki darkmagenta darkolivegreen darkorange darkorchid darkred darksalmon darkseagreen darkslateblue darkslategray darkslategrey darkturquoise darkviolet deeppink deepskyblue dimgray dimgrey dodgerblue firebrick floralwhite forestgreen fuchsia gainsboro ghostwhite gold goldenrod gray grey green greenyellow honeydew hotpink indianred indigo ivory khaki lavender lavenderblush lawngreen lemonchiffon lightblue lightcoral lightcyan lightgoldenrodyellow lightgray lightgreen lightgrey lightpink lightsalmon lightseagreen lightskyblue lightslategray lightslategrey lightsteelblue lightyellow lime limegreen linen magenta maroon mediumaquamarine mediumblue mediumorchid mediumpurple mediumseagreen mediumslateblue mediumspringgreen mediumturquoise mediumvioletred midnightblue mintcream mistyrose moccasin navajowhite navy oldlace olive olivedrab orange orangered orchid palegoldenrod palegreen paleturquoise palevioletred papayawhip peachpuff peru pink plum powderblue purple red rosybrown royalblue saddlebrown salmon sandybrown seagreen seashell sienna silver skyblue slateblue slategray slategrey snow springgreen steelblue tan teal thistle tomato turquoise violet wheat white whitesmoke yellow yellowgreen";

}


/**
 * Class for calculating axis measurements
 */
class Axis {

	function Axis($width, $max_val)
	{
		$this->full_width = $width;
		$this->max_value = $max_val;
	}

	/**
	 * Returns the grid spacing
	 */
	function Grid($min)
	{
		$g = $this->full_width / $this->max_value;
		$this->unit_size =  $g;
		while($g < $min)
			$g *= 5;

		return $g;
	}

	/**
	 * Returns the size of a unit in grid space
	 */
	function Unit()
	{
		if(!isset($this->unit_size))
			$this->Grid(1);

		return $this->unit_size;
	}

}

?>
