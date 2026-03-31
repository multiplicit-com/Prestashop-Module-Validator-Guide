# PrestaShop Module Validator Guide

A practical guide and ready-to-use tooling for PrestaShop module developers —
particularly those preparing a module for submission to the
[PrestaShop Addons Marketplace](https://addons.prestashop.com).

The Addons validator checks hundreds of rules across Requirements, Structure,
Security, Licenses, Standards, and Compatibility. Many of the failures are
mechanical and can be fixed automatically. This guide documents what the
validator actually cares about, what can be auto-fixed with
[PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer), and what needs
to be done manually.

Built and maintained by [Multiplicit](https://multiplicit.co.uk).

---

## What's in this repo

| File | Purpose |
|---|---|
| `.php-cs-fixer.php` | Drop-in PHP-CS-Fixer config tuned for PS modules — handles Standards and Licenses categories |
| `stubs/index.php` | Security stub — copy to every directory in your module |
| `stubs/.htaccess` | Root `.htaccess` — copy to your module root |
| `scripts/add-stubs.sh` | Bash script that stamps `index.php` into every directory automatically |

---

## Quick start
You can run this file on your local machine if you have PHP installed. the easiest solution is MS Powershell or any linux terminal.

### 1. Download PHP-CS-Fixer

```bash
curl -L https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/releases/latest/download/php-cs-fixer.phar \
     -o php-cs-fixer.phar
```

### 2. Copy the config

Copy `.php-cs-fixer.php` from this repo into your module root and update the
header block at the top:

```php
$header = <<<'EOF'
Your Module Display Name

@author    Your Name / Company
@copyright 2024 Your Name / Company
@license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
EOF;
```

> **Licence note:** PrestaShop Addons requires
> [AFL-3.0](https://opensource.org/licenses/AFL-3.0) for free modules. For
> paid/commercial modules, replace the licence line with your own licence text.

### 3. Run the fixer

```bash
php php-cs-fixer.phar fix --config=.php-cs-fixer.php --allow-risky=yes
```

Always use `--allow-risky=yes` — several rules required by the validator are
marked risky by the fixer.

### 4. Add security stubs

Run the helper script from your module root to stamp `index.php` into every
directory:

```bash
bash /path/to/scripts/add-stubs.sh
```

Or copy `stubs/index.php` manually into each directory. Copy `stubs/.htaccess`
to your module root.

Re-run the fixer after adding stubs so they get the licence header too.

### 5. Fix the blank line issue

`@PSR12` adds a blank line between `<?php` and `/**`. The validator requires no
blank line before the file comment. After the first fixer run, strip them:

```bash
grep -rl $'<?php\n\n/\*\*' --include="*.php" . | grep -v vendor | while read f; do
  perl -i -0pe 's|^<\?php\n\n/\*\*|<?php\n/**|' "$f"
done
```

Then run the fixer once more — it won't add them back because the config sets
`'blank_line_after_opening_tag' => false`.

---

## Manual steps the fixer can't do

### `if (!defined('_PS_VERSION_')) { exit; }` guard

Every PHP file must include this guard. For **namespaced files**, it goes after
the `namespace` declaration — PHP requires `namespace` to be the first statement
after `declare`:

```php
<?php
/**
 * licence header
 */

declare(strict_types=1);

namespace Your\Namespace;

if (!defined('_PS_VERSION_')) {
    exit;
}
```

For **non-namespaced files** (controllers, upgrade scripts, the main module
file), it goes right after the licence header:

```php
<?php
/**
 * licence header
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
```

### `config.xml` version must match `$this->version`

A mismatch is a hard Requirements error. Keep them in sync every time you bump
the version:

```xml
<!-- config.xml -->
<version><![CDATA[1.3.0]]></version>
```

```php
// YourModule.php
$this->version = '1.3.0';
```

### `composer.json` — prepend-autoloader

If your module has a `composer.json`, add this to the `config` block:

```json
"config": {
    "prepend-autoloader": false
}
```

### Upgrade scripts — correct class name

The PS module class name uses underscores matching the directory name. The type
hint in upgrade scripts must match exactly:

```php
// module directory: my_module  →  class: My_module
function upgrade_module_1_1_0(My_module $module): bool
```

This catches people out because the namespace uses CamelCase but the PS class
name does not.

---

## Known false positives

The validator's static analyser cannot resolve certain things. These will always
appear as Compatibility errors but **do not block submission** — they are a
known limitation of the tool.

| What it flags | Why it's a false positive |
|---|---|
| `$this->l()` in admin controllers | Inherited from `ModuleAdminController` → `AdminController`. The validator can't follow PS core inheritance. |
| `ftp_connect()`, `ftp_login()` etc. | PHP built-in FTP extension functions. The validator can't verify which extensions are loaded. |
| PHP 8.1 enum `::from()` / `::tryFrom()` | Built-in enum methods. The validator doesn't follow custom autoloaders. |
| Third-party library methods (e.g. phpseclib) | Classes outside the validator's scan scope. |
| Any class loaded via a custom autoloader | Same reason — the validator does pure static analysis without executing the autoloader. |

When submitting to Addons, you can note these in the submission comments.

---

## Validator categories explained

| Category | What it checks | Auto-fixable? |
|---|---|---|
| **Requirements** | `config.xml` version match, bootstrap lines, `composer.json` config | Partially |
| **Structure** | `if (!defined('_PS_VERSION_'))` in every file | Manually, then run fixer |
| **Errors** | PS core method/class usage patterns | Usually false positives |
| **Compatibility** | Static analysis — classes, methods, functions exist | Mostly false positives |
| **Licenses** | Licence header on every file | Yes — fixer handles this |
| **Security** | `index.php` stubs, `.htaccess` | Manually — use the script |
| **Standards** | PSR-12, spacing, syntax | Yes — fixer handles this |

---

## Pre-submission checklist

- [ ] `config.xml` version matches `$this->version`
- [ ] `if (!defined('_PS_VERSION_')) { exit; }` in every PHP file
- [ ] `index.php` stub in every directory (run `scripts/add-stubs.sh`)
- [ ] `.htaccess` in module root
- [ ] Licence header on every PHP file (run the fixer)
- [ ] `composer.json` has `"prepend-autoloader": false` (if applicable)
- [ ] Upgrade script functions use the correct PS class name
- [ ] PHP-CS-Fixer reports `Fixed 0 of N files` (everything clean)
- [ ] Validator shows 0 Requirements, 0 Structure, 0 Errors, 0 Licenses, 0 Standards
- [ ] Remaining Compatibility errors reviewed and confirmed as false positives
- [ ] `logo.png` in module root (144×144px — required by Addons)
- [ ] Clean install and uninstall tested on a vanilla PS8 instance
- [ ] Uninstall removes all DB tables and `Configuration` keys

---

## Contributing

PRs welcome — particularly if you find validator rules or patterns not covered
here. Please open an issue first for anything substantive.

---

## About

Made by [Multiplicit](https://multiplicit.co.uk) — Affiliate and ecommerce consultancy in the UK.
