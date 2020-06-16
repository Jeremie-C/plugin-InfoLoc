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

$('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').on('change',function(){
    if( $(this).value() == 'adresse' ) {
        $('.pingModeSel').hide();
        $('.pingMode').hide();
    }
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=pingMode]').on('change',function(){
    $('.pingMode').hide();
    $('.pingMode.'+$(this).value()).show();
});

$('#bt_FindAppBin').on('click', function() {
    $.ajax({
        type: "POST",
        url: "plugins/infoloc/core/ajax/infoloc.ajax.php",
        data: {
            action: "FindAppBin",
        },
        dataType: 'json',
        error: function(request, status, error) {
            handleAjaxError(request, status, $('#div_FindAppBin'));
        },
        success: function(data) {
 			if (data.state != 'ok') {
 				$('#div_FindAppBin').showAlert({message: data.result, level: 'danger'});
 			} else {
 				$('#div_FindAppBin').showAlert({message: data.result, level: 'success'});
 			}
 			jeedom.config.load({
                configuration: $('#config').getValues('.configKey')[0],
 				plugin: 'infoloc',
                error: function (error) {
                    $('#div_alert').showAlert({message: error.message, level: 'danger'});
                },
                success: function (data) {
                    $('#config').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    $('#div_alert').showAlert({message: '{{Sauvegarde réussie}}', level: 'success'});
                }
            });
        }
    });
});

optionCmdFrom = null;
optionCmdDest = null;
eqLogicCmd = null;

$('.eqLogicAttr[data-l1key=id]').change(function () {
    if( $(this).value() != '' ) {
        eqLogicCmd = $(this).value();
    }
});

$('.eqLogicAction[data-action=addAdresse]').on('click', function () {
    $('#div_alert').showAlert({message: '{{Ajouter un adresse en cours}}', level: 'warning'});
    $.post({
        url: 'plugins/infoloc/core/ajax/infoloc.ajax.php',
        data: {
            action: 'addAdresse'
        },
        success: function (data, status) {
            // Test si l'appel a échoué
            if (data.state !== 'ok' || status !== 'success') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            $('#div_alert').showAlert({message: '{{Ajout effectué.}}', level: 'success'});
            window.location.reload();
        },
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        }
    });
});

$( "#typeEq" ).change(function(){
    if( $('#typeEq').value() == 'adresse' ) {
        $('#alertCMD').hide();
        $('#btnCMD').hide();
        $('#getGPS').show();
    } else {
        $('#alertCMD').show();
        $('#btnCMD').show();
        $('#getGPS').hide();
    }
});

$('#table_cmd tbody').delegate('.cmdAttr[data-l1key=configuration][data-l2key=mode]', 'change', function () {
    var tr = $(this).closest('tr');
    tr.find('.modeOption').hide();
    tr.find('.modeOption' + '.' + $(this).value()).show();
    if($(this).value() == 'binary'){
        tr.find('.cmdAttr[data-l1key=subtype]').value('binary');
    } else if($(this).value() == 'battery' || $(this).value() == 'numeric' || $(this).value() == 'gpsdist' || $(this).value() == 'roaddist' || $(this).value() == 'roadtime'){
        tr.find('.cmdAttr[data-l1key=subtype]').value('numeric');
    } else {
        tr.find('.cmdAttr[data-l1key=subtype]').value('string');
    }
});

function getCmdFrom() {
    var select = '';
    $.ajax({
        type: "POST",
        url: "plugins/infoloc/core/ajax/infoloc.ajax.php",
        data: {
            action: "getCmdFrom",
            eqlogic: eqLogicCmd
        },
        dataType: "json",
        async: false,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            for (var i in data.result) {
                select += '<option value="' + data.result[i].id + '">' + data.result[i].point_name + '</option>';
            }
        }
    });
    return select;
}

function getCmdDest() {
    var select = '';
    $.ajax({
        type: "POST",
        url: "plugins/infoloc/core/ajax/infoloc.ajax.php",
        data: {
            action: "getCmdDest"
        },
        dataType: "json",
        async: false,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            console.log(data.result);
            for (var i in data.result) {
                select += '<option value="' + data.result[i].id + '">' + data.result[i].dest_name + '</option>';
            }
        }
    });
    return select;
}

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

function printEqLogic(_data){
    optionCmdFrom = null;
    optionCmdDest = null;
}

function addCmdToTable(_cmd) {
    if( !isset(_cmd) ) {
        var _cmd = {configuration: {}};
    }
    if( init(_cmd.logicalId) == 'refresh') {
        return;
    }
    if( optionCmdFrom == null ) {
        optionCmdFrom = getCmdFrom();
    }
    if( optionCmdDest == null ) {
        optionCmdDest = getCmdDest();
    }

    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" ></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="type" value="info" style="display : none;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="subtype" value="string" style="display : none;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" >';
    tr += '</td>';
    tr += '<td>';
    if( _cmd.logicalId != 'fixelatlon' && _cmd.logicalId != 'present' && _cmd.logicalId != 'gpspos' ) {
        tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="mode">';
        tr += '<option value="string">{{Autre}}</option>';
        tr += '<option value="numeric">{{Numérique}}</option>';
        tr += '<option value="binary">{{Binaire}}</option>';
        tr += '<option value="battery">{{Batterie}}</option>';
        tr += '<option value="gpsdist">{{Distance directe}}</option>';
        tr += '<option value="roaddist">{{Distance du trajet}}</option>';
        tr += '<option value="roadtime">{{Temps de trajet}}</option>';
        tr += '</select>';
    } else {
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="mode" style="display : none;">';
    }
    tr += '</td>';
    tr += '<td>';
    if( _cmd.logicalId == 'fixelatlon' ) {
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="coordinate" placeholder="{{Latitude,Longitude}}" >';
    }

    tr += '<span class="modeOption gpsdist roaddist roadtime" style="display : none;">';
    tr += 'De ';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="from" style="display : inline-block; width : 300px;" disabled>';
    tr += optionCmdFrom;
    tr += '</select>';
    tr += ' à ';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="to" style="display : inline-block; width : 300px;">';
    tr += optionCmdDest;
    tr += '</select>';
    tr += '</span>';

    tr += '<span class="modeOption roaddist roadtime" style="display : none;"> en ';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="apiProfile" style="display : inline-block; width : 100px;">';
    tr += '<option value="driving-car">{{Voiture}}</option>';
    tr += '<option value="cycling-regular">{{Vélo}}</option>';
    tr += '<option value="foot-walking">{{Marchant}}</option>';
    tr += '<option value="driving-hgv">{{Poid Lourd}}</option>';
    tr += '</select> ';
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="configuration" data-l2key="autoroute" checked/>{{Autoroutes}}</label> ';
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="configuration" data-l2key="peage" checked/>{{Péages}}</label> ';
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="configuration" data-l2key="ferry"/>{{Ferry}}</label> ';
    tr += '</span>';

    tr += '</td>';
    tr += '<td>';

    tr += '<span class="modeOption numeric" style="display : none;"> ';
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:20%;display:inline-block;">';
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:20%;display:inline-block;">';
    tr += '</span> ';

    tr += '<span class="modeOption numeric string"> ';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:20%;display:inline-block;margin-right:5px;">';
    tr += '</span> ';

    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" />{{Afficher}}</label></span> ';

    tr += '<span class="modeOption battery gpsdist numeric binary" style="display : none;"> ';
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" />{{Historiser}}</label>';
    tr += '</span> ';

    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    if( _cmd.logicalId != 'fixelatlon' && _cmd.logicalId != 'present' && _cmd.logicalId != 'gpspos' ) {
        tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    }
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
}