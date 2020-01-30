require.config({ 
	paths: { 
		'jquery':js_url+'/jquery-1.10.2.min' ,
		'layer':js_url+'/layer/layer',
		'gboy':tpl_js+'/gboy',
	},  
	  map: {
                '*': {
                    'css': 'css.min'
                }
            },
      shim: {
                layer: {
                    deps: [
                        'css!'+js_url+'/layer/theme/default/layer'
                    ]
                },
				gboy: {
                    deps: [
                        'css!'+tpl_css+'/gboy'
                    ]
                }
            }
}); 

 require(['jquery','layer','gboy'],function ($,layer,gboy) { 
		$("[data-event='submit']").on('click', function () {
		
			var obj_form = $(this).parents('form');
			
			gboy.send(obj_form);
			return false;
			
		});
});


