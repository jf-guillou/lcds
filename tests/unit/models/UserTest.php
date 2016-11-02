<?php

namespace tests\models;

use app\models\User;

class UserTest extends \Codeception\Test\Unit
{
    public function testFindUserById()
    {
        expect_that($user = User::findIdentity('admin'));
        expect($user->username)->equals('admin');

        expect_not(User::findIdentity('non-existing-user'));
    }

    /**
     * @depends testFindUserById
     */
    public function testValidateUser($user)
    {
        $user = User::findIdentity('admin');

        expect_that($user->validatePassword('admin'));
        expect_not($user->validatePassword('123456'));
    }
}
