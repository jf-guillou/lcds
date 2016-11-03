<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "device".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $last_auth
 * @property bool $enabled
 * @property DeviceHasScreen[] $deviceHasScreens
 * @property Screen[] $screens
 */
class Device extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'device';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['last_auth'], 'safe'],
            [['enabled'], 'boolean'],
            [['name'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 1024],
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
            'last_auth' => Yii::t('app', 'Last connection'),
            'enabled' => Yii::t('app', 'Enabled'),
        ];
    }

    /**
     * Update last_auth field to indicate screen last connexion.
     */
    public function setAuthenticated()
    {
        $this->last_auth = new Expression('NOW()');
        $this->save();
    }

    /**
     * Get last inserted AUTO_INCREMENT id from database.
     *
     * @return int last insert id
     */
    public function getLastId()
    {
        return $this->getDb()->getLastInsertID();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeviceHasScreens()
    {
        return $this->hasMany(DeviceHasScreen::className(), ['device_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreens()
    {
        return $this->hasMany(Screen::className(), ['id' => 'screen_id'])->viaTable('device_has_screen', ['device_id' => 'id']);
    }

    /**
     * Get next screen for this device.
     *
     * @param int $currentScreenId
     *
     * @return \app\models\Screen|null next screen
     */
    public function getNextScreen($currentScreenId = null)
    {
        // No screen available, bail out
        if (count($this->screens) === 0) {
            return;
        }

        $firstScreen = $this->screens[0];
        if ($currentScreenId === null) {
            return $firstScreen;
        }

        $foundCurrent = false;
        foreach ($this->screens as $s) {
            if ($foundCurrent) {
                return $s;
            }

            if ($currentScreenId === $s->id) {
                $foundCurrent = true; // Pick next screen after current
            }
        }

        // Return first screen if reached end of array
        return $currentScreenId != $firstScreen->id ? $firstScreen : null;
    }
}
