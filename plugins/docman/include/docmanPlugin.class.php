<?php
/**
 * Copyright (c) STMicroelectronics, 2006. All Rights Reserved.
 *
 * Originally written by Manuel Vacelet, 2006
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * 
 */
require_once('common/plugin/Plugin.class.php');
require_once('Docman_VersionFactory.class.php');
require_once('Docman_ItemFactory.class.php');

class DocmanPlugin extends Plugin {
	
	function DocmanPlugin($id) {
		$this->Plugin($id);
        $this->_addHook('cssfile',                           'cssFile',                           false);
        $this->_addHook('javascript_file',                   'jsFile',                            false);
        $this->_addHook('logs_daily',                        'logsDaily',                         false);
        $this->_addHook('permission_get_name',               'permission_get_name',               false);
        $this->_addHook('permission_get_object_type',        'permission_get_object_type',        false);
        $this->_addHook('permission_get_object_name',        'permission_get_object_name',        false);
        $this->_addHook('permission_get_object_fullname',    'permission_get_object_fullname',    false);
        $this->_addHook('permission_user_allowed_to_change', 'permission_user_allowed_to_change', false);
        $this->_addHook('service_public_areas',              'service_public_areas',              false);
        $this->_addHook('service_admin_pages',               'service_admin_pages',               false);
        $this->_addHook('permissions_for_ugroup',            'permissions_for_ugroup',            false);
        $this->_addHook('register_project_creation',         'installNewDocman',                  false);
        $this->_addHook('service_is_used',                   'service_is_used',                   false);
        $this->_addHook('soap',                              'soap',                              false);
        $this->_addHook('widget_instance',                   'myPageBox',                         false);
        $this->_addHook('widgets',                           'widgets',                           false);
        $this->_addHook('codendi',                           'codendiDaily',                      false);
        $this->_addHook('default_widgets_for_new_owner',     'default_widgets_for_new_owner',     false);
        $this->_addHook('wiki_page_updated',                 'wiki_page_updated',                 false);
        $this->_addHook('wiki_before_content',               'wiki_before_content',               false);
        $this->_addHook('isWikiPageReferenced',              'isWikiPageReferenced',              false);
        $this->_addHook('isWikiPageEditable',                'isWikiPageEditable',                false);
        $this->_addHook('userCanAccessWikiDocument',         'userCanAccessWikiDocument',         false);
        $this->_addHook('getPermsLabelForWiki',              'getPermsLabelForWiki',              false);
        $this->_addHook('ajax_reference_tooltip',            'ajax_reference_tooltip',            false);
        $this->_addHook('project_export_entry',              'project_export_entry',              false);
        $this->_addHook('project_export',                    'project_export',                    false);
        $this->_addHook('SystemEvent_PROJECT_RENAME',        'renameProject',                     false);
        $this->_addHook('file_exists_in_data_dir',           'file_exists_in_data_dir',           false);
        // Stats plugin
        $this->_addHook('plugin_statistics_disk_usage_collect_project', 'plugin_statistics_disk_usage_collect_project', false);
        $this->_addHook('plugin_statistics_disk_usage_service_label',   'plugin_statistics_disk_usage_service_label',   false);

        $this->_addHook('show_pending_documents',             'show_pending_documents',             false);

        $this->_addHook('backend_system_purge_files',  'purgeFiles',  false);
	}

