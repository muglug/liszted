(function( $ ) {
	$.fn.datePicker = function() {
		this.each(function(){
			var $datepicker = $(this);
			
			var show_month = function (month,year,days){
				month--; // corrects month
				var table = "";
				var month_date = new Date(year, month, 1);
				var month_text = months[month_date.getMonth()]+" "+month_date.getFullYear();
				var total_days = 32 - new Date(year, month, 32).getDate();
				table+= "<table>";
				table+= "<thead><tr><th>M</th><th>T</th><th>W</th><th>Th</th><th>F</th><th>Sa</th><th>Su</th></tr></thead>";
				table+= "<tbody>";
				table+= "<tr>";
				var now = new Date();
				
				var today = new Date(now.getFullYear(),now.getMonth(),now.getDate());
				for(var i =1;i<=total_days;i++){
					var day = new Date(year, month,i);
					
					var day_of_week = daynames[day.getDay()].toLowerCase();
					
					if(i==1){ // add empty days
					
						var day_count = (day.getDay()+6)%7;
						//alert(day_count);
						for(var a=0;a<day_count;a++) table+= "<td></td>";
					}
					
					var classes = day_of_week;
					var str_date = day.getFullYear()+"-" + String('0'+(day.getMonth()+1)).slice(-2) + "-" + String('0'+day.getDate()).slice(-2);
				
					if(day.valueOf() == today.valueOf()) classes+=" today";
					var day_events = "";
					if(days[str_date]){
						classes+= " on";
					}
					if(day_of_week=="monday"){
						table+= "</tr><tr>";
					}
					table+= "<td class=\""+classes+"\"><div class=\"num\">"+i+"</div></td>";
				
				}
				table+= "</tr></tbody></table>";
				return table;
			}
			
			var new_month = function(){
				var day = $datepicker.find(".pp_day").val();
				console.log($datepicker);
				var month = $datepicker.find(".pp_month").val();
				var year = $datepicker.find(".pp_year").val();
				$datepicker.find(".date table").remove();
				var days = {};
				var newdate = year+"-" + String('0'+month).slice(-2) + "-" + String('0'+day).slice(-2);
				days[newdate] = 1;
				$datepicker.find(".date").append(show_month(month,year,days));
			}
			
			$datepicker.find(".ic").click(function(){
				var month = $datepicker.find(".pp_month").val();
				var year = $datepicker.find(".pp_year").val();
				var day = $datepicker.find(".pp_day").val();
				if($(this).hasClass("right")){
					month++;
				}else{
					month--;
				}
				if(month > 12){
					year++;
					month-=12;
				}else if(month < 1){
					year--;
					month+=12;
				}
				$datepicker.find(".pp_month").val(month);
				$datepicker.find(".pp_year").val(year);
				
				new_month();
			});
			
			$datepicker.find(".date").on("click","td > div",function(){
				$datepicker.find(".date td").removeClass("on");
				$(this).parent().addClass("on");
				var month = $datepicker.find(".pp_month").val();
				var year = $datepicker.find(".pp_year").val();
				$datepicker.find(".pp_day").val($(this).text());
				var newdate = year+"-" + String('0'+month).slice(-2) + "-" + String('0'+$(this).text()).slice(-2);
				$datepicker.find(".date").attr("data-date",newdate);
			});
			
			$datepicker.find(".date select").change(function(){
				new_month();
			});
			new_month();
		});
	};
})( jQuery );

(function ($){
	$.fn.performance = function(){
		this.each(function(){
			$row = $(this);
			$row.find(".datepicker").datePicker();
			$venue_id = $row.find("input.venue-id");
			$venue = $row.find("input.venue");
			if($venue_id.siblings("button.venue").length > 0){
				$venue.parent().hide();
			}
			$venue = $("#" + $venue.attr("id"));
			
			var venue_name_id = $venue.attr("id");
			var venue_id = $venue_id.attr("id");
			$venue.autocomplete({
				source: "/edit/venue/search.php",
				minLength: 2,
				select: function( event, ui ) {
					$venue = $("#" + venue_name_id);
					$venue_id = $("#" + venue_id);
					$venue_id.val(ui.item.id);
					var id = $venue.attr("name").split("_").pop();
					
					$.get("/edit/venue/index.php?id=" + ui.item.id + "&button&performance=" + id, function (data){
						$venue.parent().hide();
						$thisrow = $venue.parents(".field-row");
						$thisrow.find("button.venue").remove();
						$venue.parent().after(data);
						$thisrow.find("button.venue").append("<div class=\"delete\" />");
					});
				}
			});
			$row.find("button.venue").append("<div class=\"delete\" />");
			$row.on("click", "button.venue", function(){
				var $dialog = $("<div class=\"dialog\" />");
				var venue_id = $(this).attr("data-id");
				lz.views.shared.addModalBackdrop(true);
				lz.views.shared.addModal($dialog);
				$.get("/edit/venue/index.php?id=" + venue_id, function (data){
					$dialog.append(data);
					$dialog.find("form").submit(function(e){
						var fields = $(this).serialize();
						console.log("saving");
						lz.views.editApi.saveVenue(fields, function(data){
							console.log(data);
							$row.find("button.venue span").text(data);
						});
						
						lz.views.shared.removeModal();
						e.preventDefault();
						return false;
					});
					$dialog.find("button.cancel").click(function(e){
						lz.views.shared.removeModal();
						e.preventDefault();
						return false;
					});
				});
			}).on("click", "button.venue .delete", function(e){
				e.stopPropagation();
				$parent = $(this).parents(".field-row");
				$parent.find(".venue-id").val("");
				$parent.find(".venue-init").show();
				$(this).parent().remove();
			}).on("click", "button.delete-performance", function (){
				if(confirm("Are you sure you want to delete this performance")){
					$parent = $(this).parents(".field-row");
					$parent.remove();
				}
			});
		});
	};
})( jQuery );


var months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
var daynames = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];



$(document).ready(function(){
	
	function add_autocomplete(){
		$( ".pl_composer" ).autocomplete({
			source: function( request, response ) {
				var matcher = util.patterns.create($.trim(request.term));
				var count = 0;
				response( $.grep( contributers, function( value ) {
					if (count > 10) return false;
					value = value.label || value.value || value;
					var test = value.match(matcher) != null; // safer than .test() 
					if(test) count++;
					return test;
				}) );
			},
			
			minLength: 2
		});
	}
	
	add_autocomplete();
	
	$("#programme_works").on("click", ".pl_advanced", function(){
		var $dialog = $("<div class=\"dialog\" />");
		var work_id = $(this).attr("data-id");
		lz.views.shared.addModalBackdrop(true);
		lz.views.shared.addModal($dialog);
		
		$.get("/edit/programme/info.php?id=" + work_id, function(data){
			$dialog.append(data);
			$dialog.find("form").submit(function(e){
				e.preventDefault();
				var fields = $(this).serialize();
				lz.views.editApi.saveProgrammeLineInfo(fields, function(data){
					
				});
				lz.views.shared.removeModal();
				return false;
			});
		});
	});
	
	$("#performances > div").performance();
	
	$("#p_addwork").click(function(){
		$.get("/edit/programme/newline.php", function(data) {
			$("#programme_works").append(data);
			add_autocomplete();
		});
		
	});
	
	$("#p_addperformer").click(function(){
		$.get("/edit/programme/newcontribution.php", function(data) {
			$("#performers").append(data);
		});
		
	});
	
	$("#p_addperformance").click(function(){
		$.get("/edit/programme/newperformance.php", function(data) {
			var $data = $(data);
			$("#performances").append($data);
			$data.performance();
		});
	});
});