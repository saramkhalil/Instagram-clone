<?php

namespace common\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Comment]].
 *
 * @see Comment
 */
class CommentQuery extends ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return Comment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Comment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Find comments by post
     * @param int $postId
     * @return CommentQuery
     */
    public function byPost($postId)
    {
        return $this->andWhere(['post_id' => $postId]);
    }

    /**
     * Find comments by user
     * @param int $userId
     * @return CommentQuery
     */
    public function byUser($userId)
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    /**
     * Find comments by multiple users
     * @param array $userIds
     * @return CommentQuery
     */
    public function byUsers($userIds)
    {
        return $this->andWhere(['user_id' => $userIds]);
    }

    /**
     * Find comments by multiple posts
     * @param array $postIds
     * @return CommentQuery
     */
    public function byPosts($postIds)
    {
        return $this->andWhere(['post_id' => $postIds]);
    }

    /**
     * Find top-level comments (not replies)
     * @return CommentQuery
     */
    public function topLevel()
    {
        return $this->andWhere(['parent_id' => null]);
    }

    /**
     * Find replies to a specific comment
     * @param int $parentId
     * @return CommentQuery
     */
    public function repliesTo($parentId)
    {
        return $this->andWhere(['parent_id' => $parentId]);
    }

    /**
     * Find edited comments
     * @return CommentQuery
     */
    public function edited()
    {
        return $this->andWhere(['is_edited' => true]);
    }

    /**
     * Find comments created today
     * @return CommentQuery
     */
    public function today()
    {
        $startOfDay = strtotime('today');
        $endOfDay = strtotime('tomorrow') - 1;
        return $this->andWhere(['between', 'created_at', $startOfDay, $endOfDay]);
    }

    /**
     * Find comments created this week
     * @return CommentQuery
     */
    public function thisWeek()
    {
        $startOfWeek = strtotime('monday this week');
        $endOfWeek = strtotime('sunday this week') + 86399;
        return $this->andWhere(['between', 'created_at', $startOfWeek, $endOfWeek]);
    }

    /**
     * Find comments created this month
     * @return CommentQuery
     */
    public function thisMonth()
    {
        $startOfMonth = strtotime('first day of this month');
        $endOfMonth = strtotime('last day of this month') + 86399;
        return $this->andWhere(['between', 'created_at', $startOfMonth, $endOfMonth]);
    }

    /**
     * Find comments created in date range
     * @param int $startDate
     * @param int $endDate
     * @return CommentQuery
     */
    public function createdBetween($startDate, $endDate)
    {
        return $this->andWhere(['between', 'created_at', $startDate, $endDate]);
    }

    /**
     * Order by creation date (newest first)
     * @return CommentQuery
     */
    public function orderByNewest()
    {
        return $this->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Order by creation date (oldest first)
     * @return CommentQuery
     */
    public function orderByOldest()
    {
        return $this->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Order by likes count (most liked first)
     * @return CommentQuery
     */
    public function orderByLikes()
    {
        return $this->orderBy(['likes_count' => SORT_DESC]);
    }

    /**
     * Order by replies count (most replied first)
     * @return CommentQuery
     */
    public function orderByReplies()
    {
        return $this->orderBy(['replies_count' => SORT_DESC]);
    }

    /**
     * Get comments for a specific post
     * @param int $postId
     * @return CommentQuery
     */
    public function forPost($postId)
    {
        return $this->byPost($postId)->topLevel()->orderByNewest();
    }

    /**
     * Get replies for a specific comment
     * @param int $commentId
     * @return CommentQuery
     */
    public function forComment($commentId)
    {
        return $this->repliesTo($commentId)->orderByOldest();
    }

    /**
     * Get comments by a specific user
     * @param int $userId
     * @return CommentQuery
     */
    public function byUserComments($userId)
    {
        return $this->byUser($userId)->orderByNewest();
    }

    /**
     * Get recent comments
     * @param int $limit
     * @return CommentQuery
     */
    public function recent($limit = 10)
    {
        return $this->orderByNewest()->limit($limit);
    }

    /**
     * Get most liked comments
     * @param int $limit
     * @return CommentQuery
     */
    public function mostLiked($limit = 10)
    {
        return $this->orderByLikes()->limit($limit);
    }

    /**
     * Get most replied comments
     * @param int $limit
     * @return CommentQuery
     */
    public function mostReplied($limit = 10)
    {
        return $this->orderByReplies()->limit($limit);
    }

    /**
     * Get comments with minimum likes
     * @param int $minLikes
     * @return CommentQuery
     */
    public function withMinLikes($minLikes)
    {
        return $this->andWhere(['>=', 'likes_count', $minLikes]);
    }

    /**
     * Get comments with minimum replies
     * @param int $minReplies
     * @return CommentQuery
     */
    public function withMinReplies($minReplies)
    {
        return $this->andWhere(['>=', 'replies_count', $minReplies]);
    }

    /**
     * Get comments with user information
     * @return CommentQuery
     */
    public function withUser()
    {
        return $this->with('user');
    }

    /**
     * Get comments with post information
     * @return CommentQuery
     */
    public function withPost()
    {
        return $this->with('post');
    }

    /**
     * Get comments with both user and post information
     * @return CommentQuery
     */
    public function withUserAndPost()
    {
        return $this->with(['user', 'post']);
    }

    /**
     * Get comments with replies
     * @return CommentQuery
     */
    public function withReplies()
    {
        return $this->with('replies');
    }

    /**
     * Get comments with parent
     * @return CommentQuery
     */
    public function withParent()
    {
        return $this->with('parent');
    }

    /**
     * Get comments for public posts only
     * @return CommentQuery
     */
    public function forPublicPosts()
    {
        return $this->innerJoinWith('post')
            ->where(['posts.is_public' => true]);
    }

    /**
     * Get comments for posts by specific users
     * @param array $userIds
     * @return CommentQuery
     */
    public function forPostsByUsers($userIds)
    {
        return $this->innerJoinWith('post')
            ->where(['posts.user_id' => $userIds]);
    }

    /**
     * Get comment count for a post
     * @param int $postId
     * @return int
     */
    public function countForPost($postId)
    {
        return $this->andWhere(['post_id' => $postId])->count();
    }

    /**
     * Get comment count for a user
     * @param int $userId
     * @return int
     */
    public function countForUser($userId)
    {
        return $this->andWhere(['user_id' => $userId])->count();
    }
}
