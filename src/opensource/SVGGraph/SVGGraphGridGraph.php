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
 * $Id: SVGGraphGridGraph.php 72 2010-04-11 11:54:55Z grahambreach $
 * For more information, please contact <graham@goat1000.com>
 */

class GridGraph extends Graph {
	var $grid_colour = 'rgb(220,220,220)';
	var $axis_colour = 'rgb(0,0,0)';
	var $label_colour = 'rgb(0,0,0)';
	var $show_grid = true;
	var $show_axes = true;
	var $show_label_h = true;
	var $show_label_v = true;
	var $axis_font = 'monospace';
	var $axis_font_size = '10'; // pixels
	var $axis_font_adjust = 0.6; // approx ratio of width to height
	var $axis_overlap = 5;
	var $show_divisions = true;
	var $division_size = 3;
	var $division_colour = NULL; // default to use axis colour

	// set to true for block-based labelling
	var $label_centre = false;

	/**
	 * Calculates the effect of axes, applying to padding
	 * $point should be true when there will be a data point at each end
	 * of the axis, and not bars between the points
	 */
	function CalcAxes($point = false)
	{
		if(isset($this->axes_calc_done))
			return;

		// make space for labels
		$v_max = $this->GetMaxValue();
		if($this->show_label_v)
			$this->pad_left += $this->axis_font_size * strlen($v_max) * $this->axis_font_adjust;
		if($this->show_label_h)
			$this->pad_bottom += $this->axis_font_size;

		$this->g_height = $this->height - $this->pad_top - $this->pad_bottom;
		$this->g_width = $this->width - $this->pad_left - $this->pad_right;

		$values = $this->GetValues();
		$v_count = count($values);
		$divisions = $point ? $v_count - 1 : $v_count;

		$x_axis = new Axis($this->g_width, $divisions);
		$y_axis = new Axis($this->g_height, $v_max);
		$this->h_grid = $x_axis->Grid(10);
		$this->v_grid = $y_axis->Grid(10);

		$this->bar_unit_width = $x_axis->Unit();
		$this->bar_unit_height = $y_axis->Unit();

		$this->axis_width = $v_count * $this->h_grid;
		$this->axis_height = floor($this->g_height / $this->v_grid) * $this->v_grid;

		$this->axes_calc_done = true;
	}

	/**
	 * Draws bar or line graph axes
	 */
	function Axes()
	{
		if(!$this->show_axes)
			return '';

		$points = array();
		$points['x1'] = $this->pad_left - $this->axis_overlap;
		$points['x2'] = $this->width - $this->pad_right + $this->axis_overlap;
		$points['y1'] = $points['y2'] = $this->height - $this->pad_bottom;
		$x_axis = $this->Element('line', $points);

		$points['x1'] = $points['x2'] = $this->pad_left;
		$points['y1'] = $this->pad_top - $this->axis_overlap;
		$points['y2'] = $this->height - $this->pad_bottom + $this->axis_overlap;
		$y_axis = $this->Element('line', $points);

		$line = array('stroke-width' => 2, 'stroke' => $this->axis_colour);
		$axis_group = $this->Element('g', $line, NULL, $x_axis . $y_axis);

		$label_group = '';
		$divisions = '';
		if($this->show_label_v || $this->show_label_h || $this->show_divisions) {
			$text = array('x' => $this->pad_left - $this->axis_overlap);
	
			$v_group = '';
			if($this->show_label_v || $this->show_divisions) {
				$labels = '';
				$text_centre = $this->axis_font_size * 0.3;
				$c = $y = 0;
				$d_path = '';

				while($y <= $this->axis_height) {
					$text['y'] = $this->height - $this->pad_bottom - $y + $text_centre;
					$labels .= $this->Element('text', $text, NULL, ($y / $this->bar_unit_height) - $this->neg_correction);
					#$labels .= $this->Element('text', $text, NULL, $y / $this->bar_unit_height);
					$d_path .= 'M' . $this->pad_left . ' ' . ($this->height - $this->pad_bottom - $y) .
						'l-' . $this->division_size . ' 0';

					++$c;
					$y = $c * $this->v_grid;
				}
				$v_group = $this->Element('g', array('text-anchor' => 'end'), NULL, $labels);
			}

			$h_group = '';
			if($this->show_label_h || $this->show_divisions) {
				$labels = '';
				$text['y'] = $this->height - $this->pad_bottom + $this->axis_font_size;
				$w = $this->width - $this->pad_left - $this->pad_right;
				$loffset = ($this->label_centre ? $this->bar_unit_width * 0.5 : 0.0);
				$c = $x = 0;
				$d_path .= 'M' . $this->pad_left . ' ' . ($this->height - $this->pad_bottom) .
					'l0 ' . $this->division_size;
	
				while($x < $this->axis_width) {
					$text['x'] = $this->pad_left + $x + $loffset;
					$key = $this->GetKey($x / $this->bar_unit_width);
					$labels .= $this->Element('text', $text, NULL, $key);
					++$c;
					$x = $c * $this->h_grid;

					$d_path .= 'M' . ($this->pad_left + $x) . ' ' . ($this->height - $this->pad_bottom) .
						'l0 ' . $this->division_size;
				}
				$h_group = $this->Element('g', array('text-anchor' => 'middle'), NULL, $labels);
			}

			$font = array(
				'font-size' => $this->axis_font_size,
				'font-family' => $this->axis_font,
				'fill' => $this->label_colour,
			);
			if($this->show_label_h || $this->show_label_v) {
				$label_group = $this->Element('g', $font, NULL, 
					($this->show_label_h ? $h_group : '') .
					($this->show_label_v ? $v_group : ''));
			}

			if($this->show_divisions) {
				$colour = is_null($this->division_colour) ? $this->axis_colour : $this->division_colour;
				$div = array('d' => $d_path, 'stroke-width' => 1, 'stroke' => $colour);
				$divisions = $this->Element('path', $div);
			}
		}
		return $divisions . $axis_group . $label_group;
	}

	/**
	 * Draws the grid behind the bar / line graph
	 */
	function Grid()
	{
		$this->CalcAxes();
		if(!$this->show_grid)
			return '';

		$x1 = $this->pad_left;
		$x2 = $this->width - $this->pad_right;
		$y1 = $this->height - $this->pad_bottom;
		$y2 = $this->pad_top;

		$path = '';
		$c = 0;
		$x = $x1;
		while($x <= $x2) {
			$path .= 'M' . $x . ' ' . $y1 . 'L' . $x . ' ' . $y2;
			++$c;
			$x = $x1 + ($c * $this->h_grid);
		}

		$c = 0;
		$y = $y1;
		while($y >= $y2) {
			$path .= 'M' . $x1 . ' ' . $y . 'L' . $x2 . ' ' . $y;
			++$c;
			$y = $y1 - ($c * $this->v_grid);
		}

		$opts = array('d' => $path, 'stroke' => $this->grid_colour);
		return $this->Element('path', $opts);
	}

}

?>
