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
 * $Id: SVGGraphPieGraph.php 72 2010-04-11 11:54:55Z grahambreach $
 * For more information, please contact <graham@goat1000.com>
 */

class PieGraph extends Graph {

	var $aspect_ratio = 1.0;
	var $sort = true;
	var $reverse = false;
	var $show_labels = true;
	var $show_label_amount = false;
	var $show_label_percent = false;
	var $label_colour = 'white';
	var $label_back_colour = NULL;
	var $label_font = 'sans-serif';
	var $label_font_weight = 'bold';
	var $label_font_size = '18'; // pixels
	var $label_fade_in_speed = 0;
	var $label_fade_out_speed = 0;
	var $label_position = 0.75;

	function Draw()
	{
		if($this->show_labels && $this->label_fade_in_speed)
			$this->AddFunction('fadeIn','fadeOut');

		$bound_x1 = $this->pad_left;
		$bound_y1 = $this->pad_top;
		$bound_x2 = $this->width - $this->pad_right;
		$bound_y2 = $this->height - $this->pad_bottom;

		$w = $bound_x2 - $bound_x1;
		$h = $bound_y2 - $bound_y1;

		if($this->aspect_ratio == 'auto')
			$this->aspect_ratio = $h/$w;
		elseif($this->aspect_ratio <= 0)
			$this->aspect_ratio = 1.0;

		$x_centre = (($bound_x2 - $bound_x1) / 2) + $bound_x1;
		$y_centre = (($bound_y2 - $bound_y1) / 2) + $bound_y1;

		if($h/$w > $this->aspect_ratio) {
			$radius_x = $w / 2.0;
			$radius_y = $radius_x * $this->aspect_ratio;
		} else {
			$radius_y = $h / 2.0;
			$radius_x = $radius_y / $this->aspect_ratio;
		}

		$speed_in = $this->show_labels && $this->label_fade_in_speed ? $this->label_fade_in_speed / 100.0 : 0;
		$speed_out = $this->show_labels && $this->label_fade_out_speed ? $this->label_fade_out_speed / 100.0 : 0;

		// take a copy for sorting
		$values = $this->GetValues();
		$total = array_sum($values);

		$unit_slice = 2.0 * pi() / $total;
		$ccount = count($this->colours);
		$vcount = count($values);
		$sub_total = 0.0;

		if($this->sort)
			arsort($values);
		$body = '';
		$labels = '';

		$slice = 0;
		foreach($values as $key => $value) {
			++$slice;

			$angle1 = $sub_total * $unit_slice;
			$angle2 = ($sub_total + $value) * $unit_slice;
			$degrees = ($angle2 - $angle1) * 180.0 / pi();

			$r1 = $radius_x;
			$r2 = $radius_y;
			$x1 = ($r1 * cos($angle1));
			$y1 = ($this->reverse ? -1 : 1) * ($r2 * sin($angle1));
			$x2 = ($r1 * cos($angle2));
			$y2 = ($this->reverse ? -1 : 1) * ($r2 * sin($angle2));

			$x1 += $x_centre;
			$y1 += $y_centre;
			$x2 += $x_centre;
			$y2 += $y_centre;

			$outer = ($degrees > 180 ? 1 : 0);
			$sweep = ($this->reverse ? 0 : 1);
			$path = "M$x_centre,$y_centre L$x1,$y1 A$r1 $r2 0 $outer,$sweep $x2,$y2 z";

			$attr = array('d' => $path, 'id' => 'pieSlice' . $slice, 'fill' => $this->GetColour(($slice-1) % $ccount, true));
			if($speed_in) {
				$attr['onmouseover'] = 'fadeIn(evt,"pieLabel' . $slice . '", ' . $speed_in . ')';
				if($speed_out)
					$attr['onmouseout'] = 'fadeOut(evt,"pieLabel' . $slice . '", ' . $speed_out . ')';
			}
			$path = $this->Element('path', $attr);
	
			$t_style = NULL;
			if($this->show_labels) {
				$ac = ($sub_total + ($value * 0.5)) * $unit_slice;
				$xc = $this->label_position * $r1 * cos($ac);
				$yc = ($this->reverse ? -1 : 1) * $this->label_position * $r2 * sin($ac);

				$text['id'] = 'pieLabel' . $slice;
				if($this->label_fade_in_speed)
					$text['opacity'] = '0.0';
				$tx = $x_centre + $xc;
				$ty = $y_centre + $yc + ($this->label_font_size * 0.3);

				// display however many lines of label
				$parts = array($key);
				if($this->show_label_amount)
					$parts[] = $value;
				if($this->show_label_percent)
					$parts[] = ($value / $total) * 100.0 . '%';

				$x_offset = is_null($this->label_back_colour) ? $tx : 0;
				$string = $this->TextLines($parts, $x_offset, $this->label_font_size);

				// make sure hovering over the text doesn't make it fade out
				if($speed_in && $speed_out)
					$text['onmouseover'] = 'fadeIn(evt,"pieLabel' . $slice . '", ' . $speed_in . ')';

				if(!is_null($this->label_back_colour)) {
					$labels .= $this->ContrastText($tx, $ty, $string, 
						$this->label_colour, $this->label_back_colour, $text);
				} else {
					$text['x'] = $tx;
					$text['y'] = $ty;
					$text['fill'] = $this->label_colour;
					$labels .= $this->Element('text', $text, NULL, $string);
				}
			}

			$body .= $this->GetLink($key, $path);

			$sub_total += $value;
		}

		// group the slices
		$attr = array('stroke' => $this->stroke_colour);
		$body = $this->Element('g', $attr, NULL, $body);

		if($this->show_labels) {
			$label_group = array(
				'text-anchor' => 'middle',
				'font-size' => $this->label_font_size,
				'font-family' => $this->label_font,
				'font-weight' => $this->label_font_weight,
			);
			$labels = $this->Element('g', $label_group, NULL, $labels);
		}
		return $body . $labels;
	}
}

?>
