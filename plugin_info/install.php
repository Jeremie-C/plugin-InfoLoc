<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function infoloc_install() {
    $cron = cron::byClassAndFunction('infoloc', 'pull');
	if ( ! is_object($cron)) {
        $cron = new cron();
        $cron->setClass('infoloc');
        $cron->setFunction('pull');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('* * * * *');
        $cron->save();
	}
}

function infoloc_update() {
    $cron = cron::byClassAndFunction('infoloc', 'pull');
	if ( ! is_object($cron)) {
        $cron = new cron();
        $cron->setClass('infoloc');
        $cron->setFunction('pull');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('* * * * *');
        $cron->save();
	} else {
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('* * * * *');
        $cron->save();
	}
	log::add('infoloc','error',__('Les commandes doivent être reconfigurées dans la gestion du plugin.',__FILE__));
	foreach (eqLogic::byType('infoloc') as $eqLogic) {
		$eqLogic->save();
	}
}

function infoloc_remove() {
    $cron = cron::byClassAndFunction('infoloc', 'pull');
	if (is_object($cron)) {
		$cron->stop();
		$cron->remove();
	}
}
?>
