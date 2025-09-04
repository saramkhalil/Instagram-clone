<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "comments".
 *
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string $content
 * @property bool $is_edited
 * @property int $likes_count
 * @property int $replies_count
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Post $post
 * @property User $user
 * @property Comment $parent
 * @property Comment[] $replies
 */
class Comment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%comments}}';
    }

    /**
     * {@inheritdoc}
     * @return CommentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CommentQuery(get_called_class());
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
            [['post_id', 'user_id', 'content'], 'required'],
            [['post_id', 'user_id', 'parent_id', 'likes_count', 'replies_count'], 'integer', 'min' => 0],
            [['content'], 'string', 'min' => 1, 'max' => 2000],
            [['is_edited'], 'boolean'],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::class, 'targetAttribute' => ['post_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Comment::class, 'targetAttribute' => ['parent_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_id' => 'Post ID',
            'user_id' => 'User ID',
            'parent_id' => 'Parent ID',
            'content' => 'Content',
            'is_edited' => 'Is Edited',
            'likes_count' => 'Likes Count',
            'replies_count' => 'Replies Count',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Parent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Comment::class, ['id' => 'parent_id']);
    }

    /**
     * Gets query for [[Replies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReplies()
    {
        return $this->hasMany(Comment::class, ['parent_id' => 'id']);
    }

    /**
     * Check if this is a reply to another comment
     * @return bool
     */
    public function isReply()
    {
        return $this->parent_id !== null;
    }

    /**
     * Check if this comment has replies
     * @return bool
     */
    public function hasReplies()
    {
        return $this->replies_count > 0;
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
     * Get formatted replies count
     * @return string
     */
    public function getFormattedRepliesCount()
    {
        return $this->formatCount($this->replies_count);
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
     * Increment replies count
     * @return bool
     */
    public function incrementRepliesCount()
    {
        $this->replies_count++;
        return $this->save(false);
    }

    /**
     * Decrement replies count
     * @return bool
     */
    public function decrementRepliesCount()
    {
        if ($this->replies_count > 0) {
            $this->replies_count--;
            return $this->save(false);
        }
        return true;
    }

    /**
     * Mark comment as edited
     * @return bool
     */
    public function markAsEdited()
    {
        $this->is_edited = true;
        return $this->save(false);
    }

    /**
     * Create a new comment
     * @param int $postId
     * @param int $userId
     * @param string $content
     * @param int|null $parentId
     * @return Comment|null
     */
    public static function createComment($postId, $userId, $content, $parentId = null)
    {
        $comment = new static();
        $comment->post_id = $postId;
        $comment->user_id = $userId;
        $comment->content = $content;
        $comment->parent_id = $parentId;

        if ($comment->save()) {
            // Update post comments count
            $post = Post::findOne($postId);
            if ($post) {
                $post->incrementCommentsCount();
            }

            // Update parent comment replies count if this is a reply
            if ($parentId) {
                $parentComment = static::findOne($parentId);
                if ($parentComment) {
                    $parentComment->incrementRepliesCount();
                }
            }

            return $comment;
        }

        return null;
    }

    /**
     * Delete comment and update counts
     * @return bool
     */
    public function delete()
    {
        $postId = $this->post_id;
        $parentId = $this->parent_id;

        if (parent::delete()) {
            // Update post comments count
            $post = Post::findOne($postId);
            if ($post) {
                $post->decrementCommentsCount();
            }

            // Update parent comment replies count if this was a reply
            if ($parentId) {
                $parentComment = static::findOne($parentId);
                if ($parentComment) {
                    $parentComment->decrementRepliesCount();
                }
            }

            return true;
        }

        return false;
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
