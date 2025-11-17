<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
define('DB_NAME', 'wordpress');
define('DB_USER', 'wordpress');
define('DB_PASSWORD', 'wordpress123');
define('DB_HOST', 'db');
define('WP_ALLOW_MULTISITE', true);
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', false );
define( 'DOMAIN_CURRENT_SITE', 'localhost:8080' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );


/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '?^+H)kNZh}NP+CHzQ91XLc8<9u)Cx(ID.`Q[,j|1a2<5oc7^RnjC-7Q~qAZj`q<A');
define('SECURE_AUTH_KEY',  'm!;Zc|9gI zT|L8-HR< IQDr(*ZgCQNaOL5ti{)--VeFX -@?Id1-lXqxy/BYCwM');
define('LOGGED_IN_KEY',    'XpC.=ix-X ,Whk6>g=!;oJwO$*!b<sn992*dtxuT&jY8|zq=/fzj[t&_Kh`N,ZOn');
define('NONCE_KEY',        '.^ILd->29BzEF$$RI|gV<Nn+0b]Q=vCTJJ(h7tXi%]r,[38;|L:Iy[u<-Zs4G;Va');
define('AUTH_SALT',        'KqF9>:[[_{OkEjgEIsjS:xl.HT:+@qBhM)=/FIE4EM/k5#ZE:Q>C`4n(MxI^Ab}a');
define('SECURE_AUTH_SALT', 'D-7;Zd2xAVS`~TX-8lu5wWrVZ_)@*7H*<P1/I]-mWcd>d.f*1rOpZz]lLa}ui?_+');
define('LOGGED_IN_SALT',   '87F<jyvw`g|^m91/pdA(M-;(`4%+. V.Mcu+vt}Re0eRw|d s8@Uqr9+HACDI#Im');
define('NONCE_SALT',       'J+&@UR8|lZx0+y1EOR,[ddC?TFSQ4kEabcs:H>Uo$).rR-Qp,H>kHS&?+&dwk^Yz');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wpt5_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
  define('ABSPATH', __DIR__ . '/wp/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
