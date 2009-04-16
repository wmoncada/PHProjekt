<?php
/**
 * Project-Module Relation
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Manage the relation between the Projects and the active modules
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_Models_ProjectModulePermissions extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Return all the modules in an array and the permission if exists
     *
     * @param int $projectId The Project id
     *
     * @return array
     */
    function getProjectModulePermissionsById($projectId)
    {
        $modules = array();
        $model   = Phprojekt_Loader::getLibraryClass('Phprojekt_Module_Module');
        foreach ($model->fetchAll(' active = 1 AND (save_type = 0 OR save_type = 2) ', ' name ASC ') as $module) {
            $modules['data'][$module->id] = array();
            $modules['data'][$module->id]['id']        = $module->id;
            $modules['data'][$module->id]['name']      = $module->name;
            $modules['data'][$module->id]['label']     = Phprojekt::getInstance()->translate($module->label);
            $modules['data'][$module->id]['inProject'] = false;
        }
        $where  = ' project_module_permissions.project_id = ' . $projectId;
        $where .= ' AND module.active = 1 ';
        $order  = ' module.name ASC';
        $select = ' module.id AS module_id ';
        $join   = ' RIGHT JOIN module ON ( module.id = project_module_permissions.module_id ';
        $join  .= ' AND (module.save_type = 0 OR module.save_type = 2) )';
        foreach ($this->fetchAll($where, $order, null, null, $select, $join) as $right) {
            $modules['data'][$right->moduleId]['inProject'] = true;
        }
        return $modules;
    }

    /**
     * Delete a project-module relation
     *
     * @param int $moduleId  The Module Id to delete
     *
     * @return void
     */
    public function deleteModuleRelation($moduleId)
    {
        $where = $this->getAdapter()->quoteInto(' module_id = ? ', (int) $moduleId);
        foreach ($this->fetchAll($where) as $relation) {
            $relation->delete();
        }
    }
}