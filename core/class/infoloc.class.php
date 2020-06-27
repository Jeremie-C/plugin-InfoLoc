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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__).'/../../../../core/php/core.inc.php';

class infoloc extends eqLogic {

    public function add_Adresse() {
        $i = count(eqlogic::byTypeAndSearhConfiguration('infoloc', 'adresse'));
        log::add('infoloc', 'debug', 'Création Objet Adresse');
        $eqLogic = new infoloc();
        $eqLogic->setName('Adresse_'.$i++);
        $eqLogic->setEqType_name('infoloc');
        $eqLogic->setConfiguration('type', 'adresse');
        $eqLogic->setIsEnable(0);
        $eqLogic->setIsVisible(0);
        $eqLogic->save();
        log::add('infoloc', 'debug', 'Création Commande Coordonnées');
        $cmd = new infolocCmd();
        $cmd->setName('Coordonnées');
        $cmd->setType('info');
        $cmd->setSubType('string');
        $cmd->setIsVisible(0);
        $cmd->setLogicalId('fixelatlon');
        $cmd->setConfiguration('mode', 'fixe');
        $cmd->setEqLogic_id($eqLogic->getId());

        $geo = config::byKey('info::latitude').','.config::byKey('info::longitude');
        if( $cmd->validateLatLong($geo) ) {
            $cmd->setConfiguration('coordinate', $geo);
        } else {
            $cmd->setConfiguration('coordinate', '48.8582602,2.2944991');
        }
        $cmd->save();
    }

    public function preInsert() {
        log::add('infoloc', 'debug', 'EqLogic - PreInsert');
        if( $this->getConfiguration('type') != 'adresse' ) {
            $this->setConfiguration('type', 'client');
            $this->setConfiguration('pingMode', 'none');
            $this->setConfiguration('pingip', '127.0.0.1');
            $this->setConfiguration('pingmac', '00:00:00:00:00:00');
            $this->setConfiguration('pingEth', '');
            log::add('infoloc', 'debug', 'EqLogic - PreInsert CLIENT');
        }
    }

    public function postInsert() {
        log::add('infoloc', 'debug', 'EqLogic - PostInsert');
        if( $this->getConfiguration('type') != 'adresse' ) {
            // Commande de présence
            $cmd = $this->getCmd(null, 'present');
            if( !is_object($cmd) ) {
                $cmd = new infolocCmd();
                $cmd->setName(__('Présent', __FILE__));
                $cmd->setEqLogic_id($this->getId());
                $cmd->setLogicalId('present');
                $cmd->setType('info');
                $cmd->setSubType('binary');
                $cmd->setConfiguration('mode', 'binary');
                $cmd->setEventOnly(1);
                $cmd->setTemplate('dashboard','line');
                $cmd->setTemplate('mobile','line');
                if( $this->getConfiguration('pingMode') == 'none' ) {
                    $cmd->setIsVisible(0);
                    $cmd->setIsHistorized(0);
                } else {
                    $cmd->setIsVisible(1);
                    $cmd->setIsHistorized(1);
                }
                $cmd->save();
            }
            // Commande de position
            $cmd = $this->getCmd(null, 'gpspos');
            if( !is_object($cmd) ) {
                $cmd = new infolocCmd();
                $cmd->setName(__('Position GPS', __FILE__));
                $cmd->setEqLogic_id($this->getId());
                $cmd->setLogicalId('gpspos');
                $cmd->setType('info');
                $cmd->setSubType('string');
                $cmd->setConfiguration('mode', 'gpspos');
                $cmd->setEventOnly(1);
                $cmd->setIsVisible(0);
                $cmd->setIsHistorized(0);
                $cmd->save();
            }
        }
    }

