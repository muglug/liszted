(function(ns){
	
	var editApi = ns.views.editApi = {};

	editApi.saveVenue = function(fields, onSuccess){
		return $.ajax({
			type: 'POST',
			url: "/edit/venue/",
			data: fields,
			success: onSuccess,
			dataType: "html"
		});
	}
	
	editApi.saveProgrammeLineInfo = function(fields, onSuccess){
		return $.ajax({
			type: 'POST',
			url: "/edit/programme/info.php",
			data: fields,
			success: onSuccess,
			dataType: "html"
		});
	}

})(window.lz);