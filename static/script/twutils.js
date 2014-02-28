/**
 * TWUtils
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 * @copyright 2012 by Alexander Thiemann. All rights reserved.
 * @version 0.2
 */

var TWUtils = {
    controller: '',
    msgBoard_since: 0,
    siteTitle: '',
    selectedWorld: '',
    dropdown: null,
    dropdown_open:false,
    dropdown_icon_path:'',
    dropdown_langs:[]
};

$(function() {
    $("#changeLanguageBtn").click(function(event) {
        event.preventDefault();
        
        TWUtils.languageDropdown(event.pageX, event.pageY);
    });
})

TWUtils.languageDropdown = function(x, y) {
    if (TWUtils.dropdown == null) {
        TWUtils.dropdown = $("<div>").addClass('languageDropdown').addClass('tooltip').css('position', 'fixed').hide();
        
        for (var i in TWUtils.dropdown_langs) {
            var lng = TWUtils.dropdown_langs[i];
            
            TWUtils.dropdown.append($('<span>')
                .html('<img src="'+TWUtils.dropdown_icon_path+'/'+lng['id']+'.png" alt="" /> ' + lng['desc'])
                .click(
                (function(internal) {
                    return function () {
                        TWUtils.languageDropdown(0, 0); 
                        top.location.href = top.location.href
                            + (top.location.href.indexOf('?') == -1 ? '?' : '&') 
                            + 'lang=' + internal; 
                    };
                })(lng['internal'])
                ));
        }
        
        $("body").append(TWUtils.dropdown);
    }
    
    if (TWUtils.dropdown_open) {
        TWUtils.dropdown_open = false;
        TWUtils.dropdown.hide();
        return;
    }
    
    TWUtils.dropdown_open = true;
    
    TWUtils.dropdown.css('left', x).css('top', y+20).show();
};

TWUtils.updateMsgBoard = function(elementID, type, typeID, only_follow, csrf_key, csrf_val)
{
    var data = {
        'type': type,
        'typeID': typeID,
        'only_follow': only_follow,
        'since': TWUtils.msgBoard_since
    };
    
    data[csrf_key] = csrf_val;
    
    $.post(TWUtils.controller + "/msgboard", data,
    function(resp) {
        if (resp.error) {
            return;
        }
        
        if (resp.since != 0) {
            TWUtils.msgBoard_since = resp.since;
        }
        
        for (var i in resp.posts)
        {
            $('#' + elementID).append($('<div>').html(resp.posts[i]));
        }
        
        if (resp.count > 0)
        {
            $(document).attr('title', "(" + resp.count + ") " + TWUtils.siteTitle); 
            $(document).focus(function() {
                $(document).attr('title', TWUtils.siteTitle);
            });
        }
        
    }, 'json');
};

TWUtils.autoComplete = function(elementID, type)
{
    $('#' + elementID).autocomplete({
        source: TWUtils.controller + "/autocomplete/" + type,
        minLength: 2,
        select: function( event, ui ) {
            //$(this).val(ui.item.id);
            $(this).hide();
            
            var parentId = $(this).attr('id');
            
            $(this).after(
                $("<span>")
                    .attr('id', parentId + "_text")
                    .text(ui.item.value)
            );
                
            $(this).after(
                $("<span>")
                    .attr('id', parentId + "_id")
                    .text(ui.item.id)
                    .css('display', 'none')
            );
        }
    });
};