<?php

use yii\db\Migration;

class m250904_145453_enhance_user_table_for_instagram extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user}}', 'first_name', $this->string(50)->after('username'));
        $this->addColumn('{{%user}}', 'last_name', $this->string(50)->after('first_name'));
        $this->addColumn('{{%user}}', 'bio', $this->text()->after('last_name'));
        $this->addColumn('{{%user}}', 'website', $this->string(255)->after('bio'));
        $this->addColumn('{{%user}}', 'phone', $this->string(20)->after('website'));
        $this->addColumn('{{%user}}', 'gender', $this->string(10)->after('phone'));
        $this->addColumn('{{%user}}', 'profile_picture', $this->string(255)->after('gender'));
        $this->addColumn('{{%user}}', 'cover_photo', $this->string(255)->after('profile_picture'));
        $this->addColumn('{{%user}}', 'is_private', $this->boolean()->defaultValue(false)->after('cover_photo'));
        $this->addColumn('{{%user}}', 'is_verified', $this->boolean()->defaultValue(false)->after('is_private'));
        $this->addColumn('{{%user}}', 'followers_count', $this->integer()->defaultValue(0)->after('is_verified'));
        $this->addColumn('{{%user}}', 'following_count', $this->integer()->defaultValue(0)->after('followers_count'));
        $this->addColumn('{{%user}}', 'posts_count', $this->integer()->defaultValue(0)->after('following_count'));
        $this->addColumn('{{%user}}', 'last_login_at', $this->integer()->after('posts_count'));
        $this->addColumn('{{%user}}', 'timezone', $this->string(50)->defaultValue('UTC')->after('last_login_at'));
    }

    public function down()
    {
        $this->dropColumn('{{%user}}', 'timezone');
        $this->dropColumn('{{%user}}', 'last_login_at');
        $this->dropColumn('{{%user}}', 'posts_count');
        $this->dropColumn('{{%user}}', 'following_count');
        $this->dropColumn('{{%user}}', 'followers_count');
        $this->dropColumn('{{%user}}', 'is_verified');
        $this->dropColumn('{{%user}}', 'is_private');
        $this->dropColumn('{{%user}}', 'cover_photo');
        $this->dropColumn('{{%user}}', 'profile_picture');
        $this->dropColumn('{{%user}}', 'gender');
        $this->dropColumn('{{%user}}', 'phone');
        $this->dropColumn('{{%user}}', 'website');
        $this->dropColumn('{{%user}}', 'bio');
        $this->dropColumn('{{%user}}', 'last_name');
        $this->dropColumn('{{%user}}', 'first_name');
    }
}
