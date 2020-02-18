var wpfep_re_capabilities_group = [];
var wpfep_re_new_capabilities = {};
var wpfep_re_current_role_capabilities = jQuery.extend( {}, wpfep_roles_editor_data.current_role_capabilities );
var wpfep_re_unsaved_capabilities = {};
var wpfep_re_capabilities_to_delete = {};

jQuery( document ).ready( function() {
    // Disable Enter key
    jQuery( window ).keydown( function( e ) {

        if( typeof e.target.id !== "undefined" && e.target.id === 'post-search-input' )
            return true;

        if( e.keyCode == 13 ) {
            event.preventDefault();
            return false;
        }
    } );

    // Disable the role title field when editing a role
    if( wpfep_roles_editor_data.current_screen_action != 'add' ) {
        jQuery( '.post-type-wpfep-roles-editor input#title' ).attr( 'disabled', 'disabled' );
    }

    var table_roles = jQuery( '.post-type-wpfep-roles-editor .wp-list-table.posts tr .row-actions' );
    if( jQuery( table_roles ).find( '.default_role' ) ) {
        jQuery( '<span class="table-role-info"> — ' + wpfep_roles_editor_data.default_role_text + '</span>' ).insertAfter( jQuery( table_roles ).find( '.default_role' ).parent().parent().find( 'strong .row-title' ) );
    }
    if( jQuery( table_roles ).find( '.delete_notify.your_role' ) ) {
        jQuery( '<span class="table-role-info"> — ' + wpfep_roles_editor_data.your_role_text + '</span>' ).insertAfter( jQuery( table_roles ).find( '.delete_notify.your_role' ).parent().parent().find( 'strong .row-title' ) );
    }

    // Dynamically change value of the Role Slug field
    jQuery( '.post-type-wpfep-roles-editor #titlewrap' ).find( '#title' ).change( function() {
        if( ! jQuery( '.post-type-wpfep-roles-editor #wpfep-role-slug' ).val() ) {
            jQuery( '.post-type-wpfep-roles-editor #wpfep-role-slug' ).val( jQuery( this ).val().toLowerCase() );
        }
    } );

    // Create an object with grouped capabilities for the Add Capability select2
    var counter = 1;
    jQuery.each( wpfep_roles_editor_data.capabilities, function( key, value ) {
        var capabilities_single_group = {};
        if( key != 'post_types' ) {
            capabilities_single_group = wpfep_re_create_capabilities_group( key, value, counter );
            wpfep_re_capabilities_group.push( capabilities_single_group );
            counter++;
        } else if( key == 'post_types' ) {
            jQuery.each( value, function( key, value ) {
                capabilities_single_group = wpfep_re_create_capabilities_group( key, value, counter );
                wpfep_re_capabilities_group.push( capabilities_single_group );
                counter++;
            } );
        }
    } );


    // Display the current role capabilities (on single role page)
    wpfep_re_display_capabilities( 'all' );

    // Check for already added capabilities and disable them before select2 initialization
    wpfep_re_disable_select_capabilities( wpfep_re_capabilities_group, wpfep_roles_editor_data.current_role_capabilities, 'add' );

    if( wpfep_re_getParameterByName( 'wpfep_re_clone' ) ) {
        var data = {
            'action'    : 'get_role_capabilities',
            'security'  : jQuery( '.post-type-wpfep-roles-editor #wpfep-re-ajax-nonce' ).val(),
            'role'      : wpfep_re_getParameterByName( 'wpfep_re_clone' )
        };

        jQuery( '.post-type-wpfep-roles-editor .wpfep-role-edit-no-cap' ).remove();

        jQuery.post( wpfep_roles_editor_data.ajaxUrl, data, function( response ) {
            if( response != 'no_caps' ) {
                jQuery( '.post-type-wpfep-roles-editor .wpfep-re-spinner-container' ).hide();
                wpfep_re_current_role_capabilities = jQuery.extend( wpfep_re_current_role_capabilities, JSON.parse( response ) );
                wpfep_re_display_capabilities( 'all' );
                wpfep_re_disable_select_capabilities( wpfep_re_capabilities_group, wpfep_re_current_role_capabilities, 'add' );
            } else {
                jQuery( '.wpfep-re-spinner-container' ).hide();
                wpfep_re_no_capabilities_found();
            }
        } );
    }
        // Delete a capability
    jQuery( '.post-type-wpfep-roles-editor #wpfep-role-edit-table' ).on( 'click', 'a.wpfep-delete-capability-link', function() {
        if( ( wpfep_roles_editor_data.current_user_role && jQuery.inArray( jQuery( this ).closest( 'span.wpfep-delete-capability' ).siblings( 'span.wpfep-capability' ).text(), wpfep_roles_editor_data.admin_capabilities ) === -1 ) || ! wpfep_roles_editor_data.current_user_role ) {
            jQuery( this ).closest( 'div.wpfep-role-edit-table-entry' ).remove();

            var deleted_capability = {};
            deleted_capability[jQuery( this ).closest( 'span.wpfep-delete-capability' ).siblings( 'span.wpfep-capability' ).text()] = jQuery( this ).closest( 'span.wpfep-delete-capability' ).siblings( 'span.wpfep-capability' ).text();
            wpfep_re_capabilities_to_delete[jQuery( this ).closest( 'span.wpfep-delete-capability' ).siblings( 'span.wpfep-capability' ).text()] = jQuery( this ).closest( 'span.wpfep-delete-capability' ).siblings( 'span.wpfep-capability' ).text();

            delete wpfep_re_current_role_capabilities[jQuery( this ).closest( 'span.wpfep-delete-capability' ).siblings( 'span.wpfep-capability' ).text()];
            delete wpfep_re_new_capabilities[jQuery( this ).closest( 'span.wpfep-delete-capability' ).siblings( 'span.wpfep-capability' ).text()];

            if( jQuery( '.wwpfep-add-new-cap-input' ).is( ':visible' ) ) {
                wpfep_re_change_select_to_input();
            }

            wpfep_re_disable_select_capabilities( wpfep_re_capabilities_group, deleted_capability, 'delete' );

            if( jQuery( '.wpfep-role-edit-table-entry' ).length < 1 ) {
                wpfep_re_no_capabilities_found();
            }

            wpfep_re_number_of_capabilities();
        }
    } );
    // Change between select2 with all existing capabilities and input to add a new capability
    jQuery( '.post-type-wpfep-roles-editor a.wpfep-add-new-cap-link' ).click( function() {
        wpfep_re_change_select_to_input();
    } );

    jQuery( '.post-type-wpfep-roles-editor .wpfep-role-editor-tab' ).click( function() {
        wpfep_re_tabs_handler( jQuery( this ) );
    } );

    wpfep_re_form_submit();

    wpfep_re_number_of_capabilities();

    // Display number of users for current role
    if( wpfep_roles_editor_data.current_role_users_count !== null ) {
        jQuery( '.post-type-wpfep-roles-editor .misc-pub-section.misc-pub-section-users span' ).find( 'strong' ).text( wpfep_roles_editor_data.current_role_users_count );
    }

    // Check if role has a title or return an error if not
    jQuery( 'body' ).on( 'submit.edit-post', '#post', function() {
        if( jQuery( '.post-type-wpfep-roles-editor #title' ).val().replace( / /g, '' ).length === 0 ) {
            window.alert( wpfep_roles_editor_data.role_name_required_error_text );
            jQuery( '.post-type-wpfep-roles-editor #major-publishing-actions .spinner' ).hide();
            jQuery( '.post-type-wpfep-roles-editor #major-publishing-actions' ).find( ':button, :submit, a.submitdelete, #post-preview' ).removeClass( 'disabled' );
            jQuery( '.post-type-wpfep-roles-editor #title' ).focus();

            wpfep_re_form_submit();

            return false;
        } else {
            jQuery( '.post-type-wpfep-roles-editor #major-publishing-actions .spinner' ).show();
        }
    } );
} );

