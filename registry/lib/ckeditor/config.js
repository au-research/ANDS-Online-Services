/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.width = "770";
	config.resize_enabled = false;
	config.toolbar_Basic =
		[
			['Bold', 'Italic','Underline','Subscript','Superscript','-', 'NumberedList','BulletedList', 'Blockquote', '-', 'Link', 'Unlink', '-', 'Table','HorizontalRule', 'Image','Styles', 'Format', 'Source']
		];	
	config.autoParagraph = false;
};
