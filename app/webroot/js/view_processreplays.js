$(document).ready(function() {
	var owl = $("#owl-process-replay");
	if(owl.length > 0) {
		owl.owlCarousel({
			navigation : true, // Show next and prev buttons
			slideSpeed : 300,
			paginationSpeed : 300,
			singleItem: true,
		});
		
		// Custom Navigation Events
		$(".next").click(function(){
			owl.trigger('owl.next');
		})
		$(".prev").click(function(){
			owl.trigger('owl.prev');
		})
		$(".play").click(function(){
			owl.trigger('owl.play',3000); 
		})
		$(".stop").click(function(){
			owl.trigger('owl.stop');
		})
	}
 });
 
 function choose_perception(elem, row_id){
	$('#perception-input-'+row_id).val(elem.options[elem.selectedIndex].value);
 }
 
 function edit_perception(row_id){
	$(".save-perception.row_"+row_id).toggle();
	$(".edit-perception.row_"+row_id).attr("onclick", "cancel_edit_perception("+row_id+")");
	$(".select-perception").attr("onchange","choose_perception(this,"+row_id+")");
	$("#perception-input-"+row_id+", .select-perception").removeAttr("disabled");
 }
 
 function cancel_edit_perception(row_id){
	$(".save-perception.row_"+row_id).toggle();
	$(".edit-perception.row_"+row_id).attr("onclick", "edit_perception("+row_id+")");
	$("#perception-input-"+row_id+", .select-perception").attr("disabled","disabled");
 }
 
 function save_perception(id){
	var perception = $(".perception-input.row_"+id).val();
	$.get("../../problem_maps/save_perception/"+id+"/"+perception, function(d){});
	$(".select-perception").val('');
	cancel_edit_perception(id);
 }

 
 