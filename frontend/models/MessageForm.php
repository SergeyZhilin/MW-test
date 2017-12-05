<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * This is the model class for table "message".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $message
 */
class MessageForm extends Model
{
    public $user_id;
    public $message;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'message'], 'required'],
            [['user_id'], 'integer'],
            [['message'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'message' => 'Message',
        ];
    }
}
