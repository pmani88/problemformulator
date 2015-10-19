var i = 0;
var tree, diagonal, svg;
var treeData;
var duration = 750;
var root;
var insertLinebreaks;
$( document ).ready(function() {
	$(".navbar").remove();

	$("#print_objtree").button();
	$("#print_objtree").click(function(){
		window.print();
	});
	
    treeData = JSON.parse($("textarea#objtree_data").val());
	
	var margin = {top: 0, right: 0, bottom: 0, left: 185},
    width = 750 - margin.right - margin.left,
    height = 890 - margin.top - margin.bottom;
	
	tree = d3.layout.tree()
		.size([height, width]);

	diagonal = d3.svg.diagonal()
		.projection(function(d) { return [d.y, d.x]; });

	svg = d3.select("#objtree_graph").append("svg")
		.attr("width", width + margin.right + margin.left)
		.attr("height", height + margin.top + margin.bottom)
		.append("g")
		.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

	root = treeData;
	root.x0 = height / 2;
	root.y0 = 0;
	
	update(root);
});

function update(source) {
	// Compute the new tree layout.
	var nodes = tree.nodes(root).reverse(),
	  links = tree.links(nodes);

	// Normalize for fixed-depth.
	nodes.forEach(function(d) { d.y = d.depth * 40; });

	// Update the nodes…
	var node = svg.selectAll("g.node")
	  .data(nodes, function(d) { return d.id || (d.id = ++i); });

	// Enter any new nodes at the parent's previous position.
	var nodeEnter = node.enter().append("g")
	  .attr("class", "node")
	  .attr("transform", function(d) { return "translate(" + source.y0 + "," + source.x0 + ")"; }) ;

	nodeEnter.append("circle")
	  .attr("r", 1e-6)
	  .style("fill", function(d) { return d._children ? "lightsteelblue" : "#fff"; });

	nodeEnter.append("text")
		.attr("class", function(d) { if(d.name.search("Decomp") > -1){return "Decomp";} })
		.attr("x", function(d) { return d.children || d._children ? -10 : 10; })
		.attr("dy", ".35em")
		.attr("text-anchor", function(d) { return d.children || d._children ? "end" : "start"; })
		.text(function(d) { 
			if(d.name.indexOf('Decomp')>-1) return "";
			
			if(d.weight > 0) 
				return d.name+' ('+d.weight+')';
			else 
				return d.name; 
		})
		.style("fill-opacity", 1e-6).call(wrap);
	  
	// Transition nodes to their new position.
	var nodeUpdate = node.transition()
		.attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });

	nodeUpdate.select("circle")
		.attr("r", 3)
		.style("fill", function(d) { return d._children ? "lightsteelblue" : "#fff"; });

	nodeUpdate.select("text")
		.style("fill-opacity", 1);

	// Transition exiting nodes to the parent's new position.
	var nodeExit = node.exit().transition()
		.attr("transform", function(d) { return "translate(" + source.y + "," + source.x + ")"; })
		.remove();

	nodeExit.select("circle")
		.attr("r", 1e-6);

	nodeExit.select("text")
		.style("fill-opacity", 1e-6);

	// Update the links…
	var link = svg.selectAll("path.link")
		.data(links, function(d) { return d.target.id; });

	// Enter any new links at the parent's previous position.
	link.enter().insert("path", "g")
		.attr("class", "link")
		.attr("d", function(d) {
		var o = {x: source.x0, y: source.y0};
		return diagonal({source: o, target: o});
		})
		.attr("stroke", "#ccc");

	// Transition links to their new position.
	link.transition()
		.attr("d", diagonal);

	// Transition exiting nodes to the parent's new position.
	link.exit().transition()
		.attr("d", function(d) {
		var o = {x: source.x, y: source.y};
		return diagonal({source: o, target: o});
		})
		.remove();

	// Stash the old positions for transition.
	nodes.forEach(function(d) {
		d.x0 = d.x;
		d.y0 = d.y;
	});
	setTimeout(function(){arrangeLabels();},800);
}

function wrap(text) {
  text.each(function() {
    var text = d3.select(this),
        words = text.text().split(/\s+/).reverse(),
        word,
        line = [],
        lineNumber = 0,
        lineHeight = 1, // ems
        y = text.attr("y"),
        dy = parseFloat(text.attr("dy")),
        tspan = text.text(null).append("tspan").attr("x", 0).attr("y", y).attr("dy", dy + "em");
    while (word = words.pop()) {
      line.push(word);
      tspan.text(line.join(" "));
      if (tspan.node().getComputedTextLength() > 228) {
        line.pop();
        tspan.text(line.join(" "));
        line = [word];
        tspan = text.append("tspan").attr("x", 0).attr("y", y).attr("dy", ++lineNumber * lineHeight + dy + "em").text(word);
      }
    }
  });
}

function arrangeLabels() {
  var move = 1;
	while(move > 0) {
		move = 0;
		svg.selectAll("text")
		.each(function() {
			var that = this,
            a = this.getBoundingClientRect();
			svg.selectAll("text").each(function() {
				if(this != that) {
				var b = this.getBoundingClientRect();
				if((Math.abs(a.left - b.left) * 2 < (a.width + b.width)) &&
				   (Math.abs(a.top - b.top) * 2 < (a.height + b.height))) {
						// overlap, move labels
						var dy = (Math.max(0, a.right - b.left) +
							   Math.min(0, a.left - b.right)) * 0.01,
						  dx = (Math.max(0, a.bottom - b.top) +
							   Math.min(0, a.top - b.bottom)) * 0.02,
						  tt = d3.transform(d3.select(this).attr("transform")),
						  to = d3.transform(d3.select(that).attr("transform"));
						move += Math.abs(dx) + Math.abs(dy);

						to.translate = [ to.translate[0] + dx, to.translate[1] + dy ];
						tt.translate = [ tt.translate[0] - dx, tt.translate[1] - dy ];
						d3.select(this).attr("transform", "translate(" + tt.translate + ")");
						d3.select(that).attr("transform", "translate(" + to.translate + ")");
						a = this.getBoundingClientRect();
					}
				}
            });
       });
	}
	decomp_pathstroke();
}

function decomp_pathstroke(){
	var str1, str2, sp1, sp2, len;
	$("text.Decomp").each(function(){ 
		str2 = $(this).parent().attr('transform').replace('translate(','').split(',');
		$("path").each(function(){
			str1 = $(this).attr("d");
			sp1 = str1.split(" ");
			len = sp1.length;
			sp2 = sp1[len-1].split(",");
			if(str2[0] == sp2[0])
				$(this).attr("stroke-dasharray", 5).attr("stroke","#FA8072");
		});
	});
}