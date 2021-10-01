<?php add_action("admin_footer", "wpfep_form_builder_footer_script");
function wpfep_form_builder_footer_script()
{
    $form_id = $_GET["edit_form"];
    $form = get_post($form_id);
    $form_title = $form->post_title;
    $form_fields = $form->post_content;
    ?>
<script>
    jQuery(function($) {
    var fbTemplate = document.getElementById("fb-editor");

    var edit_form_id = '<?php echo $_GET["edit_form"]; ?>';
    var form_title = '<?php echo $form_title; ?>';

    if (edit_form_id != '' && form_title != '') {
        $('input#form-name').val(form_title);
    }


    
    /**
     * Get form fields by form id
     */
    var all_form_fields = '<?php echo trim($form_fields, '"'); ?>';

    if (!edit_form_id) {
        all_form_fields = '[ { "type": "text", "required": false, "label": "Post Title", "className": "form-control", "name": "wpfep-post-title" }, { "type": "textarea", "required": false, "label": "Post Description", "className": "form-control", "rows": 6, "name": "wpfep-post-description" }, { "type": "text", "required": false, "label": "Post Tags", "description": "Comma (,) separated tags", "className": "form-control", "name": "text-1632230841495-0" } ]';
    }

    var options = {

        /**
         * Disable Fields subtype (text, email, number)
         */
        typeUserDisabledAttrs: {
            'text': [
                'access',
                'subtype'
            ],
            'post_title': [
                'access',
                'subtype',
                // 'name'
            ],
            'post_description': [
                'access',
                'subtype',
                // 'name'
            ],
            'post_categories': [
                'access',
                'subtype',
                // 'name'
            ],
            'post_featured_image': [
                'access',
                'value',
                'subtype',
                'placeholder',
            ],
            'autocomplete': [ 'access' ],
            'button': [ 'access' ],
            'header': [ 'access' ],
            'paragraph': [ 'access' ],
            'starRating': [ 'access' ],
            'number': [ 'access' ],
            'email': [ 'access' ],
            'hidden': [ 'access' ],
            'checkbox-group': [ 'access' ],
            'radio-group': [ 'access' ],
            'date': [ 'access' ],
            'file': [ 'access' ],
            'textarea': [ 'access','subtype' ],
            'select': [ 'access' ],
        },
        typeUserAttrs: {
            text: {
                shape: {
                label: 'Class', // i18n support by passing and array eg. ['optionCount', {count: 3}]
                multiple: false, // optional, omitting generates normal <select>
                options: {
                    'red form-control': 'Red',
                    'green form-control': 'Green',
                    'blue form-control': 'Blue'
                }
                }
            },
            post_title: {
                name: {
                    label: 'ID',
                    value: '',
                }
            },
            post_categories: {
                name: {
                    label: 'ID',
                    value: '',
                },
                multiple: {
                    label: 'Allow multiple',
                    value: false,
                    type: 'checkbox',
                }
            },
            post_featured_image: {
                name: {
                    label: 'ID',
                    value: '',
                },
                multiple: {
                    label: 'Allow multiple',
                    value: false,
                    type: 'checkbox',
                }
            },
            post_description: {
                name: {
                    label: 'ID',
                    value: '',
                },
                maxLength: {
                    label: 'Max Length',
                    value: '',
                    type: 'number',
                },
                rows: {
                    label: 'Rows',
                    value: '',
                    type: 'number',
                },
            }
        },

        /**
         * Disable Fields
         */
        disableFields: [
            'autocomplete', 
            'button'
        ],
        /**
         * controll position (left/Right & sticky)
         */
        controlPosition: 'left',
        sortableControls: false,
        stickyControls: {
            enable: true,
                offset: {
                top: 20,
            }
        },
        dataType: 'json',
        formData: all_form_fields,

        /**
         * Event fire on save button
         */
        onSave: function(evt, formData) {
          
          window.sessionStorage.setItem('formData', JSON.stringify(formData));
          var form_title = $('.fbwfp-form-builder #form-name').val();
          var form_type = $('.fbwfp-form-builder #post-type').val();
          var post_status = $('.fbwfp-form-builder #post-status').val();
          var edit_id_form = '<?php echo $_GET["edit_form"]; ?>';
          var action = 'add_new';
          if (edit_id_form) {
              action = 'edit';
          }

          /**
           * Insert form via ajax request
           */
          var data = new FormData();
          data.append("form_title", form_title);
          data.append("fields", sessionStorage.getItem("formData"));
          data.append("form_type", form_type);
          data.append("post_status", post_status);
          data.append("form_action", action);
          data.append("form_id", edit_id_form);
          data.append('action', 'fbwfp_form_builder');
          jQuery.ajax({
              type: "post",
              url: fbwpf_ajax_obj.ajax_url,
              data: data,
              contentType: false,
              processData: false,
              success: function(response) {
                  if (response == 'success') {
                      $('.popup.popup--icon.side.-success').addClass('popup--visible');
                      $('.popup.popup--icon.side.-success .popup__content p.message').html('Form Created Successfully');
                  }
              }
          });
      },

      templates: {
            post_title: function(fieldData) {
                var placeholder ='';
                var value ='';
                if (fieldData.hasOwnProperty('placeholder')) {
                    placeholder = fieldData.placeholder;
                }
                if (fieldData.hasOwnProperty('value')) {
                    value = fieldData.value;
                }
                return {
                    field: '<span id="'+fieldData.name+'">',
                    onRender: function() {
                    $(document.getElementById(fieldData.name)).html('<input class="' + fieldData.className + '" name="wpfep-post-title" placeholder="' + placeholder + '" value="' + value + '" type="text" id="'+fieldData.name+'">');
                    }
                };
            },
            post_featured_image: function(fieldData) {
             
                return {
                    field: '<span id="'+fieldData.name+'">',
                    onRender: function() {
                    $(document.getElementById(fieldData.name)).html('<input class="form-control ' + fieldData.className + '" name="wpfep-post-featured-image"  type="file" id="'+fieldData.name+'">');
                    }
                };
            },
            post_tags: function(fieldData) {
                var placeholder ='';
                var value ='';
                if (fieldData.hasOwnProperty('placeholder')) {
                    placeholder = fieldData.placeholder;
                }
                if (fieldData.hasOwnProperty('value')) {
                    value = fieldData.value;
                }
                return {
                    field: '<span id="'+fieldData.name+'">',
                    onRender: function() {
                    $(document.getElementById(fieldData.name)).html('<input class="' + fieldData.className + '" name="wpfep-post-tags" placeholder="' + placeholder + '" value="' + value + '" type="text" id="'+fieldData.name+'">');
                    }
                };
            },
            post_categories: function(fieldData) {
                var placeholder ='';
                var value ='';
                if (fieldData.hasOwnProperty('placeholder')) {
                    placeholder = '<option disabled="null" selected="null" value="">'+fieldData.placeholder+'</option>';
                }
                if (fieldData.hasOwnProperty('value')) {
                    value = fieldData.value;
                }
                <?php
                $args = [
                    "hide_empty" => false,
                ];
                $categories = get_categories($args);
                $options = "";
                $count = 0;
                foreach ($categories as $category) {
                    $options .=
                        '<option value="' .
                        $category->term_id .
                        '">' .
                        $category->name .
                        "</option>";
                    $count++;
                }
                ?>
                placeholder += ' <?php echo $options; ?> ';
                var html = '<select name="wpfep-post-categories" class="' + fieldData.className + '">';
                html += placeholder;
                html += '</select>';
                return {
                    field: '<span id="'+fieldData.name+'">',
                    onRender: function() {
                    $(document.getElementById(fieldData.name)).html(html);
                    }
                };
            },
            post_description: function(fieldData) {
                var placeholder ='';
                var value ='';
                if (fieldData.hasOwnProperty('placeholder')) {
                    placeholder = fieldData.placeholder;
                }
                if (fieldData.hasOwnProperty('value')) {
                    value = fieldData.value;
                }
                return {
                    field: '<span id="'+fieldData.name+'">',
                    onRender: function() {
                    $(document.getElementById(fieldData.name)).html('<textarea class="' + fieldData.className + '" max-length="' + fieldData.maxLength + '" rows="' + fieldData.rows + '" name="wpfep-post-description" placeholder="' + placeholder + '" id="'+fieldData.name+'">' + value + '</textarea>');
                    }
                };
            }
        },

        /**
        * Creating new fields widgets
        */
        fields: [
            {
                label: 'Post Title',
                value:'',
                className:'form-control',
                placeholder: 'Post Title',
                attrs: {
                    type: 'post_title',
                },
                icon: '<i class="formbuilder-icon-text input-control input-control-9 ui-sortable-handle"></i>'
            }, 
            {
                label: 'Post Description',
                value:'',
                className:'form-control',
                placeholder: 'Post Description',
                attrs: {
                    type: 'post_description',
                },
                icon: '<i class="formbuilder-icon-textarea input-control input-control-13 ui-sortable-handle"></i>'
            }, 
            
            {   
                label: 'Post Tags',
                className:'form-control',
                attrs: {
                    type: 'post_tags',
                },
                icon: '<i class="formbuilder-icon-text input-control input-control-13 ui-sortable-handle"></i>'
            },

            {   
                label: 'Featured Image',
                className:'form-control',
                attrs: {
                    type: 'post_featured_image',
                },
                icon: '<i class="formbuilder-icon-file input-control input-control-10 ui-sortable-handle"></i>'
            },
            
            
            {   
                label: 'Post Categories',
                className:'form-control',
                attrs: {
                    type: 'post_categories',
                },
                icon: '<i class="formbuilder-icon-select input-control input-control-5 ui-sortable-handle"></i>'
            },
            
            
        ],

        defaultFields: [
            {
                className: 'form-control',
                label: 'Default Field',
                placeholder: 'Enter your default field value',
                name: 'default-field-1',
                type: 'text',
            },
        ],
        controlOrder: [
        'post_title',
        'post_description',
        'post_tags',
        'post_categories',
        'post_featured_image',
        'text',
        'textarea'
        ],
        persistDefaultFields: false,
    };
     
    
    $(fbTemplate).formBuilder(options);
    
});

</script>

<?php
} ?>
