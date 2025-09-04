<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "post_images".
 *
 * @property int $id
 * @property int $post_id
 * @property string $image_url
 * @property string|null $thumbnail_url
 * @property string|null $alt_text
 * @property string|null $caption
 * @property int $sort_order
 * @property int|null $file_size
 * @property int|null $width
 * @property int|null $height
 * @property string|null $mime_type
 * @property bool $is_primary
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Post $post
 */
class PostImage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%post_images}}';
    }

    /**
     * {@inheritdoc}
     * @return PostImageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PostImageQuery(get_called_class());
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
            [['post_id', 'image_url'], 'required'],
            [['post_id', 'sort_order', 'file_size', 'width', 'height'], 'integer', 'min' => 0],
            [['image_url', 'thumbnail_url'], 'string', 'max' => 500],
            [['alt_text'], 'string', 'max' => 255],
            [['caption'], 'string', 'max' => 500],
            [['mime_type'], 'string', 'max' => 100],
            [['is_primary'], 'boolean'],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::class, 'targetAttribute' => ['post_id' => 'id']],
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
            'image_url' => 'Image URL',
            'thumbnail_url' => 'Thumbnail URL',
            'alt_text' => 'Alt Text',
            'caption' => 'Caption',
            'sort_order' => 'Sort Order',
            'file_size' => 'File Size',
            'width' => 'Width',
            'height' => 'Height',
            'mime_type' => 'MIME Type',
            'is_primary' => 'Is Primary',
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
     * Check if this image has a thumbnail
     * @return bool
     */
    public function hasThumbnail()
    {
        return !empty($this->thumbnail_url);
    }

    /**
     * Get thumbnail URL or fallback to main image
     * @return string
     */
    public function getThumbnailUrl()
    {
        return $this->hasThumbnail() ? $this->thumbnail_url : $this->image_url;
    }

    /**
     * Check if this image has dimensions
     * @return bool
     */
    public function hasDimensions()
    {
        return !empty($this->width) && !empty($this->height);
    }

    /**
     * Get aspect ratio
     * @return float|null
     */
    public function getAspectRatio()
    {
        if (!$this->hasDimensions()) {
            return null;
        }
        return $this->width / $this->height;
    }

    /**
     * Check if image is landscape
     * @return bool
     */
    public function isLandscape()
    {
        return $this->getAspectRatio() > 1;
    }

    /**
     * Check if image is portrait
     * @return bool
     */
    public function isPortrait()
    {
        return $this->getAspectRatio() < 1;
    }

    /**
     * Check if image is square
     * @return bool
     */
    public function isSquare()
    {
        return abs($this->getAspectRatio() - 1) < 0.01;
    }

    /**
     * Get formatted file size
     * @return string
     */
    public function getFormattedFileSize()
    {
        if (empty($this->file_size)) {
            return 'Unknown';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get image dimensions string
     * @return string
     */
    public function getDimensionsString()
    {
        if (!$this->hasDimensions()) {
            return 'Unknown';
        }
        return $this->width . ' × ' . $this->height;
    }

    /**
     * Set as primary image
     * @return bool
     */
    public function setAsPrimary()
    {
        // First, unset any existing primary image for this post
        static::updateAll(['is_primary' => false], ['post_id' => $this->post_id]);
        
        // Set this image as primary
        $this->is_primary = true;
        return $this->save(false);
    }

    /**
     * Create a new post image
     * @param int $postId
     * @param string $imageUrl
     * @param array $options
     * @return PostImage|null
     */
    public static function createImage($postId, $imageUrl, $options = [])
    {
        $image = new static();
        $image->post_id = $postId;
        $image->image_url = $imageUrl;
        
        // Set optional fields
        if (isset($options['thumbnail_url'])) {
            $image->thumbnail_url = $options['thumbnail_url'];
        }
        if (isset($options['alt_text'])) {
            $image->alt_text = $options['alt_text'];
        }
        if (isset($options['caption'])) {
            $image->caption = $options['caption'];
        }
        if (isset($options['sort_order'])) {
            $image->sort_order = $options['sort_order'];
        }
        if (isset($options['file_size'])) {
            $image->file_size = $options['file_size'];
        }
        if (isset($options['width'])) {
            $image->width = $options['width'];
        }
        if (isset($options['height'])) {
            $image->height = $options['height'];
        }
        if (isset($options['mime_type'])) {
            $image->mime_type = $options['mime_type'];
        }
        if (isset($options['is_primary'])) {
            $image->is_primary = $options['is_primary'];
        }

        // If this is the first image for the post, make it primary
        if (!isset($options['is_primary'])) {
            $existingImages = static::find()->where(['post_id' => $postId])->count();
            if ($existingImages === 0) {
                $image->is_primary = true;
            }
        }

        if ($image->save()) {
            return $image;
        }

        return null;
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
