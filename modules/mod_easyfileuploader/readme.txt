Easy File Uploader v0.8

Copyright: Michael A. Gilkes
License: GNU/GPL v2


Requirements:
 > Joomla 1.6
 > PHP 5.3+
 

Description:
This is a flexible file upload module. It allows the administrator to specify a destination folder, and allows the user to upload files to it. The administrator can also specify how many files that can be uploaded simultaneously, as well as the text in the front end. You can even have more then one module of its kind on the same page.


Main features:
 > Upload files to a specified folder in the 'images' directory.
 > Specify the maximum file size permitted to tbe uploaded.
 > Specify the types of files, by MIME type, that are permitted.
 > Customize the upload label and submit button texts.
 > Specify the background color of the results block to match your theme.
 > Upload multiple files simultaneously (Up to a maximum of 10)
 > Customize your front end text
 > Customize your file input variable, which allows to have multiple modules on a single page
 > Provide the front-end user with the option to replace existing files on upload


Changes:
 > 2010-03-21 - Module creation completed and module tested.
 > 2010-03-24 - Added Back-end ability to provide a replace existing file option in the front-end
 > 2010-03-24 - Added Back-end ability to provide custom text for asking to replace file
 > 2010-03-24 - Added Back-end ability to provide a custom variable name for the file input field
 > 2010-03-24 - Added Back-end ability to specify up to 10 multiple file uploads in the front-end
 > 2011-01-10 - Fixed MIME type tamper vulnerability
 > 2011-02-05 - Fixed syntax error in helper file
 > 2011-02-06 - Adjusted mime detection function to ignore charset value
 > 2011-02-20 - Added Joomla 1.6 Compatible branch. Removed Module Class Suffix Parameter.


Known Issues:
This module is designed to work on PHP 5.3+. If you have an earler version of PHP, you 
may encounter issues with detecting the MIME type of the uploaded file. This depends on 
your server setup, as it should work fine if PHP is allowed to call the exec function. 
See the following page for further details and considerations:
http://support.michaelgilkes.com/topic/efum-not-detecting-mime-type


Installation:
This module is designed for Joomla 1.6. To install go to the install/uninstall extensions page of Joomla Administrator and upload the package file. Then go to the Module Manager page and activate the module.


Usage:
To use this module as content in an article, first ensure than the loadmodule plugin (Content - Load Module) is Enabled. Second, give an arbitrary Position to the Easy File Uploader module, such as 'x_load', and configure the parameters. Thirdly, in the article, type '{loadposition x_load}'. Please ensure that the Menu Assignment is set to 'All' or to the specific article that it is to be displayed in. To control which types of users have access to this module, set the Access Level in the module.
To specify how many multiple uploads, go to the Advanced Parameters section, and choose from 1 to 10.
To allow more than one module on the same page, or article, give each of them a different arbitrary position. Then go to the Advanced Parameters section and specify a different Input File Variable Name for each of them. If they all have the same Input File Variable Name, it will cause any uploads to one module folder to show erroneous messages for the other modules.

Parameters:
Label Text - This is the text that labels the upload file form. The default text is 'Choose a file to upload:'.
Submit Button Text - This is the text that is on the button to upload the file. The default text is 'Upload File'.
Choose the Upload Folder - This is a drop down list of the sub-folders in the 'images' folder. The 'stories' folder is selected by default. The selected folder is where the uploaded files are stored.
Maximum File Size (bytes) - This is the maximum allowable file size, in bytes, that can be uploaded. The default value is 1000000 bytes.
Results Box Background Color - The background color of the Results information box. The default value is '#DDEEFF'.
Permitted file types (separate by semi-colon) - This is a list of the file types that are permitted to be uploaded (separated by semi-colon). Type asterisk (*) to allow any type. The default values are 'image/gif;image/jpeg;image/pjpeg;image/png;application/pdf;application/msword;application/zip;application/x-compressed;application/x-zip-compressed;multipart/x-zip;application/excel;application/vnd.ms-excel;application/x-excel;application/x-msexcel'.

Advanced Parameters:
Replace Files Question - This is the text that labels the option of whether the user wants to replace existing files. The default text is 'Replace existing files with uploaded files?'.
Yes answer text - You are free to customize the YES answer text to the replace question. The default text is 'Yes'.
No answer text - You care free to customize the NO answer text to the replace question. The default text is 'No'.
Include option to replace existing file? - This allows the administrator to decide whether to include an option in the front-end to replace en existing file with the same file name as the file to be uploaded. The default choice is 'No'.
Input File Variable Name - This allows you to specify the variable name of the input file. Only change this if you intend to have more than one Easy File Uploader module on a single article page. In that case, each module should have its own unique name. The default text is 'fileToUpload'.
Number of Files to Upload - Select the number of upload file fields to include. The default is to upload 1 file, but you may select up to 10 files to upload simultaneously. The default number is '1'.

Credit:
Jeff Channell - providing the code fix for the MIME type tamper vulnerability
