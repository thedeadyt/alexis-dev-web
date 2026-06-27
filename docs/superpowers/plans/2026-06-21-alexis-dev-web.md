# alexis dev web — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Construire le site vitrine + applicatif "alexis dev web" sur alexis-rodrigues.fr avec Laravel (API + admin Blade) servant un frontend React/Vite.

**Architecture:** Un container Docker `alexis-web` (PHP 8.3-FPM + nginx interne). Laravel gère les routes `/api/*` (JSON), `/admin/*` (Blade), et renvoie `public/build/index.html` pour toutes les autres routes (catch-all SPA React). Le build Vite est généré dans `backend/public/build/`.

**Tech Stack:** PHP 8.3, Laravel 11, MariaDB (container `php-prod-db`), Vite 5, React 18, Tailwind CSS 3, React Router 6, PHPMailer, barryvdh/laravel-dompdf, Docker, nginx.

## Global Constraints

- Répertoire racine : `/mnt/docker-volumes/alexis-dev-web/`
- Nom agence : `alexis dev web` (minuscules)
- Couleurs : fond `#111111`, accent `#8B00FF`, texte `#F9F9F9`, secondaire `#888888`
- Fonts : Space Grotesk (titres), Inter (corps), JetBrains Mono (labels `// x/`)
- DB host : `php-prod-db`, port `3306`, DB name : `alexis_dev_web`
- DB root password : `root11122005`
- Réseau Docker : `alex2-net` (external)
- Domaine : `alexis-rodrigues.fr` (remplace `nodejs:3000/sites/portfolio-alexis/`)
- PHP >= 8.3, Laravel 11, Node >= 20

---

## File Map

```
/mnt/docker-volumes/alexis-dev-web/
├── Dockerfile
├── docker-compose.yml
├── nginx.conf                         ← nginx interne container
├── supervisord.conf
├── frontend/
│   ├── package.json
│   ├── vite.config.ts                 ← outDir: ../backend/public/build
│   ├── tailwind.config.ts
│   ├── tsconfig.json
│   ├── index.html
│   └── src/
│       ├── main.tsx
│       ├── App.tsx
│       ├── index.css
│       ├── types/index.ts
│       ├── hooks/useApi.ts
│       ├── components/
│       │   ├── Navbar.tsx
│       │   └── Footer.tsx
│       └── pages/
│           ├── Home.tsx
│           ├── Services.tsx
│           ├── Projects.tsx
│           ├── ProjectDetail.tsx
│           ├── Agency.tsx
│           └── Contact.tsx
└── backend/                           ← Laravel 11 (monté comme volume)
    ├── .env
    ├── app/Http/Controllers/
    │   ├── Api/ProjectController.php
    │   ├── Api/ServiceController.php
    │   ├── Api/TestimonialController.php
    │   ├── Api/ContactController.php
    │   ├── Admin/AuthController.php
    │   ├── Admin/DashboardController.php
    │   ├── Admin/ProjectController.php
    │   ├── Admin/ServiceController.php
    │   ├── Admin/TestimonialController.php
    │   └── Admin/ContactController.php
    ├── app/Http/Middleware/AdminAuth.php
    ├── app/Models/{Contact,Project,Service,Testimonial}.php
    ├── app/Services/{MailService,PdfService}.php
    ├── database/migrations/           ← 4 migrations
    ├── database/seeders/              ← 4 seeders
    ├── resources/views/
    │   ├── admin/layout.blade.php
    │   ├── admin/login.blade.php
    │   ├── admin/dashboard.blade.php
    │   ├── admin/projects/{index,form}.blade.php
    │   ├── admin/services/{index,form}.blade.php
    │   ├── admin/testimonials/{index,form}.blade.php
    │   ├── admin/contacts/index.blade.php
    │   └── pdf/devis.blade.php
    └── routes/{api,web}.php
```

---

### Task 1 : Infrastructure Docker

**Files:**
- Create: `Dockerfile`
- Create: `docker-compose.yml`
- Create: `nginx.conf`
- Create: `supervisord.conf`

- [ ] **Step 1 : Créer `Dockerfile`**

```dockerfile
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx supervisor curl \
    freetype-dev libjpeg-turbo-dev libpng-dev \
    libzip-dev zip unzip \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo pdo_mysql mbstring zip gd opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN mkdir -p /run/nginx /var/log/supervisor \
  && chown -R www-data:www-data /var/www

EXPOSE 80
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

- [ ] **Step 2 : Créer `nginx.conf`**

```nginx
user www-data;
worker_processes auto;
pid /run/nginx.pid;
events { worker_connections 1024; }

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    sendfile on;
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;

    server {
        listen 80;
        root /var/www/html/public;
        index index.php;
        client_max_body_size 10M;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
            fastcgi_read_timeout 300;
        }

        location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
            expires 1y;
            add_header Cache-Control "public, immutable";
            try_files $uri =404;
        }
    }
}
```

- [ ] **Step 3 : Créer `supervisord.conf`**

```ini
[supervisord]
nodaemon=true
logfile=/var/log/supervisor/supervisord.log

[program:php-fpm]
command=/usr/local/sbin/php-fpm -F
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/php-fpm.err.log
stdout_logfile=/var/log/supervisor/php-fpm.out.log

[program:nginx]
command=/usr/sbin/nginx -g "daemon off;"
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/nginx.err.log
stdout_logfile=/var/log/supervisor/nginx.out.log
```

- [ ] **Step 4 : Créer `docker-compose.yml`**

```yaml
services:
  alexis-web:
    build: .
    container_name: alexis-web
    restart: unless-stopped
    networks:
      - alex2-net
    volumes:
      - ./backend:/var/www/html
    environment:
      DB_HOST: php-prod-db
      DB_PORT: 3306
      DB_DATABASE: alexis_dev_web
      DB_USERNAME: alexis_web
      DB_PASSWORD: alexis_web_2026

  calcom:
    image: calcom/cal.com:latest
    container_name: calcom
    restart: unless-stopped
    networks:
      - alex2-net
    environment:
      NEXTAUTH_URL: https://rdv.alexis-rodrigues.fr
      NEXTAUTH_SECRET: changeme_generate_with_openssl
      DATABASE_URL: mysql://alexis_web:alexis_web_2026@php-prod-db:3306/calcom
      CALENDSO_ENCRYPTION_KEY: changeme_32chars_minimum_here_xx

networks:
  alex2-net:
    external: true
```

- [ ] **Step 5 : Vérifier que `alex2-net` existe**

```bash
docker network ls | grep alex2-net
```
Attendu : une ligne avec `alex2-net`.

- [ ] **Step 6 : Builder l'image (sans démarrer)**

```bash
cd /mnt/docker-volumes/alexis-dev-web
docker build -t alexis-web .
```
Attendu : `Successfully built ...`

---

### Task 2 : Laravel init + DB + Migrations + Seeders

**Files:**
- Create: `backend/` (Laravel project)
- Create: `backend/database/migrations/` (4 fichiers)
- Create: `backend/database/seeders/` (4 fichiers)

- [ ] **Step 1 : Créer le projet Laravel**

```bash
cd /mnt/docker-volumes/alexis-dev-web
composer create-project laravel/laravel backend --prefer-dist
```

- [ ] **Step 2 : Configurer `.env`**

Éditer `backend/.env` :
```env
APP_NAME="alexis dev web"
APP_ENV=production
APP_KEY=                          # généré à l'étape suivante
APP_DEBUG=false
APP_URL=https://alexis-rodrigues.fr

DB_CONNECTION=mysql
DB_HOST=php-prod-db
DB_PORT=3306
DB_DATABASE=alexis_dev_web
DB_USERNAME=alexis_web
DB_PASSWORD=alexis_web_2026

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=contact.alex2.dev@gmail.com
MAIL_PASSWORD=                    # app password Gmail à configurer
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=contact.alex2.dev@gmail.com
MAIL_FROM_NAME="alexis dev web"

ADMIN_EMAIL=contact.alex2.dev@gmail.com
ADMIN_PASSWORD_HASH=              # généré à l'étape 4
```

- [ ] **Step 3 : Générer la clé d'application**

```bash
cd /mnt/docker-volumes/alexis-dev-web/backend
php artisan key:generate
```

- [ ] **Step 4 : Créer l'utilisateur admin en DB + créer la DB**

Se connecter à MariaDB depuis le container `php-prod-db` :
```bash
docker exec -it php-prod-db mysql -u root -proot11122005 -e "
  CREATE DATABASE IF NOT EXISTS alexis_dev_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER IF NOT EXISTS 'alexis_web'@'%' IDENTIFIED BY 'alexis_web_2026';
  GRANT ALL PRIVILEGES ON alexis_dev_web.* TO 'alexis_web'@'%';
  FLUSH PRIVILEGES;
"
```

Générer le hash bcrypt du mot de passe admin :
```bash
php -r "echo password_hash('VotreMotDePasseAdmin', PASSWORD_BCRYPT) . PHP_EOL;"
```
Copier le résultat dans `ADMIN_PASSWORD_HASH=` dans `.env`.

- [ ] **Step 5 : Migration `contacts`**

Créer `backend/database/migrations/2026_06_21_000001_create_contacts_table.php` :
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('type')->default('Site vitrine');
            $table->string('budget')->default('À définir');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('contacts'); }
};
```

- [ ] **Step 6 : Migration `projects`**

Créer `backend/database/migrations/2026_06_21_000002_create_projects_table.php` :
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('client');
            $table->string('category');
            $table->string('year', 4);
            $table->text('summary');
            $table->json('full_text');
            $table->json('tech');
            $table->json('rendered');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('projects'); }
};
```

- [ ] **Step 7 : Migration `services`**

Créer `backend/database/migrations/2026_06_21_000003_create_services_table.php` :
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label');
            $table->string('title');
            $table->string('sub');
            $table->text('body');
            $table->json('tags');
            $table->string('price');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('services'); }
};
```

- [ ] **Step 8 : Migration `testimonials`**

