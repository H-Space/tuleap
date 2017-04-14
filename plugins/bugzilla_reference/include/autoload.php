<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoload89db8299f7d3ebfcf8a263c76590d746($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'bugzilla_referenceplugin' => '/bugzilla_referencePlugin.class.php',
            'tuleap\\bugzilla\\administration\\controller' => '/Bugzilla/Administration/Controller.php',
            'tuleap\\bugzilla\\administration\\presenter' => '/Bugzilla/Administration/Presenter.php',
            'tuleap\\bugzilla\\administration\\referencepresenter' => '/Bugzilla/Administration/ReferencePresenter.php',
            'tuleap\\bugzilla\\administration\\router' => '/Bugzilla/Administration/Router.php',
            'tuleap\\bugzilla\\plugin\\descriptor' => '/Bugzilla/Plugin/Descriptor.php',
            'tuleap\\bugzilla\\plugin\\info' => '/Bugzilla/Plugin/Info.php',
            'tuleap\\bugzilla\\reference\\dao' => '/Bugzilla/Reference/Dao.php',
            'tuleap\\bugzilla\\reference\\keywordisalreadyusedexception' => '/Bugzilla/Reference/KeywordIsAlreadyUsedException.php',
            'tuleap\\bugzilla\\reference\\keywordisinvalidexception' => '/Bugzilla/Reference/KeywordIsInvalidException.php',
            'tuleap\\bugzilla\\reference\\reference' => '/Bugzilla/Reference/Reference.php',
            'tuleap\\bugzilla\\reference\\referencedestructor' => '/Bugzilla/Reference/ReferenceDestructor.php',
            'tuleap\\bugzilla\\reference\\referenceretriever' => '/Bugzilla/Reference/ReferenceRetriever.php',
            'tuleap\\bugzilla\\reference\\referencesaver' => '/Bugzilla/Reference/ReferenceSaver.php',
            'tuleap\\bugzilla\\reference\\requiredfieldemptyexception' => '/Bugzilla/Reference/RequiredFieldEmptyException.php',
            'tuleap\\bugzilla\\reference\\serverisinvalidexception' => '/Bugzilla/Reference/ServerIsInvalidException.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoload89db8299f7d3ebfcf8a263c76590d746');
// @codeCoverageIgnoreEnd
