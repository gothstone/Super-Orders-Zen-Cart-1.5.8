<?php
/**
 * @package admin
 *
 *  Based on Super Order 2.0
 *  By Frank Koehl - PM: BlindSide (original author)
 *
 *  Super Orders Updated by:
 *  ~ JT of GTICustom
 *  ~ C Jones Over the Hill Web Consulting (http://overthehillweb.com)
 *  ~ Loose Chicken Software Development, david@loosechicken.com
 *
 * DESCRIPTION:   Enhanced admin/orders.php. Features include:
 *  ~ Improved navigation options
 *  ~ An advanced payment management system.
 *  ~ EZ Integration with Ty Package Tracker and Edit Orders
 *  ~ Admin comment editing
 *  ~ Improved HTML and look & feel
 *
 * @copyright Copyright 2003-2021 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2021 Feb 22 Modified in v1.5.7c $
 */
require('includes/application_top.php');

// unset variable which is sometimes tainted by bad plugins like magneticOne tools
if (isset($module)) {
  unset($module);
}

/* BOF Super Orders 1 of 21 */
if (!defined('TY_TRACKER')) {
    define('TY_TRACKER', 'False');
}
/* BOF Super Orders 1 of 21 */

$quick_view_popover_enabled = false;
$includeAttributesInProductDetailRows = true;

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

if (isset($_GET['oID'])) {
  $_GET['oID'] = (int)$_GET['oID'];
}
if (isset($_GET['download_reset_on'])) {
  $_GET['download_reset_on'] = (int)$_GET['download_reset_on'];
}
if (isset($_GET['download_reset_off'])) {
  $_GET['download_reset_off'] = (int)$_GET['download_reset_off'];
}
if (!isset($_GET['status'])) $_GET['status'] = '';
if (!isset($_GET['list_order'])) $_GET['list_order'] = '';
if (!isset($_GET['page'])) $_GET['page'] = '';

