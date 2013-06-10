if(typeof window.jQQ !== 'object'){
(function() {
    var callbacks = [],jq;
    function loadScript(url, callback){
        var script = document.createElement("script")
        script.type = "text/javascript";
        if (typeof script.readyState === 'undefined'){  
            script.onload = function(){
                callback();
            };            
        } else { // IE LAST!
            script.onreadystatechange = function(){
                if (script.readyState === "loaded" || script.readyState === "complete"){
                    script.onreadystatechange = null;
                    callback();
                }
            };            
        };
        script.src = url;
        document.getElementsByTagName("head")[0].appendChild(script);
        return script;
    };
    function validateCallback(callback) {
        if(typeof callback === 'undefined') throw "Cannot validate callback: undefined";
        if(callback && callback.length<1) throw "Callback missing at least 1 placeholder argument";
        return callback;
    };
    function fillArray(data,qty) {
        var array  = [];
        for(var i=qty;i>0;i--) array.push(data);
        return array;
    };
    window.jQQ = {
      isReady: false,
      isolate: function() {
          var callback = validateCallback(arguments[0]);
          if( !window.jQQ.isReady )
		  {
			return callbacks.push( callback );
		  }
          return callback.apply( this, fillArray( jq, callback.length ) );
      },
      setup: function(url) {
          // wait for document to load...
          if(!document.body) return window.onload = function(){ window.jQQ.setup(url) };
          loadScript( url , function() {
              window.jQQ.isReady = true;
              // this stores the new version and gives back the old one, completely.              
              jq = jQuery.noConflict(true);
              callbacks.forEach(window.jQQ.isolate);
              delete(callbacks);
          });
      }
    };
})(window.jQuery,window.$)
}
	var requireJS = function (scripts, scriptIndex, callback) {
		var length = scripts.length;
		if (scriptIndex == length) 
		{
			callback();
			return;
		}
		var script;
		script = document.createElement("script");
		script.async = false;
		script.type = "text/javascript";
		script.src = scripts[scriptIndex];
		if (typeof script.readyState === 'undefined'){  
            script.onload = function(){
                requireJS(scripts, scriptIndex+1, callback);
            };            
        } 
		else { // IE LAST!
            script.onreadystatechange = function(){
                if (script.readyState === "loaded" || script.readyState === "complete"){
                    script.onreadystatechange = null;
                    requireJS(scripts, scriptIndex+1, callback);
                }
            };            
        };
		document.getElementsByTagName("head")[0].appendChild(script);
	};
	var requireCSS = function (scripts, scriptIndex, callback) {
		var length = scripts.length;
		if (scriptIndex == length) 
		{
			callback();
			return;
		}
		var script;
		script = document.createElement("link");
		script.rel = scripts[scriptIndex].rel;
		script.type = scripts[scriptIndex].type;
		script.href = scripts[scriptIndex].href;
		script.async = false;
		if (typeof script.readyState === 'undefined'){  
            script.onload = function(){
                requireCSS(scripts, scriptIndex+1, callback);
            };            
        } 
		else { // IE LAST!
            script.onreadystatechange = function(){
                if (script.readyState === "loaded" || script.readyState === "complete"){
                    script.onreadystatechange = null;
                    requireCSS(scripts, scriptIndex+1, callback);
                }
            };            
        };
		document.getElementsByTagName("head")[0].appendChild(script);
	};
	var getContent = function()
	{
		var url = formContentUrl;
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.async = false;
		script.src = url;
		document.getElementsByTagName("head")[0].appendChild(script);
	};
	function renderFormCallback(respData)
	{
		var jsScriptFiles = respData.head.js;
		var cssFiles = respData.head.css;
		var styleTags = respData.head.style;
		jQQ.setup(jsScriptFiles[0]);
		requireJS(jsScriptFiles, 1, function(){
			requireCSS(cssFiles, 0, function(){
                var element = document.getElementById("zurmoExternalWebForm");
                var e = document.createElement('div');
                e.innerHTML = respData.body.html;
				var bodyJs = respData.body.js;
                while(e.firstChild)
                {
                    element.appendChild(e.firstChild);
                }
				for (var bodyJsIndex in bodyJs)
				{
					var jsScriptElement = document.createElement("script");
					jsScriptElement.type = "text/javascript";
					if (bodyJs[bodyJsIndex].type == 'file')
					{
						jsScriptElement.src = bodyJs[bodyJsIndex].src;
					}
					if (bodyJs[bodyJsIndex].type == 'codeBlock')
					{
						jsScriptElement.innerHTML = "jQQ.isolate (function(jQuery,$) { " + bodyJs[bodyJsIndex].body + " });";
					}
					jsScriptElement.async = false;
                    element.appendChild(jsScriptElement);
				}
				for (var styleTagIndex in styleTags)
				{
					var styleTagElement = document.createElement("style");
					styleTagElement.type = "text/css";
					styleTagElement.innerHTML = styleTags[styleTagIndex];
					styleTagElement.async = false;
					document.getElementsByTagName("head")[0].appendChild(styleTagElement);
				}
			});
		});
	}
	getContent();