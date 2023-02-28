<?php
/**
 * Based on Super Order 2.0
 * By Frank Koehl - PM: BlindSide (original author)
 *
 * Super Orders Updated by:
 * ~ JT of GTICustom
 * ~ C Jones Over the Hill Web Consulting (http://overthehillweb.com)
 * ~ Loose Chicken Software Development, david@loosechicken.com
 * ~ Steph - JSWeb Ltd (Updated for Zen Cart 1.5.8)
 *
 * DESCRIPTION: Modifies admin/packingslip.php adding the following
 * features:
 * ~ Ability to display a special "split" packingslip when an order
 *   has been split. (Split orders feature is accessible through the
 *   order details page)
 * ~ Modifies alignment of address info
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Sep 17 Modified in v1.5.8 $
 */
require_once('includes/application_top.php');

// To override the $show_* values or $attr_img_width, see 
// https://docs.zen-cart.com/user/admin/site_specific_overrides/
if (!isset($show_product_images)) {
  $show_product_images = true;
}
if (!isset($show_attrib_images)) {
  $show_attrib_images = true;
}
$img_width = defined('IMAGE_ON_INVOICE_IMAGE_WIDTH') ? (int) IMAGE_ON_INVOICE_IMAGE_WIDTH : '100';
if (!isset($attr_img_width)) {
  $attr_img_width = '25';
}

require_once(DIR_WS_CLASSES . 'currencies.php');
require_once(DIR_WS_CLASSES . 'super_order.php');

require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'order.php');
if (isset($_GET['oID'])) {
  $oID = zen_db_prepare_input($_GET['oID']);
  $batched = false;
  $batch_item = 0;
} else {
  $batched = true;
}

$order = new order($oID);
$so = new super_order($oID);
$currencies = new currencies();

$reverse_split = (($_GET['reverse_count'] % 2) ? 'odd' : 'even');
$_GET['reverse_count']++;
$split = $_GET['split'];

