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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
// Infos Localisation Configuration Jeedom
$geo = config::byKey('info::latitude').','.config::byKey('info::longitude');
$geoConf = infolocCmd::validateLatLong($geo);
// Infos SUDO
$okSudo = jeedom::isCapable('sudo', true);
?>
<form class="form-horizontal" id="config">
    <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Coordonnées Jeedom}}</label>
            <?php
            if( $geoConf ) {
                echo "<label class='col-lg-3'><span class='label label-success'>OK</span></label>";
            } else {
                echo "<label class='col-lg-3'><span class='label label-danger'>KO</span> <a href='index.php?v=d&p=administration#infotab'>{{Configurer ici}}</a></label>";
            };
            ?>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Droits sudo}}</label>
            <?php
            if( $okSudo ) {
                echo "<label class='col-lg-3'><span class='label label-success'>OK</span></label>";
            } else {
                echo "<label class='col-lg-3'><span class='label label-danger'>KO</span> <a href='index.php?v=d&p=administration#infotab'>{{Configurer ici}}</a></label>";
            };
            ?>
        </div>
        <br/>

        <div class="form-group">
            <label class="col-lg-4 control-label"><a href='https://openrouteservice.org/dev/#/signup' target="_blank">Token OpenRoute Service</a></label>
            <div class="col-lg-4">
                <input class="configKey form-control" data-l1key="tokenORS" placeholder=""/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Commande ping}}</label>
            <div class="col-lg-3">
                <input class="configKey form-control" data-l1key="cmd_ping" disabled/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Commande arping}}</label>
            <div class="col-lg-3">
                <input class="configKey form-control" data-l1key="cmd_arping" disabled/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Commande arp-scan}}</label>
            <div class="col-lg-3">
                <input class="configKey form-control" data-l1key="cmd_arpscan" disabled/>
            </div>
        </div>
        <div id='div_FindAppBin' style="display: none;"></div>
        <div class="form-group">
    		<div class="col-lg-4"></div>
    		<div class="col-lg-3">
    			<a class="btn btn-warning" id="bt_FindAppBin" style="color : white;"><i class="fa fa-wrench"></i> {{Detecter les programmes}}</a>
    		</div>
        </div>
    </fieldset>
</form>
<?php include_file('desktop', 'infoloc', 'js', 'infoloc'); ?>
