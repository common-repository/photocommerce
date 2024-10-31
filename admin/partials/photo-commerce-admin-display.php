<?php

/**
 * Provide an admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://vitrion.nl
 * @since      1.0.0
 *
 * @package    Photo_Commerce
 * @subpackage Photo_Commerce/admin/partials
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PhotoCommerce</title>
</head>
<body>
<?php
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page, only admins are allowed to setup the app.', 'photo-commerce'));
}

$user_id = get_current_user_id();
$current_user = wp_get_current_user();
$exists = WP_Application_Passwords::application_name_exists_for_user($user_id, 'photo-commerce');
$application_password = '';
$uuid = get_option('photo-commerce-' . $user_id, false);

if (!$exists && isset($_GET['generate'])) {
    $application_password = WP_Application_Passwords::create_new_application_password($user_id, array('name' => 'photo-commerce'));
    update_option('photo-commerce-' . $user_id, $application_password[1]['uuid']);
}

if ($exists && isset($_GET['reset']) && $uuid) {
    $reset = WP_Application_Passwords::delete_application_password($user_id, $uuid);
    if ($reset) {
        update_option('photo-commerce-' . $user_id, '');

        exit(wp_redirect(admin_url('admin.php?page=photo_commerce')));
    }
}


?>

<div class="col-lg-8 mx-auto p-3 py-md-5">
    <header class="d-flex align-items-center pb-3 mb-5 border-bottom">
        <a href="/" class="d-flex align-items-center text-dark text-decoration-none font-weight-bold">
            <span class="fs-4 text-bold text-primary">Photo Commerce</span>
        </a>
    </header>

    <main>

        <?php
        if ($exists) {

            ?>
            <h1><?php _e('Setup', 'photo-commerce') ?></h1>
            <p class="fs-5 col-md-8"><?php _e("Welcome back to the PhotoCommerce setup page. You have already generated an application password for your mobile app connection. If you need to revoke your current password, simply click the 'Revoke Application Password' button below. This will immediately disable your current password, and you will be taken back to the default setup screen.", 'photo-commerce') ?></p>

        <?php
        require_once 'revoke_key.php';

        } else {

        if (isset($_GET['generate']) && !$exists){
        ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Applications Password generated successfully. Make sure to copy your new keys now as the applications password will be hidden once you leave this page.', 'photo-commerce'); ?></p>
            </div>
            <h1><?php _e('Setup', 'photo-commerce') ?></h1>
            <p class="fs-5 col-md-8"><?php _e('Congratulations! You have successfully generated a new application password for PhotoCommerce. To connect your mobile app to your WordPress website, you can use either the QR code or the manual code shown below.', 'photo-commerce') ?></p>


            <div id="qrcode"></div>
            <script>
                new QRCode(document.getElementById("qrcode"), '<?php echo base64_encode($current_user->user_login . ":" . $application_password[0]);?>');
            </script>
        <br/>
            <p><i>Manual:</i></p>
            <p>        <?php echo base64_encode($current_user->user_login . ":" . $application_password[0]);; ?></p>
        <?php
        require_once 'revoke_key.php';
        ?>

            <p class="font-italic col-md-8"><?php _e('Please note that this password is for your own use only, and should not be shared with anyone else. If you suspect that your password has been compromised, please generate a new one immediately', 'photo-commerce') ?></p>
        <?php
        } else {
        ?>
            <h1><?php _e('Setup', 'photo-commerce') ?></h1>
            <p class="fs-5 col-md-8"><?php _e("Welcome to the PhotoCommerce setup page. To connect your mobile app to your WordPress website, you'll need to generate an application password. Click the 'Generate Application Password' button below to create a new password. This password will be used to authenticate the connection between the mobile app and your website. ", 'photo-commerce') ?></p>
            <?php
            require_once 'generate_key.php';
            ?>
            <?php
        }
        }
        ?>
        <p class="font-italic col-md-8"><?php _e("Please note that your data is secure and private, and is only sent to your own WordPress site. If you have any questions or need assistance, please contact our support team.", 'photo-commerce') ?></p>

    </main>
    <footer class="pt-5 my-5 text-muted border-top">
        Created by Vitrion<br/>
        <a href="mailto:info@photo-commerce.com">Contact Support</a>
    </footer>
</div>
<script>
    function redirectToResetUrl() {
        let url = new URL(window.location.href);
        url.searchParams.append("reset", 'true');
        window.location.href = url.toString();
    }

    function generateApplicationPassword () {
        let url = new URL(window.location.href);
        url.searchParams.append("generate", 'true');
        window.location.href = url.toString();
    }
</script>
<style>

</style>
</body>
</html>

