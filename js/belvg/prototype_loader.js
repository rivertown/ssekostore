var prototypeLoader = Class.create();
prototypeLoader.prototype = {
    initialize: function(config){
        this.options        = Object.extend({
            loader: '/images/prototype_loader/ajax-loader.gif'
        }, arguments[0] || {});
        this.options.loader = config;
        this.createLoader();
    },
    createLoader: function(){
        var imgLoader       = '<div id="prototypeLoader" style="display:none"><img src="'+this.options.loader+'"></div>';	
        $$('body').each(function(el){
            new Insertion.Top(el, imgLoader);
        });
        $('prototypeLoader').addClassName('prototypeLoader');
    },  
    show: function(){
        Event.observe(document, 'click', positionLoader);
        Event.observe(document, 'mousemove', positionLoader);
    },
    hide: function(){
        $('prototypeLoader').hide();
        Event.stopObserving(document, 'click', positionLoader);
        Event.stopObserving(document, 'mousemove', positionLoader);
    }
}

function _getScroll(){
    /*if (self.pageYOffset) {
        return {scrollTop:self.pageYOffset,scrollLeft:self.pageXOffset};
    } else*/ if (document.documentElement && document.documentElement.scrollTop) { // Explorer 6 Strict
        return {scrollTop:document.documentElement.scrollTop,scrollLeft:document.documentElement.scrollLeft};
    } else if (document.body) {// all other Explorers
        return {scrollTop:document.body.scrollTop,scrollLeft:document.body.scrollLeft};
    };
};

function positionLoader(e){
    $('prototypeLoader').show();
    scrollPos   = _getScroll();
    e           = e ? e : window.event;
    cur_x       = (e.clientX) ? e.clientX : cur_x;
    cur_y       = (e.clientY) ? e.clientY : cur_y;
    left_pos    = cur_x + 13 + scrollPos['scrollLeft'];
    top_pos     = cur_y + 13 + scrollPos['scrollTop'];
    $('prototypeLoader').setStyle({
        top:top_pos+'px',
        left:left_pos+'px'
    });
}