Créer `backend/database/migrations/2026_06_21_000004_create_testimonials_table.php` :
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->text('quote');
            $table->string('author');
            $table->string('role');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('testimonials'); }
};
```

- [ ] **Step 9 : Seeder `ProjectSeeder`**

Créer `backend/database/seeders/ProjectSeeder.php` :
```php
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder {
    public function run(): void {
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
            ],
        ];

        DB::table('projects')->insert($projects);
    }
}
```

- [ ] **Step 10 : Seeder `ServiceSeeder`**

Créer `backend/database/seeders/ServiceSeeder.php` :
```php
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder {
    public function run(): void {
        $services = [
            ['slug'=>'vitrine','label'=>'// vitrine/','title'=>'Sites vitrines','sub'=>'Pour présenter votre activité efficacement','body'=>"Un site clair, rapide et bien référencé pour donner à votre activité la présence qu'elle mérite en ligne.",'tags'=>json_encode(['HTML/CSS','PHP','Tailwind']),'price'=>'À partir de 800€','sort_order'=>1,'active'=>1],
            ['slug'=>'app','label'=>'// app/','title'=>'Applications web','sub'=>'Outils métier, portails clients, back-offices','body'=>'Des applications sur-mesure qui automatisent vos processus et remplacent les tableurs éparpillés.','tags'=>json_encode(['React','PHP','MariaDB']),'price'=>'À partir de 3500€','sort_order'=>2,'active'=>1],
            ['slug'=>'api','label'=>'// api/','title'=>'APIs & intégrations','sub'=>'Connectez vos outils entre eux','body'=>'Synchronisez stock, boutique, CRM et transporteurs grâce à des API robustes et documentées.','tags'=>json_encode(['REST','JSON','OAuth']),'price'=>'À partir de 1500€','sort_order'=>3,'active'=>1],
            ['slug'=>'shop','label'=>'// shop/','title'=>'E-commerce','sub'=>'Boutiques en ligne performantes et sécurisées','body'=>'Des boutiques rapides et fiables, du tunnel de commande au paiement sécurisé et au suivi des stocks.','tags'=>json_encode(['WooCommerce','PHP','SSL']),'price'=>'À partir de 2500€','sort_order'=>4,'active'=>1],
            ['slug'=>'host','label'=>'// host/','title'=>'Hébergement managé','sub'=>'Serveur, SSL, sauvegardes, monitoring','body'=>"Nous gérons l'infrastructure de bout en bout : serveur, certificats, sauvegardes et supervision continue.",'tags'=>json_encode(['Docker','Linux','Nginx']),'price'=>'À partir de 49€/mois','sort_order'=>5,'active'=>1],
            ['slug'=>'care','label'=>'// care/','title'=>'Maintenance','sub'=>'Contrats de maintenance mensuelle sur mesure','body'=>'Mises à jour, surveillance et support réactif pour garder votre site sûr, rapide et à jour.','tags'=>json_encode(['Updates','Monitoring','Support']),'price'=>'À partir de 39€/mois','sort_order'=>6,'active'=>1],
        ];
        DB::table('services')->insert($services);
    }
}
```

- [ ] **Step 11 : Seeder `TestimonialSeeder` + `DatabaseSeeder`**

Créer `backend/database/seeders/TestimonialSeeder.php` :
```php
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
```

Modifier `backend/database/seeders/DatabaseSeeder.php` :
```php
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        $this->call([
            ProjectSeeder::class,
            ServiceSeeder::class,
            TestimonialSeeder::class,
        ]);
    }
}
```

- [ ] **Step 12 : Lancer les migrations + seeders**

```bash
cd /mnt/docker-volumes/alexis-dev-web/backend
php artisan migrate --force
php artisan db:seed
```
Attendu : toutes les migrations `Success`, puis les seeders passent sans erreur.

- [ ] **Step 13 : Créer l'utilisateur admin**

```bash
php artisan tinker --execute="
  \App\Models\User::create([
    'name' => 'Alexis',
    'email' => 'contact.alex2.dev@gmail.com',
    'password' => bcrypt('VotreMotDePasseAdmin'),
  ]);
"
```

---

### Task 3 : API Models + Controllers + Routes

**Files:**
- Create: `backend/app/Models/{Contact,Project,Service,Testimonial}.php`
- Create: `backend/app/Http/Controllers/Api/{ProjectController,ServiceController,TestimonialController,ContactController}.php`
- Modify: `backend/routes/api.php`

- [ ] **Step 1 : Models**

`backend/app/Models/Project.php` :
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Project extends Model {
    protected $fillable = ['slug','name','client','category','year','summary','full_text','tech','rendered','sort_order','active'];
    protected $casts = ['full_text'=>'array','tech'=>'array','rendered'=>'array','active'=>'boolean'];
}
```

`backend/app/Models/Service.php` :
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Service extends Model {
    protected $fillable = ['slug','label','title','sub','body','tags','price','sort_order','active'];
    protected $casts = ['tags'=>'array','active'=>'boolean'];
}
```

`backend/app/Models/Testimonial.php` :
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model {
    protected $fillable = ['quote','author','role','sort_order','active'];
    protected $casts = ['active'=>'boolean'];
}
```

`backend/app/Models/Contact.php` :
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model {
    protected $fillable = ['first_name','last_name','email','phone','type','budget','message'];
}
```

- [ ] **Step 2 : Écrire le test API**

Créer `backend/tests/Feature/ApiTest.php` :
```php
<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TestimonialSeeder;

class ApiTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->seed([ProjectSeeder::class, ServiceSeeder::class, TestimonialSeeder::class]);
    }

    public function test_projects_returns_array(): void {
        $response = $this->getJson('/api/projects');
        $response->assertStatus(200)->assertJsonIsArray();
    }

    public function test_project_by_slug(): void {
        $response = $this->getJson('/api/projects/boulangerie-martin');
        $response->assertStatus(200)->assertJsonFragment(['slug' => 'boulangerie-martin']);
    }

    public function test_project_not_found(): void {
        $this->getJson('/api/projects/inexistant')->assertStatus(404);
    }

    public function test_services_returns_array(): void {
        $this->getJson('/api/services')->assertStatus(200)->assertJsonIsArray();
    }

    public function test_testimonials_returns_array(): void {
        $this->getJson('/api/testimonials')->assertStatus(200)->assertJsonIsArray();
    }

    public function test_contact_validates_required_fields(): void {
        $this->postJson('/api/contact', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'email']);
    }

    public function test_contact_stores_on_valid_data(): void {
        $this->postJson('/api/contact', [
            'first_name' => 'Jean',
            'email' => 'jean@test.com',
            'type' => 'Site vitrine',
            'budget' => '800€',
        ])->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('contacts', ['email' => 'jean@test.com']);
    }
}
```

- [ ] **Step 3 : Lancer le test — doit échouer (routes pas encore créées)**

```bash
cd /mnt/docker-volumes/alexis-dev-web/backend
php artisan test tests/Feature/ApiTest.php
```
Attendu : FAIL — "404 on /api/projects"

- [ ] **Step 4 : Controllers API**

`backend/app/Http/Controllers/Api/ProjectController.php` :
```php
<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Project;

class ProjectController extends Controller {
    public function index(): \Illuminate\Http\JsonResponse {
        $query = Project::where('active', true)->orderBy('sort_order');
        if (request('category') && request('category') !== 'Tous') {
            $query->where('category', request('category'));
        }
        return response()->json($query->get());
    }

    public function show(string $slug): \Illuminate\Http\JsonResponse {
        $project = Project::where('slug', $slug)->where('active', true)->firstOrFail();
        return response()->json($project);
    }
}
```

`backend/app/Http/Controllers/Api/ServiceController.php` :
```php
<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Service;

class ServiceController extends Controller {
    public function index(): \Illuminate\Http\JsonResponse {
        return response()->json(Service::where('active', true)->orderBy('sort_order')->get());
    }
}
```

`backend/app/Http/Controllers/Api/TestimonialController.php` :
```php
<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;

