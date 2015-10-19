/* Global variables.. yuck I know... */
var subdirectory = '';

var Entities;
var Links;
var Decompositions;
var dragSrc;

var Req;
var Fun;
var Art;
var Beh;
var Iss;
var Usr;

var dragging = false;
var position;
var original_container;
var oldContainer;
var type;

var subtypes;

var gEntityType;
var gAction;
var gActionFlag = false;
var gIsLink = false;
var gFromId = 0;
var gToId = 0;
var url = window.location.pathname; 
var pMapId = url.substr(url.lastIndexOf('/') + 1);
/* Helper function */

/* trips whitespace off beginning and end of a string */
function trim (str) {
    return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}

/* The main show */
$(function(){
    if (window.location.pathname.split('/')[1] != 'problem_maps')
		subdirectory =  '/' + window.location.pathname.split('/')[1];
    //console.log(subdirectory);

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
        return response.Decomposition;
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
    tagName: 'li',
    attributes: {
        class: 'entity'
    },
    events: {
        'click .folder'         : 'toggleCollapse',
        'click .destroy'        : 'trash',
        'click .editable'       : 'makeEditable',
        // TODO just to prevent accidental toggleCollapse when editing text.
        //'dblclick .editable'    : 'makeEditable',
        'blur .editable'        : 'blur',
        'keypress .editable'    : 'edit',
        'onDrop'                : 'drop',
        'onLink'                : 'link',
        'mouseenter'            : 'highlight',
        'mouseleave'            : 'unhighlight'
    },
    template: _.template($('#entity-template').html()),
    initialize: function() {
        //this.listenTo(this.model, 'change', this.render);
		//console.log(this.model);
	
        this.listenTo(this.model, 'destroy', this.remove);
		
        $('#context-menu').unbind();
        $(this.el).contextmenu({
            target: '#context-menu',
            before: function (e,element) {
                e.preventDefault();
                e.stopPropagation();
                var id = element.attr('entity-id');
				var entity_type = element.attr('entity-type');
                var from_links = Links.where({from_entity_id: id});
                var to_links = Links.where({to_entity_id: id});

                this.getMenu().find('ul').empty();
				
				// View Sketchpad
				this.getMenu().find('ul').append('<li class="view-sketch" entity-id="'+id+'"><a href="#"><i class="icon-eye-open"></i>View Sketch</a><li>');
				
				//Edit subtype
				if(entity_type != 'function'){
					this.getMenu().find('ul').append('<li class="edit-subtype" type="'+entity_type+'" entity-id="'+id+'"><a href="#"><i class="icon-edit"></i>Edit Subtype</a><li>');
				}
				
				//Move entity to another category
				this.getMenu().find('ul').append('<li class="move-entity" type="'+entity_type+'" entity-id="'+id+'"><a href="#"><i class="icon-move"></i>Move</a><li>');
				
				//Delete links
                _.each(from_links, function(link){
                    var entity = Entities.findWhere({id: link.get('to_entity_id')});
                    var element = '<li link-id="' + link.get('id') + '" class="delete-link"><a href="#"><i class="icon-trash"></i>Delete link to ' + entity.get('name') + '</a><li>';
                    this.getMenu().find('ul').append(element);
                },this);

                _.each(to_links, function(link){
                    var entity = Entities.findWhere({id: link.get('from_entity_id')});
                    var element = '<li link-id="' + link.get('id') + '" class="delete-link"><a href="#"><i class="icon-trash"></i>Delete link to ' + entity.get('name') + '</a><li>';
                    this.getMenu().find('ul').append(element);
                },this);
				
                //return (from_links.length > 0 || to_links.length > 0);
				return true;
            },
            onItem: function(e, item){
                e.preventDefault();
                e.stopPropagation();
				
				var parent = $(e.target).parent();
				var parent_id = $(parent).attr('entity-id');
				var parent_type = $(parent).attr('type');
				
				//Delete Link
				if($(parent).attr('class') == 'delete-link'){
					var link_id = $(e.target).parent().attr('link-id');
					$('#context-menu').removeClass('open');
					if (confirm("Are you sure you want to delete this?")){
						var link = Links.findWhere({id: link_id});
						if (link){
							link.destroy();
						}
					}
				} 
				//Edit subtype
				else if($(parent).attr('class') == 'edit-subtype'){
					$("#context-menu").removeClass('open');
					$("#edit-subtype-overlay, #edit-subtype-menu").addClass("active");
					var select = $("select[type='"+parent_type+"']").html();
					$("#edit-subtype-options select").html(select);
					$("#edit-subtype-menu #save_subtype").attr("onclick", "editSubtype('"+parent_id+"')");
				} 
				//View Sketch
				else if($(parent).attr('class') == 'view-sketch'){ 
					$("#sketchpad-container #sketch-entity-id").val(parent_id);
					$("#context-menu").removeClass('open');
					$("#sketchpad-overlay, #sketchpad-container").addClass("active");
					load_sketchpad_data(parent_id);
					view_sketch();
				} 
				//Move Entity
				else if($(parent).attr('class') == 'move-entity'){ 
					alert("Move");
				}
            }
        });

    },
    render: function (eventName) {
	    var decomps = Decompositions.where({entity_id: this.model.get('id')});
        $(this.el).attr('entity-id', this.model.get('id'))
				  .attr('entity-type', this.model.get('type'))
				  .attr('entity-subtype', this.model.get('subtype'))
                  .html(this.template(_.extend({}, 
                        {"num_decomps": decomps.length}, 
                        this.model.toJSON())));
        if (this.model.get('current_decomposition') != null) {
            var decomp = Decompositions.findWhere({id: this.model.get('current_decomposition')});
            this.decomp_view = new DecompositionView({model: decomp});
            $(this.el).append(this.decomp_view.render().el);
        }
        else{
            $(this.el).append('<ul></ul>');
        }
        return this;
    },
	//delete entity
    trash: function(){
        if (confirm("Are you sure you want to delete this?")){
            this.removeChildFromDecomp(this.model);
			gEntityType = this.model.get("type").charAt(0).toUpperCase() + this.model.get("type").slice(1) + 's';
			gAction = gEntityType +" - Deleted entity '"+this.model.get("name")+"'";
			$('li[entity-id="' + this.model.id + '"]').addClass('highlight-delete');
			save_action(gAction);
			$('li[entity-id="' + this.model.id + '"]').removeClass('highlight-delete');
			this.model.destroy();
			//save_action(gAction);
        } else {
			return false;
		}
    },
    makeEditable: function(e){
        e.stopPropagation();
        $(this.el).parents('.entity-list').sortable('disable');
        $('li[entity-id="' + this.model.id + '"] > .editable').attr('contenteditable', true);
        $('li[entity-id="' + this.model.id + '"] > .editable').focus();
    },
	// rename entity
    blur: function(e){
        $(this.el).parents('.entity-list').sortable('enable');
        $('li[entity-id="' + this.model.id + '"] > .editable').attr('contenteditable', false);
		
		var new_name = $('li[entity-id="' + this.model.id + '"] > .editable').text().trim();
        if (new_name.length > 0) {
			var old_name = this.model.get("name");
            this.model.set('name', new_name);
            this.model.save({wait: true});
			
			gEntityType = this.model.get("type").charAt(0).toUpperCase() + this.model.get("type").slice(1) + 's';
			gAction = gEntityType +" - Entity '"+old_name+"' renamed to '"+new_name+"'";
			$('li[entity-id="' + this.model.id + '"]').addClass('highlight-rename');
			save_action(gAction);
			$('li[entity-id="' + this.model.id + '"]').removeClass('highlight-rename');
        } else {
            $('li[entity-id="' + this.model.id + '"] > .editable').text(this.model.get('name'));
        }
    },
    edit: function(e){
        if (e.keyCode == 13) {
            e.preventDefault();
            $(':focus').blur();
        }
    },
    highlightLinked: function(entity_id){
        links = Links.where({from_entity_id: entity_id});
        if (links){
            _.each(links, function(link){
                id = link.get('to_entity_id');
                selector = $('li[entity-id="' + id + '"]');
                while (selector.length == 0){
                    var entity = Entities.findWhere({id: id});
                    if (entity.get('decomposition_id') == null){
                         break;
                    }
                    var decomp = Decompositions.findWhere({id: entity.get('decomposition_id')})
                    if (!decomp){
                        break;
                    }
                    id = decomp.get('entity_id');
                    selector = $('li[entity-id="' + id + '"]');
                }
                selector.addClass('active');
            }, this);
        }

        links = Links.where({to_entity_id: entity_id});
        if (links){
            _.each(links, function(link){
                id = link.get('from_entity_id');
                selector = $('li[entity-id="' + id + '"]');
                while (selector.length == 0){
                    var entity = Entities.findWhere({id: id});
                    if (entity.get('decomposition_id') == null){
                         break;
                    }
                    var decomp = Decompositions.findWhere({id: entity.get('decomposition_id')})
                    if (!decomp){
                        break;
                    }
                    id = decomp.get('entity_id');
                    selector = $('li[entity-id="' + id + '"]');
                }
                selector.addClass('active');
                //$('li[entity-id="' + link.get('from_entity_id') + '"]').addClass('active');
            }, this);
        }
    },
    unhighlight: function(e){
        if (!dragging){
            $('.active').removeClass('active');
            var elm = $(e.target).parent().parent();
            if(elm.length > 0){
                $(e.target).parent().parent().addClass('active');
                var id = $(e.target).parent().parent().attr('entity-id');
                if (id){
                    this.highlightLinked(id);                
                }
            }
        }
    },
    highlight: function(e){
        if (!dragging){
            $('.active').removeClass('active');
            $(e.target).addClass('active');
            this.highlightLinked($(e.target).attr('entity-id'));
        }
    },
    link: function(e,o){
        e.stopPropagation();
        id1 = this.model.get('id');
        id2 = $(o.el).parent().attr('entity-id');

        var entity1 = Entities.findWhere({id: id1});
        var entity2 = Entities.findWhere({id: id2});

        if( entity1.get('type') == 'requirement' ) {
            from = entity2.get('id');  
            to = entity1.get('id');
        }
        else if( entity2.get('type') == 'requirement' ) {
            from = entity1.get('id');
            to = entity2.get('id');  
        }
        else if( entity1.get('type') == 'usescenario' ) {
            from = entity2.get('id');  
            to = entity1.get('id');
        }
        else if( entity2.get('type') == 'usescenario' ) {
            from = entity1.get('id');
            to = entity2.get('id');  
        }
        else if( entity1.get('type') == 'function' ) {
            from = entity2.get('id');  
            to = entity1.get('id');
        }
        else if( entity2.get('type') == 'function' ) {
            from = entity1.get('id');
            to = entity2.get('id');  
        }
        else if( entity1.get('type') == 'behavior' ) {
            from = entity2.get('id');  
            to = entity1.get('id');
        }
        else if( entity2.get('type') == 'behavior' ) {
            from = entity1.get('id');
            to = entity2.get('id');  
        }
        else if( entity1.get('type') == 'artifact' ) {
            from = entity2.get('id');  
            to = entity1.get('id');
        }
        else{
            from = entity1.get('id');
            to = entity2.get('id');  
        }
		
		type = entity1.get('type').substring(0, 3) + "_" + entity2.get('type').substring(0, 3);
		
		/*
        if((entity1.get('type') == 'requirement' || entity1.get('type') == 'function') &&
           (entity2.get('type') == 'requirement' || entity2.get('type') == 'function')){ 
                     type = "satisfies"; 
                 }
        else if((entity1.get('type') == 'requirement' || entity1.get('type') == 'artifact') &&
                (entity2.get('type') == 'requirement' || entity2.get('type') == 'artifact')){ 
                     type = "fulfills"; 
                 }
        else if((entity1.get('type') == 'requirement' || entity1.get('type') == 'behavior') &&
                (entity2.get('type') == 'requirement' || entity2.get('type') == 'behavior')){ 
                     type = "manages"; 
                 }
        else if((entity1.get('type') == 'function' || entity1.get('type') == 'behavior') &&
                (entity2.get('type') == 'function' || entity2.get('type') == 'behavior')){ 
                     type = "controls"; 
                 }
        else if((entity1.get('type') == 'function' || entity1.get('type') == 'artifact') &&
                (entity2.get('type') == 'function' || entity2.get('type') == 'artifact')){ 
                     type = "realizes"; 
                 }
        else if((entity1.get('type') == 'behavior' || entity1.get('type') == 'artifact') &&
                (entity2.get('type') == 'behavior' || entity2.get('type') == 'artifact')){ 
                     type = "parameterizes"; 
                 }
        else{
            type = "is related to";
        }
		*/
		
		var entity1_Type = entity1.get("type").charAt(0).toUpperCase() + entity1.get("type").slice(1) + "s";
		var entity2_Type = entity2.get("type").charAt(0).toUpperCase() + entity2.get("type").slice(1) + "s";

		gAction = "Linked - "+ entity1_Type +" Entity '"+entity1.get("name")+"' and "+ entity2_Type +" Entity '"+entity2.get("name")+"'";
		gFromId = from;
		gToId = to;
		gIsLink = true;
		//console.log(id1 + " - " + id2);
		Links.create({
            from_entity_id: from, 
            to_entity_id: to, 
            type: type,
            problem_map_id: pMapId}, 
            {wait: true});
    },
    drop: function(e,o){
		e.stopPropagation();
		var child_id = $(e.target).attr('entity-id');
		var c_model = Entities.findWhere({id: child_id});
		gEntityType = c_model.get("type").charAt(0).toUpperCase() + c_model.get("type").slice(1) + 's';
		
		if ($(e.target).parent()[0].className == 'entity-list') {
			//$(".overlay").addClass("active");
			gAction = gEntityType +" - Entity '"+c_model.get("name")+"' removed from decomposition";
			this.addChildToBase(c_model);
		} else {
			gAction = gEntityType +" - Entity '"+c_model.get("name")+"' added to decomposition";
			var parent_id = $(e.target).parent().parent().attr('entity-id');
			var p_model = Entities.findWhere({id: parent_id});
			this.addChildToParent(c_model, p_model);
		}
    },
    removeChildFromDecomp: function(c_model){
        if (c_model.get('decomposition_id')){
            parent_decomp = Decompositions.findWhere({id: c_model.get('decomposition_id')});
            p_model = Entities.findWhere({id: parent_decomp.get('entity_id')});
            child_entities = Entities.where({decomposition_id: parent_decomp.get('id')});
            child_decomps = Decompositions.where({entity_id: parent_decomp.get('entity_id')});

            if (child_entities.length == 1) { 
                p_model.set('current_decomposition', null);
                p_model.save();

                if (child_decomps.length == 1){
                    $('li[entity-id="' + parent_decomp.get('entity_id') + '"] > i.icon')
                        .removeClass('icon-folder-open')
                        .removeClass('icon-folder-close')
                        .addClass('icon-file');
                    $('li[entity-id="' + p_model.get('id') + '"] > .sup').text('');
                } else {
                    $('li[entity-id="' + parent_decomp.get('entity_id') + '"] > i.icon')
                        .removeClass('icon-folder-open')
                        .removeClass('icon-file')
                        .addClass('icon-folder-close');
                    if (child_decomps.length == 2) {
                        $('li[entity-id="' + p_model.get('id') + '"] > .sup').text('');
                    } else {
                        $('li[entity-id="' + p_model.get('id') + '"] > .sup').text(child_decomps.length - 1);
                    }
                }
                parent_decomp.destroy();
            }

            c_model.set('decomposition_id', null);
            c_model.save();
        }
    },
    addChildToBase: function(c_model){
        this.removeChildFromDecomp(c_model);

        _.each(Entities.where({decomposition_id: null, type: c_model.get('type')}),function(entity){
            entity.set('list_order', $('li[entity-id="' + entity.get('id') + '"]').index());
            entity.save();
        });
    },
    addChildToParent: function(c_model, p_model){
		did = c_model.get('decomposition_id');
		
        if (did == null || did != p_model.get('current_decomposition')){
            this.removeChildFromDecomp(c_model);

            //TODO need to add/update folder count
            $('li[entity-id="' + p_model.get('id') + '"] > i.icon')
                .removeClass('icon-file')
                .removeClass('icon-folder-closed')
                .addClass('icon-folder-open folder');

            if (p_model.get('current_decomposition') == null){
                var decomp = new Decomposition({entity_id: p_model.get('id'), 
                    problem_map_id: pMapId});
                var decomps = Decompositions.where({entity_id: p_model.get('id')});
                if (decomps.length > 0){
                    $('li[entity-id="' + p_model.get('id') + '"] > .sup').text(decomps.length+1);
                }
				/*
                decomp.save().done(function(){
                    Decompositions.add(decomp);
                    //var decomps = Decompositions.where({entity_id: p_model.get('id')});
                    p_model.set('current_decomposition', decomp.id);
                    p_model.save();
					
					c_model.set('decomposition_id', decomp.id);
                    c_model.save();

					_.each(Entities.where({decomposition_id: c_model.get('decomposition_id')}), function(entity){
						entity.set('list_order', $('li[entity-id="' + entity.get('id') + '"]').index());
						entity.save();
					});
				}, this);
				*/
				
				decomp.save({}, {
					wait: true,
					success: function (model, response){
						Decompositions.add(model);
						p_model.set('current_decomposition', model.id);
						p_model.save();
						
						c_model.set('decomposition_id', model.id);
						c_model.save();

						_.each(Entities.where({decomposition_id: c_model.get('decomposition_id')}), function(entity){
							entity.set('list_order', $('li[entity-id="' + entity.get('id') + '"]').index());
							entity.save();
						});
					}
				});
			} 
            else{
                c_model.set('decomposition_id', p_model.get('current_decomposition'));
                c_model.save();
				_.each(Entities.where({decomposition_id: c_model.get('decomposition_id')}), function(entity){
						entity.set('list_order', $('li[entity-id="' + entity.get('id') + '"]').index());
						entity.save();
					});
            }
        } else {
			_.each(Entities.where({decomposition_id: c_model.get('decomposition_id')}), function(entity){
				entity.set('list_order', $('li[entity-id="' + entity.get('id') + '"]').index());
				entity.save();
			});
		}
    },
    renderGroup: function(){

        //Render the appropriate group
        if (this.model.get('type') == 'requirement'){
            $(Req.el.children[3].children).empty();
            //Req.render();
            Req.addAll();
        }
        if (this.model.get('type') == 'usescenario'){
            $(Usr.el.children[3].children).empty();
            //Usr.render();
            Usr.addAll();
        }
        if (this.model.get('type') == 'function'){
            $(Fun.el.children[3].children).empty();
            //Fun.render();
            Fun.addAll();
        }
        if (this.model.get('type') == 'artifact'){
            $(Art.el.children[3].children).empty();
            //Art.render();
            Art.addAll();
        }
        if (this.model.get('type') == 'behavior'){
            $(Beh.el.children[3].children).empty();
            //Beh.render();
            Beh.addAll();
        }
        if (this.model.get('type') == 'issue'){
            $(Iss.el.children[3].children).empty();
            //Iss.render();
            Iss.addAll();
        }
    },
    toggleCollapse: function(e){
        e.stopPropagation();
		// collapse decomposition
        if ( this.model.get('current_decomposition') ){
            this.model.set('current_decomposition', null);
            this.model.save({wait:true});
            this.renderGroup();
			gEntityType = this.model.get("type").charAt(0).toUpperCase() + this.model.get("type").slice(1) + 's';
			gAction = gEntityType +" - Current Decomposition set to NULL for entity '"+this.model.get("name")+"'";
			
			$('li[entity-id="' + this.model.id + '"]').addClass('highlight-decomposition');
			save_action(gAction);
			$('li[entity-id="' + this.model.id + '"]').removeClass('highlight-decomposition');
        }
		// choose decomposition
        else{
            var decomps = Decompositions.where({entity_id: this.model.get('id')});
            if (decomps.length > 0){
                $('#myModal').modal();
                $('.temp-decomps').empty();
            }
            _.each(decomps, function (decomp) {
                $('.temp-decomps').append('<ul class="alt-decomp" decomp-id="' + decomp.get('id') + '"></ul');
                var curr = this;
                var did = decomp.get('id');
                $('.temp-decomps > ul[decomp-id="' + decomp.get('id') + '"]').on("click", function(e){
                    curr.model.set('current_decomposition', did);
                    curr.model.save({wait: true});
                    curr.renderGroup();
                    $('#myModal').modal('hide');
					gEntityType = curr.model.get("type").charAt(0).toUpperCase() + curr.model.get("type").slice(1) + 's';
					gAction = gEntityType +" - Current Decomposition set to "+did+" for entity '"+curr.model.get("name")+"'";
					
					$('li[entity-id="' + curr.model.id + '"]').addClass('highlight-decomposition');
					save_action(gAction);
					$('li[entity-id="' + curr.model.id + '"]').removeClass('highlight-decomposition');
                });
                var entities = Entities.where({decomposition_id: decomp.get('id')});
                _.each(entities, function (entity) {
                    $('.temp-decomps ul[decomp-id="' + decomp.get('id') + '"]').append('<li class="entity">' + entity.get('name') + '</li>');
                }, this);
            }, this);
        }
    }
});

