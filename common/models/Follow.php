<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "follows".
 *
 * @property int $id
 * @property int $follower_id
 * @property int $following_id
 * @property string $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $follower
 * @property User $following
 */
class Follow extends ActiveRecord
{
    const STATUS_ACTIVE = 'active';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_PENDING = 'pending';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%follows}}';
    }

    /**
     * {@inheritdoc}
     * @return FollowQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FollowQuery(get_called_class());
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
            [['follower_id', 'following_id'], 'required'],
            [['follower_id', 'following_id'], 'integer'],
            [['status'], 'string', 'max' => 20],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_BLOCKED, self::STATUS_PENDING]],
            [['follower_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['follower_id' => 'id']],
            [['following_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['following_id' => 'id']],
            [['follower_id', 'following_id'], 'unique', 'targetAttribute' => ['follower_id', 'following_id']],
            [['follower_id', 'following_id'], 'compare', 'compareAttribute' => 'following_id', 'operator' => '!=', 'message' => 'Users cannot follow themselves.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'follower_id' => 'Follower ID',
            'following_id' => 'Following ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Follower]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFollower()
    {
        return $this->hasOne(User::class, ['id' => 'follower_id']);
    }

    /**
     * Gets query for [[Following]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFollowing()
    {
        return $this->hasOne(User::class, ['id' => 'following_id']);
    }

    /**
     * Check if follow is active
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if follow is blocked
     * @return bool
     */
    public function isBlocked()
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    /**
     * Check if follow is pending
     * @return bool
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Activate the follow
     * @return bool
     */
    public function activate()
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save(false);
    }

    /**
     * Block the follow
     * @return bool
     */
    public function block()
    {
        $this->status = self::STATUS_BLOCKED;
        return $this->save(false);
    }

    /**
     * Set follow as pending
     * @return bool
     */
    public function setPending()
    {
        $this->status = self::STATUS_PENDING;
        return $this->save(false);
    }

    /**
     * Create a new follow relationship
     * @param int $followerId
     * @param int $followingId
     * @param string $status
     * @return Follow|null
     */
    public static function createFollow($followerId, $followingId, $status = self::STATUS_ACTIVE)
    {
        // Check if users cannot follow themselves
        if ($followerId === $followingId) {
            return null;
        }

        // Check if follow already exists
        $existingFollow = static::find()
            ->where(['follower_id' => $followerId, 'following_id' => $followingId])
            ->one();

        if ($existingFollow) {
            return $existingFollow;
        }

        $follow = new static();
        $follow->follower_id = $followerId;
        $follow->following_id = $followingId;
        $follow->status = $status;

        if ($follow->save()) {
            // Update user counts
            $follower = User::findOne($followerId);
            $following = User::findOne($followingId);
            
            if ($follower && $status === self::STATUS_ACTIVE) {
                $follower->incrementFollowingCount();
            }
            if ($following && $status === self::STATUS_ACTIVE) {
                $following->incrementFollowersCount();
            }

            return $follow;
        }

        return null;
    }

    /**
     * Remove a follow relationship
     * @param int $followerId
     * @param int $followingId
     * @return bool
     */
    public static function removeFollow($followerId, $followingId)
    {
        $follow = static::find()
            ->where(['follower_id' => $followerId, 'following_id' => $followingId])
            ->one();

        if ($follow) {
            $wasActive = $follow->isActive();
            
            if ($follow->delete()) {
                // Update user counts if the follow was active
                if ($wasActive) {
                    $follower = User::findOne($followerId);
                    $following = User::findOne($followingId);
                    
                    if ($follower) {
                        $follower->decrementFollowingCount();
                    }
                    if ($following) {
                        $following->decrementFollowersCount();
                    }
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user A follows user B
     * @param int $followerId
     * @param int $followingId
     * @return bool
     */
    public static function isFollowing($followerId, $followingId)
    {
        return static::find()
            ->where(['follower_id' => $followerId, 'following_id' => $followingId])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->exists();
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
