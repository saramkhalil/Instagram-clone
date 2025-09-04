<?php

use yii\db\Migration;

class m250904_164343_create_comments_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%comments}}', [
            'id' => $this->primaryKey(),
            'post_id' => $this->integer()->notNull()->comment('Post being commented on'),
            'user_id' => $this->integer()->notNull()->comment('User who made the comment'),
            'parent_id' => $this->integer()->null()->comment('Parent comment for replies'),
            'content' => $this->text()->notNull()->comment('Comment content'),
            'is_edited' => $this->boolean()->defaultValue(false)->comment('Whether comment was edited'),
            'likes_count' => $this->integer()->defaultValue(0)->comment('Cached likes count'),
            'replies_count' => $this->integer()->defaultValue(0)->comment('Cached replies count'),
            'created_at' => $this->integer()->notNull()->comment('Comment creation timestamp'),
            'updated_at' => $this->integer()->notNull()->comment('Comment last update timestamp'),
        ], $tableOptions);

        // Add foreign key constraints
        $this->addForeignKey(
            'fk-comments-post_id',
            '{{%comments}}',
            'post_id',
            '{{%posts}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-comments-user_id',
            '{{%comments}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-comments-parent_id',
            '{{%comments}}',
            'parent_id',
            '{{%comments}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add indexes for better performance
        $this->createIndex('idx-comments-post_id', '{{%comments}}', 'post_id');
        $this->createIndex('idx-comments-user_id', '{{%comments}}', 'user_id');
        $this->createIndex('idx-comments-parent_id', '{{%comments}}', 'parent_id');
        $this->createIndex('idx-comments-created_at', '{{%comments}}', 'created_at');
        $this->createIndex('idx-comments-likes_count', '{{%comments}}', 'likes_count');
    }

    public function down()
    {
        // Drop foreign keys first
        $this->dropForeignKey('fk-comments-parent_id', '{{%comments}}');
        $this->dropForeignKey('fk-comments-user_id', '{{%comments}}');
        $this->dropForeignKey('fk-comments-post_id', '{{%comments}}');
        
        // Drop indexes
        $this->dropIndex('idx-comments-likes_count', '{{%comments}}');
        $this->dropIndex('idx-comments-created_at', '{{%comments}}');
        $this->dropIndex('idx-comments-parent_id', '{{%comments}}');
        $this->dropIndex('idx-comments-user_id', '{{%comments}}');
        $this->dropIndex('idx-comments-post_id', '{{%comments}}');
        
        // Drop table
        $this->dropTable('{{%comments}}');
    }
}