    public function preUpdate() {
        log::add('infoloc', 'debug', 'EqLogic - PreUpdate: '.$this->getConfiguration('type'));
        if( $this->getConfiguration('type') == 'client' ) {
            if( $this->getConfiguration('pingMode') == '' ) {
                $this->setConfiguration('pingMode', 'none');
            }
            if( $this->getConfiguration('pingMode') == 'arps' ) {
                if( !preg_match("/^[0-9A-F][0-9A-F]:[0-9A-F][0-9A-F]:[0-9A-F][0-9A-F]:[0-9A-F][0-9A-F]:[0-9A-F][0-9A-F]:[0-9A-F][0-9A-F]$/", strtoupper($this->getConfiguration('pingmac'))) ) {
                    ajax::error(__('Erreur adresse mac', __FILE__).'"'.strtoupper($this->getConfiguration('pingmac')).'"');
                    return false;
                }
            } else {
                if( !preg_match("/^[0-9\.]*$/", $this->getConfiguration('pingip')) ) {
                    $ip = gethostbyname($this->getConfiguration('pingip'));
                    if( $this->getConfiguration('pingip') == gethostbyname($this->getConfiguration('pingip')) ) {
                        ajax::error(__('Erreur Hostname (gethostbyname)', __FILE__));
                        return false;
                    }
                }
            }
        }
    }

    public static function GetPingCmd() {
        log::add('infoloc','debug','Essai commande ping.');
        unset($cmd);
        $which = exec('which ping 2>&1', $cmd, $code);
        log::add('infoloc','debug','Code :'.$code);
        log::add('infoloc','debug','Return :'.join(" | ",$cmd));
        if( $code == 0 ) {
            unset($return);
            $cmd = 'sudo '.join(" | ",$cmd);
            $result = exec($cmd.' -n -c2 -q 127.0.0.1 2>&1', $return, $code);
            log::add('infoloc','debug','Code :'.$code);
            log::add('infoloc','debug','Return :'.join(" | ",$return));
            if( $code == 0 ) {
                return $cmd;
            }
        }
        return false;
    }

    public static function GetArpingCmd() {
        log::add('infoloc','debug','Essai commande arping.');
        unset($cmd);
        $which = exec('which arping 2>&1', $cmd, $code);
        log::add('infoloc','debug','Code :'.$code);
        log::add('infoloc','debug','Return :'.join(" | ",$cmd));
        if( $code == 0 ) {
            unset($return);
            $cmd = 'sudo '.join(" | ",$cmd);
            $result = exec($cmd.' -h 2>&1', $return, $code);
            log::add('infoloc','debug','Code :'.$code);
            log::add('infoloc','debug','Return :'.join(" | ",$return));
            if( $code == 0 ) {
                return $cmd;
            }
        }
        return false;
    }

    public static function GetArpscanCmd() {
        log::add('infoloc','debug','Essai commande arp-scan.');
        unset($cmd);
        $which = exec('which arp-scan 2>&1', $cmd, $code);
        log::add('infoloc','debug','Code :'.$code);
        log::add('infoloc','debug','Return :'.join(" | ",$cmd));
        if( $code == 0 ) {
            unset($return);
            $cmd = 'sudo '.join(" | ",$cmd);
            $result = exec($cmd.' -l 2>&1', $return, $code);
            log::add('infoloc','debug','Code :'.$code);
            log::add('infoloc','debug','Return :'.join(" | ",$return));
            if( $code == 0 ) {
                return $cmd;
            }
        }
        return false;
    }

    public static function pull() {
        foreach(self::byType('infoloc') as $eqLogic) {
            $eqLogic->ping();
        }
    }

