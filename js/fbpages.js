/**
* MooTools script for retrieving pages of the admin
*
* @version  1.2
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-maj-20 Stilero Webdesign http://www.stilero.com
* @category MooToolsScript
* @license    GPLv2
*/
window.addEvent('domready', function(){
    var pageID = $(fbPageIdElement).value;
    var defaultSelectList = $(fbPagesElement).get('html');  
    var pages = new Array();
    var setPageSelection = function(){
        if(pageID == ''){
            pageID = $(fbPagesElement).get('value');
        }
        $(fbPageIdElement).value = pageID;
    };
    
    var handlePageResponse = function(response){
        if(response.data == 'undefined'){
            var errormsg = '(' + response.code + ')' +
                response.type + '\n' +
                response.message;
                alert(errormsg);
        }else{
            //get the current options selectId's options
            //evalResponse(response, 'data');
            var options = $(fbPagesElement).get('html');
            //$.each(response.data, function(value, key){
//                response.data.each(function(value, key){
//                    alert(value.id);
//                });
            response.data.each(function(value, key){
                var selected = (value.id == pageID) ? ' selected="selected" ' : '';
                if(value.category != 'Application'){
                    //Object.append(pages, {'id': value.id, 'access_token': value.access_token});
                    pages[value.id] = value.access_token;
                    options = options + '<option value="' + value.id + '"'+selected+'>' + value.name + '</option>';
                }
               //var newoption = new Option("option html", "option value");
               //$(fbPagesElement).add(newoption);
            });
            $(fbPagesElement).set('html', options);
            $(fbPagesElement).setStyle('display', 'block');
            $('jform_params_fb_pages_chzn').setStyle('display', 'none');


        }
    };
    
    var evalResponse = function(response, context){
        var resAsText = 'response from ' + context + ': ';
        $each(response, function(value, key){
            if(typeOf(value) == 'object'){
                $each(value, function(objVal, objKey){
                    if(typeOf(objVal) == 'object'){
                        $each(objVal, function(obj2Val, obj2Key){
                            resAsText += 'Obj2[ ' +obj2Key + '] = '+ obj2Val + ' ';
                        });
                    }
                    resAsText += 'Obj[ ' +objKey + '] = '+ objVal + ' ';
                });
            }
            resAsText += 'resp[ ' +key+']='+ value + ' ';
        });
        alert(resAsText);
    };
    
    /**
     * AJAX method for retrieving pages
     */
    var requestPages = function(){
        if($(accessTokenElement).value == ''){
            $(fbPagesElement).set('html', defaultSelectList);
            return;
        }
        // FOR DEBUGGING
        //alert('https://graph.facebook.com/me/accounts?' + 'access_token=' + $(accessTokenElement).value + '&grant_type=manage_pages' );
        var pageRequest = new Request.JSONP({
            url: 'https://graph.facebook.com/me/accounts',
            method: 'post',
            data:{
                'access_token': $(accessTokenElement).value,
                'grant_type': 'manage_pages'
            },
            onRequest: function(){
                $(fbPagesElement).set('class', 'readonly ajaxloader');
            },
            onSuccess: function(response){
                handlePageResponse(response);
            },
            onFailure: function(response){
                alert(PLG_SYSTEM_AUTOFBOOK_JS_FAILURE + response.status);
            },
            onComplete: function(){
                $(fbPagesElement).set('class', 'readonly');
            }
        });
        pageRequest.cancel().send();
    };
    
    /**
     * Event listeners
     */
    $(fbPagesElement).addEvent('change', function(){
        pageID = $(fbPagesElement).get('value');
        $(fbPageIdElement).value = pageID;
        $(fbPageAuthTokenElement).value = pages[pageID];
    });
    
    $(accessTokenElement).addEvent('change', function(){
        requestPages();
    });
    
    requestPages();    

});