function wpfep_re_form_submit() {
    jQuery( '.post-type-wpfep-roles-editor #publishing-action #publish' ).unbind( 'click' ).one( 'click', function( e ) {
        e.preventDefault();
        jQuery( this ).addClass( 'disabled' );
        jQuery( this ).siblings( '.spinner' ).addClass( 'is-active' );
        wpfep_re_update_role_capabilities();
    } );
}

function wpfep_re_no_capabilities_found() {
    jQuery( '.post-type-wpfep-roles-editor #wpfep-role-edit-table' ).find( '#wpfep-role-edit-caps-clear' ).after(
        '<div class="wpfep-role-edit-table-entry wpfep-role-edit-no-cap">' +
            '<span class="wpfep-capability wpfep-role-edit-not-capability">' + wpfep_roles_editor_data.no_capabilities_found_text + '</span>' +
        '</div>'
    );
}

function wpfep_re_number_of_capabilities() {
    var count = 0;
    var i;

    for( i in wpfep_re_current_role_capabilities ) {
        if( wpfep_re_current_role_capabilities.hasOwnProperty( i ) ) {
            count++;
        }
    }

    jQuery( '.post-type-wpfep-roles-editor .misc-pub-section.misc-pub-section-capabilities span' ).find( 'strong' ).text( count );
}

