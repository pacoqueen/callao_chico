<?php
/**
 * @version        $Id: xmap_ext.php 28 2011-04-04 01:53:37Z guille $
 * @copyright    Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.base.adapterinstance');

/**
 * xmap_ext installer
 *
 * @package        Joomla.Framework
 * @subpackage    Installer
 * @since        1.5
 */
class JInstallerXmap_ext extends JAdapterInstance
{
    /** @var string install function routing */
    var $route = 'Install';

    protected $manifest = null;
    protected $manifest_script = null;
    protected $name = null;
    protected $scriptElement = null;

    /**
     * Custom loadLanguage method
     *
     * @access    public
     * @param    string    $path the path where to find language files
     * @since    1.6
     */
    public function loadLanguage($path=null)
    {
        $source = $this->parent->getPath('source');
        if (!$source) {
            $this->parent->setPath('source', JPATH_ADMINISTRATOR . '/components/com_xmap/extensions/'.$this->parent->extension->folder);
        }
        $this->manifest = &$this->parent->getManifest();
        $element = $this->manifest->files;
        if ($element)
        {
            $name = '';
            if (count($element->children()))
            {
                foreach ($element->children() as $file)
                {
                    if ((string)$file->attributes()->plugin)
                    {
                        $name = strtolower((string)$file->attributes()->plugin);
                        break;
                    }
                }
            }
            if ($name)
            {
                $extension = "xmap_${group}_${name}";
                $lang =& JFactory::getLanguage();
                $source = $path ? $path : JPATH_ADMINISTRATOR . "/components/com_xmap/extensions/$name";
                $folder = (string)$element->attributes()->folder;
                if ($folder && file_exists("$path/$folder"))
                {
                    $source = "$path/$folder";
                }
                    $lang->load($extension . '.sys', $source, null, false, false)
                ||    $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, false)
                ||    $lang->load($extension . '.sys', $source, $lang->getDefault(), false, false)
                ||    $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false);
            }
        }
    }
    /**
     * Custom install method
     *
     * @access    public
     * @return    boolean    True on success
     * @since    1.5
     */
    public function install()
    {
        // Get a database connector object
        $db = &$this->parent->getDbo();

        // Get the extension manifest object
        $this->manifest = $this->parent->getManifest();

        $xml = $this->manifest;

        /**
         * ---------------------------------------------------------------------------------------------
         * Manifest Document Setup Section
         * ---------------------------------------------------------------------------------------------
         */

        // Set the extensions name
        $name = (string)$xml->name;
        $name = JFilterInput::getInstance()->clean($name, 'string');
        $this->set('name', $name);

        // Get the component description
        $description = (string)$xml->description;
        if ($description) {
            $this->parent->set('message', JText::_($description));
        }
        else {
            $this->parent->set('message', '');
        }

        /*
         * Backward Compatability
         * @todo Deprecate in future version
         */
        $type = (string)$xml->attributes()->type;

        // Set the installation path
        if (count($xml->files->children()))
        {
            foreach ($xml->files->children() as $file)
            {
                if ((string)$file->attributes()->$type)
                {
                    $element = (string)$file->attributes()->$type;
                    break;
                }
            }
        }
        if (!empty ($element)) {
			$element = str_replace('xmap_','',$element);
            $this->parent->setPath('extension_root', JPATH_ADMINISTRATOR.DS.'components/com_xmap/extensions'.DS.$element);
        }
        else
        {
            $this->parent->abort(JText::_('Extension').' '.JText::_($this->route).': '.JText::_('No extension file specified'));
            return false;
        }


        /*
         * Check if we should enable overwrite settings
         */
        // Check to see if a plugin by the same name is already installed
        $query = 'SELECT `extension_id`' .
                ' FROM `#__extensions`' .
                ' WHERE type=\'xmap_ext\' AND folder = '.$db->Quote($element) .
                ' AND element = '.$db->Quote('xmap_'.$element);
        $db->setQuery($query);
        try {
            $db->Query();
        }
        catch(JException $e)
        {
            // Install failed, roll back changes
            $this->parent->abort(JText::_('Extension').' '.JText::_($this->route).': '.$db->stderr(true));
            return false;
        }
        $id = $db->loadResult();

        // if its on the fs...
        if (file_exists($this->parent->getPath('extension_root')) && (!$this->parent->getOverwrite() || $this->parent->getUpgrade()))
        {
            $updateElement = $xml->update;
            // upgrade manually set
            // update function available
            // update tag detected
            if ($this->parent->getUpgrade() || ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'update')) || is_a($updateElement, 'JXMLElement'))
            {
                // force these one
                $this->parent->setOverwrite(true);
                $this->parent->setUpgrade(true);
                if ($id) { // if there is a matching extension mark this as an update; semantics really
                    $this->route = 'Update';
                }
            }
            else if (!$this->parent->getOverwrite())
            {
                // overwrite is set
                // we didn't have overwrite set, find an udpate function or find an update tag so lets call it safe
                $this->parent->abort(JText::_('Extension').' '.JText::_($this->route).': '.JText::_('Another extension is already using directory').': "'.$this->parent->getPath('extension_root').'"');
                return false;
            }
        }

        /**
         * ---------------------------------------------------------------------------------------------
         * Filesystem Processing Section
         * ---------------------------------------------------------------------------------------------
         */

        // If the plugin directory does not exist, lets create it
        $created = false;
        if (!file_exists($this->parent->getPath('extension_root')))
        {
            if (!$created = JFolder::create($this->parent->getPath('extension_root')))
            {
                $this->parent->abort(JText::_('Extension').' '.JText::_($this->route).': '.JText::_('FAILED_TO_CREATE_DIRECTORY').': "'.$this->parent->getPath('extension_root').'"');
                return false;
            }
        }

        /*
         * If we created the plugin directory and will want to remove it if we
         * have to roll back the installation, lets add it to the installation
         * step stack
         */
        if ($created) {
            $this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
        }

        // Copy all necessary files
        if ($this->parent->parseFiles($xml->files, -1) === false)
        {
            // Install failed, roll back changes
            $this->parent->abort();
            return false;
        }

        // Parse optional tags -- media and language files for plugins go in admin app
        $this->parent->parseMedia($xml->media, 1);
        $this->parent->parseLanguages($xml->languages, 1);

        // If there is a manifest script, lets copy it.
        if ($this->get('manifest_script'))
        {
            $path['src'] = $this->parent->getPath('source').DS.$this->get('manifest_script');
            $path['dest'] = $this->parent->getPath('extension_root').DS.$this->get('manifest_script');

            if (!file_exists($path['dest']))
            {
                if (!$this->parent->copyFiles(array ($path)))
                {
                    // Install failed, rollback changes
                    $this->parent->abort(JText::_('Extension').' '.JText::_($this->route).': '.JText::_('Could not copy PHP manifest file.'));
                    return false;
                }
            }
        }

        /**
         * ---------------------------------------------------------------------------------------------
         * Database Processing Section
         * ---------------------------------------------------------------------------------------------
         */

        // Was there a plugin already installed with the same name?
        if ($id)
        {
            if (!$this->parent->getOverwrite())
            {
                // Install failed, roll back changes
                $this->parent->abort(JText::_('Extension').' '.JText::_($this->route).': '.JText::_('Plugin').' "'. $this->get('name') .'" '.JText::_('ALREADY_EXISTS'));
                return false;
            }

        }
        else
        {
            // Store in the extensions table (1.6)
            $row = & JTable::getInstance('extension');
            $row->name = $this->get('name');
            $row->type = 'xmap_ext';
            $row->ordering = 0;
            $row->element = "xmap_$element";
            $row->folder = $element;
            $row->enabled = 0;
            $row->protected = 0;
            $row->access = 1;
            $row->client_id = 0;
            $row->params = $this->parent->getParams();
            $row->custom_data = ''; // custom data
            $row->system_data = ''; // system data
            $row->manifest_cache = $this->parent->generateManifestCache();

            if (!$row->store())
            {
                // Install failed, roll back changes
                $this->parent->abort(JText::_('Extension').' '.JText::_($this->route).': '.$db->stderr(true));
                return false;
            }

            // Since we have created a plugin item, we add it to the installation step stack
            // so that if we have to rollback the changes we can undo it.
            $this->parent->pushStep(array ('type' => 'extension', 'id' => $row->extension_id));
            $id = $row->extension_id;
        }

        /*
         * Let's run the queries for the module
         *    If Joomla 1.5 compatible, with discreet sql files - execute appropriate
         *    file for utf-8 support or non-utf-8 support
         */
        // try for Joomla 1.5 type queries
        // second argument is the utf compatible version attribute
        $utfresult = $this->parent->parseSQLFiles($xml->{strtolower($this->route)}->sql);
        if ($utfresult === false)
        {
            // Install failed, rollback changes
            $this->parent->abort(JText::_('Extension').' '.JText::_($this->route).': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
            return false;
        }

        // Start Joomla! 1.6
        ob_start();
        ob_implicit_flush(false);
        if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,$this->route)) {
            $this->parent->manifestClass->{$this->route}($this);
        }
        $msg .= ob_get_contents(); // append messages
        ob_end_clean();

        /**
         * ---------------------------------------------------------------------------------------------
         * Finalization and Cleanup Section
         * ---------------------------------------------------------------------------------------------
         */

        // Lastly, we will copy the manifest file to its appropriate place.
        if (!$this->parent->copyManifest(-1))
        {
            // Install failed, rollback changes
            $this->parent->abort(JText::_('Plugin').' '.JText::_($this->route).': '.JText::_('COULD_NOT_COPY_SETUP_FILE'));
            return false;
        }
        // And now we run the postflight
        ob_start();
        ob_implicit_flush(false);
        if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'postflight')) {
            $this->parent->manifestClass->postflight($this->route, $this);
        }
        $msg .= ob_get_contents(); // append messages
        ob_end_clean();
        if ($msg != '') {
            $this->parent->set('extension_message', $msg);
        }
        return $id;
    }

    /**
     * Custom update method
     *
     * @access    public
     * @return    boolean    True on success
     * @since    1.6
     */
    function update()
    {
        // set the overwrite setting
        $this->parent->setOverwrite(true);
        $this->parent->setUpgrade(true);
        // set the route for the install
        $this->route = 'Update';
        // go to install which handles updates properly
        return $this->install();
    }

    /**
     * Custom uninstall method
     *
     * @access    public
     * @param    int        $cid    The id of the plugin to uninstall
     * @param    int        $clientId    The id of the client (unused)
     * @return    boolean    True on success
     * @since    1.5
     */
    public function uninstall($id)
    {
        // Initialise variables.
        $row    = null;
        $retval = true;
        $db        = &$this->parent->getDbo();

        // First order of business will be to load the module object table from the database.
        // This should give us the necessary information to proceed.
        $row = & JTable::getInstance('extension');
        if (!$row->load((int) $id))
        {
            JError::raiseWarning(100, JText::_('ERRORUNKOWNEXTENSION'));
            return false;
        }

        // Is the plugin we are trying to uninstall a core one?
        // Because that is not a good idea...
        if ($row->protected)
        {
            JError::raiseWarning(100, JText::_('Plugin').' '.JText::_('Uninstall').': '.JText::sprintf('WARNCOREPLUGIN', $row->name)."<br />".JText::_('WARNCOREPLUGIN2'));
            return false;
        }

        // Get the plugin folder so we can properly build the plugin path
        if (trim($row->folder) == '')
        {
            JError::raiseWarning(100, JText::_('Plugin').' '.JText::_('Uninstall').': '.JText::_('Folder field empty, cannot remove files'));
            return false;
        }

        // Set the plugin root path
        if (is_dir(JPATH_ADMINISTRATOR.DS.'/components/com_xmap/extensions'.DS.$row->folder)) {
            $this->parent->setPath('extension_root', JPATH_ADMINISTRATOR.DS.'components/com_xmap/extensions'.DS.$row->folder);
        }

        $manifestFile = $this->parent->getPath('extension_root').DS.preg_replace('/^xmap_/','',$row->element).'.xml';

        if ( ! file_exists($manifestFile) )
        {
            JError::raiseWarning(100, 'Extension Uninstall: Manifest File invalid or not found');
            return false;
        }

        $xml = JFactory::getXML($manifestFile);

        $this->manifest = $xml;

        // If we cannot load the xml file return null
        if (!$xml)
        {
            JError::raiseWarning(100, JText::_('Extension').' '.JText::_('Uninstall').': '.JText::_('Could not load manifest file'));
            return false;
        }

        /*
         * Check for a valid XML root tag.
         * @todo: Remove backwards compatability in a future version
         * Should be 'extension', but for backward compatability we will accept 'install'.
         */
        if ($xml->getName() != 'install' && $xml->getName() != 'extension')
        {
            JError::raiseWarning(100, JText::_('Extension').' '.JText::_('Uninstall').': '.JText::_('Invalid manifest file'));
            return false;
        }


        // run preflight if possible (since we know we're not an update)
        ob_start();
        ob_implicit_flush(false);
        if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'preflight')) {
            $this->parent->manifestClass->preflight($this->route, $this);
        }
        $msg = ob_get_contents(); // create msg object; first use here
        ob_end_clean();

        /*
         * Let's run the queries for the extension
         */
        // try for Joomla 1.5 type queries
        // second argument is the utf compatible version attribute
        $utfresult = $this->parent->parseSQLFiles($xml->{strtolower($this->route)}->sql);
        if ($utfresult === false)
        {
            // Install failed, rollback changes
            $this->parent->abort(JText::_('Extension').' '.JText::_('Uninstall').': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
            return false;
        }

        // Start Joomla! 1.6
        ob_start();
        ob_implicit_flush(false);
        if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'uninstall')) {
            $this->parent->manifestClass->uninstall($this);
        }
        $msg = ob_get_contents(); // append messages
        ob_end_clean();


        // Remove the plugin files
        $this->parent->removeFiles($xml->images, -1);
        $this->parent->removeFiles($xml->files, -1);
        JFile::delete($manifestFile);

        // Remove all media and languages as well
        $this->parent->removeFiles($xml->media);
        $this->parent->removeFiles($xml->languages,1);

        // Now we will no longer need the plugin object, so lets delete it
        $row->delete($row->extension_id);
        unset ($row);

        // If the folder is empty, let's delete it
        $files = JFolder::files($this->parent->getPath('extension_root'));
        
        JFolder::delete($this->parent->getPath('extension_root'));

        if ($msg) {
            $this->parent->set('extension_message',$msg);
        }

        return $retval;
    }

    /**
     * Custom discover method
     *
     * @access public
     * @return array(JExtension) list of extensions available
     * @since 2.0
     */
    function discover()
    {
        $results = Array();
        $folder_list = JFolder::folders(JPATH_ADMINISTRATOR.DS.'components/com_xmap/extensions');

        foreach ($folder_list as $folder)
        {
            $file_list = JFolder::files(JPATH_ADMINISTRATOR.DS.'components/com_xmap/extensions'.DS.$folder,'\.xml$');
            foreach ($file_list as $file)
            {
                $manifest_details = JApplicationHelper::parseXMLInstallFile(JPATH_ADMINISTRATOR.'/components/com_xmap/extensions/'.$folder.'/'.$file);
                $file = JFile::stripExt($file);
                if ($file == 'example') continue; // ignore example plugins
                $extension = &JTable::getInstance('extension');
                $extension->set('type', 'xmap_ext');
                $extension->set('client_id', 0);
                $extension->set('element', "xmap_$file");
                $extension->set('folder', $folder);
                $extension->set('name', $file);
                $extension->set('state', -1);
                $extension->set('manifest_cache', serialize($manifest_details));
                $results[] = $extension;
            }
        }
        return $results;
    }

    /**
     * Custom discover_install method
     *
     * @access public
     * @param int $id The id of the extension to install (from #__discoveredextensions)
     * @return void
     * @since 2.0
     */
    function discover_install()
    {
        $element = preg_replace('/^xmap_/','', $this->parent->extension->element);
        $manifestPath = JPATH_ADMINISTRATOR.DS.'components/com_xmap/extensions'. DS . $this->parent->extension->folder . DS . $element . '.xml';

        $this->parent->manifest = $this->parent->isManifest($manifestPath);
        $description = (string)$this->parent->manifest->description;
        if ($description) {
            $this->parent->set('message', JText::_($description));
        }
        else {
            $this->parent->set('message', '');
        }
        $this->parent->setPath('manifest', $manifestPath);
		$this->parent->setPath('extension_root', JPATH_ADMINISTRATOR.DS.'components/com_xmap/extensions'.DS.$this->parent->extension->folder);
		$this->parent->setPath('source', JPATH_ADMINISTRATOR.DS.'components/com_xmap/extensions'.DS.$this->parent->extension->folder);
        $manifest_details = JApplicationHelper::parseXMLInstallFile($manifestPath);
        $this->parent->extension->manifest_cache = serialize($manifest_details);
        $this->parent->extension->state = 0;
        $this->parent->extension->name = $manifest_details['name'];
        $this->parent->extension->params = $this->parent->getParams();
        if ($this->parent->extension->store()) {
            return $this->parent->extension->get('extension_id');
        }
        else
        {
            JError::raiseWarning(101, JText::_('Plugin').' '.JText::_('Discover Install').': '.JText::_('Failed to store extension details'));
            return false;
        }
    }

    function refreshManifestCache()
    {
        $element = preg_replace('/^xmap_/','', $this->parent->extension->element);
        $manifestPath = JPATH_ADMINISTRATOR. '/components/com_xmap/extensions/'. $this->parent->extension->folder . '/' . $element . '.xml';
        $this->parent->manifest = $this->parent->isManifest($manifestPath);
        $this->parent->setPath('manifest', $manifestPath);
		$this->parent->setPath('extension_root', JPATH_ADMINISTRATOR.DS.'components/com_xmap/extensions'.DS.$this->parent->extension->folder);
		$this->parent->setPath('source', JPATH_ADMINISTRATOR.DS.'components/com_xmap/extensions'.DS.$this->parent->extension->folder);
        $manifest_details = JApplicationHelper::parseXMLInstallFile($this->parent->getPath('manifest'));
        $this->parent->extension->manifest_cache = json_encode($manifest_details);

        $this->parent->extension->name = $manifest_details['name'];
        if ($this->parent->extension->store()) {
            return true;
        }
        else
        {
            JError::raiseWarning(101, JText::_('Plugin').' '.JText::_('Refresh Manifest Cache').': '.JText::_('Failed to store extension details'));
            return false;
        }
    }
}
