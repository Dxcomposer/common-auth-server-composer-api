<?php
require_once 'vendor/autoload.php';

$class=new \Dxkjcomposer\Comauthapi\CommonAuthAPi('11658741278226587648',['127.0.0.1:9501']);

//var_dump($class->login('web','haspwd'));
//var_dump($class->checkToken('e804c6f75988a41d1343690c92ebaafe'));
//var_dump($class->loginOut('e804c6f75988a41d1343690c92ebaafe'));
//var_dump($class->userDetail(['test1']));
//var_dump($class->projectDetail());
var_dump($class->userAll());
//var_dump($class->createUser('test1',1));
//var_dump($class->editUser('test1',1,998,'测试1'));