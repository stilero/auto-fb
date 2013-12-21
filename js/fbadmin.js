/**
* MooTools script for retrieving info about the authorised admin
*
* @version  1.2
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-maj-20 Stilero Webdesign http://www.stilero.com
* @category MooToolsScript
* @license    GPLv2
*/
window.addEvent('domready', function(){
    
    /**
     * Method for handling the response and setting the admin name
     */
    var handleResponse = function(response){
        if(response.name == 'undefined'){
            var errormsg = '(' + response.code + ')' +
                response.type + '\n' +
                response.message;
                alert(errormsg);
        }else{
            $(fbAdminElement).set('text', response.name);
            $(fbAdminIDElement).set('text', response.id);
            var options = $(fbPagesElement).get('html');
            //options = options + '<option value="' + response.id + '">' + response.name + ' Timeline</option>';
            options = options + '<option value="">' + response.name + ' Timeline</option>';
            $(fbPagesElement).set('html', options);
        }
    };
    
    /**
     * Method for displaing a loader animation
     */
    var showLoader = function(){
        var adminElementHTML = $(fbAdminElement).get('html');
        adminElementHTML += '<img src="">';
    }
    
    /**
     * Method for debugging the response
     */
    var evalResponse = function(response){
        var resAsText = 'response: ';
        $each(response, function(value, key){
            if(typeOf(value) == 'object'){
                $each(value, function(objVal, objKey){
                    resAsText += 'Obj[ ' +objKey + '] = '+ objVal + ' ';
                });
            }
            resAsText += 'resp[ ' +key+']='+ value + ' ';
        });
        alert(resAsText);
    };
    
    /**
     * AJAX method for retrieving info about the admin
     */
    var requestAdmin = function(){
        if($(accessTokenElement).value == ''){
            $(fbAdminElement).set('text', PLG_SYSTEM_AUTOFBOOK_JS_NOT_AUTHORIZED);
            return;
        }
        //FOR DEBUGGING
        //alert('https://graph.facebook.com/me?access_token=' + $(accessTokenElement).value);
        var adminRequest = new Request.JSONP({
            url: 'https://graph.facebook.com/me',
            method: 'post',
            data:{
                'access_token': $(accessTokenElement).value
            },
            onRequest: function(){
                $(fbAdminElement).set('class', 'readonly ajaxloader');
            },
            onSuccess: function(response){
                handleResponse(response);
            },
            onFailure: function(response){
               alert(PLG_SYSTEM_AUTOFBOOK_JS_FAILURE + response.status);
            },
            onComplete: function(){
                $(fbAdminElement).set('class', 'readonly');
            }
        });
        adminRequest.cancel().send();
    };
    
    /**
     * Event listeners
     */    
    $(accessTokenElement).addEvent('change', function(){
        requestAdmin();
    });
    
    requestAdmin();

});