# 🌿 Brindille

> A bilingual WordPress theme, light as a twig. / *Un thème WordPress bilingue, léger comme une brindille.*

Brindille is the custom theme and development environment behind [jasonvanstone.com](https://jasonvanstone.com). It is built with modern
WordPress practices: containerized development, Twig templating, SCSS,and Composer-managed dependencies. The site is currently published in
English and French. All content is written and translated by hand, and Polylang is used to manage the languages.

**Website:** https://jasonvanstone.com

## Why "Brindille"?

*Brindille* is the French word for *twig*. The theme uses Twig for its templates, and it aims to stay small, flexible, and easy to grow.

## Tech Stack

| Area            | Tools                                        |
| --------------- | -------------------------------------------- |
| CMS             | WordPress                                    |
| Theme           | Custom theme (no page builders)              |
| Templating      | Twig (via Timber)                            |
| Styling         | SCSS                                         |
| Multilingual    | Polylang (manual translations)               |
| Dependencies    | Composer                                     |
| Local dev       | DDEV                                         |

## Features

- 🌿 Custom theme built from scratch
- 🌍 Multilingual architecture with Polylang, currently English and French
- ✍️ Content translated manually (no automatic or machine translation)
- 🧩 Twig templates that separate presentation from PHP logic
- 🎨 Modular SCSS architecture
- 🐳 Reproducible local environment with DDEV
- 📦 PHP dependencies managed with Composer

## Requirements

- [DDEV](https://ddev.com/get-started/) and Docker
- Git

## Getting Started

```bash
# Clone the repository
git clone https://github.com/<username>/<repo>.git
cd <repo>

# Start the local environment
ddev start

# Install PHP dependencies
ddev composer install
```

The site will be available at `https://<project-name>.ddev.site`.

## Multilingual Setup

[Polylang](https://wordpress.org/plugins/polylang/) is used to organize and link translations. It does not translate anything on its own: all
translations are written manually.

Polylang is not limited to two languages. For this site, the chosen direction is English and French, but the theme is built so that other
languages could be added later.

- Languages are configured in **Languages > Settings** in the WordPress admin.
- Posts, pages, and menus are translated per language and linked together.
- Theme strings are registered with `pll_register_string()` and translated   under **Languages > Translations**.
- In Twig templates, translated strings are accessed with Polylang functions:

```twig
{{ function('pll__', 'Read more') }}
```

## Updating Dependencies with Composer

All PHP dependencies are managed with Composer and run inside the DDEV
container, so everyone uses the same PHP version.

```bash
# See which packages are outdated
ddev composer outdated

# Update all dependencies (within the constraints in composer.json)
ddev composer update

# Update a single package
ddev composer update vendor/package

# Update a package to a new major version
ddev composer require vendor/package:^2.0
```

**Recommended workflow**

1. Create a branch for the update.
2. Run `ddev composer update` (or update a specific package).
3. Test the site locally, in every language.
4. Commit `composer.json` and `composer.lock` together.
5. Deploy and run `composer install` on the target environment.

> Always commit `composer.lock`. It ensures every environment installs the
> exact same versions.

## Project Structure

```
.
├── .ddev/                  # DDEV configuration
├── wp-content/
│   └── themes/
│       └── brindille/
│           ├── views/      # Twig templates
│           ├── scss/       # Stylesheets
│           └── functions.php
├── composer.json
└── composer.lock
```

## Development

```bash
ddev start          # Start the environment
ddev stop           # Stop the environment
ddev composer ...   # Run Composer commands
ddev wp ...         # Run WP-CLI commands
```

## License

Brindille is released under the [GNU General Public License v2 or later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html) (GPL-2.0-or-later), the same license as WordPress.

See the [LICENSE](LICENSE) file for details.