class TestimonialController extends Controller {
    public function index(): \Illuminate\Http\JsonResponse {
        return response()->json(Testimonial::where('active', true)->orderBy('sort_order')->get());
    }
}
```

`backend/app/Http/Controllers/Api/ContactController.php` (sans PHPMailer pour l'instant) :
```php
<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller {
    public function store(Request $request): \Illuminate\Http\JsonResponse {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email',
            'phone'      => 'nullable|string|max:20',
            'type'       => 'nullable|string',
            'budget'     => 'nullable|string',
            'message'    => 'nullable|string|max:2000',
        ]);

        Contact::create($data);
        return response()->json(['success' => true]);
    }
}
```

- [ ] **Step 5 : Routes API**

Remplacer `backend/routes/api.php` :
```php
<?php
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{slug}', [ProjectController::class, 'show']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/testimonials', [TestimonialController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store']);
```

- [ ] **Step 6 : Lancer les tests — doivent passer**

```bash
php artisan test tests/Feature/ApiTest.php
```
Attendu : 7 tests PASS.

- [ ] **Step 7 : Commit**

```bash
cd /mnt/docker-volumes/alexis-dev-web
git init && git add -A
git commit -m "feat: Laravel init, migrations, seeders, API routes"
```

---

### Task 4 : PHPMailer — notification contact

**Files:**
- Modify: `backend/composer.json`
- Create: `backend/app/Services/MailService.php`
- Modify: `backend/app/Http/Controllers/Api/ContactController.php`

- [ ] **Step 1 : Installer PHPMailer**

```bash
cd /mnt/docker-volumes/alexis-dev-web/backend
composer require phpmailer/phpmailer
```

- [ ] **Step 2 : Créer `MailService`**

`backend/app/Services/MailService.php` :
```php
<?php
namespace App\Services;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailService {
    public function sendContactNotification(array $contact): void {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = config('mail.mailers.smtp.host');
        $mail->SMTPAuth   = true;
        $mail->Username   = config('mail.mailers.smtp.username');
        $mail->Password   = config('mail.mailers.smtp.password');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = config('mail.mailers.smtp.port');
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(config('mail.from.address'), 'alexis dev web');
        $mail->addAddress(config('mail.from.address'));
        $mail->Subject = "Nouvelle demande de {$contact['first_name']} {$contact['last_name']}";
        $mail->isHTML(true);
        $mail->Body = "
            <h2>Nouvelle demande de contact</h2>
            <p><strong>Nom :</strong> {$contact['first_name']} {$contact['last_name']}</p>
            <p><strong>Email :</strong> {$contact['email']}</p>
            <p><strong>Téléphone :</strong> " . ($contact['phone'] ?? '—') . "</p>
            <p><strong>Type :</strong> {$contact['type']}</p>
            <p><strong>Budget :</strong> {$contact['budget']}</p>
            <p><strong>Message :</strong><br>" . nl2br(htmlspecialchars($contact['message'] ?? '')) . "</p>
        ";
        $mail->send();
    }
}
```

- [ ] **Step 3 : Mettre à jour `ContactController` pour envoyer l'email**

Modifier `backend/app/Http/Controllers/Api/ContactController.php` :
```php
<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Services\MailService;
use Illuminate\Http\Request;

class ContactController extends Controller {
    public function store(Request $request): \Illuminate\Http\JsonResponse {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email',
            'phone'      => 'nullable|string|max:20',
            'type'       => 'nullable|string',
            'budget'     => 'nullable|string',
            'message'    => 'nullable|string|max:2000',
        ]);

        $contact = Contact::create($data);

        try {
            (new MailService())->sendContactNotification($data);
        } catch (\Exception $e) {
            \Log::error('Mail contact failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}
```

- [ ] **Step 4 : Commit**

```bash
git add -A && git commit -m "feat: PHPMailer contact notification"
```

---

### Task 5 : Admin — Auth middleware + Login

**Files:**
- Create: `backend/app/Http/Middleware/AdminAuth.php`
- Create: `backend/app/Http/Controllers/Admin/AuthController.php`
- Create: `backend/resources/views/admin/layout.blade.php`
- Create: `backend/resources/views/admin/login.blade.php`
- Modify: `backend/routes/web.php`
- Modify: `backend/bootstrap/app.php`

- [ ] **Step 1 : Middleware `AdminAuth`**

`backend/app/Http/Middleware/AdminAuth.php` :
```php
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class AdminAuth {
    public function handle(Request $request, Closure $next): mixed {
        if (!session('admin_logged_in')) {
            return redirect('/admin/login');
        }
        return $next($request);
    }
}
```

- [ ] **Step 2 : Enregistrer le middleware dans `bootstrap/app.php`**

Dans `backend/bootstrap/app.php`, ajouter dans `->withMiddleware()` :
```php
->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware) {
    $middleware->alias([
        'admin.auth' => \App\Http\Middleware\AdminAuth::class,
    ]);
})
```

- [ ] **Step 3 : `AuthController`**

`backend/app/Http/Controllers/Admin/AuthController.php` :
```php
<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller {
    public function showLogin(): \Illuminate\View\View {
        return view('admin.login');
    }

    public function login(Request $request): \Illuminate\Http\RedirectResponse {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user && password_verify($request->password, $user->password)) {
            session(['admin_logged_in' => true, 'admin_name' => $user->name]);
            return redirect('/admin');
        }

        return back()->withErrors(['email' => 'Identifiants incorrects.']);
    }

    public function logout(): \Illuminate\Http\RedirectResponse {
        session()->forget(['admin_logged_in', 'admin_name']);
        return redirect('/admin/login');
    }
}
```

- [ ] **Step 4 : Layout admin `layout.blade.php`**

`backend/resources/views/admin/layout.blade.php` :
```html
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — alexis dev web</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #111111; color: #F9F9F9; font-family: system-ui, sans-serif; }
    .accent { color: #8B00FF; }
    .btn-purple { background: #8B00FF; color: #fff; padding: 6px 16px; border-radius: 6px; border: none; cursor: pointer; }
    .btn-purple:hover { background: #7000cc; }
    .card { background: #1A1A1A; border: 1px solid #2A2A2A; border-radius: 8px; padding: 20px; }
  </style>
</head>
<body>
  <nav style="background:#161616;border-bottom:1px solid #2A2A2A;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;">
    <span style="font-weight:700;font-size:18px;">alexis dev web <span class="accent">// admin</span></span>
    <div style="display:flex;gap:16px;align-items:center;">
      <a href="/admin" style="color:#888;text-decoration:none;">Dashboard</a>
      <a href="/admin/projects" style="color:#888;text-decoration:none;">Projets</a>
      <a href="/admin/services" style="color:#888;text-decoration:none;">Services</a>
      <a href="/admin/testimonials" style="color:#888;text-decoration:none;">Témoignages</a>
      <a href="/admin/contacts" style="color:#888;text-decoration:none;">Contacts</a>
      <form method="POST" action="/admin/logout" style="display:inline;">
        @csrf
        <button type="submit" style="background:none;border:none;color:#888;cursor:pointer;">Déconnexion</button>
      </form>
    </div>
  </nav>
  <main style="max-width:1100px;margin:40px auto;padding:0 24px;">
    @yield('content')
  </main>
</body>
</html>
```

- [ ] **Step 5 : Vue login `login.blade.php`**

`backend/resources/views/admin/login.blade.php` :
```html
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body { background:#111111; color:#F9F9F9; display:flex; align-items:center; justify-content:center; min-height:100vh; }</style>
</head>
<body>
  <div style="background:#1A1A1A;border:1px solid #2A2A2A;border-radius:12px;padding:40px;width:360px;">
    <h1 style="font-size:22px;font-weight:700;margin-bottom:24px;">alexis dev web <span style="color:#8B00FF;">// admin</span></h1>
    @if($errors->any())
      <p style="color:#ff6b6b;margin-bottom:16px;">{{ $errors->first() }}</p>
    @endif
    <form method="POST" action="/admin/login">
      @csrf
      <div style="margin-bottom:16px;">
        <label style="display:block;margin-bottom:6px;color:#888;font-size:13px;">Email</label>
        <input type="email" name="email" required style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;outline:none;">
      </div>
      <div style="margin-bottom:24px;">
        <label style="display:block;margin-bottom:6px;color:#888;font-size:13px;">Mot de passe</label>
        <input type="password" name="password" required style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;outline:none;">
      </div>
      <button type="submit" style="width:100%;background:#8B00FF;color:#fff;border:none;border-radius:6px;padding:12px;font-size:15px;cursor:pointer;">Se connecter</button>
    </form>
  </div>
</body>
</html>
```

- [ ] **Step 6 : Routes web.php**

`backend/routes/web.php` :
```php
<?php
use App\Http\Controllers\Admin\AuthController;
use Illuminate\Support\Facades\Route;

// Admin auth
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout']);

// Admin protégé
Route::middleware('admin.auth')->prefix('admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class)->except(['show']);
    Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class)->except(['show']);
    Route::resource('testimonials', \App\Http\Controllers\Admin\TestimonialController::class)->except(['show']);
    Route::get('contacts', [\App\Http\Controllers\Admin\ContactController::class, 'index']);
    Route::get('contacts/{contact}/pdf', [\App\Http\Controllers\Admin\ContactController::class, 'pdf']);
    Route::delete('contacts/{contact}', [\App\Http\Controllers\Admin\ContactController::class, 'destroy']);
});

// Catch-all SPA React — doit être EN DERNIER
Route::get('/{any}', function () {
    $file = public_path('build/index.html');
    if (!file_exists($file)) {
        return response('Frontend not built. Run: cd frontend && npm run build', 503);
    }
    return response()->file($file);
})->where('any', '^(?!api|admin).*$');
```

- [ ] **Step 7 : Commit**

```bash
git add -A && git commit -m "feat: admin auth middleware + login"
```

---

### Task 6 : Admin CRUD — Projets, Services, Témoignages

**Files:**
- Create: `backend/app/Http/Controllers/Admin/{DashboardController,ProjectController,ServiceController,TestimonialController}.php`
- Create: `backend/resources/views/admin/{dashboard,projects/index,projects/form,services/index,services/form,testimonials/index,testimonials/form}.blade.php`

- [ ] **Step 1 : DashboardController**

`backend/app/Http/Controllers/Admin/DashboardController.php` :
```php
<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Contact, Project, Service, Testimonial};

class DashboardController extends Controller {
    public function index(): \Illuminate\View\View {
        return view('admin.dashboard', [
            'contactsCount'     => Contact::count(),
            'projectsCount'     => Project::count(),
            'servicesCount'     => Service::count(),
            'testimonialsCount' => Testimonial::count(),
            'latestContacts'    => Contact::latest()->take(5)->get(),
        ]);
    }
}
```

- [ ] **Step 2 : Vue dashboard**

`backend/resources/views/admin/dashboard.blade.php` :
```html
@extends('admin.layout')
@section('content')
  <h1 style="font-size:24px;font-weight:700;margin-bottom:24px;">Dashboard</h1>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px;">
    @foreach([['Contacts', $contactsCount, '/admin/contacts'], ['Projets', $projectsCount, '/admin/projects'], ['Services', $servicesCount, '/admin/services'], ['Témoignages', $testimonialsCount, '/admin/testimonials']] as [$label, $count, $href])
    <a href="{{ $href }}" style="text-decoration:none;" class="card">
      <div style="font-size:32px;font-weight:700;color:#8B00FF;">{{ $count }}</div>
      <div style="color:#888;margin-top:4px;">{{ $label }}</div>
    </a>
    @endforeach
  </div>
  <div class="card">
    <h2 style="font-size:16px;font-weight:600;margin-bottom:16px;">Dernières demandes</h2>
    @forelse($latestContacts as $c)
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #2A2A2A;">
        <span>{{ $c->first_name }} {{ $c->last_name }} — {{ $c->email }}</span>
        <span style="color:#888;font-size:13px;">{{ $c->created_at->format('d/m/Y H:i') }}</span>
      </div>
    @empty
      <p style="color:#888;">Aucune demande pour l'instant.</p>
    @endforelse
  </div>
@endsection
```

- [ ] **Step 3 : Admin ProjectController**

`backend/app/Http/Controllers/Admin/ProjectController.php` :
```php
<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller {
    public function index(): \Illuminate\View\View {
        return view('admin.projects.index', ['projects' => Project::orderBy('sort_order')->get()]);
    }

    public function create(): \Illuminate\View\View {
        return view('admin.projects.form', ['project' => null]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse {
        $data = $this->validate($request);
        Project::create($data);
        return redirect('/admin/projects')->with('success', 'Projet créé.');
    }

    public function edit(Project $project): \Illuminate\View\View {
        return view('admin.projects.form', compact('project'));
    }

    public function update(Request $request, Project $project): \Illuminate\Http\RedirectResponse {
        $data = $this->validate($request);
        $project->update($data);
        return redirect('/admin/projects')->with('success', 'Projet mis à jour.');
    }

    public function destroy(Project $project): \Illuminate\Http\RedirectResponse {
        $project->delete();
        return redirect('/admin/projects')->with('success', 'Projet supprimé.');
    }

    private function validate(Request $request): array {
        return $request->validate([
            'slug'       => 'required|string|max:100',
            'name'       => 'required|string|max:200',
            'client'     => 'required|string|max:100',
            'category'   => 'required|in:Sites vitrines,Applications,Infra',
            'year'       => 'required|digits:4',
            'summary'    => 'required|string',
            'full_text'  => 'required|string',
            'tech'       => 'required|string',
            'rendered'   => 'required|string',
            'sort_order' => 'required|integer',
            'active'     => 'sometimes|boolean',
        ]) + ['active' => $request->has('active')];
    }
}
```

- [ ] **Step 4 : Vues projets**

`backend/resources/views/admin/projects/index.blade.php` :
```html
@extends('admin.layout')
@section('content')
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h1 style="font-size:22px;font-weight:700;">Projets</h1>
    <a href="/admin/projects/create" class="btn-purple" style="text-decoration:none;padding:8px 18px;border-radius:6px;background:#8B00FF;color:#fff;">+ Nouveau</a>
  </div>
  @if(session('success'))<div style="background:#1a3a1a;border:1px solid #2a6a2a;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#6aff6a;">{{ session('success') }}</div>@endif
  <div class="card">
    @foreach($projects as $p)
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #2A2A2A;">
      <div>
        <span style="font-weight:600;">{{ $p->name }}</span>
        <span style="color:#888;margin-left:12px;font-size:13px;">{{ $p->category }} · {{ $p->year }}</span>
        @if(!$p->active)<span style="color:#ff6b6b;font-size:12px;margin-left:8px;">[inactif]</span>@endif
      </div>
      <div style="display:flex;gap:12px;">
        <a href="/admin/projects/{{ $p->id }}/edit" style="color:#8B00FF;text-decoration:none;">Éditer</a>
        <form method="POST" action="/admin/projects/{{ $p->id }}" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
          @csrf @method('DELETE')
          <button type="submit" style="background:none;border:none;color:#ff6b6b;cursor:pointer;">Supprimer</button>
        </form>
      </div>
    </div>
    @endforeach
  </div>
@endsection
```

`backend/resources/views/admin/projects/form.blade.php` :
```html
@extends('admin.layout')
@section('content')
  <h1 style="font-size:22px;font-weight:700;margin-bottom:24px;">{{ $project ? 'Éditer' : 'Nouveau' }} projet</h1>
  <form method="POST" action="{{ $project ? '/admin/projects/'.$project->id : '/admin/projects' }}" class="card">
    @csrf
    @if($project) @method('PUT') @endif
    @if($errors->any())<div style="color:#ff6b6b;margin-bottom:16px;">{{ $errors->first() }}</div>@endif

    @foreach([['slug','Slug (URL)','text'],['name','Nom','text'],['client','Client','text'],['year','Année','text']] as [$field, $label, $type])
    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">{{ $label }}</label>
      <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field, $project?->{$field}) }}" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>
    @endforeach

    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Catégorie</label>
      <select name="category" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
        @foreach(['Sites vitrines','Applications','Infra'] as $cat)
          <option value="{{ $cat }}" {{ old('category', $project?->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
      </select>
    </div>

    @foreach([['summary','Résumé (une ligne)'],['full_text','Texte complet (un paragraphe par ligne)'],['tech','Technologies (séparées par virgule)'],['rendered','Livrables (séparés par virgule)']] as [$field, $label])
    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">{{ $label }}</label>
      @php
        $val = old($field, $project ? (is_array($project->{$field}) ? implode("\n", $project->{$field}) : $project->{$field}) : '');
      @endphp
      <textarea name="{{ $field }}" rows="4" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">{{ $val }}</textarea>
    </div>
    @endforeach

    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Ordre</label>
      <input type="number" name="sort_order" value="{{ old('sort_order', $project?->sort_order ?? 0) }}" style="width:80px;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>

    <label style="display:flex;align-items:center;gap:8px;margin-bottom:24px;cursor:pointer;">
      <input type="checkbox" name="active" value="1" {{ old('active', $project?->active ?? true) ? 'checked' : '' }}>
      <span style="color:#888;">Actif</span>
    </label>

    <div style="display:flex;gap:12px;">
      <button type="submit" class="btn-purple">Enregistrer</button>
      <a href="/admin/projects" style="color:#888;text-decoration:none;padding:6px 0;">Annuler</a>
    </div>
  </form>
@endsection
```

- [ ] **Step 5 : Admin ServiceController + vues**

`backend/app/Http/Controllers/Admin/ServiceController.php` :
```php
<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller {
    public function index(): \Illuminate\View\View {
        return view('admin.services.index', ['services' => Service::orderBy('sort_order')->get()]);
    }
    public function create(): \Illuminate\View\View {
        return view('admin.services.form', ['service' => null]);
    }
    public function store(Request $request): \Illuminate\Http\RedirectResponse {
        Service::create($this->validated($request));
        return redirect('/admin/services')->with('success', 'Service créé.');
    }
    public function edit(Service $service): \Illuminate\View\View {
        return view('admin.services.form', compact('service'));
    }
    public function update(Request $request, Service $service): \Illuminate\Http\RedirectResponse {
        $service->update($this->validated($request));
        return redirect('/admin/services')->with('success', 'Service mis à jour.');
    }
    public function destroy(Service $service): \Illuminate\Http\RedirectResponse {
        $service->delete();
        return redirect('/admin/services')->with('success', 'Service supprimé.');
    }
    private function validated(Request $request): array {
        $data = $request->validate([
            'slug'       => 'required|string|max:100',
            'label'      => 'required|string|max:50',
            'title'      => 'required|string|max:200',
            'sub'        => 'required|string|max:200',
            'body'       => 'required|string',
            'tags'       => 'required|string',
            'price'      => 'required|string|max:100',
            'sort_order' => 'required|integer',
        ]);
        $data['tags'] = array_map('trim', explode(',', $data['tags']));
        $data['active'] = $request->has('active');
        return $data;
    }
}
```

`backend/resources/views/admin/services/index.blade.php` :
```html
@extends('admin.layout')
@section('content')
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h1 style="font-size:22px;font-weight:700;">Services</h1>
    <a href="/admin/services/create" style="text-decoration:none;padding:8px 18px;border-radius:6px;background:#8B00FF;color:#fff;">+ Nouveau</a>
  </div>
  @if(session('success'))<div style="background:#1a3a1a;border:1px solid #2a6a2a;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#6aff6a;">{{ session('success') }}</div>@endif
  <div class="card">
    @foreach($services as $s)
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #2A2A2A;">
      <div>
        <span style="font-weight:600;">{{ $s->title }}</span>
        <span style="color:#8B00FF;font-family:monospace;margin-left:12px;font-size:13px;">{{ $s->label }}</span>
        <span style="color:#888;margin-left:8px;">{{ $s->price }}</span>
      </div>
      <div style="display:flex;gap:12px;">
        <a href="/admin/services/{{ $s->id }}/edit" style="color:#8B00FF;text-decoration:none;">Éditer</a>
        <form method="POST" action="/admin/services/{{ $s->id }}" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
          @csrf @method('DELETE')
          <button type="submit" style="background:none;border:none;color:#ff6b6b;cursor:pointer;">Supprimer</button>
        </form>
      </div>
    </div>
    @endforeach
  </div>
@endsection
```

`backend/resources/views/admin/services/form.blade.php` :
```html
@extends('admin.layout')
@section('content')
  <h1 style="font-size:22px;font-weight:700;margin-bottom:24px;">{{ $service ? 'Éditer' : 'Nouveau' }} service</h1>
  <form method="POST" action="{{ $service ? '/admin/services/'.$service->id : '/admin/services' }}" class="card">
    @csrf
    @if($service) @method('PUT') @endif
    @if($errors->any())<div style="color:#ff6b6b;margin-bottom:16px;">{{ $errors->first() }}</div>@endif

    @foreach([['slug','Slug'],['label','Label code (ex: // vitrine/)'],['title','Titre'],['sub','Sous-titre'],['price','Prix (ex: À partir de 800€)']] as [$f, $l])
    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">{{ $l }}</label>
      <input type="text" name="{{ $f }}" value="{{ old($f, $service?->{$f}) }}" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>
    @endforeach

    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Description</label>
      <textarea name="body" rows="3" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">{{ old('body', $service?->body) }}</textarea>
    </div>

    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Tags (séparés par virgule)</label>
      <input type="text" name="tags" value="{{ old('tags', $service ? implode(', ', $service->tags) : '') }}" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>

    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Ordre</label>
      <input type="number" name="sort_order" value="{{ old('sort_order', $service?->sort_order ?? 0) }}" style="width:80px;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>

    <label style="display:flex;align-items:center;gap:8px;margin-bottom:24px;cursor:pointer;">
      <input type="checkbox" name="active" value="1" {{ old('active', $service?->active ?? true) ? 'checked' : '' }}>
      <span style="color:#888;">Actif</span>
    </label>

    <div style="display:flex;gap:12px;">
      <button type="submit" class="btn-purple">Enregistrer</button>
      <a href="/admin/services" style="color:#888;text-decoration:none;padding:6px 0;">Annuler</a>
    </div>
  </form>
@endsection
```

- [ ] **Step 6 : Admin TestimonialController + vues**

`backend/app/Http/Controllers/Admin/TestimonialController.php` :
```php
<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller {
    public function index(): \Illuminate\View\View {
        return view('admin.testimonials.index', ['testimonials' => Testimonial::orderBy('sort_order')->get()]);
    }
    public function create(): \Illuminate\View\View {
        return view('admin.testimonials.form', ['testimonial' => null]);
    }
    public function store(Request $request): \Illuminate\Http\RedirectResponse {
        Testimonial::create($this->validated($request));
        return redirect('/admin/testimonials')->with('success', 'Témoignage créé.');
    }
    public function edit(Testimonial $testimonial): \Illuminate\View\View {
        return view('admin.testimonials.form', compact('testimonial'));
    }
    public function update(Request $request, Testimonial $testimonial): \Illuminate\Http\RedirectResponse {
        $testimonial->update($this->validated($request));
        return redirect('/admin/testimonials')->with('success', 'Témoignage mis à jour.');
    }
    public function destroy(Testimonial $testimonial): \Illuminate\Http\RedirectResponse {
        $testimonial->delete();
        return redirect('/admin/testimonials')->with('success', 'Témoignage supprimé.');
    }
    private function validated(Request $request): array {
        return $request->validate([
            'quote'      => 'required|string',
            'author'     => 'required|string|max:100',
            'role'       => 'required|string|max:150',
            'sort_order' => 'required|integer',
        ]) + ['active' => $request->has('active')];
    }
}
```

`backend/resources/views/admin/testimonials/index.blade.php` :
```html
@extends('admin.layout')
@section('content')
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h1 style="font-size:22px;font-weight:700;">Témoignages</h1>
    <a href="/admin/testimonials/create" style="text-decoration:none;padding:8px 18px;border-radius:6px;background:#8B00FF;color:#fff;">+ Nouveau</a>
  </div>
  <div class="card">
    @foreach($testimonials as $t)
    <div style="padding:12px 0;border-bottom:1px solid #2A2A2A;">
      <div style="display:flex;justify-content:space-between;">
        <span style="font-weight:600;">{{ $t->author }}</span>
        <div style="display:flex;gap:12px;">
          <a href="/admin/testimonials/{{ $t->id }}/edit" style="color:#8B00FF;text-decoration:none;">Éditer</a>
          <form method="POST" action="/admin/testimonials/{{ $t->id }}" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
            @csrf @method('DELETE')
            <button type="submit" style="background:none;border:none;color:#ff6b6b;cursor:pointer;">Supprimer</button>
          </form>
        </div>
      </div>
      <div style="color:#888;font-size:13px;margin-top:4px;">{{ $t->role }}</div>
      <div style="color:#aaa;margin-top:6px;font-style:italic;">"{{ Str::limit($t->quote, 100) }}"</div>
    </div>
    @endforeach
  </div>
@endsection
```

`backend/resources/views/admin/testimonials/form.blade.php` :
```html
@extends('admin.layout')
@section('content')
  <h1 style="font-size:22px;font-weight:700;margin-bottom:24px;">{{ $testimonial ? 'Éditer' : 'Nouveau' }} témoignage</h1>
  <form method="POST" action="{{ $testimonial ? '/admin/testimonials/'.$testimonial->id : '/admin/testimonials' }}" class="card">
    @csrf
    @if($testimonial) @method('PUT') @endif
    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Citation</label>
      <textarea name="quote" rows="4" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">{{ old('quote', $testimonial?->quote) }}</textarea>
    </div>
    @foreach([['author','Auteur'],['role','Rôle (ex: Gérante, Boulangerie Martin)']] as [$f, $l])
    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">{{ $l }}</label>
      <input type="text" name="{{ $f }}" value="{{ old($f, $testimonial?->{$f}) }}" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>
    @endforeach
    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Ordre</label>
      <input type="number" name="sort_order" value="{{ old('sort_order', $testimonial?->sort_order ?? 0) }}" style="width:80px;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>
    <label style="display:flex;align-items:center;gap:8px;margin-bottom:24px;cursor:pointer;">
      <input type="checkbox" name="active" value="1" {{ old('active', $testimonial?->active ?? true) ? 'checked' : '' }}>
      <span style="color:#888;">Actif</span>
    </label>
    <div style="display:flex;gap:12px;">
      <button type="submit" class="btn-purple">Enregistrer</button>
      <a href="/admin/testimonials" style="color:#888;text-decoration:none;padding:6px 0;">Annuler</a>
    </div>
  </form>
@endsection
```

- [ ] **Step 7 : Commit**

```bash
git add -A && git commit -m "feat: admin CRUD projets, services, témoignages"
```

---

### Task 7 : Admin Contacts + DomPDF

**Files:**
- Create: `backend/app/Http/Controllers/Admin/ContactController.php`
- Create: `backend/app/Services/PdfService.php`
- Create: `backend/resources/views/admin/contacts/index.blade.php`
- Create: `backend/resources/views/pdf/devis.blade.php`

- [ ] **Step 1 : Installer DomPDF**

```bash
cd /mnt/docker-volumes/alexis-dev-web/backend
composer require barryvdh/laravel-dompdf
```

- [ ] **Step 2 : `PdfService`**

`backend/app/Services/PdfService.php` :
```php
<?php
namespace App\Services;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService {
    public function generateDevis(\App\Models\Contact $contact): \Illuminate\Http\Response {
        $pdf = Pdf::loadView('pdf.devis', ['contact' => $contact]);
        $filename = 'devis-' . $contact->id . '-' . str($contact->last_name)->slug() . '.pdf';
        return $pdf->download($filename);
    }
}
```

- [ ] **Step 3 : Admin ContactController**

`backend/app/Http/Controllers/Admin/ContactController.php` :
```php
<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Services\PdfService;

class ContactController extends Controller {
    public function index(): \Illuminate\View\View {
        $contacts = Contact::latest()->paginate(20);
        return view('admin.contacts.index', compact('contacts'));
    }

    public function pdf(Contact $contact): \Illuminate\Http\Response {
        return (new PdfService())->generateDevis($contact);
    }

    public function destroy(Contact $contact): \Illuminate\Http\RedirectResponse {
        $contact->delete();
        return redirect('/admin/contacts')->with('success', 'Demande supprimée.');
    }
}
```

- [ ] **Step 4 : Vue contacts index**

`backend/resources/views/admin/contacts/index.blade.php` :
```html
@extends('admin.layout')
@section('content')
  <h1 style="font-size:22px;font-weight:700;margin-bottom:24px;">Demandes de contact</h1>
  @if(session('success'))<div style="background:#1a3a1a;border:1px solid #2a6a2a;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#6aff6a;">{{ session('success') }}</div>@endif
  <div class="card">
    @forelse($contacts as $c)
    <div style="padding:16px 0;border-bottom:1px solid #2A2A2A;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
          <span style="font-weight:600;">{{ $c->first_name }} {{ $c->last_name }}</span>
          <span style="color:#888;margin-left:12px;">{{ $c->email }}</span>
          @if($c->phone)<span style="color:#888;margin-left:8px;">· {{ $c->phone }}</span>@endif
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
          <span style="color:#888;font-size:13px;">{{ $c->created_at->format('d/m/Y H:i') }}</span>
          <a href="/admin/contacts/{{ $c->id }}/pdf" style="color:#8B00FF;text-decoration:none;font-size:13px;">PDF</a>
          <form method="POST" action="/admin/contacts/{{ $c->id }}" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
            @csrf @method('DELETE')
            <button type="submit" style="background:none;border:none;color:#ff6b6b;cursor:pointer;font-size:13px;">Supprimer</button>
          </form>
        </div>
      </div>
      <div style="margin-top:6px;display:flex;gap:16px;">
        <span style="background:#1a1a2e;border:1px solid #2A2A2A;border-radius:4px;padding:2px 8px;font-size:12px;color:#8B00FF;">{{ $c->type }}</span>
        <span style="color:#888;font-size:13px;">Budget : {{ $c->budget }}</span>
      </div>
      @if($c->message)
      <p style="color:#aaa;font-size:13px;margin-top:8px;">{{ Str::limit($c->message, 150) }}</p>
      @endif
    </div>
    @empty
    <p style="color:#888;padding:16px 0;">Aucune demande pour l'instant.</p>
    @endforelse
  </div>
  <div style="margin-top:16px;">{{ $contacts->links() }}</div>
@endsection
```

- [ ] **Step 5 : Vue PDF devis**

`backend/resources/views/pdf/devis.blade.php` :
```html
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; color: #1a1a1a; font-size: 13px; margin: 0; padding: 0; }
    .header { background: #111111; color: #F9F9F9; padding: 30px 40px; }
    .header h1 { font-size: 28px; margin: 0 0 4px; }
    .header span { color: #8B00FF; }
    .content { padding: 40px; }
    .section { margin-bottom: 28px; }
    .label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
    .value { font-size: 15px; font-weight: 600; }
    .grid { display: flex; gap: 40px; flex-wrap: wrap; }
    .grid-item { flex: 1; min-width: 180px; }
    .divider { border: none; border-top: 1px solid #eee; margin: 24px 0; }
    .message-box { background: #f9f9f9; border-left: 3px solid #8B00FF; padding: 16px; border-radius: 4px; }
    .footer { margin-top: 48px; padding-top: 16px; border-top: 1px solid #eee; color: #888; font-size: 11px; }
  </style>
</head>
<body>
  <div class="header">
    <h1>alexis dev web<span>.</span></h1>
    <div style="color: #888; font-size: 12px; margin-top: 4px;">Récapitulatif de demande</div>
  </div>

  <div class="content">
    <div class="section">
      <div style="display:flex; justify-content:space-between; align-items:flex-start;">
        <div>
          <div class="label">Référence</div>
          <div class="value">#{{ str_pad($contact->id, 4, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div style="text-align:right;">
          <div class="label">Date de la demande</div>
          <div class="value">{{ $contact->created_at->format('d/m/Y') }}</div>
        </div>
      </div>
    </div>

    <hr class="divider">

    <div class="section">
      <div class="label" style="margin-bottom: 12px;">Informations client</div>
      <div class="grid">
        <div class="grid-item">
          <div class="label">Nom</div>
          <div class="value">{{ $contact->first_name }} {{ $contact->last_name }}</div>
        </div>
        <div class="grid-item">
          <div class="label">Email</div>
          <div class="value">{{ $contact->email }}</div>
        </div>
        @if($contact->phone)
        <div class="grid-item">
          <div class="label">Téléphone</div>
          <div class="value">{{ $contact->phone }}</div>
        </div>
        @endif
      </div>
    </div>

    <hr class="divider">

    <div class="section">
      <div class="label" style="margin-bottom: 12px;">Détails du projet</div>
      <div class="grid">
        <div class="grid-item">
          <div class="label">Type de prestation</div>
          <div class="value">{{ $contact->type }}</div>
        </div>
        <div class="grid-item">
          <div class="label">Budget estimé</div>
          <div class="value" style="color: #8B00FF;">{{ $contact->budget }}</div>
        </div>
      </div>
    </div>

    @if($contact->message)
    <hr class="divider">
    <div class="section">
      <div class="label" style="margin-bottom: 12px;">Message</div>
      <div class="message-box">{{ $contact->message }}</div>
    </div>
    @endif

    <div class="footer">
      <p>alexis dev web · contact.alex2.dev@gmail.com · alexis-rodrigues.fr</p>
      <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
  </div>
</body>
</html>
```

- [ ] **Step 6 : Commit**

```bash
git add -A && git commit -m "feat: admin contacts + PDF devis DomPDF"
```

---

### Task 8 : Frontend React scaffold

**Files:**
- Create: `frontend/` (projet Vite)
- Create: `frontend/vite.config.ts`
- Create: `frontend/tailwind.config.ts`
- Create: `frontend/src/index.css`

- [ ] **Step 1 : Créer le projet Vite**

```bash
cd /mnt/docker-volumes/alexis-dev-web
npm create vite@latest frontend -- --template react-ts
cd frontend && npm install
```

- [ ] **Step 2 : Installer les dépendances**

```bash
cd /mnt/docker-volumes/alexis-dev-web/frontend
npm install react-router-dom
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

- [ ] **Step 3 : Configurer `vite.config.ts`**

```ts
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  base: '/build/',
  build: {
    outDir: '../backend/public/build',
    emptyOutDir: true,
  },
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
})
```

- [ ] **Step 4 : Configurer `tailwind.config.ts`**

```ts
import type { Config } from 'tailwindcss'

export default {
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        bg: '#111111',
        surface: '#161616',
        card: '#1A1A1A',
        border: '#2A2A2A',
        accent: '#8B00FF',
        'accent-dark': '#3D0080',
        text: '#F9F9F9',
        muted: '#888888',
        indigo: '#6366F1',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['"Space Grotesk"', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'monospace'],
      },
    },
  },
  plugins: [],
} satisfies Config
```

- [ ] **Step 5 : `src/index.css`**

```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400&display=swap');
@tailwind base;
@tailwind components;
@tailwind utilities;

* { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body { background: #111111; color: #F9F9F9; font-family: 'Inter', system-ui, sans-serif; }
```

- [ ] **Step 6 : `index.html`**

Remplacer `frontend/index.html` :
```html
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>alexis dev web — Agence web sur-mesure</title>
    <meta name="description" content="Développement web sur-mesure : sites vitrines, applications, APIs, hébergement. Code propre, livraison fiable." />
  </head>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.tsx"></script>
  </body>
</html>
```

- [ ] **Step 7 : Vérifier que le build fonctionne**

```bash
cd /mnt/docker-volumes/alexis-dev-web/frontend
npm run build
```
Attendu : pas d'erreur, `backend/public/build/` créé avec `index.html` + `assets/`.

- [ ] **Step 8 : Commit**

```bash
cd /mnt/docker-volumes/alexis-dev-web
git add -A && git commit -m "feat: Vite+React+Tailwind scaffold"
```

---

### Task 9 : Types + useApi hook + App.tsx

**Files:**
- Create: `frontend/src/types/index.ts`
- Create: `frontend/src/hooks/useApi.ts`
- Create: `frontend/src/App.tsx`
- Modify: `frontend/src/main.tsx`

- [ ] **Step 1 : Types**

`frontend/src/types/index.ts` :
```ts
export interface Project {
  id: number
  slug: string
  name: string
  client: string
  category: string
  year: string
  summary: string
  full_text: string[]
  tech: string[]
  rendered: string[]
}

export interface Service {
  id: number
  slug: string
  label: string
  title: string
  sub: string
  body: string
  tags: string[]
  price: string
}

export interface Testimonial {
  id: number
  quote: string
  author: string
  role: string
}

export interface ContactForm {
  first_name: string
  last_name: string
  email: string
  phone: string
  type: string
  budget: string
  message: string
}
```

- [ ] **Step 2 : `useApi` hook**

`frontend/src/hooks/useApi.ts` :
```ts
import { useState, useEffect } from 'react'

export function useApi<T>(url: string): { data: T | null; loading: boolean; error: string | null } {
  const [data, setData] = useState<T | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    setLoading(true)
    fetch(url)
      .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`)
        return r.json()
      })
      .then(setData)
      .catch(e => setError(e.message))
      .finally(() => setLoading(false))
  }, [url])

  return { data, loading, error }
}

export async function postContact(data: Record<string, string>): Promise<{ success: boolean }> {
  const r = await fetch('/api/contact', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify(data),
  })
  if (!r.ok) {
    const err = await r.json()
    throw new Error(err.message || 'Erreur serveur')
  }
  return r.json()
}
```

- [ ] **Step 3 : `App.tsx`**

`frontend/src/App.tsx` :
```tsx
import { BrowserRouter, Routes, Route } from 'react-router-dom'
import Navbar from './components/Navbar'
import Footer from './components/Footer'
import Home from './pages/Home'
import Services from './pages/Services'
import Projects from './pages/Projects'
import ProjectDetail from './pages/ProjectDetail'
import Agency from './pages/Agency'
import Contact from './pages/Contact'

export default function App() {
  return (
    <BrowserRouter>
      <Navbar />
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/services" element={<Services />} />
        <Route path="/realisations" element={<Projects />} />
        <Route path="/realisations/:slug" element={<ProjectDetail />} />
        <Route path="/agence" element={<Agency />} />
        <Route path="/contact" element={<Contact />} />
      </Routes>
      <Footer />
    </BrowserRouter>
  )
}
```

- [ ] **Step 4 : `main.tsx`**

```tsx
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import App from './App'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
```

- [ ] **Step 5 : Commit**

```bash
git add -A && git commit -m "feat: types, useApi hook, App router"
```

---

### Task 10 : Navbar + Footer

**Files:**
- Create: `frontend/src/components/Navbar.tsx`
- Create: `frontend/src/components/Footer.tsx`

- [ ] **Step 1 : `Navbar.tsx`**

```tsx
import { useState, useEffect } from 'react'
import { NavLink } from 'react-router-dom'

const links = [
  { to: '/', label: 'Accueil', exact: true },
  { to: '/services', label: 'Services' },
  { to: '/realisations', label: 'Réalisations' },
  { to: '/agence', label: 'Agence' },
  { to: '/contact', label: 'Contact' },
]

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false)
  const [open, setOpen] = useState(false)

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 80)
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  return (
    <nav
      className="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
      style={{
        background: scrolled ? 'rgba(13,13,13,0.85)' : '#111111',
        backdropFilter: scrolled ? 'blur(10px)' : 'none',
        borderBottom: '1px solid #2A2A2A',
      }}
    >
      <div className="max-w-6xl mx-auto px-6 flex items-center justify-between h-16">
        <NavLink to="/" className="font-display font-bold text-xl text-text no-underline">
          alexis dev web<span className="text-accent">.</span>
        </NavLink>

        {/* Desktop */}
        <div className="hidden md:flex gap-8">
          {links.map(l => (
            <NavLink
              key={l.to}
              to={l.to}
              end={l.exact}
              className={({ isActive }) =>
                `text-sm no-underline transition-colors ${isActive ? 'text-text' : 'text-muted hover:text-text'}`
              }
            >
              {l.label}
            </NavLink>
          ))}
        </div>

        {/* Mobile toggle */}
        <button
          className="md:hidden text-muted"
          onClick={() => setOpen(o => !o)}
          aria-label="Menu"
        >
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            {open
              ? <><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></>
              : <><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></>
            }
          </svg>
        </button>
      </div>

      {/* Mobile menu */}
      {open && (
        <div className="md:hidden border-t border-border px-6 py-4 flex flex-col gap-4">
          {links.map(l => (
            <NavLink
              key={l.to}
              to={l.to}
              end={l.exact}
              onClick={() => setOpen(false)}
              className={({ isActive }) =>
                `text-sm no-underline ${isActive ? 'text-text' : 'text-muted'}`
              }
            >
              {l.label}
            </NavLink>
          ))}
        </div>
      )}
    </nav>
  )
}
```

- [ ] **Step 2 : `Footer.tsx`**

```tsx
import { NavLink } from 'react-router-dom'

