'use strict';

var windowWidth = jQuery(window).width();
var increasingScreenWidth = false;
var csrf = jQuery('meta[name="csrf-token"]').attr('content');
var renameFileData = {};
var checkedItems = []; // store checked items state (tr->table row)

var createNewDirectory = function (params, active) {
	// check dir name 
	if (params.input_folder_name.val() === '') {
		unbindCustomFormSubmitionEvent();
		params.input_folder_name.closest('tr').remove();
		params.form_errors.show();
		return false;
	}
	// tell user folder is being made
	if (!jQuery('#creatingFolderMsg').length) {
		params.input_folder_name.after('<span id="creatingFolderMsg">Creating new folder please wait...</span>');
	}
	var activeInputText = params.input_folder_name.val();
	params.new_directory_name.val(activeInputText);
	params.form.submit();
};
var uploadFiles = function (form) {
	form.submit();
};

var renameDirectory = function (params, active) {
	if (params.input_renamed_folder_name.val() === '') {
		unbindCustomFormSubmitionEvent();
		resetCheckedFiles();
		var parent = params.input_renamed_folder_name.parent();
		parent.find('.file_name').show();
		params.input_renamed_folder_name.remove();
		params.renamed_file_data.form.remove();
		if (countAndStoreCheckedFiles(jQuery('input[id^=checkFile-],input[id^=checkDir-]')) === 0) {
			jQuery('#DelFForm').remove();
		}
		return false;
	}
	params.renamed_file_data.form_inputs.old_file_name.attr('value', params.renamed_file_data.checkedFile.file_name.text());
	params.renamed_file_data.form.submit();
};