    function permission_get_name($params) {
        if (!$params['name']) {
            switch($params['permission_type']) {
                case 'PLUGIN_DOCMAN_READ':
                    $params['name'] = $GLOBALS['Language']->getText('plugin_docman', 'permission_read');
                    break;
                case 'PLUGIN_DOCMAN_WRITE':
                    $params['name'] = $GLOBALS['Language']->getText('plugin_docman', 'permission_write');
                    break;
                case 'PLUGIN_DOCMAN_MANAGE':
                    $params['name'] = $GLOBALS['Language']->getText('plugin_docman', 'permission_manage');
                    break;
                case 'PLUGIN_DOCMAN_ADMIN':
                    $params['name'] = $GLOBALS['Language']->getText('plugin_docman', 'permission_admin');
                    break;
                default:
                    break;
            }
        }
    }
    function permission_get_object_type($params) {
        if (!$params['object_type']) {
            if (in_array($params['permission_type'], array('PLUGIN_DOCMAN_READ', 'PLUGIN_DOCMAN_WRITE', 'PLUGIN_DOCMAN_MANAGE', 'PLUGIN_DOCMAN_ADMIN'))) {
                require_once('Docman_ItemFactory.class.php');
                $if =& new Docman_ItemFactory();
                $item =& $if->getItemFromDb($params['object_id']);
                if ($item) {
                    $params['object_type'] = is_a($item, 'Docman_Folder') ? 'folder' : 'document';
                }
            }
        }
    }
    function permission_get_object_name($params) {
        if (!$params['object_name']) {
            if (in_array($params['permission_type'], array('PLUGIN_DOCMAN_READ', 'PLUGIN_DOCMAN_WRITE', 'PLUGIN_DOCMAN_MANAGE', 'PLUGIN_DOCMAN_ADMIN'))) {
                require_once('Docman_ItemFactory.class.php');
                $if =& new Docman_ItemFactory();
                $item =& $if->getItemFromDb($params['object_id']);
                if ($item) {
                    $params['object_name'] = $item->getTitle();
                }
            }
        }
    }
    function permission_get_object_fullname($params) {
        if (!$params['object_fullname']) {
            if (in_array($params['permission_type'], array('PLUGIN_DOCMAN_READ', 'PLUGIN_DOCMAN_WRITE', 'PLUGIN_DOCMAN_MANAGE', 'PLUGIN_DOCMAN_ADMIN'))) {
                require_once('Docman_ItemFactory.class.php');
                $if =& new Docman_ItemFactory();
                $item =& $if->getItemFromDb($params['object_id']);
                if ($item) {
                    $type = is_a($item, 'Docman_Folder') ? 'folder' : 'document';
                    $name = $item->getTitle();
                    $params['object_fullname'] = $type .' '. $name; //TODO i18n
                }
            }
        }
    }
    function permissions_for_ugroup($params) {
        if (!$params['results']) {
            if (in_array($params['permission_type'], array('PLUGIN_DOCMAN_READ', 'PLUGIN_DOCMAN_WRITE', 'PLUGIN_DOCMAN_MANAGE', 'PLUGIN_DOCMAN_ADMIN'))) {
                require_once('Docman_ItemFactory.class.php');
                $if =& new Docman_ItemFactory();
                $item =& $if->getItemFromDb($params['object_id']);
                if ($item) {
                    $type = is_a($item, 'Docman_Folder') ? 'folder' : 'document';
                    $params['results']  = $GLOBALS['Language']->getText('plugin_docman', 'resource_name_'.$type, array(
                            '/plugins/docman/?group_id='. $params['group_id'] .
                              '&amp;action=details&amp;section=permissions' .
                              '&amp;id='. $item->getId()
                            , $item->getTitle()
                        )
                    );
                }
            }
        }
    }
    var $_cached_permission_user_allowed_to_change;
    function permission_user_allowed_to_change($params) {
        if (!$params['allowed']) {
            if (!$this->_cached_permission_user_allowed_to_change) {
                if (in_array($params['permission_type'], array('PLUGIN_DOCMAN_READ', 'PLUGIN_DOCMAN_WRITE', 'PLUGIN_DOCMAN_MANAGE', 'PLUGIN_DOCMAN_ADMIN'))) {
                    require_once('Docman_HTTPController.class.php');
                    $docman =& new Docman_HTTPController($this, $this->getPluginPath(), $this->getThemePath());
                    switch($params['permission_type']) {
                        case 'PLUGIN_DOCMAN_READ':
                        case 'PLUGIN_DOCMAN_WRITE':
                        case 'PLUGIN_DOCMAN_MANAGE':
                            $this->_cached_permission_user_allowed_to_change = $docman->userCanManage($params['object_id']);
                            break;
                        default:
                            $this->_cached_permission_user_allowed_to_change = $docman->userCanAdmin();
                            break;
                    }
                }
            }
            $params['allowed'] = $this->_cached_permission_user_allowed_to_change;
        }
    }
    function &getPluginInfo() {
        if (!is_a($this->pluginInfo, 'DocmanPluginInfo')) {
            require_once('DocmanPluginInfo.class.php');
            $this->pluginInfo =& new DocmanPluginInfo($this);
        }
        return $this->pluginInfo;
    }
    
