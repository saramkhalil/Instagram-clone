<?php

namespace common\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Like]].
 *
 * @see Like
 */
class LikeQuery extends ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return Like[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Like|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Find likes by user
     * @param int $userId
     * @return LikeQuery
     */
    public function byUser($userId)
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    /**
     * Find likes by post
     * @param int $postId
     * @return LikeQuery
     */
    public function byPost($postId)
    {
        return $this->andWhere(['post_id' => $postId]);
    }

    /**
     * Find likes by multiple users
     * @param array $userIds
     * @return LikeQuery
     */
    public function byUsers($userIds)
    {
        return $this->andWhere(['user_id' => $userIds]);
    }

    /**
     * Find likes by multiple posts
     * @param array $postIds
     * @return LikeQuery
     */
    public function byPosts($postIds)
    {
        return $this->andWhere(['post_id' => $postIds]);
    }

    /**
     * Find likes created today
     * @return LikeQuery
     */
    public function today()
    {
        $startOfDay = strtotime('today');
        $endOfDay = strtotime('tomorrow') - 1;
        return $this->andWhere(['between', 'created_at', $startOfDay, $endOfDay]);
    }

    /**
     * Find likes created this week
     * @return LikeQuery
     */
    public function thisWeek()
    {
        $startOfWeek = strtotime('monday this week');
        $endOfWeek = strtotime('sunday this week') + 86399;
        return $this->andWhere(['between', 'created_at', $startOfWeek, $endOfWeek]);
    }

    /**
     * Find likes created this month
     * @return LikeQuery
     */
    public function thisMonth()
    {
        $startOfMonth = strtotime('first day of this month');
        $endOfMonth = strtotime('last day of this month') + 86399;
        return $this->andWhere(['between', 'created_at', $startOfMonth, $endOfMonth]);
    }

    /**
     * Find likes created in date range
     * @param int $startDate
     * @param int $endDate
     * @return LikeQuery
     */
    public function createdBetween($startDate, $endDate)
    {
        return $this->andWhere(['between', 'created_at', $startDate, $endDate]);
    }

    /**
     * Order by creation date (newest first)
     * @return LikeQuery
     */
    public function orderByNewest()
    {
        return $this->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Order by creation date (oldest first)
     * @return LikeQuery
     */
    public function orderByOldest()
    {
        return $this->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Get likes for a specific post
     * @param int $postId
     * @return LikeQuery
     */
    public function forPost($postId)
    {
        return $this->byPost($postId)->orderByNewest();
    }

    /**
     * Get likes by a specific user
     * @param int $userId
     * @return LikeQuery
     */
    public function byUserLikes($userId)
    {
        return $this->byUser($userId)->orderByNewest();
    }

    /**
     * Get recent likes
     * @param int $limit
     * @return LikeQuery
     */
    public function recent($limit = 10)
    {
        return $this->orderByNewest()->limit($limit);
    }

    /**
     * Get most liked posts (by like count)
     * @param int $limit
     * @return LikeQuery
     */
    public function mostLikedPosts($limit = 10)
    {
        return $this->select(['post_id', 'COUNT(*) as like_count'])
            ->groupBy('post_id')
            ->orderBy(['like_count' => SORT_DESC])
            ->limit($limit);
    }

    /**
     * Get most active likers (users who like most)
     * @param int $limit
     * @return LikeQuery
     */
    public function mostActiveLikers($limit = 10)
    {
        return $this->select(['user_id', 'COUNT(*) as like_count'])
            ->groupBy('user_id')
            ->orderBy(['like_count' => SORT_DESC])
            ->limit($limit);
    }

    /**
     * Check if user liked a post
     * @param int $userId
     * @param int $postId
     * @return bool
     */
    public function isLiked($userId, $postId)
    {
        return $this->andWhere(['user_id' => $userId, 'post_id' => $postId])->exists();
    }

    /**
     * Get like relationship between user and post
     * @param int $userId
     * @param int $postId
     * @return Like|null
     */
    public function getLike($userId, $postId)
    {
        return $this->andWhere(['user_id' => $userId, 'post_id' => $postId])->one();
    }

    /**
     * Get likes with user information
     * @return LikeQuery
     */
    public function withUser()
    {
        return $this->with('user');
    }

    /**
     * Get likes with post information
     * @return LikeQuery
     */
    public function withPost()
    {
        return $this->with('post');
    }

    /**
     * Get likes with both user and post information
     * @return LikeQuery
     */
    public function withUserAndPost()
    {
        return $this->with(['user', 'post']);
    }

    /**
     * Get likes for posts by specific users
     * @param array $userIds
     * @return LikeQuery
     */
    public function forPostsByUsers($userIds)
    {
        return $this->innerJoinWith('post')
            ->where(['posts.user_id' => $userIds]);
    }

    /**
     * Get likes for public posts only
     * @return LikeQuery
     */
    public function forPublicPosts()
    {
        return $this->innerJoinWith('post')
            ->where(['posts.is_public' => true]);
    }

    /**
     * Get likes for posts created in date range
     * @param int $startDate
     * @param int $endDate
     * @return LikeQuery
     */
    public function forPostsCreatedBetween($startDate, $endDate)
    {
        return $this->innerJoinWith('post')
            ->where(['between', 'posts.created_at', $startDate, $endDate]);
    }
}