var resetCheckedFiles = function () {
	jQuery('input[id^=checkDir-],input[id^=checkFile-]').prop('checked', false);
	jQuery('#CNDForm').show();
};
var countAndStoreCheckedFiles = function (allItemsCheckBox) {
	var count = 0;
	checkedItems = []; // reset state 
	jQuery.each(allItemsCheckBox, function (i, v) {
		if (jQuery(this).is(':checked')) {
			++count;
			checkedItems.push(jQuery(this));
		}
	});
	return count;
};
var addRenameFileForm = function (target, fileID) {
	var deffered = jQuery.Deferred();
	var renameButton = jQuery('#renameFile');
	if (renameButton.length) {
		renameButton.attr('data-id', fileID);
	} else
		target.append('<form id="RFForm" action="' + location.pathname + '" method="POST">\n\
						<div id="renameFile" class="hiddenActions" data-id="' + fileID + '"><i class="fa fa-pencil" aria-hidden="true"></i>Rename File</div>\n\
						<div class="form-errors renameFolder alert alert-danger" style="display: none">The directory name field is required.</div>\n\
						<input type="hidden" name="old_file_name" value=""/>\n\
						<input type="hidden" name="renamed_file_name" value=""/>\n\
						<input type="hidden" name="renamed_file_path" value=""/>\n\
						<input type="hidden" name="action" value="rename-file"/>\n\
						<input type="hidden" name="_token" value="' + csrf + '"/>\n\
					  </form>');
	return deffered.resolve(1).promise();
};
var addDeleteFilesForm = function (target, fileID) {
	var deffered = jQuery.Deferred();
	var deleteButton = jQuery('#deleteFiles');
	if (deleteButton.length) {
		deleteButton.attr('data-id', fileID);
	} else
		target.append('<form id="DelFForm" action="' + location.pathname + '" method="POST">\n\
						<div id="deleteFiles" class="hiddenActions" data-id="' + fileID + '"><i class="fa fa-trash-o" aria-hidden="true"></i>Delete</div>\n\
						<div class="form-errors renameFolder alert alert-danger" style="display: none">Please select file or files to delete.</div>\n\
						<input type="hidden" name="delete_files" value=""/>\n\
						<input type="hidden" name="action" value="delete-files"/>\n\
						<input type="hidden" name="_token" value="' + csrf + '"/>\n\
					  </form>');
	return deffered.resolve(1).promise();
};
var addCopyFilesForm = function (target, fileID) {
	var deffered = jQuery.Deferred();
	var deleteButton = jQuery('#copyFiles');
	if (deleteButton.length) {
		deleteButton.attr('data-id', fileID);
	} else
		target.append('<form id="CFForm" action="' + location.pathname + '" method="POST">\n\
						<div id="copyFiles" class="hiddenActions" data-id="' + fileID + '"><i class="fa fa-files-o" aria-hidden="true"></i>Copy</div>\n\
						<div class="form-errors copyFolder alert alert-danger" style="display: none">Please select files or directories to copy.</div>\n\
						<input type="hidden" name="copy_files" value=""/>\n\
						<input type="hidden" name="action" value="copy-files"/>\n\
						<input type="hidden" name="_token" value="' + csrf + '"/>\n\
					  </form>');
	return deffered.resolve(1).promise();
};
var addMoveFilesForm = function (target, fileID) {
	var deffered = jQuery.Deferred();
	var moveButton = jQuery('#moveFiles');
	if (moveButton.length) {
		moveButton.attr('data-id', fileID);
	} else
		target.append('<form id="MFForm" action="' + location.pathname + '" method="POST">\n\
						<div id="moveFiles" class="hiddenActions" data-id="' + fileID + '"><i class="fa fa-arrows" aria-hidden="true"></i>Move</div>\n\
						<div class="form-errors copyFolder alert alert-danger" style="display: none">Please select files or directories to move.</div>\n\
						<input type="hidden" name="move_files" value=""/>\n\
						<input type="hidden" name="move_destination" value=""/>\n\
						<input type="hidden" name="action" value="move-files"/>\n\
						<input type="hidden" name="_token" value="' + csrf + '"/>\n\
					  </form>');
	return deffered.resolve(1).promise();
};
var addDownloadFilesForm = function (target, fileID) {
	var deffered = jQuery.Deferred();
	var downloadButton = jQuery('#downloadFiles');
	if (downloadButton.length) {
		downloadButton.attr('data-id', fileID);
	} else
		target.append('<form id="DFForm" action="' + location.pathname + '" method="POST">\n\
						<div id="downloadFiles" class="hiddenActions" data-id="' + fileID + '"><i class="fa fa-download" aria-hidden="true"></i>Download</div>\n\
						<div class="form-errors copyFolder alert alert-danger" style="display: none">Please select files for download.</div>\n\
						<input type="hidden" name="download_files" value=""/>\n\
						<input type="hidden" name="action" value="download-files"/>\n\
						<input type="hidden" name="_token" value="' + csrf + '"/>\n\
					  </form>');
	return deffered.resolve(1).promise();
};
var customFormSubmition = function (target, submitFunctionData) {
	// this event will create new directory if you click outside of row or press enter
	var body = jQuery('body');
	body.on('keypress.customFormSubmition', '#' + target.attr('id') + '', function (e) {
		var active = jQuery(this);
		// pressed enter 
		if (e.which === 13) {
			// submit form from given function name
			window[submitFunctionData['function_name']](submitFunctionData.params, active);
		}
	});
	jQuery(document).on('click.customFormSubmition', '*', function () {
		var active = jQuery(this);
		if (// add elements when this event will be ignored
				active.closest('#newFolderRow').length > 0 ||
				active.closest('#renameFile').length > 0 ||
				active.attr('id') === 'newfolderNameLiveEdit' ||
				active.attr('id') === 'createNewFolder' ||
				active.is('a') ||
				active.closest('tr').find(target).length
				)
		{
			return false;
		}
		// execute only once
		window[submitFunctionData['function_name']](submitFunctionData.params, active);
	});

};
var unbindCustomFormSubmitionEvent = function () {
	jQuery('body').off('keypress.customFormSubmition');
	jQuery(document).off('click.customFormSubmition');
};
jQuery(function () {
	var userActionForms = jQuery('#userActionContainer');
	var renameFileContainer = jQuery('#renameFileContainer');
	var moveFilesContainer = jQuery('#moveFilesContainer');
	var moveFilesModalButton = jQuery('#openMoveFilesModal');
	var triggerMoveFilesForm = jQuery('#triggerMoveFilesForm');
	var moveFilesModal = jQuery('#moveFilesModal');
	var downloadFilesContainer = jQuery('#downloadFilesContainer');
	var copyFilesContainer = jQuery('#copyFilesContainer');
	var deleteFilesContainer = jQuery('#deleteFilesContainer');
	var createNewDirForm = jQuery('#CNDForm');
	var uploadFilesForm = jQuery('#UFForm');
	var leftSideMenu = jQuery('#homepageLeftSidemenu');
	var outerWidthLeftSideMenuInitialWidth = leftSideMenu.outerWidth(true);
	var outerWidthLeftSideMenu = outerWidthLeftSideMenuInitialWidth;
	var mainContent = jQuery('#mainContent');
	var allItemsCheckBox = jQuery('input[id^=checkFile-],input[id^=checkDir-]');
	var userStorageTable = jQuery('#userStorageTable');
	var tableBody = userStorageTable.find('tbody');
	var numOfFiles = tableBody.find('tr').length; // files and directories
	var newDirectoryName = jQuery('#newDirectoryName');
	var newfolderNameLiveEdit = jQuery('#newfolderNameLiveEdit');
	var showFileManager = jQuery('#showFileManager');
	var userDirScheme = jQuery('#userDirScheme');
	var formErrors = {
		createFolder: jQuery('.form-errors.createFolder')
	};

	// set main content widt depending on left side menu outer width
	mainContent.width(windowWidth - outerWidthLeftSideMenu);
	if (windowWidth >= 768) {
		userActionForms.parent().height(userStorageTable.height());
		userActionForms.stick_in_parent({offset_top: 30});
	}
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
			userActionForms.parent().trigger("sticky_kit:detach").css({height: 'auto'});
			if (outerWidthLeftSideMenu >= 70) {
				outerWidthLeftSideMenu = outerWidthLeftSideMenu - 50;
			}
			leftSideMenu.width(outerWidthLeftSideMenu);
		}
		// medium screen
		if (windowWidth >= 768) {
			// add sticky
			userActionForms.parent().height(userStorageTable.height());
			userActionForms.stick_in_parent({offset_top: 30});
			if (outerWidthLeftSideMenu <= outerWidthLeftSideMenuInitialWidth) {
				outerWidthLeftSideMenu = outerWidthLeftSideMenu + 50;
			}
		}
		// set main content widt depending on left side menu outer width
		mainContent.width(active.width() - outerWidthLeftSideMenu);
	});
	// check all items in storage table
	jQuery('body').on('click', '#checkAll,input[id^=checkFile-],input[id^=checkDir-]', function (e) {
		var checkedRadioButton = jQuery(this);
		var renameFile = jQuery('#renameFile');
		if (checkedRadioButton.attr('id') === 'checkAll') {
			if (checkedRadioButton.hasClass('checkAll')) {
				allItemsCheckBox.prop('checked', false);
				checkedRadioButton.removeClass('checkAll');
				// show new folder button
				createNewDirForm.show();
			} else {
				checkedRadioButton.addClass('checkAll');
				allItemsCheckBox.prop('checked', 'checked');
				// hide new folder button
				createNewDirForm.hide();
			}
			jQuery('#renameFile').closest('form').remove();
		} else {
			var checkAllButton = userStorageTable.find('#checkAll');
			if (checkAllButton.hasClass('checkAll')) {
				checkAllButton.removeClass('checkAll').prop('checked', false);
			}
			var totalCheckedFiles = countAndStoreCheckedFiles(jQuery('input[id^=checkDir-],input[id^=checkFile-]'));

			if (totalCheckedFiles === 1) {
				// check for download
				var downloadFiles = true;
				var slug = checkedItems[0].attr('id').split('-');
				if (slug[0] === 'checkDir') {
					downloadFiles = false;
				}
				if (downloadFiles) {
					addDownloadFilesForm(downloadFilesContainer, checkedRadioButton.attr('id')).done(function () {});
				}
				addRenameFileForm(renameFileContainer, checkedRadioButton.attr('id')).done(function () {
					renameFileData.form = jQuery('#RFForm');
					renameFileData.form_inputs = {
						old_file_name: renameFileData.form.find('input[name="old_file_name"]'),
						renamed_file_name: renameFileData.form.find('input[name="renamed_file_name"]'),
						renamed_file_path: renameFileData.form.find('input[name="renamed_file_path"]')
					};
					var tableRow = checkedRadioButton.closest('tr');
					renameFileData.checkedFile = {
						table_row: tableRow,
						file_name: tableRow.find('.file_name'),
						file_path: tableRow.find('.name a')
					};
					// set form path
					renameFileData.form_inputs.renamed_file_path.attr('value', renameFileData.checkedFile.file_path.attr('href'));
				});

			} else {
				jQuery('#RFForm,#DFForm').remove();
			}
		}
		totalCheckedFiles = countAndStoreCheckedFiles(jQuery('input[id^=checkDir-],input[id^=checkFile-]'));
		if (totalCheckedFiles === 0) {
			// remove delte files form
			jQuery('#DelFForm,#CFForm,#MFForm').remove();
			// show new folder button
			createNewDirForm.show();
		} else {
			addCopyFilesForm(copyFilesContainer, checkedRadioButton.attr('id')).done(function () {});
			addDeleteFilesForm(deleteFilesContainer, checkedRadioButton.attr('id')).done(function () {});
			addMoveFilesForm(moveFilesContainer, checkedRadioButton.attr('id')).done(function () {});
			createNewDirForm.hide();
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
				// trigger custom from submition
				customFormSubmition(
						newfolderNameLiveEdit,
						{
							function_name: 'createNewDirectory',
							params: {
								form: createNewDirForm,
								form_errors: formErrors.createFolder,
								new_directory_name: newDirectoryName,
								input_folder_name: newfolderNameLiveEdit
							}
						}
				);
			});
		}
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
	// user has triggered rename file/dir
	jQuery('body').on('click', '#renameFile', function (e) {
		e.preventDefault();
		if (countAndStoreCheckedFiles(allItemsCheckBox) > 1) {
			return false;
		}
		if (renameFileData.form.length === 0) {
			return;
		}
		// update rename file data 
		var checkedItem = checkedItems[0];
		var tableRow = checkedItem.closest('tr');
		renameFileData.checkedFile = {
			table_row: tableRow,
			file_name: tableRow.find('.file_name'),
			file_path: tableRow.find('.name a')
		};
		var renameFileNameLiveEditInput = tableRow.find('#new_file_name');
		if (renameFileNameLiveEditInput.length > 0) {
			renameFileData.checkedFile.file_name.hide();
			renameFileNameLiveEditInput.val('');
		} else
			renameFileData.checkedFile.file_name.hide().after('<input id="new_file_name" type="txt" value=""/>');
		var renamedFolderNameLiveEdit = jQuery('#new_file_name');
		renamedFolderNameLiveEdit.focus();
		// trigger custom from submition
		customFormSubmition(
				renamedFolderNameLiveEdit,
				{
					function_name: 'renameDirectory',
					params: {
						renamed_file_data: renameFileData,
						form_errors: jQuery('.form-errors.renameFolder'),
						input_renamed_folder_name: renamedFolderNameLiveEdit
					}
				}
		);
	});
	jQuery('body').on('click keyup', '#new_file_name', function (e) {
		if (e.type === 'click') {
			e.preventDefault();
		}
		var new_file_name = jQuery(this);
		if (renameFileData.form.length === 0) {
			return false;
		}
		// set new file name -> Rename form
		renameFileData.form_inputs.renamed_file_name.attr('value', new_file_name.val());
	});

	// delete files
	jQuery('body').on('click', '#deleteFiles', function (e) {
		e.preventDefault();
		var active = jQuery(this);
		var listOfFilesToDelete = [];
		jQuery.each(checkedItems, function (i, v) {
			var fileName = jQuery(this).closest('tr').find('.name .file_name').text();
			listOfFilesToDelete.push(fileName);
		});
		if (listOfFilesToDelete.length > 0) {
			jQuery('input[name="delete_files"]').attr('value', JSON.stringify(listOfFilesToDelete));
			jQuery('#DelFForm').submit();
		}
	});
	// Download files
	jQuery('body').on('click', '#downloadFiles', function (e) {
		e.preventDefault();
		var active = jQuery(this);
		var fileName = checkedItems[0].closest('tr').find('.name a').attr('href');
		jQuery('input[name="download_files"]').attr('value', fileName);
		jQuery('#DFForm').submit();
	});
	// copy files
	jQuery('body').on('click', '#copyFiles', function (e) {
		e.preventDefault();
		var active = jQuery(this);
		var listOfFilesToCopy = [];
		jQuery.each(checkedItems, function (i, v) {
			var fileName = jQuery(this).closest('tr').find('.name .file_name').text();
			listOfFilesToCopy.push(fileName);
		});
		if (listOfFilesToCopy.length > 0) {
			jQuery('input[name="copy_files"]').attr('value', JSON.stringify(listOfFilesToCopy));
			jQuery('#CFForm').submit();
		}
	});
	// move files 
	jQuery('body').on('click', '#moveFiles', function (e) {
		e.preventDefault();
		var active = jQuery(this);
		var listOfFilesToMove = [];
		jQuery.each(checkedItems, function (i, v) {
			var path = jQuery(this).closest('tr').find('.name a').attr('href');
			listOfFilesToMove.push(path);
		});
		if (listOfFilesToMove.length > 0) {
			jQuery('input[name="move_files"]').attr('value', JSON.stringify(listOfFilesToMove));
			moveFilesModalButton[0].click();
			//jQuery('#CFForm').submit();
		}
	});
	// handle selected menu item in directory manager
	jQuery('body').on('click', '#userDirScheme .menuItemContainer', function (e) {
		e.preventDefault();
		var active = jQuery(this);
		active.toggleClass('selected');
		userDirScheme.find('.menuItemContainer').not(active).removeClass('selected');
		triggerMoveFilesForm.prop('disabled', false);
	});
	jQuery('body').on('click', 'span[id^=menuItemSubDirectories-]', function (e) {
		e.preventDefault();
		var active = jQuery(this);
		var carret = active.find('i').first();
		var li = active.closest('li');
		var subdirMenu = li.find('ul').first();
		if (subdirMenu.length > 0) {
			subdirMenu.toggle('fast', function () {
				if (subdirMenu.is(':visible')) {
					carret.removeClass('fa-caret-right').addClass('fa-caret-down');
				} else
					carret.removeClass('fa-caret-down').addClass('fa-caret-right');
			});
			return;
		}
		var parentLink = li.attr('data-link');
		jQuery.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': csrf
			}
		});
		var formData = {
			parent_item_link: parentLink,
			action: 'DM-LoadMoreMenuItems'
		};
		var ajaxResult = jQuery.ajax({
			type: 'POST',
			url: '/home',
			data: formData,
			dataType: 'json'
		});
		ajaxResult.done(function (response) {
			var data = response.data;
			if (response.status == 0 || data.length === 0) {
				return false;
			}
			carret.removeClass('fa-caret-right').addClass('fa-caret-down');
			li.append(data);
		});
	});
	// when directory manager is being closed
	moveFilesModal.on('hide.bs.modal', function (e) {
		userDirScheme.find('li').removeClass('selected');
		triggerMoveFilesForm.prop('disabled', true);
	});
	// submit move files 
	jQuery('body').on('click', '#triggerMoveFilesForm', function (e) {
		e.preventDefault();
		var active = jQuery(this);
		var currentSelectedDir = jQuery('#userDirScheme .menuItemContainer.selected');
		var path = currentSelectedDir.closest('li').attr('data-link');
		var filesToMove = jQuery.parseJSON(jQuery('#MFForm input[name="move_files"]').val());
		var canMove = true; // stupid solution in hurry !!!
		jQuery.each(filesToMove, function (i, v) {
			if (path === v) {
				//canMove = false;
				alert('You can\'t move file in to it\'s self');
				return false;
			}
		});
		if (!canMove) {
			return false;
		}
		jQuery('#MFForm input[name="move_destination"]').attr('value', path);
		jQuery('#MFForm').submit();
	});
});