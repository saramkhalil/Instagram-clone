<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Profile update form
 */
class ProfileUpdateForm extends Model
{
    public $first_name;
    public $last_name;
    public $bio;
    public $website;
    public $phone;
    public $gender;
    public $is_private;
    public $timezone;

    private $_user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name'], 'string', 'max' => 50],
            [['bio'], 'string', 'max' => 500],
            [['website'], 'url'],
            [['phone'], 'string', 'max' => 20],
            [['gender'], 'in', 'range' => ['male', 'female', 'other']],
            [['is_private'], 'boolean'],
            [['timezone'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'bio' => 'Bio',
            'website' => 'Website',
            'phone' => 'Phone Number',
            'gender' => 'Gender',
            'is_private' => 'Private Account',
            'timezone' => 'Timezone',
        ];
    }

    /**
     * Constructor
     * @param User $user
     */
    public function __construct($user)
    {
        $this->_user = $user;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->bio = $user->bio;
        $this->website = $user->website;
        $this->phone = $user->phone;
        $this->gender = $user->gender;
        $this->is_private = $user->is_private;
        $this->timezone = $user->timezone;
    }

    /**
     * Update user profile
     * @return bool
     */
    public function updateProfile()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->_user->first_name = $this->first_name;
        $this->_user->last_name = $this->last_name;
        $this->_user->bio = $this->bio;
        $this->_user->website = $this->website;
        $this->_user->phone = $this->phone;
        $this->_user->gender = $this->gender;
        $this->_user->is_private = $this->is_private;
        $this->_user->timezone = $this->timezone;

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
