<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'mystore' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', '' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '[^[m,(=J_3VDDZL3N}>l@9W@2/{zB]A]h)Y!0k@@pvS[cl0mi}b)&-;h?U?%%lCf' );
define( 'SECURE_AUTH_KEY',  'q#Xu!szXhuQED9i0LzE<.wE&*iufVJWsXkfNSt#JW;2vW&]$fE<_{{,[l@.![.I}' );
define( 'LOGGED_IN_KEY',    '4*=VkSD mkY#F8+0MPB:ezb!p6VCkMIc%rLA[ KM*5FYHIdv[~Uy&=vaSV+CjsTD' );
define( 'NONCE_KEY',        '}C9r`tq(m:TW7JcRY]i5)s Y>mf}=^b=!iQ^z%:2RCbd3>{~dWf5:xW5X2zG*w=s' );
define( 'AUTH_SALT',        'oCwF&+W2DvQf,- ~0j~)-Cn3U 2%q9fq;`r41iA|d_(7El;t68Y9+`(/iJ&[jQRL' );
define( 'SECURE_AUTH_SALT', '~&~@)5LSiB~%0Xp0?ya@Cy5HOKTwHjhWD%J,<gJBnNMHFljo@1sm;`@0gjqZT4r{' );
define( 'LOGGED_IN_SALT',   'vLN5P_DM%C[93I#eVj?`49fr{r-}T?2fPX#Z6ZeW3w8FtH/Jv(;LdtV>Nl12AkD7' );
define( 'NONCE_SALT',       'n|B6SZR&TV>$[oVR@o#y0`L?MzJ75v1`#qk6GG_P`wPFCoyK&$]AcfbPw[uY+Mn<' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
