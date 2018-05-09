<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2018
 */

/**
 * Class to render contribution search results.
 */
class CRM_findcustomfields_Selector_Search extends CRM_Core_Selector_Base implements CRM_Core_Selector_API {

  /**
   * Array of action links.
   *
   * @var array
   */
  private static $_actionLinks;

  /**
   * We use desc to remind us what that column is, name is used in the tpl
   *
   * @var array
   */
  static $_columnHeaders;
  
  /**
   * Are we restricting ourselves to a single contact
   *
   * @var boolean
   */
  protected $_limit = NULL;
  
  /**
   * What context are we being invoked from
   *
   * @var string
   */
  protected $_context = NULL;

  /**
   * QueryParams is the array returned by exportValues called on
   * the HTML_QuickForm_Controller for that page.
   *
   * @var array
   */
  public $_queryParams;

  /**
   * Represent the type of selector
   *
   * @var int
   */
  protected $_action;

  /**
   * The query object
   *
   * @var string
   */
  protected $_query;

  /**
   * Class constructor.
   *
   * @param array $queryParams
   *   Array of parameters for query.
   * @param \const|int $action - action of search basic or advanced.
   * @param string $contributionClause
   *   If the caller wants to further restrict the search (used in contributions).
   * @param bool $single
   *   Are we dealing only with one contact?.
   * @param int $limit
   *   How many contributions do we want returned.
   *
   * @param string $context
   * @param null $compContext
   *
   * @return \CRM_Contribute_Selector_Search
   */
  public function __construct(
    &$queryParams,
    $action = CRM_Core_Action::NONE,
    $limit = NULL,
    $context = 'search'
  ) {
    // submitted form values
    $this->_queryParams = &$queryParams;
    $this->_limit = $limit;
    // type of selector
    $this->_action = $action;
    $params = array();
    $whereClause = $this->whereClause($params);
    $this->_params = $params;
    $sql = "SELECT g.*, GROUP_CONCAT(f.label separator ', ') as label, GROUP_CONCAT(f.id separator ', ') as fid FROM civicrm_custom_group g LEFT JOIN civicrm_custom_field f ON g.id = f.custom_group_id WHERE $whereClause GROUP BY g.id";
    $this->_query = $sql;
  }

  /**
   * This method returns the links that are given for each search row.
   * currently the links added for each row are
   *
   * - View
   * - Edit
   *
   * @param int $componentId
   * @param null $componentAction
   * @param null $key
   * @param null $compContext
   *
   * @return array
   */
  public static function &actionLinks() {
    // check if variable _actionsLinks is populated
    if (!isset(self::$_actionLinks)) {
      self::$_actionLinks = array(
        CRM_Core_Action::BROWSE => array(
          'name' => ts('View and Edit Custom Fields'),
          'url' => 'civicrm/admin/custom/group/field',
          'qs' => 'reset=1&action=browse&gid=%%id%%',
          'title' => ts('View and Edit Custom Fields'),
        ),
        CRM_Core_Action::PREVIEW => array(
          'name' => ts('Preview'),
          'url' => 'civicrm/admin/custom/group',
          'qs' => 'action=preview&reset=1&id=%%id%%',
          'title' => ts('Preview Custom Data Set'),
        ),
        CRM_Core_Action::UPDATE => array(
          'name' => ts('Settings'),
          'url' => 'civicrm/admin/custom/group',
          'qs' => 'action=update&reset=1&id=%%id%%',
          'title' => ts('Edit Custom Set'),
        ),
        CRM_Core_Action::DISABLE => array(
          'name' => ts('Disable'),
          'ref' => 'crm-enable-disable',
          'title' => ts('Disable Custom Set'),
        ),
        CRM_Core_Action::ENABLE => array(
          'name' => ts('Enable'),
          'ref' => 'crm-enable-disable',
          'title' => ts('Enable Custom Set'),
        ),
        CRM_Core_Action::DELETE => array(
          'name' => ts('Delete'),
          'url' => 'civicrm/admin/custom/group',
          'qs' => 'action=delete&reset=1&id=%%id%%',
          'title' => ts('Delete Custom Set'),
        ),
      );
    }
    return self::$_actionLinks;
  }

