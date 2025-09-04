<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Password change form
 */
class PasswordChangeForm extends Model
{
    public $current_password;
    public $new_password;
    public $confirm_password;

    private $_user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['current_password', 'new_password', 'confirm_password'], 'required'],
            [['current_password'], 'validateCurrentPassword'],
            [['new_password'], 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
            [['confirm_password'], 'compare', 'compareAttribute' => 'new_password', 'message' => 'Passwords do not match.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'current_password' => 'Current Password',
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm New Password',
        ];
    }

    /**
     * Constructor
     * @param User $user
     */
    public function __construct($user)
    {
        $this->_user = $user;
    }

    /**
     * Validate current password
     * @param string $attribute
     * @param array $params
     */
    public function validateCurrentPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->_user->validatePassword($this->current_password)) {
                $this->addError($attribute, 'Current password is incorrect.');
            }
        }
    }

    /**
     * Change password
     * @return bool
     */
    public function changePassword()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->_user->setPassword($this->new_password);
        return $this->_user->save();
    }

    /**
     * Get user
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }
}
