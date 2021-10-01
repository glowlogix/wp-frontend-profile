<?php

/**
 * wpfep_forms_List_Table Child Class
 */
class wpfep_forms_List_Table extends WP_List_Table
{
    function search_box($text, $input_id)
    {
        if (empty($_REQUEST["s"]) && !$this->has_items()) {
            return;
        }

        $input_id = $input_id . "-search-input";

        if (!empty($_REQUEST["orderby"])) {
            echo '<input type="hidden" name="orderby" value="' .
                esc_attr($_REQUEST["orderby"]) .
                '" />';
        }
        if (!empty($_REQUEST["order"])) {
            echo '<input type="hidden" name="order" value="' .
                esc_attr($_REQUEST["order"]) .
                '" />';
        }
        if (!empty($_REQUEST["post_mime_type"])) {
            echo '<input type="hidden" name="post_mime_type" value="' .
                esc_attr($_REQUEST["post_mime_type"]) .
                '" />';
        }
        if (!empty($_REQUEST["detached"])) {
            echo '<input type="hidden" name="detached" value="' .
                esc_attr($_REQUEST["detached"]) .
                '" />';
        }
        ?>
	<p class="search-box">
	<label class="screen-reader-text" for="<?php echo $input_id; ?>"><?php echo $text; ?>:</label>
	<input type="search" id="<?php echo $input_id; ?>" name="s" value="<?php _admin_search_query(); ?>" />
	<?php submit_button($text, "button", false, false, [
     "id" => "search-submit",
 ]); ?>
	</p>
	<?php
    }

    /**
     * wpfep_forms_List_Table constructor.
     */
    public function __construct()
    {
        // Set parent defaults.
        parent::__construct([
            "singular" => "form", // Singular name of the listed records.
            "plural" => "forms", // Plural name of the listed records.
            "ajax" => false, // Does this table support ajax?
        ]);
    }

    /**
     * Get a list of columns. The format is:
     * 'internal-name' => 'Title'
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information.
     */

    public function get_columns()
    {
        $columns = [
            "cb" => '<input type="checkbox" />', // Render a checkbox instead of text.
            "title" => _x("Title", "Column label", "FBWPF"),
            "author" => _x("Author", "Column label", "FBWPF"),
            "shortcode" => _x("Shortcode", "Column label", "FBWPF"),
        ];

        return $columns;
    }

    /**
     * Get a list of sortable columns. The format is:
     * @return array An associative array containing all the columns that should be sortable.
     */
    protected function get_sortable_columns()
    {
        $sortable_columns = [
            "title" => ["name", false],
            "author" => ["author", false],
            "shortcode" => ["shortcode", false],
        ];

        return $sortable_columns;
    }

    /**
     * Get default column value.
     * @param object $item        A singular item (one full row's worth of data).
     * @param string $column_name The name/slug of the column to be processed.
     * @return string Text or HTML to be placed inside the column <td>.
     */
    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case "author":
            case "shortcode":
                return $item[$column_name];
            default:
                return print_r($item, true); // Show the whole array for troubleshooting purposes.
        }
    }

    /**
     * Get value for checkbox column.
     * @param object $item A singular item (one full row's worth of data).
     * @return string Text to be placed inside the column <td>.
     */
    protected function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args["singular"], // Let's simply repurpose the table's singular label ("user").
            $item["ID"] // The value of the checkbox should be the record's ID.
        );
    }

    /**
     * Get title column value.
     * @param object $item A singular item (one full row's worth of data).
     * @return string Text to be placed inside the column <td>.
     */
    protected function column_title($item)
    {
        $page = wp_unslash($_REQUEST["page"]); // WPCS: Input var ok.

        // Build edit row action.
        $edit_query_args = [
            "page" => "fbwfp_form_builder",
            "action" => "edit",
            "edit_form" => $item["ID"],
        ];

        $actions["edit"] = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url(
                wp_nonce_url(
                    add_query_arg($edit_query_args, "admin.php"),
                    "edit_form_" . $item["ID"]
                )
            ),
            _x("Edit", "List table row action", "FBWPF")
        );

        // Build delete row action.
        $delete_query_args = [
            "page" => $page,
            "action" => "delete",
            "delete_form" => $item["ID"],
        ];

        $actions["delete"] = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url(
                wp_nonce_url(
                    add_query_arg($delete_query_args, "admin.php"),
                    "delete_form_" . $item["ID"]
                )
            ),
            _x("Delete", "List table row action", "FBWPF")
        );

        // Return the title contents.
        return sprintf(
            '%1$s <span style="color:silver;">(id:%2$s)</span>%3$s',
            $item["title"],
            $item["ID"],
            $this->row_actions($actions)
        );
    }

    /**
     * Get an associative array ( option_name => option_title ) with the list
     * of bulk actions available on this table.
     * @return array An associative array containing all the bulk actions.
     */
    protected function get_bulk_actions()
    {
        $actions = [
            "delete" => _x("Delete", "List table bulk action", "FBWPF"),
        ];

        return $actions;
    }

    /**
     * Handle bulk actions.
     * @see $this->prepare_items()
     */
    protected function process_bulk_action()
    {
        // Detect when a bulk action is being triggered.
        if ("delete" === $this->current_action()) {
            $ids = isset($_REQUEST["form"]) ? $_REQUEST["form"] : [];
            $my_ids = "";
            foreach ($ids as $id) {
                wp_delete_post($id, true);
            }
        }
    }

    /**
     * Prepares the list of items for displaying.
     * @global wpdb $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     */
    function prepare_items($search = "")
    {
        global $wpdb; //This is used only if making any database queries

        /*
         * First, lets decide how many records per page to show
         */
        $per_page = 20;
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];

        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();

        /*
         * GET THE DATA!
         */

        if (!empty($_REQUEST["s"])) {
            $search = $_REQUEST["s"];
        }

        $data = [];
        $args = [
            "post_type" => "wpfep-form-builder",
            "post_status" => "publish",
            "posts_per_page" => -1,
            "orderby" => "ID",
            "order" => "DESC",
            "s" => $search,
        ];

        $loop = new WP_Query($args);
        while ($loop->have_posts()):
            $loop->the_post();
            $shortcode = esc_html(
                '[wpfep_form_builder id="' .
                    get_the_id() .
                    '" title="' .
                    get_the_title() .
                    '"]'
            );
            $data[] = [
                "ID" => get_the_id(),
                "title" => get_the_title(),
                "author" => get_the_author(),
                "shortcode" => $shortcode,
            ];
        endwhile;
        wp_reset_postdata();

        usort($data, [$this, "usort_reorder"]);

        /*
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data, ($current_page - 1) * $per_page, $per_page);

        /*
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;

        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args([
            "total_items" => $total_items, // WE have to calculate the total number of items.
            "per_page" => $per_page, // WE have to determine how many items to show on a page.
            "total_pages" => ceil($total_items / $per_page), // WE have to calculate the total number of pages.
        ]);
    }

    /**
     * Callback to allow sorting of example data.
     *
     * @param string $a First value.
     * @param string $b Second value.
     *
     * @return int
     */
    protected function usort_reorder($a, $b)
    {
        // If no sort, default to title.
        $orderby = !empty($_REQUEST["orderby"])
            ? wp_unslash($_REQUEST["orderby"])
            : "title"; // WPCS: Input var ok.

        // If no order, default to asc.
        $order = !empty($_REQUEST["order"])
            ? wp_unslash($_REQUEST["order"])
            : "asc"; // WPCS: Input var ok.

        // Determine sort order.
        $result = strcmp($a[$orderby], $b[$orderby]);

        return "asc" === $order ? $result : -$result;
    }
}