<?php
// Moodle Configuration File for Komodo Deployment

unset($CFG);
global $CFG;
$CFG = new stdClass();

// =========================================================================
// DATABASE SETUP
// =========================================================================
$CFG->dbtype    = 'pgsql';                  // PostgreSQL (recommended)
$CFG->dblibrary = 'native';
$CFG->dbhost    = getenv('DB_HOST') ?: 'moodle-db';
$CFG->dbname    = getenv('DB_NAME') ?: 'moodle';
$CFG->dbuser    = getenv('DB_USER') ?: 'moodle';
$CFG->dbpass    = getenv('DB_PASSWORD') ?: 'moodlepass123';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array(
    'dbpersist' => false,
    'dbport' => getenv('DB_PORT') ?: 5432,
    'dbsocket' => false,
    'dbcollation' => 'utf8_general_ci',
);

// =========================================================================
// GENERAL SETTINGS
// =========================================================================
$CFG->wwwroot   = getenv('MOODLE_URL') ?: 'http://localhost';
$CFG->dataroot  = '/var/www/moodledata';
$CFG->admin     = getenv('MOODLE_ADMIN') ?: 'admin';
$CFG->directorypermissions = 0777;

// =========================================================================
// SECURITY SETTINGS
// =========================================================================
$CFG->passwordsaltmain = 'define_a_random_string_here_' . hash('sha256', time());
$CFG->behat_wwwroot = $CFG->wwwroot;
$CFG->behat_dataroot = $CFG->dataroot . '/behat';
$CFG->behat_prefix = 'bht_';

// =========================================================================
// SESSION AND AUTHENTICATION
// =========================================================================
$CFG->sessiontimeout = 7200;
$CFG->sessioncookie = 'MoodleSession' . md5($CFG->wwwroot);
$CFG->sessioncookiepath = '/';
$CFG->sessioncookiedomain = '';
$CFG->cookiehttponly = true;
$CFG->cookiesecure = false; // Set to true if using HTTPS

// =========================================================================
// EMAIL SETTINGS
// =========================================================================
$CFG->smtphosts = getenv('SMTP_HOST') ?: 'smtp.ligjamaica.com';
$CFG->smtpuser  = getenv('SMTP_USER') ?: '';
$CFG->smtppass  = getenv('SMTP_PASSWORD') ?: '';
$CFG->smtpsecure = getenv('SMTP_SECURE') ?: 'tls';
$CFG->noreplyaddress = getenv('NOREPLY_ADDRESS') ?: 'noreply@ligjamaica.com';

// =========================================================================
// FILE UPLOAD LIMITS
// =========================================================================
$CFG->maxbytes = 209715200; // 200MB
$CFG->userquota = 1073741824; // 1GB per user

// =========================================================================
// LANGUAGE AND LOCATION
// =========================================================================
$CFG->lang = 'en';
$CFG->langmenu = 1;
$CFG->langlist = 'en,es,fr,de';

// =========================================================================
// CACHING
// =========================================================================
$CFG->cachedir = $CFG->dataroot . '/cache';
$CFG->cache_redis_host = getenv('REDIS_HOST') ?: 'localhost';
$CFG->cache_redis_port = getenv('REDIS_PORT') ?: 6379;

// If Redis is available, use it for caching (requires redis support compiled)
if (getenv('USE_REDIS') === 'true') {
    $CFG->cachestore_redis_default = array(
        'mode' => 'rec',
        'server' => $CFG->cache_redis_host . ':' . $CFG->cache_redis_port,
        'ttl' => 3600,
        'serializer' => 1,
    );
}

// =========================================================================
// LOGGING
// =========================================================================
$CFG->logdir = '/app/logs';
$CFG->log_display_errors = true;
$CFG->debug = DEBUG_DEVELOPER; // Change to DEBUG_NONE in production
$CFG->debugdisplay = true; // Set to false in production
$CFG->debugsmtp = false;

// =========================================================================
// PERFORMANCE & OPTIMIZATION
// =========================================================================
$CFG->disableuserimages = false;
$CFG->disablegradehistory = false;
$CFG->allowthemechangeonurl = false;
$CFG->pathtox = '/usr/bin';
$CFG->aspellpath = '';
$CFG->pathtodot = '';
$CFG->pathtoconvert = '';
$CFG->pathtogs = '';

// =========================================================================
// PLUGINS & FEATURES
// =========================================================================
$CFG->enableajax = true;
$CFG->enablerssfeeds = true;
$CFG->enableportfolios = true;
$CFG->enablebadges = true;
$CFG->enablecompletion = true;
$CFG->enableavailability = true;
$CFG->enablegroups = true;
$CFG->enablegroupings = true;
$CFG->enableteaching = true;

// =========================================================================
// MAINTENANCE & BACKUP
// =========================================================================
$CFG->backupdir = $CFG->dataroot . '/backups';
$CFG->tempdir = $CFG->dataroot . '/temp';
$CFG->localcachedir = $CFG->dataroot . '/localcache';

// =========================================================================
// KOMODO SPECIFIC SETTINGS
// =========================================================================
$CFG->forced_plugin_settings = array(
    'auth_manual' => array(
        'expiration' => 0,
        'expirationnotify' => 0,
        'notifyall' => 0,
        'notifyrequired' => 0,
    ),
);

// =========================================================================
// HTTP & PROXY SETTINGS
// =========================================================================
$CFG->proxyhost = getenv('PROXY_HOST') ?: '';
$CFG->proxyport = getenv('PROXY_PORT') ?: '';
$CFG->proxyuser = getenv('PROXY_USER') ?: '';
$CFG->proxypassword = getenv('PROXY_PASSWORD') ?: '';
$CFG->proxybypass = 'localhost, 127.0.0.1';

// =========================================================================
// CURL SETTINGS
// =========================================================================
$CFG->curlcache = 120;
$CFG->curllibrary = 'native';

// =========================================================================
// THIRDPARTYLIBS
// =========================================================================
$CFG->thirdpartylibs = array(
    // 'your/external/lib' => 'GPL-3.0+',
);

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems.
