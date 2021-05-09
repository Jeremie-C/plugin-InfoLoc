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
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('infoloc');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

if( config::byKey('tokenORS', 'infoloc') == '' ) {
	echo '<div class="jqAlert alert-warning" id="div_pasTokenORS" style="width: 100%;">{{Token ORS non renseigné. Rendez-vous sur la page de configuration.}}</div>';
}
if( config::byKey('cmd_ping','infoloc') == '' && config::byKey('cmd_arping','infoloc') == '' && config::byKey('cmd_arpscan','infoloc') == '') {
	echo '<div class="jqAlert alert-warning" id="div_pasCMDPing" style="width: 100%;">{{Détection de présence impossible. Rendez-vous sur la page de configuration.}}</div>';
}
?>
<div class="row row-overflow">
    <div class="col-xs-12 eqLogicThumbnailDisplay">
        <legend><i class="fa fa-cog"></i> {{Gestion}}</legend>
        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction logoPrimary" data-action="add">
                <i class="fas fa-plus-circle"></i><br /><span>{{Ajouter Client}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" data-action="addAdresse">
                <i class="fas fa-crosshairs"></i><br /><span>{{Ajouter Adresse}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
                <i class="fas fa-wrench"></i><br /><span>{{Configuration}}</span>
            </div>
        </div>

        <legend><i class="fas fa-table"></i> {{Mes clients}}</legend>
        <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
        <div class="eqLogicThumbnailContainer">
            <?php
            foreach( $eqLogics as $eqLogic ) {
                if( $eqLogic->getConfiguration('type') != 'adresse' ) {
                    $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                    echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
                    echo '<img src="' . $plugin->getPathImgIcon() . '"/><br />';
                    echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span></div>';
                }
            }
            ?>
        </div>
        <legend><i class="fas fa-crosshairs"></i> {{Mes adresses}}</legend>
        <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
        <div class="eqLogicThumbnailContainer">
            <?php
            foreach( $eqLogics as $eqLogic ) {
                if( $eqLogic->getConfiguration('type') == 'adresse' ) {
                    $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                    echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
                    echo '<img src="' . $plugin->getPathImgIcon() . '"/><br />';
                    echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span></div>';
                }
            }
            ?>
        </div>
    </div>

    <div class="col-xs-12 eqLogic" style="display: none;">
        <div class="input-group pull-right" style="display:inline-flex">
            <span class="input-group-btn">
                <a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
                <a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a>
                <a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
                <a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
            </span>
        </div>
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
            <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
        </ul>

        <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
            <div role="tabpanel" class="tab-pane active" id="eqlogictab"><br />
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Nom de l'équipement}}</label>
                            <div class="col-sm-3">
                                  <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                  <input id="typeEq" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type" style="display : none;" />
                                  <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Objet parent}}</label>
                            <div class="col-sm-3">
                                <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                    <option value="">{{Aucun}}</option>
                                    <?php
                                    foreach( jeeObject::all() as $object ) {
                                        echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"></label>
                            <div class="col-sm-9">
             			        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
              			        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                            </div>
                        </div>

                        <div class="form-group pingModeSel">
                            <label class="col-sm-2 control-label">{{Détection de présence}}</label>
                            <div class="col-sm-9">
                                <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pingMode">
                                    <option value="none">{{Aucun}}</option>
                                    <option value="icmp" <?php if( config::byKey('cmd_ping','infoloc')=='' ) {print("disabled");}?>>{{Ping ICMP}}</option>
                                    <option value="arpi" <?php if( config::byKey('cmd_arping','infoloc')=='' ) {print("disabled");}?>>{{Ping ARP}}</option>
                                    <option value="arps" <?php if( config::byKey('cmd_arpscan','infoloc')=='' ) {print("disabled");}?>>{{Scan ARP}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group pingMode icmp arpi">
                            <label class="col-sm-2 control-label">{{Adresse IP}}</label>
                            <div class="col-sm-9">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pingip"/>
                            </div>
                        </div>
                        <div class="form-group pingMode arps">
                            <label class="col-sm-2 control-label">{{Adresse MAC}}</label>
                            <div class="col-sm-9">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pingmac"/>
                            </div>
                        </div>
                        <div class="form-group pingMode arps arpi">
                            <label class="col-sm-2 control-label">{{Interface}}</label>
                            <div class="col-sm-9">
                                <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pingEth">
                                    <?php
                                    $allinterfacesinfo = network::getInterfacesInfo();
                                    foreach( $allinterfacesinfo as $interfaceinfo ) {
                                        $interfacename = $interfaceinfo["ifname"];
                                        if ( $interfacename !== "lo" ) {
                                            echo '<option value="' . $interfacename . '">' . $interfacename . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div role="tabpanel" class="tab-pane" id="commandtab"><br />
                <div id="alertCMD" class="alert jqAlert alert-info">{{Exemple d'URL à appeler}} : <?php echo network::getNetworkAccess('external')?>/plugins/infoloc/core/api/jeeInfoloc.php?apikey=<?php echo  jeedom::getApiKey('infoloc');?>&id=#ID&value=#VALUE</div>
                <a id="btnCMD" class="btn btn-success btn-sm pull-right cmdAction" data-action="add"><i class="fa fa-plus-circle"></i> {{Commandes}}</a>
                <div id="getGPS" class="alert jqAlert alert-info"><a class="control-label pull-left" href="http://www.coordonnees-gps.fr/" target="_blank"><i class="icon nature-planet5"></i>&nbsp;{{Cliquez-ici pour obtenir les coordonnées}}</a><br /></div>
                <table id="table_cmd" class="table table-bordered table-condensed">
                    <thead>
                    	<tr>
                        	<th style="width: 50px;">ID</th>
                        	<th style="width: 150px;">{{Nom}}</th>
                        	<th style="width: 150px;">{{Type}}</th>
                        	<th>{{Options}}</th>
                        	<th style="width: 300px;">{{Paramètres}}</th>
                        	<th style="width: 150px;"></th>
                    	</tr>
                	</thead>
                	<tbody>
                	</tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_file('desktop', 'infoloc', 'js', 'infoloc');?>
<?php include_file('core', 'plugin.template', 'js');?>
