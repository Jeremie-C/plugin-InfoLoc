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

try {
    require_once __DIR__ . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');
    if (!isConnect('admin')) {
        throw new \Exception(__('401 - Accès non autorisé', __FILE__));
    }
    // Initialise la gestion des requêtes Ajax
    ajax::init();
    // Ajouter addAdresse
    if (init('action') == 'addAdresse') {
        infoloc::add_Adresse();
        ajax::success();
    }
    // Chercher Point de départ
    if (init('action') == 'getCmdFrom') {
        $return = array();
        $eqLogic = eqLogic::byId( init('eqlogic') );
        if( !is_object($eqLogic) ) {
            throw new \Exception(__('Equipement inconnu : ', __FILE__) . init('eqlogic'));
        }
        if( $eqLogic->getEqType_name() != 'infoloc' ) {
            throw new \Exception(__('Equipement non Infos & Localisation : ', __FILE__) . init('eqlogic'));
        }
        foreach( $eqLogic->getCmd() as $cmd ) {
            if( $cmd->getConfiguration('mode') == 'gpspos' ) {
                $valueCmd = array(
                    'id' => $cmd->getId(),
                    'point_name' => $cmd->getHumanName()
                );
                $return[] = $valueCmd;
            }
        }
        ajax::success($return);
    }
    // Chercher Point de destination
    if (init('action') == 'getCmdDest') {
        $return = array();
        foreach( eqLogic::byType('infoloc') as $eqLogic ) {
            if( $eqLogic->getIsEnable() && $eqLogic->getConfiguration('type') == 'adresse' ) {
                $cmd = $eqLogic->getCmd(null, 'fixelatlon');
                if( is_object($cmd) ) {
                    //if( $cmd->execCmd() != '' ) {
                        $valueCmd = array(
                            'id' => $cmd->getId(),
                            'dest_name' => $eqLogic->getHumanName()
                        );
                        $return[] = $valueCmd;
                    //}
                }
            }
        }
        ajax::success($return);
    }
    // Methode de scan réseau
    if (init('action') == 'FindAppBin') {
		$PingCmd = infoloc::GetPingCmd();
		if ( $PingCmd === false ) {
			$message = __('La commande ping est introuvable, relancer les dépendances.',__FILE__);
			config::save('cmd_ping', '', 'infoloc');
		} else {
			$message = __('La commande ping est',__FILE__).' '.$PingCmd;
			config::save('cmd_ping', $PingCmd, 'infoloc');
		}
        $message .= '<br />';
		$ArpingCmd = infoloc::GetArpingCmd();
		if ( $ArpingCmd === false ) {
			$message .= __('La commande arping est introuvable, relancer les dépendances.',__FILE__);
			config::save('cmd_arping', '', 'infoloc');
		} else {
			$message .= __('La commande arping est',__FILE__).' '.$ArpingCmd;
			config::save('cmd_arping', $ArpingCmd, 'infoloc');
		}
        $message .= '<br />';
        $ArpscanCmd = infoloc::GetArpscanCmd();
		if ( $ArpscanCmd === false ) {
			$message .= __('La commande arp-scan est introuvable, relancer les dépendances.',__FILE__);
			config::save('cmd_arpscan', '', 'infoloc');
		} else {
			$message .= __('La commande arp-scan est',__FILE__).' '.$ArpscanCmd;;
			config::save('cmd_arpscan', $ArpscanCmd, 'infoloc');
		}

		if ( $PingCmd === false || $ArpingCmd === false || $ArpscanCmd === false ) {
			ajax::error($message, 1);
		} else {
			ajax::success($message);
		}
    }
    // Methode inconnue
    throw new \Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
} catch (\Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}
