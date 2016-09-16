function preDraw() {
  for (var f in fields) {
    var field = fields[f];
    console.log(field);

    var r = this.rect(
        Math.round(pW * field.x1),
        Math.round(pH * field.y1),
        Math.round(pW * (field.x2 - field.x1)),
        Math.round(pH * (field.y2 - field.y1))
      )
      .attr('fill', '#F00')
      .attr('opacity', 0.7)
      .attr('stroke', '#FFF')
      .attr('stroke-width', 6);
    r.field = field;

    var t = this.text(
        Math.round(pW * field.x1 + 6),
        Math.round(pH * field.y1 + 8),
        field.contentTypes.map(function(c) { return c.name; }).join(' - ')
      );
    r.text = t.attr('text-anchor', 'start');
    r.drag(handleMove, handleMoveStart, handleMoveEnd)
      .click(editField);
  }
}

function editField() {
  if (!this.moved) {
    $("#field-modal").html();
    $.get(editFieldUrl + this.field.id, function(html) {
      $("#field-modal").html($(html));
      $('.modal').modal('show');
    });
  }
}

var resizeMin = 20;
function handleMove(dx, dy, x, y, e) {
  var oX = this.ox;
  var oY = this.oy;
  var oH = this.oh;
  var oW = this.ow;
  this.moved = true;

  switch(this.resizing) {
    case 'TOP':
      if (oH - dy < resizeMin) {
        dy = oH - resizeMin;
      }
      if (oY + dy < 0) {
        dy = -oY;
      }
      oH = oH - dy;
      oY = oY + dy;
      break;
    case 'BOTTOM':
      if (oH + dy < resizeMin) {
        dy = resizeMin - oH;
      }
      if (oY + oH + dy > pH) {
        dy = pH - oY - oH; // oY + oH + dY = pH -> dY = 
      }
      oH = oH + dy;
      console.log(oY, oH, dy);
      break;
    case 'LEFT':
      if (oW - dx < resizeMin) {
        dx = oW - resizeMin;
      }
      if (oX + dx < 0) {
        dx = -oX;
      }
      oW = oW - dx;
      oX = oX + dx;
      break;
    case 'RIGHT':
      if (oW + dx < resizeMin) {
        dx = resizeMin - oW;
      }
      if (oX + oW + dx > pW) {
        dx = pW - oX - oW;
      }
      oW = oW + dx;
      break;
    default:
      oX = Math.max(0, Math.min(oX + dx, pW - oW));
      oY = Math.max(0, Math.min(oY + dy, pH - oH));
  }

  var pos = {
    x: oX,
    y: oY,
    width: oW,
    height: oH,
  };
  this.attr(pos).text.attr({x: oX + 6, y: oY + 8});
}

var resizeTreshold = 6;
function handleMoveStart(x, y, e) {
  this.toFront();
  this.text.toFront();
  this.resizing = null;
  this.moved = false;

  var mX = x - pX;
  var mY = y - pY;

  this.ox = this.attr('x');
  this.oy = this.attr('y');
  this.ow = this.attr('width');
  this.oh = this.attr('height');

  if (mX - this.ox < resizeTreshold) {
    this.resizing = 'LEFT';
  } else if (mY - this.oy < resizeTreshold) {
    this.resizing = 'TOP';
  } else if ((this.ox + this.ow) - mX < resizeTreshold) {
    this.resizing = 'RIGHT';
  } else if ((this.oy + this.oh) - mY < resizeTreshold) {
    this.resizing = 'BOTTOM';
  }
}

function handleMoveEnd(e) {
  if (!this.moved) {
    return;
  }

  var o = this.field;
  o.x1 = this.attr('x') / pW;
  o.y1 = this.attr('y') / pH;
  o.x2 = (this.attr('x') + this.attr('width')) / pW;
  o.y2 = (this.attr('y') + this.attr('height')) / pH;
  //console.log(o);
  $.post(setFieldPosUrl + o.id, {Field: o});
}

var pW;
var pH;
var pX;
var pY;

function load() {
  var $b = $('.background-edit');
  pW = $b.width();
  pH = $b.height();
  var offset = $b.offset();
  pX = offset.left;
  pY = offset.top;
  Raphael('design', pW, pH, preDraw);
}

$(load);
