// Raphael handler for draw init action
// Draws fields
function preDraw() {
  paper = this;
  for (var f in fields) {
    createField(fields[f]);
  }
}

// Draw single field with associated text and assign handlers
function createField(field) {
  var r = paper.rect(
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

  var t = paper.text(
    Math.round(pW * field.x1 + 6),
    Math.round(pH * field.y1 + 8),
    field.contentTypes ? field.contentTypes.map(function(c) {
      return c.id;
    }).join(' - ') : ''
  );
  r.text = t.attr('text-anchor', 'start');
  r.drag(handleMove, handleMoveStart, handleMoveEnd)
    .click(editField);
}

// Create a new field and store it
function addField($btn) {
  $.ajax({
    url: $btn.attr('href'),
    type: 'GET',
    data: {
      templateId: templateId
    },
    success: function(res) {
      if (res.success) {
        createField(res.field);
      } else {
        alert(res.message);
      }
    }
  });
}

// Show field modification popover
function editField() {
  if (!this.moved) {
    $("#field-modal").html();
    $.get(editFieldUrl + this.field.id, function(html) {
      $("#field-modal").html($(html));
      $('.modal').modal('show');
    });
  }
}

// Send field updates to backend
function updateField(id) {
  $.ajax({
    url: 'get-field',
    type: 'GET',
    data: {
      id: id
    },
    success: function(res) {
      if (res.success) {
        var field = res.field;
        getField(field.id, function(e) {
          e.attr('x', Math.round(pW * field.x1));
          e.attr('y', Math.round(pH * field.y1));
          e.attr('width', Math.round(pW * (field.x2 - field.x1)));
          e.attr('height', Math.round(pH * (field.y2 - field.y1)));
          e.text.attr('text', field.contentTypes ? field.contentTypes.map(function(c) {
            return c.id;
          }).join(' - ') : '');
        });
      }
    }
  })
}

// Find field based on ID in paper
function getField(id, cb) {
  paper.forEach(function(e) {
    if (e.type == 'rect' && e.field.id * 1 === id) {
      cb(e);
      return false;
    }
  });
}

// Remove a field based on ID
function removeField(id) {
  getField(id, function(e) {
    e.text.remove();
    e.remove();
  });
}

// Field move handler
var resizeMin = 20;

function handleMove(dx, dy, x, y, e) {
  var oX = this.ox;
  var oY = this.oy;
  var oH = this.oh;
  var oW = this.ow;
  this.moved = true;

  switch (this.resizing) {
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
  this.attr(pos).text.attr({
    x: oX + 6,
    y: oY + 8
  });
}

// Field move start handler
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

// Field move end handler
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
  $.post(setFieldPosUrl + o.id, {
    Field: o
  });
}

// Field creation on button click
$(document).on('click', '.field-add', function() {
  addField($(this));
  return false;
});

var pW;
var pH;
var pX;
var pY;
var paper = null;

// jQuery load
// Setup screen size and init Raphael
function load() {
  var $b = $('.background-edit');
  pW = $b.width();
  pH = $b.height();
  var offset = $b.offset();
  pX = offset.left;
  pY = offset.top;
  Raphael('design', pW, pH, preDraw);
}

// jQuery on load
$(load);

// Popover submit action
// Send data over ajax POST
$(document).on('beforeSubmit', '#screen-template-field-form', function() {
  var $form = $(this);
  if ($form.find('.has-error').length) {
    return false;
  }

  $.ajax({
    url: $form.attr('action'),
    type: $form.attr('method'),
    data: $form.serialize(),
    success: function(resp) {
      if (resp == '') {
        $('.modal').modal('hide');
        window.location.reload();
      } else {
        $("#field-modal").html($(resp));
        $('.modal').modal('show');
      }
    }
  })
  return false;
});

// Field delete handler
$(document).on('click', '.field-delete', function() {
  var $btn = $(this);
  console.log($btn);
  $.ajax({
    url: $btn.attr('href'),
    type: 'GET',
    success: function(resp) {
      $('.modal').modal('hide');
      if (resp.success) {
        removeField(currentField);
      } else {
        alert(resp.message);
      }
    }
  });
  return false;
});

// Disable content type incompatible choices
$(document).on('change', '#field-contenttypes input', function() {
  var $chk = $(this);
  if ($chk.val() in selfContentIds) {
    $('#field-contenttypes input').not($chk).prop('checked', false);
  } else {
    $('#field-contenttypes input').filter(function() {
      return $(this).val() in selfContentIds;
    }).prop('checked', false);
  }
});
