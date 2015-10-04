<?php

/**
 *  @author Yaasir Ketwaroo <ketwaroo.yaasir@gmail.com>
 */

namespace Ketwaroo;

use Ketwaroo\Exception\ExceptionPackageInfo;

/**
 * Description of PackageInfo
 */
class PackageInfo
{

    const COMPOSER_FILENAME = 'composer.json';

    /**
     *
     * @var array 'composer.json location'=> 
     */
    protected static $cachedInstances = [];

    protected static $cachedLookups = [];

    protected $composerJsonFile;

    protected $composerJsonData;

    protected $packageName;

    protected $packageBasePath;

    /**
     * 
     * @return static
     */
    public static function whereAmI($hint, $composerFileName = self::COMPOSER_FILENAME)
    {

        $hint = static::determineHintLocation($hint);

        if (isset(static::$cachedLookups[$hint]))
        {
            return static::$cachedInstances[static::$cachedLookups[$hint]];
        }

        $packageInfo = new static($hint, $composerFileName);

        static::$cachedLookups[$hint] = $packageInfo->getComposerJsonFilename();

        // try again. could already have a hit
        if (isset(static::$cachedInstances[static::$cachedLookups[$hint]]))
        {
            return static::$cachedInstances[static::$cachedLookups[$hint]];
        }

        static::$cachedInstances[static::$cachedLookups[$hint]] = $packageInfo;

        return $packageInfo;
    }

    /**
     * 
     * @param mixed $hint file, directory or class name/instance. works best with __FILE__
     * @
     */
    public function __construct($hint, $composerFileName = self::COMPOSER_FILENAME)
    {
        $hintLocation = $this->determineHintLocation($hint);

        $this->packageBasePath  = $this->guessBaseDir($hintLocation, $composerFileName);
        $this->composerJsonFile = $this->packageBasePath . '/' . $composerFileName;
        $this->composerJsonData = $this->loadComposerData($this->composerJsonFile);

        return $this;
    }

    protected function loadComposerData($composerFile)
    {
        if (!is_readable($composerFile))
        {
            throw new ExceptionPackageInfo('Could not read composer data.');
        }

        $this->composerJson = json_decode(file_get_contents($composerFile), true);
    }

    /**
     * 
     * @param string $hintLocation
     * @param string $composerFileName
     * @return string
     * @throws ExceptionPackageInfo
     */
    protected function guessBaseDir($hintLocation, $composerFileName)
    {
        // keep going up until we hit what we're looking for.
        $prevLocation = $hintLocation;

        do
        {
            $f = $hintLocation . '/' . $composerFileName;

            if (is_file($f))
            {
                return $hintLocation;
            }
            $prevLocation = $hintLocation;

            $hintLocation = $this->sanitisePaths(dirname($hintLocation));

            $ranOut = ($prevLocation === $hintLocation); // basically hit root directory
        }
        while (!$ranOut);


        throw new ExceptionPackageInfo('The trail went cold. Could not determine the location of the composer file and ran out of places to look.');
    }

    /**
     * 
     * @param mixed $hint 
     * @return string
     * @throws ExceptionPackageInfo
     */
    public static function determineHintLocation($hint)
    {
        if (is_object($hint) || class_exists($hint))
        {
            $r = new \ReflectionClass($hint);

            $path = $r->getFileName();
        }
        elseif (is_dir($hint))
        {
            $path = $hint;
        }
        elseif (is_file($hint))
        {
            $path = dirname($hint);
        }

        // we need full path
        $path = realpath($path);

        if (false === $path)
        {
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
    public static function sanitisePaths($path)
    {
        return preg_replace('~[\\\/]+~', '/', $path);
    }

    /**
     * 
     * @return array
     */
    public function getComposerJson()
    {
        return $this->composerJsonData;
    }

    public function getComposerJsonFilename()
    {
        return $this->composerJsonFile;
    }

    public function getPackageName()
    {
        if (empty($packageName))
        {
            if (isset($this->composerJsonData['name']))
            {
                $this->packageName = $this->composerJsonData['name'];
            }
            else // name is required but just in case..
            {
                list($package, $vendor) = array_reverse(explode('/', $this->packageBasePath));
                $this->packageName = "{$vendor}/{$package}";
            }
        }

        return $this->packageName;
    }

    public function getPackageBasePath()
    {
        return $this->packageBasePath;
    }

}
