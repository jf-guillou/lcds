function updateContents(f) {
  if (!f.canUpdate) {
    return;
  }
  console.log('Fetch updated contents', f);
  $.get(f.url, function(j) {
    if (j.success) {
      setContents(f, j.next);
    } else {
      f.$field.text('error');
    }
  });
}

var updateScreenUrl;
var lastChanges = null;
function updateScreen() {
  $.get(updateScreenUrl, function(j) {
    if (j.success) {
      console.log(j.last_changes);
      if (lastChanges == null) {
        lastChanges = j.last_changes;
      } else if (lastChanges != j.last_changes) {
        window.location.reload();
      }
    }
  });
}

function onLoad() {
  // Init
  console.log('onLoad');
  $('.field').each(function() {
    var $f = $(this);
    var f = {
      $field: $f,
      id: $f.attr('data-id'),
      url: $f.attr('data-url'),
      types: $f.attr('data-types').split(' '),
      canUpdate: false,
      previous: null,
      current: null,
      next: null,
      timeout: null,
    };

    f.canUpdate = f.url != null;
    fields.push(f);
    updateContents(f);
  });

  // Setup content updates loop
  setInterval(function() {
    for (var f in fields) {
      updateContents(fields[f]);
    }
    updateScreen();
  }, 60000);
}

function setContents(f, contents) {
  f.contents = contents;
  console.log('Set updated contents for', f);
  if (!f.timeout && contents.length) {
    next(f);
  }
}

function next(f) {
  console.log('Find what to display for', f);
  f.previous = f.current;
  f.current = null;
  var pData = f.previous && f.previous.data;
  // Avoid repeat & other field same content
  for (content in f.contents) {
    var c = f.contents[content];
    if (c.data == pData) {
      // repeat : skip if possible
      console.log('Repeat, skip if possible');
      if (f.contents.length < 2) {
        console.log('Cannot dodge repeat, not enough content');
        f.next = c;
        break;
      }
    } else if (fields.filter(function(field) {
        return field.current && field.current.data == c.data;
      }).length) {
      // same content : skip if possible
      console.log('Same content in other field, skip if possible');
      if (f.contents.length < 3) {
        console.log('Cannot dodge duplicate, not enough content');
        f.next = c;
        break;
      }
    } else {
      f.next = c;
      break;
    }
  }

  if (!f.next && f.contents.length > 0) {
    f.next = f.contents[0];
  }
  updateFieldContent(f);
}

function updateFieldContent(f) {
  if (f.next && f.next.duration > 0) {
    console.log('Display new stuff', f);
    f.current = f.next
    f.next = null;
    f.$field.html(f.current.data);
    f.$field.show();
    if (f.$field.text() != '') {
      f.$field.bigtext();
    }
    if (f.timeout) {
      clearTimeout(f.timeout);
    }
    f.timeout = setTimeout(function() {
      next(f);
    }, f.current.duration * 1000);
  } else {
    f.timeout = null;
    console.error('Cannot set content', f);
  }
}

var fields = [];
$(onLoad);