var DecompositionView = Backbone.View.extend({
    tagName: 'ul',
    initialize: function(){
        this.entity_views = [];
    },
    render: function (eventName) {
        var entities = Entities.where({decomposition_id: this.model.get('id')});
        _.each(entities, function(entity){
            var entity_view = new EntityView({model: entity});
            this.entity_views.push(entity_view);
            $(this.el).append(entity_view.render().el);
        }, this);
        return this;
    },
});

var EntityListView = Backbone.View.extend({
    tagName: 'div',
    attributes: {
        class: 'row-fluid entity-group span2'
    },
    template: _.template($('#entity-tab-template').html()),
    initialize: function(options){
        this.type = options.type;
		type = options.type;
		
        // if (this.type == 'requirement')
            // $(this.el).addClass('offset1');
		
		if(this.type!='usescenario')
			this.title = this.type.charAt(0).toUpperCase() + this.type.slice(1) + 's';
		else
			this.title = 'Use Scenario';
			
        this.listenTo(Entities, 'add', this.addOne);
        this.listenTo(Entities, 'reset', this.addAll);
	},
    render: function(){
		
        $(this.el).attr('id', this.type + 's-tab')
            .html(this.template({type: this.type,
                                 title: this.title}));
        this.input = $(this.el).find('.entity-input');

        $(this.el).find('#' + this.type).sortable({
            group: "ma-groups",
            onDragStart: function ($item, container, _super){
                
                $item.removeClass('active');
                dragging = true; 

                position = $item.index(); 
                original_container = $item.parent(); 

                parent_container = $(container.el);
                if (!parent_container.hasClass('entity-list')){
                    parent_container = parent_container.parents('.entity-list');
                }
                type = parent_container.attr('id');

                _super($item, container);
            },
            placeholder: '<li class="placeholder"/>',
            afterMove: function($placeholder, container) {
                $($placeholder)
                    .removeClass('requirement')
                    .removeClass('usescenario')
                    .removeClass('function')
                    .removeClass('artifact')
                    .removeClass('behavior')
                    .removeClass('issue')
                    .addClass(type);

                if (oldContainer && oldContainer != container){
                    $(oldContainer.el).parent().removeClass('active');
                    $(oldContainer.el).parent().removeClass(type);
                }  
                $(container.el).parent().addClass('active');
                $(container.el).parent().addClass(type);
                oldContainer = container;
            },
            onDrop: function(item, container, _super){
				//$(".overlay").addClass("active");
                dragging = false;
				
                $(container.el).parent().removeClass('active');
                $(container.el).parent().removeClass(type);

                parent_container = $(container.el);
                if (!parent_container.hasClass('entity-list')){
                    parent_container = parent_container.parents('.entity-list');
                }

                if (parent_container.attr('id') != type){
                    elm = original_container.find('li:eq(' + position + ')');
                    if (elm.length != 0){
                        elm.before(item);
                    }
                    else{
                        original_container.append(item);
                    }
                    if (!$(container.el).hasClass('entity-list')){
                        item.trigger('onLink',container);
                    }
                }
                else{
                    item.trigger('onDrop', container);
                }
                _super(item,container);
				
				if(gIsLink){
					gIsLink = false;
					$("li[entity-id='" + gFromId + "'], li[entity-id='" + gToId + "']").addClass('highlight-link');
					save_action(gAction);
					$("li[entity-id='" + gFromId + "'], li[entity-id='" + gToId + "']").removeClass('highlight-link');
				} else {
					$(item).addClass('highlight-decomposition');
					save_action(gAction);
					$(item).removeClass('highlight-decomposition');
				}
            },
        });
        return this;
    },
    events: {
        'keypress input'    : 'keyPress',
        'click button'      : 'newEntity',
    },
    addOne: function(entity) {
        if ( entity.get('decomposition_id') !== null ){
            return;
        }
        if ( entity.get('type') !== this.type ){
            return;
        }
        var view = new EntityView({model: entity});
        if (entity.get('list_order') == 0){
            $('#' + this.type).prepend(view.render().el);
        }
        else{
            $('#' + this.type).append(view.render().el);
        }
		
		if(gActionFlag){
			gActionFlag = false;
			$('li[entity-id="' + entity.id + '"]').addClass('highlight-newentity');
			save_action(gAction);
			$('li[entity-id="' + entity.id + '"]').removeClass('highlight-newentity');
		}
    },
    addAll: function() {
        Entities.each(this.addOne, this);
    },
    newEntity: function(e){
        if(!this.input.val()) return;
		var sub_type = $('select.'+this.type+'-subtypes').val();

        Entities.create({
				name: this.input.val(), 
				type: this.type, 
				subtype: sub_type,
				problem_map_id: pMapId
			},{wait: true}
		);
		
		gEntityType = this.type.charAt(0).toUpperCase() + this.type.slice(1) + 's';
		gAction = gEntityType +" - Added new Entity '"+this.input.val()+"'";
		gActionFlag = true;
		this.input.val(''); // reset input
		$("select."+this.type+"-subtypes").val(""); // reset subtype drop-down
    },
    keyPress: function(e){
        if (e.keyCode != 13) return;
        this.newEntity(e);
    },
});

