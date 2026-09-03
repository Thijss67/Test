<?php
/**
 * Instellingen van het beheerpaneel.
 *
 * Normaal hoef je hier niets aan te wijzigen. Staat de site in een andere map
 * dan public_html, pas dan alleen SITE_MAP aan.
 */

// De map waarin index.html van de site staat (een niveau boven /beheer).
define('SITE_MAP', dirname(__DIR__));

define('DATA_MAP', SITE_MAP . '/data');
define('CASES_BESTAND', DATA_MAP . '/cases.json');
define('ARTIKELEN_BESTAND', DATA_MAP . '/artikelen.json');
define('WACHTWOORD_BESTAND', DATA_MAP . '/wachtwoord.php');
define('POGINGEN_BESTAND', DATA_MAP . '/pogingen.json');

define('PORTFOLIO_MAP', SITE_MAP . '/portfolio');
define('BLOG_MAP', SITE_MAP . '/blog');
define('ASSETS_MAP', SITE_MAP . '/assets');
define('SJABLOON_MAP', __DIR__ . '/sjablonen');

// Zonder slash op het eind.
define('BASIS_URL', 'https://dh-studio.nl');

// Maximale grootte van een geuploade afbeelding (in bytes). Het paneel
// verkleint hem daarna zelf naar BEELD_BREEDTE pixels, dus een groot
// bestand uit een AI-generator of camera mag gewoon.
define('MAX_BEELD', 20 * 1024 * 1024);

// Breedte waarnaar elke afbeelding wordt teruggerekend, en de JPEG-kwaliteit.
define('BEELD_BREEDTE', 1400);
define('BEELD_KWALITEIT', 82);

// Na dit aantal mislukte pogingen is inloggen vijftien minuten geblokkeerd.
define('MAX_POGINGEN', 5);
define('BLOKKADE_SECONDEN', 900);
