<div class="wrap">
    <h1>Add New Form 
        <a style="margin-right:20px;" href="<?php echo admin_url('admin.php?page=fbwfp_all_form');?>" class="page-title-action">All Forms</a>
        <a href="<?php echo admin_url('admin.php?page=fbwfp_form_builder');?>" class="page-title-action">Add New</a>
    </h1>
</div>
<div class="fbwfp-form-builder">
    <div class="inner">
        <div class="form-name">
            <div><input type="text" autofocus="" id="form-name" name="form-name" placeholder="Form Title" value="Untitled Form"></div>
            <?php
            $types = get_post_types([], 'objects');
            $options = '';
            foreach ($types as $type) {
                $label = $type->label;
                if (isset($type->rewrite->slug)) {
                    $slug = $type->rewrite->slug;
                } else {
                    $slug = $type->name;
                }
                $options .= '<option value="'.$slug.'">'.$label.'</option>';
            }

            $form_id = $_GET['edit_form'];
            $post_type = get_post_meta($form_id, '_form_type', true);
            $post_status = get_post_meta($form_id, '_post_status', true);
            ?>
            <div>
            <select id="post-type">
                <option <?php if ($post_type == 'post') {
                echo 'selected';
            }?> value="post">Post</option>
                <option <?php if ($post_type == 'page') {
                echo 'selected';
            }?> value="page">Page</option>
            </select>
            <select id="post-status">
                <option <?php if ($post_status == 'publish') {
                echo 'selected';
            } ?> value="publish">Publish</option>
                <option <?php if ($post_status == 'draft') {
                echo 'selected';
            } ?> value="draft">Draft</option>
                <option <?php if ($post_status == 'pending') {
                echo 'selected';
            } ?> value="pending">Pending</option>
            </select>
        </div>
        </div>
    </div>
    <div id="fb-editor" style="margin-top:20px;"></div>
</div>

<div class="render-wrap"></div>