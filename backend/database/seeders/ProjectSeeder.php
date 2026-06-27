<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder {
    public function run(): void {
        $now = now()->toDateTimeString();
        $projects = [
            [
                'slug' => 'boulangerie-martin',
                'name' => 'Boulangerie Martin',
                'client' => 'Artisan',
                'category' => 'Sites vitrines',
                'year' => '2025',
                'summary' => 'Site vitrine avec carte des produits et commande de pains spéciaux en ligne.',
                'full_text' => json_encode([
                    "La Boulangerie Martin, artisan boulanger installé depuis vingt ans, souhaitait une présence en ligne à la hauteur de son savoir-faire, sans tomber dans le site générique des grandes enseignes.",
                    "Nous avons conçu un site vitrine sobre et rapide, mettant en avant les produits du jour, les horaires et un formulaire de réservation pour les pains spéciaux et commandes de fêtes.",
                    "Le back-office, développé sur-mesure en PHP, permet au gérant de mettre à jour la carte et les disponibilités en quelques clics, sans aucune compétence technique.",
                    "Résultat : un site chargé en moins d'une seconde, parfaitement référencé localement, et une trentaine de réservations en ligne chaque semaine.",
                ]),
                'tech' => json_encode(['PHP', 'Tailwind', 'MySQL']),
                'rendered' => json_encode(['Maquettes & design', 'Développement front', 'Back-office sur-mesure', 'Référencement local']),
                'sort_order' => 1, 'active' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'slug' => 'portail-asso',
                'name' => 'Portail Solidaire',
                'client' => 'Association',
                'category' => 'Applications',
                'year' => '2025',
                'summary' => 'Application de gestion des adhérents et des événements pour une association locale.',
                'full_text' => json_encode([
                    "Le Portail Solidaire est une application web destinée à une association d'aide aux personnes âgées, gérant plus de 400 adhérents et une cinquantaine de bénévoles.",
                    "L'enjeu : remplacer un patchwork de tableurs par un outil unique, accessible aux bénévoles non-techniciens, pour suivre les adhésions, planifier les visites et gérer les événements.",
                    "Nous avons développé une interface React claire adossée à une API PHP, avec gestion fine des droits, exports comptables et rappels automatiques par email.",
                    "L'association a divisé par trois le temps consacré à l'administratif, au profit du terrain.",
                ]),
                'tech' => json_encode(['React', 'PHP', 'MariaDB']),
                'rendered' => json_encode(['Architecture applicative', 'Développement full-stack', 'Gestion des droits', 'Formation des bénévoles']),
                'sort_order' => 2, 'active' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'slug' => 'api-logistique',
                'name' => 'API Logistique Pro',
                'client' => 'PME',
                'category' => 'Infra',
                'year' => '2024',
                'summary' => 'API de synchronisation entre le stock, la boutique et le transporteur.',
                'full_text' => json_encode([
                    "Logistique Pro, PME de distribution, perdait un temps considérable à ressaisir manuellement les commandes entre sa boutique en ligne, son logiciel de stock et son transporteur.",
                    "Nous avons conçu une API REST sécurisée par OAuth qui orchestre les flux en temps réel : une commande déclenche automatiquement la mise à jour du stock et la génération de l'étiquette d'expédition.",
                    "L'ensemble est déployé en conteneurs Docker, avec supervision et journalisation, pour une disponibilité maximale.",
                    "Zéro ressaisie, zéro erreur d'expédition, et plusieurs heures gagnées chaque jour.",
                ]),
                'tech' => json_encode(['REST', 'OAuth', 'Docker']),
                'rendered' => json_encode(["Conception de l'API", 'Sécurisation OAuth', 'Déploiement Docker', 'Supervision']),
                'sort_order' => 3, 'active' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'slug' => 'boutique-terroir',
                'name' => 'Boutique Terroir',
                'client' => 'E-commerce',
                'category' => 'Sites vitrines',
                'year' => '2024',
                'summary' => 'Boutique en ligne de produits régionaux avec paiement sécurisé et click & collect.',
                'full_text' => json_encode([
                    "Boutique Terroir réunit une douzaine de producteurs régionaux autour d'une boutique en ligne commune, avec retrait en point relais et livraison locale.",
                    "Nous avons mis en place une solution WooCommerce profondément personnalisée : gestion multi-vendeurs, calcul de frais de port par zone et tunnel de commande simplifié.",
                    "Le serveur, configuré sous Nginx avec mise en cache et certificat SSL, encaisse les pics de trafic des marchés de fin d'année sans ralentir.",
                    "Le chiffre d'affaires en ligne a doublé lors de la première saison.",
                ]),
                'tech' => json_encode(['WooCommerce', 'PHP', 'Nginx']),
                'rendered' => json_encode(['Intégration WooCommerce', 'Multi-vendeurs', 'Configuration serveur', 'Paiement sécurisé']),
                'sort_order' => 4, 'active' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'slug' => 'dashboard-collectivite',
                'name' => 'Tableau de bord Ville',
                'client' => 'Collectivité',
                'category' => 'Applications',
                'year' => '2023',
                'summary' => 'Tableau de bord interne de suivi des demandes citoyennes pour une mairie.',
                'full_text' => json_encode([
                    "Une commune de taille moyenne souhaitait centraliser le suivi des demandes citoyennes — voirie, propreté, espaces verts — jusqu'alors éparpillées entre services.",
                    "Nous avons livré un tableau de bord React permettant d'enregistrer, qualifier et affecter chaque demande, avec statuts, échéances et statistiques par service.",
                    "L'application est hébergée sur l'infrastructure interne de la collectivité, sous Linux, dans le respect strict des contraintes de souveraineté des données.",
                    "Les délais de traitement des demandes ont été réduits de moitié.",
                ]),
                'tech' => json_encode(['React', 'Linux', 'PostgreSQL']),
                'rendered' => json_encode(['Cahier des charges', 'Développement React', 'Hébergement souverain', 'Tableaux de bord']),
                'sort_order' => 5, 'active' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'slug' => 'site-cabinet',
                'name' => 'Cabinet Lextra',
                'client' => 'PME',
                'category' => 'Sites vitrines',
                'year' => '2023',
                'summary' => "Site vitrine élégant et prise de rendez-vous en ligne pour un cabinet d'avocats.",
                'full_text' => json_encode([
                    "Le cabinet d'avocats Lextra voulait un site à son image : sérieux, rassurant et impeccablement lisible, avec une prise de rendez-vous en ligne.",
                    "Nous avons conçu une vitrine épurée présentant les domaines d'expertise et l'équipe, complétée d'un module de réservation de créneaux connecté à l'agenda du cabinet.",
                    "L'accent a été mis sur l'accessibilité et la performance, pour une expérience irréprochable sur tous les appareils.",
                    "Le cabinet reçoit désormais l'essentiel de ses premiers rendez-vous via le site.",
                ]),
                'tech' => json_encode(['HTML/CSS', 'PHP', 'Tailwind']),
                'rendered' => json_encode(['Direction artistique', 'Développement front', 'Module de rendez-vous', 'Accessibilité']),
                'sort_order' => 6, 'active' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ];

        DB::table('projects')->insert($projects);
    }
}
