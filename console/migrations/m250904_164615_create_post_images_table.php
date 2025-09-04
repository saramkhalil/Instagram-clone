<?php

use yii\db\Migration;

class m250904_164615_create_post_images_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%post_images}}', [
            'id' => $this->primaryKey(),
            'post_id' => $this->integer()->notNull()->comment('Post this image belongs to'),
            'image_url' => $this->string(500)->notNull()->comment('URL/path to the image'),
            'thumbnail_url' => $this->string(500)->comment('URL/path to the thumbnail'),
            'alt_text' => $this->string(255)->comment('Alt text for accessibility'),
            'caption' => $this->string(500)->comment('Image-specific caption'),
            'sort_order' => $this->integer()->defaultValue(0)->comment('Order of images in the post'),
            'file_size' => $this->bigInteger()->comment('File size in bytes'),
            'width' => $this->integer()->comment('Image width in pixels'),
            'height' => $this->integer()->comment('Image height in pixels'),
            'mime_type' => $this->string(100)->comment('MIME type of the image'),
            'is_primary' => $this->boolean()->defaultValue(false)->comment('Whether this is the primary image'),
            'created_at' => $this->integer()->notNull()->comment('Image creation timestamp'),
            'updated_at' => $this->integer()->notNull()->comment('Image last update timestamp'),
        ], $tableOptions);

        // Add foreign key constraint
        $this->addForeignKey(
            'fk-post_images-post_id',
            '{{%post_images}}',
            'post_id',
            '{{%posts}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add indexes for better performance
        $this->createIndex('idx-post_images-post_id', '{{%post_images}}', 'post_id');
        $this->createIndex('idx-post_images-sort_order', '{{%post_images}}', 'sort_order');
        $this->createIndex('idx-post_images-is_primary', '{{%post_images}}', 'is_primary');
        $this->createIndex('idx-post_images-created_at', '{{%post_images}}', 'created_at');
    }

    public function down()
    {
        // Drop foreign key first
        $this->dropForeignKey('fk-post_images-post_id', '{{%post_images}}');
        
        // Drop indexes
        $this->dropIndex('idx-post_images-created_at', '{{%post_images}}');
        $this->dropIndex('idx-post_images-is_primary', '{{%post_images}}');
        $this->dropIndex('idx-post_images-sort_order', '{{%post_images}}');
        $this->dropIndex('idx-post_images-post_id', '{{%post_images}}');
        
        // Drop table
        $this->dropTable('{{%post_images}}');
    }
}
