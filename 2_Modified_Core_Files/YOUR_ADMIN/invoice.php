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
 * DESCRIPTION: Modifies admin/invoice.php, adds amount paid &
 * balance due values based on super_order class calculations. Also
 * includes the option to display a tax exemption number, configurable
 * from the admin.
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Sep 17 Modified in v1.5.8 $
 */
require_once('includes/application_top.php');
// To override the $show_* or $attr_img_width values, see 
// https://docs.zen-cart.com/user/admin/site_specific_overrides/
if (!isset($show_product_images)) {
  $show_product_images = true;
}
if (!isset($show_attrib_images)) {
  $show_attrib_images = true;
}
$img_width = defined('IMAGE_ON_INVOICE_IMAGE_WIDTH') ? (int)IMAGE_ON_INVOICE_IMAGE_WIDTH : '100';
if (!isset($attr_img_width)) {
  $attr_img_width = '25';
}

require_once(DIR_WS_CLASSES . 'currencies.php');
require_once(DIR_WS_CLASSES . 'super_order.php');

require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'order.php');
if(isset($_GET['oID'])) {
  $oID = zen_db_prepare_input($_GET['oID']);
  $batched = false;
  $batch_item = 0;
} else {
  $batched = true;
}

$order = new order($oID);
$so = new super_order($oID);
$currencies = new currencies();
$display_phone = !(STORE_PHONE == '');
$display_fax = !(STORE_FAX == '');
$display_tax = !(TAX_ID_NUMBER == '');

$show_including_tax = (DISPLAY_PRICE_WITH_TAX == 'true');
if (!isset($show_product_tax)) {
  $show_product_tax = true;
}

// prepare order-status pulldown list
$orders_statuses = array();
$orders_status_array = array();
$orders_status = $db->Execute("SELECT orders_status_id, orders_status_name
                               FROM " . TABLE_ORDERS_STATUS . "
                               WHERE language_id = " . (int)$_SESSION['languages_id']);
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
  $page_title = HEADER_INVOICE . (int)$oID;
} else {
  $page_title = HEADER_INVOICES;
}

