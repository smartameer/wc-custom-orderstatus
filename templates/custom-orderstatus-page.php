<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
  <h2><?php _e( 'Custom Order Status', 'wc_custom_orderstatus' ) ?></h2>
  <table class="wp-list-table widefat fixed striped custom-order-status">
    <thead>
      <tr>
        <th class="manage-column column-primary">Name</th>
        <th class="manage-column">Slug</th>
        <th class="manage-column">Active</th>
        <th class="manage-column">Email Enabled</th>
        <th class="manage-column">Style</th>
      <tr>
    </thead>
    <tbody>
      <?php foreach($this->map as $key => $status) { ?>
      <tr>
        <td class="column-primary">
        <mark class="td_order_status status-<?php echo $key ?>" style="background: <?php echo $status['bgcolor']; ?>;color: <?php echo $status['color'];?>">
            <span><?php echo $status['label']; ?></span>
          </mark>
        </td>
        <td><code><?php echo $key; ?></code></td>
        <td>
          <select name="status_active">
            <option <?php echo (int)($status['active']) == 1 ? 'selected' : ''; ?> value="1">Yes</option>
            <option <?php echo (int)($status['active']) == 0 ? 'selected' : ''; ?> value="0">No</option>
          </select>
        </td>
        <td>
          <select name="email_active">
            <option <?php echo (int)($status['email']) == 1 ? 'selected' : ''; ?>  value="1">Yes</option>
            <option <?php echo (int)($status['email']) == 0 ? 'selected' : ''; ?>  value="0">No</option>
          </select>
        </td>
        <td>
          <input type="color" title="Background Color" class="status-color" param="<?php echo $key; ?>" name="background" value="<?php echo $status['bgcolor']; ?>" />
          <input type="color" title="Text Color" class="status-color" param="<?php echo $key; ?>" name="color" value="<?php echo $status['color']; ?>" />
        </td>
      </tr>
      <?php } ?>
    <tbody>
    <tfoot>
      <tr>
        <th class="manage-column column-primary">Name</th>
        <th class="manage-column">Slug</th>
        <th class="manage-column">Active</th>
        <th class="manage-column">Email Enabled</th>
        <th class="manage-column">Style</th>
      <tr>
    </tfoot>
  <table>
</div>