include DIR_FS_CATALOG . DIR_WS_CLASSES . 'order.php';
$show_including_tax = (DISPLAY_PRICE_WITH_TAX == 'true');
// prepare order-status look-up list
$orders_status_array = array();
$orders_status = $db->Execute("SELECT orders_status_id, orders_status_name
                               FROM " . TABLE_ORDERS_STATUS . "
                               WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                               ORDER BY orders_status_id");
foreach ($orders_status as $status) {
  $orders_status_array[$status['orders_status_id']] = $status['orders_status_name'];
}

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$order_exists = false;
if (isset($_GET['oID']) && trim($_GET['oID']) == '') {
  unset($_GET['oID']);
}
if ($action == 'edit' && !isset($_GET['oID'])) {
  $action = '';
}

$oID = FALSE;
if (isset($_POST['oID'])) {
  $oID = zen_db_prepare_input(trim($_POST['oID']));
} elseif (isset($_GET['oID'])) {
  $oID = zen_db_prepare_input(trim($_GET['oID']));
}
if ($oID) {
  $orders = $db->Execute("SELECT orders_id
                          FROM " . TABLE_ORDERS . "
                          WHERE orders_id = " . (int)$oID);
  $order_exists = true;
  if ($orders->RecordCount() <= 0) {
    $order_exists = false;
    if ($action != '') {
      $messageStack->add_session(ERROR_ORDER_DOES_NOT_EXIST . ' ' . $oID, 'error');
    }
    zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')), 'NONSSL'));
  }
}

/* BOF Super Orders 2 of 21 */
if ($oID) {
  require_once(DIR_WS_CLASSES . 'super_order.php');
  $so = new super_order($oID);
}
/* EOF Super Orders 2 of 21 */


if (!empty($oID) && !empty($action)) {
  $zco_notifier->notify('NOTIFY_ADMIN_ORDER_PREDISPLAY_HOOK', $oID, $action);
}

        // -----
        // Determine which of the 'Notify Customer' radio buttons should be selected initially,
        // based on configuration setting in 'My Store'.  Set a default, just in case that configuration
        // setting isn't set!
        //
        if (!defined('NOTIFY_CUSTOMER_DEFAULT')) define('NOTIFY_CUSTOMER_DEFAULT', '1');
        switch (NOTIFY_CUSTOMER_DEFAULT) {
            case '0':
                $notify_email = false;
                $notify_no_email = true;
                $notify_hidden = false;
                break;
            case '-1':
                $notify_email = false;
                $notify_no_email = false;
                $notify_hidden = true;
                break;
            default:
                $notify_email = true;
                $notify_no_email = false;
                $notify_hidden = false;
                break;
        }

if (zen_not_null($action) && $order_exists == true) {
  switch ($action) {
    /* BOF Super Orders 3 of 21 */
    case 'mark_completed':
      $so->mark_completed();
      $messageStack->add_session(sprintf(SUCCESS_MARK_COMPLETED, $oID), 'success');
      zen_redirect(zen_href_link(FILENAME_ORDERS, 'action=edit&oID=' . $oID, 'NONSSL'));
      break;
    case 'mark_cancelled':
      $so->mark_cancelled();
      $messageStack->add_session(sprintf(WARNING_MARK_CANCELLED, $oID), 'warning');
      zen_redirect(zen_href_link(FILENAME_ORDERS, 'action=edit&oID=' . $oID, 'NONSSL'));
      break;
    case 'reopen':
      $so->reopen();
      $messageStack->add_session(sprintf(WARNING_ORDER_REOPEN, $oID), 'warning');
      zen_redirect(zen_href_link(FILENAME_ORDERS, 'action=edit&oID=' . $oID, 'NONSSL'));
      break;
    /* EOF Super Orders 3 of 21 */
    case 'download':
      $fileName = basename($_GET['filename']);
      $file_extension = strtolower(substr(strrchr($fileName, '.'), 1));
      switch ($file_extension) {
        case 'csv':
          $content = 'text/csv';
          break;
        case 'zip':
          $content = 'application/zip';
          break;
        case 'jpg':
          $content = 'image/jpeg';
          break;
        case 'jpeg':
          $content = 'image/jpeg';
          break;
        case 'gif':
          $content = 'image/gif';
          break;
        case 'png':
          $content = 'image/png';
          break;
        case 'eps':
          $content = 'application/postscript';
          break;
        case 'cdr':
          $content = 'application/cdr';
          break;
        case 'ai':
          $content = 'application/postscript';
          break;
        case 'pdf':
          $content = 'application/pdf';
          break;
        case 'tif':
          $content = 'image/tiff';
          break;
        case 'tiff':
          $content = 'image/tiff';
          break;
        case 'bmp':
          $content = 'image/bmp';
          break;
        case 'xls':
          $content = 'application/vnd.ms-excel';
          break;
        case 'numbers':
          $content = 'application/vnd.ms-excel';
          break;
        default:
          $messageStack->add_session(sprintf(TEXT_EXTENSION_NOT_UNDERSTOOD, $file_extension), 'error');
          zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('download', 'action')) . 'action=edit', 'NONSSL'));
      }
      $fs_path = DIR_FS_CATALOG_IMAGES . 'uploads/' . $fileName;
      if (!file_exists($fs_path)) {
        $messageStack->add_session(TEXT_FILE_NOT_FOUND, 'error');
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('download', 'action')) . 'action=edit'));
      }
      header('Content-type: ' . $content);
      header('Content-Disposition: attachment; filename="' . $fileName . '"');
      header('Content-Transfer-Encoding: binary');
      header('Cache-Control: no-cache, must-revalidate');
      header("Expires: Mon, 22 Jan 2002 00:00:00 GMT");
      readfile($fs_path);
      exit();

    case 'edit':
      // reset single download to on
      if (!empty($_GET['download_reset_on'])) {
        // adjust download_maxdays based on current date
        $check_status = $db->Execute("SELECT customers_name, customers_email_address, orders_status, date_purchased
                                      FROM " . TABLE_ORDERS . "
                                      WHERE orders_id = " . (int)$_GET['oID']);

        // check for existing product attribute download days and max
        $chk_products_download_query = "SELECT orders_products_id, orders_products_filename, products_prid
                                        FROM " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                                        WHERE orders_products_download_id = " . (int)$_GET['download_reset_on'];
        $chk_products_download = $db->Execute($chk_products_download_query);

        $chk_products_download_time_query = "SELECT pa.products_attributes_id, pa.products_id,
                                                    pad.products_attributes_filename, pad.products_attributes_maxdays, pad.products_attributes_maxcount
                                             FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa,
                                                   " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                             WHERE pa.products_attributes_id = pad.products_attributes_id
                                             AND pad.products_attributes_filename = '" . $db->prepare_input($chk_products_download->fields['orders_products_filename']) . "'
                                             AND pa.products_id = " . (int)$chk_products_download->fields['products_prid'];

        $chk_products_download_time = $db->Execute($chk_products_download_time_query);

        if ($chk_products_download_time->EOF) {
          $zc_max_days = (DOWNLOAD_MAX_DAYS == 0 ? 0 : zen_date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + DOWNLOAD_MAX_DAYS);
          $update_downloads_query = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                                     SET download_maxdays = " . (int)$zc_max_days . ",
                                         download_count = " . (int)DOWNLOAD_MAX_COUNT . "
                                     WHERE orders_id = " . (int)$_GET['oID'] . "
                                     AND orders_products_download_id = " . (int)$_GET['download_reset_on'];
        } else {
          $zc_max_days = ($chk_products_download_time->fields['products_attributes_maxdays'] == 0 ? 0 : zen_date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + $chk_products_download_time->fields['products_attributes_maxdays']);
          $update_downloads_query = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                                     SET download_maxdays = " . (int)$zc_max_days . ",
                                         download_count = " . (int)$chk_products_download_time->fields['products_attributes_maxcount'] . "
                                     WHERE orders_id = " . (int)$_GET['oID'] . "
                                     AND orders_products_download_id = " . (int)$_GET['download_reset_on'];
        }

        $db->Execute($update_downloads_query);
        unset($_GET['download_reset_on']);

        $messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_ON, 'success');
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
      }
      // reset single download to off
      if (!empty($_GET['download_reset_off'])) {
        // adjust download_maxdays based on current date
        // *** fix: adjust count not maxdays to cancel download
//          $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='0', download_count='0' where orders_id='" . $_GET['oID'] . "' and orders_products_download_id='" . $_GET['download_reset_off'] . "'";
        $update_downloads_query = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                                   SET download_count = 0
                                   WHERE orders_id = " . (int)$_GET['oID'] . "
                                   AND orders_products_download_id = " . (int)$_GET['download_reset_off'];
        $db->Execute($update_downloads_query);
        unset($_GET['download_reset_off']);

        $messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_OFF, 'success');
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
      }
      break;
    case 'update_order':
      $oID = zen_db_prepare_input($_GET['oID']);
      $comments = !empty($_POST['comments']) ? zen_db_prepare_input($_POST['comments']) : '';
      $admin_language = zen_db_prepare_input(isset($_POST['admin_language']) ? $_POST['admin_language'] : $_SESSION['languages_code']);
      $status = (int)$_POST['status'];
      if ($status < 1) {
         break;
      }

      $email_include_message = (isset($_POST['notify_comments']) && $_POST['notify_comments'] == 'on');
      $customer_notified = isset($_POST['notify']) ? (int)$_POST['notify'] : 0;

      // -----
      // Give an observer the opportunity to add to the to-be-recorded comments and/or
      // capture any posted values that it has inserted into the order-update form.
      //
      $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_UPDATE_ORDER_START', $oID);

      $order_updated = false;
      /* BOF Super Orders 4 of 21 */
      // BEGIN TY TRACKER
      $updated_by = zen_updated_by_admin();
      $status_updated = zen_update_orders_history($oID, $comments, $updated_by, $status, $customer_notified, $email_include_message);
      // END TY TRACKER
      /* EOF Super Orders 4 of 21 */
      $order_updated = ($status_updated > 0);

      $check_status = $db->ExecuteNoCache("SELECT customers_name, customers_email_address, orders_status, date_purchased
                                    FROM " . TABLE_ORDERS . "
                                    WHERE orders_id = " . (int)$oID . "
                                    LIMIT 1"
                                    );

      // trigger any appropriate updates which should be sent back to the payment gateway:
      $order = new order((int)$oID);
      if ($order->info['payment_module_code']) {
        if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
          require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
          require_once(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
          $module = new $order->info['payment_module_code'];
          if (method_exists($module, '_doStatusUpdate')) {
            $response = $module->_doStatusUpdate($oID, $status, $comments, $customer_notified, $check_status->fields['orders_status']);
          }
        }
      }

      if ($order_updated == true) {
        if ($status == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE) {

          // adjust download_maxdays based on current date
          $chk_downloads_query = "SELECT opd.*, op.products_id
                                  FROM " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd,
                                       " . TABLE_ORDERS_PRODUCTS . " op
                                  WHERE op.orders_id = " . (int)$oID . "
                                  AND opd.orders_products_id = op.orders_products_id";
          $chk_downloads = $db->Execute($chk_downloads_query);

          foreach ($chk_downloads as $chk_download) {
            $chk_products_download_time_query = "SELECT pa.products_attributes_id, pa.products_id,
                                                        pad.products_attributes_filename, pad.products_attributes_maxdays, pad.products_attributes_maxcount
                                                 FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                                 WHERE pa.products_attributes_id = pad.products_attributes_id
                                                 AND pad.products_attributes_filename = '" . $db->prepare_input($chk_download['orders_products_filename']) . "'
                                                 AND pa.products_id = " . (int)$chk_download['products_id'];

            $chk_products_download_time = $db->Execute($chk_products_download_time_query);

            if ($chk_products_download_time->EOF) {
              $zc_max_days = (DOWNLOAD_MAX_DAYS == 0 ? 0 : zen_date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + DOWNLOAD_MAX_DAYS);
              $update_downloads_query = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                                         SET download_maxdays = " . (int)$zc_max_days . ",
                                             download_count = " . (int)DOWNLOAD_MAX_COUNT . "
                                         WHERE orders_id = " . (int)$oID . "
                                         AND orders_products_download_id = " . (int)$_GET['download_reset_on'];
            } else {
              $zc_max_days = ($chk_products_download_time->fields['products_attributes_maxdays'] == 0 ? 0 : zen_date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + $chk_products_download_time->fields['products_attributes_maxdays']);
              $update_downloads_query = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                                         SET download_maxdays = " . (int)$zc_max_days . ",
                                             download_count = " . (int)$chk_products_download_time->fields['products_attributes_maxcount'] . "
                                         WHERE orders_id = " . (int)$oID . "
                                         AND orders_products_download_id = " . (int)$chk_download['orders_products_download_id'];
            }

            $db->Execute($update_downloads_query);
          }
        }
        $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
          if ($customer_notified === 1) {
              $messageStack->add_session(sprintf(SUCCESS_EMAIL_SENT, ($admin_language !== $_SESSION['languages_code'] ? '(' . strtoupper($_SESSION['languages_code']) . ') ' : '')), 'success'); // show an email sent confirmation message, with a language indicator if the order/email language was different to the admin user language
          }
        zen_record_admin_activity('Order ' . $oID . ' updated.', 'info');
      } else {
        $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
      }

        $redirect = zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['action', 'language']) . ($admin_language !== $_SESSION['languages_code'] ? '&language=' . $admin_language : ''), 'NONSSL');
        if (isset($_POST['camefrom']) && $_POST['camefrom'] === 'orderEdit') {
            $redirect .= '&action=edit';
        }
        zen_redirect($redirect);
      break;

    case 'deleteconfirm':
      $oID = zen_db_prepare_input($_POST['oID']);

      zen_remove_order($oID, $_POST['restock']);

      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')), 'NONSSL'));
      break;
    case 'delete_cvv':
      $delete_cvv = $db->Execute("UPDATE " . TABLE_ORDERS . "
                                  SET cc_cvv = '" . TEXT_DELETE_CVV_REPLACEMENT . "'
                                  WHERE orders_id = " . (int)$_GET['oID']);
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
      break;
    case 'mask_cc':
      $result = $db->Execute("SELECT cc_number
                              FROM " . TABLE_ORDERS . "
                              WHERE orders_id = " . (int)$_GET['oID']);
      $old_num = $result->fields['cc_number'];
      $new_num = substr($old_num, 0, 4) . str_repeat('*', (strlen($old_num) - 8)) . substr($old_num, -4);
      $mask_cc = $db->Execute("UPDATE " . TABLE_ORDERS . "
                               SET cc_number = '" . $new_num . "'
                               WHERE orders_id = " . (int)$_GET['oID']);
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
      break;

    case 'doRefund':
      $order = new order($oID);
      if ($order->info['payment_module_code']) {
        if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
          require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
          require_once(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
          $module = new $order->info['payment_module_code'];
          if (method_exists($module, '_doRefund')) {
            $module->_doRefund($oID);
          }
        }
      }
      zen_record_admin_activity('Order ' . $oID . ' refund processed. See order comments for details.', 'info');
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
      break;
    case 'doAuth':
      $order = new order($oID);
      if ($order->info['payment_module_code']) {
        if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
          require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
          require_once(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
          $module = new $order->info['payment_module_code'];
          if (method_exists($module, '_doAuth')) {
            $module->_doAuth($oID, $order->info['total'], $order->info['currency']);
          }
        }
      }
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
      break;
    case 'doCapture':
      $order = new order($oID);
      if ($order->info['payment_module_code']) {
        if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
          require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
          require_once(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
          $module = new $order->info['payment_module_code'];
          if (method_exists($module, '_doCapt')) {
            $module->_doCapt($oID, 'Complete', $order->info['total'], $order->info['currency']);
          }
        }
      }
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
      break;
    case 'doVoid':
      $order = new order($oID);
      if ($order->info['payment_module_code']) {
        if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
          require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
          require_once(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
          $module = new $order->info['payment_module_code'];
          if (method_exists($module, '_doVoid')) {
            $module->_doVoid($oID);
          }
        }
      }
      zen_record_admin_activity('Order ' . $oID . ' void processed. See order comments for details.', 'info');
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
      break;
      default:
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_DEFAULT_ACTION', $oID, $order);
        break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <?php /* BOF Super Orders 5 of 21 */ ?>
    <link rel="stylesheet" href="includes/css/super_stylesheet.css">
    <?php if (TY_TRACKER == 'True') { ?>
      <link rel="stylesheet" href="includes/typt_stylesheet.css">
    <?php } ?>

    <script>
      function popupWindow(url, features) {
        window.open(url, 'popupWindow', features)
      }
    </script>
    <?php /* BOF Super Orders 5 of 21 */ ?>
    <script>
      function couponpopupWindow(url) {
          window.open(url, 'popupWindow', 'toolbar=no,location=no,directories=no,status=no,menu bar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150,noreferrer')
      }
    </script>
      <?php
      if ($action === 'edit' && $editor_handler !== '') {
          include ($editor_handler);
      }
      ?>
  </head>
  <body>
    <!-- header //-->
    <?php
    require(DIR_WS_INCLUDES . 'header.php');
    ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div class="container-fluid">
      <!-- body_text //-->
      <h1><?php echo ($action == 'edit' && $order_exists) ? sprintf(HEADING_TITLE_DETAILS, (int)$oID) : HEADING_TITLE; ?></h1>

      <?php $order_list_button = '<a role="button" class="btn btn-default" href="' . zen_href_link(FILENAME_ORDERS) . '"><i class="fa fa-th-list" aria-hidden="true">&nbsp;</i> ' . BUTTON_TO_LIST . '</a>'; ?>
      <?php if ($action == '') { ?>
        <!-- search -->

        <div class="row noprint">
          <div class="form-inline">
            <div class="form-group col-xs-4 col-sm-3 col-md-3 col-lg-3">
                <?php
                echo zen_draw_form('search', FILENAME_ORDERS, '', 'get', '', true);
                echo zen_draw_label(HEADING_TITLE_SEARCH_ALL, 'searchAll', 'class="sr-only"');
                $placeholder = zen_output_string_protected(isset($_GET['search']) && $_GET['search'] != '' ? $_GET['search'] : HEADING_TITLE_SEARCH_ALL);
                ?>
              <div class="input-group">
                  <?php
                  echo zen_draw_input_field('search', '', 'id="searchAll" class="form-control" placeholder="' . $placeholder . '"');
                  if (!empty($_GET['search']) || !empty($_GET['cID'])) {
                    ?>
                  <a class="btn btn-info input-group-addon" role="button" aria-label="<?php echo TEXT_RESET_FILTER; ?>" href="<?php echo zen_href_link(FILENAME_ORDERS); ?>">
                    <i class="fa fa-times" aria-hidden="true">&nbsp;</i>
                  </a>
                <?php } ?>
              </div>
              <?php echo '</form>'; ?>
            </div>
            <div class="form-group col-xs-6 col-sm-3 col-md-3 col-lg-3">
                <?php
                echo zen_draw_form('search_orders_products', FILENAME_ORDERS, '', 'get', '', true);
                echo zen_draw_label(HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS, 'searchProduct', 'class="sr-only"');
                $placeholder = zen_output_string_protected(isset($_GET['search_orders_products']) && !empty($_GET['search_orders_products']) ? $_GET['search_orders_products'] : HEADING_TITLE_SEARCH_PRODUCTS);
                ?>
              <div class="input-group">
                  <?php
                  echo zen_draw_input_field('search_orders_products', '', 'id="searchProduct" class="form-control" aria-describedby="helpBlock3" placeholder="' . $placeholder . '"');
                  if (!empty($_GET['search_orders_products']) || !empty($_GET['cID'])) {
                    ?>
                  <a class="btn btn-info input-group-addon" role="button" aria-label="<?php echo TEXT_RESET_FILTER; ?>" href="<?php echo zen_href_link(FILENAME_ORDERS); ?>">
                    <i class="fa fa-times" aria-hidden="true">&nbsp;</i>
                  </a>
                <?php } ?>
              </div>
              <span id="helpBlock3" class="help-block"><?php echo HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS; ?></span>
              <?php echo '</form>'; ?>
            </div>
            <div class="form-group col-xs-4 col-sm-3 col-md-3 col-lg-3">
                <?php
                echo zen_draw_form('orders', FILENAME_ORDERS, '', 'get', '', true);
                echo zen_draw_label(HEADING_TITLE_SEARCH, 'oID', 'class="sr-only"');
                echo zen_draw_input_field('oID', '', 'id="oID" class="form-control" placeholder="' . HEADING_TITLE_SEARCH . '"', '', 'number');
                echo zen_draw_hidden_field('action', 'edit');
                echo '</form>';
                ?>
            </div>
            <div class="form-group col-xs-4 col-sm-3 col-md-3 col-lg-3">
                <?php
                echo zen_draw_form('status', FILENAME_ORDERS, '', 'get', '', true);
                echo zen_draw_label(HEADING_TITLE_STATUS, 'selectstatus', 'class="sr-only"');
                echo zen_draw_order_status_dropdown('status', (int)$_GET['status'], array('id' => '', 'text' => TEXT_ALL_ORDERS), 'class="form-control" onChange="this.form.submit();" id="selectstatus"');
                echo '</form>';
                ?>
            </div>
          </div>
        </div>

        <!-- search -->
      <?php } ?>

      <?php
      if ($action == 'edit' && $order_exists) {
        $order = new order($oID);
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_EDIT_BEGIN', $oID, $order);
        if ($order->info['payment_module_code']) {
          if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
            require(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
            require(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
            $module = new $order->info['payment_module_code'];
//        echo $module->admin_notification($oID);
          }
        }

        $prev_button = '';
        $result = $db->Execute("SELECT orders_id
                                  FROM " . TABLE_ORDERS . "
                                  WHERE orders_id < " . (int)$oID . "
                                  ORDER BY orders_id DESC
                                  LIMIT 1");
        if ($result->RecordCount()) {
          $prev_button = '<a role="button" class="btn btn-default" href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $result->fields['orders_id'] . '&action=edit') . '">&laquo; ' . $result->fields['orders_id'] . '</a>';
        }

        $next_button = '';
        $result = $db->Execute("SELECT orders_id
                                  FROM " . TABLE_ORDERS . "
                                  WHERE orders_id > " . (int)$oID . "
                                  ORDER BY orders_id ASC
                                  LIMIT 1");
        if ($result->RecordCount()) {
          $next_button = '<a role="button" class="btn btn-default" href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $result->fields['orders_id'] . '&action=edit') . '">' . $result->fields['orders_id'] . ' &raquo;</a>';
        }

        // -----
        // Enable an observer to inject additional buttons, either to the right or left (or both)
        // of the navigation.
        //
        $left_side_buttons = '';
        $right_side_buttons = '';
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_UPPER_BUTTONS', $oID, $left_side_buttons, $right_side_buttons);
        ?>

        <?php /* BOF Super Orders 6 of 21 */ ?>
        <div class="row">
          <div class="col-sm-4 text-right">
            <?php
            if ($so->status) {
              echo
              '<span class="status-' . $so->status . '">' . zen_datetime_short($so->status_date) . '</span>&nbsp;' .
              '<a href="' . zen_href_link(FILENAME_ORDERS, 'action=reopen&oID=' . $oID) . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_x.gif', '', '', '', '') . HEADING_REOPEN_ORDER . '</a>';
            } ?>
          </div>
        </div>
        <?php /* EOF Super Orders 6 of 21 */ ?>
        <div class="row">
          <div class="col-sm-3 col-lg-4 text-left noprint">
            <?php echo $left_side_buttons; ?>
          </div>
          <div class="col-sm-6 col-lg-4 noprint">
            <div class="input-group">
              <span class="input-group-btn">
                  <?php echo $prev_button; ?>
              </span>
              <?php
              echo zen_draw_form('input_oid', FILENAME_ORDERS, '', 'get', '', true);
              echo zen_draw_input_field('oID', '', 'class="form-control" placeholder="' . SELECT_ORDER_LIST . '"', '', 'number');
              echo zen_draw_hidden_field('action', 'edit');
              echo '</form>';
              ?>
              <div class="input-group-btn">
                <?php echo $next_button; ?>
                <?php echo $order_list_button; ?>
                <button type="button" class="btn btn-default" onclick="history.back()"><i class="fa fa-undo" aria-hidden="true">&nbsp;</i> <?php echo IMAGE_BACK; ?></button>
              </div>
            </div>
          </div>
          <div class="col-sm-3 col-lg-4 text-right noprint">
             <?php echo $right_side_buttons; ?>
          </div>
        </div>
        <div class="row noprint"><?php echo zen_draw_separator(); ?></div>
        <div class="row">
          <div class="col-sm-4">
            <table class="table">
              <tr>
                <td><strong><?php echo ENTRY_CUSTOMER_ADDRESS; ?></strong></td>
                <td><?php echo zen_address_format($order->customer['format_id'], $order->customer, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td class="noprint"><a href="https://maps.google.com/maps/search/?api=1&amp;query=<?php echo urlencode($order->customer['street_address'] . ',' . $order->customer['city'] . ',' .  $order->customer['state'] . ',' . $order->customer['postcode']); ?>" rel="noreferrer" target="map"><i class="fa fa-map">&nbsp;</i> <u><?php echo TEXT_MAP_CUSTOMER_ADDRESS; ?></u></a></td>
              </tr>
<?php
  $address_footer_suffix = '';
  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_ADDRESS_FOOTERS', 'customer', $address_footer_suffix, $order->customer);
  if (!empty($address_footer_suffix)) {
  ?>
                <tr><td>&nbsp;</td><td><?php echo $address_footer_suffix; ?></td></tr>
<?php } ?>
              <tr class="noprint">
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
                <td><strong><?php echo ENTRY_TELEPHONE_NUMBER; ?></strong></td>
                <td><a href="tel:<?php echo preg_replace('/\s+/', '', $order->customer['telephone']); ?>"><?php echo $order->customer['telephone']; ?></a></td>
              </tr>
              <tr>
                <td><strong><?php echo ENTRY_EMAIL_ADDRESS; ?></strong></td>
                <td><?php echo '<a href="mailto:' . $order->customer['email_address'] . '">' . $order->customer['email_address'] . '</a>'; ?></td>
              </tr>
              <tr>
                <td><strong><?php echo TEXT_INFO_IP_ADDRESS; ?></strong></td>
                <?php
                if (!empty($order->info['ip_address'])) {
                  $lookup_ip = substr($order->info['ip_address'], 0, strpos($order->info['ip_address'], ' '));
                  $whois_url = 'https://tools.dnsstuff.com/#whois|type=ipv4&&value=' . $lookup_ip;
                  //$whois_url = 'https://whois.domaintools.com/' . $lookup_ip;
                  $zco_notifier->notify('ADMIN_ORDERS_IP_LINKS', $lookup_ip, $whois_url);
                  ?>
                  <td class="noprint"><a href="<?php echo $whois_url; ?>" rel="noreferrer noopener" target="_blank"><?php echo $order->info['ip_address']; ?></a></td>
                <?php } else { ?>
                  <td><?php echo TEXT_UNKNOWN; ?></td>
                <?php } ?>
              </tr>
              <tr>
                <td class="noprint"><strong><?php echo ENTRY_CUSTOMER; ?></strong></td>
                <td class="noprint"><?php echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, 'search=' . $order->customer['email_address'], 'SSL') . '">' . TEXT_CUSTOMER_LOOKUP . '</a>'; ?></td>
              </tr>
            </table>
          </div>
          <div class="col-sm-4">
            <table class="table">
              <tr>
                <td><strong><?php echo ENTRY_SHIPPING_ADDRESS; ?></strong></td>
                <td><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td class="noprint"><a href="https://maps.google.com/maps/search/?api=1&amp;query=<?php echo urlencode($order->delivery['street_address'] . ',' . $order->delivery['city'] . ',' . $order->delivery['state'] . ',' . $order->delivery['postcode']); ?>" rel="noreferrer" target="map"><i class="fa fa-map">&nbsp;</i> <u><?php echo TEXT_MAP_SHIPPING_ADDRESS; ?></u></a></td>
              </tr>
<?php
  $address_footer_suffix = '';
  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_ADDRESS_FOOTERS', 'delivery', $address_footer_suffix, $order->delivery);
  if (!empty($address_footer_suffix)) {
  ?>
                <tr><td>&nbsp;</td><td><?php echo $address_footer_suffix; ?></td></tr>
<?php } ?>
            </table>
          </div>
          <div class="col-sm-4">
            <table class="table">
              <tr>
                <td><strong><?php echo ENTRY_BILLING_ADDRESS; ?></strong></td>
                <td><?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td class="noprint"><a href="https://maps.google.com/maps/search/?api=1&amp;query=<?php echo urlencode($order->billing['street_address'] . ',' . $order->billing['city'] . ',' . $order->billing['state'] . ',' . $order->billing['postcode']); ?>" rel="noreferrer" target="map"><i class="fa fa-map">&nbsp;</i> <u><?php echo TEXT_MAP_BILLING_ADDRESS; ?></u></a></td>
              </tr>
<?php
  $address_footer_suffix = '';
  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_ADDRESS_FOOTERS', 'billing', $address_footer_suffix, $order->billing);
  if (!empty($address_footer_suffix)) {
  ?>
                <tr><td>&nbsp;</td><td><?php echo $address_footer_suffix; ?></td></tr>
<?php } ?>
            </table>
          </div>
        </div>
        <div class="row noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
        <div class="row"><strong><?php echo ENTRY_ORDER_ID . $oID; ?></strong></div>
        <div class="row">
          <table>
            <tr>
              <td class="main"><strong><?php echo ENTRY_DATE_PURCHASED; ?></strong></td>
              <td class="main"><?php echo zen_date_long($order->info['date_purchased']); ?></td>
            </tr>
            <tr>
              <td class="main"><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong></td>
              <td class="main"><?php echo $order->info['payment_method']; ?></td>
            </tr>
            <?php
            if (zen_not_null($order->info['cc_type']) || zen_not_null($order->info['cc_owner']) || zen_not_null($order->info['cc_number'])) {
              ?>
              <tr class="noprint">
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo ENTRY_CREDIT_CARD_TYPE; ?></td>
                <td class="main"><?php echo $order->info['cc_type']; ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo ENTRY_CREDIT_CARD_OWNER; ?></td>
                <td class="main"><?php echo $order->info['cc_owner']; ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></td>
                <td class="main"><?php echo $order->info['cc_number'] . (zen_not_null($order->info['cc_number']) && !strstr($order->info['cc_number'], 'X') && !strstr($order->info['cc_number'], '********') ? '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_ORDERS, '&action=mask_cc&oID=' . $oID, 'NONSSL') . '" class="noprint">' . TEXT_MASK_CC_NUMBER . '</a>' : ''); ?></td>
              </tr>
              <?php if (zen_not_null($order->info['cc_cvv'])) { ?>
                <tr>
                  <td class="main"><?php echo ENTRY_CREDIT_CARD_CVV; ?></td>
                  <td class="main"><?php echo $order->info['cc_cvv'] . (zen_not_null($order->info['cc_cvv']) && !strstr($order->info['cc_cvv'], TEXT_DELETE_CVV_REPLACEMENT) ? '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_ORDERS, '&action=delete_cvv&oID=' . $oID, 'NONSSL') . '" class="noprint">' . TEXT_DELETE_CVV_FROM_DATABASE . '</a>' : ''); ?></td>
                </tr>
              <?php } ?>
              <tr>
                <td class="main"><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></td>
                <td class="main"><?php echo $order->info['cc_expires']; ?></td>
              </tr>
              <?php
            }
            ?>
          </table>
          <?php $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_PAYMENTDATA_COLUMN2', $oID, $order); ?>
        </div>
        <?php /* BOF Super Order 7 of 21 Payments Section */ ?>
        <div class="row">
           <?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?>
        </div>
        <div class="row">
          <table class="table">
            <tr>
              <td class="main"><strong><i class="fa fa-2x fa-money"></i><?php echo '&nbsp;' . (!$so->payment && !$so->refund && !$so->purchase_order && !$so->po_payment ? TEXT_NO_PAYMENT_DATA : TEXT_PAYMENT_DATA); ?></strong></td>
              <td class="text-right" colspan="6">
                <?php echo $so->button_add('payment'); ?>
                <?php echo $so->button_add('purchase_order'); ?>
                <?php echo $so->button_add('refund'); ?>
              </td>
            </tr>
          </table>
          <?php if ($so->payment || $so->refund || $so->purchase_order || $so->po_payment) { ?>
            <table class="table table-hover">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent"><?php echo PAYMENT_TABLE_NUMBER; ?></th>
                  <th class="dataTableHeadingContent"><?php echo PAYMENT_TABLE_NAME; ?></th>
                  <th class="dataTableHeadingContent"><?php echo PAYMENT_TABLE_AMOUNT; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo PAYMENT_TABLE_TYPE; ?></th>
                  <th class="dataTableHeadingContent"><?php echo PAYMENT_TABLE_POSTED; ?></th>
                  <th class="dataTableHeadingContent"><?php echo PAYMENT_TABLE_MODIFIED; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo PAYMENT_TABLE_ACTION; ?></th>
                </tr>
              </thead>
              <tbody>
                  <?php
                  $original_grand_total_paid = 0;
                  if ($so->payment) {
                    for ($a = 0; $a < sizeof($so->payment); $a++) {
                      $original_grand_total_paid = $original_grand_total_paid + $so->payment[$a]['amount'];
                      ?>
                    <tr class="paymentRow" onclick="popupWindow('<?php echo zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=payment&index=' . $so->payment[$a]['index'] . '&action=my_update', 'NONSSL'); ?>', 'scrollbars=yes,resizable=yes,width=600,height=500,screenX=150,screenY=100,top=100,left=150')">
                      <td class="paymentContent"><?php echo $so->payment[$a]['number']; ?></td>
                      <td class="paymentContent"><?php echo $so->payment[$a]['name']; ?></td>
                      <td class="paymentContent text-right"><strong><?php echo $currencies->format($so->payment[$a]['amount']); ?></strong></td>
                      <td class="paymentContent text-center"><?php echo $so->full_type($so->payment[$a]['type']); ?></td>
                      <td class="paymentContent"><?php echo zen_datetime_short($so->payment[$a]['posted']); ?></td>
                      <td class="paymentContent"><?php echo zen_datetime_short($so->payment[$a]['modified']); ?></td>
                      <td class="paymentContent text-right">
                          <?php echo $so->button_update('payment', $so->payment[$a]['index']); ?>
                          <?php echo $so->button_delete('payment', $so->payment[$a]['index']); ?>
                      </td>

                    </tr>
                    <?php
                    if ($so->refund) {
                      for ($b = 0; $b < sizeof($so->refund); $b++) {
                        if ($so->refund[$b]['payment'] == $so->payment[$a]['index']) {
                          ?>
                          <tr class="refundRow" onclick="popupWindow('<?php echo zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=refund&index=' . $so->refund[$b]['index'] . '&action=my_update'); ?>', 'scrollbars=yes,resizable=yes,width=400,height=300,screenX=150,screenY=100,top=100,left=150')">
                            <td class="refundContent"><?php echo $so->refund[$b]['number']; ?></td>
                            <td class="refundContent"><?php echo $so->refund[$b]['name']; ?></td>
                            <td class="refundContent text-right"><strong><?php echo '-' . $currencies->format($so->refund[$b]['amount']); ?></strong></td>
                            <td class="refundContent text-center"><?php echo $so->full_type($so->refund[$b]['type']); ?></td>
                            <td class="refundContent"><?php echo zen_datetime_short($so->refund[$b]['posted']); ?></td>
                            <td class="refundContent"><?php echo zen_datetime_short($so->refund[$b]['modified']); ?></td>
                            <td class="refundContent text-right">
                                <?php echo $so->button_update('refund', $so->refund[$b]['index']); ?>
                                <?php echo $so->button_delete('refund', $so->refund[$b]['index']); ?>
                            </td>
                          </tr>
                          <?php
                        }  // END if ($so->refund[$b]['payment'] == $so->payment[$a]['index'])
                      }  // END for($b = 0; $b < sizeof($so->refund); $b++)
                    }  // END if ($so->refund)
                  }  // END for($a = 0; $a < sizeof($payment); $a++)
                }  // END if ($so->payment)
                if ($so->purchase_order) {
                  for ($c = 0; $c < sizeof($so->purchase_order); $c++) {
                    if ($c < 1 && $so->payment) {
                      ?>
                      <tr>
                        <td colspan="7"><?php echo zen_black_line(); ?></td>
                      </tr>
                    <?php } ?>
                    <tr class="purchaseOrderRow" onclick="popupWindow('<?php echo zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=purchase_order&index=' . $so->purchase_order[$c]['index'] . '&action=my_update'); ?>', 'scrollbars=yes,resizable=yes,width=400,height=300,screenX=150,screenY=100,top=100,left=150')">
                      <td class="purchaseOrderContent" colspan="4"><?php echo $so->purchase_order[$c]['number']; ?></td>
                      <td class="purchaseOrderContent"><?php echo zen_datetime_short($so->purchase_order[$c]['posted']); ?></td>
                      <td class="purchaseOrderContent"><?php echo zen_datetime_short($so->purchase_order[$c]['modified']); ?></td>
                      <td class="purchaseOrderContent text-right">
                          <?php echo $so->button_update('purchase_order', $so->purchase_order[$c]['index']); ?>
                          <?php echo $so->button_delete('purchase_order', $so->purchase_order[$c]['index']); ?>
                      </td>
                    </tr>
                    <?php
                    if ($so->po_payment) {
                      for ($d = 0; $d < sizeof($so->po_payment); $d++) {
                        if ($so->po_payment[$d]['assigned_po'] == $so->purchase_order[$c]['index']) {
                          ?>
                          <tr class="paymentRow" onclick="popupWindow('<?php echo zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=payment&index=' . $so->po_payment[$d]['index'] . '&action=my_update'); ?>', 'scrollbars=yes,resizable=yes,width=400,height=300,screenX=150,screenY=100,top=100,left=150')">
                            <td class="paymentContent"><?php echo $so->po_payment[$d]['number']; ?></td>
                            <td class="paymentContent"><?php echo $so->po_payment[$d]['name']; ?></td>
                            <td class="paymentContent text-right"><strong><?php echo $currencies->format($so->po_payment[$d]['amount']); ?></strong></td>
                            <td class="paymentContent text-center"><?php echo $so->full_type($so->po_payment[$d]['type']); ?></td>
                            <td class="paymentContent"><?php echo zen_datetime_short($so->po_payment[$d]['posted']); ?></td>
                            <td class="paymentContent"><?php echo zen_datetime_short($so->po_payment[$d]['modified']); ?></td>
                            <td class="paymentContent text-right">
                                <?php echo $so->button_update('payment', $so->po_payment[$d]['index']); ?>
                                <?php echo $so->button_delete('payment', $so->po_payment[$d]['index']); ?>
                            </td>
                          </tr>
                          <?php
                          if ($so->refund) {
                            for ($e = 0; $e < sizeof($so->refund); $e++) {
                              if ($so->refund[$e]['payment'] == $so->po_payment[$d]['index']) {
                                ?>
                                <tr class="refundRow" onclick="popupWindow('<?php echo zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=refund&index=' . $so->refund[$e]['index'] . '&action=my_update'); ?>', 'scrollbars=yes,resizable=yes,width=400,height=300,screenX=150,screenY=100,top=100,left=150')">
                                  <td class="refundContent"><?php echo $so->refund[$e]['number']; ?></td>
                                  <td class="refundContent"><?php echo $so->refund[$e]['name']; ?></td>
                                  <td class="refundContent text-right"><strong><?php echo '-' . $currencies->format($so->refund[$e]['amount']); ?></strong></td>
                                  <td class="refundContent text-center"><?php echo $so->full_type($so->refund[$e]['type']); ?></td>
                                  <td class="refundContent"><?php echo zen_datetime_short($so->refund[$e]['posted']); ?></td>
                                  <td class="refundContent"><?php echo zen_datetime_short($so->refund[$e]['modified']); ?></td>
                                  <td class="refundContent text-right">
                                      <?php echo $so->button_update('refund', $so->refund[$e]['index']); ?>
                                      <?php echo $so->button_delete('refund', $so->refund[$e]['index']); ?>
                                  </td>
                                </tr>
                                <?php
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
                // display any refunds not tied directly to a payment
                if ($so->refund) {
                  for ($f = 0; $f < sizeof($so->refund); $f++) {
                    if ($so->refund[$f]['payment'] == 0) {
                      if ($f < 1) {
                        ?>
                        <tr>
                          <td colspan="7"><?php echo zen_black_line(); ?></td>
                        </tr>
                      <?php } ?>
                      <tr class="refundRow" onclick="popupWindow('<?php echo zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=refund&index=' . $so->refund[$f]['index'] . '&action=my_update'); ?>', 'scrollbars=yes,resizable=yes,width=400,height=300,screenX=150,screenY=100,top=100,left=150')">
                        <td class="refundContent"><?php echo $so->refund[$f]['number']; ?></td>
                        <td class="refundContent"><?php echo $so->refund[$f]['name']; ?></td>
                        <td class="refundContent text-right"><strong><?php echo '-' . $currencies->format($so->refund[$f]['amount']); ?></strong></td>
                        <td class="refundContent text-center"><?php echo $so->full_type($so->refund[$f]['type']); ?></td>
                        <td class="refundContent"><?php echo zen_datetime_short($so->refund[$f]['posted']); ?></td>
                        <td class="refundContent"><?php echo zen_datetime_short($so->refund[$f]['modified']); ?></td>
                        <td class="refundContent text-right">
                            <?php echo $so->button_update('refund', $so->refund[$f]['index']); ?>
                            <?php echo $so->button_delete('refund', $so->refund[$f]['index']); ?>
                        </td>
                      </tr>
                      <?php
                    }
                  }
                }  // END if ($so->refund)
                ?>
              </tbody>
            </table>
          </div>
          <div class="row">
            <table>
              <tr>
                <td class="text-center"><?php echo HEADING_COLOR_KEY; ?></td>
                <td class="purchaseOrderRow text-center" width="120"><?php echo TEXT_PURCHASE_ORDERS; ?></td>
                <td class="paymentRow text-center" width="120"><?php echo TEXT_PAYMENTS; ?></td>
                <td class="refundRow text-center" width="120"><?php echo TEXT_REFUNDS; ?></td>
              </tr>
            </table>
          </div>
        <?php } ?>
        <?php /* EOF Super Order 7 of 21 Payments Section */ ?>
        <?php
        if (isset($module) && (is_object($module) && method_exists($module, 'admin_notification'))) {
          ?>
          <div class="row noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?><br><a href="#" id="payinfo" class="noprint">Click for Additional Payment Handling Options</a></div>
          <div class="row" id="payment-details-section" style="display: none;"><?php echo $module->admin_notification($oID); ?></div>
          <div class="row noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
          <?php
        }
        ?>
        <?php /* BOF Super Order 8 of 21 */ ?>
        <!-- Begin Split Order Details //-->
        <?php
        $parent_child = $db->Execute("SELECT split_from_order, is_parent
                                      FROM " . TABLE_ORDERS . "
                                      WHERE orders_id = " . (int)$oID);
        ?>
        <div class="row">
            <?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?>
        </div>
        <?php
        if ($parent_child->fields['split_from_order']) {
          $another = new super_order($parent_child->fields['split_from_order']);
          if ($another->payment && $so->payment) {
            for ($i = 0; $i < sizeof($another->payment); $i++) {
              $payment = $another->payment[$i];
              $original_grand_total_paid = $original_grand_total_paid + $payment['amount'];
            }
            ?>
            <div class="row">
              <div class="col-sm-12">
                <strong><i class="fa fa-2x fa-money"></i> <?php echo ENTRY_ORIGINAL_PAYMENT_AMOUNT; ?></strong>
                <?php echo $currencies->format($original_grand_total_paid); ?>
              </div>
            </div>
            <div class="row">
                <?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?>
            </div>

            <?php
          }
        }
        if (!$so->status && !$parent_child->fields['split_from_order'] && sizeof($order->products) > 1) {
          ?>

          <div class="row">
            <div class="col-sm-12">
              <a href="javascript:popupWindow('<?php echo zen_href_link(FILENAME_SUPER_EDIT, 'oID=' . $oID . '&target=product'); ?>', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=725,height=450,screenX=150,screenY=100,top=100,left=150')" class="btn btn-info btn-sm"><i class="fa fa-files-o fa-lg" aria-hidden="true"></i> <?php echo ICON_EDIT_PRODUCT; ?></a>
            </div>
          </div>
        <?php }
        ?>
        <!-- End Split Order Details //-->
        <?php /* EOF Super Order 8 of 21 */ ?>
        <div class="row">
          <table class="table">
            <tr class="dataTableHeadingRow">
              <th class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
              <th class="dataTableHeadingContent hidden-xs"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></th>
              <th class="dataTableHeadingContent text-right hidden-xs"><?php echo TABLE_HEADING_TAX; ?></th>
              <th class="dataTableHeadingContent text-right"><?php echo ($show_including_tax) ? TABLE_HEADING_PRICE_EXCLUDING_TAX : TABLE_HEADING_PRICE; ?></th>
<?php if ($show_including_tax)  { ?>
              <th class="dataTableHeadingContent text-right hidden-xs"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></th>
<?php } ?>
              <th class="dataTableHeadingContent text-right"><?php echo ($show_including_tax) ? TABLE_HEADING_TOTAL_EXCLUDING_TAX : TABLE_HEADING_TOTAL; ?></th>
<?php if ($show_including_tax)  { ?>
              <th class="dataTableHeadingContent text-right hidden-xs"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></th>
<?php } ?>
            </tr>
            <?php
            /* BOF Super Orders 9 of 21 */
            if (isset($_GET['cmd'])) {
              $tempcmd = $_GET['cmd'];
            }
            $_GET['cmd'] = FILENAME_ORDERS_PACKINGSLIP;
            echo zen_draw_form('split_packing', FILENAME_ORDERS_PACKINGSLIP, '', 'get', 'target="_blank"', true) . "\n";
            unset($_GET['cmd']);
            if (isset($tempcmd)) {
                $_GET['cmd'] = $tempcmd;
            }
            echo zen_draw_hidden_field('oID', (int)$oID) . "\n";
            echo zen_draw_hidden_field('split', 'true') . "\n";
            echo zen_draw_hidden_field('reverse_count', 0) . "\n";
            /* EOF Super Orders 9 of 21 */
            for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
              if (DISPLAY_PRICE_WITH_TAX_ADMIN == 'true') {
                $priceIncTax = $currencies->format(zen_round(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), $currencies->get_decimal_places($order->info['currency'])) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
              } else {
                $priceIncTax = $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
              }
              ?>
              <tr class="dataTableRow">
                <td class="dataTableContent text-right">
                  <?php echo $order->products[$i]['qty']; ?>&nbsp;x
                </td>
                <td class="dataTableContent">
                <?php
                    echo $order->products[$i]['name'];
                    if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
                      for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
                        echo '<br><span style="white-space:nowrap;"><small>&nbsp;<i> - ';
                        echo $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value']));
                        if (zen_is_option_file($order->products[$i]['attributes'][$j]['option_id'])) {
                          $upload_name = zen_get_uploaded_file($order->products[$i]['attributes'][$j]['value']);
                          echo ' ' . '<a href="' . zen_href_link(FILENAME_ORDERS, 'action=download&oID=' . $oID . '&filename=' .  $upload_name) . '">' . TEXT_DOWNLOAD . '</a>' . ' ';
                        }
                        if ($order->products[$i]['attributes'][$j]['price'] != '0') {
                          echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
                        }
                        if ($order->products[$i]['attributes'][$j]['product_attribute_is_free'] == '1' and $order->products[$i]['product_is_free'] == '1') {
                          echo TEXT_INFO_ATTRIBUTE_FREE;
                        }
                        echo '</i></small></span>';
                      }
                    }
                    // Mobile phones only
                    echo '<span class="visible-xs">';
                    echo ' (' . $order->products[$i]['model'] .')';
                    echo '</span>';
                ?>
                </td>
                <td class="dataTableContent hidden-xs">
                  <?php echo $order->products[$i]['model']; ?>
                </td>
                <td class="dataTableContent text-right hidden-xs">
                  <?php echo zen_display_tax_value($order->products[$i]['tax']); ?>%
                </td>
                <td class="dataTableContent text-right">
                  <strong><?php echo $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format($order->products[$i]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : ''); ?></strong>
                </td>
<?php if ($show_including_tax)  { ?>
                <td class="dataTableContent text-right hidden-xs">
                  <strong><?php echo $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) . ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) : ''); ?></strong>
                </td>
<?php } ?>
                <td class="dataTableContent text-right">
                  <strong><?php echo $currencies->format(zen_round($order->products[$i]['final_price'], $currencies->get_decimal_places($order->info['currency'])) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format($order->products[$i]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : ''); ?></strong>
                </td>
<?php if ($show_including_tax)  { ?>
                <td class="dataTableContent text-right hidden-xs">
                  <strong><?php echo $priceIncTax; ?>
                    <?php if ($order->products[$i]['onetime_charges'] != 0) {
                          echo '<br>' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']);
                      }
                    ?>
                  </strong>
                </td>
<?php } ?>
              </tr>
            <?php } ?>
            <tr>
                <?php /* BOF Super Orders 10 of 21 */ ?>
                <?php
                if ($parent_child->fields['split_from_order']) {
                  ?>
                <td>
                  <button type="submit"><i class="fa fa-reply fa-lg fa-rotate-180" aria-hidden="true"></i>&nbsp;<?php echo BUTTON_SPLIT; ?></button><br>
                  <?php echo TEXT_DISPLAY_ONLY; ?>
                </td>
                <!-- Begin Order Totals Modified for Super Orders//-->
                <?php
                $colspan = 7;
              } else {
                $colspan = 8;
              }
              ?>
              <?php echo '</form> '; ?>
              <?php /* EOF Super Orders 10 of 21 */ ?>
<?php if ($show_including_tax)  { ?>
              <td colspan="8">
<?php } else { ?>
              <td colspan="6">
<?php } ?>
                <table style="margin-right: 0; margin-left: auto;">
                    <?php
                    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
                      ?>
                    <tr>
                      <td class="<?php echo str_replace('_', '-', $order->totals[$i]['class']); ?>-Text text-right">
                          <?php echo $order->totals[$i]['title']; ?>
                      </td>
                      <td class="<?php echo str_replace('_', '-', $order->totals[$i]['class']); ?>-Amount text-right">
                          <?php echo $currencies->format($order->totals[$i]['value'], true, $order->info['currency'], $order->info['currency_value']); ?>
                      </td>
                    </tr>
                    <?php
                  }

                  /* BOF Super Orders 11 of 21 */
                  // determine what to display on the "Amount Applied" and "Balance Due" lines
                  $amount_applied = $currencies->format($so->amount_applied);
                  $balance_due = $currencies->format($so->balance_due);

                  // determine display format of the number
                  // 'balanceDueRem' = customer still owes money
                  // 'balanceDueNeg' = customer is due a refund
                  // 'balanceDueNone' = order is all paid up
                  // 'balanceDueNull' = balance nullified by order status
                  switch ($so->status) {
                    case 'completed':
                      switch ($so->balance_due) {
                        case 0:
                          $class = 'balanceDueNone';
                          break;
                        case $so->balance_due < 0:
                          $class = 'balanceDueNeg';
                          break;
                        case $so->balance_due > 0:
                          $class = 'balanceDueRem';
                          break;
                      }
                      break;

                    case 'cancelled':
                      switch ($so->balance_due) {
                        case 0:
                          $class = 'balanceDueNone';
                          break;
                        case $so->balance_due < 0:
                          $class = 'balanceDueNeg';
                          break;
                        case $so->balance_due > 0:
                          $class = 'balanceDueRem';
                          break;
                      }
                      break;

                    default:
                      switch ($so->balance_due) {
                        case 0:
                          $class = 'balanceDueNone';
                          break;
                        case $so->balance_due < 0:
                          $class = 'balanceDueNeg';
                          break;
                        case $so->balance_due > 0:
                          $class = 'balanceDueRem';
                          break;
                      }
                      break;
                  }
                  /* BOF added to get currency type and value for totals */
                  $dbc = $db->Execute("SELECT currency, currency_value
                                       FROM " . TABLE_ORDERS . "
                                       WHERE orders_id =" . (int)$oID);
                  $cu = $dbc->fields['currency'];
                  $cv = $dbc->fields['currency_value'];
                  /* EOF added to get currency type and value for totals */
                  ?>
                  <tr>
                    <td colspan="2">&nbsp;</td>
                  </tr>
                  <tr>
                    <td class="ot-tax-Text text-right"><?php echo ENTRY_AMOUNT_APPLIED_CUST . ' (' . $cu . ')'; ?></td>
                    <td class="ot-tax-Amount text-right"><?php echo $currencies->format($so->amount_applied, true, $order->info['currency'], $order->info['currency_value']); ?></td>
                  </tr>
                  <tr>
                    <td class="ot-tax-Text text-right"><?php echo ENTRY_BALANCE_DUE_CUST . ' (' . $cu . ')'; ?></td>
                    <td class="ot-tax-Amount text-right"><?php echo $currencies->format($so->balance_due, true, $order->info['currency'], $order->info['currency_value']); ?></td>
                  </tr>
                  <tr>
                    <td colspan="2">&nbsp;</td>
                  </tr>
                  <tr>
                    <td class="ot-tax-Text text-right"><?php echo ENTRY_AMOUNT_APPLIED_SHOP; ?></td>
                    <td class="ot-tax-Amount text-right"><?php echo $amount_applied; ?></td>
                  </tr>
                  <tr>
                    <td class="ot-tax-Text text-right"><?php echo ENTRY_BALANCE_DUE_SHOP; ?></td>
                    <td class="ot-tax-Amount text-right"><?php echo $balance_due; ?></td>
                  </tr>
                </table>
                <?php /* EOF Super Orders 11 of 21 */ ?>
              </td>
            </tr>
          </table>
        </div>
        <?php /* BOF Super Orders 12 of 21 */ ?>
        <div class="row" style="float:right;">
            <?php if (!$so->status) { ?>
            <table class="table table-condensed table-bordered">
              <thead>
                <tr>
                  <th class="invoiceHeading"><strong><?php echo TABLE_HEADING_FINAL_STATUS; ?></strong></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <a href="<?php echo zen_href_link(FILENAME_ORDERS, 'action=mark_completed&oID=' . $oID); ?>" class="btn btn-success btn-sm" role="button"><i class="fa fa-lg fa-check-square-o" aria-hidden="true"></i> <?php echo ICON_MARK_COMPLETED; ?></a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href="<?php echo zen_href_link(FILENAME_ORDERS, 'action=mark_cancelled&oID=' . $oID); ?>" class="btn btn-danger btn-sm" role="button"><i class="fa fa-lg fa-ban" aria-hidden="true"></i> <?php echo ICON_MARK_CANCELLED; ?></a>
                  </td>
                </tr>
              </tbody>
            </table>
          <?php } ?>
        </div>
        <?php /* EOF Super Orders 12 of 21 */ ?>
        <div class="row">
            <?php
            // show downloads
            require(DIR_WS_MODULES . 'orders_download.php');
            ?>
        </div>
        <div class="row noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
        <div class="row">
          <table class="table-condensed table-striped table-bordered">
            <thead>
              <tr>
                <th class="text-center"><?php echo TABLE_HEADING_DATE_ADDED; ?></th>
                <th class="text-center hidden-xs"><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></th>
                <th class="text-center"><?php echo TABLE_HEADING_STATUS; ?></th>
<?php
  // -----
  // A watching observer can provide an associative array in the form:
  //
  // $extra_headings = array(
  //     array(
  //       'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
  //       'text' => $value
  //     ),
  // );
  //
  // Observer note:  Be sure to check that the $p2/$extra_headings value is specifically (bool)false before initializing, since
  // multiple observers might be injecting content!
  //
  $extra_headings = false;
  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_STATUS_HISTORY_EXTRA_COLUMN_HEADING', array(), $extra_headings);
  if (is_array($extra_headings)) {
      foreach ($extra_headings as $heading_info) {
          $align = (isset($heading_info['align'])) ? (' class="text-' . $heading_info['align'] . '"') : '';
?>
                <th<?php echo $align; ?>><strong><?php echo $heading_info['text']; ?></th>
<?php
      }
  }
?>
                <th class="text-center"><?php echo TABLE_HEADING_COMMENTS; ?></th>
                <th class="text-center hidden-xs"><?php echo TABLE_HEADING_UPDATED_BY; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $orders_history = $db->Execute("SELECT *
                                              FROM " . TABLE_ORDERS_STATUS_HISTORY . "
                                              WHERE orders_id = " . zen_db_input($oID) . "
                                              ORDER BY date_added");

                if ($orders_history->RecordCount() > 0) {
                  $first = true;
                  foreach ($orders_history as $item) {
                    ?>
                  <tr>
                    <td class="text-center"><?php echo zen_datetime_short($item['date_added']); ?></td>
                    <td class="text-center hidden-xs">
                        <?php
                        if ($item['customer_notified'] == '1') {
                          echo zen_image(DIR_WS_ICONS . 'tick.gif', TEXT_YES);
                        } else if ($item['customer_notified'] == '-1') {
                          echo zen_image(DIR_WS_ICONS . 'locked.gif', TEXT_HIDDEN);
                        } else {
                          echo zen_image(DIR_WS_ICONS . 'unlocked.gif', TEXT_VISIBLE);
                        }
                        ?>
                    </td>
                    <td><?php echo $orders_status_array[$item['orders_status_id']]; ?></td>
<?php
                    // -----
                    // A watching observer can provide an associative array in the form:
                    //
                    // $extra_data = array(
                    //     array(
                    //       'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
                    //       'text' => $value
                    //     ),
                    // );
                    //
                    // Observer note:  Be sure to check that the $p2/$extra_data value is specifically (bool)false before initializing, since
                    // multiple observers might be injecting content!
                    //
                    $extra_data = false;
                    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_STATUS_HISTORY_EXTRA_COLUMN_DATA', $orders_history->fields, $extra_data);
                    if (is_array($extra_data)) {
                        foreach ($extra_data as $data_info) {
                            $align = (isset($data_info['align'])) ? (' text-' . $data_info['align']) : '';
                  ?>
                                  <td class="smallText<?php echo $align; ?>"><?php echo $data_info['text']; ?></td>
                  <?php
                        }
                    }
?>
                    <td>
<?php
                        if ($first) {
                           echo nl2br(zen_db_output($item['comments']));
                           $first = false;
                        } else {
                           echo nl2br($item['comments']);
                        }
?>
                    </td>
                    <td class="text-center hidden-xs"><?php echo (!empty($item['updated_by'])) ? $item['updated_by'] : '&nbsp;'; ?></td>
                  </tr>
                  <?php
                }
              } else {
                ?>
                <tr>
                  <td colspan="4"><?php echo TEXT_NO_ORDER_HISTORY; ?></td>
                </tr>
                <?php
              }
              ?>
            </tbody>
          </table>
        </div>
<?php
    $additional_content = false;
    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_AFTER_STATUS_LISTING', $oID, $additional_content);
    if ($additional_content !== false) {
?>
        <div class="row noprint"><?php echo $additional_content; ?></div>
<?php
    }
?>
        <div class="row noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></div>
        <div class="row noprint">
          <div class="formArea">
              <?php echo zen_draw_form('statusUpdate', FILENAME_ORDERS, zen_get_all_get_params(array('action', 'language')) . 'action=update_order&language=' . $order->info['language_code'], 'post', 'class="form-horizontal"', true);
               echo zen_draw_hidden_field('camefrom', 'orderEdit'); // identify from where the form was submitted (infoBox/listing or details), to redirect back to this same page ?>
              <div class="form-group">
                  <?php echo zen_draw_label(TABLE_HEADING_COMMENTS, 'comments', 'class="col-sm-3 control-label"'); ?>
                  <div class="col-sm-9">
                      <?php echo zen_draw_textarea_field('comments', 'soft', '60', '5', '', 'id="comments" class="editorHook form-control"');
                      // remind admin user of the order/customer language in case of writing a comment.
                      if (count(zen_get_languages()) > 1) {
                          echo '<br>' . zen_get_language_icon($order->info['language_code']) . ' <strong>' . sprintf(TEXT_EMAIL_LANGUAGE, ucfirst(zen_get_language_name($order->info['language_code']))) . '</strong>';
                          echo zen_draw_hidden_field('admin_language', $_SESSION['languages_code']);
                      } ?>
                  </div>
              </div>
<?php
    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_ADDL_HISTORY_INPUTS', array());
?>
            <div class="form-group">
                <?php echo zen_draw_label(ENTRY_STATUS, 'status', 'class="col-sm-3 control-label"'); ?>
              <div class="col-sm-9">
                  <?php echo zen_draw_order_status_dropdown('status', $order->info['orders_status'], '', 'id="status" class="form-control"'); ?>
              </div>
            </div>
<?php
        // -----
        // Give an observer the chance to supply some additional status-related inputs.  Each
        // entry in the $extra_status_inputs returned contains:
        //
        // array(
        //    'label' => array(
        //        'text' => 'The label text',   (required)
        //        'addl_class' => {Any additional class to be applied to the label} (optional)
        //        'parms' => {Any additional parameters for the label, e.g. 'style="font-weight: 700;"} (optional)
        //    ),
        //    'input' => 'The HTML to be inserted' (required)
        // )
        $extra_status_inputs = array();
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_EXTRA_STATUS_INPUTS', $order, $extra_status_inputs);
        if (!empty($extra_status_inputs)) {
            foreach ($extra_status_inputs as $extra_status) {
                $addl_class = (isset($extra_status['label']['addl_class'])) ? (' ' . $extra_status['label']['addl_class']) : '';
                $parms = (isset($extra_status['label']['parms'])) ? (' ' . $extra_status['label']['parms']) : '';
?>
            <div class="form-group">
                <div class="col-sm-3 control-label<?php echo $addl_class; ?>"<?php echo $parms; ?>><?php echo $extra_status['label']['text']; ?></div>
                <div class="col-sm-9"><?php echo $extra_status['input']; ?></div>
            </div>
<?php
            }
        }
?>
            <div class="form-group">
                <div class="col-sm-3 control-label" style="font-weight: 700;"><?php echo ENTRY_NOTIFY_CUSTOMER; ?></div>
              <div class="col-sm-9">
                <div class="radio">
                  <label><?php echo zen_draw_radio_field('notify', '1', $notify_email) . TEXT_EMAIL; ?></label>
                </div>
                <div class="radio">
                  <label><?php echo zen_draw_radio_field('notify', '0', $notify_no_email) . TEXT_NOEMAIL; ?></label>
                </div>
                <div class="radio">
                  <label><?php echo zen_draw_radio_field('notify', '-1', $notify_hidden) . TEXT_HIDE; ?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
                <?php echo zen_draw_label(ENTRY_NOTIFY_COMMENTS, 'notify_comments', 'class="col-sm-3 control-label"'); ?>
              <div class="col-sm-9">
                  <?php echo zen_draw_checkbox_field('notify_comments', '', true, '', 'id="notify_comments"'); ?>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-9 col-sm-offset-3">
                <button type="submit" class="btn btn-info"><?php echo IMAGE_UPDATE; ?></button>
              </div>
            </div>
            <?php echo '</form>'; ?>
          </div>
        </div>
        <div class="row noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></div>
<?php
        // -----
        // Enable the addition of extra buttons when editing the order.
        //
        $extra_buttons = '';
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_EDIT_BUTTONS', $oID, $order, $extra_buttons);
?>
        <div class="row text-right noprint">
          <?php /* BOF Super Orders 13 of 21 */ ?>
          <a href="<?php echo zen_href_link(FILENAME_SUPER_DATA_SHEET, 'oID=' . $_GET['oID']); ?>" target="_blank" class="btn btn-info" role="button"><?php echo ICON_ORDER_PRINT; ?></a>
          <?php /* EOF Super Orders 13 of 21 */ ?>
          <a href="<?php echo zen_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $_GET['oID']); ?>" target="_blank" class="btn btn-primary" role="button"><?php echo IMAGE_ORDERS_INVOICE; ?></a>
          <a href="<?php echo zen_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID']); ?>" target="_blank" class="btn btn-primary" role="button"><?php echo IMAGE_ORDERS_PACKINGSLIP; ?></a>
          <?php echo $order_list_button; ?>
          <?php echo $extra_buttons; ?>
        </div>
        <?php
// check if order has open gv
        $gv_check = $db->Execute("SELECT order_id, unique_id
                                    FROM " . TABLE_COUPON_GV_QUEUE . "
                                    WHERE order_id = " . (int)$_GET['oID'] . "
                                    AND release_flag = 'N'
                                    LIMIT 1");
        if ($gv_check->RecordCount() > 0) {
          ?>
          <div class="row noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></div>
          <div class="row text-right noprint">
            <a href="<?php echo zen_href_link(FILENAME_GV_QUEUE, 'order=' . $_GET['oID']); ?>"><?php echo IMAGE_GIFT_QUEUE; ?></a>
          </div>
          <?php
        }
        ?>
        <?php
      } else {
        ?>
        <?php /* BOF Super Orders 14 of 21 */ ?>
        <div class="row">
          <div class="col-sm-12">
            <a href="<?php echo zen_href_link(FILENAME_SUPER_BATCH_STATUS); ?>" class="btn btn-info btn-sm" role="button"><?php echo BOX_CUSTOMERS_SUPER_BATCH_STATUS; ?></a>&nbsp;<a href="<?php echo zen_href_link(FILENAME_SUPER_BATCH_FORMS, ''); ?>" class="btn btn-info btn-sm" role="button"><?php echo BOX_CUSTOMERS_SUPER_BATCH_FORMS; ?></a>
          </div>
        </div>
        <?php /* EOF Super Orders 14 of 21 */ ?>
<?php
        // Additional notification, allowing admin-observers to include additional legend icons
        $extra_legends = '';
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_MENU_LEGEND', array(), $extra_legends);
?>
        <div class="row"><?php echo TEXT_LEGEND . ' ' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', TEXT_BILLING_SHIPPING_MISMATCH, 10, 10) . ' ' . TEXT_BILLING_SHIPPING_MISMATCH . $extra_legends; ?></div>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table id="orders-table" class="table table-hover">
              <thead>
                <tr class="dataTableHeadingRow">
                    <?php
// Sort Listing
                    switch ($_GET['list_order']) {
                      case "id-asc":
                        $disp_order = "c.customers_id";
                        break;
                      case "firstname":
                        $disp_order = "c.customers_firstname";
                        break;
                      case "firstname-desc":
                        $disp_order = "c.customers_firstname DESC";
                        break;
                      case "lastname":
                        $disp_order = "c.customers_lastname, c.customers_firstname";
                        break;
                      case "lastname-desc":
                        $disp_order = "c.customers_lastname DESC, c.customers_firstname";
                        break;
                      case "company":
                        $disp_order = "a.entry_company";
                        break;
                      case "company-desc":
                        $disp_order = "a.entry_company DESC";
                        break;
                      default:
                        $disp_order = "c.customers_id DESC";
                    }
                    ?>
                  <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ORDERS_ID; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                  <td class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
<?php if ($quick_view_popover_enabled) { ?>
                  <td></td>
<?php } ?>
                  <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                  <td class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_STATUS; ?></td>
                  <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_CUSTOMER_COMMENTS; ?></td>
<?php
  // -----
  // A watching observer can provide an associative array in the form:
  //
  // $extra_headings = array(
  //     array(
  //       'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
  //       'text' => $value
  //     ),
  // );
  //
  // Observer note:  Be sure to check that the $p2/$extra_headings value is specifically (bool)false before initializing, since
  // multiple observers might be injecting content!
  //
  $extra_headings = false;
  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_LIST_EXTRA_COLUMN_HEADING', array(), $extra_headings);
  if (is_array($extra_headings)) {
      foreach ($extra_headings as $heading_info) {
          $align = (isset($heading_info['align'])) ? (' text-' . $heading_info['align']) : '';
?>
                <td class="dataTableHeadingContent<?php echo $align; ?>"><?php echo $heading_info['text']; ?></td>
<?php
      }
  }
?>
                  <td class="dataTableHeadingContent noprint text-right"><?php echo TABLE_HEADING_ACTION; ?></td>
                </tr>
              </thead>
              <tbody>
                  <?php
// Only one or the other search
// create search_orders_products filter
                  $search = '';
                  $search_distinct = ' ';
                  $new_table = '';
                  $new_fields = '';
                  $keywords = '';
                  if (!empty($_GET['search_orders_products'])) {
                    $search_distinct = ' distinct ';
                    $new_table = " left join " . TABLE_ORDERS_PRODUCTS . " op on (op.orders_id = o.orders_id) ";
                    $keywords = zen_db_input(zen_db_prepare_input($_GET['search_orders_products']));
                    $search = " and (op.products_model like '%" . $keywords . "%' or op.products_name like '%" . $keywords . "%')";
                    if (substr(strtoupper($_GET['search_orders_products']), 0, 3) == 'ID:') {
                      $keywords = TRIM(substr($_GET['search_orders_products'], 3));
                      $search = " and op.products_id ='" . (int)$keywords . "'";
                    }
                  } elseif (!empty($_GET['search'])) {
// create search filter
                      $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
                      $search = " and (o.customers_city like '%" . $keywords . "%' or o.customers_postcode like '%" . $keywords . "%' or o.date_purchased like '%" . $keywords . "%' or o.billing_name like '%" . $keywords . "%' or o.billing_company like '%" . $keywords . "%' or o.billing_street_address like '%" . $keywords . "%' or o.delivery_city like '%" . $keywords . "%' or o.delivery_postcode like '%" . $keywords . "%' or o.delivery_name like '%" . $keywords . "%' or o.delivery_company like '%" . $keywords . "%' or o.delivery_street_address like '%" . $keywords . "%' or o.billing_city like '%" . $keywords . "%' or o.billing_postcode like '%" . $keywords . "%' or o.customers_email_address like '%" . $keywords . "%' or o.customers_name like '%" . $keywords . "%' or o.customers_company like '%" . $keywords . "%' or o.customers_street_address  like '%" . $keywords . "%' or o.customers_telephone like '%" . $keywords . "%' or o.ip_address  like '%" . $keywords . "%')";
                  }
                  $new_fields .= ", o.customers_company, o.customers_email_address, o.customers_street_address, o.delivery_company, o.delivery_name, o.delivery_street_address, o.billing_company, o.billing_name, o.billing_street_address, o.payment_module_code, o.shipping_module_code, o.orders_status, o.ip_address, o.language_code ";

                  $order_by = " ORDER BY o.orders_id DESC";
                  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_SEARCH_PARMS', $keywords, $search, $search_distinct, $new_fields, $new_table, $order_by);

                  $orders_query_raw = "SELECT " . $search_distinct . " o.orders_id, o.customers_id, o.customers_name, o.payment_method, o.shipping_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, o.order_total" .
                      $new_fields . "
                          FROM (" . TABLE_ORDERS . " o " .
                      $new_table . ")
                          LEFT JOIN " . TABLE_ORDERS_STATUS . " s ON (o.orders_status = s.orders_status_id AND s.language_id = " . (int)$_SESSION['languages_id'] . ")";


                  if (!empty($_GET['cID'])) {
                    $cID = (int)zen_db_prepare_input($_GET['cID']);
                    $orders_query_raw .= " WHERE o.customers_id = " . (int)$cID;
                  } elseif ($_GET['status'] != '') {
                    $status = (int)zen_db_prepare_input($_GET['status']);
                    $orders_query_raw .= " WHERE s.orders_status_id = " . (int)$status . $search;
                  } else {
                    $orders_query_raw .= (trim($search) != '') ? preg_replace('/ *AND /i', ' WHERE ', $search) : '';
                  }

                  $orders_query_raw .= $order_by;

// Split Page
// reset page when page is unknown
                  if (($_GET['page'] == '' or $_GET['page'] <= 1) && !empty($_GET['oID'])) {
                    $check_page = $db->Execute($orders_query_raw);
                    $check_count = 1;
                    if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_ORDERS) {
                      while (!$check_page->EOF) {
                        if ($check_page->fields['orders_id'] == $_GET['oID']) {
                          break;
                        }
                        $check_count++;
                        $check_page->MoveNext();
                      }
                      $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS_ORDERS) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS_ORDERS) != 0 ? .5 : 0)), 0);
                    } else {
                      $_GET['page'] = 1;
                    }
                  }

//    $orders_query_numrows = '';
                  $orders_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_ORDERS, $orders_query_raw, $orders_query_numrows);
                  $orders = $db->Execute($orders_query_raw);
                  while (!$orders->EOF) {
                    if ((!isset($_GET['oID']) || (isset($_GET['oID']) && ($_GET['oID'] == $orders->fields['orders_id']))) && !isset($oInfo)) {
                      $oInfo = new objectInfo($orders->fields);
                    }

                    if (isset($oInfo) && is_object($oInfo) && ($orders->fields['orders_id'] == $oInfo->orders_id)) {
                      echo '<tr id="defaultSelected" class="dataTableRowSelected order-listing-row" data-oid="' . $orders->fields['orders_id'] . '" data-current="current">' . "\n";
                    } else {
                      echo '<tr class="dataTableRow order-listing-row" data-oid="' . $orders->fields['orders_id'] . '">' . "\n";
                    }

                    $show_difference = '';
                    if ((strtoupper($orders->fields['delivery_name']) != strtoupper($orders->fields['billing_name']) and trim($orders->fields['delivery_name']) != '')) {
                      $show_difference = zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', TEXT_BILLING_SHIPPING_MISMATCH, 10, 10) . '&nbsp;';
                    }
                    if ((strtoupper($orders->fields['delivery_street_address']) != strtoupper($orders->fields['billing_street_address']) and trim($orders->fields['delivery_street_address']) != '')) {
                      $show_difference = zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', TEXT_BILLING_SHIPPING_MISMATCH, 10, 10) . '&nbsp;';
                    }
                    //-Additional "difference" icons can be added on a per-order basis and/or additional icons to be added to the "action" column.
                    $extra_action_icons = '';
                    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_SHOW_ORDER_DIFFERENCE', array(), $orders->fields, $show_difference, $extra_action_icons);

                    $show_payment_type = $orders->fields['payment_module_code'] . '<br>' . $orders->fields['shipping_module_code'];
                    /* BOF Super Orders 15 of 21 */
                    $close_status = so_close_status($orders->fields['orders_id']);
                    if ($close_status) {
                      $class = "status-" . $close_status['type'];
                    } else {
                      $class = "dataTableContent";
                    }
                    /* EOF Super Orders 15 of 21 */
                    $sql = "SELECT op.orders_products_id, op.products_quantity AS qty, op.products_name AS name, op.products_model AS model
                            FROM " . TABLE_ORDERS_PRODUCTS . " op
                            WHERE op.orders_id = " . (int)$orders->fields['orders_id'];
                    $orderProducts = $db->Execute($sql, false, true, 1800);
                    $product_details = '';
                    if ($includeAttributesInProductDetailRows) {
                      foreach($orderProducts as $product) {
                        $product_details .= $product['qty'] . ' x ' . $product['name'] . (!empty($product['model']) ? ' (' . $product['model'] . ')' :'') . "\n";
                        $sql = "SELECT products_options, products_options_values
                            FROM " .  TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                            WHERE orders_products_id = " . (int)$product['orders_products_id'] . " ORDER BY orders_products_attributes_id ASC";
                        $productAttributes = $db->Execute($sql, false, true, 1800);
                        foreach ($productAttributes as $attr) {
                          if (!empty($attr['products_options'])) {
                             $product_details .= '&nbsp;&nbsp;- ' . $attr['products_options'] . ': ' . zen_output_string_protected($attr['products_options_values']) . "\n";
                          }
                        }
                        $product_details .= '<hr>'; // add HR
                      }
                      $product_details = rtrim($product_details);
                      $product_details = preg_replace('~<hr>$~', '', $product_details); // remove last HR
                      $product_details = nl2br($product_details);
                    }
                    ?>
                <td class="dataTableContent text-center"><?php echo $show_difference . $orders->fields['orders_id']; ?></td>
                <td class="dataTableContent"><?php echo $show_payment_type; ?></td>
                <?php /* EOF Super Orders 16 of 21 */ ?>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $orders->fields['customers_id'], 'NONSSL') . '">' . zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW . ' ' . TABLE_HEADING_CUSTOMERS) . '</a>&nbsp;'; ?>
                    <?php
                    echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $orders->fields['customers_id'] . '&action=edit', 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_cust_info.gif', MINI_ICON_INFO) . '</a>&nbsp;';
                    echo '<a href="' . zen_href_link(FILENAME_ORDERS, 'cID=' . $orders->fields['customers_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_cust_orders.gif', MINI_ICON_ORDERS) . '</a>&nbsp;';
                    echo '<a href="' . zen_href_link(FILENAME_MAIL, 'origin=' . FILENAME_ORDERS . '&customer=' . $orders->fields['customers_email_address'] . '&cID=' . $orders->fields['customers_id']) . '">' . $orders->fields['customers_name'] . ($orders->fields['customers_company'] != '' ? '<br />' . $orders->fields['customers_company'] : '') . '</a>';
                    ?>
                </td>
                <?php /* EOF Super Orders 16 of 21 */ ?>
                <td class="dataTableContent text-right" title="<?php echo zen_output_string($product_details, array('"' => '&quot;', "'" => '&#39;', '<br />' => '', '<hr>' => "----\n")); ?>">
                  <?php echo strip_tags($currencies->format($orders->fields['order_total'], true, $orders->fields['currency'], $orders->fields['currency_value'])); ?>
                </td>
<?php if ($quick_view_popover_enabled) { ?>
                <td class="dataTableContent text-right dataTableButtonCell">
                    <a tabindex="0" class="btn btn-xs btn-link orderProductsPopover" role="button" data-toggle="popover"
                       data-trigger="focus"
                       data-placement="left"
                       title="<?php echo TEXT_PRODUCT_POPUP_TITLE; ?>"
                       data-content="<?php echo zen_output_string($product_details, array('"' => '&quot;', "'" => '&#39;', '<br />' => '<br>')); ?>"
                    >
                        <?php echo TEXT_PRODUCT_POPUP_BUTTON; ?>
                    </a>
                </td>
<?php } ?>
                <td class="dataTableContent text-center"><?php echo zen_datetime_short($orders->fields['date_purchased']); ?></td>
                <td class="dataTableContent text-right"><?php echo ($orders->fields['orders_status_name'] != '' ? $orders->fields['orders_status_name'] : TEXT_INVALID_ORDER_STATUS); ?></td>
                <?php $order_comments = zen_output_string_protected(zen_get_orders_comments($orders->fields['orders_id'])); ?>
                <td class="dataTableContent text-center"<?php if (!empty($order_comments)) echo ' title="' . $order_comments . '"'; ?>><?php if (!empty($order_comments)) echo zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', '', 16, 16); ?></td>
<?php
  // -----
  // A watching observer can provide an associative array in the form:
  //
  // $extra_data = array(
  //     array(
  //       'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
  //       'text' => $value
  //     ),
  // );
  //
  // Observer note:  Be sure to check that the $p3/$extra_data value is specifically (bool)false before initializing, since
  // multiple observers might be injecting content!
  //
  $extra_data = false;
  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_LIST_EXTRA_COLUMN_DATA', (isset($oInfo) ? $oInfo : array()), $orders->fields, $extra_data);
  if (is_array($extra_data)) {
      foreach ($extra_data as $data_info) {
          $align = (isset($data_info['align'])) ? (' text-' . $data_info['align']) : '';
?>
                <td class="dataTableContent<?php echo $align; ?>"><?php echo $data_info['text']; ?></td>
<?php
      }
  }
?>

                <td class="dataTableContent noprint text-right dataTableButtonCell">
                    <?php
                    echo '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders->fields['orders_id'] . '&action=edit', 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>' . $extra_action_icons;
                    ?>
                    &nbsp;
                    <?php
                    if (isset($oInfo) && is_object($oInfo) && ($orders->fields['orders_id'] == $oInfo->orders_id)) {
                      echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                    } else {
                      /* BOF Super Orders 17 of 21 */
                      echo '<a href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $orders->fields['orders_id'] . '&action=edit', 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_details.gif', ICON_ORDER_DETAILS) . '</a>&nbsp';
                      echo '<a href="' . zen_href_link(FILENAME_SUPER_SHIPPING_LABEL, 'oID=' . $orders->fields['orders_id']) . '" target="_blank">' . zen_image(DIR_WS_IMAGES . 'icon_shipping_label.gif', ICON_ORDER_SHIPPING_LABEL) . '</a>&nbsp;';
                      echo '<a href="' . zen_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $orders->fields['orders_id']) . '" target="_blank">' . zen_image(DIR_WS_IMAGES . 'icon_invoice.gif', ICON_ORDER_INVOICE) . '</a>&nbsp;';
                      echo '<a href="' . zen_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $orders->fields['orders_id']) . '" target="_blank">' . zen_image(DIR_WS_IMAGES . 'icon_packingslip.gif', ICON_ORDER_PACKINGSLIP) . '</a>&nbsp;';
                      echo '<a href="' . zen_href_link(FILENAME_SUPER_DATA_SHEET, 'oID=' . $orders->fields['orders_id']) . '" target="_blank">' . zen_image(DIR_WS_IMAGES . 'icon_print.gif', ICON_ORDER_PRINT) . '</a>&nbsp;';
                      echo '<a href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $orders->fields['orders_id'] . '&action=delete', 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete2.gif', ICON_ORDER_DELETE) . '</a>&nbsp;';
                      /* EOF Super Orders 17 of 21 */
                      echo '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID')) . 'oID=' . $orders->fields['orders_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                    }
                    ?>&nbsp;</td>
                </tr>
                <?php
                $orders->MoveNext();
              }
              ?>
              </tbody>
            </table>
            <table class="table">
              <tr>
                  <td><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_ORDERS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                  <td class="text-right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_ORDERS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'],
                          zen_get_all_get_params(['page', 'oID', 'action'])); ?></td>
              </tr>
              <?php
              if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
              ?>
                  <tr>
                      <td class="text-right" colspan="2">
                      <?php
                          echo '<a href="' . zen_href_link(FILENAME_ORDERS, '', 'NONSSL') . '" class="btn btn-default" role="button">' . IMAGE_RESET . '</a>';
                          if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
                              $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
                              echo '<br>' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
                          }
                      ?>
                      </td>
                  </tr>
              <?php
              }
              ?>
            </table>
          </div>
          <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
              <?php
              $heading = array();
              $contents = array();

              switch ($action) {
                case 'delete':
                  $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_ORDER . '</h4>');

                  $contents = array('form' => zen_draw_form('orders', FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . '&action=deleteconfirm', 'post', 'class="form-horizontal"', true) . zen_draw_hidden_field('oID', $oInfo->orders_id));
//      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br><br><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');
                  $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br><br><strong>' . ENTRY_ORDER_ID . $oInfo->orders_id . '<br>' . $oInfo->order_total . '<br>' . $oInfo->customers_name . ($oInfo->customers_company != '' ? '<br>' . $oInfo->customers_company : '') . '</strong>');
                  $contents[] = array('text' => '<br><label>' . zen_draw_checkbox_field('restock') . ' ' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY . '</label>');
                  $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id, 'NONSSL') . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                  break;
                default:
                  if (isset($oInfo) && is_object($oInfo)) {
                    $heading[] = array('text' => '<h4>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . zen_datetime_short($oInfo->date_purchased) . '</h4>');

                    $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '" class="btn btn-primary" role="button">' . IMAGE_DETAILS . '</a> <a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete', 'NONSSL') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                    $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id) . '" target="_blank" class="btn btn-info" role="button">' . IMAGE_ORDERS_INVOICE . '</a> <a href="' . zen_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id) . '" target="_blank" class="btn btn-info" role="button">' . IMAGE_ORDERS_PACKINGSLIP . '</a>');
                    /* BOF Super Orders 18 of 21 */
                    // Begin - Add Edit Order button to order order list page
                    if (SO_EDIT_ORDERS_SWITCH == 'True') {
                      $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_EDIT_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '">' . zen_image_button('button_edit.gif', ICON_ORDER_EDIT) . '</a>');
                    }
                    $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_SUPER_DATA_SHEET, 'oID=' . $oInfo->orders_id) . '" target="_blank" class="btn btn-info" role="button"><i class="fa fa-lg fa-print" aria-hidden="true"></i> ' . SUPER_IMAGE_ORDER_PRINT . '</a>');
                    $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_SUPER_SHIPPING_LABEL, 'oID=' . $oInfo->orders_id) . '" target="_blank" class="btn btn-info" role="button">' . SUPER_IMAGE_SHIPPING_LABEL . '</a>');
                    // End - Add Edit Order button to order order list page
                    /* EOF Super Orders 18 of 21 */
                    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_MENU_BUTTONS', $oInfo, $contents);

                    // each contents array is drawn in a div, so this form block must be a single array element.
                    $contents[] = ['text' =>
                        zen_draw_form('statusUpdate', FILENAME_ORDERS, zen_get_all_get_params(['action','language']) . 'action=update_order' . (!isset($_GET['oID']) ? '&oID=' . $oInfo->orders_id : '') . '&language=' . $oInfo->language_code, 'post', '', true) . // form action uses the order language to change the session language on the update. On initial page load (from another page), $_GET['oID'] is not set, hence clause in form action
                        '<fieldset style="border:solid thin slategray;padding:5px"><legend style="width:inherit;">&nbsp;' . IMAGE_UPDATE . '&nbsp;</legend>' .
                        ($oInfo->language_code !== $_SESSION['languages_code'] ? zen_draw_hidden_field('admin_language', $_SESSION['languages_code']) : '') . // if the order language is different to the current admin language, record the admin language, to restore it in the redirect after the status update email has been sent
                        '<label class="control-label" for="notify">' . IMAGE_SEND_EMAIL . '</label> ' .
                        zen_draw_checkbox_field('notify', '1', $notify_email, '', 'class="checkbox-inline" id="notify"') . "<br>\n" .
                        '<label class="control-label" for="status">' . ENTRY_STATUS . '</label>' . zen_draw_order_status_dropdown('status', $oInfo->orders_status, '', 'onChange="this.form.submit();" id="status" class="form-control"') . "\n" .
                        '</fieldset></form>' . "\n"];

                    $contents[] = array('text' => '<br>' . TEXT_DATE_ORDER_CREATED . ' ' . zen_date_short($oInfo->date_purchased));
                    $contents[] = array('text' => '<br>' . $oInfo->customers_email_address);
                    $contents[] = array('text' => TEXT_INFO_IP_ADDRESS . ' ' . $oInfo->ip_address);
                    if (zen_not_null($oInfo->last_modified)) {
                      $contents[] = array('text' => TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . zen_date_short($oInfo->last_modified));
                    }
                    $contents[] = array('text' => '<br>' . TEXT_INFO_PAYMENT_METHOD . ' ' . $oInfo->payment_method);
                    $contents[] = array('text' => '<br>' . ENTRY_SHIPPING . ' ' . $oInfo->shipping_method);

