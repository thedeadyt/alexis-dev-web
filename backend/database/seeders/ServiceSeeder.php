<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder {
    public function run(): void {
        $now = now()->toDateTimeString();
        $services = [
            ['slug'=>'vitrine','label'=>'// vitrine/','title'=>'Sites vitrines','sub'=>'Pour présenter votre activité efficacement','body'=>"Un site clair, rapide et bien référencé pour donner à votre activité la présence qu'elle mérite en ligne.",'tags'=>json_encode(['HTML/CSS','PHP','Tailwind']),'price'=>'À partir de 800€','sort_order'=>1,'active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['slug'=>'app','label'=>'// app/','title'=>'Applications web','sub'=>'Outils métier, portails clients, back-offices','body'=>'Des applications sur-mesure qui automatisent vos processus et remplacent les tableurs éparpillés.','tags'=>json_encode(['React','PHP','MariaDB']),'price'=>'À partir de 3500€','sort_order'=>2,'active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['slug'=>'api','label'=>'// api/','title'=>'APIs & intégrations','sub'=>'Connectez vos outils entre eux','body'=>'Synchronisez stock, boutique, CRM et transporteurs grâce à des API robustes et documentées.','tags'=>json_encode(['REST','JSON','OAuth']),'price'=>'À partir de 1500€','sort_order'=>3,'active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['slug'=>'shop','label'=>'// shop/','title'=>'E-commerce','sub'=>'Boutiques en ligne performantes et sécurisées','body'=>'Des boutiques rapides et fiables, du tunnel de commande au paiement sécurisé et au suivi des stocks.','tags'=>json_encode(['WooCommerce','PHP','SSL']),'price'=>'À partir de 2500€','sort_order'=>4,'active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['slug'=>'host','label'=>'// host/','title'=>'Hébergement managé','sub'=>'Serveur, SSL, sauvegardes, monitoring','body'=>"Nous gérons l'infrastructure de bout en bout : serveur, certificats, sauvegardes et supervision continue.",'tags'=>json_encode(['Docker','Linux','Nginx']),'price'=>'À partir de 49€/mois','sort_order'=>5,'active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['slug'=>'care','label'=>'// care/','title'=>'Maintenance','sub'=>'Contrats de maintenance mensuelle sur mesure','body'=>'Mises à jour, surveillance et support réactif pour garder votre site sûr, rapide et à jour.','tags'=>json_encode(['Updates','Monitoring','Support']),'price'=>'À partir de 39€/mois','sort_order'=>6,'active'=>1,'created_at'=>$now,'updated_at'=>$now],
        ];
        DB::table('services')->insert($services);
    }
}
