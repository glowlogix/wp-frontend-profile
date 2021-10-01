<?php
/**
 * Plugin core function
 *
 * This file contains all the core function of this plugin
 * add form, update forms, delete forms
 * Upload files, insert post types posts
 *
 * @author glowlogix
 *
 */

/**
 * Enqueue js and css files
 */
add_action("admin_enqueue_scripts", "fbwfp_enqueue_scripts");
function fbwfp_enqueue_scripts()
{
    // register styles
    wp_enqueue_style("fbwpf-css", FBWPF_PLUGIN_URL . "assets/css/style.css");
    // enqueue scripts
    wp_enqueue_script(
        "fbwpf-jquery-ui",
        FBWPF_PLUGIN_URL . "assets/js/jquery-ui.min.js",
        ["jquery"],
        false,
        true
    );
    wp_enqueue_script(
        "fbwpf-form-builder",
        FBWPF_PLUGIN_URL . "assets/js/form-builder.min.js",
        ["jquery"],
        false,
        true
    );
    wp_enqueue_script(
        "fbwpf-form-render-script",
        FBWPF_PLUGIN_URL . "assets/js/form-render.min.js",
        ["jquery"],
        false,
        true
    );
    // wp_enqueue_script( 'textarea.tinymce',FBWPF_PLUGIN_URL.'assets/js/textarea.tinymce.js', array( 'jquery' ));
    wp_enqueue_script(
        "fbwpf-script",
        FBWPF_PLUGIN_URL . "assets/js/script.js",
        ["jquery"],
        false,
        true
    );
    wp_localize_script("fbwpf-script", "fbwpf_ajax_obj", [
        "ajax_url" => admin_url("admin-ajax.php"),
    ]);
}

add_action("wp_enqueue_scripts", "fbwfp_enqueue_scripts_front");
function fbwfp_enqueue_scripts_front()
{
    wp_enqueue_style(
        "fbwpf-css",
        FBWPF_PLUGIN_URL . "assets/css/style-front.css"
    );
    wp_enqueue_script(
        "fbwpf-jquery-ui-front",
        FBWPF_PLUGIN_URL . "assets/js/jquery-ui.min.js",
        ["jquery"]
    );
    wp_enqueue_script(
        "fbwpf-form-builder-front",
        FBWPF_PLUGIN_URL . "assets/js/form-builder.min.js",
        ["jquery"]
    );
    wp_enqueue_script(
        "fbwpf-form-render-script-front",
        FBWPF_PLUGIN_URL . "assets/js/form-render.min.js",
        ["jquery"]
    );
    // wp_enqueue_script( 'textarea.tinymce',FBWPF_PLUGIN_URL.'assets/js/textarea.tinymce.js', array( 'jquery' ));
    wp_enqueue_script(
        "fbwpf-jquery-from-front",
        FBWPF_PLUGIN_URL . "assets/js/form-front.js",
        ["jquery"]
    );
}

/**
 * Save wp frontend profile form builder form
 */
add_action("wp_ajax_fbwfp_form_builder", "fbwfp_save_formbuilder_form");
add_action("wp_ajax_nopriv_fbwfp_form_builder", "fbwfp_save_formbuilder_form");
function fbwfp_save_formbuilder_form()
{
    $form_title = $_POST["form_title"];
    $form_fields = stripslashes($_POST["fields"]);
    $form_type = $_POST["form_type"];
    $post_status = $_POST["post_status"];
    $form_action = $_POST["form_action"];
    $form_id = $_POST["form_id"];

    $my_post = [
        "post_title" => wp_strip_all_tags($form_title),
        "post_content" => wp_kses_post($form_fields),
        "post_status" => "publish",
        "post_type" => "wpfep-form-builder",
        "post_author" => get_current_user_id(),
    ];

    if ($form_action == "edit") {
        $my_post["ID"] = $form_id;
        $post = wp_update_post($my_post);
        $post = $form_id;
    } else {
        $post = wp_insert_post($my_post);
    }

    if ($post) {
        update_post_meta($post, "_form_type", $form_type);
        update_post_meta($post, "_post_status", $post_status);

        echo "success";
    }
    die();
}

