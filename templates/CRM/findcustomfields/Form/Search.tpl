{*
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
*}
{crmStyle ext=biz.lcdservices.findcustomfields file=css/findcustomfields_civicrm.css}
{* Search form and results for Fields *}
{if $action eq 1 or $action eq 2 or $action eq 4}
  {include file="CRM/Custom/Form/Group.tpl"}
{elseif $action eq 1024}
  {include file="CRM/Custom/Form/Preview.tpl"}
{elseif $action eq 8}
  {include file="CRM/Custom/Form/DeleteGroup.tpl"}
{else}

  {assign var="showBlock" value="'searchForm'"}
  {assign var="hideBlock" value="'searchForm_show'"}
  <div class="crm-block crm-form-block crm-custom-search-form-block">
    <div class="crm-accordion-wrapper crm-custom_search_form-accordion">
      <div class="crm-accordion-header crm-master-accordion-header">
        {ts}Custom Data{/ts}
      </div><!-- /.crm-accordion-header -->
      <div class="crm-accordion-body">
        {strip}
          <table class="form-layout">
            <tr>
              <td class="font-size12pt" colspan="2">
                  {$form.title.label}&nbsp;&nbsp;{$form.title.html|crmAddClass:'medium'}
              </td>
              <td class="font-size12pt" colspan="2">
                  {$form.fields_name.label}&nbsp;&nbsp;{$form.fields_name.html|crmAddClass:'medium'}
              </td>
              <td class="font-size12pt" colspan="2">
                  {$form.is_active.label}&nbsp;&nbsp;{$form.is_active.html|crmAddClass:'medium'}
              </td>
              <td class="font-size12pt" colspan="2">
                  {$form.extends.label}&nbsp;&nbsp;{$form.extends.html|crmAddClass:'medium'}
              </td>
            </tr>
            <tr>
              <td>
              {include file="CRM/common/formButtons.tpl" location="bottom"}
              <div class="crm-submit-buttons reset-advanced-search">
                <a href="{crmURL p='civicrm/custom/findcustomfields' q='reset=1'}" id="resetSearch" class="crm-hover-button" title="{ts}Clear all search criteria{/ts}">
                  <i class="crm-i fa-undo"></i>
                  &nbsp;{ts}Reset Form{/ts}
                </a>
              </div>
            </td>
            </tr>
            </table>
        {/strip}
      </div><!-- /.crm-accordion-body -->
    </div><!-- /.crm-accordion-wrapper -->
  </div><!-- /.crm-form-block -->

  {if $rowsEmpty || $rows}
    <div class="crm-content-block">
    {if $rowsEmpty}
    <div class="crm-results-block crm-results-block-empty">
      There are no custom groups matching your search criteria.
    </div>
    {/if}

    {if $rows}
      <div class="crm-results-block">
        {* Search request has returned 1 or more matching rows. *}
        {* This section handles form elements for action task select and submit *}
        {* This section displays the rows along and includes the paging controls *}
        <div class="help">
          {ts}Custom data is stored in custom fields. Custom fields are organized into logically related custom data sets (e.g. Volunteer Info). Use custom fields to collect and store custom data which are not included in the standard CiviCRM forms. You can create one or many sets of custom fields.{/ts} {docURL page="user/organising-your-data/custom-fields"}
          <p>{ts 1=$coreCustomFieldsUrl}You are using the Find Custom Fields extension, which is a drop-in replacement for the standard custom data management tool. If you would like to access the standard form, <a href='%1'>click here.</a>{/ts}</p>
        </div>

        {include file="CRM/findcustomfields/Form/Selector.tpl" context="Search"}
        {* END Actions/Results section *}
      </div>
    {/if}

    </div>
  {/if}
{/if}
