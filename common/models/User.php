<?php

namespace common\models;

use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use Yii;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 * @property string $first_name
 * @property string $last_name
 * @property string $bio
 * @property string $website
 * @property string $phone
 * @property string $gender
 * @property string $profile_picture
 * @property string $cover_photo
 * @property boolean $is_private
 * @property boolean $is_verified
 * @property integer $followers_count
 * @property integer $following_count
 * @property integer $posts_count
 * @property integer $last_login_at
 * @property string $timezone
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
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
            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            [['first_name', 'last_name'], 'string', 'max' => 50],
            [['bio'], 'string'],
            [['website'], 'url'],
            [['phone'], 'string', 'max' => 20],
            [['gender'], 'in', 'range' => ['male', 'female', 'other']],
            [['profile_picture', 'cover_photo'], 'string', 'max' => 255],
            [['is_private', 'is_verified'], 'boolean'],
            [['followers_count', 'following_count', 'posts_count'], 'integer', 'min' => 0],
            [['last_login_at'], 'integer'],
            [['timezone'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token)
    {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Get user's full name
     * @return string
     */
    public function getFullName()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get display name (full name or username)
     * @return string
     */
    public function getDisplayName()
    {
        $fullName = $this->getFullName();
        return !empty($fullName) ? $fullName : $this->username;
    }

    /**
     * Get user's initials for avatar
     * @return string
     */
    public function getInitials()
    {
        $initials = '';
        if (!empty($this->first_name)) {
            $initials .= strtoupper(substr($this->first_name, 0, 1));
        }
        if (!empty($this->last_name)) {
            $initials .= strtoupper(substr($this->last_name, 0, 1));
        }
        return !empty($initials) ? $initials : strtoupper(substr($this->username, 0, 1));
    }

    /**
     * Check if user has profile picture
     * @return bool
     */
    public function hasProfilePicture()
    {
        return !empty($this->profile_picture);
    }

    /**
     * Get profile picture URL or default
     * @return string
     */
    public function getProfilePictureUrl()
    {
        return $this->hasProfilePicture() ? $this->profile_picture : '/images/default-avatar.png';
    }

    /**
     * Check if user has cover photo
     * @return bool
     */
    public function hasCoverPhoto()
    {
        return !empty($this->cover_photo);
    }

    /**
     * Get cover photo URL or default
     * @return string
     */
    public function getCoverPhotoUrl()
    {
        return $this->hasCoverPhoto() ? $this->cover_photo : '/images/default-cover.jpg';
    }

    /**
     * Check if user is following another user
     * @param int $userId
     * @return bool
     */
    public function isFollowing($userId)
    {
        return Follow::isFollowing($this->id, $userId);
    }

    /**
     * Check if user is followed by another user
     * @param int $userId
     * @return bool
     */
    public function isFollowedBy($userId)
    {
        return Follow::isFollowing($userId, $this->id);
    }

    /**
     * Follow another user
     * @param int $userId
     * @return bool
     */
    public function follow($userId)
    {
        if ($this->id === $userId) {
            return false; // Cannot follow yourself
        }

        return Follow::createFollow($this->id, $userId) !== null;
    }

    /**
     * Unfollow another user
     * @param int $userId
     * @return bool
     */
    public function unfollow($userId)
    {
        return Follow::removeFollow($this->id, $userId);
    }

    /**
     * Get users that this user follows
     * @return \yii\db\ActiveQuery
     */
    public function getFollowing()
    {
        return $this->hasMany(User::class, ['id' => 'following_id'])
            ->viaTable('{{%follows}}', ['follower_id' => 'id'], function($query) {
                $query->andWhere(['status' => Follow::STATUS_ACTIVE]);
            });
    }

    /**
     * Get users that follow this user
     * @return \yii\db\ActiveQuery
     */
    public function getFollowers()
    {
        return $this->hasMany(User::class, ['id' => 'follower_id'])
            ->viaTable('{{%follows}}', ['following_id' => 'id'], function($query) {
                $query->andWhere(['status' => Follow::STATUS_ACTIVE]);
            });
    }

    /**
     * Get mutual follows (users who follow each other)
     * @return \yii\db\ActiveQuery
     */
    public function getMutualFollows()
    {
        return $this->hasMany(User::class, ['id' => 'following_id'])
            ->viaTable('{{%follows}}', ['follower_id' => 'id'], function($query) {
                $query->andWhere(['status' => Follow::STATUS_ACTIVE]);
            })
            ->andWhere(['in', 'id', 
                Follow::find()->select('following_id')->where(['follower_id' => $this->id])
            ]);
    }

    /**
     * Get suggested users to follow
     * @param int $limit
     * @return User[]
     */
    public function getSuggestedFollows($limit = 10)
    {
        $followedUserIds = Follow::find()
            ->select('following_id')
            ->where(['follower_id' => $this->id, 'status' => Follow::STATUS_ACTIVE])
            ->column();

        if (empty($followedUserIds)) {
            return [];
        }

        $suggestedUserIds = Follow::find()
            ->select('following_id')
            ->where(['in', 'follower_id', $followedUserIds])
            ->andWhere(['not in', 'following_id', array_merge($followedUserIds, [$this->id])])
            ->andWhere(['status' => Follow::STATUS_ACTIVE])
            ->groupBy('following_id')
            ->limit($limit)
            ->column();

        return User::find()->where(['id' => $suggestedUserIds])->all();
    }

    /**
     * Update last login timestamp
     * @return bool
     */
    public function updateLastLogin()
    {
        $this->last_login_at = time();
        return $this->save(false);
    }

    /**
     * Increment followers count
     * @return bool
     */
    public function incrementFollowersCount()
    {
        $this->followers_count++;
        return $this->save(false);
    }

    /**
     * Decrement followers count
     * @return bool
     */
    public function decrementFollowersCount()
    {
        if ($this->followers_count > 0) {
            $this->followers_count--;
            return $this->save(false);
        }
        return true;
    }

    /**
     * Increment following count
     * @return bool
     */
    public function incrementFollowingCount()
    {
        $this->following_count++;
        return $this->save(false);
    }

    /**
     * Decrement following count
     * @return bool
     */
    public function decrementFollowingCount()
    {
        if ($this->following_count > 0) {
            $this->following_count--;
            return $this->save(false);
        }
        return true;
    }

    /**
     * Increment posts count
     * @return bool
     */
    public function incrementPostsCount()
    {
        $this->posts_count++;
        return $this->save(false);
    }

    /**
     * Decrement posts count
     * @return bool
     */
    public function decrementPostsCount()
    {
        if ($this->posts_count > 0) {
            $this->posts_count--;
            return $this->save(false);
        }
        return true;
    }

    /**
     * Get formatted followers count
     * @return string
     */
    public function getFormattedFollowersCount()
    {
        return $this->formatCount($this->followers_count);
    }

    /**
     * Get formatted following count
     * @return string
     */
    public function getFormattedFollowingCount()
    {
        return $this->formatCount($this->following_count);
    }

    /**
     * Get formatted posts count
     * @return string
     */
    public function getFormattedPostsCount()
    {
        return $this->formatCount($this->posts_count);
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
     * Check if user can view another user's profile
     * @param User $user
     * @return bool
     */
    public function canViewProfile($user)
    {
        // Public profiles can be viewed by anyone
        if (!$user->is_private) {
            return true;
        }

        // Private profiles can only be viewed by followers or the user themselves
        return $this->id === $user->id || $user->isFollowedBy($this->id);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[PublicPosts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPublicPosts()
    {
        return $this->hasMany(Post::class, ['user_id' => 'id'])->where(['is_public' => true]);
    }

    /**
     * Get user's latest posts
     * @param int $limit
     * @return Post[]
     */
    public function getLatestPosts($limit = 10)
    {
        return $this->getPosts()->orderByNewest()->limit($limit)->all();
    }

    /**
     * Get user's most liked posts
     * @param int $limit
     * @return Post[]
     */
    public function getMostLikedPosts($limit = 10)
    {
        return $this->getPosts()->orderByLikes()->limit($limit)->all();
    }

    /**
     * Get posts liked by this user
     * @return \yii\db\ActiveQuery
     */
    public function getLikedPosts()
    {
        return $this->hasMany(Post::class, ['id' => 'post_id'])
            ->viaTable('{{%likes}}', ['user_id' => 'id']);
    }

    /**
     * Get user's liked posts
     * @param int $limit
     * @return Post[]
     */
    public function getLikedPostsList($limit = 10)
    {
        return Like::getLikedPosts($this->id, $limit);
    }

    /**
     * Like a post
     * @param Post $post
     * @return bool
     */
    public function likePost($post)
    {
        return $post->like($this);
    }

    /**
     * Unlike a post
     * @param Post $post
     * @return bool
     */
    public function unlikePost($post)
    {
        return $post->unlike($this);
    }

    /**
     * Toggle like on a post
     * @param Post $post
     * @return bool true if liked, false if unliked
     */
    public function toggleLikePost($post)
    {
        return $post->toggleLike($this);
    }

    /**
     * Check if user liked a post
     * @param Post $post
     * @return bool
     */
    public function hasLikedPost($post)
    {
        return $post->isLikedBy($this);
    }
}
