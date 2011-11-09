function tableOrdering( order, dir, task ) {
	var form = document.adminForm;

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	submitform( task );
}

function submitform(pressbutton){
	if (pressbutton) {
		document.adminForm.task.value=pressbutton;
	}
	if (typeof document.adminForm.onsubmit == "function") {
		document.adminForm.onsubmit();
	}
	document.adminForm.submit();
}

function checkChangeForm(){
	var varform = eval('document.adminForm');
	nameField = varform.elements['data[subscriber][name]'];
	if(nameField && (( typeof acymailing != 'undefined' && nameField.value == acymailing['NAMECAPTION'] ) || nameField.value.length < 2)){
		if(typeof acymailing != 'undefined'){ alert(acymailing['NAME_MISSING']); }
		nameField.className = nameField.className +' invalid';
		return false;
	}

	var emailField = varform.elements['data[subscriber][email]'];
	if(emailField){
		emailField.value = emailField.value.replace(/ /g,"");
        var filter = /^([a-z0-9_'&\.\-\+])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,10})+$/i;
        if(!emailField || (typeof acymailing != 'undefined' && emailField.value == acymailing['EMAILCAPTION']) || !filter.test(emailField.value)){
          if(typeof acymailing != 'undefined'){ alert(acymailing['VALID_EMAIL']); }
          emailField.className = emailField.className +' invalid';
          return false;
        }
	}

	if(typeof acymailing != 'undefined' && typeof acymailing['reqFieldsComp'] != 'undefined' && acymailing['reqFieldsComp'].length > 0){
		for(var i =0;i<acymailing['reqFieldsComp'].length;i++){
			elementName = 'data[subscriber]['+acymailing['reqFieldsComp'][i]+']';
			elementToCheck = varform.elements[elementName];
			if(elementToCheck){
				var isValid = false;
				if(typeof elementToCheck.value != 'undefined'){
					if(elementToCheck.value==' ' && typeof varform[elementName+'[]'] != 'undefined'){
						if(varform[elementName+'[]'].checked){
							isValid = true;
						}else{
							for(var a=0; a < varform[elementName+'[]'].length; a++){
								if(varform[elementName+'[]'][a].checked && varform[elementName+'[]'][a].value.length>0) isValid = true;
							}
						}
					}else{
						if(elementToCheck.value.length>0) isValid = true;
					}
				}else{
					for(var a=0; a < elementToCheck.length; a++){
					   if(elementToCheck[a].checked && elementToCheck[a].value.length>0) isValid = true;
					}
				}
				if(!isValid){
					elementToCheck.className = elementToCheck.className +' invalid';
					alert(acymailing['validFieldsComp'][i]);
					return false;
				}
			}else{
				if((varform.elements[elementName+'[day]'] && varform.elements[elementName+'[day]'].value<1) || (varform.elements[elementName+'[month]'] && varform.elements[elementName+'[month]'].value<1) || (varform.elements[elementName+'[year]'] && varform.elements[elementName+'[year]'].value<1902)){
					if(varform.elements[elementName+'[day]'] && varform.elements[elementName+'[day]'].value<1) varform.elements[elementName+'[day]'].className = varform.elements[elementName+'[day]'].className + ' invalid';
					if(varform.elements[elementName+'[month]'] && varform.elements[elementName+'[month]'].value<1) varform.elements[elementName+'[month]'].className = varform.elements[elementName+'[month]'].className + ' invalid';
					if(varform.elements[elementName+'[year]'] && varform.elements[elementName+'[year]'].value<1902) varform.elements[elementName+'[year]'].className = varform.elements[elementName+'[year]'].className + ' invalid';
					alert(acymailing['validFieldsComp'][i]);
					return false;
				}
			}
		}
	}

    var captchaField = varform.elements['acycaptcha'];
    if(captchaField){
 	   if(captchaField.value.length<1){
 		   if(typeof acymailing != 'undefined'){ alert(acymailing['CAPTCHA_MISSING']); }
 		   captchaField.className = captchaField.className +' invalid';
            return false;
 	   }
    }
	return true;
}