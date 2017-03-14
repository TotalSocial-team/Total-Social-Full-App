<?php

/** Laravel - A PHP Framework For Web Artisans

*
*
* @package Laravel
* @author George
*/

$uri = urlcode(
  parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.

if ($uri !== "/" && file_exists(_DIR_.'/public'.$uri)) {
  return false;
}

require once _DIR_. '/public/index.php';
