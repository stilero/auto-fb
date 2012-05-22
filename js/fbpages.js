window.addEvent('domready', function(){
    
    var handlePageResponse = function(response){
        if(!$defined(response.data)){
            var errormsg = '(' + response.code + ')' +
                response.type + '\n' +
                response.message;
                alert(errormsg);
        }else{
            //get the current options selectId's options
            var options = $(fbPagesElement).get('html'); 
            $each(response.data, function(value, key){
                options = options + '<option value="' + value.id + '">' + value.name + '</option>';
               //var newoption = new Option("option html", "option value");
               //$(fbPagesElement).add(newoption);
            });
            $(fbPagesElement).set('html', options);

        }
    };
    
    var pageRequest = new Request.JSONP({
        url: 'https://graph.facebook.com/me/accounts',
        method: 'post',
        data:{
            'access_token': $(accessTokenElement).value,
            'grant_type': 'manage_pages'
        },
        onRequest: function(response){
        },
        onSuccess: function(response){
            handlePageResponse(response);
        },
        onFailure: function(response){
            alert(PLG_SYSTEM_AUTOFBOOK_JS_FAILURE + response.status);
        }
    });
        
    
    
    $(accessTokenElement).addEvent('change', function(){
        pageRequest.send();
    });
    
    pageRequest.send();    

});