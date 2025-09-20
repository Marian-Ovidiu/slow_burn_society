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

// define( 'DB_NAME', 'slow_burn' );
// define( 'DB_USER', 'root' );
// define( 'DB_PASSWORD', 'root' );

define( 'DB_NAME', 'u597020236_sbs' );
define( 'DB_USER', 'u597020236_sbs' );
define( 'DB_PASSWORD', '414984.Hmo' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '{^0Yh+@~Xq@DUXC3T%fU+p[Y,fDE.Iq#WlcSr<A&-b>QKm[JT[LqD0UN}%X/<RtX' );
define( 'SECURE_AUTH_KEY',  '@l=qQ9JhXuQ!2gjt=|_1+{l--7YR02_^(,e(KR.4,~rUmI9pHAl-ArR9JbHl Vsk' );
define( 'LOGGED_IN_KEY',    'bgsk$iG1b%X4GFWpU>mP.WPpyCK}!iS{Gk~P-fro<!7=nEdKVp&Ewib:XWZ^0@W$' );
define( 'NONCE_KEY',        '7AGzSuRfHxyN%^Y X(B,,d]HT=h/1d#J9,@1kEa+3HYAO?t_^-6Vvln).]ygYn,y' );
define( 'AUTH_SALT',        ']<uw:14W;%0?iYmYDN[tr(7Q(]0Jttf*}L;k(d38D &@qIk#LFy?TA| 4{1Pon@u' );
define( 'SECURE_AUTH_SALT', 'T;}.4QS-+WkR<kES7$).!Mz^G_6J5FKBV;[[8H_Z_T,7Rm>p.<u_H:E:nY!Y!f_n' );
define( 'LOGGED_IN_SALT',   '_MMtt%ZZm}cBmPT4vqZNPQVKlJ`b~NJoF47*SC@%j6E%0sj$N?<V1J02tCr*{zm)' );
define( 'NONCE_SALT',       ' *4@HGC##?KPbAL:;mB*rD7v^k_4lU!s]]{-TL-Tzu#*j3QoZ>L0tf0HE+#hd~~5' );

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
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );
define('WP_CACHE', false);
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

/* Add any custom values between this line and the "stop editing" line. */

define('WP_IMAGE_EDITORS', array('WP_Image_Editor_GD'));


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
