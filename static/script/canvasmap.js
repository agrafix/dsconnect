/**
 * HTML5 Canvas Map
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 * @copyright 2012 by Alexander Thiemann. All rights reserved.
 * @version 0.3
 */

function TW_CanvasMap(width, height, canvasID) 
{
    this.marked = {player: [], ally: [], village: []};
    
    this.width = width;
    this.height = height;
    
    this.center = {x: 500, y: 500};
    
    this.scale = 10;
    
    // load div
    this.loadDiv = $("<div>").addClass('twmap-loading').text("Loading...").css('width', width-30);
    $('#' + canvasID).before(this.loadDiv);
    this.loadDiv.hide();
    // end
    
    this.canvasEl = document.getElementById(canvasID);
    this.ctx = this.canvasEl.getContext("2d");
    
    this.clearMap();
    this.isLoading("Loading");
}

TW_CanvasMap.prototype.clearMarked = function()
{
    this.marked = {player: [], ally: [], village: []};
};

TW_CanvasMap.prototype.isLoading = function(text)
{
    this.ctx.font = '15pt Verdana';
    this.ctx.textAlign = 'center';
    
    this.ctx.fillStyle = 'rgb(0, 0, 0)';
    this.ctx.fillText(text, this.width/2, this.height/2);
    
    this.loadDiv.text(text);
    this.loadDiv.fadeIn();
};

TW_CanvasMap.prototype.doneLoading = function()
{
    $('.twmap-loading').stop().slideUp();
};

TW_CanvasMap.prototype.clearMap = function()
{
    this.ctx.fillStyle = 'rgb(40, 109, 29)';
    this.ctx.fillRect(0, 0, this.width, this.height);
};

TW_CanvasMap.prototype.setScale = function(s)
{
    this.scale = s;
}

TW_CanvasMap.prototype.setCenter = function(x, y)
{
    this.center.x = x;
    this.center.y = y;
};

TW_CanvasMap.prototype.drawGrid = function(gridSize, lnWidth)
{
    for (var i = 1; i<=gridSize; i++) {
        var x = i*(1000/gridSize) - this.center.x;
        x = (this.width / 2) + x*this.scale;
        
        if (x > 0 && x < this.width) {
            this._drawLine(x, 0, x, this.height, lnWidth);
        }
        
        var y = i*(1000/gridSize) - this.center.y;
        y = (this.height / 2) + y*this.scale;
        
        if (y > 0 && y < this.height) {
            this._drawLine(0, y, this.width, y, lnWidth);
        }
    }
};

TW_CanvasMap.prototype._drawLine = function(sx, sy, tx, ty, width)
{
    var offset = (width % 2 == 0 ? 0 : 0.5);
    
    this.ctx.lineWidth = width;
    
    this.ctx.strokeStyle = 'rgb(0, 0, 0)';
    this.ctx.fillStyle = 'rgb(0, 0, 0)';
    this.ctx.beginPath();
    this.ctx.moveTo(sx-offset, sy-offset);
    this.ctx.lineTo(tx-offset, ty-offset);
    this.ctx.closePath();
    this.ctx.stroke();
};

TW_CanvasMap.prototype.drawArrow = function(sx, sy, tx, ty)
{
    sx = this.scale/2 + (this.width / 2) + (sx - this.center.x) * this.scale;
    sy = this.scale/2 + (this.width / 2) + (sy - this.center.y) * this.scale;
    
    tx = this.scale/2 + (this.width / 2) + (tx - this.center.x) * this.scale;
    ty = this.scale/2 + (this.width / 2) + (ty - this.center.y) * this.scale;
    
    this.ctx.strokeStyle = 'rgba(0, 0, 0, 0.8)';
    this.ctx.lineWidth = 1.5;
    this.ctx.beginPath();
    // tail
    this.ctx.moveTo(sx, sy);
    this.ctx.lineTo(tx, ty);
    
    // head
    var angle = Math.atan2(ty-sy, tx-sx);
    var headSize = this.scale*1.2;
    
    this.ctx.lineTo(tx-headSize*Math.cos(angle-Math.PI/6),
                    ty-headSize*Math.sin(angle-Math.PI/6));
    this.ctx.moveTo(tx, ty);
    this.ctx.lineTo(tx-headSize*Math.cos(angle+Math.PI/6),
                    ty-headSize*Math.sin(angle+Math.PI/6));
                    
    this.ctx.closePath();
    this.ctx.stroke();
};