export default function Footer() {
  return (
    <footer className="border-t border-border mt-24">
      <div className="max-w-6xl mx-auto px-6 py-12 flex flex-col md:flex-row justify-between gap-8">
        <div>
          <div className="font-display font-bold text-lg mb-2">
            alexis dev web<span className="text-accent">.</span>
          </div>
          <p className="text-muted text-sm">Des sites qui travaillent pour vous.</p>
        </div>
        <div className="flex gap-12">
          <div>
            <div className="text-xs text-muted uppercase tracking-widest mb-3">Navigation</div>
            {[['/', 'Accueil'], ['/services', 'Services'], ['/realisations', 'Réalisations'], ['/agence', 'Agence'], ['/contact', 'Contact']].map(([to, label]) => (
              <NavLink key={to} to={to} className="block text-sm text-muted hover:text-text no-underline mb-2 transition-colors">
                {label}
              </NavLink>
            ))}
          </div>
          <div>
            <div className="text-xs text-muted uppercase tracking-widest mb-3">Contact</div>
            <a href="mailto:contact.alex2.dev@gmail.com" className="block text-sm text-muted hover:text-text no-underline mb-2 transition-colors">
              contact.alex2.dev@gmail.com
            </a>
            <NavLink to="/contact" className="block text-sm text-accent hover:text-text no-underline mb-2 transition-colors">
              Prendre RDV →
            </NavLink>
          </div>
        </div>
      </div>
      <div className="border-t border-border text-center py-4 text-xs text-muted">
        © {new Date().getFullYear()} alexis dev web — Tous droits réservés
      </div>
    </footer>
  )
}
```

- [ ] **Step 3 : Commit**

```bash
git add -A && git commit -m "feat: Navbar + Footer components"
```

---

### Task 11 : Pages Home + Services

**Files:**
- Create: `frontend/src/pages/Home.tsx`
- Create: `frontend/src/pages/Services.tsx`

- [ ] **Step 1 : `Home.tsx`**

```tsx
import { useNavigate } from 'react-router-dom'
import { useApi } from '../hooks/useApi'
import type { Project, Service, Testimonial } from '../types'

