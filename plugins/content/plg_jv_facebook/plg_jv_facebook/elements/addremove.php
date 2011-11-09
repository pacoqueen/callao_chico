<?php

/**

 * @package JV Facebook Plugin for Joomla! 1.5

 * @author http://www.zootemplate.com

 * @copyright (C) 2011- zootemplate.Com

 * @license PHP files are GNU/GPL

**/

 

// Check to ensure this file is within the rest of the framework

defined('JPATH_BASE') or die();

class JElementAddremove extends JElement

{

	var	$_name = 'addremove';

 

	function fetchElement($name, $value, &$node, $control_name)

	{

		// Base name of the HTML control.

		$ctrl	= $control_name .'['. $name .']';

 

		// Construct an array of the HTML OPTION statements.

		$options = array ();

		foreach ($node->children() as $option)

		{

			$val	= $option->attributes('value');

			$text	= $option->data();

			$options[] = JHTML::_('select.option', $val, JText::_($text));

		}

 

		// Construct the various argument calls that are supported.

		$attribs	= ' ';

		if ($v = $node->attributes( 'size' )) {

			$attribs	.= 'size="'.$v.'"';

		}

		if ($v = $node->attributes( 'class' )) {

			$attribs	.= 'class="'.$v.'"';

		} else {

			$attribs	.= 'class="inputbox"';

		}

		if ($m = $node->attributes( 'multiple' ))

		{

			$attribs	.= ' multiple="multiple"';

			$ctrl		.= '[]';

		}



		// ajax article

		$baseURL = JURI::base();

		$position = strrpos($baseURL,'administrator');

		$url = substr($baseURL,0,$position);		

		$ajax_url = $url."plugins/content/plg_jv_facebook/elements/article_ajax.php";		

		

?>

		<script type="text/javascript">

			window.addEvent('domready', function() {

				var td = $('paramsaddremove').getParent();

				var jv_html='';

				jv_html += '<span id="jv_btn_add" style="cursor:pointer;"><img src="../plugins/content/plg_jv_facebook/assets/images/downarrow.png" alt="" title="Add"/></span>';

				jv_html += '<span id="jv_btn_remove" style="cursor:pointer;margin-top:10px;"><img src="../plugins/content/plg_jv_facebook/assets/images/uparrow.png" alt="" title="Remove"/></span>';

				td.innerHTML = jv_html;

				

				// load article selected config

				loadSelected();				

				// add article

				$('jv_btn_add').addEvent('click',function(){

					addSelection();

				});

				// remove article

				$('jv_btn_remove').addEvent('click',function(){

					removeSelection();

				});

				// load article config

				getDt();
				
				// select source
				
				$('paramsenable_comcom_content').addEvent('click',function(){
					getDt();
				});
				
				$('paramsenable_comcom_k2').addEvent('click',function(){
					getDt();
				});
				
				$('paramsenable_comboth').addEvent('click',function(){
					getDt();
				});

				// onselected category load ajax aticle

				$('paramscontent_cate').addEvent('change',function(){

					getDt();

				});
				$('paramsk2_cate').addEvent('change',function(){

					getDt();

				});

				//hide article id and title

				$('paramsarticle_id').getParent().getParent().setStyle("display","none");

				$('paramsarticle_title').getParent().getParent().setStyle("display","none");

			});



			// function get data by ajax

			function getDt()

			{

				// get array id of content category selected

				var arr_content_id = "";

				for(var i = $('paramscontent_cate').options.length-1; i >= 0; i--){

					if ($('paramscontent_cate').options[i].selected) {   

						arr_content_id = arr_content_id + $('paramscontent_cate').options[i].value + ",";

					}

				}

				// get array id of k2 category selected

				var arr_k2_id = "";

				for(var i = $('paramsk2_cate').options.length-1; i >= 0; i--){

					if ($('paramsk2_cate').options[i].selected) {   

						arr_k2_id = arr_k2_id + $('paramsk2_cate').options[i].value + ",";

					}

				}

				// get data by ajax

				// get data by ajax
				var url;
				if ($('paramsenable_comcom_content').checked == true){ 
					url = "<?php echo $ajax_url; ?>" + "?contentid=" + arr_content_id+"&k2id=nul";
				} else if ($('paramsenable_comcom_k2').checked == true) {
					url = "<?php echo $ajax_url; ?>" + "?contentid=null&k2id="+arr_k2_id;
				} else {
					url = "<?php echo $ajax_url; ?>" + "?contentid=" + arr_content_id+"&k2id="+arr_k2_id;
				}

				var myAjax = new Ajax(url, {

					onComplete: function(req){

						var resp = Json.evaluate( req );

						//remove all option curently

						for(var i = $('paramsarticle_select').options.length-1; i >= 0; i--){

							$('paramsarticle_select').removeChild($('paramsarticle_select').options[i]);

						}

						// load new article by category

						for( var i=0 ; i < resp.length;i+=2){

							var opt       = document.createElement('option');

			  				opt.value     = resp[i];

			  				opt.innerHTML = resp[i+1];

			  				$('paramsarticle_select').appendChild(opt);

						}

					}

				}).request();

			}

			//add article selected

			function addSelection()

			{				

				var select_box = $('paramsarticle_select');

				var art_id = $('paramsarticle_id');

				var art_title = $('paramsarticle_title');

				

				var st_id = art_id.innerHTML;

				var st_title = art_title.innerHTML;

				for(var i = select_box.options.length-1; i >= 0; i--){

					if(select_box.options[i].selected){

					st_id = st_id + select_box.options[i].value + ";";

					st_title = st_title + select_box.options[i].innerHTML + ";";

					}

				}

				art_id.innerHTML = st_id;

				art_title.innerHTML = st_title;				

				loadSelected();		

			}	



			//load article selected

			function loadSelected()

			{

				var art_id = $('paramsarticle_id');

				var art_title = $('paramsarticle_title');



				var id = art_id.innerHTML;

				var title = art_title.innerHTML;

				

				var idString = new String(id); 

				var id_arr = idString.split(';');

				

				var idString = new String(title); 

				var title_arr = idString.split(';');

				

				for(var i = $('jv_selection_selected').options.length-1; i >= 0; i--){

					$('jv_selection_selected').removeChild($('jv_selection_selected').options[i]);

				}

				

				for(var i=0; i < id_arr.length-1; i++) {

					var opt       = document.createElement('option');

	  				opt.value     = id_arr[i];

	  				opt.innerHTML = title_arr[i];

	  				$('jv_selection_selected').appendChild(opt);  

				}

			}

			

			// remove article selected

			function removeSelection()

			{			

				var selected_box = $('jv_selection_selected');

                for (var i = selected_box.options.length-1; i >= 0; i--) {

                   if (selected_box.options[i].selected) {                   

						var opt       = document.createElement('option');

		  				opt.value     = selected_box.options[i].value;

		  				opt.innerHTML = selected_box.options[i].innerHTML;

		  				selected_box.removeChild(selected_box.options[i]);

                   }					

                }                

				// reload article selected

				var art_id = $('paramsarticle_id');

				var art_title = $('paramsarticle_title');

				var st_id = '';

				var st_title = '';

				for (var i = selected_box.options.length-1; i >= 0; i--) {

					st_id = st_id + selected_box.options[i].value + ";";

					st_title = st_title + selected_box.options[i].innerHTML + ";";

				}

				art_id.innerHTML = st_id;

				art_title.innerHTML = st_title;	

			}

	

		</script>

<?php

		// Render the HTML SELECT list.

		return JHTML::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', $value, $control_name.$name );

	}

}









































