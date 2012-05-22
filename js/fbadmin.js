window.addEvent('domready', function(){
    
    var handleResponse = function(response){
        if(!$defined(response.name)){
            var errormsg = '(' + response.code + ')' +
                response.type + '\n' +
                response.message;
                //alert(errormsg);
        }else{
            $(fbAdminElement).set('text', response.name);
        }
    };
    
    var myRequest = new Request.JSONP({
        url: 'https://graph.facebook.com/me',
        method: 'post',
        data:{
            'access_token': $(accessTokenElement).value
        },
        onSuccess: function(response){
            handleResponse(response);
        },
        onFailure: function(response){
            //alert(PLG_SYSTEM_AUTOFBOOK_JS_FAILURE + response.status);
        }
    });
        
    
    
    $(accessTokenElement).addEvent('change', function(){
        myRequest.send();
    });
    
    myRequest.send();    

});