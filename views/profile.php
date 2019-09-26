<?php
/*
  If you would like to edit this file, copy it to your current theme's directory and edit it there.
  wpfep will always look in your theme's directory first, before using this default template.
 */
   $user_id = get_current_user_id();
   $user = get_userdata($user_id);
?>
 <?php
    if ( !is_user_logged_in()) {
      echo "<div class='wpfep-login-alert'>";
        printf( __( "This page is restricted. Please %s to view this page.", 'wpptm' ), wp_loginout( '', false ) );
        echo "</div>";
       return;
    }
    ?>
<div class="wpfep-profile-card">
    <?php
            $user = wp_get_current_user();
            if ( $user ) {
        ?>
        <br>
        <img src="<?php echo esc_url( get_avatar_url( $user->ID ) ); ?>" alt="avatar" style="width:100%"/>
        <?php } ?>
    <div class="wpfep_user_details">
            <?php 
                if($user->display_name != '') {

                    echo '<h2>' .$user->display_name. '</h2>';
                }
            ?>
        <p class="title"><?php echo implode(', ', $user->roles);?></p>
        <p><?php echo $user->user_email;?></p>
        <?php  if($user->user_url != '') {?>
        <p><?php echo "<a href=".$user->user_url.">".$user->user_url."</a>";?></p>
        <?php } if($user->description != ''){?>
        <div class="wpfep_user_bio">
            <h3 class="wpfep_label_user_bio">User Bio</h3>
            <p>
                <?php echo  $user->description ;?>
            </p>

        </div>
        <?php }?>
        <div class="wpfep_end_profile"></div>
    </div>
</div>

