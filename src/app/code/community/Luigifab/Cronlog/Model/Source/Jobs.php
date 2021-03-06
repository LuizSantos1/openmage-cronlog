<?php
/**
 * Created D/10/02/2013
 * Updated V/19/06/2020
 *
 * Copyright 2012-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/openmage/cronlog
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

class Luigifab_Cronlog_Model_Source_Jobs extends Varien_Data_Collection {

	public function getCollection($type = null) {

		// getName() = le nom du tag xml
		// => /config/crontab/jobs/cronlog_send_report
		// <crontab>
		//  <jobs>
		//   <cronlog_send_report>                       <= $config
		//    <run>
		//     <model>cronlog/observer::sendMail</model>
		//    <schedule>
		//     <disabled>1</disabled>
		$config = Mage::getModel('core/config')->loadBase()->loadModules()->loadDb();
		$nodes  = $config->getXpath('/config/crontab/jobs/*');

		foreach ($nodes as $config) {

			$jobcode = $config->getName();
			$configurable = Mage::getConfig()->getNode('default/crontab/jobs/'.$jobcode);

			$expr = empty($config->schedule->config_path) ? null  : Mage::getStoreConfig((string) $config->schedule->config_path);
			$expr = empty($config->schedule->cron_expr)   ? $expr : $config->schedule->cron_expr;
			$expr = empty($configurable->schedule->config_path) ? $expr : Mage::getStoreConfig((string) $configurable->schedule->config_path);
			$expr = empty($configurable->schedule->cron_expr)   ? $expr : $configurable->schedule->cron_expr;
			$expr = empty(trim($expr)) ? null : trim($expr);

			$model = empty($config->run->model) ? null : $config->run->model;
			$model = empty($configurable->run->model) ? $model : $configurable->run->model;

			$moduleName = Mage::getConfig()->getModelClassName($model);
			$moduleName = mb_substr($moduleName, 0, mb_stripos($moduleName, '_', mb_stripos($moduleName, '_') + 1));
			$moduleName = str_replace('_', '/', $moduleName);

			// tâche désactivée si
			// - balise disabled
			// - ou configuration disabled
			// - ou pas de programmation (= ni balise config_path/cron_expr, ni configuration config_path/cron_expr)
			$isDisabled = (!empty($config->schedule->disabled) || !empty($configurable->schedule->disabled) || empty($expr)) ?
				'disabled' : 'enabled';

			// tâche en lecture seule si
			// - balise disabled
			// - ou pas de balise de programmation (= pas de balise config_path/cron_expr)
			// - ou pas de programmation (= ni balise config_path/cron_expr, ni configuration config_path/cron_expr)
			$isReadOnly = (!empty($config->schedule->disabled) ||
			               (empty($config->schedule->config_path) && empty($config->schedule->cron_expr)) ||
			                empty($expr));

			$item = new Varien_Object();
			$item->setData('module', $moduleName);
			$item->setData('job_code', $jobcode);
			$item->setData('cron_expr', $expr);
			$item->setData('model', $model);
			$item->setData('status', $isDisabled);
			$item->setData('is_read_only', $isReadOnly);

			if ((($type == 'ro') && $isReadOnly) || (($type == 'rw') && !$isReadOnly) || empty($type))
				$this->addItem($item);
		}

		usort($this->_items, static function ($a, $b) {
			return strnatcasecmp($a->getData('job_code'), $b->getData('job_code'));
		});

		return $this;
	}

	public function toOptionArray() {
		return $this->getCollection()->_toOptionArray('job_code', 'job_code');
	}
}