<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['request'] = array(
    'advance_pay' => 'http://www.hunwater.com'.'/order_api/plat_advance',
    'plat_pay' => 'http://www.hunwater.com'.'/order_api/plat_pay_record',
    'cancel' => 'http://www.hunwater.com'.'/order_api/cancel_order',
    'refund' => 'http://www.hunwater.com'.'/order_api/refund_order',
    'modify' => 'http://www.hunwater.com'.'/order_api/modify_price',
);
