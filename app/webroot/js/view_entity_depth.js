$(function(){
    var url = window.location.pathname 

	var subdirectory = "";
    if (window.location.pathname.split('/')[1] != 'problem_maps'){
		subdirectory =  '/' + window.location.pathname.split('/')[1];
    }

    var pMapId = url.substr(url.lastIndexOf('/') + 1);
	
	/* Models */
	var Entity = Backbone.Model.extend({
		url: function(){
			if (this.get('id'))
		return subdirectory + '/entities/' + this.get('id') + '.json';
			else
		return subdirectory + '/entities.json';
		},
		parse: function(response) {
			return response.Entity
		},
		toJSON: function() {
			var entity = _.clone(this.attributes);
			return {'Entity': entity};
		}
	});

	var Decomposition = Backbone.Model.extend({
		url: function(){
			if (this.get('id'))
		return subdirectory + '/decompositions/' + this.get('id') + '.json';
			else
		return subdirectory + '/decompositions.json';
		},
		parse: function(response) {
			return response.Decomposition
		},
		toJSON: function() {
			var decomposition = _.clone(this.attributes);
			return {'Decomposition': decomposition};
		}
	});

	var Link = Backbone.Model.extend({
		url: function(){
			if (this.get('id'))
		return subdirectory + '/links/' + this.get('id') + '.json';
			else
		return subdirectory + '/links.json';
		},
		parse: function(response) {
			return response.Link
		},
		toJSON: function() {
			var link = _.clone(this.attributes);
			return {'Link': link};
		}
	});

	/* Collections */ 
	var EntityList = Backbone.Collection.extend({
		model: Entity,
		url: function() {
			return subdirectory + '/entities.json?problem_map_id=' + pMapId;
		},
		comparator: function(entity){
			// sort first by list_order then by id (order of creation)
			return parseInt(entity.get('list_order')) + 0.00001 * parseInt(entity.get('id'));
		}
	});

	var DecompositionList = Backbone.Collection.extend({
		model: Decomposition,
		url: function() {
			return subdirectory + '/decompositions.json?problem_map_id=' + pMapId;
		}
	});

	var LinkList = Backbone.Collection.extend({
		model: Link,
		url: function() {
			return subdirectory + '/links.json?problem_map_id=' + pMapId;
		}
	});

	/* Instantiations of the collections */
	Entities = new EntityList();
	Decompositions = new DecompositionList();
	Links = new LinkList();

	/* Views */
	var EntityView = Backbone.View.extend({
	});

	var LinkView = Backbone.View.extend({
	});

	var DecompositionView = Backbone.View.extend({
	});

	var EntityGraphView = Backbone.View.extend({
	});

	function getChildrenEntities(entityType, id){
		var children = [];

		if (id == null){
			Entities.where({type: entityType, decomposition_id: null}).forEach(function(element){
					var data = {}
					data['name'] = element.get('name');
					data['children'] = getChildrenDecomps(entityType, element.id);
					children.push(data);
			});
		} else {
			Entities.where({type: entityType, decomposition_id: id}).forEach(function(element){
					var data = {}
					data['name'] = element.get('name');
					data['children'] = getChildrenDecomps(entityType, element.id);
					
					children.push(data);
			});
		}

		return children;
	}

	function getChildrenDecomps(entityType, id){
		var children = [];

		Decompositions.where({entity_id: id}).forEach(function(element){
			var data = {}
			
			data['name'] = 'Decomp' + element.get('id');
			data['children'] = getChildrenEntities(entityType, element.get('id'));

			children.push(data);
		});

		return children;
	}
	
	if (typeof String.prototype.startsWith != 'function' ) {
	  String.prototype.startsWith = function( str ) {
		return this.substring( 0, str.length ) === str;
	  }
	};

	
	var getDepth = function (obj) {
		var depth = 0;
		if (obj.children) {
			obj.children.forEach(function (d) {
				var decomp_count = 0;
				if(d.name.startsWith( "Decomp" ))
					decomp_count += 1;
				var tmpDepth = getDepth(d)
				if (tmpDepth > depth) {
					depth = tmpDepth - decomp_count;
				}
			})
		}
		return 1 + depth
	}	
	
	var calculateDepth = function (type){
		var data = {};
		data['name'] = type + "s";
		data['children'] = getChildrenEntities(type);
		
		var json_data = JSON.stringify(data);
		var depth = getDepth(data) - 2;
		if(depth == -1)
			depth = "<i>no children</i>"
		var content = '<li>' + type + ' - ' + depth + '</li>';
		$("#container ul").append(content);
	}
	
	/* Get data from server */
	Links.fetch().done(function() {
		Entities.fetch().done(function(){
			Decompositions.fetch().done(function(){
				calculateDepth('requirement');
				calculateDepth('usescenario');
				calculateDepth('function');
				calculateDepth('artifact');
				calculateDepth('behavior');
				calculateDepth('issue');
			});  
		});
	});
	
});