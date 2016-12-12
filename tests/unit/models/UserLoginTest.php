<?php

namespace tests\models;

use app\models\UserLogin;

class UserLoginTest extends \Codeception\Test\Unit
{
    public function testValidation()
    {
        $login = new UserLogin();

        $login->username = 'admin';
        $login->password = 'admin';
        $login->remember_me = true;

        expect_that($login->validate());

        $login->remember_me = 'non-boolean-value';

        expect_not($login->validate());
    }

    public function testSave()
    {
        $login = new UserLogin();

        $login->username = 'admin';
        $login->password = 'admin';

        expect_not($login->save());
    }
}
