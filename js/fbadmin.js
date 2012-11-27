window.addEvent('domready', function(){
    
    var handleResponse = function(response){
        //checkResponse(response);
        if(response.name == 'undefined'){
            var errormsg = '(' + response.code + ')' +
                response.type + '\n' +
                response.message;
                alert(errormsg);
        }else{
            $(fbAdminElement).set('text', response.name);
        }
    };
    
    var showLoader = function(){
        var adminElementHTML = $(fbAdminElement).get('html');
        adminElementHTML += '<img src="">';
    }
    
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
    
    var requestAdmin = function(){
        if($(accessTokenElement).value == ''){
            $(fbAdminElement).set('text', PLG_SYSTEM_AUTOFBOOK_JS_NOT_AUTHORIZED);
            return;
        }
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
    
    
        
    $(accessTokenElement).addEvent('change', function(){
        requestAdmin();
    });
    
    requestAdmin();

});