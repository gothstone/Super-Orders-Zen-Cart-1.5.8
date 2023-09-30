<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Jun 04 New in v1.5.8-alpha $
*/

$define = [
    'HEADING_TITLE' => 'Orders',
    'HEADING_TITLE_DETAILS' => 'Order Details (#%u)',
    'HEADING_TITLE_SEARCH' => 'Order ID:',
    'HEADING_TITLE_STATUS' => 'Status:',
    'HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS' => 'Product Name or ID:XX or Model',
    'HEADING_TITLE_SEARCH_ALL' => 'Search: ',
    'HEADING_TITLE_SEARCH_PRODUCTS' => 'Product search: ',
    'TEXT_RESET_FILTER' => 'Remove search filter',
    'TABLE_HEADING_PAYMENT_METHOD' => 'Payment<br>Shipping',
    'TABLE_HEADING_ORDERS_ID' => 'ID',
    'TEXT_BILLING_SHIPPING_MISMATCH' => 'Billing and Shipping do not match ',
    'TABLE_HEADING_ZONE_INFO' => 'Zone',
    'TABLE_HEADING_ORDER_TOTAL' => 'Order Total',
    'TABLE_HEADING_DATE_PURCHASED' => 'Date Purchased',
    'TABLE_HEADING_TYPE' => 'Order Type',
    'TABLE_HEADING_QUANTITY' => 'Qty.',
    'TABLE_HEADING_UPDATED_BY' => 'Updated By',
    'ENTRY_CUSTOMER' => 'Customer:',
    'ENTRY_CUSTOMER_ADDRESS' => 'Customer Address:<br><i class="fa fa-2x fa-user"></i>',
    'ENTRY_SHIPPING_ADDRESS' => 'Shipping Address:<br><i class="fa fa-2x fa-truck"></i>',
    'ENTRY_BILLING_ADDRESS' => 'Billing Address:<br><i class="fa fa-2x fa-credit-card"></i>',
    'ENTRY_PAYMENT_METHOD' => 'Payment Method:',
    'ENTRY_CREDIT_CARD_TYPE' => 'Credit Card Type:',
    'ENTRY_CREDIT_CARD_OWNER' => 'Credit Card Owner:',
    'ENTRY_CREDIT_CARD_NUMBER' => 'Credit Card Number:',
    'ENTRY_CREDIT_CARD_CVV' => 'Credit Card CVV Number:',
    'ENTRY_CREDIT_CARD_EXPIRES' => 'Credit Card Expires:',
    'TEXT_ADDITIONAL_PAYMENT_OPTIONS' => 'Click for Additional Payment Handling Options',
    'ENTRY_SHIPPING' => 'Shipping:',
    'ENTRY_STATUS' => 'Status:',
    'ENTRY_NOTIFY_CUSTOMER' => 'Notify Customer:',
    'ENTRY_NOTIFY_COMMENTS' => 'Append Comments:',
    'TEXT_INFO_HEADING_DELETE_ORDER' => 'Delete Order',
    'TEXT_INFO_DELETE_INTRO' => 'Are you sure you want to delete this order?',
    'TEXT_INFO_RESTOCK_PRODUCT_QUANTITY' => 'Restock product quantity',
    'TEXT_DATE_ORDER_CREATED' => 'Date Created:',
    'TEXT_DATE_ORDER_LAST_MODIFIED' => 'Last Modified:',
    'TEXT_INFO_PAYMENT_METHOD' => 'Payment Method:',
    'TEXT_ALL_ORDERS' => 'All Orders',
    'EMAIL_SEPARATOR' => '------------------------------------------------------',
    'EMAIL_TEXT_SUBJECT' => 'Order Update',
    'EMAIL_TEXT_ORDER_NUMBER' => 'Order Number:',
    'EMAIL_TEXT_INVOICE_URL' => 'Order Details:',
    'EMAIL_TEXT_DATE_ORDERED' => 'Date Ordered:',
    'EMAIL_TEXT_COMMENTS_UPDATE' => '<em>The comments for your order are: </em>',
    'EMAIL_TEXT_STATUS_UPDATED' => 'Your order has been updated to the following status:' . "\n",
    'EMAIL_TEXT_STATUS_LABEL' => '<strong>New status:</strong> %s' . "\n\n",
    'EMAIL_TEXT_STATUS_PLEASE_REPLY' => 'Please reply to this email if you have any questions.' . "\n",
    'ERROR_ORDER_DOES_NOT_EXIST' => 'Error: Order does not exist.',
    'SUCCESS_ORDER_UPDATED' => 'Success: Order has been successfully updated.',
    'WARNING_ORDER_NOT_UPDATED' => 'Warning: Nothing to change. The order was not updated.',
    'TEXT_INFO_ATTRIBUTE_FREE' => '&nbsp;-&nbsp;<span class="alert">FREE</span>',
    'TEXT_DOWNLOAD' => 'Download',
    'TEXT_DOWNLOAD_TITLE' => 'Order Download Status',
    'TEXT_DOWNLOAD_STATUS' => 'Status',
    'TEXT_DOWNLOAD_FILENAME' => 'Filename',
    'TEXT_DOWNLOAD_MAX_DAYS' => 'Days',
    'TEXT_DOWNLOAD_MAX_COUNT' => 'Count',
    'TEXT_DOWNLOAD_AVAILABLE' => 'Available',
    'TEXT_DOWNLOAD_EXPIRED' => 'Expired',
    'TEXT_DOWNLOAD_MISSING' => 'Not on Server',
    'TEXT_EXTENSION_NOT_UNDERSTOOD' => 'File extension %s not supported',
    'TEXT_FILE_NOT_FOUND' => 'File not found',
    'IMAGE_ICON_STATUS_CURRENT' => 'Status - Available',
    'IMAGE_ICON_STATUS_EXPIRED' => 'Status - Expired',
    'IMAGE_ICON_STATUS_MISSING' => 'Status - Missing',
    'SUCCESS_ORDER_UPDATED_DOWNLOAD_ON' => 'Download was successfully enabled',
    'SUCCESS_ORDER_UPDATED_DOWNLOAD_OFF' => 'Download was successfully disabled',
    'TEXT_MORE' => '... more',
    'TEXT_INFO_IP_ADDRESS' => 'IP Address: ',
    'TEXT_DELETE_CVV_FROM_DATABASE' => 'Delete CVV from database',
    'TEXT_DELETE_CVV_REPLACEMENT' => 'Deleted',
    'TEXT_MASK_CC_NUMBER' => 'Mask this number',
    'TEXT_INFO_EXPIRED_DATE' => 'Expired Date:<br>',
    'TEXT_INFO_EXPIRED_COUNT' => 'Expired Count:<br>',
    'TABLE_HEADING_CUSTOMER_COMMENTS' => 'Customer<br>Comments',
    'TEXT_COMMENTS_YES' => 'Customer Comments - YES',
    'TEXT_COMMENTS_NO' => 'Customer Comments - NO',
    'TEXT_CUSTOMER_LOOKUP' => '<i class="fa fa-search"></i> Lookup Customer',
    'TEXT_INVALID_ORDER_STATUS' => '<span class="alert">(Invalid Order Status)</span>',
    'BUTTON_TO_LIST' => 'Order List',
    'SELECT_ORDER_LIST' => 'Jump to Order:',
    'TEXT_MAP_CUSTOMER_ADDRESS' => 'Map Customer Address',
    'TEXT_MAP_SHIPPING_ADDRESS' => 'Map Shipping Address',
    'TEXT_MAP_BILLING_ADDRESS' => 'Map Billing Address',
    'TEXT_EMAIL_LANGUAGE' => 'Order Language: %s',
    'SUCCESS_EMAIL_SENT' => 'Email %s sent to customer',
    'WARNING_PAYMENT_MODULE_DOESNT_EXIST' => "The order's payment module (%s) no longer exists.",
    'WARNING_PAYMENT_MODULE_NOTIFICATIONS_DISABLED' => 'The configuration of the order\'s payment module (%s) has changed. No refunds, auths, captures or voids can be done for this order.',
/* Super Order Additional Defines */
    'HEADING_TITLE_ORDERS_LISTING' => 'Orders Listing',
    'HEADING_TITLE_ORDER_DETAILS' => 'Order # ',
    'HEADING_TITLE_STATUS' => 'Status:',
    'HEADING_REOPEN_ORDER' => 'Re-Open Order',
    'TABLE_HEADING_STATUS_HISTORY' => 'Order Status History &amp; Comments',
    'TABLE_HEADING_ADD_COMMENTS' => 'Add Comments',
    'TABLE_HEADING_FINAL_STATUS' => 'Close Order',
    'TABLE_HEADING_PAYMENT_METHOD' => 'Payment Method',
    'PAYMENT_TABLE_NUMBER' => 'Number',
    'PAYMENT_TABLE_NAME' => 'Payor Name',
    'PAYMENT_TABLE_AMOUNT' => 'Amount',
    'PAYMENT_TABLE_TYPE' => 'Type',
    'PAYMENT_TABLE_POSTED' => 'Date Posted',
    'PAYMENT_TABLE_MODIFIED' => 'Last Modified',
    'PAYMENT_TABLE_ACTION' => 'Action',
    'ALT_TEXT_ADD' => 'Add',
    'ALT_TEXT_UPDATE' => 'Update',
    'ALT_TEXT_DELETE' => 'Delete',
    'ENTRY_PAYMENT_DETAILS' => 'Payment Details',
    'ENTRY_CUSTOMER_ADDRESS' => 'Customer Address:',
    'TEXT_ICON_LEGEND' => 'Action Icon Legend:',
    'TEXT_BILLING_SHIPPING_MISMATCH' => 'Billing and Shipping do not match',
    'TEXT_INFO_EXPIRED_DATE' => 'Expired Date:<br />',
    'TEXT_INFO_EXPIRED_COUNT' => 'Expired Count:<br />',
    'TEXT_INFO_SHIPPING_METHOD' => 'Shipping Method:',
    'TEXT_DISPLAY_ONLY' => '(Display Only)',
    'TEXT_CURRENT_STATUS' => 'Current Status: ',
    'SUCCESS_MARK_COMPLETED' => 'Success: Order #%s is completed!',
    'WARNING_MARK_CANCELLED' => 'Warning: Order #%s has been cancelled',
    'WARNING_ORDER_REOPEN' => 'Warning: Order #%s has been re-opened',
    'TEXT_NEW_WINDOW' => ' (New Window)',
    'IMAGE_SHIPPING_LABEL' => 'Shipping Label',
    'IMAGE_ORDER_DETAILS' => 'Display Order Details',
    'ICON_ORDER_DETAILS' => 'Display Order Details',
    'ICON_ORDER_PRINT' => 'Print Data Sheet' . TEXT_NEW_WINDOW);
    'ICON_ORDER_INVOICE' => 'Display Invoice' . TEXT_NEW_WINDOW);
    'ICON_ORDER_PACKINGSLIP' => 'Display Packing Slip' . TEXT_NEW_WINDOW);
    'ICON_ORDER_SHIPPING_LABEL' => 'Display Shipping Label' . TEXT_NEW_WINDOW);
    'ICON_ORDER_DELETE' => 'Delete Order',
    'ICON_EDIT_CONTACT' => 'Edit Contact Data',
    'ICON_EDIT_PRODUCT' => 'Split Order',
    'ICON_EDIT_HISTORY' => 'Edit Hidden (Admin) Comments',
    'ICON_CLOSE_STATUS' => 'Close Status',
    'ICON_MARK_COMPLETED' => 'Mark Order Completed',
    'ICON_MARK_CANCELLED' => 'Mark Order Cancelled',
    'ICON_ORDER_EDIT' => 'Edit this Order',
    'SUPER_IMAGE_ORDER_PRINT' => 'Print Data Sheet' . TEXT_NEW_WINDOW);
    'SUPER_IMAGE_ORDERS_INVOICE' => 'Display Invoice' . TEXT_NEW_WINDOW);
    'SUPER_IMAGE_ORDERS_PACKINGSLIP' => 'Display Packing Slip' . TEXT_NEW_WINDOW);
    'SUPER_IMAGE_SHIPPING_LABEL' => 'Display Shipping Label' . TEXT_NEW_WINDOW);
    'MINI_ICON_ORDERS' => 'Show Customer\'s Orders',
    'MINI_ICON_INFO' => 'Show Customer\'s Profile',
    'ENTRY_ORIGINAL_PAYMENT_AMOUNT' => 'Split Order - Grand Total Paid:&nbsp;&nbsp;&nbsp;&nbsp;',
    'ENTRY_AMOUNT_APPLIED_CUST' => 'Amount Applied:',
    'ENTRY_BALANCE_DUE_CUST' => 'Balance Due:',
    'ENTRY_AMOUNT_APPLIED_SHOP' => 'Amount Applied: (Default Store Currency)',
    'ENTRY_BALANCE_DUE_SHOP' => 'Balance Due: (Default Store Currency)',
    'HEADING_COLOR_KEY' => 'Color Key:',
    'TEXT_PURCHASE_ORDERS' => 'Purchase Order',
    'TEXT_PAYMENTS' => 'Payment',
    'TEXT_REFUNDS' => 'Refund',
    'BUTTON_SPLIT' => 'Split Packing Slip',
    'TEXT_NO_PAYMENT_DATA' => 'No Order Payment Data Available',
    'TEXT_PAYMENT_DATA' => 'Order Payment Data',
    'TEXT_MAILTO' => 'mailto',
    'TEXT_STORE_EMAIL' => 'web',
    'TEXT_WHOIS_LOOKUP' => 'whois',
];

return $define;