/**
 * Delete form by ID
 */
add_action("admin_init", "fbwfp_delete_form");
function fbwfp_delete_form()
{
    $form = $_GET["page"];
    if ($form == "fbwfp_all_form" && !empty($_GET["delete_form"])) {
        $form_id = $_GET["delete_form"];
        wp_delete_post($form_id, false);
        wp_redirect(admin_url("admin.php/") . "?page=" . fbwfp_all_form);
    }
}

/**
 * Before form submit validations
 */
add_action("wpfep_before_post_insert", "wpfep_before_post_insert_function");
function wpfep_before_post_insert_function()
{
    if (isset($_POST["wpfep-form-action"])) {
        $data = $_POST;
        $form_id = $data["form-id"];
        $post = get_post((int) $form_id);
        $form_fields = $post->post_content;
        $fields_json = trim($form_fields, '"');
        $form_data = json_decode($fields_json);
        foreach ($form_data as $formField) {
            if ($formField->type == "post_title") {
                $name = "wpfep-post-title";
            } elseif ($formField->type == "post_description") {
                $name = "wpfep-post-description";
            } elseif ($formField->type == "post_tags") {
                $name = "wpfep-post-tags";
            } elseif ($formField->type == "post_categories") {
                $name = "wpfep-post-categories";
            } elseif ($formField->type == "post_featured_image") {
                $name = "wpfep-featured-image";
            } else {
                $name = $formField->name;
            }
            $fields[$name] = [
                "label" => $formField->label,
                "required" => $formField->required,
            ];
        }

        $message = "";
        foreach ($data as $key => $value) {
            if (
                !empty($fields[$key]) &&
                $fields[$key]["required"] == true &&
                empty($value)
            ) {
                $message .= $fields[$key]["label"] . ",";
            }
        }

        // if($message != ''){
        //   $current_page = $_POST['current_page'];
        //   wp_redirect($current_page.'?form_error=validation');
        // }
    }
}

/**
 * Insert posts to db
 */
add_action("init", "fbwpf_save_form_data");
function fbwpf_save_form_data()
{
    if (isset($_POST["wpfep-form-action"])) {
        $data = $_POST;
        $form_id = $data["form-id"];
        // validation before post insert
        // do_action('wpfep_before_post_insert');
        $post_status = get_post_meta((int) $form_id, "_post_status", true);

        $post_type = get_post_meta((int) $form_id, "_form_type", true);

        $post = [
            "post_type" => $post_type,
            "post_title" => esc_html($data["wpfep-post-title"]),
            "post_content" => wp_kses_post($data["wpfep-post-description"]),
            "post_status" => $post_status,
            "post_author" => get_current_user_id(),
        ];

        // return inserted post id
        $post_id = wp_insert_post($post);

        // Insert tags tags for the post
        if ($post_id) {
            // Array of Tags to add
            $tags = explode(",", $data["wpfep-post-tags"]);
            wp_set_post_tags($post_id, $tags);
        }

        // assign categories to post
        if ($post_id) {
            $categories = $data["wpfep-post-categories"]; // Array of Tags to add
            wp_set_post_categories($post_id, $categories);
        }

        // Adding other custom fields
        if ($post_id) {
            foreach ($data as $key => $value) {
                $value = $value;
                if (
                    $key != "wpfep-post-title" &&
                    $key != "wpfep-post-description" &&
                    $key != "wpfep-post-tags" &&
                    $key != "wpfep-post-categories" &&
                    $key != "wpfep-form-action"
                ) {
                    update_post_meta(
                        $post_id,
                        $key,
                        sanitize_text_field($value)
                    );
                }
            }
        }

        // insert post thumbnail
        if ($post_id && !empty($_FILES["wpfep-featured-image"])) {
            $image = $_FILES["wpfep-featured-image"];
            $upload_id = wpfep_post_thumbnail_fbwpf($image, $post_id);
            update_post_meta($post_id, "_thumbnail_id", $upload_id);
        }

        /**
         * upload file and save metafields
         */
        $all_files = $_FILES;
        if (!empty($all_files)) {
            foreach ($all_files as $key => $file) {
                if ($key != "wpfep-featured-image") {
                    $upload_id = wpfep_post_thumbnail_fbwpf($file, $post_id);
                    update_post_meta($post_id, $key, $upload_id);
                }
            }
        }
        $current_page = $_POST["current_page"];
        wp_redirect($current_page);
    }
}

