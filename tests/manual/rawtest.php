<?php

/*
 *  @author Yaasir Ketwaroo <ketwaroo.yaasir@gmail.com>
 */
error_reporting(-1);
require_once '../../src/Ketwaroo/PackageInfo.php';
require_once '../../src/Ketwaroo/Exception/ExceptionPackageInfo.php';

var_dump('__FILE__ test');
$x = Ketwaroo\PackageInfo::whereAmI(__FILE__);
var_dump($x->getPackageName());
var_dump($x->getPackageBasePath());

var_dump('cache test 1');
$y = Ketwaroo\PackageInfo::whereAmI(__FILE__);
var_dump($y->getPackageName());
var_dump($y->getPackageBasePath());

var_dump('cache test 2');
$z = Ketwaroo\PackageInfo::whereAmI(__DIR__);
var_dump($z->getPackageName());
var_dump($z->getPackageBasePath());

class ZZZ
{

    public function __construct()
    {
        var_dump('object test');
        var_dump(Ketwaroo\PackageInfo::whereAmI($this)->getPackageName());
        var_dump(Ketwaroo\PackageInfo::whereAmI($this)->getPackageBasePath());
    }

}

$zzz = new ZZZ();