const metrics = [
  { value: '12+', label: 'Projets livrés' },
  { value: '3 ans', label: "D'expérience" },
  { value: '100%', label: 'Code sur-mesure' },
  { value: '< 24h', label: 'Délai de réponse' },
]

export default function Home() {
  const navigate = useNavigate()
  const { data: projects } = useApi<Project[]>('/api/projects')
  const { data: services } = useApi<Service[]>('/api/services')
  const { data: testimonials } = useApi<Testimonial[]>('/api/testimonials')

  return (
    <main className="pt-16">
      {/* Hero */}
      <section className="min-h-screen flex flex-col justify-center px-6 max-w-6xl mx-auto">
        <div className="border-l-2 border-accent pl-6 mb-8">
          <span className="font-mono text-accent text-sm">// agence web sur-mesure</span>
        </div>
        <h1 className="font-display font-bold text-5xl md:text-7xl leading-tight mb-6">
          Des sites qui<br />
          <span className="text-accent">travaillent</span><br />
          pour vous.
        </h1>
        <p className="text-muted text-xl max-w-xl mb-10">
          Développement web sur-mesure : sites vitrines, applications métier, APIs. Code propre, architecture solide, performances optimisées.
        </p>
        <div className="flex gap-4 flex-wrap">
          <button onClick={() => navigate('/contact')} className="bg-accent text-white px-8 py-3 rounded font-medium hover:bg-accent-dark transition-colors">
            Démarrer un projet
          </button>
          <button onClick={() => navigate('/realisations')} className="border border-border text-text px-8 py-3 rounded font-medium hover:border-accent transition-colors">
            Voir les réalisations
          </button>
        </div>
      </section>

      {/* Métriques */}
      <section className="border-y border-border py-12 px-6">
        <div className="max-w-6xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8">
          {metrics.map(m => (
            <div key={m.label} className="text-center">
              <div className="font-display font-bold text-3xl text-accent">{m.value}</div>
              <div className="text-muted text-sm mt-1">{m.label}</div>
            </div>
          ))}
        </div>
      </section>

      {/* Aperçu services */}
      <section className="py-24 px-6 max-w-6xl mx-auto">
        <div className="flex items-end justify-between mb-12">
          <h2 className="font-display font-bold text-3xl">Ce qu'on fait</h2>
          <button onClick={() => navigate('/services')} className="text-accent text-sm hover:text-text transition-colors">
            Tous les services →
          </button>
        </div>
        <div className="grid md:grid-cols-3 gap-6">
          {(services ?? []).slice(0, 3).map(s => (
            <div key={s.id} className="bg-card border border-border rounded-lg p-6">
              <div className="font-mono text-accent text-sm mb-3">{s.label}</div>
              <h3 className="font-display font-semibold text-lg mb-2">{s.title}</h3>
              <p className="text-muted text-sm leading-relaxed mb-4">{s.body}</p>
              <div className="flex flex-wrap gap-2">
                {s.tags.map(t => (
                  <span key={t} className="text-xs border border-border rounded px-2 py-1 text-muted">{t}</span>
                ))}
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* Réalisations preview */}
      <section className="py-24 px-6 max-w-6xl mx-auto border-t border-border">
        <div className="flex items-end justify-between mb-12">
          <h2 className="font-display font-bold text-3xl">Réalisations récentes</h2>
          <button onClick={() => navigate('/realisations')} className="text-accent text-sm hover:text-text transition-colors">
            Voir tout →
          </button>
        </div>
        <div className="grid md:grid-cols-3 gap-6">
          {(projects ?? []).slice(0, 3).map(p => (
            <button
              key={p.id}
              onClick={() => navigate(`/realisations/${p.slug}`)}
              className="bg-card border border-border rounded-lg p-6 text-left hover:border-accent transition-colors w-full"
            >
              <div className="flex justify-between items-start mb-4">
                <span className="text-xs border border-border rounded px-2 py-1 text-muted">{p.category}</span>
                <span className="text-muted text-xs">{p.year}</span>
              </div>
              <h3 className="font-display font-semibold text-lg mb-2">{p.name}</h3>
              <p className="text-muted text-sm leading-relaxed mb-4">{p.summary}</p>
              <div className="flex flex-wrap gap-2">
                {p.tech.map(t => (
                  <span key={t} className="font-mono text-xs text-accent">{t}</span>
                ))}
              </div>
            </button>
          ))}
        </div>
      </section>

      {/* Témoignages */}
      <section className="py-24 px-6 max-w-6xl mx-auto border-t border-border">
        <h2 className="font-display font-bold text-3xl mb-12">Ce qu'ils disent</h2>
        <div className="grid md:grid-cols-2 gap-6">
          {(testimonials ?? []).map(t => (
            <div key={t.id} className="bg-card border border-border rounded-lg p-8">
              <p className="text-text leading-relaxed mb-6 italic">"{t.quote}"</p>
              <div>
                <div className="font-semibold">{t.author}</div>
                <div className="text-muted text-sm">{t.role}</div>
              </div>
            </div>
          ))}
        </div>
      </section>
    </main>
  )
}
```

- [ ] **Step 2 : `Services.tsx`**

```tsx
import { useNavigate } from 'react-router-dom'
import { useApi } from '../hooks/useApi'
import type { Service } from '../types'

const steps = [
  { num: '01', title: 'Analyse', desc: 'Compréhension de vos besoins, contraintes et objectifs.' },
  { num: '02', title: 'Conception', desc: 'Maquettes et architecture validées avec vous avant le dev.' },
  { num: '03', title: 'Développement', desc: 'Code livré en itérations courtes avec points réguliers.' },
  { num: '04', title: 'Livraison', desc: 'Mise en ligne, formation et support inclus.' },
]

export default function Services() {
  const navigate = useNavigate()
  const { data: services, loading } = useApi<Service[]>('/api/services')

  return (
    <main className="pt-24 pb-24 px-6 max-w-6xl mx-auto">
      <div className="mb-16">
        <span className="font-mono text-accent text-sm">// services/</span>
        <h1 className="font-display font-bold text-4xl md:text-5xl mt-4 mb-4">Ce qu'on construit</h1>
        <p className="text-muted text-xl max-w-xl">Du site vitrine à l'application métier complexe, chaque projet est développé sur-mesure.</p>
      </div>

      {loading ? (
        <div className="text-muted">Chargement...</div>
      ) : (
        <div className="grid md:grid-cols-2 gap-6 mb-24">
          {(services ?? []).map(s => (
            <div key={s.id} className="bg-card border border-border rounded-lg p-8 hover:border-accent transition-colors">
              <div className="font-mono text-accent text-sm mb-3">{s.label}</div>
              <h2 className="font-display font-semibold text-xl mb-1">{s.title}</h2>
              <p className="text-muted text-sm mb-4">{s.sub}</p>
              <p className="text-text leading-relaxed mb-6">{s.body}</p>
              <div className="flex flex-wrap gap-2 mb-6">
                {s.tags.map(t => (
                  <span key={t} className="text-xs border border-border rounded px-2 py-1 text-muted">{t}</span>
                ))}
              </div>
              <div className="font-display font-bold text-accent">{s.price}</div>
            </div>
          ))}
        </div>
      )}

      {/* Étapes */}
      <section className="border-t border-border pt-16">
        <h2 className="font-display font-bold text-3xl mb-12">Comment on travaille</h2>
        <div className="grid md:grid-cols-4 gap-8">
          {steps.map(s => (
            <div key={s.num}>
              <div className="font-display font-bold text-4xl text-accent mb-4">{s.num}</div>
              <h3 className="font-semibold text-lg mb-2">{s.title}</h3>
              <p className="text-muted text-sm leading-relaxed">{s.desc}</p>
            </div>
          ))}
        </div>
      </section>

      <div className="mt-16 text-center">
        <button onClick={() => navigate('/contact')} className="bg-accent text-white px-8 py-3 rounded font-medium hover:bg-accent-dark transition-colors">
          Discutons de votre projet
        </button>
      </div>
    </main>
  )
}
```

- [ ] **Step 3 : Commit**

```bash
git add -A && git commit -m "feat: pages Home et Services"
```

---

### Task 12 : Pages Réalisations + Détail projet

**Files:**
- Create: `frontend/src/pages/Projects.tsx`
- Create: `frontend/src/pages/ProjectDetail.tsx`

- [ ] **Step 1 : `Projects.tsx`**

```tsx
import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useApi } from '../hooks/useApi'
import type { Project } from '../types'

const filters = ['Tous', 'Sites vitrines', 'Applications', 'Infra']

export default function Projects() {
  const navigate = useNavigate()
  const { data: projects, loading } = useApi<Project[]>('/api/projects')
  const [active, setActive] = useState('Tous')

  const filtered = (projects ?? []).filter(p => active === 'Tous' || p.category === active)

  return (
    <main className="pt-24 pb-24 px-6 max-w-6xl mx-auto">
      <div className="mb-12">
        <span className="font-mono text-accent text-sm">// réalisations/</span>
        <h1 className="font-display font-bold text-4xl md:text-5xl mt-4 mb-4">Nos projets</h1>
        <p className="text-muted text-xl">Code propre, clients satisfaits.</p>
      </div>

      {/* Filtres */}
      <div className="flex gap-3 flex-wrap mb-10">
        {filters.map(f => (
          <button
            key={f}
            onClick={() => setActive(f)}
            className="px-4 py-1.5 rounded border text-sm transition-colors"
            style={{
              background: active === f ? '#8B00FF' : 'transparent',
              color: active === f ? '#fff' : '#888',
              borderColor: active === f ? '#8B00FF' : '#2A2A2A',
            }}
          >
            {f}
          </button>
        ))}
      </div>

      {loading ? (
        <div className="text-muted">Chargement...</div>
      ) : (
        <div className="grid md:grid-cols-2 gap-6">
          {filtered.map(p => (
            <button
              key={p.id}
              onClick={() => navigate(`/realisations/${p.slug}`)}
              className="bg-card border border-border rounded-lg p-8 text-left hover:border-accent transition-colors w-full"
            >
              <div className="flex justify-between items-start mb-6">
                <span className="text-xs border border-border rounded px-2 py-1 text-muted">{p.category}</span>
                <span className="text-muted text-xs">{p.year}</span>
              </div>
              <h2 className="font-display font-semibold text-xl mb-1">{p.name}</h2>
              <p className="text-muted text-sm mb-6 leading-relaxed">{p.summary}</p>
              <div className="flex flex-wrap gap-2">
                {p.tech.map(t => (
                  <span key={t} className="font-mono text-xs text-accent border border-accent/30 rounded px-2 py-0.5">{t}</span>
                ))}
              </div>
            </button>
          ))}
        </div>
      )}
    </main>
  )
}
```

- [ ] **Step 2 : `ProjectDetail.tsx`**

```tsx
import { useParams, useNavigate } from 'react-router-dom'
import { useApi } from '../hooks/useApi'
import type { Project } from '../types'

export default function ProjectDetail() {
  const { slug } = useParams<{ slug: string }>()
  const navigate = useNavigate()
  const { data: project, loading, error } = useApi<Project>(`/api/projects/${slug}`)

  if (loading) return <main className="pt-32 px-6 max-w-6xl mx-auto text-muted">Chargement...</main>
  if (error || !project) return (
    <main className="pt-32 px-6 max-w-6xl mx-auto">
      <p className="text-muted">Projet introuvable.</p>
      <button onClick={() => navigate('/realisations')} className="mt-4 text-accent">← Retour</button>
    </main>
  )

  return (
    <main className="pt-24 pb-24 px-6 max-w-4xl mx-auto">
      <button onClick={() => navigate('/realisations')} className="text-muted text-sm mb-8 flex items-center gap-2 hover:text-text transition-colors">
        ← Toutes les réalisations
      </button>

      <div className="flex gap-3 items-center mb-6">
        <span className="text-xs border border-border rounded px-2 py-1 text-muted">{project.category}</span>
        <span className="text-muted text-xs">{project.year}</span>
        <span className="text-muted text-xs">· {project.client}</span>
      </div>

      <h1 className="font-display font-bold text-4xl md:text-5xl mb-4">{project.name}</h1>
      <p className="text-muted text-xl leading-relaxed mb-12">{project.summary}</p>

      {/* Texte complet */}
      <div className="space-y-6 mb-12">
        {project.full_text.map((para, i) => (
          <p key={i} className="text-text leading-relaxed">{para}</p>
        ))}
      </div>

      {/* Tech + livrables */}
      <div className="grid md:grid-cols-2 gap-8 border-t border-border pt-10">
        <div>
          <div className="font-mono text-accent text-sm mb-3">// technologies/</div>
          <div className="flex flex-wrap gap-2">
            {project.tech.map(t => (
              <span key={t} className="font-mono text-sm border border-accent/30 text-accent rounded px-3 py-1">{t}</span>
            ))}
          </div>
        </div>
        <div>
          <div className="font-mono text-accent text-sm mb-3">// livrables/</div>
          <ul className="space-y-2">
            {project.rendered.map(r => (
              <li key={r} className="flex items-center gap-2 text-text text-sm">
                <span className="text-accent">✓</span> {r}
              </li>
            ))}
          </ul>
        </div>
      </div>

      <div className="mt-16 text-center">
        <button onClick={() => navigate('/contact')} className="bg-accent text-white px-8 py-3 rounded font-medium hover:bg-accent-dark transition-colors">
          Démarrer un projet similaire
        </button>
      </div>
    </main>
  )
}
```

- [ ] **Step 3 : Commit**

```bash
git add -A && git commit -m "feat: pages Réalisations et détail projet"
```

---

### Task 13 : Pages Agence + Contact

**Files:**
- Create: `frontend/src/pages/Agency.tsx`
- Create: `frontend/src/pages/Contact.tsx`

- [ ] **Step 1 : `Agency.tsx`**

```tsx
import { useApi } from '../hooks/useApi'
import type { Testimonial } from '../types'

const philosophies = [
  { title: "Code d'abord", body: "Un site est avant tout un logiciel. Nous privilégions un code propre, durable et performant plutôt que les effets de surface." },
  { title: "Transparence totale", body: "Devis détaillés, points réguliers, code qui vous appartient. Aucune boîte noire, aucune dépendance imposée." },
  { title: "Partenariat long terme", body: "Nous ne livrons pas pour disparaître. Maintenance, évolutions et support : nous restons disponibles dans la durée." },
]
const techGroups = [
  { name: 'Frontend', items: ['React', 'Tailwind', 'TypeScript', 'Vite'] },
  { name: 'Backend', items: ['PHP', 'Laravel', 'MySQL', 'PostgreSQL'] },
  { name: 'Infra', items: ['Docker', 'Linux', 'Nginx', 'Cloudflare'] },
  { name: 'Outils', items: ['Git', 'Figma', 'Postman', 'CI/CD'] },
]
const stats = [
  { value: '12+', label: 'Projets livrés' },
  { value: '3 ans', label: "D'expérience" },
  { value: '100%', label: 'Réalisé en interne' },
]

export default function Agency() {
  const { data: testimonials } = useApi<Testimonial[]>('/api/testimonials')

  return (
    <main className="pt-24 pb-24 px-6 max-w-6xl mx-auto">
      <div className="mb-16">
        <span className="font-mono text-accent text-sm">// agence/</span>
        <h1 className="font-display font-bold text-4xl md:text-5xl mt-4 mb-4">alexis dev web</h1>
        <p className="text-muted text-xl max-w-xl">Agence web indépendante spécialisée dans le développement sur-mesure.</p>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-3 gap-8 border border-border rounded-lg p-8 mb-16">
        {stats.map(s => (
          <div key={s.label} className="text-center">
            <div className="font-display font-bold text-4xl text-accent">{s.value}</div>
            <div className="text-muted text-sm mt-1">{s.label}</div>
          </div>
        ))}
      </div>

      {/* Philosophie */}
      <section className="mb-16">
        <h2 className="font-display font-bold text-3xl mb-10">Notre philosophie</h2>
        <div className="grid md:grid-cols-3 gap-6">
          {philosophies.map(p => (
            <div key={p.title} className="bg-card border border-border rounded-lg p-6">
              <h3 className="font-display font-semibold text-lg mb-3 text-accent">{p.title}</h3>
              <p className="text-muted text-sm leading-relaxed">{p.body}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Stack tech */}
      <section className="mb-16 border-t border-border pt-16">
        <h2 className="font-display font-bold text-3xl mb-10">Notre stack</h2>
        <div className="grid md:grid-cols-4 gap-6">
          {techGroups.map(g => (
            <div key={g.name}>
              <div className="font-mono text-accent text-sm mb-4">// {g.name.toLowerCase()}/</div>
              <ul className="space-y-2">
                {g.items.map(item => (
                  <li key={item} className="text-text text-sm flex items-center gap-2">
                    <span className="w-1 h-1 bg-accent rounded-full inline-block"></span>
                    {item}
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </section>

      {/* Témoignages */}
      {testimonials && testimonials.length > 0 && (
        <section className="border-t border-border pt-16">
          <h2 className="font-display font-bold text-3xl mb-10">Ce qu'ils disent</h2>
          <div className="grid md:grid-cols-2 gap-6">
            {testimonials.map(t => (
              <div key={t.id} className="bg-card border border-border rounded-lg p-8">
                <p className="text-text leading-relaxed mb-6 italic">"{t.quote}"</p>
                <div>
                  <div className="font-semibold">{t.author}</div>
                  <div className="text-muted text-sm">{t.role}</div>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}
    </main>
  )
}
```

- [ ] **Step 2 : `Contact.tsx`**

```tsx
import { useState } from 'react'
import { postContact } from '../hooks/useApi'

const types = ['Site vitrine', 'Application web', 'API & intégration', 'E-commerce', 'Hébergement', 'Maintenance', 'Autre']
const budgets = ['À définir', '< 500€', '500€ – 1 500€', '1 500€ – 5 000€', '5 000€ – 15 000€', '> 15 000€']

export default function Contact() {
  const [form, setForm] = useState({ first_name: '', last_name: '', email: '', phone: '', type: 'Site vitrine', budget: 'À définir', message: '' })
  const [rgpd, setRgpd] = useState(false)
  const [submitted, setSubmitted] = useState(false)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const canSubmit = rgpd && form.first_name.trim() && form.email.trim()

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!canSubmit) return
    setLoading(true)
    setError(null)
    try {
      await postContact(form)
      setSubmitted(true)
      window.scrollTo(0, 0)
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Une erreur est survenue.')
    } finally {
      setLoading(false)
    }
  }

  if (submitted) {
    return (
      <main className="pt-32 pb-24 px-6 max-w-2xl mx-auto text-center">
        <div className="text-6xl mb-6">✓</div>
        <h1 className="font-display font-bold text-3xl mb-4">Message reçu !</h1>
        <p className="text-muted text-lg">Je reviens vers vous sous 24h. À bientôt.</p>
      </main>
    )
  }

  return (
    <main className="pt-24 pb-24 px-6 max-w-6xl mx-auto">
      <div className="mb-12">
        <span className="font-mono text-accent text-sm">// contact/</span>
        <h1 className="font-display font-bold text-4xl md:text-5xl mt-4 mb-4">Parlons de votre projet</h1>
        <p className="text-muted text-xl">Réponse garantie sous 24h.</p>
      </div>

      <div className="grid md:grid-cols-2 gap-16">
        {/* Formulaire */}
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid grid-cols-2 gap-4">
            {[['first_name', 'Prénom *', 'text'], ['last_name', 'Nom', 'text']].map(([name, label, type]) => (
              <div key={name}>
                <label className="block text-muted text-sm mb-2">{label}</label>
                <input
                  type={type}
                  name={name}
                  value={form[name as keyof typeof form]}
                  onChange={e => setForm(f => ({ ...f, [name]: e.target.value }))}
                  className="w-full bg-card border border-border rounded px-4 py-3 text-text outline-none focus:border-accent transition-colors"
                />
              </div>
            ))}
          </div>

          {[['email', 'Email *', 'email'], ['phone', 'Téléphone', 'tel']].map(([name, label, type]) => (
            <div key={name}>
              <label className="block text-muted text-sm mb-2">{label}</label>
              <input
                type={type}
                name={name}
                value={form[name as keyof typeof form]}
                onChange={e => setForm(f => ({ ...f, [name]: e.target.value }))}
                className="w-full bg-card border border-border rounded px-4 py-3 text-text outline-none focus:border-accent transition-colors"
              />
            </div>
          ))}

          <div>
            <label className="block text-muted text-sm mb-2">Type de projet</label>
            <select
              name="type"
              value={form.type}
              onChange={e => setForm(f => ({ ...f, type: e.target.value }))}
              className="w-full bg-card border border-border rounded px-4 py-3 text-text outline-none focus:border-accent transition-colors"
            >
              {types.map(t => <option key={t} value={t}>{t}</option>)}
            </select>
          </div>

          <div>
            <label className="block text-muted text-sm mb-2">Budget estimé</label>
            <select
              name="budget"
              value={form.budget}
              onChange={e => setForm(f => ({ ...f, budget: e.target.value }))}
              className="w-full bg-card border border-border rounded px-4 py-3 text-text outline-none focus:border-accent transition-colors"
            >
              {budgets.map(b => <option key={b} value={b}>{b}</option>)}
            </select>
          </div>

          <div>
            <label className="block text-muted text-sm mb-2">Message</label>
            <textarea
              name="message"
              rows={5}
              value={form.message}
              onChange={e => setForm(f => ({ ...f, message: e.target.value }))}
              placeholder="Décrivez votre projet, vos besoins..."
              className="w-full bg-card border border-border rounded px-4 py-3 text-text outline-none focus:border-accent transition-colors resize-none"
            />
          </div>

          <label className="flex items-start gap-3 cursor-pointer">
            <div
              onClick={() => setRgpd(r => !r)}
              className="w-5 h-5 rounded border flex-shrink-0 mt-0.5 flex items-center justify-center transition-colors"
              style={{ background: rgpd ? '#8B00FF' : '#161616', borderColor: rgpd ? '#8B00FF' : '#2A2A2A' }}
            >
              {rgpd && <span className="text-white text-xs">✓</span>}
            </div>
            <span className="text-muted text-sm">
              J'accepte que mes données soient utilisées pour traiter ma demande de contact.
            </span>
          </label>

          {error && <p className="text-red-400 text-sm">{error}</p>}

          <button
            type="submit"
            disabled={!canSubmit || loading}
            className="w-full py-3 rounded font-medium text-white transition-all"
            style={{ background: canSubmit ? '#8B00FF' : '#3D0080', opacity: canSubmit ? 1 : 0.6, cursor: canSubmit ? 'pointer' : 'not-allowed' }}
          >
            {loading ? 'Envoi...' : 'Envoyer la demande'}
          </button>
        </form>

        {/* Cal.com RDV */}
        <div>
          <h2 className="font-display font-bold text-2xl mb-4">Prendre rendez-vous</h2>
          <p className="text-muted text-sm mb-6">Préférez un appel découverte de 30 minutes ? Réservez directement un créneau.</p>
          <div className="bg-card border border-border rounded-lg overflow-hidden" style={{ height: 600 }}>
            <iframe
              src="https://rdv.alexis-rodrigues.fr"
              className="w-full h-full border-0"
              title="Prise de rendez-vous"
            />
          </div>
        </div>
      </div>
    </main>
  )
}
```

- [ ] **Step 3 : Commit**

```bash
git add -A && git commit -m "feat: pages Agence et Contact"
```

---

### Task 14 : Build + wiring Laravel + Nginx update + Déploiement

**Files:**
- Modify: `backend/config/session.php` (cookie secure)
- Modify: `/mnt/docker-volumes/nginx/conf.d/alex2-server.conf`

- [ ] **Step 1 : Build React final**

```bash
cd /mnt/docker-volumes/alexis-dev-web/frontend
npm run build
```
Attendu : `backend/public/build/index.html` créé.

- [ ] **Step 2 : Vérifier que Laravel sert le build**

```bash
cd /mnt/docker-volumes/alexis-dev-web/backend
php artisan serve --port=8000
```
Ouvrir `http://localhost:8000` → doit afficher la page React.
Ouvrir `http://localhost:8000/api/projects` → doit retourner le JSON des projets.
Ouvrir `http://localhost:8000/admin/login` → doit afficher la page de connexion.

- [ ] **Step 3 : Configurer les cookies session en HTTPS**

Dans `backend/config/session.php`, mettre :
```php
'secure' => env('APP_ENV') === 'production',
'same_site' => 'lax',
```

- [ ] **Step 4 : Installer les dépendances PHP dans le container**

```bash
cd /mnt/docker-volumes/alexis-dev-web
docker-compose up -d alexis-web
docker-compose exec alexis-web composer install --no-dev --optimize-autoloader
docker-compose exec alexis-web php artisan config:cache
docker-compose exec alexis-web php artisan route:cache
docker-compose exec alexis-web php artisan migrate --force
docker-compose exec alexis-web php artisan db:seed
```

- [ ] **Step 5 : Créer l'utilisateur admin dans le container**

```bash
docker-compose exec alexis-web php artisan tinker --execute="
  \App\Models\User::create([
    'name' => 'Alexis',
    'email' => 'contact.alex2.dev@gmail.com',
    'password' => bcrypt('VotreMotDePasseAdmin'),
  ]);
"
```

- [ ] **Step 6 : Mettre à jour nginx `alex2-server.conf`**

Trouver le bloc `server { ... server_name alexis-rodrigues.fr ...}` HTTPS et remplacer le `location /` :
```nginx
server {
    listen 443 ssl;
    server_name alexis-rodrigues.fr www.alexis-rodrigues.fr;

    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    include /etc/nginx/conf.d/security-headers.conf;

    location / {
        set $upstream_alexis "alexis-web:80";
        proxy_pass http://$upstream_alexis;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
    }
}
```

Ajouter le bloc pour Cal.com (après le bloc alexis-rodrigues.fr) :
```nginx
server {
    listen 80;
    server_name rdv.alexis-rodrigues.fr;
    return 301 https://$host$request_uri;
}
server {
    listen 443 ssl;
    server_name rdv.alexis-rodrigues.fr;

    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    include /etc/nginx/conf.d/security-headers.conf;

    location / {
        set $upstream_calcom "calcom:3000";
        proxy_pass http://$upstream_calcom;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-Proto https;
    }
}
```

- [ ] **Step 7 : Recharger nginx**

```bash
docker exec nginx-proxy nginx -t
docker exec nginx-proxy nginx -s reload
```
Attendu : `nginx: configuration file ... test is successful`

- [ ] **Step 8 : Vérification finale**

```bash
# Site public
curl -I https://alexis-rodrigues.fr
# → HTTP 200

# API
curl https://alexis-rodrigues.fr/api/projects | python3 -m json.tool | head -20
# → JSON avec 6 projets

# Admin
curl -I https://alexis-rodrigues.fr/admin/login
# → HTTP 200
```

- [ ] **Step 9 : Commit final**

```bash
cd /mnt/docker-volumes/alexis-dev-web
git add -A && git commit -m "feat: déploiement complet alexis dev web"
```

---

## Notes de configuration post-déploiement

1. **PHPMailer / SMTP Gmail** : activer l'authentification à 2 facteurs sur Gmail, générer un "App Password" et le mettre dans `MAIL_PASSWORD` du `.env`, puis `docker-compose exec alexis-web php artisan config:cache`.

2. **Cal.com** : après démarrage du container `calcom`, accéder à `https://rdv.alexis-rodrigues.fr` pour compléter la configuration initiale (compte, calendrier, créneaux).

3. **Mise à jour du site (projets, services...)** : aller sur `https://alexis-rodrigues.fr/admin` avec les credentials configurés.

4. **Rebuild frontend** : si tu modifies le frontend :
   ```bash
   cd /mnt/docker-volumes/alexis-dev-web/frontend
   npm run build
   docker-compose exec alexis-web php artisan config:cache
   ```
