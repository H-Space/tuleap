<?php
/**
 * Copyright (c) Sogilis, 2015. All Rights Reserved.
 * Copyright (c) Enalean, 2016. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

class FRSXMLImporter {

    const MAPPING_KEY = 'frs_release_mapping';

    /** @var XML_RNGValidator */
    private $xml_validator;

    /** @var Logger */
    private $logger;

    /** @var FRSPackageFactory */
    private $package_factory;

    /** @var FRSReleaseFactory */
    private $release_factory;

    /** @var FRSFileFactory */
    private $file_factory;

    /** @var User\XML\Import\IFindUserFromXMLReference */
    private $user_finder;

    /** @var FRSFileTypeDao */
    private $filetype_dao;

    /** @var FRSProcessorDao */
    private $processor_dao;

    /** @var PermissionsManager */
    private $permission_manager;

    /** @var UGroupManager */
    private $ugroup_manager;

    public function __construct(
        Logger $logger,
        XML_RNGValidator $xml_validator,
        FRSPackageFactory $package_factory,
        FRSReleaseFactory $release_factory,
        FRSFileFactory $file_factory,
        User\XML\Import\IFindUserFromXMLReference $user_finder,
        UGroupManager $ugroup_manager,
        XMLImportHelper $xml_import_helper,
        FRSProcessorDao $processor_dao = null,
        FRSFileTypeDao $filetype_dao = null,
        PermissionsManager $permission_manager = null)
    {
        $this->logger = new WrapperLogger($logger, "FRSXMLImporter");
        $this->xml_validator = $xml_validator;
        $this->package_factory = $package_factory;
        $this->release_factory = $release_factory;
        $this->file_factory = $file_factory;
        $this->user_finder = $user_finder;
        $this->filetype_dao = $filetype_dao;
        $this->processor_dao = $processor_dao;
        $this->permission_manager = $permission_manager;
        $this->ugroup_manager = $ugroup_manager;
        $this->xml_import_helper = $xml_import_helper;
    }

    private function getFileTypeDao(){
        if(empty($this->filetype_dao)) {
            $this->filetype_dao = new FRSFileTypeDao();
        }
        return $this->filetype_dao;
    }

    private function getProcessorDao() {
        if(empty($this->processor_dao)) {
            $this->processor_dao = new FRSProcessorDao();
        }
        return $this->processor_dao;
    }

    private function getUGroupManager() {
        if(empty($this->ugroup_manager)) {
            $this->ugroup_manager = UGroupManager::instance();
        }
        return $this->ugroup_manager;
    }

    private function getPermissionsManager() {
        if(empty($this->permission_manager)) {
            $this->permission_manager = PermissionsManager::instance();
        }
        return $this->permission_manager;
    }

    public function import(Project $project, SimpleXMLElement $xml, $extraction_path, array &$frs_release_mapping)
    {
        $xml_frs = $xml->frs;
        if(!$xml_frs) {
            return true;
        }

        $created_id_map = array( 'package' => array() );
        foreach($xml_frs->package as $xml_pkg) {
            $this->importPackage($project, $xml_pkg, $extraction_path, $created_id_map, $frs_release_mapping);
        }

        if(isset($xml_frs->administrators)) {
            foreach($xml_frs->administrators->user as $xml_admin) {
                $this->importAdministrator($project, $xml_admin);
            }
        }
        EventManager::instance()->processEvent(
            Event::IMPORT_COMPAT_REF_XML,
            array(
                'logger'          => $this->logger,
                'created_refs'    => $created_id_map,
                'service_name'    => 'frs',
                'xml_content'     => $xml->frs->references,
                'project'         => $project,
            )
        );
        return true;
    }

    private function importAdministrator(Project $project, SimpleXMLElement $xml_admin) {
        $user = $this->xml_import_helper->getUser($xml_admin);
        if(!$user->isMember($project->getId())) {
            $this->logger->warn("User '{$user->getName()}' is not member of the project. Skipping admin import...");
            return;
        }

        $this->logger->debug("Setting {$user->getName()} as FRS administrator.");
        $frs_admin_group = $this->ugroup_manager->getUGroupByName($project, ProjectUGroup::$normalized_names[ProjectUGroup::FILE_MANAGER_ADMIN]);
        $frs_admin_group->addUser($user);
    }

    private function importPackage(
        Project $project,
        SimpleXMLElement $xml_pkg,
        $extraction_path,
        array &$created_id_map,
        array &$frs_release_mapping
    ) {
        $attrs   = $xml_pkg->attributes();
        $id      = isset($attrs['id']) ? (string) $attrs['id'] : null;
        $rank    = isset($attrs['rank']) ? $attrs['rank'] : 'end';
        $hidden  = isset($attrs['hidden']) ? $attrs['hidden'] : 'false';
        $hidden  = $hidden == 'true' || $hidden == '1';
        $package = new FRSPackage();
        $package->setGroupId($project->getId());
        $package->setName((string) $attrs['name']);
        $package->setStatusID($hidden ? FRSPackage::STATUS_HIDDEN : FRSPackage::STATUS_ACTIVE);
        $package->setRank($rank);
        $package->setApproveLicense(true);
        $package->setPackageID($this->package_factory->create($package->toArray()));

        $read_perms = array();
        foreach($xml_pkg->{'read-access'} as $perm) {
            $ugroup_name = (string) $perm->ugroup;
            $ugroup = $this->getUGroupManager()->getUGroupByName($project, $ugroup_name);
            $read_perms[] = $ugroup->getId();
        }
        $this->getPermissionsManager()->savePermissions($project, $package->getPackageID(), FRSPackage::PERM_READ, $read_perms);

        foreach($xml_pkg->children() as $xml_rel) {
            if($xml_rel->getName() != "release") continue;
            $this->importRelease($project, $package, $xml_rel, $extraction_path, $frs_release_mapping, $created_id_map);
        }
        if($id != null) {
            if(isset($created_id_map[$id])) {
                $this->logger->error("You already referenced a package with the id $id.");
            } else {
                $created_id_map['package'][$id] = $package->getPackageID();
            }
        }
    }

    private function importRelease(
        Project $project,
        FRSPackage $package,
        SimpleXMLElement $xml_rel,
        $extraction_path,
        array &$frs_release_mapping,
        array &$created_id_map
    ) {
        $user  = $this->user_finder->getUser($xml_rel->user);
        $attrs = $xml_rel->attributes();
        $id    = isset($attrs['id']) ? (string) $attrs['id'] : null;

        $release = new FRSRelease();
        $release->setProject($project);
        $release->setReleaseDate(strtotime($attrs['time']));
        $release->setName((string)$attrs['name']);
        $release->setStatusID(FRSRelease::STATUS_ACTIVE);
        $release->setPackageID($package->getPackageID());
        $release->setNotes((string) $xml_rel->notes);
        $release->setChanges((string) $xml_rel->changes);
        $release->setPreformatted($attrs['preformatted'] == '1' || $attrs['preformatted'] == 'true');
        $release->setReleasedBy($user->getId());

        $created_release_id = $this->release_factory->create($release->toArray());
        $release->setReleaseID($created_release_id);

        if (isset($attrs['artifact_id'])) {
            $frs_release_mapping[$created_release_id] = (string) $attrs['artifact_id'];
        }

        $read_perms = array();
        foreach($xml_rel->{'read-access'} as $perm) {
            $ugroup_name = (string) $perm->ugroup;
            $ugroup = $this->getUGroupManager()->getUGroupByName($project, $ugroup_name);
            $read_perms[] = $ugroup->getId();
        }
        $this->getPermissionsManager()->savePermissions($project, $release->getReleaseID(), FRSRelease::PERM_READ, $read_perms);

        foreach($xml_rel->xpath('file') as $xml_file) {
            $this->importFile($project, $release, $user, $xml_file, $extraction_path);
        }

        if($id != null) {
            if(isset($created_id_map['release'][$id])) {
                $this->logger->error("You already referenced a release with the id $id.");
            } else {
                $created_id_map['release'][$id] = $release->getReleaseID();
            }
        }
    }

    private function importFile(Project $project, FRSRelease $release, PFUser $user, SimpleXMLElement $xml_file, $extraction_path) {
        $user  = empty($xml_file->user) ? $user : $this->user_finder->getUser($xml_file->user);
        $attrs = $xml_file->attributes();
        $src   = $extraction_path . '/' . $attrs['src'];
        $name  = isset($attrs['name']) ? (string)$attrs['name'] : basename($src);
        $md5   = strtolower(md5_file($src));
        $time  = strtotime($attrs['release-time']);
        $date  = strtotime($attrs['post-date']);
        $desc  = "";

        $type_id = null;
        if(isset($attrs['filetype']) && !empty($attrs['filetype'])) {
            $type_id = $this->getFileTypeDao()->searchTypeId($attrs['filetype']);
            if(is_null($type_id)) {
                throw new Exception("Invalid filetype '{$attrs['filetype']}'");
            }
        }

        $proc_id = null;
        if(isset($attrs['arch']) && !empty($attrs['arch'])) {
            $proc_id = $this->getProcessorDao()->searchProcessorId($project->getID(), $attrs['arch']);
            if(is_null($proc_id)) {
                throw new Exception("Invalid architecture '{$attrs['arch']}'");
            }
        }

        foreach($xml_file->children() as $elem) {
            if($elem->getName() != "description") continue;
            $desc .= (string) $elem;
        }

        if(isset($attrs['md5sum'])) {
            $expected_md5 = strtolower($attrs['md5sum']);
            if($expected_md5 != $md5) throw new Exception(
                "Import of file $src failed because the file is corrupted ".
                "(expected MD5 $expected_md5, got $md5)");
        }

        $dirPath = $this->file_factory->getSrcDir($project);
        $dest = "$dirPath/$name";
        if(!copy($src, $dest)) {
            throw new Exception("Could not copy $src to $dest");
        }

        $newFile = new FRSFile();
        $newFile->setGroup($project);
        $newFile->setRelease($release);
        $newFile->setFileName($name);
        // hardcoded 100 constant. See src/www/include/html.php function
        // html_build_multiple_select_box_from_array()
        $newFile->setProcessorID(is_null($proc_id) ? 100 : $proc_id);
        $newFile->setTypeID(is_null($type_id) ? 100 : $type_id);
        $newFile->setReferenceMd5($md5);
        $newFile->setComputedMd5($md5);
        $newFile->setUserId($user->getId());
        $newFile->setComment($desc);
        $newFile->setReleaseTime($time);
        $newFile->setPostDate($date);
        $this->file_factory->createFile($newFile);
    }
}
