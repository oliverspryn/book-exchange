var Renderer = function(canvas) {
//The canvas and item renderer objects
	var jQCanvas = $(canvas);
	var win = $(window);
	var canvas = jQCanvas.get(0);
	var ctx = canvas.getContext('2d');
	var gfx = arbor.Graphics(canvas);
 	var particleSystem = null;

//Mouse-related variables and objects
	var mouseP = null;	
	var nearest = null;
	var selected = null;

	var that = {
	//Called on plugin initialization
		init : function(system) {
			particleSystem = system;

			particleSystem.screen({
				padding : [50, 60, 50, 60],
				size    : {
					height : jQCanvas.height(),
					width  : win.width() - 350
				}
			}); 

			win.resize(that.resize);
			that.resize();
			that.initMouseHandling();
		},
		
	//Called when the window is resized
		resize : function() {
			particleSystem.screen({
				padding : [50, 60, 50, 60],
				size    : {
					height : jQCanvas.height(),
					width  : win.width() - 350
				}
			});
	
			that.redraw();
		},
		
	//Called on each frame update to draw the nodes and connect them with edges
		redraw : function() {
			gfx.clear();

			particleSystem.eachEdge(function(edge, node1, node2) {
				if (edge.source.data.alpha && edge.target.data.alpha) {
					gfx.line(node1, node2, {
						alpha  : edge.target.data.alpha,
						length : edge.source.data.length,
						stroke : '#CCCCCC',
						width  : 2
					});
				}
			});

			particleSystem.eachNode(function(node, point) {
				var label = node.name;
				var width = ctx.measureText(label).width + 25;

				if (node.data.alpha == 0) {
					return;
				}

				if (node.data.shape == 'dot') {
					gfx.oval(point.x - width/2, point.y - width/2, width, width, {
						alpha : node.data.alpha,
						fill  : node.data.color
					});
				} else {
					gfx.rect(point.x - width/2, point.y - 10, width, 20, 4, {
						alpha : node.data.alpha,
						fill  : node.data.color
					});
				}

				gfx.text(label, point.x, point.y + 7, {
					align : 'center',
					color : 'white',
					font  : 'Arial',
					size  : 12
				});
			});
		},

	//Handle all mouse events
		initMouseHandling : function() {
			var dragged = null;
			var nearestName = null;
			var oldmass = 1;

			var handler = {
			//Handle mouse click events
				clicked : function(e) {
					var pos = jQCanvas.offset();
					mouseP = arbor.Point(e.pageX - pos.left, e.pageY - pos.top);
					nearest = dragged = particleSystem.nearest(mouseP);
					
					if (nearest && selected && nearest.node === selected.node) {
						document.location.href = selected.node.data.link;
						return false;
					}

					if (dragged && dragged.node !== null) {
						dragged.node.fixed = true;
					}

					jQCanvas.bind('mousemove', handler.dragged);
					jQCanvas.bind('mousemove', handler.moved);
					win.bind('mouseup', handler.dropped);

					return false
				},

			//Handle node drag events
				dragged : function(e) {
					var pos = jQCanvas.offset();
					var point = arbor.Point(e.pageX - pos.left, e.pageY - pos.top);

					if (!nearest) {
						return;
					}

					if (dragged !== null && dragged.node !== null) {
						var particlePos = particleSystem.fromScreen(point);
						dragged.node.p = particlePos;
					}

					return false
				},

			//Handle node dropped events
				dropped : function(e) {
					if (dragged === null || dragged.node === undefined) {
						return;
					}

					if (dragged && dragged.node !== null) {
						dragged.node.fixed = true;
					}

					dragged.node.tempMass = 50;
					dragged = null;
					selected = null;
					mouseP = null;

					jQCanvas.unbind('mousemove', handler.dragged);
					win.unbind('mouseup', handler.dropped);

					return false;
				},

			//Handle mouse move events
				moved : function(e) {
					var pos = jQCanvas.offset();
					mouseP = arbor.Point(e.pageX - pos.left, e.pageY - pos.top);
					nearest = particleSystem.nearest(mouseP);

					if (!nearest.node) {
						return false;
					}

					if (nearest.node.data.shape != 'dot') {
						selected = (nearest.distance < 50) ? nearest : null;

						if (selected) {
							jQCanvas.addClass('link');
						} else {
							jQCanvas.removeClass('link');
						}
					} else {
						if (!isNaN(nearest.node.name)) {
							if (nearest.node.name != nearestName) {
								nearestName = nearest.node.name;

							//Show the hidden leaf nodes
								var parent = particleSystem.getEdgesFrom(nearestName)[0].source;
								var children = $.map(particleSystem.getEdgesFrom(nearestName), function(edge) {
									return edge.target;
								});

								particleSystem.eachNode(function(node) {
									if (node.data.shape == 'dot') {
										return; //Leaf nodes are rectangles
									}

									var visible = ($.inArray(node, children) != -1);
									var alpha = visible ? 1 : 0;
									var duration = .5;

									particleSystem.tweenNode(node, duration, {
										alpha : alpha
									});

									if (alpha) {
										node.p.x = parent.p.x + 0.05 * Math.random();
										node.p.y = parent.p.y + 0.05 * Math.random();
										node.tempMass = 0.001;
									}
								});
							}

							jQCanvas.removeClass('link');
						}
					}

					return false;
				}
			}

			jQCanvas.mousedown(handler.clicked);
			jQCanvas.mousemove(handler.moved);
		}
	}

	return that
}
