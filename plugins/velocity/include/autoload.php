<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoloadc56bb4a4918eafaa33ac46edec4166f1($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'tuleap\\velocity\\plugin\\plugindescriptor' => '/Velocity/Plugin/PluginDescriptor.php',
            'tuleap\\velocity\\plugin\\plugininfo' => '/Velocity/Plugin/PluginInfo.php',
            'tuleap\\velocity\\semantic\\semanticvelocity' => '/Velocity/Semantic/SemanticVelocity.php',
            'tuleap\\velocity\\semantic\\semanticvelocityadminpresenter' => '/Velocity/Semantic/SemanticVelocityAdminPresenter.php',
            'velocityplugin' => '/velocityPlugin.class.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoloadc56bb4a4918eafaa33ac46edec4166f1');
// @codeCoverageIgnoreEnd