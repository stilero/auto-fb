window.addEvent('domready', function(){
    var pageID = $(fbPageIdElement).value;
    var defaultSelectList = $(fbPagesElement).get('html');  
    
    var setPageSelection = function(){
        if(pageID == ''){
            pageID = $(fbPagesElement).get('value');
        }
        
        $(fbPageIdElement).value = pageID;
    };
    
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
                var selected = (value.id == pageID) ? ' selected="selected" ' : '';
                if(value.category != 'Application'){
                    options = options + '<option value="' + value.id + '"'+selected+'>' + value.name + '</option>';
                }
               //var newoption = new Option("option html", "option value");
               //$(fbPagesElement).add(newoption);
            });
            $(fbPagesElement).set('html', options);

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
    var requestPages = function(){
        if($(accessTokenElement).value == ''){
            $(fbPagesElement).set('html', defaultSelectList);
            return;
        }
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
    
    
        
    $(fbPagesElement).addEvent('change', function(){
        pageID = $(fbPagesElement).get('value');
        $(fbPageIdElement).value = pageID;
    });
    
    $(accessTokenElement).addEvent('change', function(){
        requestPages();
    });
    
    requestPages();    

});