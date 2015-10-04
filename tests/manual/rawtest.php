<?php

/*
 *  @author Yaasir Ketwaroo <ketwaroo.yaasir@gmail.com>
 */

require_once '../../src/K/PackageInfo.php';
require_once '../../src/K/Exception/ExceptionPackageInfo.php';

$x = Ketwaroo\PackageInfo::whereAmI(__FILE__);
var_dump($x->getPackageName());
var_dump($x->getPackageBasePath());

// cache test
$y = Ketwaroo\PackageInfo::whereAmI(__FILE__);
var_dump($y->getPackageName());
var_dump($y->getPackageBasePath());

// cahe test 2
$z = Ketwaroo\PackageInfo::whereAmI(__DIR__);
var_dump($z->getPackageName());
var_dump($z->getPackageBasePath());
