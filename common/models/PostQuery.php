<?php

namespace common\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Post]].
 *
 * @see Post
 */
class PostQuery extends ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return Post[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Post|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Find public posts only
     * @return PostQuery
     */
    public function public()
    {
        return $this->andWhere(['is_public' => true]);
    }

    /**
     * Find posts by user
     * @param int $userId
     * @return PostQuery
     */
    public function byUser($userId)
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    /**
     * Find posts by multiple users (for feed)
     * @param array $userIds
     * @return PostQuery
     */
    public function byUsers($userIds)
    {
        return $this->andWhere(['user_id' => $userIds]);
    }

    /**
     * Find posts with images
     * @return PostQuery
     */
    public function withImages()
    {
        return $this->innerJoinWith('postImages');
    }

    /**
     * Find posts with captions
     * @return PostQuery
     */
    public function withCaptions()
    {
        return $this->andWhere(['not', ['caption' => null]])
                    ->andWhere(['!=', 'caption', '']);
    }

    /**
     * Find posts by location
     * @param string $location
     * @return PostQuery
     */
    public function byLocation($location)
    {
        return $this->andWhere(['like', 'location', $location]);
    }

    /**
     * Find posts with location
     * @return PostQuery
     */
    public function withLocation()
    {
        return $this->andWhere(['not', ['location' => null]])
                    ->andWhere(['!=', 'location', '']);
    }

    /**
     * Order by creation date (newest first)
     * @return PostQuery
     */
    public function orderByNewest()
    {
        return $this->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Order by creation date (oldest first)
     * @return PostQuery
     */
    public function orderByOldest()
    {
        return $this->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Order by likes count (most liked first)
     * @return PostQuery
     */
    public function orderByLikes()
    {
        return $this->orderBy(['likes_count' => SORT_DESC]);
    }

    /**
     * Order by comments count (most commented first)
     * @return PostQuery
     */
    public function orderByComments()
    {
        return $this->orderBy(['comments_count' => SORT_DESC]);
    }

    /**
     * Order by views count (most viewed first)
     * @return PostQuery
     */
    public function orderByViews()
    {
        return $this->orderBy(['views_count' => SORT_DESC]);
    }

    /**
     * Find posts created today
     * @return PostQuery
     */
    public function today()
    {
        $startOfDay = strtotime('today');
        $endOfDay = strtotime('tomorrow') - 1;
        return $this->andWhere(['between', 'created_at', $startOfDay, $endOfDay]);
    }

    /**
     * Find posts created this week
     * @return PostQuery
     */
    public function thisWeek()
    {
        $startOfWeek = strtotime('monday this week');
        $endOfWeek = strtotime('sunday this week') + 86399;
        return $this->andWhere(['between', 'created_at', $startOfWeek, $endOfWeek]);
    }

    /**
     * Find posts created this month
     * @return PostQuery
     */
    public function thisMonth()
    {
        $startOfMonth = strtotime('first day of this month');
        $endOfMonth = strtotime('last day of this month') + 86399;
        return $this->andWhere(['between', 'created_at', $startOfMonth, $endOfMonth]);
    }

    /**
     * Find posts created in date range
     * @param int $startDate
     * @param int $endDate
     * @return PostQuery
     */
    public function createdBetween($startDate, $endDate)
    {
        return $this->andWhere(['between', 'created_at', $startDate, $endDate]);
    }

    /**
     * Find posts with minimum likes
     * @param int $minLikes
     * @return PostQuery
     */
    public function withMinLikes($minLikes)
    {
        return $this->andWhere(['>=', 'likes_count', $minLikes]);
    }

    /**
     * Find posts with minimum comments
     * @param int $minComments
     * @return PostQuery
     */
    public function withMinComments($minComments)
    {
        return $this->andWhere(['>=', 'comments_count', $minComments]);
    }

    /**
     * Find posts with minimum views
     * @param int $minViews
     * @return PostQuery
     */
    public function withMinViews($minViews)
    {
        return $this->andWhere(['>=', 'views_count', $minViews]);
    }

    /**
     * Find posts that user can view (public or from followed users)
     * @param User $user
     * @return PostQuery
     */
    public function viewableBy($user)
    {
        // Get users that the current user follows
        $followedUserIds = Follow::find()
            ->select('following_id')
            ->where(['follower_id' => $user->id])
            ->column();

        // Add current user to the list
        $followedUserIds[] = $user->id;

        return $this->andWhere([
            'or',
            ['is_public' => true],
            ['user_id' => $followedUserIds]
        ]);
    }

    /**
     * Find trending posts (high engagement)
     * @return PostQuery
     */
    public function trending()
    {
        return $this->andWhere([
            'or',
            ['>=', 'likes_count', 100],
            ['>=', 'comments_count', 10],
            ['>=', 'views_count', 1000]
        ])->orderByLikes();
    }

    /**
     * Find popular posts (high likes)
     * @return PostQuery
     */
    public function popular()
    {
        return $this->withMinLikes(50)->orderByLikes();
    }
}