    function cssFile($params) {
        // Only show the stylesheet if we're actually in the Docman pages.
        // This stops styles inadvertently clashing with the main site.
        if (strpos($_SERVER['REQUEST_URI'], $this->getPluginPath()) === 0 ||
            strpos($_SERVER['REQUEST_URI'], '/widgets/') === 0 
        ) {
            echo '<link rel="stylesheet" type="text/css" href="'.$this->getThemePath().'/css/style.css" />'."\n";
        }
    }
    
    function jsFile($params) {
        // Only show the stylesheet if we're actually in the Docman pages.
        // This stops styles inadvertently clashing with the main site.
        if (strpos($_SERVER['REQUEST_URI'], $this->getPluginPath()) === 0) {
            echo '<script type="text/javascript" src="/scripts/behaviour/behaviour.js"></script>'."\n";
            echo '<script type="text/javascript" src="/scripts/scriptaculous/scriptaculous.js"></script>'."\n";
            echo '<script type="text/javascript" src="'.$this->getPluginPath().'/scripts/docman.js"></script>'."\n";
        }
    }

    function logsDaily($params) {
        require_once('Docman_HTTPController.class.php');
        $controler =& new Docman_HTTPController($this, $this->getPluginPath(), $this->getThemePath());
        $controler->logsDaily($params);
    }
    
    function service_public_areas($params) {
        if ($params['project']->usesService('docman')) {
            $params['areas'][] = '<a href="/plugins/docman/?group_id='. $params['project']->getId() .'">' .
                '<img src="'. $this->getThemePath() .'/images/ic/text.png" />&nbsp;' .
                $GLOBALS['Language']->getText('plugin_docman', 'descriptor_name') .': '.
                $GLOBALS['Language']->getText('plugin_docman', 'title') .
                '</a>';
        }
    }
    function service_admin_pages($params) {
        if ($params['project']->usesService('docman')) {
            $params['admin_pages'][] = '<a href="/plugins/docman/?action=admin&amp;group_id='. $params['project']->getId() .'">' .
                $GLOBALS['Language']->getText('plugin_docman', 'service_lbl_key') .' - '. 
                $GLOBALS['Language']->getText('plugin_docman', 'admin_title') .
                '</a>';
        }
    }
    function installNewDocman($params) {
        require_once('Docman_HTTPController.class.php');
        $controler =& new Docman_HTTPController($this, $this->getPluginPath(), $this->getThemePath());
        $controler->installDocman($params['ugroupsMapping'], $params['group_id']);
    }
    function service_is_used($params) {
        if (isset($params['shortname']) && $params['shortname'] == 'docman') {
            if (isset($params['is_used']) && $params['is_used']) {
                $this->installNewDocman(array('ugroupsMapping' => false));
            }
        }
    }
    function soap($arams) {
        require_once('soap.php');
    }

