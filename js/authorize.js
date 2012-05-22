window.addEvent('domready', function(){
    var appID = $(appIdElement).value;
    var appSecret = $(appSecretElement).value;
    var redirectURI = catcherURI;
    var authCode = $(authCodeElement).value;

        
    var setButtonHref = function(){
        var link = 'https://www.facebook.com/dialog/oauth' + 
            '?client_id=' + appID +
            '&redirect_uri=' + redirectURI +
            '&scope=publish_stream,share_item,offline_access,manage_pages';
            $( authorizeElement).href = link;
        if($(accessTokenElement).value == ''){
            $( authorizeElement).set('class', 'fb-connect');
            $( authorizeElement).text = 'Connect to FB';
        }else{
            $( authorizeElement).set('class', 'fb-connect-remove');
            $( authorizeElement).text = 'Remove FB Connection';
        }
    };
    
    $( authorizeElement).addEvent('click', function(e){
        if($(accessTokenElement).value != ''){
            e.preventDefault();
            $(authCodeElement).value = '';
            $(accessTokenElement).value = '';
            setButtonHref();
        }
    });
    
    var displayButton = function(){
        setButtonHref();
        if(appID == '' || appSecret == '' || redirectURI == ''){
            $(authorizeElement).setStyle( 'display', 'none');
        }else{
             //setButtonHref();
            $(authorizeElement).setStyle( 'opacity', '0');
            $(authorizeElement).setStyle( 'display', 'block');
            $(authorizeElement).fade('in');
        }
    };
    
    var handleResponse = function(response){
        if(!$defined(response.access_token)){
            var errormsg = '(' + response.code + ')' +
                response.type + '\n' +
                response.message;
                alert(errormsg);
        }else{
            $(accessTokenElement).value = response.access_token;
            $(authCodeElement).value = '';        
            alert(PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS);
        }
    };
    
    var requestAccessToken = function(){
        authCode = $(authCodeElement).value;
        var reqUrl = helpersURI + 'authorizer.php';
        var myRequest = new Request.JSON({
            url: reqUrl,
            method: 'post',
            data:{'client_id': appID,
                'client_secret': appSecret,
                'code': authCode,
                'redirect_uri': catcherURI
            },
            onSuccess: function(response){
                handleResponse(response);
            },
            onFailure: function(response){
                alert(PLG_SYSTEM_AUTOFBOOK_JS_FAILURE + response.status);
            }
        });
        
        myRequest.send();    
    };
    
    displayButton();

    $(appIdElement).addEvent('keyup', function(){
        appID = $(appIdElement).value;
        displayButton();
    });
    
    $(appSecretElement).addEvent('keyup', function(){
        appSecret = $(appSecretElement).value;
        displayButton();
    });
    
    $(authCodeElement).addEvent('change', function(){
        authCode = $(authCodeElement).value;
        requestAccessToken();
    });
        
});