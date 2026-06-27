# alexis dev web — Design Spec
**Date :** 2026-06-21  
**Domaine :** alexis-rodrigues.fr (remplace portfolio-alexis)  
**Répertoire :** /mnt/docker-volumes/alexis-dev-web/

---

## 1. Contexte

Site vitrine + applicatif pour l'agence **alexis dev web**. Migré depuis un bundle HTML opaque (KODEX.) vers une stack maintenable. Le contenu existant (6 projets, 6 services, 2 témoignages, 5 pages) est reproduit fidèlement, le design dark/violet est conservé.

---

## 2. Stack

| Couche | Technologie |
|--------|------------|
| Frontend | Vite + React 18 + Tailwind CSS + React Router |
| Backend | PHP 8.3 + Laravel 11 |
| Base de données | MariaDB (host, port 3306, DB: `alexis_dev_web`) |
| Admin | Blade + Tailwind (custom, pas de Filament) |
| PDF | DomPDF (via Laravel) |
| Mail | PHPMailer |
| RDV | Cal.com (container Docker dédié) |
| Infra | Docker (container `alexis-web`) + nginx-proxy `alex2-net` |

---

## 3. Architecture (Approche B)

Un seul container `alexis-web` (PHP-FPM + nginx interne). Laravel sert le build React comme assets statiques et expose les routes API + admin.

```
alexis-rodrigues.fr
└── container: alexis-web
    ├── frontend/          ← Vite + React (source)
    │   └── dist/          ← build → copié dans backend/public/build/
    └── backend/           ← Laravel 11
        ├── routes/api.php       ← API JSON
        ├── routes/web.php       ← /admin/* Blade + catch-all SPA
        └── public/build/        ← assets React servis

rdv.alexis-rodrigues.fr
└── container: calcom      ← Cal.com Docker officiel
```

---

## 4. Frontend

### Pages React
| Route | Composant | Contenu |
|-------|-----------|---------|
| `/` | Home | Hero + métriques + aperçu services + 3 projets + témoignages |
| `/services` | Services | 6 services avec prix + 4 étapes |
| `/realisations` | Projects | Grille filtrée (Tous / Sites vitrines / Applications / Infra) |
| `/realisations/:slug` | ProjectDetail | Description complète du projet |
| `/agence` | Agency | Philosophie (3 valeurs) + stack tech + stats |
| `/contact` | Contact | Formulaire + iframe Cal.com |

### Structure fichiers
```
frontend/src/
├── pages/           Home, Services, Projects, ProjectDetail, Agency, Contact
├── components/      Navbar, Footer
├── hooks/           useApi.ts (fetch /api/*)
└── types/           index.ts
```

### Design système
- Fond : `#111111`, accent : `#8B00FF`, texte : `#F9F9F9`, secondaire : `#888888`
- Titres : Space Grotesk — Corps : Inter — Labels code : JetBrains Mono (`// label/`)
- Transitions scroll, filtres projets, nav sticky blur

### Data flow
- Toutes les données (projets, services, témoignages) viennent de `/api/*` → MariaDB
- Pas de contenu hardcodé dans le frontend

---

## 5. Backend Laravel

### Routes API (`/api/*`, JSON, publiques)
```
GET  /api/projects              liste projets (filtre ?category=)
GET  /api/projects/{slug}       détail projet
GET  /api/services              liste services
GET  /api/testimonials          témoignages
POST /api/contact               formulaire → PHPMailer + DB
```

### Routes Admin (`/admin/*`, auth Laravel session)
```
GET       /admin                     dashboard
GET/POST  /admin/projects            CRUD projets
GET/POST  /admin/services            CRUD services
GET/POST  /admin/testimonials        CRUD témoignages
GET       /admin/contacts            liste demandes reçues
GET       /admin/contacts/{id}/pdf   génère + télécharge PDF devis (DomPDF)
POST      /admin/login               authentification (un seul admin)
```

### Traitement formulaire contact
1. Validation Laravel (firstName, email requis)
2. Insertion en DB (`contacts`)
3. Envoi email via PHPMailer (notification à Alexis)
4. Réponse JSON `{ success: true }`

### Génération PDF devis
- Vue Blade `resources/views/pdf/devis.blade.php`
- Contenu : nom client, email, téléphone, type projet, budget, message, date de demande, logo agence
- Généré à la demande depuis `/admin/contacts/{id}/pdf`

---

## 6. Base de données

```sql
-- Demandes contact
contacts (
  id, first_name, last_name, email, phone,
  type, budget, message, created_at, updated_at
)

-- Projets (contenu gérable depuis l'admin)
projects (
  id, slug, name, client, category, year,
  summary, full_text JSON, tech JSON, rendered JSON,
  sort_order, active TINYINT, created_at, updated_at
)

-- Services
services (
  id, slug, title, sub, body,
  tags JSON, price, sort_order, active TINYINT,
  created_at, updated_at
)

-- Témoignages
testimonials (
  id, quote, author, role,
  sort_order, active TINYINT,
  created_at, updated_at
)

-- Admin (un seul utilisateur)
users (Laravel par défaut)
```

Seeders : données du bundle injectées à l'init (6 projets, 6 services, 2 témoignages).

---

## 7. Infrastructure

### Docker Compose (`/mnt/docker-volumes/alexis-dev-web/docker-compose.yml`)
```yaml
services:
  alexis-web:
    build: .
    container_name: alexis-web
    restart: unless-stopped
    networks: [alex2-net]
    volumes:
      - ./backend:/var/www/html
    environment:
      DB_HOST: host.docker.internal
      DB_PORT: 3306
      DB_DATABASE: alexis_dev_web
      DB_USERNAME: ...
      DB_PASSWORD: ...
    extra_hosts:
      - "host.docker.internal:host-gateway"

  calcom:
    image: calcom/cal.com
    container_name: calcom
    restart: unless-stopped
    networks: [alex2-net]
    environment:
      NEXTAUTH_URL: https://rdv.alexis-rodrigues.fr
      DATABASE_URL: ...

networks:
  alex2-net:
    external: true
```

### Dockerfile (`alexis-web`)
- Base : `php:8.3-fpm-alpine`
- Extensions : pdo_mysql, mbstring, zip, gd (DomPDF)
- Composer, nginx interne, supervisor (FPM + nginx)

### Nginx `alex2-server.conf` — modifications
- Bloc `alexis-rodrigues.fr` : proxy vers `http://alexis-web:80` (remplace `nodejs:3000/sites/portfolio-alexis/`)
- Nouveau bloc `rdv.alexis-rodrigues.fr` : proxy vers `http://calcom:3000`

---

## 8. Contenu migré depuis le bundle

### Projets (6)
Boulangerie Martin, Portail Solidaire, API Logistique Pro, Boutique Terroir, Tableau de bord Ville, Cabinet Lextra

### Services (6)
Sites vitrines (800€), Applications web (3500€), APIs & intégrations (1500€), E-commerce (2500€), Hébergement managé (49€/mois), Maintenance (39€/mois)

### Témoignages (2)
Sophie Martin (Boulangerie Martin), Pierre Lambert (Association Solidaire)

---

## 9. Hors périmètre

- SSL : géré par le nginx-proxy existant (cert.pem déjà en place)
- Cal.com config complète (OAuth, DB) : configurée manuellement après déploiement
- Emails SMTP : configuration PHPMailer via `.env` (credentials à fournir)
- Backups DB : infrastructure existante du serveur
