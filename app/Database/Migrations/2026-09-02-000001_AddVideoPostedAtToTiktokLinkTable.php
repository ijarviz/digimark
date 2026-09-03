<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The TikTok video's own upload date (from the scraped payload's
 * createTime), not to be confused with created_at (when the link was added
 * to this app). Static per-video metadata, so it lives on tiktok_link
 * rather than tiktok_insight_daily.
 */
class AddVideoPostedAtToTiktokLinkTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tiktok_link', [
            'video_posted_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'tiktok_video_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tiktok_link', ['video_posted_at']);
    }
}
