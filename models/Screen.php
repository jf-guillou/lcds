<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "screen".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $template_id
 * @property string $last_changes
 * @property ScreenTemplate $template
 * @property string $last_auth
 * @property bool $active
 * @property ScreenHasFlow[] $screenHasFlows
 * @property Flow[] $flows
 */
class Screen extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'screen';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['template_id'], 'integer'],
            [['last_changes', 'last_auth', 'template'], 'safe'],
            [['active'], 'boolean'],
            [['name'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 1024],
            [['template_id'], 'exist', 'skipOnError' => true, 'targetClass' => ScreenTemplate::className(), 'targetAttribute' => ['template_id' => 'id']],
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
            'template_id' => Yii::t('app', 'Template ID'),
            'last_changes' => Yii::t('app', 'Last Changes'),
            'last_auth' => Yii::t('app', 'Last Auth'),
            'active' => Yii::t('app', 'Active'),
        ];
    }

    /**
     * Loop through parent flows to build a flows tree.
     *
     * @return array flows with all parents
     */
    public function allFlows()
    {
        $ret = [];

        // Initialize main loop
        $newFlows = $this->flows;
        while (count($newFlows)) {
            // Append latest loop parents
            $ret = array_merge($ret, $newFlows);

            $parents = [];
            foreach ($newFlows as $flow) {
                if ($flow->parent_id != null) {
                    $f = $flow->parent;
                    if ($f) {
                        $parents[] = $f;
                    }
                }
            }
            // Prepare next loop, will merge if necessary later
            $newFlows = $parents;
        }

        return $ret;
    }

    /**
     * Update last_modified field to force screen reload.
     */
    public function setModified()
    {
        $this->last_changes = new Expression('NOW()');
        $this->save();
    }

    /**
     * Update last_auth field to indicate screen last connexion.
     */
    public function setAuthenticated()
    {
        $this->last_auth = new Expression('NOW()');
        $this->save();
    }

    public function getLastId()
    {
        return $this->getDb()->getLastInsertID();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(ScreenTemplate::className(), ['id' => 'template_id']);
    }

    /**
     * Update template_id field.
     *
     * @param string $templateId template ID
     */
    public function setTemplate($templateId)
    {
        $this->template_id = $templateId;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreenHasFlows()
    {
        return $this->hasMany(ScreenHasFlow::className(), ['screen_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlows()
    {
        return $this->hasMany(Flow::className(), ['id' => 'flow_id'])->viaTable('screen_has_flow', ['screen_id' => 'id']);
    }
}
