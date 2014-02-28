<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<div style="padding: 10px;" id="mcontainer">
    <div style="float:right;width:350px;">
        <div class="ui-widget" style="margin-bottom:10px;">
            <div class="ui-state-highlight ui-corner-all" style="padding: 0.7em;">

                <p style="font-weight:bold;">
                    <?= ui_ficon('wrench'); ?> <?= lang('general_settings'); ?>
                </p>

                <table>
                    <tr>
                        <th><?= lang('general_map_center'); ?></th>
                        <td>
                            <input id="cx" value="<?= $cx; ?>" style="width:30px;" />|
                            <input id="cy" value="<?= $cy; ?>" style="width:30px;" />
                        </td>
                    </tr>
                    <tr>
                        <th><?= lang('general_map_zoom'); ?></th>
                        <td>
                            <select id="zoom">
                                <?php for ($i = 1; $i <= 20; $i++): ?>
                                    <option <?php if ($i == $zoom) { echo 'selected="selected"';} ?>>
                                        <?= $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <button id="storeBtn">
                    <?= ui_ficon('map'); ?>
                    <?= lang('map_render'); ?>
                </button>

            </div>
        </div>

        <div class="ui-widget" style="margin-bottom:10px;">
            <div class="ui-state-highlight ui-corner-all" style="padding: 0.7em;">

                <p style="font-weight:bold;">
                    <?= ui_ficon('map_add'); ?> <?= lang('general_markup'); ?>
                </p>

                <table id="markupTable">
                    <tr>
                        <th>Farbe</th>
                        <th>Name</th>
                        <th></th>
                    </tr>
                    
                </table>

                <button id="addBtn"><?= ui_ficon('add'); ?></button>

            </div>
        </div>
    </div>

    <canvas id="main_map" width="600" height="600">
        <?=lang('map_wrong_browser');?>
    </canvas>
</div>

<script type="text/javascript">
    
    var currentlyMarked = [];
    
    $(function() {   
        var map = new TW_CanvasMap(600, 600, 'main_map'); 
        map.setScale(<?= $zoom; ?>);
        map.setCenter(<?= $cx; ?>, <?= $cy; ?>);
        
        map.clearMarked();
        for(var i in currentlyMarked)
        {
            var m = currentlyMarked[i];
            if (m != undefined) {
                map.mark(m.type, m.id, m.color);
            }
        }
        
        map.render('<?= site_url('tools/map/dataAPI'); ?>', 
        "<?= $this->security->get_csrf_token_name(); ?>", 
        "<?= $this->security->get_csrf_hash(); ?>");
                  
        $('#storeBtn')
        .button()
        .click(function() {
            if (typeof (window.history.pushState) == 'function') {
                window.history.pushState(null, 
                    'DSConect', 
                    '<?=site_url('tools/map/show');?>/' + $('#cx').val() + '/' + $('#cy').val()
                        + '/' + $('#zoom').val());
            }
            
            map.setScale($('#zoom').val());
            map.setCenter($('#cx').val(), $('#cy').val());
            map.clearMarked();
            for(var i in currentlyMarked)
            {
                var m = currentlyMarked[i];
                if (m != undefined) {
                    map.mark(m.type, m.id, m.color);
                }
            }
        
            map.render('<?= site_url('tools/map/dataAPI'); ?>', 
            "<?= $this->security->get_csrf_token_name(); ?>", 
            "<?= $this->security->get_csrf_hash(); ?>");
        });
        
        function add_markup(id, name, color, type, desc)
        {
            currentlyMarked[id] = {'id': id, 'color': color, 'name': name, 'type': type};
            
            var tr = $('<tr>');
            
            $('#markupTable').append(tr);
            
            tr.attr('id', 'markup_' + id);
            
            tr.append(
                    $('<td>').css('background-color', color).text(' '),
                    $('<td>').text(desc + ': ' + name),
                    $('<td>').html('<?=ui_ficon('cross');?>').css('cursor', 'pointer').click(
                        (function(killID) { return function() { remove_markup(killID) } })(id)
                    )
                );
        }
        
        function remove_markup(id)
        {
            currentlyMarked[id] = undefined;
            
            $('#markup_' + id).remove();
        }
        
        function create_markup(type, desc)
        {
            var d = $('<div>');
            
            d.append($('<span>').attr('id', 'color_preview').css('font-weight', 'bold').text('<?=lang('map_color');?>: '));
            d.append($('<input>').attr('id', 'color').val('#0000FF').keyup(function() {
                $('#color_preview').css('color', $(this).val());
            }));
            
            d.append($('<br>'));
            d.append($('<br>'));
            
            d.append($('<span>').css('font-weight', 'bold').text(desc + ': '));
            
            if (type == 'village') {
                d.append($('<input>').css('width', 60).attr('id', 'vx'));
                d.append($('<span>').css('font-weight', 'bold').text('|'));
                d.append($('<input>').css('width', 60).attr('id', 'vy'));
            }
            else {
                d.append($('<input>').attr('id', 'tname'));
            }
            
            $('#mcontainer').append(d);
            TWUtils.autoComplete('tname', type);
            
            d.dialog({
                title: desc,
                autoOpen: true,
                height: 300,
                width: 350,
                modal: true,
                buttons: {
                    '<?=lang('general_save');?>': function() {
                        
                        if (type == 'village') {
                            var x = $('#vx').val();
                            var y = $('#vy').val();
                            
                            if (isNaN(x) || isNaN(y)) {
                                alert('<?=lang('map_village_not_found');?>');
                                return;
                            }
                            
                            $.post(TWUtils.controller + "/coords_to_id", {
                                'x': x,
                                'y': y,
                                
                                "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
                            },
                            function(data) {
                                if (data.error) {
                                    alert('<?=lang('map_village_not_found');?>');
                                    return;
                                }
                                
                                var color = $('#color').val();
                                var tid = data.id;
                                var tname = data.name;
                                
                                add_markup(tid, tname, color, type, desc);
                                $(d).dialog( "close" );
                                
                            }, "json");
                        }
                        else {
                            var color = $('#color').val();
                            var tid = $('#tname_id').text();
                            var tname = $('#tname_text').text();
                            
                            if (!tid) {
                                alert('<?=lang('map_nothing_selected');?>');
                                return;
                            }
                            
                            add_markup(tid, tname, color, type, desc);
                            $( this ).dialog( "close" );
                        }
                    },
                    
                    '<?=lang('general_cancel');?>': function() {
                        $( this ).dialog( "close" );
                    }
                },
                close: function() {
                    $(this).remove();
                }
            });
        }
   
        $('#addBtn')
        .button()
        .click(function() {
            var d = $('<div>').text('<?=lang('map_markup_type');?>');
            $('#mcontainer').append(d);
            
            
            d.dialog({
                autoOpen: true,
                height: 150,
                width: 350,
                modal: true,
                buttons: {
                    '<?=lang('profile_player');?>': function() {
                        $( this ).dialog( "close" );
                        create_markup('player', '<?=lang('profile_player');?>');
                    },
                    '<?=lang('profile_ally');?>': function() {
                        $( this ).dialog( "close" );
                        create_markup('ally', '<?=lang('profile_ally');?>');
                    },
                    '<?=lang('profile_village');?>': function() {
                        $( this ).dialog( "close" );
                        create_markup('village', '<?=lang('profile_village');?>');
                    },
                    '<?=lang('general_cancel');?>': function() {
                        $( this ).dialog( "close" );
                    }
                },
                close: function() {
                    $(this).remove();
                }
            });
        });
    });
</script>

<?php $this->load->view("basic/footer"); ?>