<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "flow".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $parent_id
 * @property Content[] $contents
 * @property Flow $parent
 * @property Flow[] $flows
 * @property ScreenHasFlow[] $screenHasFlows
 * @property Screen[] $screens
 * @property UserHasFlow[] $userHasFlows
 * @property User[] $userUsernames
 */
class Flow extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'flow';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent_id', 'id'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 1024],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => self::className(), 'targetAttribute' => ['parent_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'parent_id' => Yii::t('app', 'Parent'),
            'parent' => Yii::t('app', 'Parent flow'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContents()
    {
        return $this->hasMany(Content::className(), ['flow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreenHasFlows()
    {
        return $this->hasMany(ScreenHasFlow::className(), ['flow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreens()
    {
        return $this->hasMany(Screen::className(), ['id' => 'screen_id'])->viaTable('screen_has_flow', ['flow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserHasFlows()
    {
        return $this->hasMany(UserHasFlow::className(), ['flow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['username' => 'user_username'])->viaTable('user_has_flow', ['flow_id' => 'id']);
    }

    public static function availableQuery($user)
    {
        if ($user->can('setFlowContent')) {
            return self::find();
        } elseif ($user->can('setOwnFlowContent')) {
            return self::find()->joinWith(['users'])->where(['username' => $user->identity->username]);
        }
    }

    public function canView($user)
    {
        if ($user->can('setFlowContent')) {
            return true;
        }
        if ($user->can('setOwnFlowContent') && in_array($user->identity, $this->users)) {
            return true;
        }

        return false;
    }
}