// prepare order-status pulldown list
$orders_statuses = array();
$orders_status_array = array();
$orders_status = $db->Execute("SELECT orders_status_id, orders_status_name
                               FROM " . TABLE_ORDERS_STATUS . "
                               WHERE language_id = " . (int) $_SESSION['languages_id']);
foreach ($orders_status as $order_status) {
  $orders_statuses[] = array(
    'id' => $order_status['orders_status_id'],
    'text' => $order_status['orders_status_name'] . ' [' . $order_status['orders_status_id'] . ']'
  );
  $orders_status_array[$order_status['orders_status_id']] = $order_status['orders_status_name'];
}

$show_customer = false;
if ($order->billing['name'] != $order->delivery['name']) {
  $show_customer = true;
}
if ($order->billing['street_address'] != $order->delivery['street_address']) {
  $show_customer = true;
}
?>

<?php
if ($batched == false) {
  $page_title = HEADER_PACKINGSLIP . (int) $oID;
} else {
  $page_title = HEADER_PACKINGSLIPS;
}

if (($batched == false) or ($batched == true and $batch_item == 1)) { ?>
  <!doctype html>
  <html <?php echo HTML_PARAMS; ?>>

  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <style>
      @media screen {
        div.form-separator {
          border-style: none none solid none;
          border-bottom: thick dotted #000000;
        }

        img {
          vertical-align: middle;
        }
      }

      @media print {
        div.form-separator {
          display: none;
        }
      }
    </style>
  </head>

  <body>
    <?php
}

if (($batched == true) and ($batch_item > 1) and (($batch_item % $forms_per_page) == 0)) { ?>
    <div style="page-break-before:always"><span style="display: none;">&nbsp;</span></div>
    <br />
    <div class="form-separator"></div>
    <?php
} ?>
  <div class="container">
    <!-- body_text //-->
    <table class="table">
      <tr>
        <td class="pageHeading">
          <?php echo nl2br(STORE_NAME_ADDRESS); ?>
        </td>
        <td class="pageHeading text-right">
          <a href="<?php echo FILENAME_ORDERS_PACKINGSLIP . '?' . zen_get_all_get_params(); ?>"><?php echo zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT) ?></a>
          <br>
          <?php echo TEXT_PACKING_SLIP; ?>
          <br>
          <?php echo ENTRY_ORDER_ID . (int) $oID; ?>
        </td>
      </tr>
    </table>
    <div>
      <?php echo zen_draw_separator(); ?>
    </div>
    <?php
    $additional_content = false;
    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_PACKINGSLIP_ADDITIONAL_DATA_TOP', $oID, $additional_content);
    if ($additional_content !== false) {
      ?>
      <table class="table">
        <tr>
          <td class="main additional_data" colspan="2">
            <?php echo $additional_content; ?>
          </td>
        </tr>
      </table>
      <?php
    }
    ?>
    <table class="table">
      <?php
      if ($show_customer == true) {
        ?>
        <tr>
          <td class="main" colspan="2"><b>
              <?php echo ENTRY_CUSTOMER; ?>
            </b></td>
        </tr>
        <tr>
          <td class="main" colspan="2">
            <?php echo zen_address_format($order->customer['format_id'], $order->customer, 1, '', '<br>'); ?>
          </td>
        </tr>
      <?php } ?>
      <tr>
        <td style="border: none">
          <table>
            <tr>
              <td class="main"><b>
                  <?php echo ENTRY_SOLD_TO; ?>
                </b></td>
            </tr>
            <tr>
              <td class="main">
                <?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, '', '<br>'); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?>
              </td>
            </tr>
            <tr>
              <td class="main">
                <?php echo ENTRY_TELEPHONE_NUMBER . ' ' . $order->customer['telephone']; ?>
              </td>
            </tr>
            <tr>
              <td class="main">
                <?php echo '<a href="mailto:' . $order->customer['email_address'] . '">' . $order->customer['email_address'] . '</a>'; ?>
              </td>
            </tr>
          </table>
        </td>
        <td style="border: none">
          <table>
            <tr>
              <td class="main"><b>
                  <?php echo ENTRY_SHIP_TO; ?>
                </b></td>
            </tr>
            <tr>
              <td class="main">
                <?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <?php
    // Trim shipping details
    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
      if ($order->totals[$i]['class'] == 'ot_shipping') {
        $shipping_method = $order->totals[$i]['title'];
        break;
      }
    } ?>
    <table>
      <tr>
        <td class="main"><strong>
            <?php echo ENTRY_DATE_PURCHASED; ?>
          </strong></td>
        <td class="main">
          <?php echo zen_date_long($order->info['date_purchased']); ?>
        </td>
      </tr>
      <tr>
        <td class="main"><strong>
            <?php echo ENTRY_PAYMENT_METHOD; ?>
          </strong></td>
        <td class="main">
          <?php echo $order->info['payment_method']; ?>
        </td>
      </tr>
      <tr>
        <td class="main"><strong>
            <?php echo ENTRY_SHIPPING_METHOD; ?>
          </strong></td>
        <td class="main">
          <?php echo $shipping_method; ?>
        </td>
      </tr>
    </table>

    <div>
      <?php echo zen_draw_separator('pixel_trans.gif', '', '10'); ?>
    </div>

    <table class="table table-striped">
      <thead>
        <tr class="dataTableHeadingRow">
          <?php if ($show_product_images) { ?>
            <th class="dataTableHeadingContent" style="width: <?php echo (int) $img_width . 'px'; ?>">&nbsp;</th>
          <?php } ?>
          <th class="dataTableHeadingContent" style="width: 10%; text-align: right;">
            <?php echo TABLE_HEADING_QTY; ?>
          </th>
          <th class="dataTableHeadingContent" style="width: 70%;">
            <?php echo TABLE_HEADING_PRODUCTS; ?>
          </th>
          <th class="dataTableHeadingContent">
            <?php echo TABLE_HEADING_PRODUCTS_MODEL; ?>
          </th>
          <?php
          // -----
          // Additional column-headings can be added.
          //
          // A watching observer can provide an associative array in the following format (for the products' listing ONLY):
          //
          // $extra_headings = array(
          //     array(
          //       'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
          //       'text' => $value
          //     ),
          // );
          //
          // Observer notes:
          // - Be sure to check that the $p2/$extra_headings value is specifically (bool)false before initializing, since
          //   multiple observers might be injecting content!
          // - If heading-columns are added, be sure to add the associated data columns, too, via the
          //   'NOTIFY_ADMIN_PACKINGSLIP_DATA' notification.
          //
          $extra_headings = false;
          $zco_notifier->notify('NOTIFY_ADMIN_PACKINGSLIP_HEADING', '', $extra_headings);
          if (is_array($extra_headings)) {
            foreach ($extra_headings as $heading_info) {
              $align = (isset($heading_info['align'])) ? (' text-' . $heading_info['align']) : '';
              ?>
              <th class="dataTableHeadingContent<?php echo $align; ?>"><?php echo $heading_info['text']; ?></th>
              <?php
            }
          }
          ?>
        </tr>
      </thead>
      <tbody>
        <?php
        /*
         * Notifier to allow packing slip to be sorted to required order
         *
         * Set $sort_order to the order->products array counter in the sequence you require the invoice to be displayed
         */
        $sort_order = false;
        $zco_notifier->notify('NOTIFY_ADMIN_PACKINGSLIP_SORT_DISPLAY', $order->products, $sort_order);
        for ($ii = 0, $n = sizeof($order->products); $ii < $n; $ii++) {
          if (is_array($sort_order)) {
            $i = $sort_order[$ii];
          } else {
            $i = $ii;
          }
          $product_name = $order->products[$i]['name'];
          ?>
          <tr class="dataTableRow">
            <?php if ($show_product_images) { ?>
              <td class="dataTableContent">
                <?php echo zen_image(DIR_WS_CATALOG . DIR_WS_IMAGES . zen_get_products_image($order->products[$i]['id']), zen_output_string($product_name), (int) $img_width); ?>
              </td>
            <?php } ?>

            <td class="dataTableContent text-right">
              <?php echo zen_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK);
              echo '&nbsp;' . $order->products[$i]['qty']; ?>&nbsp;
            </td>
            <?php echo $product_name; ?>
            <?php
            if (isset($order->products[$i]['attributes']) && (($k = sizeof($order->products[$i]['attributes'])) > 0)) {
              ?>
              <ul>
                <?php
                for ($j = 0; $j < $k; $j++) {
                  $attribute_name = $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value']));
                  $attribute_image = zen_get_attributes_image($order->products[$i]['id'], $order->products[$i]['attributes'][$j]['option_id'], $order->products[$i]['attributes'][$j]['value_id']);
                  ?>
                  <li>
                    <?php

                    if ($show_attrib_images && !empty($attribute_image)) {
                      echo zen_image(DIR_WS_CATALOG . DIR_WS_IMAGES . $attribute_image, zen_output_string($attribute_name), (int) $attr_img_width);
                    }
                    ?>
                    <small>
                      <i>
                        <?php echo $attribute_name; ?>
                      </i>
                    </small>
                  </li>
                  <?php
                }
                ?>
              </ul>
              <?php
            }
            ?>
            </td>
            <td class="dataTableContent">
              <?php echo $order->products[$i]['model']; ?>
            </td>
            <?php
            // -----
            // Additional fields can be added into columns.
            //
            // A watching observer can provide an associative array in the following format:
            //
            // $extra_data = array(
            //     array(
            //       'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
            //       'text' => $value
            //     ),
            // );
            //
            // Observer notes:
            // - Be sure to check that the $p2/$extra_data value is specifically (bool)false before initializing, since
            //   multiple observers might be injecting content!
            // - If heading-columns are added, be sure to add the associated header columns, too, via the
            //   'NOTIFY_ADMIN_PACKINGSLIP_HEADING' notification.
            //
            $extra_data = false;
            $zco_notifier->notify('NOTIFY_ADMIN_PACKINGSLIP_DATA', $order->products[$i]['id'], $extra_data);
            if (is_array($extra_data)) {
              foreach ($extra_data as $data_info) {
                $align = (isset($data_info['align'])) ? (' text-' . $data_info['align']) : '';
                ?>
                <td class="dataTableContent<?php echo $align; ?>"><?php echo $data_info['text']; ?></td>
                <?php
              }
            }
            ?>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
    <?php
    $parent_child = $db->Execute("SELECT split_from_order, is_parent
                                         FROM " . TABLE_ORDERS . "
                                         WHERE orders_id = '" . $oID . "'");

    if ($parent_child->fields['split_from_order']):

      $so = new super_order($parent_child->fields['split_from_order']);
      $order = new order($parent_child->fields['split_from_order']);

      for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) { ?>
        <tr class="dataTableRow">
          <?php
          if ($show_product_images) { ?>
            <td class="dataTableContent">
              <?php echo zen_image(DIR_WS_CATALOG . DIR_WS_IMAGES . zen_get_products_image($order->products[$i]['id']), zen_output_string($product_name), (int) $img_width); ?>
            </td>
            <?php
          } ?>
          <td class="dataTableContent text-right">
            <?php echo zen_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS);
            echo '&nbsp;' . $order->products[$i]['qty']; ?>&nbsp;
          </td>
          <td class="dataTableContent">
            <?php
            echo $product_name;
            if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) { ?>
              <ul>
                <?php
                for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
                  $attribute_name = $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value']));
                  $attribute_image = zen_get_attributes_image($order->products[$i]['id'], $order->products[$i]['attributes'][$j]['option_id'], $order->products[$i]['attributes'][$j]['value_id']); ?>
                  <li>
                    <?php
                    if ($show_attrib_images && !empty($attribute_image)) {
                      echo zen_image(DIR_WS_CATALOG . DIR_WS_IMAGES . $attribute_image, zen_output_string($attribute_name), (int) $attr_img_width);
                    } ?>
                    <small>
                      <i>
                        <?php echo $attribute_name; ?>
                      </i>
                    </small>
                  </li>
                  <?php
                } ?>
              </ul>
              <?php
            } ?>
          </td>
          <td class="dataTableContent">
            <?php echo $order->products[$i]['model']; ?>
          </td>
        </tr>
        <?php
      }
    endif; ?>
    </tbody>
    </table>

    <?php if (ORDER_COMMENTS_PACKING_SLIP > 0) { ?>
      <table class="table table-condensed">
        <thead>
          <tr>
            <th class="text-center"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></th>
            <th class="text-center"><strong><?php echo TABLE_HEADING_STATUS; ?></strong></th>
            <th class="text-center"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></th>
          </tr>
        </thead>
        <tbody>
          <?php
          $orders_history = $db->Execute("SELECT orders_status_id, date_added, customer_notified, comments
                                            FROM " . TABLE_ORDERS_STATUS_HISTORY . "
                                            WHERE orders_id = " . zen_db_input($oID) . "
                                            AND customer_notified >= 0
                                            ORDER BY date_added");

          if ($orders_history->RecordCount() > 0) {
            $count_comments = 0;
            foreach ($orders_history as $order_history) {
              $count_comments++;
              ?>
              <tr>
                <td class="text-center"><?php echo zen_datetime_short($order_history['date_added']); ?></td>
                <td><?php echo $orders_status_array[$order_history['orders_status_id']]; ?></td>
                <td class="text-left">
                <?php 
                if (empty($order_history['comments'])) {
                  echo TEXT_NONE;
                } else {
                  if ($count_comments == 1) {
                    echo nl2br(zen_output_string_protected($order_history['comments'])); 
                  } else {
                    echo $order_history['comments']; 
                  }
                }
                ?>
                &nbsp;
                </td>
                </tr>
                <?php
                if (ORDER_COMMENTS_PACKING_SLIP == 1 && $count_comments >= 1) {
                  break;
                }
              }
            } else {
              ?>
              <tr>
                <td colspan="3"><?php echo TEXT_NO_ORDER_HISTORY; ?></td>
              </tr>
            <?php
          }
          ?>
        </tbody>
      </table>
    <?php } // order comments ?>
    <?php
    $additional_content = false;
    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_PACKINGSLIP_ADDITIONAL_DATA_BOTTOM', $oID, $additional_content);
    if ($additional_content !== false) {
      ?>
      <table class="table">
        <tr><td class="main additional_data" colspan="2"><?php echo $additional_content; ?></td></tr>
      </table>
      <?php
    }
    ?>

    <?php
    if ($_GET['split'] || $parent_child->fields['split_from_order']) { ?>
      <table>
        <div>
          <?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?>
        </div>
        <tr>
          <td align="right">
            <table border="0" cellpadding="2" cellspacing="0">
              <tr>
                <td class="smallText">
                  <?php echo zen_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK); ?>
                </td>
                <td class="smallText">
                  <?php echo ENTRY_PRODUCTS_INCL; ?>
                </td>
              </tr>
              <tr>
                <td class="smallText">
                  <?php echo zen_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS); ?>
                </td>
                <td class="smallText">
                  <?php echo ENTRY_PRODUCTS_EXCL; ?>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <?php
    } ?>
    <!-- body_text_eof //-->
  </div>
  <?php
  if (($batched == false) or (($batched == true) and ($batch_item == $number_of_orders))) { ?>
  </body>

  </html>
  <?php
  }
  require_once(DIR_WS_INCLUDES . 'application_bottom.php'); ?>