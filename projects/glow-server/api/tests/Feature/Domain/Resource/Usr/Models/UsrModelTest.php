<?php

namespace Tests\Feature\Domain\Resource\Usr\Models;

use stdClass;
use Tests\TestCase;

class UsrModelTest extends TestCase
{
    public function test_modekKeyが作れている()
    {
        // Setup

        // Exercise
        $model1 = TestUsrModel::create('user1', 'test11', 101);
        $model2 = TestUsrModel::create('user1', 'test12', 102);
        $model3 = TestUsrModel::create('user2', 'test21', 201);

        // Verify
        $this->assertEquals('user1_test11', $model1->makeModelKey());
        $this->assertEquals('user1_test12', $model2->makeModelKey());
        $this->assertEquals('user2_test21', $model3->makeModelKey());
    }

    public function test_モデルのプロパティ変動判定が正しいことを確認()
    {
        // Setup
        $newModel = TestUsrModel::create('user1', 'new', 1);

        $obj = new stdClass();
        $obj->usr_user_id = 'user1';
        $obj->test_id = 'existing';
        $obj->test_int_value = 2;
        $existingModel = TestUsrModel::createFromRecord($obj);

        $obj = new stdClass();
        $obj->usr_user_id = 'user1';
        $obj->test_id = 'existingAndChanged';
        $obj->test_int_value = 3;
        $changedModel = TestUsrModel::createFromRecord($obj);
        $changedModel->setTestIntValue(333);

        $obj = new stdClass();
        $obj->usr_user_id = 'user1';
        $obj->test_id = 'existingAndChangedByNull';
        $obj->test_int_value = 4;
        $obj->test_string_nullable_value = null;
        $changedModel2 = TestUsrModel::createFromRecord($obj);
        $changedModel2->setTestStringNullableValue('not_null_string');

        $obj = new stdClass();
        $obj->usr_user_id = 'user1';
        $obj->test_id = 'existingAndChangedToNull';
        $obj->test_int_value = 5;
        $obj->test_string_nullable_value = 'not_null_string';
        $changedModel3 = TestUsrModel::createFromRecord($obj);
        $changedModel3->setTestStringNullableValue(null);

        // Exercise

        // Verify
        $this->assertTrue($newModel->isChanged());
        $this->assertTrue($newModel->isNew());

        $this->assertFalse($existingModel->isChanged());
        $this->assertFalse($existingModel->isNew());

        $this->assertTrue($changedModel->isChanged());
        $this->assertFalse($changedModel->isNew());

        $this->assertTrue($changedModel2->isChanged());
        $this->assertFalse($changedModel2->isNew());

        $this->assertTrue($changedModel3->isChanged());
        $this->assertFalse($changedModel3->isNew());
    }

    public function test_idがないなら自動生成されて、あるならそのまま使用できている()
    {
        // Setup
        $newModel = TestUsrModel::create('user1', 'new', 1);

        $obj = new stdClass();
        $obj->id = 'existing_model_id';
        $obj->usr_user_id = 'user1';
        $obj->test_id = 'existing';
        $obj->test_int_value = 2;
        $existingModel = TestUsrModel::createFromRecord($obj);

        // Exercise

        // Verify
        $this->assertNotEmpty($newModel->getId());
        $this->assertEquals('existing_model_id', $existingModel->getId());
    }

    public function test_syncOriginalでプロパティ変動なしの状態になっている()
    {
        // Setup
        $newModel = TestUsrModel::create('user1', 'new', 1);

        $obj = new stdClass();
        $obj->usr_user_id = 'user1';
        $obj->test_id = 'existing';
        $obj->test_int_value = 2;
        $existingModel = TestUsrModel::createFromRecord($obj);

        $obj = new stdClass();
        $obj->usr_user_id = 'user1';
        $obj->test_id = 'existingAndChanged';
        $obj->test_int_value = 3;
        $changedModel = TestUsrModel::createFromRecord($obj);
        $changedModel->setTestIntValue(333);

        // Exercise
        $newModel->syncOriginal();
        $existingModel->syncOriginal();
        $changedModel->syncOriginal();

        // Verify
        $this->assertFalse($newModel->isChanged());
        $this->assertFalse($newModel->isNew());

        $this->assertFalse($existingModel->isChanged());
        $this->assertFalse($existingModel->isNew());

        $this->assertFalse($changedModel->isChanged());
        $this->assertFalse($changedModel->isNew());
    }
}
