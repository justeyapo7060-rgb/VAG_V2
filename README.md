# VAG_V2 — Gestion de l'atelier (formations, cohortes, apprenants, paiements)

Application Laravel 13. Elle fonctionne **sans MySQL** : SQLite est utilisé par défaut.

## Prérequis

- PHP **8.4 ou plus** avec les extensions `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`
- [Composer](https://getcomposer.org) 2

Node/npm n'est **pas** nécessaire (les pages chargent leurs assets via CDN).

## Démarrage rapide

```bash
composer setup          # installe les dépendances, crée .env, la base SQLite, la clé, migre
composer demo           # (optionnel) réinitialise la base et la remplit de données de démonstration
php artisan serve       # http://localhost:8000/apprenants
```

Les commandes détaillées, si vous préférez :

```bash
composer install
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
php artisan migrate --seed
```

## Vérifier que tout fonctionne

```bash
php artisan test                      # 13 tests (paiements, rappels, impayés)
php artisan paiements:rappel --dry-run
php artisan schedule:list
```

## Paiements des formations

- À l'inscription, l'apprenant verse un **acompte** (versement n°1), retranché du total de toutes ses formations.
- Les versements suivants sont **libres** (montants et dates), **4 versements maximum** acompte inclus ; le 4ᵉ doit solder le reste.
- Le solde doit être réglé **avant la fin de la cohorte** (`date_fin`).
- **Rappel par e-mail** à tous les non-soldés le **dernier jour de chaque mois à 08:00** (`paiements:rappel`).
- Suivi des impayés : menu **Finances · Impayés** (`/paiements/impayes`) ; paiements d'un apprenant : `/apprenants/{id}/paiements`.

### Planification (rappels de fin de mois)

En production, une seule entrée cron suffit :

```
* * * * * cd /chemin/du/projet && php artisan schedule:run >> /dev/null 2>&1
```

En local : `php artisan schedule:work`. Configurez `MAIL_*` dans `.env` pour envoyer de vrais e-mails
(par défaut `MAIL_MAILER=log` écrit les e-mails dans `storage/logs/laravel.log`).

## Utiliser MySQL à la place de SQLite

Dans `.env` : `DB_CONNECTION=mysql`, puis `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, et `php artisan migrate --seed`.