function wpfep_re_tabs_handler( tab ) {
    wpfep_re_display_capabilities( jQuery( tab ).data( 'wpfep-re-tab' ) );

    jQuery( '.post-type-wpfep-roles-editor .wpfep-role-editor-tab-title.wpfep-role-editor-tab-active' ).removeClass( 'wpfep-role-editor-tab-active' );
    jQuery( tab ).closest( '.wpfep-role-editor-tab-title' ).addClass( 'wpfep-role-editor-tab-active' );
}

function wpfep_re_disable_select_capabilities( wpfep_re_capabilities_group, capabilities, action ) {
    if( capabilities != null ) {
        jQuery.each( wpfep_re_capabilities_group, function( key, value ) {
            jQuery.each( value['children'], function( key, value ) {
                if( value['text'] in capabilities ) {
                    if( action == 'add' ) {
                        value['disabled'] = true;
                    } else if( action == 'delete' ) {
                        value['disabled'] = false;
                    }
                }
            } );
        } );
    }

    wpfep_re_initialize_select2( wpfep_re_capabilities_group );
}

function wpfep_re_initialize_select2( wpfep_re_capabilities_group ) {
    var capabilities_select = jQuery( '.wpfep-capabilities-select' );

    capabilities_select.empty();
    capabilities_select.select2( {
        placeholder: wpfep_roles_editor_data.select2_placeholder_text,
        allowClear: true,
        data: wpfep_re_capabilities_group,
        templateResult: function( data ) {
            if( data.id == null || jQuery.inArray( data.text, wpfep_roles_editor_data.capabilities['custom']['capabilities'] ) === -1 ) {
                return data.text;
            }

            var option = jQuery( '<span></span>' );
            var delete_cap = jQuery( '<a class="wpfep-re-cap-perm-delete">' + wpfep_roles_editor_data.delete_permanently_text + '</a>' );

            delete_cap.on( 'mouseup', function( event ) {
                event.stopPropagation();
            } );

            delete_cap.on( 'click', function( event ) {
                if( confirm( wpfep_roles_editor_data.capability_text + ': ' + jQuery( this ).siblings( 'span' ).text() + '\n\n' + wpfep_roles_editor_data.capability_perm_delete_text ) ) {
                    wpfep_re_delete_capability_permanently( jQuery( this ).siblings( 'span' ).text() );
                }
            } );

            option.text( data.text );
            option = option.add( delete_cap );

            return option;
        }
    } );
}