/* Get data from server */
Links.fetch().done(function() {
    Decompositions.fetch().done(function(){
        Entities.fetch().done(function(){

            /* Construct the views */
            Req = new EntityListView({type: 'requirement'});
            Usr = new EntityListView({type: 'usescenario'});
            Fun = new EntityListView({type: 'function'});
            Art = new EntityListView({type: 'artifact'});
            Beh = new EntityListView({type: 'behavior'});
            Iss = new EntityListView({type: 'issue'});
            
            /* Load all the tabs */
            $('#tabs').append(Req.render().el);
            $('#tabs').append(Usr.render().el);
            $('#tabs').append(Fun.render().el);
            $('#tabs').append(Art.render().el);
            $('#tabs').append(Beh.render().el);
            $('#tabs').append(Iss.render().el);

			/* Display Entity Subtypes - Start */
			var type_arr = [];
			type_arr.push(Req.type);
			type_arr.push(Usr.type);
			type_arr.push(Fun.type);
			type_arr.push(Art.type);
			type_arr.push(Beh.type);
			type_arr.push(Iss.type);
			
			subtypes = JSON.parse($('textarea#entity-subtypes').val());
			for(var index in type_arr){
				var entity_type = type_arr[index];
				if(entity_type == 'function'){
					$('select.'+entity_type+'-subtypes').attr('disabled','disabled');
					continue ;
				}
				var option_html = '';
				option_html += '<option value="">-- Select --</option>';
				for (var index in subtypes[entity_type]) {
					option_html += '<option value="'+subtypes[entity_type][index]+'">'+subtypes[entity_type][index]+'</option>'
				}
				$('select.'+entity_type+'-subtypes').html(option_html);
			}
			/* Display Entity Subtypes - End */
			
            /* Load the tooltips */ 
            $('#requirement-tooltip').tooltip({
                html: true,
                title: "Requirement are the objectives, goals, and constraints that must be addressed in the final design. There may be both functional and structural requirements (e.g., must allow contact info to be stored). "});
            $('#usescenario-tooltip').tooltip({
                html: true,
                title: "Use Scenario."});
            $('#function-tooltip').tooltip({
                html: true,
                title: "Functions are the actions and procedures of the design. Usually these contain a verb (e.g., create contact)."});
            $('#artifact-tooltip').tooltip({
                html: true,
                title: "Artifacts are the structures and components of the design. Usually these contain a noun (e.g., add contact button)."});
            $('#behavior-tooltip').tooltip({
                html: true,
                title: "Behaviors are the principles or laws that govern how the artifacts and functions interact (e.g., button performs function when tapped)"});
            $('#issue-tooltip').tooltip({
                html: true,
                title: "Issues are the questions and concerns that might arise during the course of creating a design (e.g., will my application support blind users?)"});
			
			/* CSV stuff */
			/*
            $('#requirement-csv').click(function(){
                outputCSV('requirement');
            });
            $('#function-csv').click(function(){
                outputCSV('function');
            });
            $('#artifact-csv').click(function(){
                outputCSV('artifact');
            });
            $('#behavior-csv').click(function(){
                outputCSV('behavior');
            });
            $('#issue-csv').click(function(){
                outputCSV('issue');
            });
			*/

            /* load all the entities */
            Req.addAll();
            Usr.addAll();
            Fun.addAll();
            Art.addAll();
            Beh.addAll();
            Iss.addAll();
        });    
    });
});

