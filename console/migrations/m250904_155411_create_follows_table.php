<?php

use yii\db\Migration;

class m250904_155411_create_follows_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%follows}}', [
            'id' => $this->primaryKey(),
            'follower_id' => $this->integer()->notNull()->comment('User who is following'),
            'following_id' => $this->integer()->notNull()->comment('User being followed'),
            'status' => $this->string(20)->defaultValue('active')->comment('Follow status: active, blocked, pending'),
            'created_at' => $this->integer()->notNull()->comment('Follow creation timestamp'),
            'updated_at' => $this->integer()->notNull()->comment('Follow last update timestamp'),
        ], $tableOptions);

        // Add foreign key constraints
        $this->addForeignKey(
            'fk-follows-follower_id',
            '{{%follows}}',
            'follower_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-follows-following_id',
            '{{%follows}}',
            'following_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add unique constraint to prevent duplicate follows
        $this->createIndex('idx-follows-unique', '{{%follows}}', ['follower_id', 'following_id'], true);

        // Add indexes for better performance
        $this->createIndex('idx-follows-follower_id', '{{%follows}}', 'follower_id');
        $this->createIndex('idx-follows-following_id', '{{%follows}}', 'following_id');
        $this->createIndex('idx-follows-status', '{{%follows}}', 'status');
        $this->createIndex('idx-follows-created_at', '{{%follows}}', 'created_at');
    }

    public function down()
    {
        // Drop foreign keys first
        $this->dropForeignKey('fk-follows-following_id', '{{%follows}}');
        $this->dropForeignKey('fk-follows-follower_id', '{{%follows}}');
        
        // Drop indexes
        $this->dropIndex('idx-follows-created_at', '{{%follows}}');
        $this->dropIndex('idx-follows-status', '{{%follows}}');
        $this->dropIndex('idx-follows-following_id', '{{%follows}}');
        $this->dropIndex('idx-follows-follower_id', '{{%follows}}');
        $this->dropIndex('idx-follows-unique', '{{%follows}}');
        
        // Drop table
        $this->dropTable('{{%follows}}');
    }
}
