//
//  barnes-hut.js
//
//  implementation of the barnes-hut quadtree algorithm for n-body repulsion
//  http://www.cs.princeton.edu/courses/archive/fall03/cs126/assignments/barnes-hut.html
//
//  Created by Christian Swinehart on 2011-01-14.
//  Copyright (c) 2011 Samizdat Drafting Co. All rights reserved.
//

  var BarnesHutTree = function(){
    var _branches = []
    var _branchCtr = 0
    var _root = null
    var _theta = .5
    
    var that = {
      init:function(topleft, bottomright, theta){
        _theta = theta

        // create a fresh root node for these spatial bounds
        _branchCtr = 0
        _root = that._newBranch()
        _root.origin = topleft
        _root.size = bottomright.subtract(topleft)
      },
      
      insert:function(newParticle){
        // add a particle to the tree, starting at the current _root and working down
        var node = _root
        var queue = [newParticle]

        while (queue.length){
          var particle = queue.shift()
          var p_mass = particle._m || particle.m
          var p_quad = that._whichQuad(particle, node)

          if (node[p_quad]===undefined){
            // slot is empty, just drop this node in and update the mass/c.o.m.
            node[p_quad] = particle
            node.mass += p_mass
            if (node.p){
              node.p = node.p.add(particle.p.multiply(p_mass))
            }else{
              node.p = particle.p.multiply(p_mass)
            }
            
          }else if ('origin' in node[p_quad]){
            // slot conatins a branch node, keep iterating with the branch
            // as our new root
            node.mass += (p_mass)
            if (node.p) node.p = node.p.add(particle.p.multiply(p_mass))
            else node.p = particle.p.multiply(p_mass)
            
            node = node[p_quad]
            queue.unshift(particle)
          }else{
            // slot contains a particle, create a new branch and recurse with
            // both points in the queue now
            var branch_size = node.size.divide(2)
            var branch_origin = new Point(node.origin)
            if (p_quad[0]=='s') branch_origin.y += branch_size.y
            if (p_quad[1]=='e') branch_origin.x += branch_size.x

            // replace the previously particle-occupied quad with a new internal branch node
            var oldParticle = node[p_quad]
            node[p_quad] = that._newBranch()
            node[p_quad].origin = branch_origin
            node[p_quad].size = branch_size
            node.mass = p_mass
            node.p = particle.p.multiply(p_mass)
            node = node[p_quad]

            if (oldParticle.p.x===particle.p.x && oldParticle.p.y===particle.p.y){
              // prevent infinite bisection in the case where two particles
              // have identical coordinates by jostling one of them slightly
              var x_spread = branch_size.x*.08
              var y_spread = branch_size.y*.08
              oldParticle.p.x = Math.min(branch_origin.x+branch_size.x,  
                                         Math.max(branch_origin.x,  
                                                  oldParticle.p.x - x_spread/2 + 
                                                  Math.random()*x_spread))
              oldParticle.p.y = Math.min(branch_origin.y+branch_size.y,  
                                         Math.max(branch_origin.y,  
                                                  oldParticle.p.y - y_spread/2 + 
                                                  Math.random()*y_spread))
            }

            // keep iterating but now having to place both the current particle and the
            // one we just replaced with the branch node
            queue.push(oldParticle)
            queue.unshift(particle)
          }

        }

      },

      applyForces:function(particle, repulsion){
        // find all particles/branch nodes this particle interacts with and apply
        // the specified repulsion to the particle
        var queue = [_root]
        while (queue.length){
          node = queue.shift()
          if (node===undefined) continue
          if (particle===node) continue
          
          if ('f' in node){
            // this is a particle leafnode, so just apply the force directly
            var d = particle.p.subtract(node.p);
            var distance = Math.max(1.0, d.magnitude());
            var direction = ((d.magnitude()>0) ? d : Point.random(1)).normalize()
            particle.applyForce(direction.multiply(repulsion*(node._m||node.m))
                                      .divide(distance * distance) );
          }else{
            // it's a branch node so decide if it's cluster-y and distant enough
            // to summarize as a single point. if it's too complex, open it and deal
            // with its quadrants in turn
            var dist = particle.p.subtract(node.p.divide(node.mass)).magnitude()
            var size = Math.sqrt(node.size.x * node.size.y)
            if (size/dist > _theta){ // i.e., s/d > Θ
              // open the quad and recurse
              queue.push(node.ne)
              queue.push(node.nw)
              queue.push(node.se)
              queue.push(node.sw)
            }else{
              // treat the quad as a single body
              var d = particle.p.subtract(node.p.divide(node.mass));
              var distance = Math.max(1.0, d.magnitude());
              var direction = ((d.magnitude()>0) ? d : Point.random(1)).normalize()
              particle.applyForce(direction.multiply(repulsion*(node.mass))
                                           .divide(distance * distance) );
            }
          }
        }
      },
      
      _whichQuad:function(particle, node){
        // sort the particle into one of the quadrants of this node
        if (particle.p.exploded()) return null
        var particle_p = particle.p.subtract(node.origin)
        var halfsize = node.size.divide(2)
        if (particle_p.y < halfsize.y){
          if (particle_p.x < halfsize.x) return 'nw'
          else return 'ne'
        }else{
          if (particle_p.x < halfsize.x) return 'sw'
          else return 'se'
        }
      },
      
      _newBranch:function(){
        // to prevent a gc horrorshow, recycle the tree nodes between iterations
        if (_branches[_branchCtr]){
          var branch = _branches[_branchCtr]
          branch.ne = branch.nw = branch.se = branch.sw = undefined
          branch.mass = 0
          delete branch.p
        }else{
          branch = {origin:null, size:null, 
                    nw:undefined, ne:undefined, sw:undefined, se:undefined, mass:0}
          _branches[_branchCtr] = branch
        }

        _branchCtr++
        return branch
      }
    }
    
    return that
  }


