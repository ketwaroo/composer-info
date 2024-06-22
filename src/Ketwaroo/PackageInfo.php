<?php

/**
 *  @author Yaasir Ketwaroo 
 */

namespace Ketwaroo;

use Ketwaroo\Exception\ExceptionPackageInfo;

/**
 * Description of PackageInfo
 */
class PackageInfo {

    /**
     * Default composer.json file name.
     */
    const COMPOSER_FILENAME = 'composer.json';

    /**
     *
     * @var static[] 'composer.json location'=>instances.
     */
    protected static $cachedInstances = [];

    /**
     *
     * @var array 'various hint strings'=>'canonical composer.json location'
     */
    protected static $cachedLookups = [];

    /**
     *
     * @var string 
     */
    protected $composerJsonFile;

    /**
     *
     * @var array 
     */
    protected $composerJsonData;

    /**
     *
     * @var string 
     */
    protected $packageName;

    /**
     *
     * @var string 
     */
    protected $packageBasePath;

    /**
     * Instantiate/cache and get the package info for a given hint.
     * 
     * @param mixed $hint ideally __DIR__. Also accepts __FILE__, class name or class instance.
     * @param string $composerFileName alternate composer.json file name.
     * @return static
     */
    public static function whereAmI($hint, $composerFileName = self::COMPOSER_FILENAME) {

        $hint = static::determineHintLocation($hint);

        if (isset(static::$cachedLookups[$hint])) {
            return static::$cachedInstances[static::$cachedLookups[$hint]];
        }

        $packageInfo = new static($hint, $composerFileName);

        static::$cachedLookups[$hint] = $packageInfo->getComposerJsonFilename();

        // try again. could already have a hit
        if (isset(static::$cachedInstances[static::$cachedLookups[$hint]])) {
            return static::$cachedInstances[static::$cachedLookups[$hint]];
        }

        static::$cachedInstances[static::$cachedLookups[$hint]] = $packageInfo;

        return $packageInfo;

    }

    /**
     * 
     * @param mixed $hint ideally __DIR__. Also accepts __FILE__, class name or class instance.
     * @param string $composerFileName alternate composer.json file name.
     */
    public function __construct($hint, $composerFileName = self::COMPOSER_FILENAME) {
        $hintLocation = $this->determineHintLocation($hint);

        $this->packageBasePath  = $this->guessBaseDir($hintLocation, $composerFileName);
        $this->composerJsonFile = $this->packageBasePath . '/' . $composerFileName;
        $this->loadComposerData();

    }

    /**
     * Reads the composer data.
     * 
     * @throws ExceptionPackageInfo
     */
    protected function loadComposerData() {
        if (!is_readable($this->composerJsonFile)) {
            throw new ExceptionPackageInfo('Could not read composer data.');
        }

        $this->composerJsonData = json_decode(file_get_contents($this->composerJsonFile), true);

    }

    /**
     * Try to figure out "root" directory.
     * 
     * @param string $hintLocation
     * @param string $composerFileName
     * @return string
     * @throws ExceptionPackageInfo
     */
    protected function guessBaseDir($hintLocation, $composerFileName) {
        // keep going up until we hit what we're looking for.
        do {
            $f = $hintLocation . '/' . $composerFileName;

            if (is_file($f)) {
                return $hintLocation;
            }
            $prevLocation = $hintLocation;

            $hintLocation = $this->sanitisePaths(dirname($hintLocation));

            $ranOut = ($prevLocation === $hintLocation); // basically hit root directory
        } while (!$ranOut);

        throw new ExceptionPackageInfo('The trail went cold. Could not determine the location of the composer file and ran out of places to look.');

    }

    /**
     * Convert whatever hint we got into a usable file path.
     * 
     * @param mixed $hint 
     * @return string Real path to the hint.
     * @throws ExceptionPackageInfo If hint location could not be determined.
     */
    protected static function determineHintLocation($hint) {
        if (is_string($hint) && file_exists($hint)) {
            $path = $hint;
        }
        elseif (is_object($hint) || class_exists($hint)) {
            $r    = new \ReflectionClass($hint);
            $path = $r->getFileName();
        }

        // we need full path. could be weird link
        $path = realpath($path);

        if (false === $path) {
            throw new ExceptionPackageInfo('Is it real? Could not determine real location of hint.');
        }

        return static::sanitisePaths($path);

    }

    /**
     * converts windows paths to unix.
     * 
     * @param string $path
     * @return string
     */
    public static function sanitisePaths($path) {
        return preg_replace('~[\\\/]+~', '/', $path);

    }

    /**
     * 
     * @return array composer data.
     */
    public function getComposerJson() {
        return $this->composerJsonData;

    }

    /**
     * 
     * @return string
     */
    public function getComposerJsonFilename() {
        return $this->composerJsonFile;

    }

    /**
     * 
     * @return string vendor/package
     */
    public function getPackageName() {
        if (empty($packageName)) {
            if (isset($this->composerJsonData['name'])) {
                $this->packageName = $this->composerJsonData['name'];
            }
            else { // name is required but just in case..
                list($package, $vendor) = array_reverse(explode('/', $this->packageBasePath));
                $this->packageName = "{$vendor}/{$package}";
            }
        }

        return $this->packageName;

    }

    /**
     * "root" of the package.
     * 
     * @return string
     */
    public function getPackageBasePath() {
        return $this->packageBasePath;

    }
}
