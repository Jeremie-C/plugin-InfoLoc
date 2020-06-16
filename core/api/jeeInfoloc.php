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
header('Content-type: application/json');
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

if (!jeedom::apiAccess(init('apikey'), 'infoloc')) {
 echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (Infos & Localisation)', __FILE__);
 die();
}

$content = file_get_contents('php://input');
$json = json_decode($content, true);
log::add('infoloc', 'debug', $content);

$cmd = infolocCmd::byId(init('id'));
if (!is_object($cmd)) {
    throw new Exception(__('Commande ID inconnue : ', __FILE__) . init('id'));
}
if ($cmd->getEqLogic()->getEqType_name() != 'infoloc') {
    throw new Exception(__('Commande inconnue de Infos & Localisation : ', __FILE__) . init('id'));
}

$value = init('value');
if (strpos($value, 'https://') !== false || strpos($value, 'http://') !== false) {
    $url = parse_url($value);
    parse_str($url['query'], $output);
    if (isset($output['q'])) {
        $value = $output['q'];
    }
    if (isset($output['ll'])) {
        $value = $output['ll'];
    }
}

switch( $cmd->getConfiguration('mode') ) {
    case 'string':
    case 'numeric':
    case 'binary':
        $cmd->event($value);
        break;
    case 'battery':
        $cmd->event($value);
        $cmd->getEqLogic()->batteryStatus($value);
        break;
    case 'gpspos':
        $cmd->event($value);
        break;
    default:
        throw new Exception(__('Commande Infos & Localisation non autorisée : ', __FILE__) . init('id'));
        break;
}

return true;
?>
