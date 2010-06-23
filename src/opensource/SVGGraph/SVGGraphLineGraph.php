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
 * $Id: SVGGraphLineGraph.php 72 2010-04-11 11:54:55Z grahambreach $
 * For more information, please contact <graham@goat1000.com>
 */

require_once('SVGGraphGridGraph.php');

/**
 * LineGraph - joined line, with axes and grid
 */
class LineGraph extends GridGraph {

	var $line_stroke_width = 2;
	var $marker_size = 5;
	var $marker_type = 'circle';
	var $marker_colour = NULL;
	var $fill_under = false;

	// private
	var $markers = array();

	function Draw()
	{
		$this->CalcAxes(true);
		$body = $this->Grid();

		$attr = array('stroke' => $this->stroke_colour,
			'stroke-width' => $this->line_stroke_width, 'fill' => 'none');

		$bnum = 0;
		$cmd = 'M';
		$ccount = count($this->colours);
		$path = '';
		$markers = '';
		if($this->fill_under) {
			$path = 'M' . $this->pad_left . ' ' . ($this->pad_top + $this->g_height);
			$cmd = 'L';
			$attr['fill'] = $this->GetColour(0);
		}

		$values = $this->GetValues();
		foreach($values as $key => $value) {
			$x = $this->pad_left + ($this->bar_unit_width * $bnum);
			$y = $this->pad_top + $this->g_height - ($value * $this->bar_unit_height);

			//$link = $this->GetLink($key);
			$path .= "$cmd$x $y ";
			++$bnum;

			// no need to repeat same L command
			$cmd = $cmd == 'M' ? 'L' : '';
			$this->AddMarker($x,$y,$key);
		}

		if($this->fill_under)
			$path .= $cmd . $x . ' ' . ($this->pad_top + $this->g_height) . 'z';

		$attr['d'] = $path;
		$body .= $this->Element('path', $attr);

		$body .= $this->Axes();
		$body .= $this->CrossHairs();
		$body .= $this->DrawMarkers();
		return $body;
	}

	/**
	 * Changes to crosshair cursor by overlaying a transparent rectangle
	 */
	function CrossHairs()
	{
		$rect = array(
			'width' => $this->width, 'height' => $this->height,
			'opacity' => 0.0, 'cursor' => 'crosshair',
		);
		return $this->Element('rect', $rect);
	}


	/**
	 * Adds a marker to the list
	 */
	function AddMarker($x, $y, $key)
	{
		$this->markers[] = array('x' => $x, 'y' => $y, 'key' => $key);
	}

	/**
	 * Draws (linked) markers on the graph
	 */
	function DrawMarkers()
	{
		if($this->marker_size == 0 || count($this->markers) == 0)
			return '';

		$marker = array('id' => 'lMrk');
		if(!is_null($this->marker_colour))
		$marker['fill'] = !is_null($this->marker_colour) ? $this->marker_colour : $this->GetColour(0, true);

		switch($this->marker_type) {
		case 'triangle' :
			$type = 'path';
			$a = $this->marker_size;
			$o = $a * tan(pi() / 6);
			$h = $a / cos(pi() / 6);
			$marker['d'] = "M$a,$o L0,-$h L-$a,$o z";
			break;
		case 'square' :
			$type = 'rect';
			$marker['x'] = $marker['y'] = -$this->marker_size;
			$marker['width'] = $marker['height'] = $this->marker_size * 2;
			break;
		case 'circle' :
		default :
			$type = 'circle';
			$marker['r'] = $this->marker_size;
		}

		// add marker symbol to defs area
		$this->defs[] = $this->Element('symbol', NULL, NULL, $this->Element($type, $marker, NULL));

		$markers = '';
		foreach($this->markers as $m) {
			$key = $m['key'];
			unset($m['key']);
			$m['xlink:href'] = '#lMrk';
			$markers .= $this->GetLink($key, $this->Element('use', $m));
		}
		return $markers;
	}
}

?>
