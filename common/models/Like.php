<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "likes".
 *
 * @property int $id
 * @property int $user_id
 * @property int $post_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 * @property Post $post
 */
class Like extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%likes}}';
    }

    /**
     * {@inheritdoc}
     * @return LikeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LikeQuery(get_called_class());
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
            [['user_id', 'post_id'], 'required'],
            [['user_id', 'post_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::class, 'targetAttribute' => ['post_id' => 'id']],
            [['user_id', 'post_id'], 'unique', 'targetAttribute' => ['user_id', 'post_id']],
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
            'post_id' => 'Post ID',
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
     * Gets query for [[Post]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    /**
     * Create a new like
     * @param int $userId
     * @param int $postId
     * @return Like|null
     */
    public static function createLike($userId, $postId)
    {
        // Check if like already exists
        $existingLike = static::find()
            ->where(['user_id' => $userId, 'post_id' => $postId])
            ->one();

        if ($existingLike) {
            return $existingLike;
        }

        $like = new static();
        $like->user_id = $userId;
        $like->post_id = $postId;

        if ($like->save()) {
            // Update post likes count
            $post = Post::findOne($postId);
            if ($post) {
                $post->incrementLikesCount();
            }
            return $like;
        }

        return null;
    }

    /**
     * Remove a like
     * @param int $userId
     * @param int $postId
     * @return bool
     */
    public static function removeLike($userId, $postId)
    {
        $like = static::find()
            ->where(['user_id' => $userId, 'post_id' => $postId])
            ->one();

        if ($like) {
            if ($like->delete()) {
                // Update post likes count
                $post = Post::findOne($postId);
                if ($post) {
                    $post->decrementLikesCount();
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user liked a post
     * @param int $userId
     * @param int $postId
     * @return bool
     */
    public static function isLiked($userId, $postId)
    {
        return static::find()
            ->where(['user_id' => $userId, 'post_id' => $postId])
            ->exists();
    }

    /**
     * Toggle like on a post
     * @param int $userId
     * @param int $postId
     * @return bool true if liked, false if unliked
     */
    public static function toggleLike($userId, $postId)
    {
        if (static::isLiked($userId, $postId)) {
            return !static::removeLike($userId, $postId); // Return false if unliked
        } else {
            return static::createLike($userId, $postId) !== null; // Return true if liked
        }
    }

    /**
     * Get posts liked by a user
     * @param int $userId
     * @param int $limit
     * @return Post[]
     */
    public static function getLikedPosts($userId, $limit = 10)
    {
        $postIds = static::find()
            ->select('post_id')
            ->where(['user_id' => $userId])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->column();

        return Post::find()->where(['id' => $postIds])->orderBy(['created_at' => SORT_DESC])->all();
    }

    /**
     * Get users who liked a post
     * @param int $postId
     * @param int $limit
     * @return User[]
     */
    public static function getLikedByUsers($postId, $limit = 10)
    {
        $userIds = static::find()
            ->select('user_id')
            ->where(['post_id' => $postId])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->column();

        return User::find()->where(['id' => $userIds])->all();
    }

    /**
     * Get like count for a post
     * @param int $postId
     * @return int
     */
    public static function getLikeCount($postId)
    {
        return static::find()->where(['post_id' => $postId])->count();
    }

    /**
     * Get like count for a user (total likes on their posts)
     * @param int $userId
     * @return int
     */
    public static function getUserLikeCount($userId)
    {
        return static::find()
            ->innerJoinWith('post')
            ->where(['posts.user_id' => $userId])
            ->count();
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
