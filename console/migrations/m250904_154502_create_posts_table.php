<?php

use yii\db\Migration;

class m250904_154502_create_posts_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%posts}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('User who created the post'),
            'caption' => $this->text()->comment('Post caption/description'),
            'location' => $this->string(255)->comment('Location where photo was taken'),
            'is_public' => $this->boolean()->defaultValue(true)->comment('Whether post is public or private'),
            'likes_count' => $this->integer()->defaultValue(0)->comment('Cached likes count'),
            'comments_count' => $this->integer()->defaultValue(0)->comment('Cached comments count'),
            'shares_count' => $this->integer()->defaultValue(0)->comment('Cached shares count'),
            'views_count' => $this->integer()->defaultValue(0)->comment('Cached views count'),
            'created_at' => $this->integer()->notNull()->comment('Post creation timestamp'),
            'updated_at' => $this->integer()->notNull()->comment('Post last update timestamp'),
        ], $tableOptions);

        // Add foreign key constraint
        $this->addForeignKey(
            'fk-posts-user_id',
            '{{%posts}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add indexes for better performance
        $this->createIndex('idx-posts-user_id', '{{%posts}}', 'user_id');
        $this->createIndex('idx-posts-created_at', '{{%posts}}', 'created_at');
        $this->createIndex('idx-posts-is_public', '{{%posts}}', 'is_public');
        $this->createIndex('idx-posts-likes_count', '{{%posts}}', 'likes_count');
    }

    public function down()
    {
        // Drop foreign key first
        $this->dropForeignKey('fk-posts-user_id', '{{%posts}}');
        
        // Drop indexes
        $this->dropIndex('idx-posts-likes_count', '{{%posts}}');
        $this->dropIndex('idx-posts-is_public', '{{%posts}}');
        $this->dropIndex('idx-posts-created_at', '{{%posts}}');
        $this->dropIndex('idx-posts-user_id', '{{%posts}}');
        
        // Drop table
        $this->dropTable('{{%posts}}');
    }
}
