<?php
/**
 * Copyright (c) Enalean, 2013. All Rights Reserved.
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

class Git_Driver_Gerrit_MembershipManager {
    const GROUP_CONTRIBUTORS = 'contributors';
    const GROUP_INTEGRATORS  = 'integrators';
    const GROUP_SUPERMEN     = 'supermen';
    const GROUP_OWNERS       = 'owners';

    public static $GERRIT_GROUPS = array(self::GROUP_CONTRIBUTORS => Git::PERM_READ,
                                         self::GROUP_INTEGRATORS  => Git::PERM_WRITE,
                                         self::GROUP_SUPERMEN     => Git::PERM_WPLUS,
                                         self::GROUP_OWNERS       => Git::SPECIAL_PERM_ADMIN);

    private $git_repository_factory;
    private $gerrit_server_factory;
    private $gerrit_driver;
    private $permissions_manager;
    private $ugroup_manager;

    public function __construct(
        GitRepositoryFactory $git_repository_factory,
        Git_Driver_Gerrit $gerrit_driver,
        PermissionsManager $permissions_manager,
        Git_RemoteServer_GerritServerFactory $gerrit_server_factory,
        UGroupManager $ugroup_manager
    ) {
        $this->git_repository_factory = $git_repository_factory;
        $this->gerrit_driver          = $gerrit_driver;
        $this->permissions_manager    = $permissions_manager;
        $this->gerrit_server_factory  = $gerrit_server_factory;
        $this->ugroup_manager         = $ugroup_manager;
    }

    public function updateUserMembership(User $user, $ugroup, Project $project, Git_Driver_Gerrit_MembershipCommand $command) {
        $repositories = $this->getMigratedRepositoriesOfAProject($project);
        foreach ($repositories as $repository) {
            $this->updateUserGerritGroupsAccordingToPermissions($user, $project, $repository, $command);
        }
    }

    private function updateUserGerritGroupsAccordingToPermissions(User $user, Project $project, GitRepository $repository, Git_Driver_Gerrit_MembershipCommand $command) {
        $remote_server   = $this->gerrit_server_factory->getServer($repository);
        $command->permissions_manager = $this->permissions_manager;
        $command->process($remote_server, $user, $project, $repository);
        
    }

    private function getGerritGroupName(Project $project, GitRepository $repo, $group_name) {
        $project_name    = $project->getUnixName();
        $repository_name = $repo->getFullName();

        return "$project_name/$repository_name-$group_name";
    }

    private function getMigratedRepositoriesOfAProject(Project $project) {
        $migrated_repositories = array();
        $repositories          = $this->git_repository_factory->getAllRepositories($project);

        foreach ($repositories as $repository) {
            if ($repository->isMigratedToGerrit()) {
                $migrated_repositories[] = $repository;
            }
        }

        return $migrated_repositories;
    }

    private function getConcernedGerritGroups(User $user, Project $project, GitRepository $repository) {
        $groups_full_names = array();

        foreach (self::$GERRIT_GROUPS as $group_name => $permission) { 
            $groups_with_permission = $this->getUgroupsWithPermission($repository, $permission);
            if (count($groups_with_permission) > 0) {
                if (! $this->isUserInGroups($user, $project, $groups_with_permission)) {
                    $groups_full_names[] = $this->getGerritGroupName($project, $repository, $group_name);
                }
            }
        }

        return $groups_full_names;
    }

    private function isUserInGroups($user, $project, $group_list) {
        $user_groups = $user->getUgroups($project->getID(), null);
        foreach ($user_groups as $user_group) {
            if (in_array($user_group, $group_list)) {
                return true;
            }
        }

        return false;

    }

    private function getUgroupsWithPermission(GitRepository $repository, $permission) {
        $dar_ugroups = $this->permissions_manager->getUgroupIdByObjectIdAndPermissionType($repository->getId(), $permission);
        $ugroups     = array();

        foreach ($dar_ugroups as $row) {
            $ugroups[]     = $row['ugroup_id'];
        }
        
        return $ugroups;
    }
}
?>