  /**
   * Getter for array of the parameters required for creating pager.
   *
   * @param $action
   * @param array $params
   */
  public function getPagerParams($action, &$pager_params) {
    $pager_params['status'] = ts('Contribution') . ' %%StatusMessage%%';
    $pager_params['csvString'] = NULL;
    if ($this->_limit) {
      $pager_params['rowCount'] = $this->_limit;
    }
    else {
      $pager_params['rowCount'] = CRM_Utils_Pager::ROWCOUNT;
    }

    $pager_params['buttonTop'] = 'PagerTopButton';
    $pager_params['buttonBottom'] = 'PagerBottomButton';
  }

  /**
   * Returns total number of rows for the query.
   *
   * @param string $action
   *
   * @return int
   *   Total number of rows
   */
  public function getTotalCount($action) {
    $dao = CRM_Core_DAO::executeQuery($this->_query, $this->_params);
    return $dao->N;
  }

  /**
   * Returns all the rows in the given offset and rowCount.
   *
   * @param string $action
   *   The action being performed.
   * @param int $offset
   *   The row number to start from.
   * @param int $rowCount
   *   The number of rows to return.
   * @param string $sort
   *   The sql string that describes the sort order.
   * @param string $output
   *   What should the result set include (web/email/csv).
   *
   * @return int
   *   the total number of rows for this action
   */
  public function &getRows($action, $offset, $rowCount, $sort, $output = NULL) {
    // process the result of the query
    if (!empty($sort)) {
      $sort = $sort->orderBy();
      $this->_query .= " ORDER BY $sort ";
    }
    if ($rowCount > 0 && $offset >= 0) {
      $offset = CRM_Utils_Type::escape($offset, 'Int');
      $rowCount = CRM_Utils_Type::escape($rowCount, 'Int');
      $this->_query .= " LIMIT $offset, $rowCount ";
    }
    
    $dao = CRM_Core_DAO::executeQuery($this->_query, $this->_params);
    $fields = CRM_Core_DAO_CustomGroup::fields();      
    $customGroup = array();
    $customGroupExtends = CRM_Core_SelectValues::customGroupExtends();
    $customGroupStyle = CRM_Core_SelectValues::customGroupStyle();
    while ($dao->fetch()) {
      $id = $dao->id;
      $customGroup[$id] = array();
      foreach ($fields as $name => $value) {
        $dbName = $value['name'];
        if (isset($dao->$dbName) && $dao->$dbName !== 'null') {
          $customGroup[$id][$dbName] = $dao->$dbName;
          if ($name != $dbName) {
            $customGroup[$id][$name] = $dao->$dbName;
          }
        }
      }
      // form all action links
      $action = array_sum(array_keys(self::actionLinks()));

      // update enable/disable links depending on custom_group properties.
      if ($dao->is_active) {
        $action -= CRM_Core_Action::ENABLE;
      }
      else {
        $action -= CRM_Core_Action::DISABLE;
      }
      $customGroup[$id]['order'] = $customGroup[$id]['weight'];
      $customGroup[$id]['action'] = CRM_Core_Action::formLink(self::actionLinks(), $action,
        array('id' => $id),
        ts('more'),
        FALSE,
        'customGroup.row.actions',
        'CustomGroup',
        $id
      );
      if (!empty($customGroup[$id]['style'])) {
        $customGroup[$id]['style_display'] = $customGroupStyle[$customGroup[$id]['style']];
      }
      $customGroup[$id]['extends_display'] = $customGroupExtends[$customGroup[$id]['extends']];
      $fields_labels = explode(",", $dao->label);
      $fields_id = explode(",", $dao->fid);
      $customGroup[$id]['custom_fields_name'] = $fields_labels;
      $customGroup[$id]['custom_fields_id'] = $fields_id;
    }
    
    //fix for Displaying subTypes
    $subTypes = array();

    $subTypes['Activity'] = CRM_Core_PseudoConstant::activityType(FALSE, TRUE, FALSE, 'label', TRUE);
    $subTypes['Contribution'] = CRM_Contribute_PseudoConstant::financialType();
    $subTypes['Membership'] = CRM_Member_BAO_MembershipType::getMembershipTypes(FALSE);
    $subTypes['Event'] = CRM_Core_OptionGroup::values('event_type');
    $subTypes['Grant'] = CRM_Core_OptionGroup::values('grant_type');
    $subTypes['Campaign'] = CRM_Campaign_PseudoConstant::campaignType();
    $subTypes['Participant'] = array();
    $subTypes['ParticipantRole'] = CRM_Core_OptionGroup::values('participant_role');;
    $subTypes['ParticipantEventName'] = CRM_Event_PseudoConstant::event();
    $subTypes['ParticipantEventType'] = CRM_Core_OptionGroup::values('event_type');
    $subTypes['Individual'] = CRM_Contact_BAO_ContactType::subTypePairs('Individual', FALSE, NULL);
    $subTypes['Household'] = CRM_Contact_BAO_ContactType::subTypePairs('Household', FALSE, NULL);
    $subTypes['Organization'] = CRM_Contact_BAO_ContactType::subTypePairs('Organization', FALSE, NULL);

    $relTypeInd = CRM_Contact_BAO_Relationship::getContactRelationshipType(NULL, 'null', NULL, 'Individual');
    $relTypeOrg = CRM_Contact_BAO_Relationship::getContactRelationshipType(NULL, 'null', NULL, 'Organization');
    $relTypeHou = CRM_Contact_BAO_Relationship::getContactRelationshipType(NULL, 'null', NULL, 'Household');

    $allRelationshipType = array();
    $allRelationshipType = array_merge($relTypeInd, $relTypeOrg);
    $allRelationshipType = array_merge($allRelationshipType, $relTypeHou);

    //adding subtype specific relationships CRM-5256
    $relSubType = CRM_Contact_BAO_ContactType::subTypeInfo();
    foreach ($relSubType as $subType => $val) {
      $subTypeRelationshipTypes = CRM_Contact_BAO_Relationship::getContactRelationshipType(NULL, NULL, NULL, $val['parent'],
        FALSE, 'label', TRUE, $subType
      );
      $allRelationshipType = array_merge($allRelationshipType, $subTypeRelationshipTypes);
    }

    $subTypes['Relationship'] = $allRelationshipType;

    $cSubTypes = CRM_Core_Component::contactSubTypes();
    $contactSubTypes = array();
    foreach ($cSubTypes as $key => $value) {
      $contactSubTypes[$key] = $key;
    }

    $subTypes['Contact'] = $contactSubTypes;

    CRM_Core_BAO_CustomGroup::getExtendedObjectTypes($subTypes);

    foreach ($customGroup as $key => $values) {
      $subValue = CRM_Utils_Array::value('extends_entity_column_value', $customGroup[$key]);
      $subName = CRM_Utils_Array::value('extends_entity_column_id', $customGroup[$key]);
      $type = CRM_Utils_Array::value('extends', $customGroup[$key]);
      if ($subValue) {
        $subValue = explode(CRM_Core_DAO::VALUE_SEPARATOR,
          substr($subValue, 1, -1)
        );
        $colValue = NULL;
        foreach ($subValue as $sub) {
          if ($sub) {
            if ($type == 'Participant') {
              if ($subName == 1) {
                $colValue = $colValue ? $colValue . ', ' . $subTypes['ParticipantRole'][$sub] : $subTypes['ParticipantRole'][$sub];
              }
              elseif ($subName == 2) {
                $colValue = $colValue ? $colValue . ', ' . $subTypes['ParticipantEventName'][$sub] : $subTypes['ParticipantEventName'][$sub];
              }
              elseif ($subName == 3) {
                $colValue = $colValue ? $colValue . ', ' . $subTypes['ParticipantEventType'][$sub] : $subTypes['ParticipantEventType'][$sub];
              }
            }
            elseif ($type == 'Relationship') {
              $colValue = $colValue ? $colValue . ', ' . $subTypes[$type][$sub . '_a_b'] : $subTypes[$type][$sub . '_a_b'];
              if (isset($subTypes[$type][$sub . '_b_a'])) {
                $colValue = $colValue ? $colValue . ', ' . $subTypes[$type][$sub . '_b_a'] : $subTypes[$type][$sub . '_b_a'];
              }
            }
            else {
              $colValue = $colValue ? ($colValue . (isset($subTypes[$type][$sub]) ? ', ' . $subTypes[$type][$sub] : '')) : (isset($subTypes[$type][$sub]) ? $subTypes[$type][$sub] : '');
            }
          }
        }
        $customGroup[$key]["extends_entity_column_value"] = $colValue;
      }
      else {
        if (is_array(CRM_Utils_Array::value($type, $subTypes))) {
          $customGroup[$key]["extends_entity_column_value"] = ts("Any");
        }
      }
    }

    $returnURL = CRM_Utils_System::url('civicrm/custom/findcustomfields', "reset=1");
    CRM_Utils_Weight::addOrder($customGroup, 'CRM_Core_DAO_CustomGroup',
      'id', $returnURL
    );
    $rows = $customGroup;
    return $rows;
  }

