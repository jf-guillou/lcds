<?php

namespace tests\models;

use app\models\Flow;

class FlowTest extends \Codeception\Test\Unit
{
    public function testCreation()
    {
        $flow = new Flow();
        $flow->name = 'test-flow';

        expect_that($flow->save());
    }

    public function testSetParent()
    {
        $parentFlow = new Flow();
        $parentFlow->name = 'parent-flow';
        $parentFlow->save();

        $childFlow = new Flow();
        $childFlow->name = 'child-flow';
        $childFlow->parent_id = $parentFlow->id;
        expect_that($childFlow->save());

        $childFlow->parent_id = null;
        $childFlow->parent = $parentFlow;
        expect_that($childFlow->save());
    }
}
