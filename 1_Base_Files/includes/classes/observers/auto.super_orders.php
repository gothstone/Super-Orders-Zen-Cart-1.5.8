<?php
    /*  developed, copyrighted and brought to you by proseLA
        https://rossroberts.com

        01/2022  project: super_orders file: auto.super_orders.php
    */


    class zcObserverSuperOrders extends base
    {
    public function __construct()
    {
        $this->attach(
            $this,
            [
                'NOTIFY_ORDER_DURING_CREATE_ADDED_ORDER_HEADER',
            ]
        );
    }

        public function update(&$class, $eventID, &$p1, &$p2, &$p3, &$p4)
        {
            switch ($eventID) {
                case 'NOTIFY_ORDER_DURING_CREATE_ADDED_ORDER_HEADER':
                    if (zen_not_null($p1['cc_type']) || zen_not_null($p1['cc_owner']) || zen_not_null($p1['cc_number'])) {
                        require(DIR_WS_CLASSES . 'super_order.php');
                        $so = new super_order($p2);
                        $so->cc_line_item();
                    }
                    break;
                default:
                    break;
            }
        }
    }
