'use strict';
jQuery(function () {
	var allItemsCheckBox = jQuery('input[id^=checkFile-],input[id^=checkDir-]');
	var userStorageTable = jQuery('#userStorageTable');
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
});