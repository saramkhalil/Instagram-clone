<?php

namespace common\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[User]].
 *
 * @see User
 */
class UserQuery extends ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return User[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return User|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Find active users only
     * @return UserQuery
     */
    public function active()
    {
        return $this->andWhere(['status' => User::STATUS_ACTIVE]);
    }

    /**
     * Find verified users only
     * @return UserQuery
     */
    public function verified()
    {
        return $this->andWhere(['is_verified' => true]);
    }

    /**
     * Find public users only
     * @return UserQuery
     */
    public function public()
    {
        return $this->andWhere(['is_private' => false]);
    }

    /**
     * Find users by username pattern
     * @param string $username
     * @return UserQuery
     */
    public function byUsername($username)
    {
        return $this->andWhere(['like', 'username', $username]);
    }

    /**
     * Find users by name pattern (first_name or last_name)
     * @param string $name
     * @return UserQuery
     */
    public function byName($name)
    {
        return $this->andWhere([
            'or',
            ['like', 'first_name', $name],
            ['like', 'last_name', $name]
        ]);
    }

    /**
     * Order by followers count (descending)
     * @return UserQuery
     */
    public function orderByFollowers()
    {
        return $this->orderBy(['followers_count' => SORT_DESC]);
    }

    /**
     * Order by posts count (descending)
     * @return UserQuery
     */
    public function orderByPosts()
    {
        return $this->orderBy(['posts_count' => SORT_DESC]);
    }

    /**
     * Order by creation date (newest first)
     * @return UserQuery
     */
    public function orderByNewest()
    {
        return $this->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Find users with profile pictures
     * @return UserQuery
     */
    public function withProfilePicture()
    {
        return $this->andWhere(['not', ['profile_picture' => null]])
                    ->andWhere(['!=', 'profile_picture', '']);
    }

    /**
     * Find users by gender
     * @param string $gender
     * @return UserQuery
     */
    public function byGender($gender)
    {
        return $this->andWhere(['gender' => $gender]);
    }

    /**
     * Find users created in date range
     * @param int $startDate
     * @param int $endDate
     * @return UserQuery
     */
    public function createdBetween($startDate, $endDate)
    {
        return $this->andWhere(['between', 'created_at', $startDate, $endDate]);
    }
}
