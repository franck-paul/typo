<?php
/**
 * @brief typo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('DC_CONTEXT_ADMIN')) {exit;}

// Getting current parameters
$typo_active      = (boolean) $core->blog->settings->typo->typo_active;
$typo_entries     = (boolean) $core->blog->settings->typo->typo_entries;
$typo_comments    = (boolean) $core->blog->settings->typo->typo_comments;
$typo_dashes_mode = (integer) $core->blog->settings->typo->typo_dashes_mode;

// Saving new configuration
if (!empty($_POST['saveconfig'])) {
    try
    {
        $core->blog->settings->addNamespace('typo');

        $typo_active      = (empty($_POST['active'])) ? false : true;
        $typo_entries     = (empty($_POST['entries'])) ? false : true;
        $typo_comments    = (empty($_POST['comments'])) ? false : true;
        $typo_dashes_mode = (integer) $_POST['dashes_mode'];
        $core->blog->settings->typo->put('typo_active', $typo_active, 'boolean');
        $core->blog->settings->typo->put('typo_entries', $typo_entries, 'boolean');
        $core->blog->settings->typo->put('typo_comments', $typo_comments, 'boolean');
        $core->blog->settings->typo->put('typo_dashes_mode', $typo_dashes_mode, 'integer');
        $core->blog->triggerBlog();
        $msg = __('Configuration successfully updated.');
    } catch (Exception $e) {
        $core->error->add($e->getMessage());
    }
}
?>
<html>
<head>
  <title><?php echo __('Typo'); ?></title>
</head>

<body>
<?php
echo dcPage::breadcrumb(
    [
        html::escapeHTML($core->blog->name) => '',
        __('Typographic replacements')      => ''
    ]);

if (!empty($msg)) {
    dcPage::success($msg);
}

$dashes_mode_options = [
    1 => __('"--" for em-dashes; no en-dash support (default)'),
    2 => __('"---" for em-dashes; "--" for en-dashes'),
    3 => __('"--" for em-dashes; "---" for en-dashes')
];
?>

<form method="post" action="plugin.php">
  <p>
    <?php echo form::checkbox('active', 1, $typo_active); ?>
    <label class="classic" for="active"><?php echo __('Enable typographic replacements for this blog'); ?></label>
  </p>

  <h3><?php echo __('Options'); ?></h3>
  <p>
    <?php echo form::checkbox('entries', 1, $typo_entries); ?>
    <label class="classic" for="entries"><?php echo __('Enable typographic replacements for entries'); ?></label>
  </p>
  <p>
    <?php echo form::checkbox('comments', 1, $typo_comments, '', '', false, 'aria-describedby="trackbacks"'); ?>
    <label class="classic" for="comments"><?php echo __('Enable typographic replacements for comments'); ?></label>
  </p>
  <p class="form-note" id="trackbacks"><?php echo __('Excluding trackbacks'); ?></p>

<?php
echo '<fieldset class="fieldset"><legend>' . __('Dashes replacement mode') . '</legend>';
$i = 0;
foreach ($dashes_mode_options as $k => $v) {
    echo '<p><label for="dashes_mode-' . $i . '" class="classic">' .
    form::radio(['dashes_mode', 'dashes_mode-' . $i], $k, $typo_dashes_mode == $k) . ' ' . $v . '</label></p>';
    $i++;
}
echo '</fieldset>';
?>

  <p><input type="hidden" name="p" value="typo" />
    <?php echo $core->formNonce(); ?>
    <input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
  </p>
</form>

</body>
</html>