    function myPageBox($params) {
        switch ($params['widget']) {
            case 'plugin_docman_mydocman':
                require_once('Docman_Widget_MyDocman.class.php');
                $params['instance'] = new Docman_Widget_MyDocman($this->getPluginPath());
                break;
            case 'plugin_docman_my_embedded':
                require_once('Docman_Widget_MyEmbedded.class.php');
                $params['instance'] = new Docman_Widget_MyEmbedded($this->getPluginPath());
                break;
            case 'plugin_docman_project_embedded':
                require_once('Docman_Widget_ProjectEmbedded.class.php');
                $params['instance'] = new Docman_Widget_ProjectEmbedded($this->getPluginPath());
                break;
            case 'plugin_docman_mydocman_search':
                require_once('Docman_Widget_MyDocmanSearch.class.php');
                $params['instance'] = new Docman_Widget_MyDocmanSearch($this->getPluginPath());
                break;
            default:
                break;
        }
    }
    function widgets($params) {
        require_once('common/widget/WidgetLayoutManager.class.php');
        if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_USER) {
            $params['codendi_widgets'][] = 'plugin_docman_mydocman';
            $params['codendi_widgets'][] = 'plugin_docman_mydocman_search';
            $params['codendi_widgets'][] = 'plugin_docman_my_embedded';
        }
        if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_GROUP) {
            $params['codendi_widgets'][] = 'plugin_docman_project_embedded';
        }
    }
    function default_widgets_for_new_owner($params) {
        require_once('common/widget/WidgetLayoutManager.class.php');
        if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_USER) {
            $params['widgets'][] = array(
                'name' => 'plugin_docman_mydocman',
                'column' => 1,
                'rank' => 2,
            );
        }
    }
    /**
     * Hook: called by daily codendi script.
     */
    function codendiDaily() {
        require_once('Docman_HTTPController.class.php');
        $controler =& new Docman_HTTPController($this, $this->getPluginPath(), $this->getThemePath());
        $controler->notifyFuturObsoleteDocuments();
    }

    function process() {
        require_once('Docman_HTTPController.class.php');
        $controler =& new Docman_HTTPController($this, $this->getPluginPath(), $this->getThemePath());
        $controler->process();
    }
    
    protected $soapControler;
    public function processSOAP(&$request) {
        require_once('Docman_SOAPController.class.php');
        if ($this->soapControler) {
            $this->soapControler->setRequest($request);
        } else {
            $this->soapControler = new Docman_SOAPController($this, $this->getPluginPath(), $this->getThemePath(), $request);
        }
        return $this->soapControler->process();
    }
     
    function wiki_page_updated($params) {
        require_once('Docman_WikiRequest.class.php');
        $request = new Docman_WikiRequest(array('action' => 'wiki_page_updated',
                                                'wiki_page' => $params['wiki_page'],
                                                'diff_link' => $params['diff_link'],
                                                'group_id'  => $params['group_id'],
                                                'user'      => $params['user'],
                                                'version'   => $params['version']));
        $this->_getWikiController($request)->process(); 
    }

    function wiki_before_content($params) {
        require_once('Docman_WikiRequest.class.php');
        $params['action'] = 'wiki_before_content';
        $request = new Docman_WikiRequest($params);
        $this->_getWikiController($request)->process(); 
    }

    function isWikiPageReferenced($params) {
        require_once('Docman_WikiRequest.class.php');
        $params['action'] = 'check_whether_wiki_page_is_referenced';
        $request = new Docman_WikiRequest($params);
        $this->_getWikiController($request)->process(); 
    }

    function isWikiPageEditable($params) {
        require_once('Docman_WikiRequest.class.php');
        $request = new Docman_WikiRequest($params);
        $this->_getWikiController($request)->process();
    }

    function userCanAccessWikiDocument($params) {
        require_once('Docman_WikiRequest.class.php');
        $params['action'] = 'check_whether_user_can_access';
        $request = new Docman_WikiRequest($params);
        $this->_getWikiController($request)->process(); 
    }

    function getPermsLabelForWiki($params) {
        require_once('Docman_WikiRequest.class.php');
        $params['action'] = 'getPermsLabelForWiki';
        $request = new Docman_WikiRequest($params);
        $this->_getWikiController($request)->process(); 
    }
    
    protected $_wiki_controller;
    protected function _getWikiController($request) {
        if (!$this->_wiki_controller) {
            require_once('Docman_WikiController.class.php');
            $this->_wiki_controller = new Docman_WikiController($this, $this->getPluginPath(), $this->getThemePath(), $request);
            
        } else {
            $this->_wiki_controller->setRequest($request);
        }
        return $this->_wiki_controller;
    }
    
    function ajax_reference_tooltip($params) {
        if ($params['reference']->getServiceShortName() == 'docman') {
            require_once('Docman_CoreController.class.php');
            $request = new Codendi_Request(array(
                'id'       => $params['val'],
                'group_id' => $params['group_id'],
                'action'   => 'ajax_reference_tooltip'
            ));
            $controler =& new Docman_CoreController($this, $this->getPluginPath(), $this->getThemePath(), $request);
            $controler->process();
        }
    }

    /**
     *  hook to display the link to export project data
     *  @param void
     *  @return void
     */
    function project_export_entry($params) {
        // Docman perms
        $url  = '?group_id='.$params['group_id'].'&export=plugin_docman_perms';
        $params['labels']['plugin_eac_docman']                           = $GLOBALS['Language']->getText('plugin_docman','Project_access_permission');
        $params['data_export_links']['plugin_eac_docman']                = $url.'&show=csv';
        $params['data_export_format_links']['plugin_eac_docman']         = $url.'&show=format';
        $params['history_export_links']['plugin_eac_docman']             = null;
        $params['history_export_format_links']['plugin_eac_docman']      = null;
        $params['dependencies_export_links']['plugin_eac_docman']        = null;
        $params['dependencies_export_format_links']['plugin_eac_docman'] = null;
    }

    /**
     *  hook to display the link to export project data
     *  @param void
     *  @return void
     */
    function project_export($params) {
        if($params['export'] == 'plugin_docman_perms') {
            include_once('Docman_PermissionsExport.class.php');
            $request = HTTPRequest::instance();
            $permExport = new Docman_PermissionsExport($params['project']);
            if ($request->get('show') == 'csv') {
                $permExport->toCSV();
            } else { // show = format
                $permExport->renderDefinitionFormat();
            }
            exit;
        }
    }

    /**
     * Hook called when a project is being renamed
     * @param Array $params
     * @return Boolean
     */
    function renameProject($params) {
        $docmanPath = $this->getPluginInfo()->getPropertyValueForName('docman_root').'/';
        //Is this project using docman
        if (is_dir($docmanPath.$params['project']->getUnixName())){
            require_once('Docman_VersionFactory.class.php');
            $version      = new Docman_VersionFactory(); 
            return $version->renameProject($docmanPath, $params['project'], $params['new_name']);
        }
        return true;
    }
    
    /**
     * Hook called before renaming project to check the name validity
     * @param Array $params
     */
    function file_exists_in_data_dir($params) {
        $docmanPath = $this->getPluginInfo()->getPropertyValueForName('docman_root').'/';
        $path = $docmanPath.$params['new_name'];
        
        if (Backend::fileExists($path)) {
            $params['result']= false;
            $params['error'] = $GLOBALS['Language']->getText('plugin_docman','name_validity');
        }
    }

    /**
     * Hook to collect docman disk size usage per project
     *
     * @param array $params
     */
    function plugin_statistics_disk_usage_collect_project($params) {
        $row  = $params['project_row'];
        $root = $this->getPluginInfo()->getPropertyValueForName('docman_root');
        $path = $root.'/'.strtolower($row['unix_group_name']);
        $params['DiskUsageManager']->storeForGroup($row['group_id'], 'plugin_docman', $path);
    }

    /**
     * Hook to list docman in the list of serices managed by disk stats
     * 
     * @param array $params
     */
    function plugin_statistics_disk_usage_service_label($params) {
        $params['services']['plugin_docman'] = 'Docman';
    }
    
    
    /**
     * Hook to list pending documents and/or versions of documents in site admin page
     *
     * @param array $params
     */
    function show_pending_documents($params) {
        $request = HTTPRequest::instance();
        $limit = 10;


        //return all pending versions for given group id
        $offsetVers = $request->getValidated('offsetVers', 'uint', 0);
        if ( !$offsetVers || $offsetVers < 0 ) {
            $offsetVers = 0;
        }
        $version = new Docman_VersionFactory();
        $res = $version->listPendingVersions($params['group_id'], $offsetVers, $limit);
        if (isset($res) && $res) {
            $html = '';
            $html .= '<div class="contenu_onglet" id="contenu_onglet_version">';
            $html .= $this->showPendingVersions($res['versions'], $params['group_id'], $res['nbVersions'], $offsetVers, $limit);
            $html .='</div>';
            $params['id'][] = 'version';
            $params['nom'][] = $GLOBALS['Language']->getText('plugin_docman','deleted_version');
            $params['html'][]= $html;
        }
        //return all pending items for given group id
        $offsetItem = $request->getValidated('offsetItem', 'uint', 0);
        if ( !$offsetItem || $offsetItem < 0 ) {
            $offsetItem = 0;
        }

        $item = new Docman_ItemFactory($params['group_id']);
        $res = $item->listPendingItems($params['group_id'], $offsetItem , $limit);
        if (isset($res) && $res) {
            $html = '';
            $html .= '<div class="contenu_onglet" id="contenu_onglet_item">';
            $html .= $this->showPendingItems($res['items'], $params['group_id'], $res['nbItems'], $offsetItem, $limit);
            $html .='</div>';
            $params['id'][] = 'item';
            $params['nom'][]= $GLOBALS['Language']->getText('plugin_docman','deleted_item');
            $params['html'][]= $html;
        }
    }
    
    function showPendingVersions($versions, $groupId, $nbVersions, $offset, $limit) {
        $hp = Codendi_HTMLPurifier::instance();

        $html ='';
        $title =array();
        $title[] = $GLOBALS['Language']->getText('plugin_docman','item_id');
        $title[] = $GLOBALS['Language']->getText('plugin_docman','doc_title');
        $title[] = $GLOBALS['Language']->getText('plugin_docman','label');
        $title[] = $GLOBALS['Language']->getText('plugin_docman','number');
        $title[] = $GLOBALS['Language']->getText('plugin_docman','delete_date');
        $title[] = $GLOBALS['Language']->getText('plugin_docman','restore_version');

        if ($nbVersions > 0) {

            $html .= '<H3>'.$GLOBALS['Language']->getText('plugin_docman', 'deleted_version').'</H3><P>';
            $html .= html_build_list_table_top ($title);
            $i=1;

            foreach ($versions as $row ){
                $html .= '<tr class="'. html_get_alt_row_color($i++) .'">'.
                '<td>'.$row['item_id'].'</td>'.
                '<td>'.$hp->purify($row['title'], CODENDI_PURIFIER_BASIC, $groupId).'</td>'.
                '<td>'.$hp->purify($row['label']).'</td>'.
                '<td>'.$row['number'].'</td>'.
                '<td>'.format_date($GLOBALS['Language']->getText('system', 'datefmt'),$row['date']).'</td>'.
                '<td align="center"><a href="/plugins/docman/restore_documents.php?group_id='.$groupId.'&func=confirm_restore_version&id='.$row['id'].'&item_id='.$row['item_id'].'" ><IMG SRC="'.util_get_image_theme("trash-x.png").'" onClick="return confirm(\'Confirm restore of this version\')" BORDER=0 HEIGHT=16 WIDTH=16></a></td></tr>';
            }
            $html .= '</TABLE>'; 


            $html .= '<div style="text-align:center" class="'. util_get_alt_row_color($i++) .'">';

            if ($offset > 0) {
                $html .=  '<a href="?group_id='.$groupId.'&focus=version&offsetVers='.($offset -$limit).'">[ '.$GLOBALS['Language']->getText('plugin_docman', 'previous').'  ]</a>';
                $html .= '&nbsp;';
            }
            if (($offset + $limit) < $nbVersions) {
                $html .= '&nbsp;';
                $html .='<a href="?group_id='.$groupId.'&focus=version&offsetVers='.($offset+$limit).'">[ '.$GLOBALS['Language']->getText('plugin_docman', 'next').' ]</a>';
            }
            $html .='<br>'.($offset+$i-2).'/'.$nbVersions.'</br>';
            $html .= '</div>';
          
        } else {
            $html .= $GLOBALS['Response']->addFeedback('info',$GLOBALS['Language']->getText('plugin_docman', 'no_pending_versions'));
        }
        return $html;
    }

    function showPendingItems($res, $groupId, $nbItems, $offset, $limit) {
        $hp = Codendi_HTMLPurifier::instance();
        $uh = UserHelper::instance();

        $html ='';
        $title =array();
        $title[] = $GLOBALS['Language']->getText('plugin_docman','item_id');
        $title[] = $GLOBALS['Language']->getText('plugin_docman','doc_title');
        $title[] = $GLOBALS['Language']->getText('plugin_docman','location');
        $title[] = $GLOBALS['Language']->getText('plugin_docman','owner');
        $title[] = $GLOBALS['Language']->getText('plugin_docman','delete_date');
        $title[] = $GLOBALS['Language']->getText('plugin_docman','restore_item');


        if ($nbItems > 0) {
            $html .= '<H3>'.$GLOBALS['Language']->getText('plugin_docman', 'deleted_item').'</H3><P>';
            $html .= html_build_list_table_top ($title);
            $i=1;
            foreach ($res as $row ){

                $html .='<tr class="'. html_get_alt_row_color($i++) .'">'.
                '<td>'.$row['id'].'</td>'.
                '<td>'. $hp->purify($row['title'], CODENDI_PURIFIER_BASIC, $groupId).'</td>'.
                '<td>'.$hp->purify($row['location']).'</td>'.
                '<td>'.$uh->getDisplayNameFromUserId($row['user']).'</td>'.
                '<td>'.format_date($GLOBALS['Language']->getText('system', 'datefmt'),$row['date']).'</td>'.
                '<td align="center"><a href="/plugins/docman/restore_documents.php?group_id='.$groupId.'&func=confirm_restore_item&id='.$row['id'].'" ><IMG SRC="'.util_get_image_theme("trash-x.png").'" onClick="return confirm(\'Confirm restore of this item\')" BORDER=0 HEIGHT=16 WIDTH=16></a></td></tr>';
            }
            $html .= '</TABLE>'; 

            $html .= '<div style="text-align:center" class="'. util_get_alt_row_color($i++) .'">';

            if ($offset > 0) {
                $html .=  '<a href="?group_id='.$groupId.'&focus=item&offsetItem='.($offset -$limit).'">[ '.$GLOBALS['Language']->getText('plugin_docman', 'previous').'  ]</a>';
                $html .= '&nbsp;';
            }
            if (($offset + $limit) < $nbItems) {
                $html .= '&nbsp;';
                $html .= '<a href="?group_id='.$groupId.'&focus=item&offsetItem='.($offset+$limit).'">[ '.$GLOBALS['Language']->getText('plugin_docman', 'next').' ]</a>';
            }
            $html .='<br>'.($offset +$i-2).'/'.$nbItems.'</br>';
            $html .= '</div>';

        } else {
            $html .= $GLOBALS['Response']->addFeedback('info',$GLOBALS['Language']->getText('plugin_docman', 'no_pending_items'));
        }
        return $html;
    }

    /**
     * Hook to purge deleted items if their agony ends today
     *
     * @param Array $params
     *
     * @return void
     */
    function purgeFiles(array $params) {
        $itemFactory = new Docman_ItemFactory();
        $itemFactory->purgeDeletedItems($params['time']);

        $versionFactory = new Docman_VersionFactory();
        $versionFactory->purgeDeletedVersions($params['time']);
    }

}

?>
