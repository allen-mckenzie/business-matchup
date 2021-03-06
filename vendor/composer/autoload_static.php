<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite8688ddf5e9e3671bfd2d8ca1ec6ab4a
{
    public static $files = array (
        '54427b3bde4cd549d15a05029bc5fadc' => __DIR__ . '/../..' . '/includes/classes/class-business-matchup-cpt.php',
        '753dff471c115229bd6befc0a298a613' => __DIR__ . '/../..' . '/includes/classes/class-business-matchup-settings.php',
        '1715dc798e8a5e69c777dd9d63595950' => __DIR__ . '/../..' . '/includes/classes/class-business-matchup-polls-page.php',
        'b92b0b7d953b1a686fdd57f2907c5aa9' => __DIR__ . '/../..' . '/includes/classes/class-business-matchup-api.php',
    );

    public static $prefixLengthsPsr4 = array (
        'B' => 
        array (
            'BusinessMatchup\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'BusinessMatchup\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite8688ddf5e9e3671bfd2d8ca1ec6ab4a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite8688ddf5e9e3671bfd2d8ca1ec6ab4a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite8688ddf5e9e3671bfd2d8ca1ec6ab4a::$classMap;

        }, null, ClassLoader::class);
    }
}
