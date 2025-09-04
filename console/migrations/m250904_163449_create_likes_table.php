<?php

use yii\db\Migration;

class m250904_163449_create_likes_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%likes}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('User who liked the post'),
            'post_id' => $this->integer()->notNull()->comment('Post that was liked'),
            'created_at' => $this->integer()->notNull()->comment('Like creation timestamp'),
            'updated_at' => $this->integer()->notNull()->comment('Like last update timestamp'),
        ], $tableOptions);

        // Add foreign key constraints
        $this->addForeignKey(
            'fk-likes-user_id',
            '{{%likes}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-likes-post_id',
            '{{%likes}}',
            'post_id',
            '{{%posts}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add unique constraint to prevent duplicate likes
        $this->createIndex('idx-likes-unique', '{{%likes}}', ['user_id', 'post_id'], true);

        // Add indexes for better performance
        $this->createIndex('idx-likes-user_id', '{{%likes}}', 'user_id');
        $this->createIndex('idx-likes-post_id', '{{%likes}}', 'post_id');
        $this->createIndex('idx-likes-created_at', '{{%likes}}', 'created_at');
    }

    public function down()
    {
        // Drop foreign keys first
        $this->dropForeignKey('fk-likes-post_id', '{{%likes}}');
        $this->dropForeignKey('fk-likes-user_id', '{{%likes}}');
        
        // Drop indexes
        $this->dropIndex('idx-likes-created_at', '{{%likes}}');
        $this->dropIndex('idx-likes-post_id', '{{%likes}}');
        $this->dropIndex('idx-likes-user_id', '{{%likes}}');
        $this->dropIndex('idx-likes-unique', '{{%likes}}');
        
        // Drop table
        $this->dropTable('{{%likes}}');
    }
}
