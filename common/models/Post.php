<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "posts".
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $caption
 * @property string|null $location
 * @property bool $is_public
 * @property int $likes_count
 * @property int $comments_count
 * @property int $shares_count
 * @property int $views_count
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 * @property PostImage[] $postImages
 * @property Like[] $likes
 * @property Comment[] $comments
 */
class Post extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%posts}}';
    }

    /**
     * {@inheritdoc}
     * @return PostQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PostQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'likes_count', 'comments_count', 'shares_count', 'views_count'], 'integer', 'min' => 0],
            [['caption'], 'string'],
            [['location'], 'string', 'max' => 255],
            [['is_public'], 'boolean'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'caption' => 'Caption',
            'location' => 'Location',
            'is_public' => 'Is Public',
            'likes_count' => 'Likes Count',
            'comments_count' => 'Comments Count',
            'shares_count' => 'Shares Count',
            'views_count' => 'Views Count',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[PostImages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostImages()
    {
        return $this->hasMany(PostImage::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Likes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLikes()
    {
        return $this->hasMany(Like::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Comments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['post_id' => 'id']);
    }

    /**
     * Get the first image of the post
     * @return PostImage|null
     */
    public function getFirstImage()
    {
        return $this->getPostImages()->orderBy(['sort_order' => SORT_ASC])->one();
    }

    /**
     * Get all images of the post ordered by sort_order
     * @return PostImage[]
     */
    public function getOrderedImages()
    {
        return $this->getPostImages()->orderBy(['sort_order' => SORT_ASC])->all();
    }

    /**
     * Check if post has images
     * @return bool
     */
    public function hasImages()
    {
        return $this->getPostImages()->exists();
    }

    /**
     * Get formatted likes count
     * @return string
     */
    public function getFormattedLikesCount()
    {
        return $this->formatCount($this->likes_count);
    }

    /**
     * Get formatted comments count
     * @return string
     */
    public function getFormattedCommentsCount()
    {
        return $this->formatCount($this->comments_count);
    }

    /**
     * Get formatted shares count
     * @return string
     */
    public function getFormattedSharesCount()
    {
        return $this->formatCount($this->shares_count);
    }

    /**
     * Get formatted views count
     * @return string
     */
    public function getFormattedViewsCount()
    {
        return $this->formatCount($this->views_count);
    }

    /**
     * Format count for display (e.g., 1.2K, 1.5M)
     * @param int $count
     * @return string
     */
    private function formatCount($count)
    {
        if ($count >= 1000000) {
            return round($count / 1000000, 1) . 'M';
        } elseif ($count >= 1000) {
            return round($count / 1000, 1) . 'K';
        }
        return (string) $count;
    }

    /**
     * Increment likes count
     * @return bool
     */
    public function incrementLikesCount()
    {
        $this->likes_count++;
        return $this->save(false);
    }

    /**
     * Decrement likes count
     * @return bool
     */
    public function decrementLikesCount()
    {
        if ($this->likes_count > 0) {
            $this->likes_count--;
            return $this->save(false);
        }
        return true;
    }

    /**
     * Increment comments count
     * @return bool
     */
    public function incrementCommentsCount()
    {
        $this->comments_count++;
        return $this->save(false);
    }

    /**
     * Decrement comments count
     * @return bool
     */
    public function decrementCommentsCount()
    {
        if ($this->comments_count > 0) {
            $this->comments_count--;
            return $this->save(false);
        }
        return true;
    }

    /**
     * Increment shares count
     * @return bool
     */
    public function incrementSharesCount()
    {
        $this->shares_count++;
        return $this->save(false);
    }

    /**
     * Increment views count
     * @return bool
     */
    public function incrementViewsCount()
    {
        $this->views_count++;
        return $this->save(false);
    }

    /**
     * Check if user can view this post
     * @param User $user
     * @return bool
     */
    public function canView($user)
    {
        // Public posts can be viewed by anyone
        if ($this->is_public) {
            return true;
        }

        // Private posts can only be viewed by the author or followers
        if ($this->user_id === $user->id) {
            return true;
        }

        // Check if user follows the post author
        return $this->user->isFollowedBy($user->id);
    }

    /**
     * Like this post
     * @param User $user
     * @return bool
     */
    public function like($user)
    {
        return Like::createLike($user->id, $this->id) !== null;
    }

    /**
     * Unlike this post
     * @param User $user
     * @return bool
     */
    public function unlike($user)
    {
        return Like::removeLike($user->id, $this->id);
    }

    /**
     * Toggle like on this post
     * @param User $user
     * @return bool true if liked, false if unliked
     */
    public function toggleLike($user)
    {
        return Like::toggleLike($user->id, $this->id);
    }

    /**
     * Check if user liked this post
     * @param User $user
     * @return bool
     */
    public function isLikedBy($user)
    {
        return Like::isLiked($user->id, $this->id);
    }

    /**
     * Get users who liked this post
     * @param int $limit
     * @return User[]
     */
    public function getLikedByUsers($limit = 10)
    {
        return Like::getLikedByUsers($this->id, $limit);
    }

    /**
     * Get like count for this post
     * @return int
     */
    public function getLikeCount()
    {
        return Like::getLikeCount($this->id);
    }

    /**
     * Get time ago string
     * @return string
     */
    public function getTimeAgo()
    {
        $time = time() - $this->created_at;
        
        if ($time < 60) {
            return 'just now';
        } elseif ($time < 3600) {
            $minutes = floor($time / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($time < 86400) {
            $hours = floor($time / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($time < 2592000) {
            $days = floor($time / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($time < 31536000) {
            $months = floor($time / 2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        } else {
            $years = floor($time / 31536000);
            return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
        }
    }
}
