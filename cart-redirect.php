<?php

require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );
require( dirname( __FILE__ ) . '/vendor/autoload.php');


$WC_ZIFTR = $GLOBALS['wc_ziftr'];

$WC_ZIFTR->redirect_cart();
