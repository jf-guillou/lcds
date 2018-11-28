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
 * @property User[] $users
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
            [['parent'], 'safe'],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => self::class, 'targetAttribute' => ['parent_id' => 'id']],
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
        return $this->hasMany(Content::class, ['flow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    /**
     * Set parent_id with flow.
     *
     * @param \app\models\Flow $flow parent flow
     */
    public function setParent($flow)
    {
        $this->parent_id = $flow->id;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreenHasFlows()
    {
        return $this->hasMany(ScreenHasFlow::class, ['flow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreens()
    {
        return $this->hasMany(Screen::class, ['id' => 'screen_id'])->viaTable('screen_has_flow', ['flow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserHasFlows()
    {
        return $this->hasMany(UserHasFlow::class, ['flow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['username' => 'user_username'])->viaTable('user_has_flow', ['flow_id' => 'id']);
    }

    /**
     * Build a query for a specific user, allowing to see only authorized flows.
     *
     * @param \yii\web\User $user
     *
     * @return \yii\db\ActiveQuery
     */
    public static function availableQuery($user)
    {
        if ($user->can('setFlowContent')) {
            return self::find();
        } elseif ($user->can('setOwnFlowContent') && $user->identity instanceof \app\models\User) {
            return self::find()->joinWith(['users'])->where(['username' => $user->identity->username]);
        }
    }

    /**
     * Check if a specific user is allowed to see this flow.
     *
     * @param \yii\web\User $user
     *
     * @return bool can see
     */
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
