<?php

/**
 * @file
 * Add a table of notes from related contacts.
 *
 * Copyright (C) 2013-15, AGH Strategies, LLC <info@aghstrategies.com>
 * Licensed under the GNU Affero Public License 3.0 (see LICENSE.txt)
 */

require_once 'findcustomfields.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function findcustomfields_civicrm_config(&$config) {
  _findcustomfields_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function findcustomfields_civicrm_xmlMenu(&$files) {
  _findcustomfields_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function findcustomfields_civicrm_install() {
  return _findcustomfields_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function findcustomfields_civicrm_uninstall() {
  return _findcustomfields_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function findcustomfields_civicrm_enable() {
  return _findcustomfields_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function findcustomfields_civicrm_disable() {
  return _findcustomfields_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function findcustomfields_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _findcustomfields_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function findcustomfields_civicrm_managed(&$entities) {
  return _findcustomfields_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_navigationMenu
 */
function findcustomfields_civicrm_navigationMenu(&$navMenu) {
  $pages = array(
    'admin_page' => array(
      'label'      => 'Custom Data',
      'name'       => 'Custom Data',
      'url'        => 'civicrm/admin/custom/group',
      'parent' => array('Administer'),
      'permission' => 'access CiviContribute,administer CiviCRM',
      'operator'   => 'AND',
      'separator'  => NULL,
      'active'     => 1,
    ),
  );
  foreach ($pages as $item) {
    // Check that our item doesn't already exist.
    $menu_item_search = array('url' => $item['url']);
    $menu_items = array();
    CRM_Core_BAO_Navigation::retrieve($menu_item_search, $menu_items);
    if (!empty($menu_items)) {
      $item['url'] = 'civicrm/custom/findcustomfields';
    }
  }
}