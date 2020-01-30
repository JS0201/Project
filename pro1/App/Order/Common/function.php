<?php



function ch_prom($type) {
    $arr = array(
        'amount_discount' => '满额立减',
        'number_discount' => '满件立减',
        'amount_give' => '满额赠礼',
        'number_give' => '满件赠礼',
    );
    return $arr[$type];
}

?>