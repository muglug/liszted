(function(ns){
	ns.lz = {modules:{},views:{}}
})(window);

(function(ns){
	
	$().ready(function(){ });
	
	var shared=ns.views.shared={};

	shared.addModalBackdrop = function(isClickClose){
		var self=this;
		$("#dialog-outer").remove();
		var outer=$('<div id="dialog-outer" />');
		var inner=$('<div id="dialog-inner" />');
		outer.append(inner);
		if(isClickClose){
			inner.click(function(){
				shared.removeModal()
			});
		}
		$("body").append(outer);
	};
	
	shared.addModal = function($object){
		var inner=$("#dialog-inner");
		inner.append($object);
		$object.click(function(e){
			e.stopPropagation();
		});
	};
	
	shared.removeModal=function(){
		$(".popover").hide();
		$("#dialog-outer").remove()
	};

})(window.lz);

$("#event-filter").change(function (){
	console.log("dfd");
	$("div.calendar").hide();
	$("div.calendar." + $(this).val()).show();
});

$(".user-chooser li").click(function (e){
	var $parent = $(this);
	if(!$parent.hasClass("picked")){
		$parent.addClass("picked");
		e.preventDefault();
		$(".user-chooser").addClass("picked");
		$("#user").val($(this).attr("data-id"));
		$("#password").focus();
	}
	e.stopPropagation();
});

$(".user-chooser form > a").click(function (e){
	var $parent = $(this);
	$(".warning").remove();
	e.preventDefault();
	$(".user-chooser").removeClass("picked");
	$(".user-chooser li").removeClass("picked");
});