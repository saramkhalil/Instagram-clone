<?php

namespace common\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Follow]].
 *
 * @see Follow
 */
class FollowQuery extends ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return Follow[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Follow|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Find active follows only
     * @return FollowQuery
     */
    public function active()
    {
        return $this->andWhere(['status' => Follow::STATUS_ACTIVE]);
    }

    /**
     * Find blocked follows only
     * @return FollowQuery
     */
    public function blocked()
    {
        return $this->andWhere(['status' => Follow::STATUS_BLOCKED]);
    }

    /**
     * Find pending follows only
     * @return FollowQuery
     */
    public function pending()
    {
        return $this->andWhere(['status' => Follow::STATUS_PENDING]);
    }

    /**
     * Find follows by follower
     * @param int $followerId
     * @return FollowQuery
     */
    public function byFollower($followerId)
    {
        return $this->andWhere(['follower_id' => $followerId]);
    }

    /**
     * Find follows by following user
     * @param int $followingId
     * @return FollowQuery
     */
    public function byFollowing($followingId)
    {
        return $this->andWhere(['following_id' => $followingId]);
    }

    /**
     * Find mutual follows (users who follow each other)
     * @param int $userId1
     * @param int $userId2
     * @return FollowQuery
     */
    public function mutual($userId1, $userId2)
    {
        return $this->andWhere([
            'or',
            ['follower_id' => $userId1, 'following_id' => $userId2],
            ['follower_id' => $userId2, 'following_id' => $userId1]
        ]);
    }

    /**
     * Find follows created today
     * @return FollowQuery
     */
    public function today()
    {
        $startOfDay = strtotime('today');
        $endOfDay = strtotime('tomorrow') - 1;
        return $this->andWhere(['between', 'created_at', $startOfDay, $endOfDay]);
    }

    /**
     * Find follows created this week
     * @return FollowQuery
     */
    public function thisWeek()
    {
        $startOfWeek = strtotime('monday this week');
        $endOfWeek = strtotime('sunday this week') + 86399;
        return $this->andWhere(['between', 'created_at', $startOfWeek, $endOfWeek]);
    }

    /**
     * Find follows created this month
     * @return FollowQuery
     */
    public function thisMonth()
    {
        $startOfMonth = strtotime('first day of this month');
        $endOfMonth = strtotime('last day of this month') + 86399;
        return $this->andWhere(['between', 'created_at', $startOfMonth, $endOfMonth]);
    }

    /**
     * Find follows created in date range
     * @param int $startDate
     * @param int $endDate
     * @return FollowQuery
     */
    public function createdBetween($startDate, $endDate)
    {
        return $this->andWhere(['between', 'created_at', $startDate, $endDate]);
    }

    /**
     * Order by creation date (newest first)
     * @return FollowQuery
     */
    public function orderByNewest()
    {
        return $this->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Order by creation date (oldest first)
     * @return FollowQuery
     */
    public function orderByOldest()
    {
        return $this->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Get followers of a user
     * @param int $userId
     * @return FollowQuery
     */
    public function followersOf($userId)
    {
        return $this->byFollowing($userId)->active();
    }

    /**
     * Get users that a user follows
     * @param int $userId
     * @return FollowQuery
     */
    public function followingOf($userId)
    {
        return $this->byFollower($userId)->active();
    }

    /**
     * Get mutual follows between two users
     * @param int $userId1
     * @param int $userId2
     * @return FollowQuery
     */
    public function mutualFollows($userId1, $userId2)
    {
        return $this->mutual($userId1, $userId2)->active();
    }

    /**
     * Get recent followers
     * @param int $userId
     * @param int $limit
     * @return FollowQuery
     */
    public function recentFollowers($userId, $limit = 10)
    {
        return $this->followersOf($userId)->orderByNewest()->limit($limit);
    }

    /**
     * Get recent following
     * @param int $userId
     * @param int $limit
     * @return FollowQuery
     */
    public function recentFollowing($userId, $limit = 10)
    {
        return $this->followingOf($userId)->orderByNewest()->limit($limit);
    }

    /**
     * Check if user A follows user B
     * @param int $followerId
     * @param int $followingId
     * @return bool
     */
    public function isFollowing($followerId, $followingId)
    {
        return $this->andWhere([
            'follower_id' => $followerId,
            'following_id' => $followingId,
            'status' => Follow::STATUS_ACTIVE
        ])->exists();
    }

    /**
     * Get follow relationship between two users
     * @param int $followerId
     * @param int $followingId
     * @return Follow|null
     */
    public function getFollow($followerId, $followingId)
    {
        return $this->andWhere([
            'follower_id' => $followerId,
            'following_id' => $followingId
        ])->one();
    }

    /**
     * Get users that user A follows but user B doesn't follow
     * @param int $userIdA
     * @param int $userIdB
     * @return FollowQuery
     */
    public function followingNotMutual($userIdA, $userIdB)
    {
        return $this->andWhere([
            'and',
            ['follower_id' => $userIdA],
            ['not in', 'following_id', 
                Follow::find()->select('following_id')->where(['follower_id' => $userIdB])
            ]
        ])->active();
    }

    /**
     * Get suggested users to follow (users followed by user's followers)
     * @param int $userId
     * @param int $limit
     * @return FollowQuery
     */
    public function suggestedFollows($userId, $limit = 10)
    {
        return $this->andWhere([
            'and',
            ['in', 'follower_id', 
                Follow::find()->select('follower_id')->where(['following_id' => $userId])
            ],
            ['not in', 'following_id', 
                Follow::find()->select('following_id')->where(['follower_id' => $userId])
            ],
            ['!=', 'following_id', $userId]
        ])->active()->groupBy('following_id')->limit($limit);
    }
}
