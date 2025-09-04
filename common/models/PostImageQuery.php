<?php

namespace common\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[PostImage]].
 *
 * @see PostImage
 */
class PostImageQuery extends ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return PostImage[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return PostImage|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Find images by post
     * @param int $postId
     * @return PostImageQuery
     */
    public function byPost($postId)
    {
        return $this->andWhere(['post_id' => $postId]);
    }

    /**
     * Find images by multiple posts
     * @param array $postIds
     * @return PostImageQuery
     */
    public function byPosts($postIds)
    {
        return $this->andWhere(['post_id' => $postIds]);
    }

    /**
     * Find primary images only
     * @return PostImageQuery
     */
    public function primary()
    {
        return $this->andWhere(['is_primary' => true]);
    }

    /**
     * Find images with thumbnails
     * @return PostImageQuery
     */
    public function withThumbnails()
    {
        return $this->andWhere(['not', ['thumbnail_url' => null]])
                    ->andWhere(['!=', 'thumbnail_url', '']);
    }

    /**
     * Find images with dimensions
     * @return PostImageQuery
     */
    public function withDimensions()
    {
        return $this->andWhere(['not', ['width' => null]])
                    ->andWhere(['not', ['height' => null]]);
    }

    /**
     * Find landscape images
     * @return PostImageQuery
     */
    public function landscape()
    {
        return $this->withDimensions()
                    ->andWhere(['>', 'width', 'height']);
    }

    /**
     * Find portrait images
     * @return PostImageQuery
     */
    public function portrait()
    {
        return $this->withDimensions()
                    ->andWhere(['<', 'width', 'height']);
    }

    /**
     * Find square images
     * @return PostImageQuery
     */
    public function square()
    {
        return $this->withDimensions()
                    ->andWhere(['width' => 'height']);
    }

    /**
     * Find images by MIME type
     * @param string $mimeType
     * @return PostImageQuery
     */
    public function byMimeType($mimeType)
    {
        return $this->andWhere(['mime_type' => $mimeType]);
    }

    /**
     * Find images by multiple MIME types
     * @param array $mimeTypes
     * @return PostImageQuery
     */
    public function byMimeTypes($mimeTypes)
    {
        return $this->andWhere(['mime_type' => $mimeTypes]);
    }

    /**
     * Find images with minimum width
     * @param int $minWidth
     * @return PostImageQuery
     */
    public function withMinWidth($minWidth)
    {
        return $this->andWhere(['>=', 'width', $minWidth]);
    }

    /**
     * Find images with minimum height
     * @param int $minHeight
     * @return PostImageQuery
     */
    public function withMinHeight($minHeight)
    {
        return $this->andWhere(['>=', 'height', $minHeight]);
    }

    /**
     * Find images with maximum file size
     * @param int $maxFileSize
     * @return PostImageQuery
     */
    public function withMaxFileSize($maxFileSize)
    {
        return $this->andWhere(['<=', 'file_size', $maxFileSize]);
    }

    /**
     * Find images created today
     * @return PostImageQuery
     */
    public function today()
    {
        $startOfDay = strtotime('today');
        $endOfDay = strtotime('tomorrow') - 1;
        return $this->andWhere(['between', 'created_at', $startOfDay, $endOfDay]);
    }

    /**
     * Find images created this week
     * @return PostImageQuery
     */
    public function thisWeek()
    {
        $startOfWeek = strtotime('monday this week');
        $endOfWeek = strtotime('sunday this week') + 86399;
        return $this->andWhere(['between', 'created_at', $startOfWeek, $endOfWeek]);
    }

    /**
     * Find images created this month
     * @return PostImageQuery
     */
    public function thisMonth()
    {
        $startOfMonth = strtotime('first day of this month');
        $endOfMonth = strtotime('last day of this month') + 86399;
        return $this->andWhere(['between', 'created_at', $startOfMonth, $endOfMonth]);
    }

    /**
     * Find images created in date range
     * @param int $startDate
     * @param int $endDate
     * @return PostImageQuery
     */
    public function createdBetween($startDate, $endDate)
    {
        return $this->andWhere(['between', 'created_at', $startDate, $endDate]);
    }

    /**
     * Order by sort order (ascending)
     * @return PostImageQuery
     */
    public function orderBySortOrder()
    {
        return $this->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * Order by creation date (newest first)
     * @return PostImageQuery
     */
    public function orderByNewest()
    {
        return $this->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Order by creation date (oldest first)
     * @return PostImageQuery
     */
    public function orderByOldest()
    {
        return $this->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Order by file size (largest first)
     * @return PostImageQuery
     */
    public function orderByFileSize()
    {
        return $this->orderBy(['file_size' => SORT_DESC]);
    }

    /**
     * Order by dimensions (largest first)
     * @return PostImageQuery
     */
    public function orderByDimensions()
    {
        return $this->orderBy(['width' => SORT_DESC, 'height' => SORT_DESC]);
    }

    /**
     * Get images for a specific post ordered by sort order
     * @param int $postId
     * @return PostImageQuery
     */
    public function forPost($postId)
    {
        return $this->byPost($postId)->orderBySortOrder();
    }

    /**
     * Get primary image for a specific post
     * @param int $postId
     * @return PostImage|null
     */
    public function primaryForPost($postId)
    {
        return $this->byPost($postId)->primary()->one();
    }

    /**
     * Get recent images
     * @param int $limit
     * @return PostImageQuery
     */
    public function recent($limit = 10)
    {
        return $this->orderByNewest()->limit($limit);
    }

    /**
     * Get images with post information
     * @return PostImageQuery
     */
    public function withPost()
    {
        return $this->with('post');
    }

    /**
     * Get images for public posts only
     * @return PostImageQuery
     */
    public function forPublicPosts()
    {
        return $this->innerJoinWith('post')
            ->where(['posts.is_public' => true]);
    }

    /**
     * Get images for posts by specific users
     * @param array $userIds
     * @return PostImageQuery
     */
    public function forPostsByUsers($userIds)
    {
        return $this->innerJoinWith('post')
            ->where(['posts.user_id' => $userIds]);
    }

    /**
     * Get images for posts created in date range
     * @param int $startDate
     * @param int $endDate
     * @return PostImageQuery
     */
    public function forPostsCreatedBetween($startDate, $endDate)
    {
        return $this->innerJoinWith('post')
            ->where(['between', 'posts.created_at', $startDate, $endDate]);
    }

    /**
     * Get image count for a post
     * @param int $postId
     * @return int
     */
    public function countForPost($postId)
    {
        return $this->andWhere(['post_id' => $postId])->count();
    }

    /**
     * Get total file size for a post
     * @param int $postId
     * @return int
     */
    public function totalFileSizeForPost($postId)
    {
        return $this->andWhere(['post_id' => $postId])->sum('file_size') ?: 0;
    }
}