  /**
   * Returns the column headers as an array of tuples:
   * (name, sortName (key to the sort array))
   *
   * @param string $action
   *   The action being performed.
   * @param string $output
   *   What should the result set include (web/email/csv).
   *
   * @return array
   *   the column headers that need to be displayed
   */
  public function &getColumnHeaders($action = NULL, $output = NULL) {
    $pre = array();
    self::$_columnHeaders = array(
      array(
            'name' => ts('Set'),
            'sort' => 'title',
            'field_name' => 'title',
            'direction' => CRM_Utils_Sort::DONTCARE,
          ),
           array(
            'name' => ts(''),
            'field_name' => 'fields',
          ),
          array(
            'name' => ts('Enabled'),
            'field_name' => 'is_active',
          ),
          array(
            'name' => ts('Used For'),
            'sort' => 'extends',
            'field_name' => 'extends',
            'direction' => CRM_Utils_Sort::DESCENDING,
          ),
          array(
            'name' => ts('Type'),
            'field_name' => 'extends_entity_column_id',
          ),
          array(
            'name' => ts('Order'),
            'field_name' => 'weight',
          ),
          array(
            'name' => ts('Style'),
            'field_name' => 'style',
          ),
    );
    self::$_columnHeaders
      = array_merge(
        self::$_columnHeaders, array(
          array('desc' => ts('Actions'), 'type' => 'actions'),
        )
      );
    foreach (array_keys(self::$_columnHeaders) as $index) {
      // Add weight & space it out a bit to allow headers to be inserted.
      self::$_columnHeaders[$index]['weight'] = $index * 10;
    }

    return self::$_columnHeaders;
  }

