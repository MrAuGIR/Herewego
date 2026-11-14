# HereWeGo

Plateforme d'évènements et de covoiturage : les organisateurs publient des évènements,
les participants s'y inscrivent et proposent ou réservent des places de transport.

Stack : PHP 8.2, Symfony 7.4, Doctrine ORM 3, MySQL, Messenger (emails asynchrones).
L'application Symfony vit dans `sources/`.

## Démarrage (Docker)

```bash
make start                                   # conteneurs www, db, phpmyadmin, maildev
docker exec herewego_www composer install
docker exec herewego_www php bin/console doctrine:migrations:migrate -n
```

| Service    | URL                     |
|------------|-------------------------|
| Application| http://localhost:3000   |
| phpMyAdmin | http://localhost:8080   |
| Maildev    | http://localhost:8081   |

Le port HTTP est surchargeable si un autre projet occupe le 3000 : `WWW_PORT=3001 make start`.

## Tests, qualité & CI

PHPUnit tourne sur MySQL avec `dama/doctrine-test-bundle` : chaque test s'exécute dans une
transaction roll-backée, la base reste propre.

```bash
make test-db         # une seule fois : crée herewego_test et joue les migrations
```

Tout s'exécute **dans le conteneur** `herewego_www` via le `Makefile` (`make start` au préalable) :

```bash
make quality         # PHP-CS-Fixer (dry-run) + PHPStan niveau 6 + lint Symfony
make cs-fix          # corrige le style PHP
make audit           # composer audit (CVE connues, paquets abandonnés)
make test            # PHPUnit
make test-coverage   # PHPUnit + couverture + seuil bloquant (70 %)
make check           # tout : qualité + audit + tests + couverture
```

| Outil          | Config                              | Exigence                                                     |
|----------------|-------------------------------------|--------------------------------------------------------------|
| PHP-CS-Fixer   | `sources/.php-cs-fixer.dist.php`    | `@Symfony` + `@Symfony:risky`, `declare(strict_types=1)`     |
| PHPStan        | `sources/phpstan.dist.neon`         | niveau 6, extensions Symfony/Doctrine/PHPUnit, sans baseline |
| Lint Symfony   | `composer lint`                     | container, YAML, Twig, mapping Doctrine                      |
| PHPUnit        | `sources/phpunit.dist.xml`          | couverture lignes ≥ 70 % (`bin/coverage-check.php`)          |
| composer audit | —                                   | aucune CVE connue                                            |

Rapport HTML de couverture : `sources/var/coverage/html/index.html` (driver pcov dans l'image
Docker, ou xdebug en mode `coverage` à défaut).

Organisation des tests :

- `tests/Unit` : PHP pur (entités, voters, règles d'envoi de mails, sécurité, client SIRET mocké) ;
- `tests/Integration` : container Symfony + BDD (repositories, services, commande `app:send-email-transport`) ;
- `tests/Functional` : requêtes HTTP via `WebTestCase` (pages publiques, évènements, transports,
  espaces utilisateur/organisateur, inscription, FAQ, administration) ;
- `tests/Support` : helpers partagés (`CreatesEntities`, `FunctionalTestCase`, `SetsIds`).

En environnement de test, les fichiers générés (uploads, logos, CSV) sont écrits dans
`sources/var/test-files/`, jamais dans `public/`.

### Hook git pre-push

À installer **une fois** après le clone :

```bash
make hooks-install   # git config core.hooksPath .githooks
```

Avant chaque `git push`, `.githooks/pre-push` lance PHP-CS-Fixer, PHPStan, le lint Symfony,
`composer audit` et PHPUnit (avec seuil de couverture) dans Docker ; le push est annulé au
premier échec. Contournement d'urgence : `git push --no-verify`.

### Intégration continue

Sur chaque PR vers `develop` ou `main` et chaque push dans ces branches,
[GitHub Actions](.github/workflows/ci.yml) lance 3 jobs : qualité (CS, PHPStan, lint),
sécurité (`composer audit`), tests + couverture (MySQL 8, base montée depuis les migrations).
Le rapport de couverture est publié en artefact et résumé dans l'onglet *Summary* du run.

## Cache applicatif (production)

Le cache applicatif (`cache.app`, dont le rate limiter d'inscription : une entrée
par IP) est stocké **en base** (table `cache_items`, adaptateur Doctrine DBAL) et non
en fichiers : l'adaptateur fichier écrit un fichier par entrée sans jamais supprimer
les expirées, ce qui peut saturer le **quota d'inodes** de l'hébergement mutualisé
(incident Bibliotheque : 127 000 fichiers dans `var/cache/prod/pools`).

Au déploiement de ce changement (depuis `sources/`) :

```bash
php bin/console doctrine:migrations:migrate -n --env=prod   # crée cache_items
php bin/console cache:clear --env=prod
rm -rf var/cache/prod/pools/app                             # anciens fichiers, devenus inutiles
```

Purge des lignes expirées (sans impact sur le quota de fichiers) :
`php bin/console cache:pool:prune --env=prod` (`make cache-prune` en local), à
planifier si possible (cron hebdomadaire).
