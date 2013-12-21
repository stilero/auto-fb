/**
* MooTools script for authorising with Facebook
*
* @version  1.2
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-maj-20 Stilero Webdesign http://www.stilero.com
* @category MooToolsScript
* @license    GPLv2
*/
window.addEvent('domready', function(){
    var appID = $(appIdElement).value;
    var appSecret = $(appSecretElement).value;
    var redirectURI = catcherURI;
    var authCode = $(authCodeElement).value;
    var authElmnt = $(authorizeElement).get('html');
    var loader = '<span class="ajaxloader-blue"></span>';
        
    /**
     * Method for handling the looks and function of the Connect button
     */    
    var setButtonHref = function(){
        var link = 'https://www.facebook.com/dialog/oauth' + 
            '?client_id=' + appID +
            '&redirect_uri=' + redirectURI +
            '&scope=publish_stream,share_item,offline_access,manage_pages,user_groups,user_status';
            $(authorizeElement).href = link;
        if($(accessTokenElement).value == ''){
            $(authorizeElement).innerHTML = 'Connect to FB';
            $(authorizeElement).set('class', 'fbconnect');
        }else{
            $(authorizeElement).innerHTML = 'Remove FB Connection';
            $(authorizeElement).set('class', 'fbdisconnect');
        }
    };
    
    /**
     * Method for clearing and resetting authorisation
     */
    var clearAuthorization = function(){
        $(authCodeElement).value = '';
        $(fbPageIdElement).value = '';
        $(accessTokenElement).value = '';
        $(authorizeElement).innerHTML = 'Connect to FB';
        setButtonHref();
        $(accessTokenElement).fireEvent('change');
    };
    
    /**
     * Method for displaying the connect button when all fileds are entered
     */
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
    
    /**
     * Method for showing the loader animation
     */
    var showLoader = function(){
        $(authorizeElement).set('html', authElmnt + loader);   
    };
    
    /**
     * Method for hiding the loader animation
     */
    var hideLoader = function(){
        $(authorizeElement).set('html', authElmnt);   
    };
    
    var postAuthorization = function(){
        $(authCodeElement).value = '';
        //alert(PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS);
        $(accessTokenElement).fireEvent('change');
        setButtonHref();
    }
    
    var handleResponse = function(response){
        if(response.access_token == 'undefined'){
            var errormsg = '(' + response.code + ')' +
                response.type + '\n' +
                response.message;
                alert(errormsg);
        }else{
            //$(accessTokenElement).value = response.access_token;
            //postAuthorization();
            
            $(accessTokenElement).value = response.access_token;
            postAuthorization();
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
            onRequest: function(){
            },
            onSuccess: function(response){
                handleResponse(response);
            },
            onFailure: function(response){
                alert(PLG_SYSTEM_AUTOFBOOK_JS_FAILURE + response.status);
            },
            onComplete: function(){
                $(authorizeElement).set('html', authElmnt);
            }
        });
        
        myRequest.cancel().send();    
    };
    
    displayButton();
    
    /**
     * Event Listeners
     */
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
        
    $(authorizeElement).addEvent('click', function(e){
        if($(accessTokenElement).value != ''){
            e.preventDefault();
            clearAuthorization();
        }else{
            showLoader();
        }
    });
});