if (($batched == false) or ($batched == true and $batch_item == 1)) { ?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <style>
      @media screen {
        div.form-separator {border-style:none none solid none;border-bottom:thick dotted #000000;}
      }
      @media print {
        div.form-separator {display: none;}
      }
    </style>
    <script>
      function couponpopupWindow(url) { /* just a stub for coupon output that might fire it */ }
    </script>
  </head>
  <body>
<?php
} ?>

<?php
$prev_oID = $oID - 1;
$next_oID = $oID + 1;

$prev_button = '<a href ="' . zen_href_link(FILENAME_SUPER_INVOICE, 'oID=' . $prev_oID) . '">' . zen_draw_separator('pixel_trans.gif', '50', '30') . '</a>';
$check_for_next = $db->Execute("SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_id = '" . (int)$next_oID . "'");
if (zen_not_null($check_for_next->fields['orders_id'])) {
  $next_button = '<a href ="' . zen_href_link(FILENAME_SUPER_INVOICE, 'oID=' . $next_oID) . '">' . zen_draw_separator('pixel_trans.gif', '50', '30') . '</a>';
} else {
  $next_button = '<a href ="' . zen_href_link(FILENAME_SUPER_ORDERS) . '">' . zen_draw_separator('pixel_trans.gif', '50', '30') . '</a>';
}

if (($batched == true) && ($batch_item > 1) && (($batch_item % $forms_per_page) == 0)) { ?>
  <div style="page-break-before:always"><span style="display: none;">&nbsp;</span></div>
  <div class="form-separator"></div>
<?php
} ?>

<div class="container">
  <!-- body_text //-->
  <table class="table">
    <tr>
      <td class="pageHeading storeaddress">
        <?php
        echo nl2br(STORE_NAME_ADDRESS);
        if ($display_tax) echo '<br><br>' . HEADER_TAX_ID . TAX_ID_NUMBER; ?>
        <br>
      </td>
      <td valign="top"><table border="0" cellpadding="0" cellspacing="2">
        <?php
        if ($display_phone) { ?>
          <tr>
            <td class="invoiceHeading text-left" valign="top"><?php echo HEADER_PHONE; ?></td>
            <td class="invoiceHeading text-left" valign="top"><?php echo STORE_PHONE; ?></td>
          </tr>
        <?php
        } ?>
            <?php if ($display_fax) { ?>
              <tr>
                <td class="invoiceHeading text-left" valign="top"><?php echo HEADER_FAX; ?></td>
                <td class="invoiceHeading text-left" valign="top"><?php echo STORE_FAX; ?></td>
              </tr>
            <?php } ?>
            <tr>
              <td class="invoiceHeading text-left" valign="bottom"><?php echo $prev_button; ?></td>
              <td class="invoiceHeading text-right" valign="bottom"><?php echo $next_button; ?></td>
            </tr>
          </table></td>
          <td class="pageHeading text-right">
            <?php
            echo zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT) . '<br><br>';
            echo HEADER_INVOICE . (int)$oID; ?>
          </td>
        </tr>
      </table>
      <div><?php echo zen_draw_separator(); ?></div>
      <?php
      $additional_content = false; 
      $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_INVOICE_ADDITIONAL_DATA_TOP', $oID, $additional_content);
      if ($additional_content !== false) {
      ?>
        <table class="table">
          <tr><td class="main additional_data" colspan="2"><?php echo $additional_content; ?></td></tr>
        </table>
      <?php
      }
      ?>
      <table class="table">
        <tr>
          <?php
          if ($show_customer == true) { ?>
            <td style="border: none">
              <table>
                <tr>
                  <td class="main" colspan="2"><b><?php echo ENTRY_CUSTOMER; ?></b></td>
                </tr>
                <tr>
                  <td class="main" colspan="2"><?php echo zen_address_format($order->customer['format_id'], $order->customer, 1, '', '<br>'); ?></td>
                </tr>
              </table>
            </td>
          <?php } ?>
          <td style="border: none">
            <table>
              <tr>
                <td class="main"><b><?php echo ENTRY_SOLD_TO; ?></b></td>
              </tr>
              <tr>
                <td class="main"><?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
                <td class="main">
                    <?php echo ENTRY_TELEPHONE_NUMBER . ' ' . $order->customer['telephone']; ?>
                </td>
              </tr>
              <tr>
                <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '">' . $order->customer['email_address'] . '</a>'; ?></td>
              </tr>
            </table>
          </td>
          <td style="border: none">
            <table>
              <tr>
                <td class="main"><b><?php echo ENTRY_SHIP_TO; ?></b></td>
              </tr>
              <tr>
                <td class="main"><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <?php
      if ($so->purchase_order) { ?>
        <table>
          <tr>
            <td class="main"><b><?php echo ENTRY_PO_INFO; ?></b></td>
          </tr>
          <tr>
            <td class="main"><strong><?php echo HEADER_PO_NUMBER; ?></strong></td>
            <td class="main"><?php echo $so->purchase_order[0]['number']; ?></td>
          </tr>
          <tr>
            <td class="main"><strong><?php echo HEADER_PO_INVOICE_DATE; ?></strong></td>
            <td class="main"><?php echo zen_date_short($so->purchase_order[0]['posted']); ?></td>
          </tr>
          <tr>
            <td class="main"><strong><?php echo HEADER_PO_TERMS; ?></strong></td>
            <td class="main"><?php echo HEADER_PO_TERMS_LENGTH; ?></td>
          </tr>
        </table>
        <div><?php echo zen_draw_separator('pixel_trans.gif', '', '10'); ?></div>
      <?php
      } ?>
      <table>
        <tr>
          <td class="main"><strong><?php echo ENTRY_DATE_PURCHASED; ?></strong></td>
          <td class="main"><?php echo zen_date_long($order->info['date_purchased']); ?></td>
        </tr>
        <tr>
          <td class="main"><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong></td>
          <td class="main"><?php echo $order->info['payment_method']; ?></td>
        </tr>
      </table>
      <div><?php echo zen_draw_separator('pixel_trans.gif', '', '10'); ?></div>
      <table class="table table-striped">
        <thead>
          <tr class="dataTableHeadingRow">
            <?php if ($show_product_images) { ?>
              <th class="dataTableHeadingContent" style="width: <?php echo (int)$img_width . 'px'; ?>">&nbsp;</th>
            <?php } ?>
            <th class="dataTableHeadingContent">&nbsp;</th>
            <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_NAME; ?></th>
            <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></th>
