<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketplace_categories')) {
            $categories = [
                ['name' => 'Free Fire', 'slug' => 'free-fire', 'sort_order' => 1],
                ['name' => 'Roblox', 'slug' => 'roblox', 'sort_order' => 2],
                ['name' => 'Steam', 'slug' => 'steam', 'sort_order' => 3],
                ['name' => 'League of Legends', 'slug' => 'league-of-legends', 'sort_order' => 4],
                ['name' => 'Valorant', 'slug' => 'valorant', 'sort_order' => 5],
                ['name' => 'Minecraft', 'slug' => 'minecraft', 'sort_order' => 6],
                ['name' => 'Assinaturas e Premium', 'slug' => 'assinaturas-e-premium', 'sort_order' => 7],
                ['name' => 'Outros', 'slug' => 'outros', 'sort_order' => 99],
            ];

            $now = now();
            foreach ($categories as $cat) {
                $exists = DB::table('marketplace_categories')->where('slug', $cat['slug'])->exists();
                if (! $exists) {
                    DB::table('marketplace_categories')->insert([
                        'name' => $cat['name'],
                        'slug' => $cat['slug'],
                        'sort_order' => $cat['sort_order'],
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        if (! Schema::hasTable('sales_achievements')) {
            return;
        }

        $levels = [
            [1, 1, 'Bronze'],
            [2, 5, 'Âmbar'],
            [3, 15, 'Prata'],
            [4, 30, 'Turquesa'],
            [5, 50, 'Esmeralda'],
            [6, 80, 'Safira'],
            [7, 120, 'Rubi'],
            [8, 180, 'Ametista'],
            [9, 250, 'Ônix'],
            [10, 350, 'Diamante'],
            [11, 500, 'Obsidiana'],
            [12, 700, 'Titânio'],
            [13, 1000, 'Platina'],
            [14, 1400, 'Cristal'],
            [15, 2000, 'Lenda'],
            [16, 2800, 'Mestre'],
            [17, 4000, 'Grão-Mestre'],
            [18, 5500, 'Campeão'],
            [19, 7500, 'Imortal'],
            [20, 10000, 'Supremo'],
        ];

        $now = now();
        foreach ($levels as [$level, $threshold, $name]) {
            $slug = 'nivel-'.$level.'-'.Str::slug($name);
            $exists = DB::table('sales_achievements')->where('slug', $slug)->exists();
            if ($exists) {
                continue;
            }

            DB::table('sales_achievements')->insert([
                'slug' => $slug,
                'name' => "Nível {$level}: {$name}",
                'description' => "Alcance {$threshold} vendas válidas para subir ao nível {$level}.",
                'threshold' => $threshold,
                'metric_type' => 'sales_count',
                'image' => null,
                'sort_order' => $level,
                'is_active' => true,
                'reward_name' => "Placa Nível {$level}",
                'reward_description' => "Placa física de faturamento — {$name}",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_achievements')) {
            DB::table('sales_achievements')->where('metric_type', 'sales_count')->where('slug', 'like', 'nivel-%')->delete();
        }
        if (Schema::hasTable('marketplace_categories')) {
            DB::table('marketplace_categories')->whereIn('slug', [
                'free-fire', 'roblox', 'steam', 'league-of-legends', 'valorant', 'minecraft', 'assinaturas-e-premium', 'outros',
            ])->delete();
        }
    }
};
