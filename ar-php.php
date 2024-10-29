<?php
/*
Plugin Name: ar-php
Plugin URI: http://wordpress.org/extend/plugins/ar-php
Description: Adds Ar-PHP project functionality in TinyMCE editor.
Version: 0.7
Author: Khaled Al Shmaa and Khaled Al Hourani
Author URI: http://www.ar-php.org/
*/

if(!class_exists('ArPHP')) {

class ArPHP {
  function ArPHP() { //constructor
    global $wp_version;

    // Add Settings Panel
    add_action('admin_menu', array($this, 'addPanel'));
    // Update Settings on Save
    if ($_POST['action'] == 'arphp_update') {
      add_action('init', array($this, 'saveSettings'));
    }

    // Default settings for arphp plugin
    add_action('init', array($this, 'defaultSettings'));

    // version check
    if ($wp_version < 2.7) {
      add_action('admin_notices', array($this, 'versionWarning'));
    }
  }

  // Display a warning if plugin is installed on a WordPress lower than 2.6
  function versionWarning() {
    global $wp_version;

    echo "
      <div id='arphp-warning' class='updated fade-ff0000'>
        <p><strong>" . 
        __('Ar PHP is only compatible with WordPress v2.6 and up. You are currently using WordPress v', 'arphp') . 
        $wp_version . 
        "</strong></p>
      </div>";
  }

  // Add action to build an arphp page
  function addPanel() {
    //Add the Settings and User Panels
    add_options_page('Ar-PHP', 'Ar-PHP', 10, 'Ar-PHP', array($this, 'arphpSettings'));
  }

  // Default settings for this plugin
  function defaultSettings () {
    $default = array(
      'date' => '0',
      'hijri_date' => '0',
      'spell_numbers' => '0',
      'convert_layout' => '0',
      'transliterate' => '0'
    );

    // Set defaults if no values exist
    if (!get_option('arphp')) {
      add_option('arphp', $default);
    }
    else { // Set Defaults if new value does not exist
      $arphp = get_option('arphp');
      // Check to see if all defaults exists in option table's record, and assign values to empty keys
      foreach($default as $key => $val) {
        if (!$arphp[$key]) {
          $arphp[$key] = $val;
          $new = true;
        }
      }

      if ($new) {
        update_option('arphp', $arphp);
      }
    }

    // Run plugin script and buttons
    if (!current_user_can ('edit_posts') && !current_user_can('edit_pages')) {
      return;
    }
    if (get_user_option ('rich_editing') == 'true') {
      add_filter('mce_external_plugins', array($this, 'tinymce_arphp_plugin'));
      add_filter('mce_buttons', array($this, 'tinymce_arphp_buttons'));
    }
  }

  // arphp page settings
  function arphpSettings() {
    // Get options from option table
    $arphp = get_option('arphp');

    // Display message if any
    if ($_POST['notice']) {
      echo '<div id="message" class="updated fade"><p><strong>' . $_POST['notice'] . '.</strong></p></div>';
    }
?>

    <link rel="stylesheet" href="<?php echo WP_PLUGIN_URL; ?>/ar-php/css/style.css" type="text/css" />

    <div class="wrap" dir="ltr">
      <br/>
      <h2><?php _e('Ar PHP Settings', 'arphp') ?></h2>

      <form method="post" action="">

        <p><?php _e('Check the buttons you would like to show in your editor.', 'arphp');?></p>
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <td>
                <label class="item">
                  <input type="checkbox" name="arphp_date" value="1" <?php if ($arphp['date']) echo 'checked="checked"';?> /> 
                  <img src="<?php echo WP_PLUGIN_URL; ?>/ar-php/img/a_calendar.gif" /> <?php _e('Date', 'arphp');?>
                </label>

                <label class="item">
                  <input type="checkbox" name="arphp_hijri_date" value="1" <?php if ($arphp['hijri_date']) echo 'checked="checked"';?> /> 
                  <img src="<?php echo WP_PLUGIN_URL; ?>/ar-php/img/h_calendar.gif" /> <?php _e('Hijri Date', 'arphp');?>
                </label>

                <label class="item">
                  <input type="checkbox" name="arphp_spell_numbers" value="1" <?php if ($arphp['spell_numbers']) echo 'checked="checked"';?> /> 				<img src="<?php echo WP_PLUGIN_URL; ?>/ar-php/img/numbers.gif" /> <?php _e('Spell Numbers', 'arphp');?>
                </label>

                <label class="item">
                  <input type="checkbox" name="arphp_convert_layout" value="1" <?php if ($arphp['convert_layout']) echo 'checked="checked"';?> /> 				<img src="<?php echo WP_PLUGIN_URL; ?>/ar-php/img/keyboard.gif" /> <?php _e('Convert Layout', 'arphp');?>
                </label>

                <label class="item">
                  <input type="checkbox" name="arphp_transliterate" value="1" <?php if ($arphp['transliterate']) echo 'checked="checked"';?> /> 				<img src="<?php echo WP_PLUGIN_URL; ?>/ar-php/img/terms.gif" /> <?php _e('Transliterate', 'arphp');?>
                </label>
              </td>
            </tr>
          </tbody>
        </table>

        <p class="submit"><input name="Submit" value="<?php _e('Save Changes', 'arphp');?>" type="submit" />
        <input name="action" value="arphp_update" type="hidden" />
      </form>
    </div>

<?php
  }

  // Save the new settings of arphp options
  function saveSettings() {
    // Get the new values from the submitted POST
    $update['date']	= $_POST['arphp_date'];
    $update['hijri_date'] = $_POST['arphp_hijri_date'];
    $update['spell_numbers'] = $_POST['arphp_spell_numbers'];
    $update['convert_layout'] = $_POST['arphp_convert_layout'];
    $update['transliterate'] = $_POST['arphp_transliterate'];

    // Save the new settings to option table's record
    update_option('arphp', $update);

    // Display success message
    $_POST['notice'] = __('Settings Saved', 'arphp');
  }


  function tinymce_arphp_plugin($plugin_array) {
    $plugin_array['arphp'] = WP_PLUGIN_URL.'/ar-php/editor_plugin.js';
    return $plugin_array;
  }

  function tinymce_arphp_buttons($buttons) {
    // Get options from option table
    $arphp = get_option('arphp');

    array_push($buttons, 'separator');

    if ($arphp['date']) {
      array_push($buttons, 'ardate');
    }
    if ($arphp['hijri_date']) {
      array_push($buttons, 'hijri');
    }
    if ($arphp['spell_numbers']) {
      array_push($buttons, 'arnumber');
    }
    if ($arphp['convert_layout']) {
      array_push($buttons, 'arkeyboard');
    }
    if ($arphp['transliterate']) {
      array_push($buttons, 'enterms');
    }

    return $buttons;
  }

} // End arphp class

} // End the BIG if


# Run The Plugin! DUH :\
if (class_exists('ArPHP')) {
  $arphp = new ArPHP();
}