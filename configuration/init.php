<?php

/*--------------------------------
| Configuration de l'application
|---------------------------------
| On retourne la configuration
| que l'application utilisera pour
| lancer la configuration initila
| de l'application
*/
return (object) [
    // Nom de l'Application
	"appname" => "The Snoop Framework",
    // La local
	"timezone" => "Africa/Abidjan",
    // Liste des extensions valides en cas d'upload de fichier
	"uploadFileExtension" => [ "jpg", "png", "jpeg" ],
    // Repertoire d'upload de fichier, Deux types sont valides
    // :folder|:ftp
	"uploadConfiguration" => (object) [
        "type" => "folder",
        "dirname" => dirname(__DIR__) . "/app/public/upload"
    ],
    // Niveau de log
	"loglevel" => "dev",
    // Repertoire de log
    "logDirecotoryName" => dirname(__DIR__) . "/logger",
    // En cas d'utilisation de token. Ceci est le temps de vie d'un token
	"tokenExpirateTime" => 50000,
    // chemin du fichier 404
	"notFoundFileName" => dirname(__DIR__) . "/app/views/404.php",
    // Template par defaut utiliser. Le systeme implement 3 moteur de template
    // Valeur possible: twig, mustache, jade
  	"template" => "mustache",
    // Le repertoire des vues
    "views" => dirname(__DIR__) . "/app/views",
    // Le repertoire de cache.
    "cacheFolder" => dirname(__DIR__) . "/cache/template",
    // Liste des namespaces
    "names" => [
        "namespace" => [
            "controller" => "App\Http\MyController",
            "middleware" => "App\Http\Middleware",
            "autoload" => dirname(__DIR__) . "/app/UserApplicationAutoload"
        ],
        // Liste de middleware
        "middleware" => ["auth"],
        "app_autoload" => "App\\UserApplicationAutoload"
    ],
    "type" => "dev"
];