<?php
          // -----
          // Additional column-headings can be added before the Tax columns.
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
          //   'NOTIFY_ADMIN_INVOICE_DATA_B4_TAX' notification.
          //
          $extra_headings = false;
          $zco_notifier->notify('NOTIFY_ADMIN_INVOICE_HEADING_B4_TAX', '', $extra_headings);
          if (is_array($extra_headings)) {
              foreach ($extra_headings as $heading_info) {
                  $align = (isset($heading_info['align'])) ? (' text-' . $heading_info['align']) : '';
?>
            <th class="dataTableHeadingContent<?php echo $align; ?>"><?php echo $heading_info['text']; ?></th>
<?php
              }
          }
?>
<?php if ($show_product_tax) { ?>
            <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_TAX; ?></th>
<?php } ?>
            <th class="dataTableHeadingContent text-right"><?php echo ($show_including_tax) ? TABLE_HEADING_PRICE_EXCLUDING_TAX : TABLE_HEADING_PRICE; ?></th>
<?php if ($show_including_tax)  { ?>
            <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></th>
<?php } ?>
            <th class="dataTableHeadingContent text-right"><?php echo ($show_including_tax) ? TABLE_HEADING_TOTAL_EXCLUDING_TAX : TABLE_HEADING_TOTAL; ?></th>
<?php if ($show_including_tax)  { ?>
            <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></th>
<?php } ?>
<?php
          // -----
          // Additional column-headings can be added after the Tax columns.
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
          //   'NOTIFY_ADMIN_INVOICE_DATA_AFTER_TAX' notification.
          //
          $extra_headings = false;
          $zco_notifier->notify('NOTIFY_ADMIN_INVOIVE_HEADERS_AFTER_TAX', '', $extra_headings);
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
          $decimals = $currencies->get_decimal_places($order->info['currency']);
          /*
           * Add notifier to allow invoice to be sorted to required order
           *
           * Set $sort_order to the order->products array counter in the sequence you require the invoice to be displayed
           */
          $sort_order = false;
          $zco_notifier->notify('NOTIFY_ADMIN_INVOICE_SORT_DISPLAY', $order->products, $sort_order);
          for ($ii = 0, $n = sizeof($order->products); $ii < $n; $ii++) {
              if (is_array($sort_order)) {
                  $i = $sort_order[$ii];
              } else {
                  $i = $ii;
              }
              $product_name = $order->products[$i]['name'];
            if (DISPLAY_PRICE_WITH_TAX_ADMIN == 'true') {
              $priceIncTax = $currencies->format(zen_round(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), $decimals) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
            } else {
              $priceIncTax = $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
            }
            ?>
          <tr class="dataTableRow">
              <?php if ($show_product_images) { ?>
                <td class="dataTableContent">
                  <?php echo zen_image(DIR_WS_CATALOG . DIR_WS_IMAGES . zen_get_products_image($order->products[$i]['id']), zen_output_string($product_name), (int)$img_width); ?>
                </td>
              <?php } ?>

              <td class="dataTableContent text-right">
                <?php echo $order->products[$i]['qty']; ?>&nbsp;x
              </td>
              <td class="dataTableContent"><?php echo $product_name; ?>
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
                            echo zen_image(DIR_WS_CATALOG.DIR_WS_IMAGES . $attribute_image, zen_output_string($attribute_name), (int)$attr_img_width);
                        }
                        ?>
                      <small>
                        <i>
                        <?php echo $attribute_name; ?>
                        <?php
                              if ($order->products[$i]['attributes'][$j]['price'] != '0') {
                                echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
                              }
                              if ($order->products[$i]['attributes'][$j]['product_attribute_is_free'] == '1' and $order->products[$i]['product_is_free'] == '1') {
                                echo TEXT_INFO_ATTRIBUTE_FREE;
                              }
                        ?>
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
            // Additional fields can be added into columns before the Tax columns.
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
            //   'NOTIFY_ADMIN_INVOICE_HEADERS_B4_QTY' notification.
            //
            $extra_data = false;
            $zco_notifier->notify('NOTIFY_ADMIN_INVOICE_DATA_B4_TAX',  $order->products[$i]['id'], $extra_data);
            if (is_array($extra_data)) {
                foreach ($extra_data as $data_info) {
                    $align = (isset($data_info['align'])) ? (' text-' . $data_info['align']) : '';
?>
              <td class="dataTableContent<?php echo $align; ?>"><?php echo $data_info['text']; ?></td>
<?php
                }
            }