    public function ping() {
        if( $this->getIsEnable() && $this->getConfiguration('pingMode') != 'none' ) {
            log::add('infoloc','debug','Ping: '.$this->getHumanName());
            log::add('infoloc','debug','Mode : '.$this->getConfiguration('pingMode'));

            $presentCmd = $this->getCmd(null, 'present');
            $posgpsCmd = $this->getCmd(null, 'gpspos');
            $geo = config::byKey('info::latitude').','.config::byKey('info::longitude');
            if( infolocCmd::validateLatLong($geo) ) {
                $gpspos = $geo;
            } else {
                $gpspos = '';
            }

            switch( $this->getConfiguration('pingMode') ) {
                case 'arps':
                    $cmd = config::byKey('cmd_arpscan', 'infoloc');
                    $cmd.= ' -I '.$this->getConfiguration('pingEth');
                    $cmd.= ' -l -g --retry=5 -t 800 -T ';
                    $cmd.= $this->getConfiguration('pingmac').' 2>&1';

                    log::add('infoloc','debug',$cmd);
                    $res = exec($cmd, $return, $code);
                    log::add('infoloc','debug','Retour commande '.join("\n", $return));

                    if( preg_match("/\t".strtolower($this->getConfiguration('pingmac'))."\t/", strtolower(join("\n", $return))) ) {
                        if( $presentCmd->execCmd() != 1 ) {
                            $presentCmd->event(1);
                            $posgpsCmd->event($geo);
                            log::add('infoloc','info',$this->getHumanName().' '.__('est présent', __FILE__));
                        }
                    } else {
                        if( $presentCmd->execCmd() != 0 ) {
                            $presentCmd->event(0);
                            log::add('infoloc','info',$this->getHumanName().' '.__('est absent', __FILE__));
                        }
                    }
                    break;
                case 'arpi':
                    $cmd = config::byKey('cmd_arping', 'infoloc');
                    $cmd.= ' -c 10 -C 1 -w 5';
                    $cmd.= ' -I '.$this->getConfiguration('pingEth').' ';
                    $cmd.= $this->getConfiguration('pingip').' 2>&1';

                    log::add('infoloc','debug',$cmd);
                    $res = exec($cmd, $return, $code);
                    log::add('infoloc','debug','Retour commande '.$code.' - '.join("\n", $return));

                    $return = array_values(array_filter($return));
                    $line = $return[count($return)-4];
                    if( preg_match("/([a-fA-F0-9:]{17}|[a-fA-F0-9]{12})\s\(".$this->getConfiguration('pingip')."\):\s/", $line) ) {
                        if( $presentCmd->execCmd() != 1 ) {
                            $presentCmd->event(1);
                            $posgpsCmd->event($geo);
                            log::add('infoloc','info',$this->getHumanName().' '.__('est présent', __FILE__));
                        }
                    } else {
                        if( $presentCmd->execCmd() != 0 ) {
                            $presentCmd->event(0);
                            log::add('infoloc','info',$this->getHumanName().' '.__('est absent', __FILE__));
                        }
                    }
                    break;
                default:
                    $cmd = config::byKey('cmd_ping', 'infoloc');
                    $cmd.= ' -n -c2 -q ';
                    $cmd.= $this->getConfiguration('pingip').' 2>&1';

                    log::add('infoloc','debug',$cmd);
                    $res = exec($cmd, $return, $code);
                    log::add('infoloc','debug','Retour commande '.join("\n", $return));

                    if( $code == 0 ) {
                        if( $presentCmd->execCmd() != 1 ) {
                            $presentCmd->event(1);
                            $posgpsCmd->event($geo);
                            log::add('infoloc','info',$this->getHumanName().' '.__('est présent', __FILE__));
                        }
                    } else {
                        if( $presentCmd->execCmd() != 0 ) {
                            $presentCmd->event(0);
                            log::add('infoloc','info',$this->getHumanName().' '.__('est absent', __FILE__));
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Get dependancy information
     * @return array Python3 command return.
     */
    public static function dependancy_info() {
        $return = [
            'state' => 'ok',
            'log' => 'infoloc_update',
            'progress_file' => '/tmp/dependancy_infoloc_in_progress'
        ];
        if( exec('which ping | wc -l')==0 || exec('which arping | wc -l')==0 || exec('which arp-scan | wc -l')==0 ) {
            if (exec("dpkg --get-selections | grep -v deinstall | grep -E 'iputils-ping|arping|arp-scan' | wc -l") != 2) {
                $return['state'] = 'nok';
            }
        }
        return $return;
    }
    /**
     * Install dependancies.
     * @return array Shell script command return.
     */
    public static function dependancy_install() {
        log::remove(__CLASS__ . '_update');
        return [
            'script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder('infoloc') . '/dependance',
            'log' => log::getPathToLog(__CLASS__ . '_update')
        ];
    }
}

class infolocCmd extends cmd {

    public function preInsert() {
        log::add('infoloc', 'debug', 'CMD - PreInsert : '.$this->getConfiguration('mode'));
        switch( $this->getConfiguration('mode') ) {
            case 'battery':
                $eqLogic = infoloc::byId( $this->getEqLogic_id() );
                foreach( $eqLogic->getCmd() as $cmd ) {
                    if( $cmd->getConfiguration('mode') == 'battery' ) {
                        throw new \Exception(__('Cet équipement à déjà une commande de batterie', __FILE__));
                    }
                }
                $this->setUnite('%');
                $this->setConfiguration('minValue', 0);
                $this->setConfiguration('maxValue', 100);
                break;
            case 'roaddist':
            case 'gpsdist':
                $this->setUnite('km');
                break;
            case 'roadtime':
                $this->setUnite('min');
                break;
        }
    }

    public function preSave() {
        log::add('infoloc', 'debug', 'CMD - PreSave : '.$this->getConfiguration('mode'));
        if( $this->getConfiguration('mode') == 'gpsdist' || $this->getConfiguration('mode') == 'roaddist' || $this->getConfiguration('mode') == 'roadtime' ) {
            $from = '#'.$this->getConfiguration('from').'#';
            $to = '#'.$this->getConfiguration('to').'#';
            $this->setValue($from.$to);
        }
        if( substr($this->getConfiguration('apiProfile'), 0, 7) != 'driving' && ($this->getConfiguration('mode') == 'roaddist' || $this->getConfiguration('mode') == 'roadtime') ) {
            $this->setConfiguration('autoroute', '0');
            $this->setConfiguration('peage', '0');
        }
        if( $this->getConfiguration('mode') == 'fixe' ) {
            log::add('infoloc', 'debug', 'Valeur: '.$this->getConfiguration('coordinate'));
            if( !self::validateLatLong($this->getConfiguration('coordinate')) ) {
                throw new \Exception(__('Coordonnées incorrectes.', __FILE__));
            }
        }
    }

    public function postSave() {
        switch( $this->getConfiguration('mode') ) {
            case 'fixe':
                $this->event($this->getConfiguration('coordinate'));
                break;
            case 'gpsdist':
                $this->execute();
                break;
            case 'roaddist':
                $this->execute();
                break;
            case 'roadtime':
                $this->execute();
                break;
        }
    }

    function validateLatLong($geo) {
        log::add('infoloc', 'debug', 'Validation coordonnées: '.$geo);
        return preg_match("/^[-]?((([0-8]?[0-9])(\.(\d{1,15}))?)|(90(\.0+)?)),\s?[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.(\d{1,15}))?)|180(\.0+)?)$/", $geo);
    }

    function calcultrajet($from, $to, $profile='driving-car', $hyghway=1, $toll=1, $ferry=0) {
        if (strcmp($from, $to) == 0) {
            return array('distance' => 0, 'time' => 0);
        }
        $from = explode(',', $from);
        $to = explode(',', $to);

        $avoid = array();
        $preference = 'recommended';
        $optionsAdd = '';

        if( substr($profile, 0, 7) == 'driving' ) {
            if( $hyghway != 1 ) {
                $avoid[] = "highways";
            }
            if( $toll != 1 ) {
                $avoid[] = "tollways";
            }
            if( $profile == 'driving-hgv' ) {
                $preference = 'fastest';
                $optionsAdd.= ',"vehicle_type":"hgv"';
            }
        }
        if( $ferry != 1 ) {
            $avoid[] = "ferries";
        }
        $avoid = json_encode($avoid);

        $token = config::byKey('tokenORS', 'infoloc');
        $language = substr( strtolower(config::byKey('language')),0,2);
        $units = 'km';
        if( $language == 'en_us' ) {
            $units = 'mi';
        }

        $options = '{"instructions":"false","geometry":"false","suppress_warnings":"true",';
        $options.= '"preference":"'.$preference.'","language":"'.$language.'",';
        $options.= '"options":{"avoid_features":'.$avoid.$optionsAdd.'},"units":"'.$units.'",';
        $options.= '"coordinates":[['.$from[1].','.$from[0].'],['.$to[1].','.$to[0].']]';
        $options.= '}';

        log::add('infoloc', 'debug', 'Options: '.$options);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openrouteservice.org/v2/directions/".$profile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $options);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Accept: application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8",
          "Authorization: ".$token,
          "Content-Type: application/json; charset=utf-8"
        ));

        if( ! $response = curl_exec($ch)) {
            log::add('infoloc','error', 'Erreur Curl: '.curl_error($ch));
            return array('distance' => '', 'time' => '');
        }
        curl_close($ch);
        log::add('infoloc', 'debug', 'Reponse: '.$response);

        $data = json_decode($response, true);

        if( array_key_exists('error', $data) ) {
            if( is_array($data['error']) ) {
                log::add('infoloc', 'error', 'Erreur '.$data['error']['code'].' : '.$data['error']['message']);
            } else {
                log::add('infoloc', 'error', $data['error']);
            }
            return array('distance' => '', 'time' => '');
        }

        $distance = $data['routes'][0]['summary']['distance'];
        $distance = round($distance, 1);
        $time = $data['routes'][0]['summary']['duration'];
        $time = floor($time / 60);
        log::add('infoloc', 'debug', 'distance: '.$distance.' time: '.$time);
        return array('distance' => $distance, 'time' => $time);
    }

    function distance($lat1, $lng1, $lat2, $lng2) {
        $earth_radius = 6378.137; // Terre = sphère de 6378km de rayon
        $rlo1 = deg2rad($lng1);
        $rla1 = deg2rad($lat1);
        $rlo2 = deg2rad($lng2);
        $rla2 = deg2rad($lat2);
        $dlo = ($rlo2 - $rlo1) / 2;
        $dla = ($rla2 - $rla1) / 2;
        $a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
        $d = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $value = round(($earth_radius * $d), 2);
        log::add('infoloc', 'debug', 'distance: '.$value);
        return $value;
    }

    public function execute($_options = array()) {
        log::add('infoloc', 'debug', '##################################');
        log::add('infoloc', 'debug', 'EQLOGIC_ID: '.$this->getEqLogic()->getId());
        log::add('infoloc', 'debug', 'CMD_ID: '.$this->getId());
        log::add('infoloc', 'debug', $this->getConfiguration('mode'));
        log::add('infoloc', 'debug', '##################################');

        switch ($this->getConfiguration('mode')) {
            case 'gpsdist':
                $from = infolocCmd::byId($this->getConfiguration('from'));
                $to = infolocCmd::byId($this->getConfiguration('to'));
                $from = explode(',', $from->execCmd());
                $to = explode(',', $to->execCmd());
                if( count($from) > 2 ) {
                    $from[2] = implode(',', array_slice($from, 1));
                }
                if( count($to) > 2 ) {
                    $to[2] = implode(',', array_slice($to, 1));
                }
                if( count($to) == 2 && count($from) == 2 ) {
                    return self::distance($from[0], $from[1], $to[0], $to[1]);
                }
                return '';
                break;
            case 'roaddist':
                $from = infolocCmd::byId($this->getConfiguration('from'));
                $to = infolocCmd::byId($this->getConfiguration('to'));
                $profil = $this->getConfiguration('apiProfile');
                $autoroute = $this->getConfiguration('autoroute');
                $peages = $this->getConfiguration('peage');
                $ferry = $this->getConfiguration('ferry');
                try {
                    $result = self::calcultrajet($from->execCmd(), $to->execCmd(), $profil, $autoroute, $peages, $ferry);
                    return $result['distance'];
                } catch (Exception $e) {
                    return '';
                }
                break;
            case 'roadtime':
                $from = infolocCmd::byId($this->getConfiguration('from'));
                $to = infolocCmd::byId($this->getConfiguration('to'));
                $profil = $this->getConfiguration('apiProfile');
                $autoroute = $this->getConfiguration('autoroute');
                $peages = $this->getConfiguration('peage');
                $ferry = $this->getConfiguration('ferry');
                try {
                    $result = self::calcultrajet($from->execCmd(), $to->execCmd(), $profil, $autoroute, $peages, $ferry);
                    return $result['time'];
                } catch (Exception $e) {
                    return '';
                }
                break;
        }
    }
}
?>