// check if order has open gv
                    $gv_check = $db->Execute("SELECT order_id, unique_id
                                              FROM " . TABLE_COUPON_GV_QUEUE . "
                                              WHERE order_id = " . (int)$oInfo->orders_id . "
                                              AND release_flag = 'N'
                                              LIMIT 1");
                    if ($gv_check->RecordCount() > 0) {
                      $goto_gv = '<a href="' . zen_href_link(FILENAME_GV_QUEUE, 'order=' . $oInfo->orders_id) . '" class="btn btn-primary" role="button">' . IMAGE_GIFT_QUEUE . '</a>';
                      $contents[] = array('text' => '<br>' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '', '3', 'style="width:100%"'));
                      $contents[] = array('align' => 'text-center', 'text' => $goto_gv);
                    }

                    // indicate if comments exist
                    $orders_history_query = $db->Execute("SELECT orders_status_id, date_added, customer_notified, comments
                                                          FROM " . TABLE_ORDERS_STATUS_HISTORY . "
                                                          WHERE orders_id = " . (int)$oInfo->orders_id . "
                                                          AND comments != ''");

                    if ($orders_history_query->RecordCount() > 0) {
                      $contents[] = array('text' => '<br>' . TABLE_HEADING_COMMENTS);
                      $contents[] = array('text' => nl2br(zen_output_string_protected($orders_history_query->fields['comments'])));
                    }

                    $contents[] = array('text' => '<br>' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '', '3', 'style="width:100%"'));
                    $order = new order($oInfo->orders_id);
                    $contents[] = array('text' => TABLE_HEADING_PRODUCTS . ': ' . sizeof($order->products));
                    for ($i = 0, $n=sizeof($order->products); $i <$n; $i++) {
                      $contents[] = array('text' => $order->products[$i]['qty'] . '&nbsp;x&nbsp;' . $order->products[$i]['name']);

                      if (!empty($order->products[$i]['attributes'])) {
                        for ($j = 0, $nn=sizeof($order->products[$i]['attributes']); $j < $nn; $j++) {
                          $contents[] = array('text' => '&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value'])) . '</i>');
                        }
                      }
                      if ($i > MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING and MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING != 0) {
                        $contents[] = array('text' => TEXT_MORE);
                        break;
                      }
                    }

                    if (sizeof($order->products) > 0) {
                      /* BOF Super Orders 19 of 21 */
                      // Begin add Edit Orders button to lower buttons
                      if (SO_EDIT_ORDERS_SWITCH == 'True') {
                        $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '" class="btn btn-primary" role="button">' . IMAGE_DETAILS . '</a>&nbsp;<a href="' . zen_href_link(FILENAME_EDIT_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '" class="btn btn-primary" role="button">' . ICON_ORDER_EDIT . '</a>');
                      } else {
                        $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '" class="btn btn-primary" role="button">' . IMAGE_DETAILS . '</a>');
                      }
                      // End add Edit Orders button to lower buttons
                      /* EOF Super Orders 19 of 21 */
                    }
                  }
                  break;
              }
              $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_MENU_BUTTONS_END', (isset($oInfo) ? $oInfo : array()), $contents);

              if ((zen_not_null($heading)) && (zen_not_null($contents))) {
                $box = new box;
                echo $box->infoBox($heading, $contents);
              }
              ?>
          </div>
          <?php /* BOF Super Orders 20 of 21 */ ?>
          <!-- SHORTCUT ICON LEGEND BOF-->
          <div class="row">
            <table>
              <thead>
                <tr>
                  <th colspan="2"><strong><?php echo TEXT_ICON_LEGEND; ?></strong></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', TEXT_BILLING_SHIPPING_MISMATCH, 10, 10); ?></td>
                  <td><?php echo TEXT_BILLING_SHIPPING_MISMATCH; ?></td>
                </tr>
                <tr>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_cust_info.gif', MINI_ICON_INFO); ?></td>
                  <td><?php echo MINI_ICON_INFO; ?></td>
                </tr>
                <tr>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_cust_orders.gif', MINI_ICON_ORDERS); ?></td>
                  <td><?php echo MINI_ICON_ORDERS; ?></td>
                </tr>
                <tr>
                  <td colspan="2"><?php echo zen_draw_separator('pixel_black.gif'); ?></td>
                </tr>
                <tr>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_details.gif', ICON_ORDER_DETAILS); ?></td>
                  <td><?php echo ICON_ORDER_DETAILS; ?></td>
                </tr>
                <tr>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_shipping_label.gif', ICON_ORDER_SHIPPING_LABEL); ?></td>
                  <td><?php echo ICON_ORDER_SHIPPING_LABEL; ?></td>
                </tr>
                <tr>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_invoice.gif', ICON_ORDER_INVOICE); ?></td>
                  <td><?php echo ICON_ORDER_INVOICE; ?></td>
                </tr>
                <tr>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_packingslip.gif', ICON_ORDER_PACKINGSLIP); ?></td>
                  <td><?php echo ICON_ORDER_PACKINGSLIP; ?></td>
                </tr>
                <!-- Begin - add Edit Orders to legend icons -->
                <?php if (SO_EDIT_ORDERS_SWITCH == 'True') { ?>
                  <tr>
                    <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_ORDER_EDIT); ?></td>
                    <td><?php echo ICON_ORDER_EDIT; ?></td>
                  </tr>
                <?php } ?>
                <!-- End - add Edit Orders to legend icons -->
                <tr>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_print.gif', ICON_ORDER_PRINT); ?></td>
                  <td><?php echo ICON_ORDER_PRINT; ?></td>
                </tr>
                <tr>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_delete2.gif', ICON_ORDER_DELETE); ?></td>
                  <td><?php echo ICON_ORDER_DELETE; ?></td>
                </tr>
                <tr>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO); ?></td>
                  <td><?php echo IMAGE_ICON_INFO; ?></td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- SHORTCUT ICON LEGEND EOF -->
          <?php /* EOF Super Orders 20 of 21 */  ?>
          <?php
      }
      ?>
      <!-- body_text_eof //-->

    </div>
    <!-- body_eof //-->

    <!--  enable on-page script tools -->
    <script>
        jQuery(document).ready(function() {
            jQuery("#payinfo").click(function () {
                jQuery("#payment-details-section").toggle()
            });
        });
        <?php
        $order_link = str_replace('&amp;', '&', zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . "oID=[*]"));
        ?>
        jQuery(function () {
            const orderLink = '<?php echo $order_link; ?>';
            jQuery("tr.order-listing-row td").not('.dataTableButtonCell').on('click', (function() {
                window.location.href = orderLink.replace('[*]', jQuery(this).parent().attr('data-oid') + (jQuery(this).parent().attr('data-current') ? '&action=edit' : ''));
            }));
            jQuery('[data-toggle="popover"]').popover({html:true,sanitize: true});
        })
    </script>


    <!-- footer //-->
    <div class="footer-area">
      <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <?php /* EOF Super Orders 21 of 21 */  ?>
    </div>
    <?php /* EOF Super Orders 21 of 21 */  ?>
    </div>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
