var height_before;
var height_after;
var list_height;
var height_diff;
var tutorial_type;

$(document).ready(function(){
	tutorial_type = $("table input#tutorial_type:checked").val();
	tutorial_prompt('1');
});

function tutorial_prompt(step){
	$.get("../tutorial_prompts/"+step+"/"+tutorial_type, function(d){
		$("#tutorial_prompt").html(d);
	});
}

function tutorial_enable(pid, tType){
	list_height = $("div.row-fluid.scroll").height();
	height_before = $("#tutorial_prompt").height();
	
	var tutorial_on = 0;
	if($('table input#tutorial_switch').is(':checked')){
		tutorial_on = 1;
	}
	tutorial_switch(pid, tutorial_on, tType);
}

function tutorial_switch(pid, tutorial_on, tType){
	$.get("../tutorial_switch/"+pid+"/"+tutorial_on+"/"+tType, function(d){
		location.reload();
	});
}

