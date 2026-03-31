<?php
/**
 * Security stub — prevents directory listing.
 *
 * Copy this file into every directory in your module.
 * Run PHP-CS-Fixer afterwards to stamp the licence header.
 *
 * See https://github.com/multiplicit-com/prestashop-module-validator-guide
 */
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Location: ../');
exit;
