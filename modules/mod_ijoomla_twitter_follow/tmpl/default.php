<?php
/**
 * @copyright   (C) 2011 iJoomla, Inc. - All rights reserved.
 * @license  GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author  iJoomla.com webmaster@ijoomla.com
 * @url   http://www.ijoomla.com/licensing/
 * the PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript  *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at http://www.ijoomla.com/licensing/
*/
defined('_JEXEC') or die('Restricted access');
?>

<a href="http://twitter.com/<?php echo $params->get('screen_name'); ?>" class="twitter-follow-button"
    <?php if (!$params->get('show_count')) { ?>data-show-count="false"<?php } ?>
    <?php if ($params->get('lang') != 'en') { ?>data-lang="<?php echo $params->get('lang'); ?>"<?php } ?>
    data-width="100%"
    <?php if ($params->get('bg')) { ?>data-button="grey" data-text-color="#FFFFFF" data-link-color="#00AEFF"<?php } ?>
    >Follow @<?php echo $params->get('screen_name'); ?></a>
<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
