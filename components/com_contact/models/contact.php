<?php
/**
 * @version		$Id: contact.php 20806 2011-02-21 19:44:59Z dextercowley $
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');

/**
 * @package		Joomla.Site
 * @subpackage	com_contact
 * @since 1.5
 */
class ContactModelContact extends JModelItem
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_contact.contact';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = JRequest::getInt('id');
		$this->setState('contact.id', $pk);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$user = JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_contact')) &&  (!$user->authorise('core.edit', 'com_contact'))){
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}

	/**
	 * Gets a list of contacts
	 * @param array
	 * @return mixed Object or null
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('contact.id');

		if ($this->_item === null) {
			$this->_item = array();
		}

		if (!isset($this->_item[$pk])) {
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true);

				$query->select($this->getState('item.select', 'a.*') . ','
				. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
				. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END AS catslug ');
				$query->from('#__contact_details AS a');

				// Join on category table.
				$query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access');
				$query->join('LEFT', '#__categories AS c on c.id = a.catid');


				// Join over the categories to get parent category titles
				$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias');
				$query->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

				$query->where('a.id = ' . (int) $pk);

				// Filter by start and end dates.
				$nullDate = $db->Quote($db->getNullDate());
				$nowDate = $db->Quote(JFactory::getDate()->toMySQL());


				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');
				if (is_numeric($published)) {
					$query->where('(a.published = ' . (int) $published . ' OR a.published =' . (int) $archived . ')');
					$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')');
					$query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if ($error = $db->getErrorMsg()) {
					throw new JException($error);
				}

				if (empty($data)) {
					throw new JException(JText::_('COM_CONTACT_ERROR_CONTACT_NOT_FOUND'), 404);
				}

				// Check for published state if filter set.
				if (((is_numeric($published)) || (is_numeric($archived))) && (($data->published != $published) && ($data->published != $archived)))
				{
					JError::raiseError(404, JText::_('COM_CONTACT_ERROR_CONTACT_NOT_FOUND'));
				}

				// Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($data->params);
				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				$registry = new JRegistry;
				$registry->loadJSON($data->metadata);
				$data->metadata = $registry;

				// Compute access permissions.
				if ($access = $this->getState('filter.access')) {
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else {
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user = JFactory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					if ($data->catid == 0 || $data->category_access === null) {
						$data->params->set('access-view', in_array($data->access, $groups));
					}
					else {
						$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
					}
				}

				$this->_item[$pk] = $data;
			}
			catch (JException $e)
			{
				$this->setError($e);
				$this->_item[$pk] = false;
			}

		}
		if ($this->_item[$pk])
		{
			if ($extendedData = $this->getContactQuery($pk)) {
				$this->_item[$pk]->articles = $extendedData->articles;
				$this->_item[$pk]->profile = $extendedData->profile;
			}
		}
  		return $this->_item[$pk];

	}




	protected function  getContactQuery($pk = null)
	{
		// TODO: Cache on the fingerprint of the arguments
		$db		= $this->getDbo();
		$user	= JFactory::getUser();
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('contact.id');

		$query	= $db->getQuery(true);
		if ($pk) {
			$query->select('a.*, cc.access as category_access, cc.title as category_name, '
			. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
			. ' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(\':\', cc.id, cc.alias) ELSE cc.id END AS catslug ');

			$query->from('#__contact_details AS a');

			$query->join('INNER', '#__categories AS cc on cc.id = a.catid');

			$query->where('a.id = ' . (int) $pk);
			$published = $this->getState('filter.published');
			$archived = $this->getState('filter.archived');
			if (is_numeric($published)) {
				$query->where('a.published IN (1,2)');
				$query->where('cc.published IN (1,2)');
			}
			$groups		= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');

			try {
				$db->setQuery($query);
				$result = $db->loadObject();
	
				if ($error = $db->getErrorMsg()) {
					throw new Exception($error);
				}
	
				if (empty($result)) {
						throw new JException(JText::_('COM_CONTACT_ERROR_CONTACT_NOT_FOUND'), 404);
				}

			// If we are showing a contact list, then the contact parameters take priority
			// So merge the contact parameters with the merged parameters
				if ($this->getState('params')->get('show_contact_list')) {
					$registry = new JRegistry;
					$registry->loadJSON($result->params);
					$this->getState('params')->merge($registry);
				}
			} catch (Exception $e) {
				$this->setError($e);
				return false;
			}

			if ($result) {
				$user	= JFactory::getUser();
				$groups	= implode(',', $user->getAuthorisedViewLevels());
				//get the content by the linked user
				$query	= $db->getQuery(true);
				$query->select('id, title, state, access, created'); 
				$query->from('#__content');
				$query->where('created_by = '.(int)$result->user_id);
				$query->where('access IN ('. $groups.')');
				$query->order('state DESC, created DESC');
				if (is_numeric($published)) {
					$query->where('state IN (1,2)');
				}
				$db->setQuery($query, 0, 10);
				$articles = $db->loadObjectList();
				$result->articles = $articles;

			//get the profile information for the linked user
			if ($result) {
					require_once JPATH_ADMINISTRATOR.'/components/com_users/models/user.php';
					$userModel = JModel::getInstance('User','UsersModel',array('ignore_request' => true));
						$data = $userModel->getItem((int)$result->user_id);
			
					JPluginHelper::importPlugin('user');
					$form = new JForm('com_users.profile');
					// Get the dispatcher.
					$dispatcher	= JDispatcher::getInstance();
	
					// Trigger the form preparation event.
					$dispatcher->trigger('onContentPrepareForm', array($form, $data));
					// Trigger the data preparation event.
					$dispatcher->trigger('onContentPrepareData', array('com_users.profile', $data));
	
					// Load the data into the form after the plugins have operated.
					$form->bind($data);
					$result->profile = $form;
				}

			$this->contact = $result;
			return $result;
			}
		}
	}
}

