/* Function to view the Sketchpad */
function view_sketch(){
	var sketchpad = Raphael.sketchpad("sketchpad-viewer", {
		width: 400,
		height: 400,
		strokes: eval($("#sketchpad-data").val()),
		editing: false
	});

	var entity_id = $("#sketchpad-container #sketch-entity-id").val();
	
	// When the sketchpad changes, update the input field.
	sketchpad.change(function() {
		if(sketchpad.json()!= "[]")
			$("#sketchpad-data").val(sketchpad.json());
		else
			$("#sketchpad-data").val('');
	});

	$('#sketch-edit').click(function(){
		$("#sketch-draw, #sketch-erase, #sketch-undo, #sketch-redo, #sketch-clear, #sketch-save, #sketch-delete").css('display','inline-block');
		$("#sketch-edit").css('display','none');
		sketchpad.editing(true);
	});
	
	$('#sketch-draw').click(function(){
		sketchpad.editing(true);
		// sketchpad.pen().color("#000");
	});
	
	$('#sketch-save').click(function(){
		sketchpad.editing(true);
		save_sketch(entity_id);
	});
	
	$('#sketch-delete').click(function(){
		sketchpad.editing("erase");
	});
	/*
	$('#sketch-erase').click(function(){
		sketchpad.pen().color("#fff");
	});
	*/
	$('#sketch-undo').click(function(){
		sketchpad.undo();
	});
	
	$('#sketch-redo').click(function(){
		sketchpad.redo();
	});
	
	$('#sketch-clear').click(function(){
		sketchpad.clear();
	});
}

/* Close the sketchpad */
function closeSketchViewer(){
	$("#sketchpad-overlay, #sketchpad-container").removeClass("active");
	$('#sketchpad-viewer').html('');
	$('#sketchpad-data').val('');
	$("#sketch-draw, #sketch-erase, #sketch-undo, #sketch-redo, #sketch-clear, #sketch-save, #sketch-delete").css('display','none');
	$("#sketch-edit").css('display','inline-block');
}