function wpfep_re_delete_capability_permanently( capability ) {
    var data = {
        'action'        : 'delete_capability_permanently',
        'security'      : jQuery( '.post-type-wpfep-roles-editor #wpfep-re-ajax-nonce' ).val(),
        'capability'    : capability
    };

    jQuery( '.post-type-wpfep-roles-editor .wpfep-role-edit-table-entry' ).remove();
    jQuery( '.post-type-wpfep-roles-editor .wpfep-re-spinner-container' ).show();

    jQuery.post( wpfep_roles_editor_data.ajaxUrl, data, function( response ) {
        window.location.reload();
    } );
}

function wpfep_re_create_capabilities_group( key, value, counter ) {
    var capabilities_single_group_caps = {};
    var capabilities_single_group_caps_array = [];

    jQuery.each( value['capabilities'], function( key, value ) {
        capabilities_single_group_caps = {
            id: value + '_' + counter,
            text: value
        };

        capabilities_single_group_caps_array.push( capabilities_single_group_caps );
    } );

    return {
        category: key,
        text: value['label'],
        children: capabilities_single_group_caps_array
    };
}

function wpfep_re_display_capabilities( action ) {
    jQuery( '.post-type-wpfep-roles-editor .wpfep-re-spinner-container' ).hide();
    jQuery( '.post-type-wpfep-roles-editor .wpfep-role-edit-table-entry' ).remove();

    var capabilities;
    if( action == 'all' ) {
        capabilities = wpfep_re_current_role_capabilities;
    } else {
        capabilities = wpfep_re_capabilities_group;
    }

    jQuery.each( capabilities, function( key, value ) {
        var table = jQuery( '#wpfep-role-edit-table' );

        if( action == 'all' ) {
            wpfep_re_display_capability( key );
        } else {
            if( value['category'] == action ) {
                jQuery.each( value['children'], function( key, value ) {
                    if( wpfep_re_current_role_capabilities != null && value['text'] in wpfep_re_current_role_capabilities ) {
                        wpfep_re_display_capability( value['text'] );
                    }
                } );
            }

            if( value['category'] == action && action == 'custom' ) {
                if( ! jQuery.isEmptyObject( wpfep_re_new_capabilities ) ) {
                    jQuery.each( wpfep_re_new_capabilities, function( key, value ) {
                        if( ! ( value in wpfep_roles_editor_data.all_capabilities ) ) {
                            var new_capability_check = 0;
                            jQuery.each( wpfep_roles_editor_data.capabilities, function( key2, value2 ) {
                                if( value2['label'] && value2['label'] != 'Custom' && jQuery.inArray( value, value2['capabilities'] ) !== -1 ) {
                                    new_capability_check++;
                                }
                            } );

                            if( new_capability_check == 0 ) {
                                wpfep_re_display_capability( value );
                            }
                        }
                    } );
                }
            }
        }
    } );

    if( jQuery( '.post-type-wpfep-roles-editor .wpfep-role-edit-table-entry' ).length ) {
        jQuery( '.post-type-wpfep-roles-editor .wpfep-role-edit-no-cap' ).remove();
    } else {
        wpfep_re_no_capabilities_found();
    }
}