TW_CanvasMap.prototype.drawVillage = function(x, y, color)
{
    x -= this.center.x;
    y -= this.center.y;
    
    x = (this.width / 2) + x*this.scale;
    y = (this.height / 2) + y*this.scale;
    
    var spacing = 0;
    if (this.scale >= 5) {
        spacing = 1;
    }
    else if (this.scale >= 7) {
        spacing = 3;
    }
    
    this.ctx.fillStyle = color;
    this.ctx.fillRect(x+spacing, y+spacing, this.scale-spacing, this.scale-spacing);
};

TW_CanvasMap.prototype.markVillage = function(id, color) 
{
    this.marked.village.push({'id': id, 'color': color});
};

TW_CanvasMap.prototype.markPlayer = function(id, color) 
{
    this.marked.player.push({'id': id, 'color': color});
};

TW_CanvasMap.prototype.markAlly = function(id, color) 
{
    this.marked.ally.push({'id': id, 'color': color});
};

TW_CanvasMap.prototype.mark = function(type, id, color)
{
    this.marked[type].push({'id': id, 'color': color});
};

TW_CanvasMap.prototype.cacheName = function(data)
{
    var hash = 1;
    
    for(var i in data) {
        if (i == "x_min" || i == "x_max" || i == "y_min" || i == "y_max") {
            var tfact = 1000;
            
            if (i == "y_min" || i == "y_max") {
                tfact = 10000;
            }
            
            hash += data[i]*tfact;
        }
        else {
            for (var j in data[i]) {
                var fact = 1;
                
                if (j == "player") {
                    fact = 100;
                }
                else {
                    if (j == "ally") {
                        fact = 10;
                    }
                }
                
                hash += data[i][j]['id'] * fact;
            }
        }
    }
    
    hash = TWUtils.selectedWorld + "_" + hash;
    
    return hash;
};

TW_CanvasMap.prototype.render = function(dataApi, csrf_key, csrf_value, onComplete)
{
    if (!onComplete)
    {
        onComplete = function() {};
    }
    
    this.clearMap();
    
    var offsetX = Math.ceil((this.width / this.scale) / 2) + 1;
    var offsetY = Math.ceil((this.height / this.scale) / 2) + 1;
    
    var data = {
        'x_min': this.center.x - offsetX,
        'x_max': this.center.x + offsetX,
        'y_min': this.center.y - offsetY,
        'y_max': this.center.y + offsetY,
        
        'village': this.marked.village,
        'player': this.marked.player,
        'ally': this.marked.ally
    };
    
    var cacheName = this.cacheName(data);
    
    if (window.sessionStorage != null
        && window.sessionStorage.hasOwnProperty(cacheName)
        && navigator.userAgent.indexOf("Firefox") != -1) {
        this.isLoading('Loading map from cache...');
        
        // store complete func 
        this._tmpComplete = onComplete;
        
        // check if loadable from session cache?
        var cachedImg = new Image();
        cachedImg.onload = $.proxy(function() {
            this.clearMap();
            this.ctx.drawImage(cachedImg, 0, 0);
            
            // done loading
            this.doneLoading();
            
            // call on complete
            this._tmpComplete();
            
            // unset
            this._tmpComplete = undefined;
        }, this);
        
        // load image
        cachedImg.src = window.sessionStorage[cacheName];
        
        // thats it ;)
        return;
    }
    
    data[csrf_key] = csrf_value;
    
    this.isLoading('Downloading Map-Data...');
    
    $.post(dataApi, data,
    (function(self, completeFn, cName) {
        return function(villages) {
            self.clearMap();
            
            for(var i in villages)
            {
                var v = villages[i];
                
                self.drawVillage(v.x, v.y, v.color);
            }
            
            self.drawGrid(10, 1.5);
            
            if (self.scale >= 4) {
                self.drawGrid(100, 1);
            }
            
            self.doneLoading();
            
            completeFn();
            
            if (window.sessionStorage != null) {
                // session caching
                window.sessionStorage[cName] = self.canvasEl.toDataURL("image/png");
            }
        }
    })(this, onComplete, cacheName),
    'json');
};