  /**
   * @return mixed
   */
  public function alphabetQuery() {
    return $this->_query->searchQuery(NULL, NULL, NULL, FALSE, FALSE, TRUE);
  }

  /**
   * @return string
   */
  public function &getQuery() {
    return $this->_query;
  }

  /**
   * Name of export file.
   *
   * @param string $output
   *   Type of output.
   *
   * @return string
   *   name of the file
   */
  public function getExportFileName($output = 'csv') {
    return ts('CiviCRM Field Name Search');
  }
  /**
   * @param array $queryParams
   * @param bool $sortBy
   * @param $force
   *
   * @return string
   */
  public function whereClause(&$params) {
    $clauses = array();
    
    $title = CRM_Utils_Array::value('title', $this->_queryParams);   
    $is_active = CRM_Utils_Array::value('is_active', $this->_queryParams);   
    $extends = CRM_Utils_Array::value('extends', $this->_queryParams);   
    $fields_name = CRM_Utils_Array::value('fields_name', $this->_queryParams);
    
    
    if ($title) {
      $clauses[] = "title LIKE %1";
      $params[1] = array('%' . $title . '%', 'String', FALSE);
    }
    
    if ($is_active) {
      if($is_active == 'enabled') {
        $clauses[] = "g.is_active = 1";
      }
      else if( $is_active == 'disabled' ) {
        $clauses[] = "g.is_active = 0";
      }
      else{
        $clauses[] = "g.is_active IN (0, 1)";
      }
    }
    
    if ($extends) {
      $clauses[] = " extends = '$extends'";
    }
    
    if ($fields_name) {
      $clauses[] = "label LIKE %2";
      $params[2] = array('%' . $fields_name . '%', 'String', FALSE);
    }
    return !empty($clauses) ? implode(' AND ', $clauses) : '(1)';
  }
}
