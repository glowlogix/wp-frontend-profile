
var el = wp.element.createElement;

wp.blocks.registerBlockType('wpfep/frontend-profile', {

   title: 'Frontend Profile', // Block name visible to user

   icon: 'admin-users', // Toolbar icon can be either using WP Dashicons or custom SVG

   category: 'widgets', // Under which category the block would appear

   attributes: { // The data this block will be storing

      type: { type: 'string', default: '[wpfep-register]' }, //  box type for loading the appropriate CSS class. Default class is 'default'.

      title: { type: 'string' }, //  box title in h4 tag

      content: { type: 'array', source: 'children', selector: 'p' } ///  box content in p tag

   },

 edit: function(props) {
   // How our block renders in the editor in edit mode

  

   function updateContent( newdata ) {
      props.setAttributes( { content: newdata } );
   }

   function updateType( event ) {
      props.setAttributes( { type: event.target.value } );
   }

   return el( 'div',
      {
         className: 'wpfep-guten-block'
      },
        el("h4", {value: "WP Frontend Profile" }, "WPFEP Shortcodes\n"),
      el(
         'select',
         {
            onChange: updateType,
            value: props.attributes.type,
         },
         el("option", {value: "[wpfep-register]" }, "Register"),
         el("option", {value: "[wpfep-login]" }, "Login"),
         el("option", {value: "[wpfep-profile]" }, "Profile")
      ),
      
      el(
         wp.editor.RichText,
         {
            tagName: 'p',
            onChange: updateContent,
            value: props.attributes.type,
            placeholder: 'Enter description here...'
         }
      )
   ); // End return

},  // End edit()

save: function(props) {
   // How our block renders on the frontend

   return el( 'div',
      {
         className: 'wpfep-guten-block'
      },
    
      el( wp.editor.RichText.Content, {
         tagName: 'p',
         value: props.attributes.type
      })

   ); // End return

} // End save()
});