?>
<?php if ($show_product_tax) { ?>
            <td class="dataTableContent text-right">
              <?php echo zen_display_tax_value($order->products[$i]['tax']); ?>%
            </td>
<?php } ?>
            <td class="dataTableContent text-right">
              <strong><?php echo $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format($order->products[$i]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : ''); ?></strong>
            </td>
<?php if ($show_including_tax)  { ?>
            <td class="dataTableContent text-right">
              <strong><?php echo $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) . ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) : ''); ?></strong>
            </td>
<?php } ?>
            <td class="dataTableContent text-right">
              <strong><?php echo $currencies->format(zen_round($order->products[$i]['final_price'], $decimals) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format($order->products[$i]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : ''); ?></strong>
            </td>
<?php if ($show_including_tax)  { ?>
            <td class="dataTableContent text-right align-top">
              <strong>
                <?php echo $priceIncTax; ?>
                <?php if ($order->products[$i]['onetime_charges'] != 0) {
                    echo '<br>' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']);
                }
                ?>
              </strong>
            </td>
<?php } 
            // -----
            // Additional fields can be added into columns after the Tax columns.
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
            //   'NOTIFY_ADMIN_INVOICE_HEADERS_AFTER_TAX' notification.
            //
            $extra_data = false;
            $zco_notifier->notify('NOTIFY_ADMIN_INVOICE_DATA_AFTER_TAX', $order->products[$i]['id'], $extra_data);
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
    <table class="table">
        <?php
        for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
          ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="text-right <?php echo str_replace('_', '-', $order->totals[$i]['class']); ?>-Text"><?php echo $order->totals[$i]['title']; ?></td>
            <td class="text-right <?php echo str_replace('_', '-', $order->totals[$i]['class']); ?>-Amount"><?php echo $order->totals[$i]['text']; ?></td>
          </tr>
        <?php
        } ?>
      </table>
      <?php
      $dbc= $db->Execute("SELECT currency, currency_value FROM " . TABLE_ORDERS . " WHERE orders_id ='" . (int)$oID . "'");
      $cu = $dbc->fields['currency'];
      $cv = $dbc->fields['currency_value'];
      ?>
      <table class="table">
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="text-right ot-tax-TextPrint"><strong><?php echo ENTRY_AMOUNT_APPLIED_CUST . '(' . $cu.')'; ?></strong></td>
          <td class="text-right printMain"><strong><?php echo $currencies->format($so->amount_applied, true, $order->info['currency'], $order->info['currency_value']); ?></strong></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="text-right ot-tax-TextPrint"><strong><?php echo ENTRY_BALANCE_DUE_CUST . '(' . $cu.')'; ?></strong></td>
          <td class="text-right printMain"><strong><?php echo $currencies->format($so->balance_due, true, $order->info['currency'], $order->info['currency_value']); ?></strong></td>
        </tr>
      </table>
      <?php if (ORDER_COMMENTS_INVOICE > 0) { ?>
        <table class="table table-condensed" style="width:100%;">
          <thead>
            <tr>
              <th class="text-left"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></th>
              <th class="text-left"><strong><?php echo TABLE_HEADING_STATUS; ?></strong></th>
              <th class="text-left"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></th>
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
                  <td class="text-left"><?php echo zen_datetime_short($order_history['date_added']); ?></td>
                  <td class="text-left"><?php echo $orders_status_array[$order_history['orders_status_id']]; ?></td>
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
                if (ORDER_COMMENTS_INVOICE == 1 && $count_comments >= 1) {
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
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_INVOICE_ADDITIONAL_DATA_BOTTOM', $oID, $additional_content);
          if ($additional_content !== false) {
      ?>
          <table class="table">
              <tr><td class="main additional_data" colspan="2"><?php echo $additional_content; ?></td></tr>
          </table>
      <?php
          }
      ?>
    </div>

    <!-- body_text_eof //-->
<?php if (($batched == false) or (($batched == true) and ($batch_item == $number_of_orders))) { ?>
  </body>
</html>
<?php } ?>
<?php require_once(DIR_WS_INCLUDES . 'application_bottom.php'); ?>