/**
 * Form display shortcode
 *
 * Create shortcode to show form at front end
 */
add_shortcode("wpfep_form_builder", "fbwfp_form_builder_shortcode");
function fbwfp_form_builder_shortcode($atts)
{
    // Shortcode attr
    $atts = shortcode_atts(
        [
            "id" => "",
            "title" => "",
        ],
        $atts
    );

    ob_start();

    // get post data by post id
    $post = get_post((int) $atts["id"]);

    //  Form type posts, page
    $form_type = get_post_meta((int) $atts["id"], "_form_type", true);

    // form fields as json formate
    $form_fields = $post->post_content;
    $fields_json = trim($form_fields, '"');

    if ($form_type == "post") {
        $action =
            '<input type="hidden" name="wpfep-form-action" value="' .
            $form_type .
            '">';
    } else {
        $action =
            '<input type="hidden" name="wpfep-form-action" value="' .
            $form_type .
            '">';
    }

    global $wp;
    $page_url = home_url($wp->request);

    echo '<div id="fbwfp-form-render">
     <form action="' .
        esc_url(admin_url()) .
        '/admin-post.php" method="post" enctype="multipart/form-data"> <div id="fb-form-wrap"> <input type="hidden" name="form-id" value="' .
        $atts["id"] .
        '"> <div class="rendered-form">' .
        $action;
    echo '<input name="current_page" type="hidden" value="' . $page_url . '">';
    $form_data = json_decode($fields_json);
    // print_r($form_data);exit;
    foreach ($form_data as $key => $field) {
        $field = (array) $field;
        /**
         * input type text, number, email, date
         */
        if (
            $field["type"] == "text" ||
            $field["type"] == "date" ||
            $field["type"] == "number" ||
            $field["type"] == "email"
        ) {
            if (isset($field["name"])) {
                $fname = $field["name"];
            } else {
                $fname = "";
            } ?>
          <div class="formbuilder-<?php echo $field[
              "type"
          ]; ?> form-group <?php echo $fname; ?>">
            
            <?php if (isset($field["label"])) {
              echo '<label class="formbuilder-' .
                    $field["type"] .
                    '-label" for="' .
                    $fname .
                    '">' .
                    $field["label"];
              if (isset($field["required"]) && $field["required"] == true) {
                  echo '<span class="formbuilder-required">*</span>';
              }
              if (isset($field["description"])) {
                  echo '<span class="tooltip-element" tooltip="' .
                        $field["description"] .
                        '">?</span>';
              }
              echo "</label>";
          } ?>

            <input 
                type="<?php echo $field["type"]; ?>"
                <?php
                if (isset($field["name"])) {
                    echo ' name="' . $fname . '"';
                }
            if (isset($field["description"])) {
                echo ' title="' . $field["description"] . '"';
            }
            if (isset($field["value"])) {
                echo ' value="' . $field["value"] . '"';
            }
            if (isset($field["className"])) {
                echo ' class="form-control ' . $field["className"] . '"';
            } else {
                echo ' class="form-control"';
            }
            if (isset($field["maxlength"])) {
                echo ' maxlength="' . $field["maxlength"] . '"';
            }
            if (isset($field["required"]) && $field["required"] == true) {
                echo ' aria-required="true" required="' .
                        $field["required"] .
                        '"';
            }
            if (isset($field["name"])) {
                echo ' id="' . $fname . '"';
            }
            if (isset($field["placeholder"])) {
                echo ' placeholder="' . $field["placeholder"] . '"';
            } ?> 
              >
          </div>
        <?php
        }

        /**
         * post title field
         */
        if ($field["type"] == "post_title") { ?>
          <?php if (isset($field["name"])) {
            $fname = $field["name"];
        } else {
            $fname = "";
        } ?>
          <div class="formbuilder-<?php echo $field[
              "type"
          ]; ?> form-group <?php echo $fname; ?>">
            
            <?php if (isset($field["label"])) {
              echo '<label class="formbuilder-' .
                    $field["type"] .
                    '-label" for="' .
                    $fname .
                    '">' .
                    $field["label"];
              if (isset($field["required"]) && $field["required"] == true) {
                  echo '<span class="formbuilder-required">*</span>';
              }
              if (isset($field["description"])) {
                  echo '<span class="tooltip-element" tooltip="' .
                        $field["description"] .
                        '">?</span>';
              }
              echo "</label>";
          } ?>

            <input 
                type="<?php echo $field["type"]; ?>" 
                name="wpfep-post-title"
                <?php
                if (isset($field["description"])) {
                    echo ' title="' . $field["description"] . '"';
                }
                if (isset($field["value"])) {
                    echo ' value="' . $field["value"] . '"';
                }
                if (isset($field["className"])) {
                    echo ' class="form-control ' . $field["className"] . '"';
                } else {
                    echo ' class="form-control"';
                }
                if (isset($field["maxlength"])) {
                    echo ' maxlength="' . $field["maxlength"] . '"';
                }
                if (isset($field["required"]) && $field["required"] == true) {
                    echo ' aria-required="true" required="' .
                        $field["required"] .
                        '"';
                }
                if (isset($field["name"])) {
                    echo ' id="' . $fname . '"';
                }
                if (isset($field["placeholder"])) {
                    echo ' placeholder="' . $field["placeholder"] . '"';
                }
                ?> 
              >
          </div>
        <?php }

        /**
         * post tags field
         */
        if ($field["type"] == "post_tags") { ?>
          <?php if (isset($field["name"])) {
            $fname = $field["name"];
        } else {
            $fname = "";
        } ?>
          <div class="formbuilder-<?php echo $field[
              "type"
          ]; ?> form-group <?php echo $fname; ?>">
            
            <?php if (isset($field["label"])) {
              echo '<label class="formbuilder-' .
                    $field["type"] .
                    '-label" for="' .
                    $fname .
                    '">' .
                    $field["label"];
              if (isset($field["required"]) && $field["required"] == true) {
                  echo '<span class="formbuilder-required">*</span>';
              }
              if (isset($field["description"])) {
                  echo '<span class="tooltip-element" tooltip="' .
                        $field["description"] .
                        '">?</span>';
              }
              echo "</label>";
          } ?>

            <input 
                type="<?php echo $field["type"]; ?>" 
                name="wpfep-post-tags"
                <?php
                if (isset($field["description"])) {
                    echo ' title="' . $field["description"] . '"';
                }
                if (isset($field["value"])) {
                    echo ' value="' . $field["value"] . '"';
                }
                if (isset($field["className"])) {
                    echo ' class="form-control ' . $field["className"] . '"';
                } else {
                    echo ' class="form-control"';
                }
                if (isset($field["maxlength"])) {
                    echo ' maxlength="' . $field["maxlength"] . '"';
                }
                if (isset($field["required"]) && $field["required"] == true) {
                    echo ' aria-required="true" required="' .
                        $field["required"] .
                        '"';
                }
                if (isset($field["name"])) {
                    echo ' id="' . $fname . '"';
                }
                if (isset($field["placeholder"])) {
                    echo ' placeholder="' . $field["placeholder"] . '"';
                }
                ?> 
              >
          </div>
        <?php }

        /**
         * Post description field
         */
        if ($field["type"] == "post_description") { ?>
        <div class="formbuilder-textarea form-group <?php echo $field[
            "name"
        ]; ?>">
        <?php if (isset($field["label"])) {
            echo '<label class="formbuilder-' .
                $field["type"] .
                '-label" for="' .
                $field["name"] .
                '">' .
                $field["label"];
            if (isset($field["required"]) && $field["required"] == true) {
                echo '<span class="formbuilder-required">*</span>';
            }
            if (isset($field["description"])) {
                echo '<span class="tooltip-element" tooltip="' .
                    $field["description"] .
                    '">?</span>';
            }
            echo "</label>";
        } ?>
          <textarea name="wpfep-post-description"
          <?php
          if (isset($field["className"])) {
              echo ' class="form-control ' . $field["className"] . '"';
          } else {
              echo ' class="form-control"';
          }
          if (isset($field["rows"])) {
              echo ' rows="' . $field["rows"] . '"';
          }
          if (isset($field["description"])) {
              echo ' title="' . $field["description"] . '"';
          }
          if (isset($field["maxlength"])) {
              echo ' maxlength="' . $field["maxlength"] . '"';
          }
          if (isset($field["required"]) && $field["required"] == true) {
              echo ' aria-required="true" required="' .
                  $field["required"] .
                  '"';
          }
          if (isset($field["name"])) {
              echo ' id="' . $fname . '"';
          }
          if (isset($field["placeholder"])) {
              echo ' placeholder="' . $field["placeholder"] . '"';
          }
          ?> ><?php if (isset($field["value"])) {
              echo $field["value"];
          } ?></textarea>
        </div>
      <?php }

        /**
         * post categories
         */
        if ($field["type"] == "post_categories") { ?>
        <div class="formbuilder-select form-group ">
          <?php if (isset($field["label"])) {
            $fname = $field["name"];
            echo '<label class="formbuilder-' .
                  $field["type"] .
                  '-label" for="' .
                  $fname .
                  '">' .
                  $field["label"];
            if (isset($field["required"]) && $field["required"] == true) {
                echo '<span class="formbuilder-required">*</span>';
            }
            if (isset($field["description"])) {
                echo '<span class="tooltip-element" tooltip="' .
                      $field["description"] .
                      '">?</span>';
            }
            echo "</label>";
        } ?>
          <?php
          if (isset($field["placeholder"])) {
              echo '<option disabled="null" selected="null">' .
                  $field["placeholder"] .
                  "</option>";
          }

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
          <select 
            <?php
            if (isset($field["multiple"]) && $field["multiple"] == true) {
                echo 'multiple="true"';
                echo ' name="wpfep-post-categories[]"';
            } else {
                echo ' name="wpfep-post-categories"';
            }
            if (isset($field["className"])) {
                echo ' class="form-control ' . $field["className"] . '"';
            } else {
                echo ' class="form-control"';
            }
            if (isset($field["description"])) {
                echo ' title="' . $field["description"] . '"';
            }
            if (isset($field["required"]) && $field["required"] == true) {
                echo ' aria-required="true" required="' .
                    $field["required"] .
                    '"';
            }
            if (isset($field["name"])) {
                echo ' id="' . $fname . '"';
            }
            ?> 
          >
            <?php echo $options; ?>
          </select>
        </div>
      <?php }

        /**
         * input type radio, checkbox
         */
        if (
            $field["type"] == "radio-group" ||
            $field["type"] == "checkbox-group"
        ) {
            if (isset($field["name"])) {
                $fname = $field["name"];
            } else {
                $fname = "";
            } ?>
        <div class="formbuilder-radio-group form-group <?php echo $fname; ?>">
        <?php if (isset($field["label"])) {
                echo '<label class="formbuilder-' .
                $field["type"] .
                '-label" for="' .
                $fname .
                '">' .
                $field["label"];
                if (isset($field["required"]) && $field["required"] == true) {
                    echo '<span class="formbuilder-required">*</span>';
                }
                if (isset($field["description"])) {
                    echo '<span class="tooltip-element" tooltip="' .
                    $field["description"] .
                    '">?</span>';
                }
                echo "</label>";
            } ?>
          <div class="<?php echo $field["type"]; ?>">
              <?php
              if ($field["type"] == "radio-group") {
                  $field_type = "radio";
              } else {
                  $field_type = "checkbox";
              }
            if (isset($field["values"])) {
                foreach ($field["values"] as $key => $value) {
                    $value = (array) $value; ?>
                  <div class="formbuilder-<?php echo $field_type; ?>-inline">
                    <input name="<?php echo $fname; ?>" id="<?php echo $fname .
    "-" .
    $key; ?>" <?php if (
    isset($field["required"]) &&
    $field["required"] == true
) {
        echo ' aria-required="true" required="' . $field["required"] . '"';
    } ?> value="<?php echo $value["value"]; ?>" <?php if (
    isset($value["selected"])
) {
        echo "selected";
    } ?> type="<?php echo $field_type; ?>">
                    <label for="<?php echo $fname .
                        "-" .
                        $key; ?>"><?php echo $value["label"]; ?></label>
                  </div>
                <?php
                }
            } ?>
              
          </div>
        </div>
      <?php
        }

        /**
         * Textarea field
         */
        if ($field["type"] == "textarea") { ?>
        <div class="formbuilder-textarea form-group <?php echo $field[
            "name"
        ]; ?>">
        <?php if (isset($field["label"])) {
            echo '<label class="formbuilder-' .
                $field["type"] .
                '-label" for="' .
                $field["name"] .
                '">' .
                $field["label"];
            if (isset($field["required"]) && $field["required"] == true) {
                echo '<span class="formbuilder-required">*</span>';
            }
            if (isset($field["description"])) {
                echo '<span class="tooltip-element" tooltip="' .
                    $field["description"] .
                    '">?</span>';
            }
            echo "</label>";
        } ?>
          <textarea
          <?php
          if (isset($field["name"])) {
              echo ' name="' . $field["name"] . '"';
          }
          if (isset($field["className"])) {
              echo ' class="' . $field["className"] . '"';
          }
          if (isset($field["rows"])) {
              echo ' rows="' . $field["rows"] . '"';
          }
          if (isset($field["description"])) {
              echo ' title="' . $field["description"] . '"';
          }
          if (isset($field["maxlength"])) {
              echo ' maxlength="' . $field["maxlength"] . '"';
          }
          if (isset($field["required"]) && $field["required"] == true) {
              echo ' aria-required="true" required="' .
                  $field["required"] .
                  '"';
          }
          if (isset($field["name"])) {
              echo ' id="' . $fname . '"';
          }
          if (isset($field["placeholder"])) {
              echo ' placeholder="' . $field["placeholder"] . '"';
          }
          ?> ><?php if (isset($field["value"])) {
              echo $field["value"];
          } ?></textarea>
        </div>
      <?php }

        /**
         * Heading field, paragraph field
         */
        if ($field["type"] == "header" || $field["type"] == "paragraph") { ?>
        <div <?php if (isset($field["className"])) {
            echo ' class="' . $field["className"] . '"';
        } ?>><<?php echo $field["subtype"]; ?>><?php echo $field[
    "label"
]; ?></<?php echo $field["subtype"]; ?>></div>
      <?php }

        /**
         * slect option field
         */
        if ($field["type"] == "select") { ?>
        <div class="formbuilder-select form-group field-select-1632720135299-0">
          <?php if (isset($field["label"])) {
            echo '<label class="formbuilder-' .
                  $field["type"] .
                  '-label" for="' .
                  $fname .
                  '">' .
                  $field["label"];
            if (isset($field["required"]) && $field["required"] == true) {
                echo '<span class="formbuilder-required">*</span>';
            }
            if (isset($field["description"])) {
                echo '<span class="tooltip-element" tooltip="' .
                      $field["description"] .
                      '">?</span>';
            }
            echo "</label>";
        } ?>
          <select 
            <?php
            if (isset($field["multiple"])) {
                echo 'multiple="true"';
                if (isset($field["name"])) {
                    echo ' name="' . $fname . '[]"';
                }
            } else {
                if (isset($field["name"])) {
                    echo ' name="' . $fname . '"';
                }
            }
            if (isset($field["className"])) {
                echo ' class="' . $field["className"] . '"';
            }
            if (isset($field["description"])) {
                echo ' title="' . $field["description"] . '"';
            }
            if (isset($field["required"]) && $field["required"] == true) {
                echo ' aria-required="true" required="' .
                    $field["required"] .
                    '"';
            }
            if (isset($field["name"])) {
                echo ' id="' . $fname . '"';
            }
            ?> 
          >
            <?php
            if (isset($field["placeholder"])) {
                echo '<option disabled="null" selected="null">' .
                    $field["placeholder"] .
                    "</option>";
            }

            if (isset($field["values"])) {
                foreach ($field["values"] as $key => $value) {
                    $value = (array) $value; ?>
                <option <?php if (isset($value["selected"])) {
                        echo "selected";
                    } ?> value="<?php echo $value[
     "value"
 ]; ?>" id="<?php echo $field["name"] . "-" . $key; ?>"><?php echo $value[
    "label"
]; ?></option>
              <?php
                }
            }
            ?>
          </select>
        </div>
      <?php }

        /**
         * input type file
         */
        if ($field["type"] == "file") { ?> 
        <div class="formbuilder-file form-group field-file-1632720143051-0">
          <?php if (isset($field["label"])) {
            echo '<label class="formbuilder-' .
                  $field["type"] .
                  '-label" for="' .
                  $field["name"] .
                  '">' .
                  $field["label"];
            if (isset($field["required"]) && $field["required"] == true) {
                echo '<span class="formbuilder-required">*</span>';
            }
            if (isset($field["description"])) {
                echo '<span class="tooltip-element" tooltip="' .
                      $field["description"] .
                      '">?</span>';
            }
            echo "</label>";
        } ?>
          <input 
          type="file" 
          <?php
          if (isset($field["multiple"])) {
              echo 'multiple="true"';
              if (isset($field["name"])) {
                  echo ' name="' . $field["name"] . '[]"';
              }
          } else {
              if (isset($field["name"])) {
                  echo ' name="' . $field["name"] . '"';
              }
          }
          if (isset($field["description"])) {
              echo ' title="' . $field["description"] . '"';
          }
          if (isset($field["className"])) {
              echo ' class="' . $field["className"] . '"';
          }
          if (isset($field["required"]) && $field["required"] == true) {
              echo ' aria-required="true" required="' .
                  $field["required"] .
                  '"';
          }
          if (isset($field["name"])) {
              echo ' id="' . $field["name"] . '"';
          }
          if (isset($field["placeholder"])) {
              echo ' placeholder="' . $field["placeholder"] . '"';
          }
          ?>
          >
        </div>
      <?php }

        /**
         * input type file
         */
        if ($field["type"] == "post_featured_image") { ?> 
        <div class="formbuilder-file form-group">
          <?php if (isset($field["label"])) {
            echo '<label class="formbuilder-' .
                  $field["type"] .
                  '-label" for="' .
                  $field["name"] .
                  '">' .
                  $field["label"];
            if (isset($field["required"]) && $field["required"] == true) {
                echo '<span class="formbuilder-required">*</span>';
            }
            if (isset($field["description"])) {
                echo '<span class="tooltip-element" tooltip="' .
                      $field["description"] .
                      '">?</span>';
            }
            echo "</label>";
        } ?>
          <input 
          type="file" 
          <?php
          if (isset($field["multiple"])) {
              echo 'multiple="true"';
              echo ' name="wpfep-featured-image[]"';
          } else {
              echo ' name="wpfep-featured-image"';
          }
          if (isset($field["description"])) {
              echo ' title="' . $field["description"] . '"';
          }
          if (isset($field["className"])) {
              echo ' class="form-control ' . $field["className"] . '"';
          } else {
              echo ' class="form-control"';
          }
          if (isset($field["required"]) && $field["required"] == true) {
              echo ' aria-required="true" required="' .
                  $field["required"] .
                  '"';
          }
          if (isset($field["name"])) {
              echo ' id="' . $field["name"] . '"';
          }
          if (isset($field["placeholder"])) {
              echo ' placeholder="' . $field["placeholder"] . '"';
          }
          ?>
          >
        </div>
      <?php }
    }

    echo '<div class="form-submit"><button type="submit">Submit</button></div>
    </div></div>
    </form>
    </div>';
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}
/**
 * upload attachment
 */
