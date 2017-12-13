<?php

// change the following paths if necessary
//$yiic=dirname(__FILE__).'/../../lib/yii_framework/yii.php';
//$config=dirname(__FILE__).'/config/console.php';

$yiic='/data/epw/PHPProject/server/lib/yii_framework/yii.php';
$config='/data/epw/PHPProject/server/project/protected/config/console.php';

require_once($yiic);
Yii::createConsoleApplication($config)->run();