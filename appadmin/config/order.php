<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$host = 'http://www.hunwater.com';
$config['request'] = array(
    'advance_pay' => $host.'/order_api/plat_advance',
    'plat_pay' => $host.'/order_api/plat_pay_record',
    'cancel' => $host.'/order_api/cancel_order',
    'refund' => $host.'/order_api/refund_order',
    'modify' => $host.'/order_api/modify_price',
);
