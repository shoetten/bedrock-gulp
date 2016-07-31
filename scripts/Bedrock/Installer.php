<?php

namespace Bedrock;

use Composer\Script\Event;

class Installer {
    public static $saltKeys = array(
        'AUTH_KEY',
        'SECURE_AUTH_KEY',
        'LOGGED_IN_KEY',
        'NONCE_KEY',
        'AUTH_SALT',
        'SECURE_AUTH_SALT',
        'LOGGED_IN_SALT',
        'NONCE_SALT'
    );

    public static $dbKeys = [
        'DB_NAME',
        'DB_HOST',
        'DB_USER',
        'DB_PASSWORD',
        'WP_ENV',
        'WP_HOME',
        'WP_SITEURL',
    ];

    private static $io;

    /**
     * Holds the root path.
     * @var string
     */
    private static $root;

    /**
     * Holds the path to the .env file.
     * @var string
     */
    private static $envFilePath;

    /**
     * @param Event $event
     */
    public static function addSalts(Event $event)
    {
        static::$root        = dirname(dirname(__DIR__));
        static::$envFilePath = static::$root . '/.env';

        static::$io     = $event->getIO();
        $generate_salts = false;

        // Only add new/regenerate salts if we are doing a fresh install and specify "yes" to the question.
        // This way the salts aren't cleared whenever a `composer install` is done on deploy.
        if (static::$io->isInteractive()) {
            $generate_salts = static::$io->askConfirmation('<info>Generate salts and append to .env file?</info> [<comment>Y,n</comment>]? ', true);
        }

        // No need to continue - exit.
        if (! $generate_salts) {
            return;
        }

        // Open the file for writing.
        $handle = fopen(static::$envFilePath, 'a');

        $keys = [];

        // Get the salt keys.
        foreach (static::$saltKeys as $key) {
            $keys[] = static::getEnvVar($key, Installer::generateSalt());
        }

        // Append additional keys to the .env file/keys array.
        foreach (static::setEnvironmentVariables() as $key => $val) {
            $keys[] = static::getEnvVar($key, $val);
        }

        if ($handle) {
            fwrite($handle, implode($keys, "\n"));
            fclose($handle);
        }
    }

    /**
     * Returns the key/value pair that will be appended to the .env file.
     *
     * @param $key
     * @param $value
     * @return string
     */
    private static function getEnvVar($key, $value)
    {
        return sprintf("%s='%s'", $key, $value);
    }

    /**
     * Create the .env file and set up the salts.
     *
     * @return void
     */
    private static function createEnvFile()
    {
        if (file_exists(static::$envFilePath)) {
            return false;
        }
    }

    /**
     * Slightly modified/simpler version of wp_generate_password
     *
     * @link <https://github.com/WordPress/WordPress/blob/cd8cedc40d768e9e1d5a5f5a08f1bd677c804cb9/wp-includes/pluggable.php#L1575>
     */
    public static function generateSalt($length = 64)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $chars .= '!@#$%^&*()';
        $chars .= '-_ []{}<>~`+=,.;:/?|';

        $salt = '';
        for ($i = 0; $i < $length; $i++) {
            $salt .= substr($chars, rand(0, strlen($chars) - 1), 1);
        }

        return $salt;
    }

    /**
     * Prompt for additional default environment variables on install with interaction.
     * Database variables are DB_HOST, DB_NAME, DB_USER, DB_PASSWORD
     * WP Environment Variables are WP_ENV, WP_HOME, WP_SITEURL
     *
     * @return array
     */
    private static function setEnvironmentVariables()
    {
        $vars = [];

        // Set up some sensible defaults for env vars.
        $parentDirBasename = basename(dirname(static::$envFilePath));
        $wpEnv             = 'development';
        $wpHomeUrl         = 'http://' . $parentDirBasename . '.dev';
        $wpSiteUrl         = $wpHomeUrl . '/wp';
        $dbHost            = 'localhost';
        $dbName            = self::formatDatabaseName($parentDirBasename);
        $dbUser            = 'root';
        $dbPassword        = 'root';

        $vars['DB_HOST']     = static::$io->ask("Database host? [<comment>$dbHost</comment>]", $dbHost);
        $vars['DB_NAME']     = static::$io->ask("Database name? [<comment>$dbName</comment>]", $dbName);
        $vars['DB_USER']     = static::$io->ask("Database user? [<comment>$dbUser</comment>]", $dbUser);
        $vars['DB_PASSWORD'] = static::$io->ask("Database password? [<comment>$dbPassword</comment>]", $dbPassword);

        $vars['WP_ENV']     = static::$io->ask("WP Environment? [<comment>$wpEnv</comment>]", $wpEnv);
        $vars['WP_HOME']    = static::$io->ask("WP Home URL? [<comment>$wpHomeUrl</comment>]", $wpHomeUrl);
        $vars['WP_SITEURL'] = static::$io->ask("WP Site (admin) URL? [<comment>$wpSiteUrl</comment>]", $wpSiteUrl);

        // Make sure we have a proper format for the database name.
        $vars['DB_NAME'] = static::formatDatabaseName($vars['DB_NAME']);

        return $vars;
    }

    /**
     * @param $parentDirBasename
     * @return mixed
     */
    private static function formatDatabaseName($parentDirBasename)
    {
        $dbName = preg_replace('/(?:[^\w]+)/', '_', basename($parentDirBasename));
        return $dbName;
    }
}