/*
function outputCSV(group){
    var csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Parent" + ", ";
    csvContent += "Child" + ", ";
    csvContent += "Decomp" + "\n";

    Entities.where({type: group}).forEach(function(element, index){

        if (element.get('decomposition_id') != null){
            decomp = Decompositions.findWhere({id: element.get('decomposition_id')});
            if (decomp){
                parent_ent = Entities.findWhere({id: decomp.get('entity_id')});
                csvContent += parent_ent.get('name') + ", ";
                csvContent += element.get('name') + ", ";
                csvContent += element.get('decomposition_id') + "\n";
            }
        }
        else{
            csvContent += ", ";
            csvContent += element.get('name') + ", ";
            csvContent += ", \n";
        }
    });    

    var encodedUri = encodeURI(csvContent);
    window.open(encodedUri);
}
*/

function updateSearchIcon(e){
    if($(e.target).val().length > 0){
        $(e.target).parent().find('i')
            .removeClass('icon-search')
            .addClass('icon-remove-sign')
            .unbind()
            .on('click', function(e){
                $(e.target).parent().parent().find('input').val('');
                $(e.target).parent().parent().find('input').change();
                $('.active').removeClass('active');
            });
    }
    else{
        $(e.target).parent().find('i')
            .unbind()
            .removeClass('icon-remove-sign')
            .addClass('icon-search');
    }

    var entity = Entities.findWhere({name: $(e.target).val()});

    if (entity){
        id = entity.get('id');
        selector = $('li[entity-id="' + id + '"]');
        while (selector.length == 0){
            var entity = Entities.findWhere({id: id});
            if (entity.get('decomposition_id') == null){
                 break;
            }
            var decomp = Decompositions.findWhere({id: entity.get('decomposition_id')})
            if (!decomp){
                break;
            }
            id = decomp.get('entity_id');
            selector = $('li[entity-id="' + id + '"]');
        }
        $('.active').removeClass('active');
        selector.addClass('active');
    }
}