function wpfep_re_display_capability( capability ) {
    var title = '';
    var wpfep_capability_class = 'wpfep-capability';
    if( ! wpfep_roles_editor_data.current_role_capabilities || ( wpfep_roles_editor_data.current_role_capabilities && ! ( capability in wpfep_roles_editor_data.current_role_capabilities ) ) ) {
        wpfep_capability_class = wpfep_capability_class + ' wpfep-new-capability';
        title = 'title = "' + wpfep_roles_editor_data.new_cap_update_title_text + '"';
    } else if( wpfep_re_getParameterByName( 'wpfep_re_clone' ) && ! wpfep_roles_editor_data.current_role_capabilities ) {
        wpfep_capability_class = wpfep_capability_class + ' wpfep-new-capability';
        title = 'title = "' + wpfep_roles_editor_data.new_cap_publish_title_text + '"';
    }

    var delete_link = '<a class="wpfep-delete-capability-link" href="javascript:void(0)">Delete</a>';
    if( wpfep_roles_editor_data.current_user_role && jQuery.inArray( capability, wpfep_roles_editor_data.admin_capabilities ) !== -1 ) {
        delete_link = '<span class="wpfep-delete-capability-disabled" title="' + wpfep_roles_editor_data.cap_no_delete_text + '">' + wpfep_roles_editor_data.delete_text + '</span>';
    }

    jQuery( '.post-type-wpfep-roles-editor #wpfep-role-edit-table' ).find( '#wpfep-role-edit-caps-clear' ).after(
        '<div class="wpfep-role-edit-table-entry" ' + title + '>' +
            '<span class="' + wpfep_capability_class + '">' + capability + '</span>' +
            '<span class="wpfep-delete-capability">' + delete_link + '</span>' +
        '</div>'
    );
}

