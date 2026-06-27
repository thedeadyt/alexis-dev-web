<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestimonialSeeder extends Seeder {
    public function run(): void {
        DB::table('testimonials')->insert([
            ['quote'=>"KODEX a compris notre métier avant d'écrire la moindre ligne de code. Le site est rapide, simple à gérer, et nous recevons des commandes tous les jours.",'author'=>'Sophie Martin','role'=>'Gérante, Boulangerie Martin','sort_order'=>1,'active'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['quote'=>"Enfin un prestataire qui parle clairement et tient ses délais. L'application gère toute notre association sans accroc depuis un an.",'author'=>'Pierre Lambert','role'=>'Président, Association Solidaire','sort_order'=>2,'active'=>1,'created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
