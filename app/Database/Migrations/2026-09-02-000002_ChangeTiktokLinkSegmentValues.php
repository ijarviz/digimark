<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Replaces the segment taxonomy from influencer-tier (nano/micro/macro/
 * celebrity) to target-audience persona (daily_life/mahasiswa/kantoran/
 * irt) per updated campaign requirements. The two taxonomies don't map
 * onto each other, so existing values are cleared rather than guessed.
 */
class ChangeTiktokLinkSegmentValues extends Migration
{
    public function up()
    {
        $this->db->table('tiktok_link')->update(['segment' => null]);

        $this->forge->modifyColumn('tiktok_link', [
            'segment' => [
                'name'       => 'segment',
                'type'       => 'ENUM',
                'constraint' => ['daily_life', 'mahasiswa', 'kantoran', 'irt'],
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->db->table('tiktok_link')->update(['segment' => null]);

        $this->forge->modifyColumn('tiktok_link', [
            'segment' => [
                'name'       => 'segment',
                'type'       => 'ENUM',
                'constraint' => ['nano', 'micro', 'macro', 'celebrity'],
                'null'       => true,
            ],
        ]);
    }
}
