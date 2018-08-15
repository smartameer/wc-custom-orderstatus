<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$queryurl = parse_url(html_entity_decode(esc_url( add_query_arg(array()))));
parse_str( $queryurl['query'], $getvar);
$createform = true;
$action = 'orderstatus_create';
$slug = '';
$statusdata = array('label' => '', 'email' => true, 'background' => '#dddddd', 'color' => '#000000');
if (isset($getvar['id'])) {
    if (isset($this->map[$getvar['id']])) {
        $statusdata = $this->map[$getvar['id']];
        $createform = false;
        $action = 'orderstatus_update';
        $slug = $getvar['id'];
    } else {
        $_GET['error'] = 'invalid_status_code';
    }
}

?>
<div class="wrap">
  <h1>
    <?php _e( 'Custom Order Status', 'wc_custom_orderstatus' ) ?>
    <a class="button button-default" href="<?php echo esc_url(add_query_arg(array('page' => 'order-status'), admin_url('admin.php'))); ?>" title="New">New</a>
  </h1>
  <?php if (isset($_GET['error'])) {
     echo '<div class="error"><p>';
     if ($_GET['error'] == 'duplicate_order_status') {
         echo 'An order status with same name already exists.';
     } else if ($_GET['error'] == 'invalid_status_code') {
         echo 'No Status exist which you have requested for.';
     }
     echo '</p></div>';
  } ?>
  <div class="row custom-order-status">
    <div class="three">
      <table class="wp-list-table widefat fixed striped">
        <?php if (sizeof($this->map) > 0) { ?>
        <thead>
          <tr>
            <th class="manage-column column-primary">Name</th>
            <th class="manage-column">Email Enabled</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($this->map as $key => $status) { ?>
          <tr class="<?php echo ($slug == $key) ? 'active' : ''; ?>">
            <td class="column-primary">
              <a href="<?php echo esc_url(add_query_arg(array('id' => $key))); ?>">
                <mark class="td_order_status status-<?php echo $key ?>" style="background: <?php echo $status['background']; ?>;color: <?php echo $status['color'];?>">
                  <span><?php echo $status['label']; ?></span>
                </mark>
              </a>
            </td>
            <td>
              <span class="status"> <?php echo (int)($status['email']) == 1 ? 'Enabled' : 'Disabled'; ?></span>
            </td>
          </tr>
          <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <th class="manage-column column-primary">Name</th>
            <th class="manage-column">Email Enabled</th>
          </tr>
        </tfoot>
        <?php } ?>
      </table>
    </div>
    <div class="one">
      <h2><span class="type"><?php echo $createform ? 'Add' : 'Update'; ?></span> custom order status</h2>
      <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" name="order-status-form">
        <input type="hidden" name="action" value="<?php echo $action; ?>" />
        <input type="hidden" name="status_key" value="<?php echo $slug; ?>" />
        <?php wp_nonce_field($action, 'orderstatus_nonce');?>
        <table class="form-table">
          <tr>
            <th scope="row"><label><?php _e('Name') ?></label></th>
            <td>
              <input name="name" type="text" placeholder="Status Name" autocomplete="off" value="<?php echo $statusdata['label']; ?>"/>
              <br class="clear"/>
              <br class="clear"/>
              <mark class="td_order_status form-order-status" style="<?php echo 'background:' . $statusdata['background'] .';color:' . $statusdata['color']; ?>">
                <span><?php echo !empty($statusdata['label']) ? $statusdata['label'] : 'Status Name'; ?></span>
              </mark>
            </td>
          </tr>
          <tr>
            <th scope="row"><label><?php _e('Background Color') ?></label></th>
            <td>
              <input name="background" class="status-color" type="color" value="<?php echo $statusdata['background']; ?>" />
            </td>
          </tr>
          <tr>
            <th scope="row"><label><?php _e('Text Color') ?></label></th>
            <td>
              <input name="color" class="status-color" type="color" value="<?php echo $statusdata['color']; ?>" />
            </td>
          </tr>
          <tr>
            <th scope="row"><label><?php _e('Send Email (to customer)') ?></label></th>
            <td>
              <label for="emailstatus">
                <input id="emailstatus" name="email" type="checkbox" value="1" <?php echo $statusdata['email'] ? 'checked="checked"' : ''; ?> />
              <label>
            </td>
          </tr>
        </table>
        <br/>
        <hr/>
        <br/>
        <input name="submit" type="submit" class="button button-primary button-large" value="<?php echo $createform ? 'Add' : 'Update'; ?>">
      </form>
    </div>
  </div>
</div>
