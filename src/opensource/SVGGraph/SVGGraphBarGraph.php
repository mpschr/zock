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
 * $Id: SVGGraphBarGraph.php 72 2010-04-11 11:54:55Z grahambreach $
 * For more information, please contact <graham@goat1000.com>
 */

require_once('SVGGraphGridGraph.php');

class BarGraph extends GridGraph {

	var $bar_space = 10;
	var $label_centre = true;

	function Draw()
	{
		$this->CalcAxes();
		$body = $this->Grid();

		$bar_width = ($this->bar_space >= $this->bar_unit_width ? '1' : 
			$this->bar_unit_width - $this->bar_space);
		$bar_style = array('stroke' => $this->stroke_colour);
		$bar = array('width' => $bar_width);

		$bnum = 0;
		$b_start = $this->pad_left + ($this->bar_space / 2);
		$ccount = count($this->colours);
		$values = $this->GetValues();
		foreach($values as $key => $value) {
			$bar['x'] = $b_start + ($this->bar_unit_width * $bnum);
			$bar['height'] = $value * $this->bar_unit_height;
			$bar['y'] = $this->pad_top + $this->g_height - $bar['height'];

			$bar_style['fill'] = $this->GetColour($bnum % $ccount);

			$rect = $this->Element('rect', $bar, $bar_style);
			$body .= $this->GetLink($key, $rect);
			++$bnum;
		}

		$body .= $this->Axes();
		return $body;
	}

}
?>