function wpfep_post_thumbnail_fbwpf($file, $post_id)
{
    $attachment_file = $file;

    if (is_array($attachment_file["name"])) {
        $ids = "";
        foreach ($attachment_file["name"] as $key => $dfile) {
            $files = [
                "name" => $attachment_file["name"][$key],
                "type" => $attachment_file["type"][$key],
                "tmp_name" => $attachment_file["tmp_name"][$key],
                "error" => $attachment_file["error"][$key],
                "size" => $attachment_file["size"][$key],
            ];
            if ($key == 0) {
                $ids .= wpfep_insert_attachment($files);
            } else {
                $ids .= "," . wpfep_insert_attachment($files);
            }
        }
        return $ids;
    } else {
        $attachment_file = $file;
        return wpfep_insert_attachment($attachment_file);
    }
}

function wpfep_insert_attachment($attachment_file)
{
    // WordPress environment
    require dirname(__FILE__) . "/../../../../wp-load.php";

    $wordpress_upload_dir = wp_upload_dir();
    // $wordpress_upload_dir['path'] is the full server path to wp-content/uploads/2017/05, for multisite works good as well
    // $wordpress_upload_dir['url'] the absolute URL to the same folder, actually we do not need it, just to show the link to file
    if (empty($attachment_file)) {
        die("File is not selected.");
    }

    $new_file_path =
        $wordpress_upload_dir["path"] . "/" . $attachment_file["name"];
    $new_file_mime = mime_content_type($attachment_file["tmp_name"]);

    if ($attachment_file["error"]) {
        die($attachment_file["error"]);
    }

    if ($attachment_file["size"] > wp_max_upload_size()) {
        die("It is too large than expected.");
    }

    if (!in_array($new_file_mime, get_allowed_mime_types())) {
        die('WordPress doesn\'t allow this type of uploads.');
    }
    $i = 1;
    while (file_exists($new_file_path)) {
        $i++;
        $new_file_path =
            $wordpress_upload_dir["path"] .
            "/" .
            $i .
            "_" .
            $attachment_file["name"];
    }

    // looks like everything is OK
    if (move_uploaded_file($attachment_file["tmp_name"], $new_file_path)) {
        $upload_id = wp_insert_attachment(
            [
                "guid" =>
                    $wordpress_upload_dir["url"] .
                    "/" .
                    basename($new_file_path),
                "post_mime_type" => $new_file_mime,
                "post_title" => preg_replace(
                    '/\.[^.]+$/',
                    "",
                    $attachment_file["name"]
                ),
                "post_content" => "",
                "post_status" => "inherit",
            ],
            $new_file_path
        );

        // wp_generate_attachment_metadata() won't work if you do not include this file
        require_once ABSPATH . "wp-admin/includes/image.php";

        // Generate and save the attachment metas into the database
        wp_update_attachment_metadata(
            $upload_id,
            wp_generate_attachment_metadata($upload_id, $new_file_path)
        );

        // Show the uploaded file in browser
        return $upload_id;
    }
}