function wpfep_re_add_capability() {
    var capabilities_select = jQuery( '.post-type-wpfep-roles-editor .wpfep-capabilities-select' );
    var new_capability_input = jQuery( '.post-type-wpfep-roles-editor .wpfep-add-new-cap-input' );
    var table = jQuery( '.post-type-wpfep-roles-editor #wpfep-role-edit-table' );
    var capabilities = {};
    var no_duplicates = {};

    if( jQuery( '.post-type-wpfep-roles-editor .select2.select2-container' ).is( ':visible' ) && jQuery( capabilities_select ).val() != null ) {
        jQuery( capabilities_select ).find( 'option:selected' ).each( function() {
            if( ! no_duplicates[jQuery( this ).text()] ) {
                if( ! jQuery( '.post-type-wpfep-roles-editor .wpfep-role-editor-tab.wpfep-role-editor-all' ).closest( 'li.wpfep-role-editor-tab-title' ).hasClass( 'wpfep-role-editor-tab-active' ) ) {
                    wpfep_re_tabs_handler( jQuery( '.post-type-wpfep-roles-editor .wpfep-role-editor-tab.wpfep-role-editor-all' ) );
                }

                var title = '';
                var wpfep_capability_class = 'wpfep-capability';
                if( ! wpfep_roles_editor_data.current_role_capabilities || ( wpfep_roles_editor_data.current_role_capabilities && ! ( jQuery( this ).text() in wpfep_roles_editor_data.current_role_capabilities ) ) ) {
                    wpfep_capability_class = wpfep_capability_class + ' wpfep-new-capability';
                    wpfep_re_unsaved_capabilities[jQuery( this ).text()] = jQuery( this ).text();
                    title = 'title = "' + wpfep_roles_editor_data.new_cap_update_title_text + '"';
                } else if( wpfep_re_getParameterByName( 'wpfep_re_clone' ) && ! wpfep_roles_editor_data.current_role_capabilities ) {
                    wpfep_capability_class = wpfep_capability_class + ' wpfep-new-capability';
                    title = 'title = "' + wpfep_roles_editor_data.new_cap_publish_title_text + '"';
                }

                jQuery( '.post-type-wpfep-roles-editor .wpfep-role-edit-no-cap' ).remove();

                jQuery( table ).find( '#wpfep-role-edit-caps-clear' ).after(
                    '<div class="wpfep-role-edit-table-entry wpfep-new-capability-highlight" ' + title + '>' +
                        '<span class="' + wpfep_capability_class + '">' + jQuery( this ).text() + '</span>' +
                        '<span class="wpfep-delete-capability"><a class="wpfep-delete-capability-link" href="javascript:void(0)">' + wpfep_roles_editor_data.delete_text + '</a></span>' +
                    '</div>' );

                capabilities[jQuery( this ).text()] = jQuery( this ).text();
                no_duplicates[jQuery( this ).text()] = jQuery( this ).text();

                delete wpfep_re_capabilities_to_delete[jQuery( this ).text()];
            }
        } );

        wpfep_re_new_capability( capabilities );

        wpfep_re_disable_select_capabilities( wpfep_re_capabilities_group, capabilities, 'add' );

        jQuery( capabilities_select ).val( null ).trigger( 'change' );

        wpfep_re_number_of_capabilities();

        setTimeout( function() {
            jQuery( '.post-type-wpfep-roles-editor .wpfep-role-edit-table-entry' ).removeClass( 'wpfep-new-capability-highlight' );
        }, 500 );
    } else if( jQuery( new_capability_input ).is( ':visible' ) && jQuery( new_capability_input ).val().length != 0 ) {
        var new_capability_value = jQuery( new_capability_input ).val();
        new_capability_value = new_capability_value.trim().replace( /<.*?>/g, '' ).replace( /\s/g, '_' ).replace( /[^a-zA-Z0-9_]/g, '' );

        if( new_capability_value && ( ! wpfep_roles_editor_data.hidden_capabilities || ! ( new_capability_value in wpfep_roles_editor_data.hidden_capabilities ) ) ) {
            if( ! ( new_capability_value in wpfep_re_current_role_capabilities ) && ! ( new_capability_value in wpfep_re_new_capabilities ) ) {
                wpfep_re_tabs_handler( jQuery( '.post-type-wpfep-roles-editor .wpfep-role-editor-tab.wpfep-role-editor-all' ) );

                var title = '';
                var wpfep_capability_class = 'wpfep-capability';
                if( ! wpfep_roles_editor_data.current_role_capabilities || ( wpfep_roles_editor_data.current_role_capabilities && ! ( new_capability_value in wpfep_roles_editor_data.current_role_capabilities ) ) ) {
                    wpfep_capability_class = wpfep_capability_class + ' wpfep-new-capability';
                    wpfep_re_unsaved_capabilities[new_capability_value] = new_capability_value;
                    title = 'title = "' + wpfep_roles_editor_data.new_cap_update_title_text + '"';
                } else if( wpfep_re_getParameterByName( 'wpfep_re_clone' ) && ! wpfep_roles_editor_data.current_role_capabilities ) {
                    wpfep_capability_class = wpfep_capability_class + ' wpfep-new-capability';
                    title = 'title = "' + wpfep_roles_editor_data.new_cap_publish_title_text + '"';
                }

                jQuery( '.post-type-wpfep-roles-editor .wpfep-role-edit-no-cap' ).remove();

                jQuery( table ).find( '#wpfep-role-edit-caps-clear' ).after(
                    '<div class="wpfep-role-edit-table-entry wpfep-new-capability-highlight" ' + title + '>' +
                        '<span class="' + wpfep_capability_class + '">' + new_capability_value + '</span>' +
                        '<span class="wpfep-delete-capability"><a class="wpfep-delete-capability-link" href="javascript:void(0)">' + wpfep_roles_editor_data.delete_text + '</a></span>' +
                    '</div>' );

                capabilities[new_capability_value] = new_capability_value;
                delete wpfep_re_capabilities_to_delete[new_capability_value];

                wpfep_re_change_select_to_input();

                wpfep_re_new_capability( capabilities );

                wpfep_re_disable_select_capabilities( wpfep_re_capabilities_group, capabilities, 'add' );

                jQuery( new_capability_input ).val( '' );

                wpfep_re_number_of_capabilities();

                setTimeout( function() {
                    jQuery( '.post-type-wpfep-roles-editor .wpfep-role-edit-table-entry' ).removeClass( 'wpfep-new-capability-highlight' );
                }, 500 );
            } else {
                jQuery( new_capability_input ).val( '' );

                jQuery( '.post-type-wpfep-roles-editor #wpfep-duplicate-capability-error' ).show().delay( 3000 ).fadeOut();
            }
        } else if( wpfep_roles_editor_data.hidden_capabilities && new_capability_value in wpfep_roles_editor_data.hidden_capabilities ) {
            jQuery( '.post-type-wpfep-roles-editor #wpfep-hidden-capability-error' ).show().delay( 3000 ).fadeOut();
        } else {
            jQuery( '.post-type-wpfep-roles-editor #wpfep-add-capability-error' ).show().delay( 3000 ).fadeOut();
        }
    } else {
        jQuery( '.post-type-wpfep-roles-editor #wpfep-add-capability-error' ).show().delay( 3000 ).fadeOut();
    }
}

