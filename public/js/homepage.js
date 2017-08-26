'use strict';
var createNewDirectory = function (form) {
	form.submit();
};
var uploadFiles = function (form) {
	form.submit();
};
var windowWidth = jQuery(window).width();
var increasingScreenWidth = false;

jQuery(function () {
	var createNewDirForm = jQuery('#CNDForm');
	var uploadFilesForm = jQuery('#UFForm');
	var leftSideMenu = jQuery('#homepageLeftSidemenu');
	var outerWidthLeftSideMenuInitialWidth = leftSideMenu.outerWidth(true);
	var outerWidthLeftSideMenu = outerWidthLeftSideMenuInitialWidth;
	var mainContent = jQuery('#mainContent');
	var allItemsCheckBox = jQuery('input[id^=checkFile-],input[id^=checkDir-]');
	var userStorageTable = jQuery('#userStorageTable');
	var tableBody = userStorageTable.find('tbody');
	var newDirectoryName = jQuery('#newDirectoryName');
	var newfolderNameLiveEdit = jQuery('#newfolderNameLiveEdit');
	var showFileManager = jQuery('#showFileManager');
	var formErrors = {
		createFolder: jQuery('.form-errors.createFolder')
	};
	// set main content widt depending on left side menu outer width
	mainContent.width(windowWidth - outerWidthLeftSideMenu);
	// resize screen 
	jQuery(window).resize(function (e) {
		var active = jQuery(this);
		if (windowWidth < active.width()) {
			increasingScreenWidth = true;
		} else
			increasingScreenWidth = false;

		windowWidth = active.width();
		// small screen
		if (windowWidth < 768) {
			if (outerWidthLeftSideMenu >= 70) {
				outerWidthLeftSideMenu = outerWidthLeftSideMenu - 50;
			}
			leftSideMenu.width(outerWidthLeftSideMenu);
		}
		// medium screen
		if (windowWidth >= 768) {
			if (outerWidthLeftSideMenu <= outerWidthLeftSideMenuInitialWidth) {
				outerWidthLeftSideMenu = outerWidthLeftSideMenu + 50;
			}
		}
		// set main content widt depending on left side menu outer width
		mainContent.width(active.width() - outerWidthLeftSideMenu);
	});
	// check all items in storage table
	jQuery('body').on('click', '#checkAll,input[id^=checkFile-],input[id^=checkDir-]', function (e) {
		var active = jQuery(this);
		if (active.attr('id') === 'checkAll') {
			if (active.hasClass('checkAll')) {
				allItemsCheckBox.prop('checked', false);
				active.removeClass('checkAll');
			} else {
				active.addClass('checkAll');
				allItemsCheckBox.prop('checked', 'checked');
			}
		} else {
			var checkAllButton = userStorageTable.find('#checkAll');
			if (checkAllButton.hasClass('checkAll')) {
				checkAllButton.removeClass('checkAll').prop('checked', false);
			}
		}
	});
	// New folder (table row ), place at begining 
	jQuery('body').on('click', '#createNewFolder', function (e) {
		e.preventDefault();
		var newFolderRow = tableBody.find('#newFolderRow');
		if (newFolderRow.length > 0) {
			newFolderRow.find('input').val('');
			alert('Please insert name for your directory! ');
		} else {
			tableBody.prepend('<tr id="newFolderRow"><td></td><td><span class="typeIcon"><i class="fa fa-folder-o" aria-hidden="true"></i></span><input id="newfolderNameLiveEdit" type="text" value="" placeholder="Type folder name, hit enter"/></td><td></td></tr>').promise().done(function () {
				// pass new state
				newfolderNameLiveEdit = jQuery('#newfolderNameLiveEdit');
				// add focus to our field
				newfolderNameLiveEdit.focus();
				// trigger custom event listener
				jQuery(document).trigger('clickedOutsideNewFolderTableRow', [newfolderNameLiveEdit]);
			});
		}
	});

	// this event will create new directory if you click outside of row or press enter
	jQuery(document).on('clickedOutsideNewFolderTableRow', function (e, newfolderNameLiveEdit) {
		jQuery(document).on('keypress', '#newfolderNameLiveEdit', function (e) {
			var activeInput = jQuery(this);
			var activeInputText = activeInput.val();
			if (e.which === 13) {
				newDirectoryName.val(activeInputText);
				if (newDirectoryName.val() === '') {
					newfolderNameLiveEdit.focus();
					formErrors.createFolder.show();
					return false;
				}
				createNewDirectory(createNewDirForm);
			}
		});
		jQuery(document).on('click', '*', function () {
			var active = jQuery(this);
			//return false;
			if (active.closest('#newFolderRow').length > 0 || active.attr('id') === 'newfolderNameLiveEdit' || active.attr('id') === 'createNewFolder' || active.is('a')) {
				return false;
			}
			// set new dir name 
			newDirectoryName.val(jQuery('#newfolderNameLiveEdit').val());
			if (newDirectoryName.val() === '') {
				newfolderNameLiveEdit.focus();
				formErrors.createFolder.show();
				return false;
			}
			// add msg for creation
			if (!jQuery('#creatingFolderMsg').length) {
				newfolderNameLiveEdit.after('<span id="creatingFolderMsg">Creating new folder please wait...</span>');
			}
			// create new directory, submit form 
			createNewDirectory(createNewDirForm);
		});
	});
	// trigger upload, user file manager
	jQuery('body').on('click', '#uploadFiles', function (e) {
		e.preventDefault();
		showFileManager.trigger('click');
	});
	// monitor if user has pick files for upload	
	jQuery('body').on('change', '#showFileManager', function (e) {
		var active = jQuery(this);
		if (active.val().length > 0) {
			uploadFiles(uploadFilesForm);
		}
	});
});