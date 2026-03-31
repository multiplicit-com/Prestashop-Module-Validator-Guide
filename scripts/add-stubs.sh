#!/usr/bin/env bash
# add-stubs.sh
#
# Stamps a security index.php into every directory of your module that
# doesn't already have one. Run from your module root.
#
# Usage:
#   bash /path/to/add-stubs.sh
#
# After running, re-run PHP-CS-Fixer so the licence header gets added
# to the new index.php files.
#
# See https://github.com/multiplicit-com/prestashop-module-validator-guide

set -euo pipefail

STUB='<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Location: ../");
exit;'

SKIP_DIRS=("vendor" "node_modules" ".git")

should_skip() {
    local dir="$1"
    for skip in "${SKIP_DIRS[@]}"; do
        if [[ "$dir" == *"/$skip"* || "$dir" == *"\\$skip"* ]]; then
            return 0
        fi
    done
    return 1
}

count=0

while IFS= read -r -d '' dir; do
    if should_skip "$dir"; then
        continue
    fi
    if [ ! -f "$dir/index.php" ]; then
        echo "$STUB" > "$dir/index.php"
        echo "Created: $dir/index.php"
        ((count++))
    fi
done < <(find . -type d -print0)

echo ""
echo "Done — $count index.php file(s) created."
echo "Now re-run PHP-CS-Fixer to add the licence header to new files."
