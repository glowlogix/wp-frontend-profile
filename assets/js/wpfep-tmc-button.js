jQuery(document).ready(function($) {
    tinymce.create('tinymce.plugins.wpfep_button', {
        init : function(editor, url) {
                var menuItem = [];
                 var ds_img = wpfep_assets_url +'/assets/icon/wpfep.png';
                $.each( wpfep_shortcode, function( i, val ){
                    var tempObj = {
                            text : val.title,
                            onclick: function() {
                                editor.insertContent(val.content)
                            }
                        };
                        
                    menuItem.push( tempObj );
                } );
                // Register buttons - trigger above command when clickeditor
                editor.addButton('wpfep_button', {
                    title : 'Frontend profile shortcodes', 
                    classes : 'wpfep-ss',
                    type  : 'menubutton',
                    menu  : menuItem,
                    style : ' background-size : 22px; background-repeat : no-repeat; background-image: url( '+ ds_img +' );'
                });
        },   
    });
    // Register our TinyMCE plugin
    tinymce.PluginManager.add('wpfep_button', tinymce.plugins.wpfep_button);
});