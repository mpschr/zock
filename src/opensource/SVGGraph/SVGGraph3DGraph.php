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
 * $Id: SVGGraph3DGraph.php 72 2010-04-11 11:54:55Z grahambreach $
 * For more information, please contact <graham@goat1000.com>
 */

require_once('SVGGraphGridGraph.php');

class ThreeDGraph extends GridGraph {

	var $project_angle = 30;
	var $label_centre = true;

	// Number of data ranges
	var $depth = 1;


	/**
	 * Returns the projection angle in radians
	 */
	function AngleRadians()
	{
		return $this->project_angle * pi() / 180.0;
	}

	/**
	 * Converts x,y,z coordinates into flat x,y
	 */
	function Project($x, $y, $z)
	{
		$a = $this->AngleRadians();
		$x1 = $z * cos($a);
		$y1 = $z * sin($a);
		return array($x + $x1, $y - $y1);
	}


	/**
	 * Calculates the sizes of the 3D axes and grid
	 */
	function CalcAxes($point = false)
	{
		parent::CalcAxes($point);
	
		$v_max = $this->GetMaxValue();

		// calculate bar width
		$values = $this->GetValues();
		$v_count = count($values);
		$divisions = $point ? $v_count - 1 : $v_count;

		$a = $this->AngleRadians();
		$x_axis = new Axis3D($this->g_width, $divisions, $this->depth * cos($a));
		$this->h_grid = $x_axis->Grid(10);
		$this->bar_unit_width = $x_axis->Unit();

		// adjust grid height for depth
		$this->g_height -= $this->depth * $this->bar_unit_width * sin($a);
		$y_axis = new Axis($this->g_height, $v_max);
		$this->v_grid = $y_axis->Grid(10);
		$this->bar_unit_height = $y_axis->Unit();
	
		$this->axis_width = $v_count * $this->h_grid;
		$this->axis_height = floor($this->g_height / $this->v_grid) * $this->v_grid;

		$this->axes_calc_done = true;
	}


	/**
	 * Draws the grid behind the bar / line graph
	 */
	function Grid()
	{
		if(!$this->show_grid)
			return '';

		$values = $this->GetValues();
		$x_w = $this->axis_width;
		$y_h = $this->axis_height;
		$x1 = $this->pad_left;
		$x2 = $x1 + $x_w + $this->axis_overlap;
		$y1 = $this->height - $this->pad_bottom;
		$y2 = $y1 - $y_h - $this->axis_overlap;
		$h = $this->height - $this->pad_bottom - $this->pad_top;
		$w = $this->width - $this->pad_left - $this->pad_right;

		$path = '';

		// move to depth
		$z = $this->depth * $this->h_grid;
		list($xd,$yd) = $this->Project(0, 0, $z);
		$y = $h + $this->pad_top;


		$c = 0;
		$x = $x1;
		while($x < $x2) {
			$path .= 'M' . $x . ' ' . $y1 . 'l' . $xd . ' ' . $yd . 'l0 ' . -$y_h;
			++$c;
			$x = $x1 + ($c * $this->h_grid);
		}
		$path .= 'M' . $x1 . ' ' . $y . 'l' . $x_w . ' 0';

		$c = 0;
		$y = $y1;
		while($y >= $y2) {
			$path .= 'M ' . $x1 . ' ' . $y . 'l' . $xd . ' ' . $yd . 'l' . $x_w . ' 0';
			++$c;
			$y = $y1 - ($c * $this->v_grid);
		}
		$path .= 'M' . $x1 . ' ' . $y1 . 'l0 ' . -$y_h;
 
		$opts = array('d' => $path, 'stroke' => $this->grid_colour, 'fill' => 'none');
		return $this->Element('path', $opts);
	}
}


/**
 * Class for calculating axis measurements
 */
class Axis3D extends Axis {

	/**
	 * width = actual space
	 * max_val = maximum value on axis
	 * extra = space at end in value units
	 */
	function Axis3D($width, $max_val, $extra)
	{
		$this->full_width = $width;
		$this->max_value = $max_val;
		$this->extra = $extra;
	}

	/**
	 * Returns the grid spacing
	 */
	function Grid($min)
	{
		$g = $this->full_width / ($this->max_value + $this->extra);
		$this->unit_size =  $g;
		while($g < $min)
			$g *= 5;

		return $g;
	}

}

?>