//
// etc.js
//
// misc utilities
//

  var trace = function(msg){
    if (typeof(window)=='undefined' || !window.console) return
    var len = arguments.length
    var args = []
    for (var i=0; i<len; i++) args.push("arguments["+i+"]")
    eval("console.log("+args.join(",")+")")
  }  

  var dirname = function(path){
    var pth = path.replace(/^\/?(.*?)\/?$/,"$1").split('/')
    pth.pop()
    return "/"+pth.join("/")
  }
  var basename = function(path){
    // var pth = path.replace(/^\//,'').split('/')
    var pth = path.replace(/^\/?(.*?)\/?$/,"$1").split('/')
    
    var base = pth.pop()
    if (base=="") return null
    else return base
  }

  var _ordinalize_re = /(\d)(?=(\d\d\d)+(?!\d))/g
  var ordinalize = function(num){
    var norm = ""+num
    if (num < 11000){
      norm = (""+num).replace(_ordinalize_re, "$1,")
    } else if (num < 1000000){
      norm = Math.floor(num/1000)+"k"
    } else if (num < 1000000000){
      norm = (""+Math.floor(num/1000)).replace(_ordinalize_re, "$1,")+"m"
    }
    return norm
  }

  /* Nano Templates (Tomasz Mazur, Jacek Becela) */
  var nano = function(template, data){
    return template.replace(/\{([\w\-\.]*)}/g, function(str, key){
      var keys = key.split("."), value = data[keys.shift()]
      $.each(keys, function(){ 
        if (value.hasOwnProperty(this)) value = value[this] 
        else value = str
      })
      return value
    })
  }
  
  var objcopy = function(old){
    if (old===undefined) return undefined
    if (old===null) return null
    
    if (old.parentNode) return old
    switch (typeof old){
      case "string":
      return old.substring(0)
      break
      
      case "number":
      return old + 0
      break
      
      case "boolean":
      return old === true
      break
    }

    var newObj = ($.isArray(old)) ? [] : {}
    $.each(old, function(ik, v){
      newObj[ik] = objcopy(v)
    })
    return newObj
  }
  
  var objmerge = function(dst, src){
    dst = dst || {}
    src = src || {}
    var merge = objcopy(dst)
    for (var k in src) merge[k] = src[k]
    return merge
  }
  
  var objcmp = function(a, b, strict_ordering){
    if (!a || !b) return a===b // handle null+undef
    if (typeof a != typeof b) return false // handle type mismatch
    if (typeof a != 'object'){
      // an atomic type
      return a===b
    }else{
      // a collection type
      
      // first compare buckets
      if ($.isArray(a)){
        if (!($.isArray(b))) return false
        if (a.length != b.length) return false
      }else{
        var a_keys = []; for (var k in a) if (a.hasOwnProperty(k)) a_keys.push(k)
        var b_keys = []; for (var k in b) if (b.hasOwnProperty(k)) b_keys.push(k)
        if (!strict_ordering){
          a_keys.sort()
          b_keys.sort()
        }
        if (a_keys.join(',') !== b_keys.join(',')) return false
      }
      
      // then compare contents
      var same = true
      $.each(a, function(ik){
        var diff = objcmp(a[ik], b[ik])
        same = same && diff
        if (!same) return false
      })
      return same
    }
  }

  var objkeys = function(obj){
    var keys = []
    $.each(obj, function(k,v){ if (obj.hasOwnProperty(k)) keys.push(k) })
    return keys
  }
  
  var objcontains = function(obj){
    if (!obj || typeof obj!='object') return false
    for (var i=1, j=arguments.length; i<j; i++){
      if (obj.hasOwnProperty(arguments[i])) return true
    }
    return false
  }

  var uniq = function(arr){
    // keep in mind that this is only sensible with a list of strings
    // anything else, objkey type coercion will turn it into one anyway
    var len = arr.length
    var set = {}
    for (var i=0; i<len; i++){
      set[arr[i]] = true
    }

    return objkeys(set) 
  }

  var arbor_path = function(){
    var candidates = $("script").map(function(elt){
      var src = $(this).attr('src')
      if (!src) return
      if (src.match(/arbor[^\/\.]*.js|dev.js/)){
        return src.match(/.*\//) || "/"
      }
    })

    if (candidates.length>0) return candidates[0] 
    else return null
  }
  



//
// kernel.js
//
// run-loop manager for physics and tween updates
//
    
  var Kernel = function(pSystem){
    // in chrome, web workers aren't available to pages with file:// urls
    var chrome_local_file = window.location.protocol == "file:" &&
                            navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
    var USE_WORKER = (window.Worker !== undefined && !chrome_local_file)    

    var _physics = null
    var _tween = null
    var _fpsWindow = [] // for keeping track of the actual frame rate
    _fpsWindow.last = new Date()
    var _screenInterval = null
    var _attached = null

    var _tickInterval = null
    var _lastTick = null
    var _paused = false
    
    var that = {
      system:pSystem,
      tween:null,
      nodes:{},

      init:function(){ 
        if (typeof(Tween)!='undefined') _tween = Tween()
        else if (typeof(arbor.Tween)!='undefined') _tween = arbor.Tween()
        else _tween = {busy:function(){return false},
                       tick:function(){return true},
                       to:function(){ trace('Please include arbor-tween.js to enable tweens'); _tween.to=function(){}; return} }
        that.tween = _tween
        var params = pSystem.parameters()
                
        if(USE_WORKER){
          trace('using web workers')
          _screenInterval = setInterval(that.screenUpdate, params.timeout)

          _physics = new Worker(arbor_path()+'physics/worker.js')
          _physics.onmessage = that.workerMsg
          _physics.onerror = function(e){ trace('physics:',e) }
          _physics.postMessage({type:"physics", 
                                physics:objmerge(params, 
                                                {timeout:Math.ceil(params.timeout)}) })
        }else{
          trace("couldn't use web workers, be careful...")
          _physics = Physics(params.dt, params.stiffness, params.repulsion, params.friction, that.system._updateGeometry)
          that.start()
        }

        return that
      },

      //
      // updates from the ParticleSystem
      graphChanged:function(changes){
        // a node or edge was added or deleted
        if (USE_WORKER) _physics.postMessage({type:"changes","changes":changes})
        else _physics._update(changes)
        that.start() // <- is this just to kick things off in the non-worker mode? (yes)
      },

      particleModified:function(id, mods){
        // a particle's position or mass is changed
        // trace('mod',objkeys(mods))
        if (USE_WORKER) _physics.postMessage({type:"modify", id:id, mods:mods})
        else _physics.modifyNode(id, mods)
        that.start() // <- is this just to kick things off in the non-worker mode? (yes)
      },

      physicsModified:function(param){

        // intercept changes to the framerate in case we're using a worker and
        // managing our own draw timer
        if (!isNaN(param.timeout)){
          if (USE_WORKER){
            clearInterval(_screenInterval)
            _screenInterval = setInterval(that.screenUpdate, param.timeout)
          }else{
            // clear the old interval then let the call to .start set the new one
            clearInterval(_tickInterval)
            _tickInterval=null
          }
        }

        // a change to the physics parameters 
        if (USE_WORKER) _physics.postMessage({type:'sys',param:param})
        else _physics.modifyPhysics(param)
        that.start() // <- is this just to kick things off in the non-worker mode? (yes)
      },
      
      workerMsg:function(e){
        var type = e.data.type
        if (type=='geometry'){
          that.workerUpdate(e.data)
        }else{
          trace('physics:',e.data)
        }
      },
      _lastPositions:null,
      workerUpdate:function(data){
        that._lastPositions = data
        that._lastBounds = data.bounds
      },
      

      // 
      // the main render loop when running in web worker mode
      _lastFrametime:new Date().valueOf(),
      _lastBounds:null,
      _currentRenderer:null,
      screenUpdate:function(){        
        var now = new Date().valueOf()
        
        var shouldRedraw = false
        if (that._lastPositions!==null){
          that.system._updateGeometry(that._lastPositions)
          that._lastPositions = null
          shouldRedraw = true
        }
        
        if (_tween && _tween.busy()) shouldRedraw = true

        if (that.system._updateBounds(that._lastBounds)) shouldRedraw=true
        

        if (shouldRedraw){
          var render = that.system.renderer
          if (render!==undefined){
            if (render !== _attached){
               render.init(that.system)
               _attached = render
            }          
            
            if (_tween) _tween.tick()
            render.redraw()

            var prevFrame = _fpsWindow.last
            _fpsWindow.last = new Date()
            _fpsWindow.push(_fpsWindow.last-prevFrame)
            if (_fpsWindow.length>50) _fpsWindow.shift()
          }
        }
      },

      // 
      // the main render loop when running in non-worker mode
      physicsUpdate:function(){
        if (_tween) _tween.tick()
        _physics.tick()

        var stillActive = that.system._updateBounds()
        if (_tween && _tween.busy()) stillActive = true

        var render = that.system.renderer
        var now = new Date()        
        var render = that.system.renderer
        if (render!==undefined){
          if (render !== _attached){
            render.init(that.system)
            _attached = render
          }          
          render.redraw({timestamp:now})
        }

        var prevFrame = _fpsWindow.last
        _fpsWindow.last = now
        _fpsWindow.push(_fpsWindow.last-prevFrame)
        if (_fpsWindow.length>50) _fpsWindow.shift()

        // but stop the simulation when energy of the system goes below a threshold
        var sysEnergy = _physics.systemEnergy()
        if ((sysEnergy.mean + sysEnergy.max)/2 < 0.05){
          if (_lastTick===null) _lastTick=new Date().valueOf()
          if (new Date().valueOf()-_lastTick>1000){
            // trace('stopping')
            clearInterval(_tickInterval)
            _tickInterval = null
          }else{
            // trace('pausing')
          }
        }else{
          // trace('continuing')
          _lastTick = null
        }
      },


      fps:function(newTargetFPS){
        if (newTargetFPS!==undefined){
          var timeout = 1000/Math.max(1,targetFps)
          that.physicsModified({timeout:timeout})
        }
        
        var totInterv = 0
        for (var i=0, j=_fpsWindow.length; i<j; i++) totInterv+=_fpsWindow[i]
        var meanIntev = totInterv/Math.max(1,_fpsWindow.length)
        if (!isNaN(meanIntev)) return Math.round(1000/meanIntev)
        else return 0
      },

      // 
      // start/stop simulation
      // 
      start:function(unpause){
      	if (_tickInterval !== null) return; // already running
        if (_paused && !unpause) return; // we've been .stopped before, wait for unpause
        _paused = false
        
        if (USE_WORKER){
           _physics.postMessage({type:"start"})
        }else{
          _lastTick = null
          _tickInterval = setInterval(that.physicsUpdate, 
                                      that.system.parameters().timeout)
        }
      },
      stop:function(){
        _paused = true
        if (USE_WORKER){
           _physics.postMessage({type:"stop"})
        }else{
          if (_tickInterval!==null){
            clearInterval(_tickInterval)
            _tickInterval = null
          }
        }
      
      }
    }
    
    return that.init()    
  }
  

var Colors = (function(){
  var iscolor_re = /#[0-9a-f]{6}/i
  var hexrgb_re = /#(..)(..)(..)/

  var d2h = function(d){
    // decimal to hex
    var s=d.toString(16); 
    return (s.length==2) ? s : '0'+s
  }
  
  var h2d = function(h){
    // hex to decimal
    return parseInt(h,16);
  }

  var _isRGB = function(color){
    if (!color || typeof color!='object') return false
    var components = objkeys(color).sort().join("")
    if (components == 'abgr') return true
  }

  // var _isHSB = function(color){
  //   if (!color || typeof cssOrHex!='object') return false
  //   var components = objkeys(color).sort().join("")
  //   if (components == 'hsb') return true
  // }


  var that = {
    CSS:{aliceblue:"#f0f8ff", antiquewhite:"#faebd7", aqua:"#00ffff", aquamarine:"#7fffd4", azure:"#f0ffff", beige:"#f5f5dc", bisque:"#ffe4c4", black:"#000000", blanchedalmond:"#ffebcd", blue:"#0000ff", blueviolet:"#8a2be2", brown:"#a52a2a", burlywood:"#deb887", cadetblue:"#5f9ea0", chartreuse:"#7fff00", chocolate:"#d2691e", coral:"#ff7f50", cornflowerblue:"#6495ed", cornsilk:"#fff8dc", crimson:"#dc143c", cyan:"#00ffff", darkblue:"#00008b", darkcyan:"#008b8b", darkgoldenrod:"#b8860b", darkgray:"#a9a9a9", darkgrey:"#a9a9a9", darkgreen:"#006400", darkkhaki:"#bdb76b", darkmagenta:"#8b008b", darkolivegreen:"#556b2f", darkorange:"#ff8c00", darkorchid:"#9932cc", darkred:"#8b0000", darksalmon:"#e9967a", darkseagreen:"#8fbc8f", darkslateblue:"#483d8b", darkslategray:"#2f4f4f", darkslategrey:"#2f4f4f", darkturquoise:"#00ced1", darkviolet:"#9400d3", deeppink:"#ff1493", deepskyblue:"#00bfff", dimgray:"#696969", dimgrey:"#696969", dodgerblue:"#1e90ff", firebrick:"#b22222", floralwhite:"#fffaf0", forestgreen:"#228b22", fuchsia:"#ff00ff", gainsboro:"#dcdcdc", ghostwhite:"#f8f8ff", gold:"#ffd700", goldenrod:"#daa520", gray:"#808080", grey:"#808080", green:"#008000", greenyellow:"#adff2f", honeydew:"#f0fff0", hotpink:"#ff69b4", indianred:"#cd5c5c", indigo:"#4b0082", ivory:"#fffff0", khaki:"#f0e68c", lavender:"#e6e6fa", lavenderblush:"#fff0f5", lawngreen:"#7cfc00", lemonchiffon:"#fffacd", lightblue:"#add8e6", lightcoral:"#f08080", lightcyan:"#e0ffff", lightgoldenrodyellow:"#fafad2", lightgray:"#d3d3d3", lightgrey:"#d3d3d3", lightgreen:"#90ee90", lightpink:"#ffb6c1", lightsalmon:"#ffa07a", lightseagreen:"#20b2aa", lightskyblue:"#87cefa", lightslategray:"#778899", lightslategrey:"#778899", lightsteelblue:"#b0c4de", lightyellow:"#ffffe0", lime:"#00ff00", limegreen:"#32cd32", linen:"#faf0e6", magenta:"#ff00ff", maroon:"#800000", mediumaquamarine:"#66cdaa", mediumblue:"#0000cd", mediumorchid:"#ba55d3", mediumpurple:"#9370d8", mediumseagreen:"#3cb371", mediumslateblue:"#7b68ee", mediumspringgreen:"#00fa9a", mediumturquoise:"#48d1cc", mediumvioletred:"#c71585", midnightblue:"#191970", mintcream:"#f5fffa", mistyrose:"#ffe4e1", moccasin:"#ffe4b5", navajowhite:"#ffdead", navy:"#000080", oldlace:"#fdf5e6", olive:"#808000", olivedrab:"#6b8e23", orange:"#ffa500", orangered:"#ff4500", orchid:"#da70d6", palegoldenrod:"#eee8aa", palegreen:"#98fb98", paleturquoise:"#afeeee", palevioletred:"#d87093", papayawhip:"#ffefd5", peachpuff:"#ffdab9", peru:"#cd853f", pink:"#ffc0cb", plum:"#dda0dd", powderblue:"#b0e0e6", purple:"#800080", red:"#ff0000", rosybrown:"#bc8f8f", royalblue:"#4169e1", saddlebrown:"#8b4513", salmon:"#fa8072", sandybrown:"#f4a460", seagreen:"#2e8b57", seashell:"#fff5ee", sienna:"#a0522d", silver:"#c0c0c0", skyblue:"#87ceeb", slateblue:"#6a5acd", slategray:"#708090", slategrey:"#708090", snow:"#fffafa", springgreen:"#00ff7f", steelblue:"#4682b4", tan:"#d2b48c", teal:"#008080", thistle:"#d8bfd8", tomato:"#ff6347", turquoise:"#40e0d0", violet:"#ee82ee", wheat:"#f5deb3", white:"#ffffff", whitesmoke:"#f5f5f5", yellow:"#ffff00", yellowgreen:"#9acd32"},

    // possible invocations:
    //    decode(1,2,3,.4)      -> {r:1,   g:2,   b:3,   a:0.4}
    //    decode(128, .7)       -> {r:128, g:128, b:128, a:0.7}    
    //    decode("#ff0000")     -> {r:255, g:0,   b:0,   a:1}
    //    decode("#ff0000",.5)  -> {r:255, g:0,   b:0,   a:0.5}
    //    decode("white")       -> {r:255, g:255, b:255, a:1}
    //    decode({r:0,g:0,b:0}) -> {r:0,   g:0,   b:0,   a:1}
    decode:function(clr){
      var argLen = arguments.length
      for (var i=argLen-1; i>=0; i--) if (arguments[i]===undefined) argLen--
      var args = arguments
      if (!clr) return null
      if (argLen==1 && _isRGB(clr)) return clr

      var rgb = null

      if (typeof clr=='string'){
        var alpha = 1
        if (argLen==2) alpha = args[1]
        
        var nameMatch = that.CSS[clr.toLowerCase()]
        if (nameMatch!==undefined){
           clr = nameMatch
        }
        var hexMatch = clr.match(iscolor_re)
        if (hexMatch){
          vals = clr.match(hexrgb_re)
          // trace(vals)
          if (!vals || !vals.length || vals.length!=4) return null    
          rgb = {r:h2d(vals[1]), g:h2d(vals[2]), b:h2d(vals[3]), a:alpha}
        }
      }else if (typeof clr=='number'){
        if (argLen>=3){
          rgb = {r:args[0], g:args[1], b:args[2], a:1}
          if (argLen>=4) rgb.a *= args[3]
        }else if(argLen>=1){
          rgb = {r:args[0], g:args[0], b:args[0], a:1}
          if (argLen==2) rgb.a *= args[1]
        }
      }


      // if (!rgb) trace("<null color>")
      // else trace(nano("<r:{r} g:{g} b:{b} a:{a}>",rgb))
      // 
      // if (arguments.length==1){        
      //   if (_isRGB(clr)) return clr
      //   if (!clr || typeof clr!='string') return null
      // 
      //   var nameMatch = that.CSS[clr.toLowerCase()]
      //   if (nameMatch!==undefined){
      //      clr = nameMatch
      //   }
      //   var hexMatch = clr.match(iscolor_re)
      //   if (hexMatch){
      //     vals = clr.match(hexrgb_re)
      //     if (!vals || !vals.length || vals.length!=4) return null    
      //     var rgb = {r:h2d(vals[1]), g:h2d(vals[2]), b:h2d(vals[3])}
      //     return rgb
      //   }
      // }
      
      return rgb
    },
    validate:function(str){
      if (!str || typeof str!='string') return false
      
      if (that.CSS[str.toLowerCase()] !== undefined) return true
      if (str.match(iscolor_re)) return true
      return false
    },
    
    // transform
    mix:function(color1, color2, proportion){
      var c1 = that.decode(color1)
      var c2 = that.decode(color2)
      
      // var mixed = ... should this be a triplet or a string?
    },
    blend:function(rgbOrHex, alpha){
      alpha = (alpha!==undefined) ? Math.max(0,Math.min(1,alpha)) : 1
      
      var rgb = that.decode(rgbOrHex)
      if (!rgb) return null
      
      if (alpha==1) return rgbOrHex
      var rgb = rgbOrHex
      if (typeof rgbOrHex=='string') rgb = that.decode(rgbOrHex)
      
      var blended = objcopy(rgb)
      blended.a *= alpha
      
      return nano("rgba({r},{g},{b},{a})", blended)
    },
    
    // output
    encode:function(rgb){
      if (!_isRGB(rgb)){
        rgb = that.decode(rgb)
        if (!_isRGB(rgb)) return null
      }
      if (rgb.a==1){
        return nano("#{r}{g}{b}", {r:d2h(rgb.r), g:d2h(rgb.g), b:d2h(rgb.b)} )        
      }else{
        return nano("rgba({r},{g},{b},{a})", rgb)
      }

      // encoding = encoding || "hex"
      // if (!_isRGB(rgb)) return null
      // switch(encoding){
      // case "hex":
      //   return nano("#{r}{g}{b}", {r:d2h(rgb.r), g:d2h(rgb.g), b:d2h(rgb.b)} )
      //   break
      //   
      // case "rgba":
      //   return nano("rgba({r},{g},{b},{alpha})", rgb)
      //   break
      // }
      // // if (rgb===undefined || !rgb.length || rgb.length!=3) return null
      // // return '#'+$.map(rgb, function(c){return d2h(c)}).join("")
    }
  }
  
  return that
})();

//
//  primitives
//
//  Created by Christian Swinehart on 2010-12-08.
//  Copyright (c) 2011 Samizdat Drafting Co. All rights reserved.
//


var Primitives = function(ctx, _drawStyle, _fontStyle){

    ///MACRO:primitives-start
    var _Oval = function(x,y,w,h,style){
      this.x = x
      this.y = y
      this.w = w
      this.h = h
      this.style = (style!==undefined) ? style : {}
    }
    _Oval.prototype = {
      draw:function(overrideStyle){
        this._draw(overrideStyle)
      },

      _draw:function(x,y,w,h, style){
        if (objcontains(x, 'stroke', 'fill', 'width')) style = x
        if (this.x!==undefined){
          x=this.x, y=this.y, w=this.w, h=this.h;
          style = objmerge(this.style, style)
        }
        style = objmerge(_drawStyle, style)
        if (!style.stroke && !style.fill) return

        var kappa = .5522848;
            ox = (w / 2) * kappa, // control point offset horizontal
            oy = (h / 2) * kappa, // control point offset vertical
            xe = x + w,           // x-end
            ye = y + h,           // y-end
            xm = x + w / 2,       // x-middle
            ym = y + h / 2;       // y-middle

        ctx.save()
          ctx.beginPath();
          ctx.moveTo(x, ym);
          ctx.bezierCurveTo(x, ym - oy, xm - ox, y, xm, y);
          ctx.bezierCurveTo(xm + ox, y, xe, ym - oy, xe, ym);
          ctx.bezierCurveTo(xe, ym + oy, xm + ox, ye, xm, ye);
          ctx.bezierCurveTo(xm - ox, ye, x, ym + oy, x, ym);
          ctx.closePath();

          // trace(style.fill, style.stroke)
          if (style.fill!==null){
            // trace("fill",fillColor, Colors.encode(fillColor))
            if (style.alpha!==undefined) ctx.fillStyle = Colors.blend(style.fill, style.alpha)
            else ctx.fillStyle = Colors.encode(style.fill)
            ctx.fill()
          }

          if (style.stroke!==null){
            ctx.strokeStyle = Colors.encode(style.stroke)
            if (!isNaN(style.width)) ctx.lineWidth = style.width
            ctx.stroke()
          }      
        ctx.restore()
      }

    }

    var _Rect = function(x,y,w,h,r,style){
      if (objcontains(r, 'stroke', 'fill', 'width')){
         style = r
         r = 0
      }
      this.x = x
      this.y = y
      this.w = w
      this.h = h
      this.r = (r!==undefined) ? r : 0
      this.style = (style!==undefined) ? style : {}
    }
    _Rect.prototype = {
      draw:function(overrideStyle){
        this._draw(overrideStyle)
      },

      _draw:function(x,y,w,h,r, style){
        if (objcontains(r, 'stroke', 'fill', 'width', 'alpha')){
          style = r; r=0;
        }else if (objcontains(x, 'stroke', 'fill', 'width', 'alpha')){
          style = x
        }
        if (this.x!==undefined){
          x=this.x, y=this.y, w=this.w, h=this.h;
          style = objmerge(this.style, style)
        }
        style = objmerge(_drawStyle, style)
        if (!style.stroke && !style.fill) return

        var rounded = (r>0)
        ctx.save()
        ctx.beginPath();
        ctx.moveTo(x+r, y);
        ctx.lineTo(x+w-r, y);
        if (rounded) ctx.quadraticCurveTo(x+w, y, x+w, y+r);
        ctx.lineTo(x+w, y+h-r);
        if (rounded) ctx.quadraticCurveTo(x+w, y+h, x+w-r, y+h);
        ctx.lineTo(x+r, y+h);
        if (rounded) ctx.quadraticCurveTo(x, y+h, x, y+h-r);
        ctx.lineTo(x, y+r);
        if (rounded) ctx.quadraticCurveTo(x, y, x+r, y);      


        if (style.fill!==null){
          if (style.alpha!==undefined) ctx.fillStyle = Colors.blend(style.fill, style.alpha)
          else ctx.fillStyle = Colors.encode(style.fill)
          ctx.fill()
        }

        if (style.stroke!==null){
          ctx.strokeStyle = Colors.encode(style.stroke)
          if (!isNaN(style.width)) ctx.lineWidth = style.width
          ctx.stroke()
        }      
        ctx.restore()
      }
    }

    var _Path = function(x1, y1, x2, y2, style){
      // calling patterns:
      // ƒ( x1, y1, x2, y2, <style> )
      // ƒ( {x:1, y:1}, {x:2, y:2}, <style> )
      // ƒ( [ {x:1, y:1}, {x:2, y:2}, ...], <style> ) one continuous line
      // ƒ( [ [{x,y}, {x,y}], [{x,y}, {x,y}], ...], <style> ) separate lines

      if (style!==undefined || typeof y2=='number'){
        // ƒ( x1, y1, x2, y2, <style> )
        this.points = [ {x:x1,y:y1}, {x:x2,y:y2} ]
        this.style = style || {}
      }else if ($.isArray(x1)){
        // ƒ( [ {x:1, y:1}, {x:2, y:2}, ...], <style> )
        this.points = x1
        this.style = y1 || {}
      }else{
        // ƒ( {x:1, y:1}, {x:2, y:2}, <style> )
        this.points = [ x1, y1 ]
        this.style = x2 || {}
      }
    }
    _Path.prototype = {
      draw:function(overrideStyle){
        if (this.points.length<2) return

        var sublines = []
        if (!$.isArray(this.points[0])) sublines.push(this.points)
        else sublines = this.points
        
        ctx.save()
          ctx.beginPath();
          $.each(sublines, function(i, lineseg){
            ctx.moveTo(lineseg[0].x+.5, lineseg[0].y+.5);
            $.each(lineseg, function(i, pt){
              if (i==0) return
              ctx.lineTo(pt.x+.5, pt.y+.5);
            })
          })

          var style = $.extend(objmerge(_drawStyle, this.style), overrideStyle)
          if (style.closed) ctx.closePath()

          if (style.fill!==undefined){
            var fillColor = Colors.decode(style.fill, (style.alpha!==undefined) ? style.alpha : 1)
            if (fillColor) ctx.fillStyle = Colors.encode(fillColor)
              ctx.fill()
          }

          if (style.stroke!==undefined){
            var strokeColor = Colors.decode(style.stroke, (style.alpha!==undefined) ? style.alpha : 1)
            if (strokeColor) ctx.strokeStyle = Colors.encode(strokeColor)
            if (!isNaN(style.width)) ctx.lineWidth = style.width
            ctx.stroke()
          }
  			ctx.restore()
      }
    }
    

    var _Color = function(a,b,c,d){
      var rgba = Colors.decode(a,b,c,d)
      if (rgba){
        this.r = rgba.r
        this.g = rgba.g
        this.b = rgba.b
        this.a = rgba.a
      }
    }

    _Color.prototype = {
      toString:function(){
        return Colors.encode(this)
      },
      blend:function(){
        trace("blend",this.r,this.g,this.b,this.a)
      }
    }

    // var _Font = function(face, size){
    //   this.face = (face!=undefined) ? face : "sans-serif"
    //   this.size = (size!=undefined) ? size : 12
    //   // this.alignment = (opts.alignment!=undefined) ? alignment : "left"
    //   // this.baseline = (opts.baseline!=undefined) ? baseline : "ideographic"
    //   // this.color = (opts.color!=undefined) ? Colors.decode(opts.color) : Colors.decode("black")
    // }
    // _Font.prototype = {
    //   _use:function(face, size){
    //     // var params = $.extend({face:face, size:size}, opts)
    //     // $.each('face size alignment baseline color'.split(" "), function(i, param){
    //     //   if (params[param]!==undefined){
    //     //     if (param=='color') _fontStyle[param] = Colors.decode(params[param])
    //     //     else _fontStyle[param] = params[param]
    //     //   }
    //     // })
    // 
    //     // ctx.textAlign = _fontStyle.alignment
    //     // ctx.textBaseline = _fontStyle.baseline
    //     ctx.font = nano("{size}px {face}", {face:face, size:size})
    //     // trace(ctx.font,face,size)      
    //     // ctx.fillStyle = Colors.encode(_fontStyle)
    //     // _fontStyle = {face:face, size:size, alignment:opts.alignment, baseline:opts.baseline, color:opts.color}
    //   },
    //   use:function(){
    //     ctx.font = nano("{size}px {face}", this)
    //   }
    // }
    // 
    // 

  ///MACRO:primitives-end

  






  return {
    _Oval:_Oval,
    _Rect:_Rect,
    _Color:_Color,
    _Path:_Path
    // _Frame:Frame
  }
}

//
//  graphics.js
//
//  Created by Christian Swinehart on 2010-12-07.
//  Copyright (c) 2011 Samizdat Drafting Co. All rights reserved.
//

var Graphics = function(canvas){
  var dom = $(canvas)
  var ctx = $(dom).get(0).getContext('2d')

  var _bounds = null

  var _colorMode = "rgb" // vs hsb
  var _coordMode = "origin" // vs "center"

  var _drawLibrary = {}
  var _drawStyle = {background:null, 
                    fill:null, 
                    stroke:null,
                    width:0}

  var _fontLibrary = {}
  var _fontStyle = {font:"sans-serif",
                   size:12, 
                   align:"left",
                   color:Colors.decode("black"),
                   alpha:1,
                   baseline:"ideographic"}

  var _lineBuffer = [] // calls to .lines sit here until flushed by .drawlines
  
  ///MACRO:primitives-start
  var primitives = Primitives(ctx, _drawStyle, _fontStyle)
  var _Oval = primitives._Oval
  var _Rect = primitives._Rect
  var _Color = primitives._Color
  var _Path = primitives._Path
  ///MACRO:primitives-end    


  // drawStyle({background:"color" or {r,g,b,a}, 
  //            fill:"color" or {r,g,b,a}, 
  //            stroke:"color" or {r,g,b,a}, 
  //            alpha:<number>, 
  //            weight:<number>})




  
  var that = {
    init:function(){
      if (!ctx) return null
      return that
    },

    // canvas-wide settings
    size:function(width,height){
      if (!isNaN(width) && !isNaN(height)){
        dom.attr({width:width,height:height})
        
        // if (_drawStyle.fill!==null) that.fill(_drawStyle.fill)
        // if (_drawStyle.stroke!==null) that.stroke(_drawStyle.stroke)
        // that.textStyle(_fontStyle)
        
        // trace(_drawStyle,_fontStyle)
      }
      return {width:dom.attr('width'), height:dom.attr('height')}
    },

    clear:function(x,y,w,h){
      if (arguments.length<4){
        x=0; y=0
        w=dom.attr('width')
        h=dom.attr('height')
      }
      
      ctx.clearRect(x,y,w,h)
      if (_drawStyle.background!==null){
        ctx.save()
        ctx.fillStyle = Colors.encode(_drawStyle.background)
        ctx.fillRect(x,y,w,h)
        ctx.restore()
      }
    },

    background:function(a,b,c,d){
      if (a==null){
        _drawStyle.background = null
        return null
      }
      
      var fillColor = Colors.decode(a,b,c,d)
      if (fillColor){
        _drawStyle.background = fillColor
        that.clear()
      }
    },


    // drawing to screen
    noFill:function(){
      _drawStyle.fill = null
    },
    fill:function(a,b,c,d){
      if (arguments.length==0){
        return _drawStyle.fill
      }else if (arguments.length>0){
        var fillColor = Colors.decode(a,b,c,d)
        _drawStyle.fill = fillColor
        ctx.fillStyle = Colors.encode(fillColor)
      }
    },
    
    noStroke:function(){
      _drawStyle.stroke = null
      ctx.strokeStyle = null
    },
    stroke:function(a,b,c,d){
      if (arguments.length==0 && _drawStyle.stroke!==null){
        return _drawStyle.stroke
      }else if (arguments.length>0){
        var strokeColor = Colors.decode(a,b,c,d)
        _drawStyle.stroke = strokeColor
        ctx.strokeStyle = Colors.encode(strokeColor)
      }
    },
    strokeWidth:function(ptsize){
      if (ptsize===undefined) return ctx.lineWidth
      ctx.lineWidth = _drawStyle.width = ptsize
    },
    
    
    
    Color:function(clr){
      return new _Color(clr)
    },


    // Font:function(fontName, pointSize){
    //   return new _Font(fontName, pointSize)
    // },
    // font:function(fontName, pointSize){
    //   if (fontName!==undefined) _fontStyle.font = fontName
    //   if (pointSize!==undefined) _fontStyle.size = pointSize
    //   ctx.font = nano("{size}px {font}", _fontStyle)
    // },


    drawStyle:function(style){
      // without arguments, show the current state
      if (arguments.length==0) return objcopy(_drawStyle)
      
      // if this is a ("stylename", {style}) invocation, don't change the current
      // state but add it to the library
      if (arguments.length==2){
        var styleName = arguments[0]
        var styleDef = arguments[1]
        if (typeof styleName=='string' && typeof styleDef=='object'){
          var newStyle = {}
          if (styleDef.color!==undefined){
            var textColor = Colors.decode(styleDef.color)
            if (textColor) newStyle.color = textColor
          }
          $.each('background fill stroke width'.split(' '), function(i, param){
            if (styleDef[param]!==undefined) newStyle[param] = styleDef[param]
          })
          if (!$.isEmptyObject(newStyle)) _drawLibrary[styleName] = newStyle
        }
        return
      }
      
      // if a ("stylename") invocation, load up the selected style
      if (arguments.length==1 && _drawLibrary[arguments[0]]!==undefined){
        style = _drawLibrary[arguments[0]]
      }
            
      // for each of the properties specified, update the canvas state
      if (style.width!==undefined) _drawStyle.width = style.width
      ctx.lineWidth = _drawStyle.width
      
      $.each('background fill stroke',function(i, color){
        if (style[color]!==undefined){
          if (style[color]===null) _drawStyle[color] = null
          else{
            var useColor = Colors.decode(style[color])
            if (useColor) _drawStyle[color] = useColor
          }
        }
      })
      ctx.fillStyle = _drawStyle.fill
      ctx.strokeStyle = _drawStyle.stroke
    },

    textStyle:function(style){
      // without arguments, show the current state
      if (arguments.length==0) return objcopy(_fontStyle)
      
      // if this is a ("name", {style}) invocation, don't change the current
      // state but add it to the library
      if (arguments.length==2){
        var styleName = arguments[0]
        var styleDef = arguments[1]
        if (typeof styleName=='string' && typeof styleDef=='object'){
          var newStyle = {}
          if (styleDef.color!==undefined){
            var textColor = Colors.decode(styleDef.color)
            if (textColor) newStyle.color = textColor
          }
          $.each('font size align baseline alpha'.split(' '), function(i, param){
            if (styleDef[param]!==undefined) newStyle[param] = styleDef[param]
          })
          if (!$.isEmptyObject(newStyle)) _fontLibrary[styleName] = newStyle
        }
        return
      }
      
      if (arguments.length==1 && _fontLibrary[arguments[0]]!==undefined){
        style = _fontLibrary[arguments[0]]
      }
            
      if (style.font!==undefined) _fontStyle.font = style.font
      if (style.size!==undefined) _fontStyle.size = style.size
      ctx.font = nano("{size}px {font}", _fontStyle)

      if (style.align!==undefined){
         ctx.textAlign = _fontStyle.align = style.align
      }
      if (style.baseline!==undefined){
         ctx.textBaseline = _fontStyle.baseline = style.baseline
      }

      if (style.alpha!==undefined) _fontStyle.alpha = style.alpha
      if (style.color!==undefined){
        var textColor = Colors.decode(style.color)
        if (textColor) _fontStyle.color = textColor
      }
      if (_fontStyle.color){
        var textColor = Colors.blend(_fontStyle.color, _fontStyle.alpha)
        if (textColor) ctx.fillStyle = textColor
      }
      // trace(_fontStyle,opts)
    },

    text:function(textStr, x, y, opts){ // opts: x,y, color, font, align, baseline, width
      if (arguments.length>=3 && !isNaN(x)){
        opts = opts || {}
        opts.x = x
        opts.y = y
      }else if (arguments.length==2 && typeof(x)=='object'){
        opts = x
      }else{
        opts = opts || {}
      }

      var style = objmerge(_fontStyle, opts)
      ctx.save()
        if (style.align!==undefined) ctx.textAlign = style.align
        if (style.baseline!==undefined) ctx.textBaseline = style.baseline
        if (style.font!==undefined && !isNaN(style.size)){
          ctx.font = nano("{size}px {font}", style)
        }

        var alpha = (style.alpha!==undefined) ? style.alpha : _fontStyle.alpha
        var color = (style.color!==undefined) ? style.color : _fontStyle.color
        ctx.fillStyle = Colors.blend(color, alpha)
        
        // if (alpha>0) ctx.fillText(textStr, style.x, style.y);        
        if (alpha>0) ctx.fillText(textStr, Math.round(style.x), style.y);        
      ctx.restore()
    },

    textWidth:function(textStr, style){ // style: x,y, color, font, align, baseline, width
      style = objmerge(_fontStyle, style||{})
      ctx.save()
        ctx.font = nano("{size}px {font}", style)
        var width = ctx.measureText(textStr).width			  
      ctx.restore()
      return width
    },
    
    // hasFont:function(fontName){
    //   var testTxt = 'H h H a H m H b H u H r H g H e H r H f H o H n H s H t H i H v H'
    //   ctx.save()
    //   ctx.font = '10px sans-serif'
    //   var defaultWidth = ctx.measureText(testTxt).width
    // 
    //   ctx.font = '10px "'+fontName+'"'
    //   var putativeWidth = ctx.measureText(testTxt).width
    //   ctx.restore()
    //   
    //   // var defaultWidth = that.textWidth(testTxt, {font:"Times New Roman", size:120})
    //   // var putativeWidth = that.textWidth(testTxt, {font:fontName, size:120})
    //   trace(defaultWidth,putativeWidth,ctx.font)
    //   // return (putativeWidth!=defaultWidth || fontName=="Times New Roman")
    //   return putativeWidth!=defaultWidth
    // },
    
    
    // shape primitives.
    // classes will return an {x,y,w,h, fill(), stroke()} object without drawing
    // functions will draw the shape based on current stroke/fill state
    Rect:function(x,y,w,h,r,style){
      return new _Rect(x,y,w,h,r,style)
    },
    rect:function(x, y, w, h, r, style){
      _Rect.prototype._draw(x,y,w,h,r,style)
    },
    
    Oval:function(x, y, w, h, style) {
      return new _Oval(x,y,w,h, style)
    },
    oval:function(x, y, w, h, style) {
      style = style || {}
      _Oval.prototype._draw(x,y,w,h, style)
    },
    
    // draw a line immediately
    line:function(x1, y1, x2, y2, style){
      var p = new _Path(x1,y1,x2,y2)
      p.draw(style)
    },
    
    // queue up a line segment to be drawn in a batch by .drawLines
    lines:function(x1, y1, x2, y2){
      if (typeof y2=='number'){
        // ƒ( x1, y1, x2, y2)
        _lineBuffer.push( [ {x:x1,y:y1}, {x:x2,y:y2} ] )
      }else{
        // ƒ( {x:1, y:1}, {x:2, y:2} )
        _lineBuffer.push( [ x1,y1 ] )
      }
    },
    
    // flush the buffered .lines to screen
    drawLines:function(style){
      var p = new _Path(_lineBuffer)
      p.draw(style)
      _lineBuffer = []
    }
    

  }
  
  return that.init()    
}


// // helpers for figuring out where to draw arrows
// var intersect_line_line = function(p1, p2, p3, p4)
// {
//  var denom = ((p4.y - p3.y)*(p2.x - p1.x) - (p4.x - p3.x)*(p2.y - p1.y));
// 
//  // lines are parallel
//  if (denom === 0) {
//    return false;
//  }
// 
//  var ua = ((p4.x - p3.x)*(p1.y - p3.y) - (p4.y - p3.y)*(p1.x - p3.x)) / denom;
//  var ub = ((p2.x - p1.x)*(p1.y - p3.y) - (p2.y - p1.y)*(p1.x - p3.x)) / denom;
// 
//  if (ua < 0 || ua > 1 || ub < 0 || ub > 1) {
//    return false;
//  }
// 
//  return arbor.Point(p1.x + ua * (p2.x - p1.x), p1.y + ua * (p2.y - p1.y));
// }
// 
// var intersect_line_box = function(p1, p2, p3, w, h)
// {
//  var tl = {x: p3.x, y: p3.y};
//  var tr = {x: p3.x + w, y: p3.y};
//  var bl = {x: p3.x, y: p3.y + h};
//  var br = {x: p3.x + w, y: p3.y + h};
// 
//  var result;
//  if (result = intersect_line_line(p1, p2, tl, tr)) { return result; } // top
//  if (result = intersect_line_line(p1, p2, tr, br)) { return result; } // right
//  if (result = intersect_line_line(p1, p2, br, bl)) { return result; } // bottom
//  if (result = intersect_line_line(p1, p2, bl, tl)) { return result; } // left
// 
//  return false;
// }

//
// easing.js
// the world-famous penner easing equations
//

/*
 *
 * TERMS OF USE - EASING EQUATIONS
 * 
 * Open source under the BSD License. 
 * 
 * Copyright © 2001 Robert Penner
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice, this list of 
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list 
 * of conditions and the following disclaimer in the documentation and/or other materials 
 * provided with the distribution.
 * 
 * Neither the name of the author nor the names of contributors may be used to endorse 
 * or promote products derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED 
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE. 
 *
 */
 
 var Easing = (function(){
  var that = {
    // t: current time, b: beginning value, c: change in value, d: duration   
    linear: function(t, b, c, d){
      return c*(t/d) + b
    },
    quadin: function (t, b, c, d) {
  		return c*(t/=d)*t + b;
  	},
    quadout: function (t, b, c, d) {
  		return -c *(t/=d)*(t-2) + b;
  	},
    quadinout: function (t, b, c, d) {
  		if ((t/=d/2) < 1) return c/2*t*t + b;
  		return -c/2 * ((--t)*(t-2) - 1) + b;
  	},
    cubicin: function (t, b, c, d) {
  		return c*(t/=d)*t*t + b;
  	},
    cubicout: function (t, b, c, d) {
  		return c*((t=t/d-1)*t*t + 1) + b;
  	},
    cubicinout: function (t, b, c, d) {
  		if ((t/=d/2) < 1) return c/2*t*t*t + b;
  		return c/2*((t-=2)*t*t + 2) + b;
  	},
    quartin: function (t, b, c, d) {
  		return c*(t/=d)*t*t*t + b;
  	},
    quartout: function (t, b, c, d) {
  		return -c * ((t=t/d-1)*t*t*t - 1) + b;
  	},
    quartinout: function (t, b, c, d) {
  		if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
  		return -c/2 * ((t-=2)*t*t*t - 2) + b;
  	},
    quintin: function (t, b, c, d) {
  		return c*(t/=d)*t*t*t*t + b;
  	},
    quintout: function (t, b, c, d) {
  		return c*((t=t/d-1)*t*t*t*t + 1) + b;
  	},
    quintinout: function (t, b, c, d) {
  		if ((t/=d/2) < 1) return c/2*t*t*t*t*t + b;
  		return c/2*((t-=2)*t*t*t*t + 2) + b;
  	},
    sinein: function (t, b, c, d) {
  		return -c * Math.cos(t/d * (Math.PI/2)) + c + b;
  	},
    sineout: function (t, b, c, d) {
  		return c * Math.sin(t/d * (Math.PI/2)) + b;
  	},
    sineinout: function (t, b, c, d) {
  		return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
  	},
    expoin: function (t, b, c, d) {
  		return (t==0) ? b : c * Math.pow(2, 10 * (t/d - 1)) + b;
  	},
    expoout: function (t, b, c, d) {
  		return (t==d) ? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;
  	},
    expoinout: function (t, b, c, d) {
  		if (t==0) return b;
  		if (t==d) return b+c;
  		if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
  		return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
  	},
    circin: function (t, b, c, d) {
  		return -c * (Math.sqrt(1 - (t/=d)*t) - 1) + b;
  	},
    circout: function (t, b, c, d) {
  		return c * Math.sqrt(1 - (t=t/d-1)*t) + b;
  	},
    circinout: function (t, b, c, d) {
  		if ((t/=d/2) < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;
  		return c/2 * (Math.sqrt(1 - (t-=2)*t) + 1) + b;
  	},
    elasticin: function (t, b, c, d) {
  		var s=1.70158;var p=0;var a=c;
  		if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
  		if (a < Math.abs(c)) { a=c; var s=p/4; }
  		else var s = p/(2*Math.PI) * Math.asin (c/a);
  		return -(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
  	},
    elasticout: function (t, b, c, d) {
  		var s=1.70158;var p=0;var a=c;
  		if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
  		if (a < Math.abs(c)) { a=c; var s=p/4; }
  		else var s = p/(2*Math.PI) * Math.asin (c/a);
  		return a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b;
  	},
    elasticinout: function (t, b, c, d) {
  		var s=1.70158;var p=0;var a=c;
  		if (t==0) return b;  if ((t/=d/2)==2) return b+c;  if (!p) p=d*(.3*1.5);
  		if (a < Math.abs(c)) { a=c; var s=p/4; }
  		else var s = p/(2*Math.PI) * Math.asin (c/a);
  		if (t < 1) return -.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
  		return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*.5 + c + b;
  	},
    backin: function (t, b, c, d, s) {
  		if (s == undefined) s = 1.70158;
  		return c*(t/=d)*t*((s+1)*t - s) + b;
  	},
    backout: function (t, b, c, d, s) {
  		if (s == undefined) s = 1.70158;
  		return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
  	},
    backinout: function (t, b, c, d, s) {
  		if (s == undefined) s = 1.70158; 
  		if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;
  		return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;
  	},
    bouncein: function (t, b, c, d) {
  		return c - that.bounceOut (d-t, 0, c, d) + b;
  	},
    bounceout: function (t, b, c, d) {
  		if ((t/=d) < (1/2.75)) {
  			return c*(7.5625*t*t) + b;
  		} else if (t < (2/2.75)) {
  			return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
  		} else if (t < (2.5/2.75)) {
  			return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
  		} else {
  			return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
  		}
  	},
    bounceinout: function (t, b, c, d) {
  		if (t < d/2) return that.bounceIn (t*2, 0, c, d) * .5 + b;
  		return that.bounceOut(t*2-d, 0, c, d) * .5 + c*.5 + b;
  	}
	}
	return that
})();

//
// tween.js
//
// interpolator of .data field members for nodes and edges
//

  var Tween = function(){
    var _tweens = {}
    var _done = true
    
    var that = {
      init:function(){
        return that
      },
      
      busy:function(){
        var busy = false
        for (var k in _tweens){ busy=true; break}
        return busy
      },
      
      to:function(node, dur, to){
        var now = new Date().valueOf()
        var seenFields = {}

        var tween = {from:{}, to:{}, colors:{}, node:node, t0:now, t1:now+dur*1000, dur:dur*1000}
        var easing_fn = "linear"
        for (var k in to){
          if (k=='easing'){
            // need to do better here. case insensitive and default to linear
            // also be okay with functions getting passed in
            var ease = to[k].toLowerCase()
            if (ease in Easing) easing_fn = ease
            continue
          }else if (k=='delay'){
            var delay = (to[k]||0) * 1000
            tween.t0 += delay
            tween.t1 += delay
            continue
          }
          
          if (Colors.validate(to[k])){
            // it's a hex color string value
            tween.colors[k] = [Colors.decode(node.data[k]), Colors.decode(to[k]), to[k]]
            seenFields[k] = true
          }else{
            tween.from[k] = (node.data[k]!=undefined) ? node.data[k] : to[k]
            tween.to[k] = to[k]
            seenFields[k] = true
          }
        }
        tween.ease = Easing[easing_fn]

        if (_tweens[node._id]===undefined) _tweens[node._id] = []
        _tweens[node._id].push(tween)
        
        // look through queued prunes for any redundancies
        if (_tweens.length>1){
          for (var i=_tweens.length-2; i>=0; i++){
            var tw = _tweens[i]

            for (var k in tw.to){
              if (k in seenFields) delete tw.to[k]
              else seenFields[k] = true
            }

            for (var k in tw.colors){
              if (k in seenFields) delete tw.colors[k]
              else seenFields[k] = true
            }

            if ($.isEmptyObject(tw.colors) && $.isEmptyObject(tw.to)){
              _tweens.splice(i,1)
            }

          }
        }
        
        _done = false
      },

      interpolate:function(pct, src, dst, ease){
        ease = (ease||"").toLowerCase()
        var easing_fn = Easing.linear
        if (ease in Easing) easing_fn = Easing[ease]

        var proportion = easing_fn( pct, 0,1, 1 )
        if (Colors.validate(src) && Colors.validate(dst)){
          return lerpRGB(proportion, src,dst)
        }else if (!isNaN(src)){
          return lerpNumber(proportion, src,dst)
        }else if (typeof src=='string'){
          return (proportion<.5) ? src : dst
        }
        
      },

      tick:function(){
        var empty = true
        for (var k in _tweens){ empty=false; break}
        if (empty) return
        
        var now = new Date().valueOf()
        
        $.each(_tweens, function(id, tweens){
          var unprunedTweens = false
          
          $.each(tweens, function(i, tween){
            var proportion = tween.ease( (now-tween.t0), 0,1, tween.dur )
            proportion = Math.min(1.0, proportion)
            var from = tween.from
            var to = tween.to
            var colors = tween.colors
            var nodeData = tween.node.data

            var lastTick = (proportion==1.0)

            for (var k in to){
              switch (typeof to[k]){
                case "number":
                  nodeData[k] = lerpNumber(proportion, from[k], to[k])
                  if (k=='alpha') nodeData[k] = Math.max(0,Math.min(1, nodeData[k]))
                  break
                case "string":
                  if (lastTick){
                    nodeData[k] = to[k]
                  }
                  break
              }
            }
            
            for (var k in colors){
              if (lastTick){
                nodeData[k] = colors[k][2]
              }else{
                var rgb = lerpRGB(proportion, colors[k][0], colors[k][1])
                nodeData[k] = Colors.encode(rgb)
              }
            }

            if (lastTick){
               tween.completed = true
               unprunedTweens = true
            }
          })
          
          if (unprunedTweens){
            _tweens[id] = $.map(tweens, function(t){ if (!t.completed) return t})
            if (_tweens[id].length==0) delete _tweens[id]
          }
        })
        
        _done = $.isEmptyObject(_tweens)
        return _done
      }
    }
    return that.init()
  }
  
  var lerpNumber = function(proportion,from,to){
    return from + proportion*(to-from)
  }
  
  var lerpRGB = function(proportion,from,to){
    proportion = Math.max(Math.min(proportion,1),0)
    var mixture = {}
    
    $.each('rgba'.split(""), function(i, c){
      mixture[c] = Math.round( from[c] + proportion*(to[c]-from[c]) )
    })
    return mixture
  }

  
// })()

//
// atoms.js
//
// particle system- or physics-related datatypes
//

var Node = function(data){
	this._id = _nextNodeId++; // simple ints to allow the Kernel & ParticleSystem to chat
	this.data = data || {};  // the user-serviceable parts
	this._mass = (data.mass!==undefined) ? data.mass : 1
	this._fixed = (data.fixed===true) ? true : false
	this._p = new Point((typeof(data.x)=='number') ? data.x : null, 
                     (typeof(data.y)=='number') ? data.y : null)
  delete this.data.x
  delete this.data.y
  delete this.data.mass
  delete this.data.fixed
};
var _nextNodeId = 1

var Edge = function(source, target, data){
	this._id = _nextEdgeId--;
	this.source = source;
	this.target = target;
	this.length = (data.length!==undefined) ? data.length : 1
	this.data = (data!==undefined) ? data : {};
	delete this.data.length
};
var _nextEdgeId = -1

var Particle = function(position, mass){
  this.p = position;
  this.m = mass;
	this.v = new Point(0, 0); // velocity
	this.f = new Point(0, 0); // force
};
Particle.prototype.applyForce = function(force){
	this.f = this.f.add(force.divide(this.m));
};

var Spring = function(point1, point2, length, k)
{
	this.point1 = point1; // a particle
	this.point2 = point2; // another particle
	this.length = length; // spring length at rest
	this.k = k;           // stiffness
};
Spring.prototype.distanceToParticle = function(point)
{
  // see http://stackoverflow.com/questions/849211/shortest-distance-between-a-point-and-a-line-segment/865080#865080
  var n = that.point2.p.subtract(that.point1.p).normalize().normal();
  var ac = point.p.subtract(that.point1.p);
  return Math.abs(ac.x * n.x + ac.y * n.y);
};

var Point = function(x, y){
  if (x && x.hasOwnProperty('y')){
    y = x.y; x=x.x;
  }
  this.x = x;
  this.y = y;  
}

Point.random = function(radius){
  radius = (radius!==undefined) ? radius : 5
	return new Point(2*radius * (Math.random() - 0.5), 2*radius* (Math.random() - 0.5));
}

Point.prototype = {
  exploded:function(){
    return ( isNaN(this.x) || isNaN(this.y) )
  },
  add:function(v2){
  	return new Point(this.x + v2.x, this.y + v2.y);
  },
  subtract:function(v2){
  	return new Point(this.x - v2.x, this.y - v2.y);
  },
  multiply:function(n){
  	return new Point(this.x * n, this.y * n);
  },
  divide:function(n){
  	return new Point(this.x / n, this.y / n);
  },
  magnitude:function(){
  	return Math.sqrt(this.x*this.x + this.y*this.y);
  },
  normal:function(){
  	return new Point(-this.y, this.x);
  },
  normalize:function(){
  	return this.divide(this.magnitude());
  }
}

//
// physics.js
//
// the particle system itself. either run inline or in a worker (see worker.js)
//

  var Physics = function(dt, stiffness, repulsion, friction, updateFn){
    var bhTree = BarnesHutTree() // for computing particle repulsion
    var active = {particles:{}, springs:{}}
    var free = {particles:{}}
    var particles = []
    var springs = []
    var _epoch=0
    var _energy = {sum:0, max:0, mean:0}
    var _bounds = {topleft:new Point(-1,-1), bottomright:new Point(1,1)}

    var SPEED_LIMIT = 1000 // the max particle velocity per tick
    
    var that = {
      stiffness:(stiffness!==undefined) ? stiffness : 1000,
      repulsion:(repulsion!==undefined)? repulsion : 600,
      friction:(friction!==undefined)? friction : .3,
      gravity:false,
      dt:(dt!==undefined)? dt : 0.02,
      theta:.4, // the criterion value for the barnes-hut s/d calculation
      
      init:function(){
        return that
      },

      modifyPhysics:function(param){
        $.each(['stiffness','repulsion','friction','gravity','dt','precision'], function(i, p){
          if (param[p]!==undefined){
            if (p=='precision'){
              that.theta = 1-param[p]
              return
            }
            that[p] = param[p]
             
            if (p=='stiffness'){
              var stiff=param[p]
              $.each(active.springs, function(id, spring){
                spring.k = stiff
              })             
            }
          }
        })
      },

      addNode:function(c){
        var id = c.id
        var mass = c.m

        var w = _bounds.bottomright.x - _bounds.topleft.x
        var h = _bounds.bottomright.y - _bounds.topleft.y
        var randomish_pt = new Point((c.x != null) ? c.x: _bounds.topleft.x + w*Math.random(),
                                     (c.y != null) ? c.y: _bounds.topleft.y + h*Math.random())

        
        active.particles[id] = new Particle(randomish_pt, mass);
        active.particles[id].connections = 0
        active.particles[id].fixed = (c.f===1)
        free.particles[id] = active.particles[id]
        particles.push(active.particles[id])        
      },

      dropNode:function(c){
        var id = c.id
        var dropping = active.particles[id]
        var idx = $.inArray(dropping, particles)
        if (idx>-1) particles.splice(idx,1)
        delete active.particles[id]
        delete free.particles[id]
      },

      modifyNode:function(id, mods){
        if (id in active.particles){
          var pt = active.particles[id]
          if ('x' in mods) pt.p.x = mods.x
          if ('y' in mods) pt.p.y = mods.y
          if ('m' in mods) pt.m = mods.m
          if ('f' in mods) pt.fixed = (mods.f===1)
          if ('_m' in mods){
            if (pt._m===undefined) pt._m = pt.m
            pt.m = mods._m            
          }
        }
      },

      addSpring:function(c){
        var id = c.id
        var length = c.l
        var from = active.particles[c.fm]
        var to = active.particles[c.to]
        
        if (from!==undefined && to!==undefined){
          active.springs[id] = new Spring(from, to, length, that.stiffness)
          springs.push(active.springs[id])
          
          from.connections++
          to.connections++
          
          delete free.particles[c.fm]
          delete free.particles[c.to]
        }
      },

      dropSpring:function(c){
        var id = c.id
        var dropping = active.springs[id]
        
        dropping.point1.connections--
        dropping.point2.connections--
        
        var idx = $.inArray(dropping, springs)
        if (idx>-1){
           springs.splice(idx,1)
        }
        delete active.springs[id]
      },

      _update:function(changes){
        // batch changes phoned in (automatically) by a ParticleSystem
        _epoch++
        
        $.each(changes, function(i, c){
          if (c.t in that) that[c.t](c)
        })
        return _epoch
      },


      tick:function(){
        that.tendParticles()
        that.eulerIntegrator(that.dt)
        that.tock()
      },

      tock:function(){
        var coords = []
        $.each(active.particles, function(id, pt){
          coords.push(id)
          coords.push(pt.p.x)
          coords.push(pt.p.y)
        })

        if (updateFn) updateFn({geometry:coords, epoch:_epoch, energy:_energy, bounds:_bounds})
      },

      tendParticles:function(){
        $.each(active.particles, function(id, pt){
          // decay down any of the temporary mass increases that were passed along
          // by using an {_m:} instead of an {m:} (which is to say via a Node having
          // its .tempMass attr set)
          if (pt._m!==undefined){
            if (Math.abs(pt.m-pt._m)<1){
              pt.m = pt._m
              delete pt._m
            }else{
              pt.m *= .98
            }
          }

          // zero out the velocity from one tick to the next
          pt.v.x = pt.v.y = 0           
        })

      },
      
      
      // Physics stuff
      eulerIntegrator:function(dt){
        if (that.repulsion>0){
          if (that.theta>0) that.applyBarnesHutRepulsion()
          else that.applyBruteForceRepulsion()
        }
        if (that.stiffness>0) that.applySprings()
        that.applyCenterDrift()
        if (that.gravity) that.applyCenterGravity()
        that.updateVelocity(dt)
        that.updatePosition(dt)
      },

      applyBruteForceRepulsion:function(){
        $.each(active.particles, function(id1, point1){
          $.each(active.particles, function(id2, point2){
            if (point1 !== point2){
              var d = point1.p.subtract(point2.p);
              var distance = Math.max(1.0, d.magnitude());
              var direction = ((d.magnitude()>0) ? d : Point.random(1)).normalize()

              // apply force to each end point
              // (consult the cached `real' mass value if the mass is being poked to allow
              // for repositioning. the poked mass will still be used in .applyforce() so
              // all should be well)
              point1.applyForce(direction.multiply(that.repulsion*(point2._m||point2.m)*.5)
                                         .divide(distance * distance * 0.5) );
              point2.applyForce(direction.multiply(that.repulsion*(point1._m||point1.m)*.5)
                                         .divide(distance * distance * -0.5) );

            }
          })          
        })
      },
      
      applyBarnesHutRepulsion:function(){
        if (!_bounds.topleft || !_bounds.bottomright) return
        var bottomright = new Point(_bounds.bottomright)
        var topleft = new Point(_bounds.topleft)

        // build a barnes-hut tree...
        bhTree.init(topleft, bottomright, that.theta)        
        $.each(active.particles, function(id, particle){
          bhTree.insert(particle)
        })
        
        // ...and use it to approximate the repulsion forces
        $.each(active.particles, function(id, particle){
          bhTree.applyForces(particle, that.repulsion)
        })
      },
      
      applySprings:function(){
        $.each(active.springs, function(id, spring){
          var d = spring.point2.p.subtract(spring.point1.p); // the direction of the spring
          var displacement = spring.length - d.magnitude()//Math.max(.1, d.magnitude());
          var direction = ( (d.magnitude()>0) ? d : Point.random(1) ).normalize()

          // BUG:
          // since things oscillate wildly for hub nodes, should probably normalize spring
          // forces by the number of incoming edges for each node. naive normalization 
          // doesn't work very well though. what's the `right' way to do it?

          // apply force to each end point
          spring.point1.applyForce(direction.multiply(spring.k * displacement * -0.5))
          spring.point2.applyForce(direction.multiply(spring.k * displacement * 0.5))
        });
      },


      applyCenterDrift:function(){
        // find the centroid of all the particles in the system and shift everything
        // so the cloud is centered over the origin
        var numParticles = 0
        var centroid = new Point(0,0)
        $.each(active.particles, function(id, point) {
          centroid.add(point.p)
          numParticles++
        });

        if (numParticles==0) return
        
        var correction = centroid.divide(-numParticles)
        $.each(active.particles, function(id, point) {
          point.applyForce(correction)
        })
        
      },
      applyCenterGravity:function(){
        // attract each node to the origin
        $.each(active.particles, function(id, point) {
          var direction = point.p.multiply(-1.0);
          point.applyForce(direction.multiply(that.repulsion / 100.0));
        });
      },
      
      updateVelocity:function(timestep){
        // translate forces to a new velocity for this particle
        $.each(active.particles, function(id, point) {
          if (point.fixed){
             point.v = new Point(0,0)
             point.f = new Point(0,0)
             return
          }

          var was = point.v.magnitude()
          point.v = point.v.add(point.f.multiply(timestep)).multiply(1-that.friction);
          point.f.x = point.f.y = 0

          var speed = point.v.magnitude()          
          if (speed>SPEED_LIMIT) point.v = point.v.divide(speed*speed)
        });
      },

      updatePosition:function(timestep){
        // translate velocity to a position delta
        var sum=0, max=0, n = 0;
        var bottomright = null
        var topleft = null

        $.each(active.particles, function(i, point) {
          // move the node to its new position
          point.p = point.p.add(point.v.multiply(timestep));
          
          // keep stats to report in systemEnergy
          var speed = point.v.magnitude();
          var e = speed*speed
          sum += e
          max = Math.max(e,max)
          n++

          if (!bottomright){
            bottomright = new Point(point.p.x, point.p.y)
            topleft = new Point(point.p.x, point.p.y)
            return
          }
        
          var pt = point.p
          if (pt.x===null || pt.y===null) return
          if (pt.x > bottomright.x) bottomright.x = pt.x;
          if (pt.y > bottomright.y) bottomright.y = pt.y;          
          if   (pt.x < topleft.x)   topleft.x = pt.x;
          if   (pt.y < topleft.y)   topleft.y = pt.y;
        });
        
        _energy = {sum:sum, max:max, mean:sum/n, n:n}
        _bounds = {topleft:topleft||new Point(-1,-1), bottomright:bottomright||new Point(1,1)}
      },

      systemEnergy:function(timestep){
        // system stats
        return _energy
      }

      
    }
    return that.init()
  }
  
  var _nearParticle = function(center_pt, r){
      var r = r || .0
      var x = center_pt.x
      var y = center_pt.y
      var d = r*2
      return new Point(x-r+Math.random()*d, y-r+Math.random()*d)
  }

//
// system.js
//
// the main controller object for creating/modifying graphs 
//

  var ParticleSystem = function(repulsion, stiffness, friction, centerGravity, targetFps, dt, precision){
  // also callable with ({stiffness:, repulsion:, friction:, timestep:, fps:, dt:, gravity:})
    
    var _changes=[]
    var _notification=null
    var _epoch = 0

    var _screenSize = null
    var _screenStep = .04
    var _screenPadding = [20,20,20,20]
    var _bounds = null
    var _boundsTarget = null

    if (typeof stiffness=='object'){
      var _p = stiffness
      friction = _p.friction
      repulsion = _p.repulsion
      targetFps = _p.fps
      dt = _p.dt
      stiffness = _p.stiffness
      centerGravity = _p.gravity
      precision = _p.precision
    }

    friction = isNaN(friction) ? .5 : friction
    repulsion = isNaN(repulsion) ? 1000 : repulsion
    targetFps = isNaN(targetFps) ? 55 : targetFps
    stiffness = isNaN(stiffness) ? 600 : stiffness
    dt = isNaN(dt) ? 0.02 : dt
    precision = isNaN(precision) ? .6 : precision
    centerGravity = (centerGravity===true)
    var _systemTimeout = (targetFps!==undefined) ? 1000/targetFps : 1000/50
    var _parameters = {repulsion:repulsion, stiffness:stiffness, friction:friction, dt:dt, gravity:centerGravity, precision:precision, timeout:_systemTimeout}
    var _energy

    var state = {
      renderer:null, // this is set by the library user
      tween:null, // gets filled in by the Kernel
      nodes:{}, // lookup based on node _id's from the worker
      edges:{}, // likewise
      adjacency:{}, // {name1:{name2:{}, name3:{}}}
      names:{}, // lookup table based on 'name' field in data objects
      kernel: null
    }

    var that={
      parameters:function(newParams){
        if (newParams!==undefined){
          if (!isNaN(newParams.precision)){
            newParams.precision = Math.max(0, Math.min(1, newParams.precision))
          }
          $.each(_parameters, function(p, v){
            if (newParams[p]!==undefined) _parameters[p] = newParams[p]
          })
          state.kernel.physicsModified(newParams)
        }
        return _parameters
      },

      fps:function(newFPS){
        if (newFPS===undefined) return state.kernel.fps()
        else that.parameters({timeout:1000/(newFPS||50)})
      },


      start:function(){
        state.kernel.start()
      },
      stop:function(){
        state.kernel.stop()
      },

      addNode:function(name, data){
        data = data || {}
        var priorNode = state.names[name]
        if (priorNode){
          priorNode.data = data
          return priorNode
        }else if (name!=undefined){
          // the data object has a few magic fields that are actually used
          // by the simulation:
          //   'mass' overrides the default of 1
          //   'fixed' overrides the default of false
          //   'x' & 'y' will set a starting position rather than 
          //             defaulting to random placement
          var x = (data.x!=undefined) ? data.x : null
          var y = (data.y!=undefined) ? data.y : null
          var fixed = (data.fixed) ? 1 : 0

          var node = new Node(data)
          node.name = name
          state.names[name] = node
          state.nodes[node._id] = node;

          _changes.push({t:"addNode", id:node._id, m:node.mass, x:x, y:y, f:fixed})
          that._notify();
          return node;

        }
      },

      // remove a node and its associated edges from the graph
      pruneNode:function(nodeOrName) {
        var node = that.getNode(nodeOrName)
        
        if (typeof(state.nodes[node._id]) !== 'undefined'){
          delete state.nodes[node._id]
          delete state.names[node.name]
        }


        $.each(state.edges, function(id, e){
          if (e.source._id === node._id || e.target._id === node._id){
            that.pruneEdge(e);
          }
        })

        _changes.push({t:"dropNode", id:node._id})
        that._notify();
      },

      getNode:function(nodeOrName){
        if (nodeOrName._id!==undefined){
          return nodeOrName
        }else if (typeof nodeOrName=='string' || typeof nodeOrName=='number'){
          return state.names[nodeOrName]
        }
        // otherwise let it return undefined
      },

      eachNode:function(callback){
        // callback should accept two arguments: Node, Point
        $.each(state.nodes, function(id, n){
          if (n._p.x==null || n._p.y==null) return
          var pt = (_screenSize!==null) ? that.toScreen(n._p) : n._p
          callback.call(that, n, pt);
        })
      },

      addEdge:function(source, target, data){
        source = that.getNode(source) || that.addNode(source)
        target = that.getNode(target) || that.addNode(target)
        data = data || {}
        var edge = new Edge(source, target, data);

        var src = source._id
        var dst = target._id
        state.adjacency[src] = state.adjacency[src] || {}
        state.adjacency[src][dst] = state.adjacency[src][dst] || []

        var exists = (state.adjacency[src][dst].length > 0)
        if (exists){
          // probably shouldn't allow multiple edges in same direction
          // between same nodes? for now just overwriting the data...
          $.extend(state.adjacency[src][dst].data, edge.data)
          return
        }else{
          state.edges[edge._id] = edge
          state.adjacency[src][dst].push(edge)
          var len = (edge.length!==undefined) ? edge.length : 1
          _changes.push({t:"addSpring", id:edge._id, fm:src, to:dst, l:len})
          that._notify()
        }

        return edge;

      },

      // remove an edge and its associated lookup entries
      pruneEdge:function(edge) {

        _changes.push({t:"dropSpring", id:edge._id})
        delete state.edges[edge._id]
        
        for (var x in state.adjacency){
          for (var y in state.adjacency[x]){
            var edges = state.adjacency[x][y];

            for (var j=edges.length - 1; j>=0; j--)  {
              if (state.adjacency[x][y][j]._id === edge._id){
                state.adjacency[x][y].splice(j, 1);
              }
            }
          }
        }

        that._notify();
      },

      // find the edges from node1 to node2
      getEdges:function(node1, node2) {
        node1 = that.getNode(node1)
        node2 = that.getNode(node2)
        if (!node1 || !node2) return []
        
        if (typeof(state.adjacency[node1._id]) !== 'undefined'
          && typeof(state.adjacency[node1._id][node2._id]) !== 'undefined'){
          return state.adjacency[node1._id][node2._id];
        }

        return [];
      },

      getEdgesFrom:function(node) {
        node = that.getNode(node)
        if (!node) return []
        
        if (typeof(state.adjacency[node._id]) !== 'undefined'){
          var nodeEdges = []
          $.each(state.adjacency[node._id], function(id, subEdges){
            nodeEdges = nodeEdges.concat(subEdges)
          })
          return nodeEdges
        }

        return [];
      },

      getEdgesTo:function(node) {
        node = that.getNode(node)
        if (!node) return []

        var nodeEdges = []
        $.each(state.edges, function(edgeId, edge){
          if (edge.target == node) nodeEdges.push(edge)
        })
        
        return nodeEdges;
      },

      eachEdge:function(callback){
        // callback should accept two arguments: Edge, Point
        $.each(state.edges, function(id, e){
          var p1 = state.nodes[e.source._id]._p
          var p2 = state.nodes[e.target._id]._p


          if (p1.x==null || p2.x==null) return
          
          p1 = (_screenSize!==null) ? that.toScreen(p1) : p1
          p2 = (_screenSize!==null) ? that.toScreen(p2) : p2
          
          if (p1 && p2) callback.call(that, e, p1, p2);
        })
      },


      prune:function(callback){
        // callback should be of the form ƒ(node, {from:[],to:[]})

        var changes = {dropped:{nodes:[], edges:[]}}
        if (callback===undefined){
          $.each(state.nodes, function(id, node){
            changes.dropped.nodes.push(node)
            that.pruneNode(node)
          })
        }else{
          that.eachNode(function(node){
            var drop = callback.call(that, node, {from:that.getEdgesFrom(node), to:that.getEdgesTo(node)})
            if (drop){
              changes.dropped.nodes.push(node)
              that.pruneNode(node)
            }
          })
        }
        // trace('prune', changes.dropped)
        return changes
      },
      
      graft:function(branch){
        // branch is of the form: { nodes:{name1:{d}, name2:{d},...}, 
        //                          edges:{fromNm:{toNm1:{d}, toNm2:{d}}, ...} }

        var changes = {added:{nodes:[], edges:[]}}
        if (branch.nodes) $.each(branch.nodes, function(name, nodeData){
          var oldNode = that.getNode(name)
          // should probably merge any x/y/m data as well...
          // if (oldNode) $.extend(oldNode.data, nodeData)
          
          if (oldNode) oldNode.data = nodeData
          else changes.added.nodes.push( that.addNode(name, nodeData) )
          
          state.kernel.start()
        })
        
        if (branch.edges) $.each(branch.edges, function(src, dsts){
          var srcNode = that.getNode(src)
          if (!srcNode) changes.added.nodes.push( that.addNode(src, {}) )

          $.each(dsts, function(dst, edgeData){

            // should probably merge any x/y/m data as well...
            // if (srcNode) $.extend(srcNode.data, nodeData)


            // i wonder if it should spawn any non-existant nodes that are part
            // of one of these edge requests...
            var dstNode = that.getNode(dst)
            if (!dstNode) changes.added.nodes.push( that.addNode(dst, {}) )

            var oldEdges = that.getEdges(src, dst)
            if (oldEdges.length>0){
              // trace("update",src,dst)
              oldEdges[0].data = edgeData
            }else{
            // trace("new ->",src,dst)
              changes.added.edges.push( that.addEdge(src, dst, edgeData) )
            }
          })
        })

        // trace('graft', changes.added)
        return changes
      },

      merge:function(branch){
        var changes = {added:{nodes:[], edges:[]}, dropped:{nodes:[], edges:[]}}

        $.each(state.edges, function(id, edge){
          // if ((branch.edges[edge.source.name]===undefined || branch.edges[edge.source.name][edge.target.name]===undefined) &&
          //     (branch.edges[edge.target.name]===undefined || branch.edges[edge.target.name][edge.source.name]===undefined)){
          if ((branch.edges[edge.source.name]===undefined || branch.edges[edge.source.name][edge.target.name]===undefined)){
                that.pruneEdge(edge)
                changes.dropped.edges.push(edge)
              }
        })
        
        var prune_changes = that.prune(function(node, edges){
          if (branch.nodes[node.name] === undefined){
            changes.dropped.nodes.push(node)
            return true
          }
        })
        var graft_changes = that.graft(branch)        
        changes.added.nodes = changes.added.nodes.concat(graft_changes.added.nodes)
        changes.added.edges = changes.added.edges.concat(graft_changes.added.edges)
        changes.dropped.nodes = changes.dropped.nodes.concat(prune_changes.dropped.nodes)
        changes.dropped.edges = changes.dropped.edges.concat(prune_changes.dropped.edges)
        
        // trace('changes', changes)
        return changes
      },

      
      tweenNode:function(nodeOrName, dur, to){
        var node = that.getNode(nodeOrName)
        if (node) state.tween.to(node, dur, to)
      },

      tweenEdge:function(a,b,c,d){
        if (d===undefined){
          // called with (edge, dur, to)
          that._tweenEdge(a,b,c)
        }else{
          // called with (node1, node2, dur, to)
          var edges = that.getEdges(a,b)
          $.each(edges, function(i, edge){
            that._tweenEdge(edge, c, d)    
          })
        }
      },

      _tweenEdge:function(edge, dur, to){
        if (edge && edge._id!==undefined) state.tween.to(edge, dur, to)
      },

      _updateGeometry:function(e){
        if (e != undefined){          
          var stale = (e.epoch<_epoch)

          _energy = e.energy
          var pts = e.geometry // an array of the form [id1,x1,y1, id2,x2,y2, ...]
          if (pts!==undefined){
            for (var i=0, j=pts.length/3; i<j; i++){
              var id = pts[3*i]
                            
              // canary silencer...
              if (stale && state.nodes[id]==undefined) continue;
              
              state.nodes[id]._p.x = pts[3*i + 1]
              state.nodes[id]._p.y = pts[3*i + 2]
            }
          }          
        }
      },
      
      // convert to/from screen coordinates
      screen:function(opts){
        if (opts == undefined) return {size:(_screenSize)? objcopy(_screenSize) : undefined, 
                                       padding:_screenPadding.concat(), 
                                       step:_screenStep}
        if (opts.size!==undefined) that.screenSize(opts.size.width, opts.size.height)
        if (!isNaN(opts.step)) that.screenStep(opts.step)
        if (opts.padding!==undefined) that.screenPadding(opts.padding)
      },
      
      screenSize:function(canvasWidth, canvasHeight){
        _screenSize = {width:canvasWidth,height:canvasHeight}
        that._updateBounds()
      },

      screenPadding:function(t,r,b,l){
        if ($.isArray(t)) trbl = t
        else trbl = [t,r,b,l]

        var top = trbl[0]
        var right = trbl[1]
        var bot = trbl[2]
        if (right===undefined) trbl = [top,top,top,top]
        else if (bot==undefined) trbl = [top,right,top,right]
        
        _screenPadding = trbl
      },

      screenStep:function(stepsize){
        _screenStep = stepsize
      },

      toScreen:function(p) {
        if (!_bounds || !_screenSize) return
        // trace(p.x, p.y)

        var _padding = _screenPadding || [0,0,0,0]
        var size = _bounds.bottomright.subtract(_bounds.topleft)
        var sx = _padding[3] + p.subtract(_bounds.topleft).divide(size.x).x * (_screenSize.width - (_padding[1] + _padding[3]))
        var sy = _padding[0] + p.subtract(_bounds.topleft).divide(size.y).y * (_screenSize.height - (_padding[0] + _padding[2]))

        // return arbor.Point(Math.floor(sx), Math.floor(sy))
        return arbor.Point(sx, sy)
      },
      
      fromScreen:function(s) {
        if (!_bounds || !_screenSize) return

        var _padding = _screenPadding || [0,0,0,0]
        var size = _bounds.bottomright.subtract(_bounds.topleft)
        var px = (s.x-_padding[3]) / (_screenSize.width-(_padding[1]+_padding[3]))  * size.x + _bounds.topleft.x
        var py = (s.y-_padding[0]) / (_screenSize.height-(_padding[0]+_padding[2])) * size.y + _bounds.topleft.y

        return arbor.Point(px, py);
      },

      _updateBounds:function(newBounds){
        // step the renderer's current bounding box closer to the true box containing all
        // the nodes. if _screenStep is set to 1 there will be no lag. if _screenStep is
        // set to 0 the bounding box will remain stationary after being initially set 
        if (_screenSize===null) return
        
        if (newBounds) _boundsTarget = newBounds
        else _boundsTarget = that.bounds()
        
        // _boundsTarget = newBounds || that.bounds()
        // _boundsTarget.topleft = new Point(_boundsTarget.topleft.x,_boundsTarget.topleft.y)
        // _boundsTarget.bottomright = new Point(_boundsTarget.bottomright.x,_boundsTarget.bottomright.y)

        var bottomright = new Point(_boundsTarget.bottomright.x, _boundsTarget.bottomright.y)
        var topleft = new Point(_boundsTarget.topleft.x, _boundsTarget.topleft.y)
        var dims = bottomright.subtract(topleft)
        var center = topleft.add(dims.divide(2))


        var MINSIZE = 4                                   // perfect-fit scaling
        // MINSIZE = Math.max(Math.max(MINSIZE,dims.y), dims.x) // proportional scaling

        var size = new Point(Math.max(dims.x,MINSIZE), Math.max(dims.y,MINSIZE))
        _boundsTarget.topleft = center.subtract(size.divide(2))
        _boundsTarget.bottomright = center.add(size.divide(2))

        if (!_bounds){
          if ($.isEmptyObject(state.nodes)) return false
          _bounds = _boundsTarget
          return true
        }
        
        // var stepSize = (Math.max(dims.x,dims.y)<MINSIZE) ? .2 : _screenStep
        var stepSize = _screenStep
        _newBounds = {
          bottomright: _bounds.bottomright.add( _boundsTarget.bottomright.subtract(_bounds.bottomright).multiply(stepSize) ),
          topleft: _bounds.topleft.add( _boundsTarget.topleft.subtract(_bounds.topleft).multiply(stepSize) )
        }
        
        // return true if we're still approaching the target, false if we're ‘close enough’
        var diff = new Point(_bounds.topleft.subtract(_newBounds.topleft).magnitude(), _bounds.bottomright.subtract(_newBounds.bottomright).magnitude())        
        if (diff.x*_screenSize.width>1 || diff.y*_screenSize.height>1){
          _bounds = _newBounds
          return true
        }else{
         return false        
        }
      },

      energy:function(){
        return _energy
      },

      bounds:function(){
        //  TL   -1
        //     -1   1
        //        1   BR
        var bottomright = null
        var topleft = null

        // find the true x/y range of the nodes
        $.each(state.nodes, function(id, node){
          if (!bottomright){
            bottomright = new Point(node._p)
            topleft = new Point(node._p)
            return
          }
        
          var point = node._p
          if (point.x===null || point.y===null) return
          if (point.x > bottomright.x) bottomright.x = point.x;
          if (point.y > bottomright.y) bottomright.y = point.y;          
          if   (point.x < topleft.x)   topleft.x = point.x;
          if   (point.y < topleft.y)   topleft.y = point.y;
        })


        // return the true range then let to/fromScreen handle the padding
        if (bottomright && topleft){
          return {bottomright: bottomright, topleft: topleft}
        }else{
          return {topleft: new Point(-1,-1), bottomright: new Point(1,1)};
        }
      },

      // Find the nearest node to a particular position
      nearest:function(pos){
        if (_screenSize!==null) pos = that.fromScreen(pos)
        // if screen size has been specified, presume pos is in screen pixel
        // units and convert it back to the particle system coordinates
        
        var min = {node: null, point: null, distance: null};
        var t = that;
        
        $.each(state.nodes, function(id, node){
          var pt = node._p
          if (pt.x===null || pt.y===null) return
          var distance = pt.subtract(pos).magnitude();
          if (min.distance === null || distance < min.distance){
            min = {node: node, point: pt, distance: distance};
            if (_screenSize!==null) min.screenPoint = that.toScreen(pt)
          }
        })
        
        if (min.node){
          if (_screenSize!==null) min.distance = that.toScreen(min.node.p).subtract(that.toScreen(pos)).magnitude()
           return min
        }else{
           return null
        }
      },

      _notify:function() {
        // pass on graph changes to the physics object in the worker thread
        // (using a short timeout to batch changes)
        if (_notification===null) _epoch++
        else clearTimeout(_notification)
        
        _notification = setTimeout(that._synchronize,20)
        // that._synchronize()
      },
      _synchronize:function(){
        if (_changes.length>0){
          state.kernel.graphChanged(_changes)
          _changes = []
          _notification = null
        }
      }
    }    
    
    state.kernel = Kernel(that)
    state.tween = state.kernel.tween || null



    // some magic attrs to make the Node objects phone-home their physics-relevant changes

    var defineProperty = Object.defineProperty ||
      function (obj, name, desc) {
        if (desc.get)
          obj.__defineGetter__(name, desc.get)
        if (desc.set)
          obj.__defineSetter__(name, desc.set)
      }

    var RoboPoint = function (n) {
      this._n = n;
    }
    RoboPoint.prototype = new Point();
    defineProperty(RoboPoint.prototype, "x", {
      get: function(){ return this._n._p.x; },
      set: function(newX){ state.kernel.particleModified(this._n._id, {x:newX}) }
    })
    defineProperty(RoboPoint.prototype, "y", {
      get: function(){ return this._n._p.y; },
      set: function(newY){ state.kernel.particleModified(this._n._id, {y:newY}) }
    })

    defineProperty(Node.prototype, "p", {
      get: function() { 
        return new RoboPoint(this)
      },
      set: function(newP) { 
        this._p.x = newP.x
        this._p.y = newP.y
        state.kernel.particleModified(this._id, {x:newP.x, y:newP.y})
      }
    })

    defineProperty(Node.prototype, "mass", {
      get: function() { return this._mass; },
      set: function(newM) { 
        this._mass = newM
        state.kernel.particleModified(this._id, {m:newM})
      }
    })

    defineProperty(Node.prototype, "tempMass", {
      set: function(newM) { 
        state.kernel.particleModified(this._id, {_m:newM})
      }
    })

    defineProperty(Node.prototype, "fixed", {
      get: function() { return this._fixed; },
      set:function(isFixed) { 
        this._fixed = isFixed
        state.kernel.particleModified(this._id, {f:isFixed?1:0})
      }
    })
    
    return that
  };
  

//
// dev.js
//
// module wrapper for running from the un-minified src files
//
//
// to run from src, make sure your html includes look like:
//   <script src="js/src/etc.js"></script>
//   <script src="js/src/kernel.js"></script>
//   <script src="js/src/graphics/colors.js"></script>
//   <script src="js/src/graphics/primitives.js"></script>
//   <script src="js/src/graphics/graphics.js"></script>
//   <script src="js/src/tween/easing.js"></script>
//   <script src="js/src/tween/tween.js"></script>
//   <script src="js/src/physics/atoms.js"></script>
//   <script src="js/src/physics/physics.js"></script>
//   <script src="js/src/physics/system.js"></script>
//   <script src="js/src/dev.js"></script>


(function(){

  arbor = (typeof(arbor)!=='undefined') ? arbor : {}
  $.extend(arbor, {
    // object constructors (don't use ‘new’, just call them)
    ParticleSystem:ParticleSystem,
    Tween:Tween,
    Point:function(x, y){ return new Point(x, y) },
    Graphics:function(canvas){ return Graphics(canvas) },

    // immutable objects with useful methods
    colors:{
      CSS:Colors.CSS,           // {colorname:#fef2e2,...}
      validate:Colors.validate, // ƒ(str) -> t/f
      decode:Colors.decode,     // ƒ(hexString_or_cssColor) -> {r,g,b,a}
      encode:Colors.encode,     // ƒ({r,g,b,a}) -> hexOrRgbaString
      blend:Colors.blend        // ƒ(color, opacity) -> rgbaString
    },
    etc:{      
      trace:trace,              // ƒ(msg) -> safe console logging
      dirname:dirname,          // ƒ(path) -> leading part of path
      basename:basename,        // ƒ(path) -> trailing part of path
      ordinalize:ordinalize,    // ƒ(num) -> abbrev integers (and add commas)
      objcopy:objcopy,          // ƒ(old) -> clone an object
      objcmp:objcmp,            // ƒ(a, b, strict_ordering) -> t/f comparison
      objkeys:objkeys,          // ƒ(obj) -> array of all keys in obj
      objmerge:objmerge,        // ƒ(dst, src) -> like $.extend but non-destructive
      uniq:uniq,                // ƒ(arr) -> array of unique items in arr
      arbor_path:arbor_path    // ƒ() -> guess the directory of the lib code
    }
  })

  
})();

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
					width  : jQCanvas.width()
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
					width  : (win.width() > 980) ? (win.width() - 355) : (win.width() - 5)
				}
			});

			jQCanvas.attr('width', (win.width() > 980) ? (win.width() - 355) : (win.width() - 5));
	
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