$('.search-query').on('keyup', function(e){
    updateSearchIcon(e);
});
$('.search-query').on('change', function(e){
    updateSearchIcon(e);
});

$('.search-query').typeahead({
    source: function(query, process){
        return Entities.pluck('name');
    }
});

});

/* Modify the subtype of an entity */
function editSubtype(id){
	$("#save_subtype_msg").addClass("hidden");
	var entity = Entities.findWhere({id: id});
	var subtype = $("#edit-subtype-options select").val();
	entity.save({'subtype': subtype}, {
					success: function (model, response){
						$("li.entity[entity-id="+id+"]").attr("entity-subtype",subtype);
						$("#save_subtype_msg").removeClass("hidden");
					}
				});
}

/* Close the edit subtype pop-up */
function closeEditSubtype(){
	$("#edit-subtype-overlay, #edit-subtype-menu").removeClass("active");
	$("#save_subtype_msg").addClass("hidden");
}

/* Delete invalid current decomposition ids */
function reset_invalid_decompid(){
	$.get(subdirectory+"/problem_maps/reset_invalid_current_decomposition/"+pMapId, function(){
		location.reload();
	});
}

/* Function to save html snapshot of each action */
function save_action(action){
	var url = window.location.pathname; 
    var pMapId = url.substr(url.lastIndexOf('/') + 1);
	
	var html_str = $("#tabs").html();
	$.post("../../problem_maps/save_action",{action: action, problem_map_id: pMapId, htmlstring: html_str},function(){});
 }

/* Load sketchpad data from DB */
function load_sketchpad_data(id){
	var entity = Entities.findWhere({id: id});
	var sketchdata = entity.get('sketch_data');
	$("textarea#sketchpad-data").val(sketchdata);
}
 
/* Save sketchpad data */
function save_sketch(id){
	var entity = Entities.findWhere({id: id});
	var sketchdata = $("textarea#sketchpad-data").val();
	entity.save({'sketch_data': sketchdata});
}