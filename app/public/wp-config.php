<?php
//Begin Really Simple Security session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple Security cookie settings
//Begin Really Simple Security key
define('RSSSL_KEY', 'j66xuWIoisjo2ecNiSDMGCUkAgau1eiTj1QyZg62xBXuEwFLlT5Vy129d3TCjOrJ');
//END Really Simple Security key
define( 'WP_CACHE', true );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );
/** Database username */
define( 'DB_USER', 'root' );
/** Database password */
define( 'DB_PASSWORD', 'root' );
/** Database hostname */
define( 'DB_HOST', 'localhost' );
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
define( 'AUTH_KEY',          '},?O)<z|l`##X9n%,Hy&qDE3Q5I-on9wDu9;;=)$^[?&6Z4j2rIFMxATcp3<LWWE' );
define( 'SECURE_AUTH_KEY',   'TeWhewU=Z}@(!d2$nB$8$+t./;7q{L^;ljI>F`EVm{9qj_pqv67K6A6zEL]}Nm^J' );
define( 'LOGGED_IN_KEY',     'Tuu7<dR@b.~ec]b%h6_]J j^N?rs3,~Ku[6RpV)VXg:V.>X^^7>2mcvCmgp>KaAs' );
define( 'NONCE_KEY',         'ikgp+:$-Zf4[Wwzt-j->^D @IFCY@6BIQ%ATlHYmMa]]2,0Gw7dD%74L3@D_d@9G' );
define( 'AUTH_SALT',         '#Gen<NytB=nz.@DT[N)krpI&;RMo~8 @-rp?&,9Z3(h@/s!}`}oML!I9UMM:{${s' );
define( 'SECURE_AUTH_SALT',  'dCdkKj2n>&$`nl#77ym@GdE]l;z1o{c8F<xg+MKL3Mihg?,ZD1,[u7_?SeW.=~%c' );
define( 'LOGGED_IN_SALT',    '`G|8O,.UNu4X6JIec%oV@Z,+%}msBF%SZ>]c+T[;/EZEp_zSESZ5qOEJ=VL]hMhi' );
define( 'NONCE_SALT',        'vsT)0u&?iw=$8r)Z`&2!xx<IRqt4aCXF)LN01dI)Cw{XU=*CAFVWw7Q~a5}jeSCh' );
define( 'WP_CACHE_KEY_SALT', 'Rw%;.GEF-ynr4wmP<A/Ot7OVW$T3Q#:e+an}(k2QFk$Gz2K&TU/SQn@?%tNn?_am' );
/**#@-*/
/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
/* Add any custom values between this line and the "stop editing" line. */
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}
define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
