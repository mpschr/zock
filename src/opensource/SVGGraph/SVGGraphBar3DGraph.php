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
 * $Id: SVGGraphBar3DGraph.php 72 2010-04-11 11:54:55Z grahambreach $
 * For more information, please contact <graham@goat1000.com>
 */


require_once('SVGGraph3DGraph.php');

class Bar3DGraph extends ThreeDGraph {

	var $bar_space = 10;

	function Draw()
	{
		// make sure project_angle is in range
		if($this->project_angle < 0)
			$this->project_angle = 0;
		elseif($this->project_angle > 90)
			$this->project_angle = 90;

		$this->CalcAxes();
		$body = $this->Grid();
		$axes = $this->Axes();

		$values = $this->GetValues();

		$block_width = $this->bar_unit_width - $this->bar_space;

		// make the top parallelogram, set it as a symbol for re-use
		list($bx,$by) = $this->Project(0,0,$block_width);
		$top = array(
			'id' => 'bTop',
			'd' => "M0,0 l$block_width,0 l$bx,$by l-$block_width,0 z"
		);
		$this->defs[] = $this->Element('symbol', NULL, NULL, $this->Element('path', $top));
		$top = array('xlink:href' => '#bTop');

		$bnum = 0;
		$ccount = count($this->colours);

		// get the translation for the whole bar
		list($tx, $ty) = $this->Project(0,0,$this->bar_space / 2);
		$group = array('transform' => "translate($tx,$ty)");

		$baseline = $this->height - $this->pad_bottom;
		$b_start = $this->pad_left + ($this->bar_space / 2);
		$bar = array('width' => $block_width);

		$bars = '';
		foreach($values as $key => $value) {
			$bar['x'] = $b_start + ($this->bar_unit_width * $bnum);
			$bar['height'] = $value * $this->bar_unit_height;
			$bar['y'] = $baseline - $bar['height'];

			$top['transform'] = "translate($bar[x],$bar[y])";
			$side_x = $bar['x'] + $block_width;
			$side = array(
				'd' => "M0,0 l$bx,$by l0,$bar[height] l-$bx," . -$by . " z",
				'transform' => "translate($side_x,$bar[y])"
			);
			$group['fill'] = $this->GetColour($bnum % $ccount);
			$top['fill'] = $this->GetColour($bnum % $ccount, TRUE);

			$rect = $this->Element('rect', $bar);
			$bar_top = $this->Element('use', $top);
			$bar_side = $this->Element('path', $side);
			$link = $this->GetLink($key, $rect . $bar_top . $bar_side);

			$bars .= $this->Element('g', $group, NULL, $link);
			++$bnum;
		}

		$bgroup = array(
			'stroke' => $this->stroke_colour,
			'fill' => 'none',
			'stroke-linejoin' => 'round'
		);
		$body .= $this->Element('g', $bgroup, NULL, $bars);
		return $body . $axes;
	}
}

?>