function wpfep_re_new_capability( capabilities ) {
    jQuery.each( capabilities, function( key, value ) {
        if( ! ( value in wpfep_roles_editor_data.all_capabilities ) || ! ( value in wpfep_re_current_role_capabilities ) ) {
            wpfep_re_new_capabilities[value] = value;
        }
    } );

    jQuery.extend( wpfep_re_current_role_capabilities, wpfep_re_new_capabilities );
}

function wpfep_re_update_role_capabilities() {
    jQuery( '.post-type-wpfep-roles-editor #wpfep-role-slug-hidden' ).val( jQuery( '#wpfep-role-slug' ).val() );

    var data = {
        'action'                    : 'update_role_capabilities',
        'security'                  : jQuery( '.post-type-wpfep-roles-editor #wpfep-re-ajax-nonce' ).val(),
        'role_display_name'         : jQuery( '.post-type-wpfep-roles-editor #titlediv' ).find( '#title' ).val(),
        'role'                      : jQuery( '.post-type-wpfep-roles-editor #wpfep-role-slug' ).val(),
        'new_capabilities'          : wpfep_re_unsaved_capabilities,
        'all_capabilities'          : wpfep_re_current_role_capabilities,
        'capabilities_to_delete'    : wpfep_re_capabilities_to_delete
    };

    jQuery.post( wpfep_roles_editor_data.ajaxUrl, data, function( response ) {
        jQuery( '.post-type-wpfep-roles-editor #publishing-action #publish' ).removeClass( 'disabled' ).trigger( 'click' );
    } );
}

function wpfep_re_change_select_to_input() {
    if( jQuery( '.post-type-wpfep-roles-editor .select2.select2-container' ).is( ':visible' ) ) {
        jQuery( '.post-type-wpfep-roles-editor .select2.select2-container' ).hide();
        jQuery( '.post-type-wpfep-roles-editor .wpfep-add-new-cap-input' ).show();
        jQuery( '.post-type-wpfep-roles-editor a.wpfep-add-new-cap-link' ).text( wpfep_roles_editor_data.cancel_text );
    } else {
        jQuery( '.post-type-wpfep-roles-editor .select2.select2-container' ).show();
        jQuery( '.post-type-wpfep-roles-editor .wpfep-add-new-cap-input' ).hide();
        jQuery( '.post-type-wpfep-roles-editor a.wpfep-add-new-cap-link' ).text( wpfep_roles_editor_data.add_new_capability_text );
    }
}

function wpfep_re_getParameterByName( name, url ) {
    if( ! url ) {
        url = window.location.href;
    }

    name = name.replace( /[\[\]]/g, "\\$&" );

    var regex = new RegExp( "[?&]" + name + "(=([^&#]*)|&|#|$)" ), results = regex.exec( url );

    if( ! results ) {
        return null;
    }

    if( ! results[2] ) {
        return '';
    }

    return decodeURIComponent( results[2].replace( /\+/g, " " ) );
}
jQuery(document).ready(function(){
  jQuery("#wpfep_edit_role_capabilities .handlediv").click(function(){
    jQuery("#wpfep-role-edit-caps-div").toggle();
  });
});
