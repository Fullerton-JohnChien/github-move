var slideGallery=new Class({Version:"1.3.1",Implements:[Options,Events],options:{holder:".holder",elementsParent:"ul",elements:"li",nextItem:".next",prevItem:".prev",stop:".stop",start:".start",speed:600,duration:4000,steps:1,current:0,transition:"sine:in:out",direction:"horizontal",mode:"callback",currentClass:"current",nextDisableClass:"next-disable",prevDisableClass:"prev-disable",paging:false,pagingEvent:"click",pagingHolder:".paging",random:false,autoplay:false,autoplayOpposite:false,stopOnHover:true},initialize:function(a,b){if(a.length==null){this.gallery=a}else{this.gallery=a[0]}if(!this.gallery){return false}this.setOptions(b);this.holder=this.gallery.getElement(this.options.holder);this.itemsParent=this.holder.getElement(this.options.elementsParent);this.items=this.itemsParent.getElements(this.options.elements);this.next=this.gallery.getElement(this.options.nextItem);this.prev=this.gallery.getElement(this.options.prevItem);this.stop=this.gallery.getElement(this.options.stop);this.start=this.gallery.getElement(this.options.start);this.current=this.options.current;this.bound={rotate:this.rotate.bind(this)};if(this.options.direction=="horizontal"){this.direction="margin-left";this.size=this.items[0].getWidth();this.visible=Math.round(this.holder.getWidth()/this.size)}else{this.direction="margin-top";this.size=this.items[0].getHeight();this.visible=Math.round(this.holder.getHeight()/this.size)}if(this.items.length<=this.visible){if(this.next){this.next.addClass(this.options.nextDisableClass).addEvent("click",function(){return false})}if(this.prev){this.prev.addClass(this.options.prevDisableClass).addEvent("click",function(){return false})}if(this.stop){this.stop.addEvent("click",function(){return false})}if(this.start){this.start.addEvent("click",function(){return false})}this.gallery.addClass("stopped no-active");this.fireEvent("start",this.current,this.visible,this.items.length,this.items[this.current]);return false}this.options.steps=this.options.steps>this.visible?this.visible:this.options.steps;this.options.duration=this.options.duration<1000?1000:this.options.duration;this.options.speed=this.options.speed>6000?6000:this.options.speed;if(this.options.speed>this.options.duration){this.options.speed=this.options.duration}this.fx=new Fx.Tween(this.itemsParent,{property:this.direction,duration:this.options.speed,transition:this.options.transition,link:"cancel",fps:100,onCancel:function(){if(!this.callChain()){this.fireEvent("chainComplete",this.subject)}return this}});if(this.options.random){this.shuffle()}this.getInitialCurrent();if(this.options.mode=="circle"){while(this.items.length<this.options.steps+this.visible){this.itemsParent.innerHTML+=this.itemsParent.innerHTML;this.items=this.itemsParent.getElements(this.options.elements)}for(var c=0;c<this.current;c++){this.items[c].inject(this.itemsParent,"bottom")}this.options.paging=false}else{if(this.options.paging){this.createPaging()}this.play(false)}if(this.next){this.next.addEvent("click",function(){this.nextSlide();return false}.bind(this))}if(this.prev){this.prev.addEvent("click",function(){this.prevSlide();return false}.bind(this))}if(this.options.autoplay||this.options.autoplayOpposite){this.timer=this.bound.rotate.delay(this.options.duration)}else{this.gallery.addClass("stopped")}if(this.start){this.start.addEvent("click",function(){clearTimeout(this.timer);this.gallery.removeClass("stopped");this.timer=this.bound.rotate.delay(this.options.duration);return false}.bind(this))}if(this.stop){this.stop.addEvent("click",function(){this.gallery.addClass("stopped");clearTimeout(this.timer);return false}.bind(this))}if(this.options.stopOnHover){this.gallery.addEvent("mouseenter",function(){clearTimeout(this.timer)}.bind(this));this.gallery.addEvent("mouseleave",function(){if(!this.gallery.hasClass("stopped")){clearTimeout(this.timer);this.timer=this.bound.rotate.delay(this.options.duration)}}.bind(this))}this.fireEvent("start",this.current,this.visible,this.items.length,this.items[this.current])},getInitialCurrent:function(){var a=this.items.get("class").indexOf(this.options.currentClass);if(a!=-1){this.current=a}else{if(this.current>this.items.length-1){this.current=this.items.length-1}else{if(this.current<0){this.current=0}}}if(this.options.mode!="circle"&&this.visible+this.current>=this.items.length){this.current=this.items.length-this.visible}return this},rotate:function(){if(!this.options.autoplayOpposite){this.nextSlide()}else{this.prevSlide()}this.timer=this.bound.rotate.delay(this.options.duration);return this},play:function(a){if(this.options.mode=="line"){this.sidesChecking()}if(a){this.fx.start(-this.current*this.size)}else{this.fx.set(-this.current*this.size)}if(this.options.paging){this.setActivePage()}this.fireEvent("play",this.current,this.visible,this.items.length,this.items[this.current]);return this},nextSlide:function(){if(this.options.mode!="circle"){if(this.visible+this.current>=this.items.length){if(this.options.mode=="callback"){this.current=0}}else{if(this.visible+this.current+this.options.steps>=this.items.length){this.current=this.items.length-this.visible}else{this.current+=this.options.steps}}this.play(true)}else{var a=this.current;if((this.current+=this.options.steps)>=this.items.length){this.current-=this.items.length}this.fx.start(-this.size*this.options.steps).chain(function(){for(var b=0;b<this.options.steps;b++){if(a>=this.items.length){a=0}this.items[a++].inject(this.itemsParent,"bottom")}this.fx.set(0)}.bind(this));this.fireEvent("play",this.current,this.visible,this.items.length,this.items[this.current])}return this},prevSlide:function(){if(this.options.mode!="circle"){if(this.current<=0){if(this.options.mode=="callback"){this.current=this.items.length-this.visible}}else{if(this.current-this.options.steps<=0){this.current=0}else{this.current-=this.options.steps}}this.play(true)}else{for(var a=0;a<this.options.steps;a++){if(this.current-1<0){this.current=this.items.length}this.items[--this.current].inject(this.itemsParent,"top")}this.fx.set(-this.size*this.options.steps).start(0);this.fireEvent("play",this.current,this.visible,this.items.length,this.items[this.current])}return this},sidesChecking:function(){this.next.removeClass(this.options.nextDisableClass);this.prev.removeClass(this.options.prevDisableClass);if(this.visible+this.current>=this.items.length){this.next.addClass(this.options.nextDisableClass)}else{if(this.current==0){this.prev.addClass(this.options.prevDisableClass)}}return this},createPaging:function(){this.paging=new Element("ul");var c=this.gallery.getElement(this.options.pagingHolder);if(c!=null){this.paging.inject(c)}else{this.paging.inject(this.gallery).addClass("paging")}var b=Math.ceil((this.items.length-this.visible)/this.options.steps)+1;var d="";for(var a=0;a<b;a++){d+='<li><a href="#">'+parseInt(a+1)+"</a></li>"}this.paging=this.paging.set("html",d).getElements("a");this.paging.each(function(f,e){f.addEvent(this.options.pagingEvent,function(){if(e<b-1){this.current=e*this.options.steps}else{this.current=this.items.length-this.visible}this.play(true);return false}.bind(this))}.bind(this));return this},setActivePage:function(){this.paging.removeClass("active")[Math.ceil(this.current/this.options.steps)].addClass("active");return this},shuffle:function(){var a="";this.items.sort(function(){return 0.5-Math.random()}).each(function(b){a+=new Element("div").adopt(b).get("html")});this.items=this.itemsParent.set("html",a).getElements(this.options.elements);return this}});var fadeGallery=new Class({Extends:slideGallery,initialize:function(a,b){if(b.mode=="circle"){b.mode="callback"}this.parent(a,b);this.fxFade=[];this.items.each(function(d,c){this.fxFade[c]=new Fx.Tween(d,{property:"opacity",duration:this.options.speed,transition:this.options.transition,link:"cancel"});this.fxFade[c].set(0)}.bind(this));this.play(false)},play:function(a){if(this.previous==null){this.previous=0;return false}if(this.options.mode=="line"){this.sidesChecking()}if(a){this.fxFade[this.previous].start(0);this.fxFade[this.current].start(1)}else{this.fxFade[this.previous].set(0);this.fxFade[this.current].set(1)}this.previous=this.current;if(this.options.paging){this.setActivePage()}this.fireEvent("play",this.current,this.visible,this.items.length,this.items[this.current])}});