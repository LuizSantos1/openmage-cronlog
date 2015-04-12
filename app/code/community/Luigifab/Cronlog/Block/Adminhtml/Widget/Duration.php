<?php
/**
 * Created S/26/04/2014
 * Updated W/01/04/2015
 * Version 5
 *
 * Copyright 2012-2015 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://redmine.luigifab.info/projects/magento/wiki/cronlog (shared with maillog and versioning)
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 2 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

class Luigifab_Cronlog_Block_Adminhtml_Widget_Duration extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

	public function render(Varien_Object $row) {

		if (!in_array($row->getData('executed_at'), array('', '0000-00-00 00:00:00', null)) &&
		    !in_array($row->getData('finished_at'), array('', '0000-00-00 00:00:00', null))) {

			$data = strtotime($row->getData('finished_at')) - strtotime($row->getData('executed_at'));
			$minutes = intval($data / 60);
			$seconds = intval($data % 60);

			if ($data > 599)
				$data = '<strong>'.(($seconds > 9) ? $minutes.':'.$seconds : $minutes.':0'.$seconds).'</strong>';
			else if ($data > 119)
				$data = '<strong>'.(($seconds > 9) ? '0'.$minutes.':'.$seconds : '0'.$minutes.':0'.$seconds).'</strong>';
			else if ($data > 59)
				$data = ($seconds > 9) ? '0'.$minutes.':'.$seconds : '0'.$minutes.':0'.$seconds;
			else if ($data > 0)
				$data = ($seconds > 9) ? '00:'.$data : '00:0'.$data;
			else
				$data = '&lt; 1';

			return $data;
		}
	}
}