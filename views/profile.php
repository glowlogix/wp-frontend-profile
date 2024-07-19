<?php
/**
 * @package wp-front-end-profile
 * If you would like to edit this file, copy it to your current theme's directory and edit it there.
 * wpfep will always look in your theme's directory first, before using this default template.
 */

defined('ABSPATH') || exit;

$user_id = get_current_user_id();
$user    = get_userdata($user_id);
$args    = array(
    'post_type' => 'post',
    'author'    => $user_id,
);

$current_user_posts = get_posts($args);
$total              = count($current_user_posts); ?>
<?php
if (! is_user_logged_in()) {
    echo "<div class='wpfep-login-alert'>";
    printf(esc_attr('This page is restricted. Please %s to view this page.', 'wpfep'), wp_loginout('', false));
    echo '</div>';

    return;
}
?>

<div class="wpfep_row">
    <div class="wpfep-profile-sidebar wpfep-columns wpfep-small-6 wpfep-lg-3">
        <?php
            $user = wp_get_current_user();
        if($user) {
            ?>
            <br>
            <img src="<?php echo esc_url(get_avatar_url($user_id)); ?>" alt="avatar" style="width:100%"/>
            <?php
        }
        ?>
        <div class="wpfep_user_details">
            <?php
            if ('' != $user->display_name) {
                echo '<h5>' . esc_html($user->display_name) . '</h5>';
            }
            ?>
            <p><strong><?php esc_attr_e('Email', 'wpfep'); ?>: </strong><?php echo esc_html($user->user_email); ?></p>
            <?php if ('' != $user->user_url) { ?>
            <p><strong><?php esc_attr_e('Website', 'wpfep'); ?>: </strong><?php echo '<a href=' . esc_html($user->user_url) . '>' . esc_html($user->user_url) . '</a>'; ?></p>
            <?php } if ('' != $user->description) { ?>
            <div class="wpfep_user_bio">
                <p>
                    <strong><?php esc_attr_e('User Bio', 'wpfep'); ?> : </strong>
                    <?php echo esc_html($user->description); ?>
                </p>
            </div>
            <?php } ?>
            <a class="btn" href="<?php echo esc_html(get_edit_profile_page()); ?>"><?php esc_attr_e('Edit Profile', 'wpfep'); ?></a>
            <div class="wpfep_end_profile"></div>
        </div>
    </div>

    <div class="wpfep-profile-items">
            <?php
            $wpfep_paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;

            $args = array(
                'post_status'    => 'publish',
                'posts_per_page' => 5,
                'author'         => $user_id,
                'paged'          => $wpfep_paged,
            );

            // The Query.
            $the_query = new WP_Query($args);
            ?>
            <ul class="wpfep-profile-item-ul">
            <h4><?php esc_html('My Posts', 'wpfep'); ?></h4>
            <?php
            // The Loop.
            if ($the_query->have_posts()) {
                while ($the_query->have_posts()) {
                    $the_query->the_post(); ?>
                        <li class="wpfep-profile-item-li wpfep-profile-item-clearfix">
                            <div>
                                <h5 class="wpfep-profile-item-title">
                                    <a href="<?php echo esc_html(get_the_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                </h5>
                                <time class="wpfep-profile-item-time published" datetime="<?php echo esc_html(get_the_time('c')); ?>">
                                    <?php echo get_the_date(); ?>
                                </time>
                                <div class="wpfep-profile-item-summary">
                                    <?php
                                    $excerpt = strip_shortcodes(wp_trim_words(get_the_excerpt(), 15, '...')); ?>
                                    <p><?php esc_html($excerpt); ?></p>
                                </div>
                            </div>
                        </li>
                        <?php
                }
                /* Restore original Post Data */
                wp_reset_postdata();
                do_action('wpfep_profile_pagination', $the_query->max_num_pages);
            } else {
                // no posts found.
                echo '<p>' . esc_attr_e('Post not Found', 'wpfep') . '</p>';
            }
            ?>
        </ul>
    </div>
</div>