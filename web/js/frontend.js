/**
 * Screen class constructor
 * @param {string} updateScreenUrl global screen update checks url
 */
function Screen(updateScreenUrl) {
  this.fields = [];
  this.url = updateScreenUrl;
  this.lastChanges = null;
  this.remaining = 0;
}

/**
 * Ajax GET on updateScreenUrl to check lastChanges timestamp and reload if necessary
 */
Screen.prototype.checkUpdates = function() {
  var s = this;
  $.get(this.url, function(j) {
    if (j.success) {
      if (s.lastChanges == null) {
        s.lastChanges = j.data;
      } else if (s.lastChanges != j.data) {
        s.reload();
      }
    }
  });
}

/**
 * Start Screen reload procedure, checking for every field timeout
 */
Screen.prototype.reload = function() {
  if (this.stopping) {
    return;
  }

  this.stopping = true;
  for (var f in this.fields) {
    if (this.fields[f].timeout) {
      this.remaining++;
    }
  }
}

/**
 * Actual Screen reload action
 */
Screen.prototype.doReload = function() {
  window.location.reload();
}

/**
 * Field class constructor
 * @param {jQuery.Object} $f     field object
 * @param {Screen} screen parent screen object
 */
function Field($f, screen) {
  this.$field = $f;
  this.id = $f.attr('data-id');
  this.url = $f.attr('data-url');
  this.types = $f.attr('data-types').split(' ');
  this.canUpdate = this.url != null;
  this.contents = [];
  this.previous = null;
  this.current = null;
  this.next = null;
  this.timeout = null;
  this.screen = screen;
}

/**
 * Retrieves contents from backend for this field
 */
Field.prototype.getContents = function() {
  if (!this.canUpdate) {
    return;
  }

  var f = this;
  $.get(this.url, function(j) {
    if (j.success) {
      f.contents = j.next;
      if (!f.timeout && f.contents.length) {
        f.pickNext();
      }
    } else {
      f.setError(j.message || 'Error');
    }
  });
}

/**
 * Display error in field text
 */
Field.prototype.setError = function(err) {
  this.$field.text(err);
}

/**
 * Loop through field contents to pick next displayable content
 */
Field.prototype.pickNext = function() {
  if (this.screen.stopping) { // Stoping screen
    if (--this.screen.remaining <= 0) {
      return this.screen.doReload();
    }
    return;
  }
  this.previous = this.current;
  this.current = null;
  var pData = this.previous && this.previous.data;
  // Avoid repeat & other field same content
  while (true) {
    var c = this.contents[Math.floor(Math.random() * this.contents.length)];
    if (c.data == pData) {
      // Will repeat, avoid if enough content
      if (this.contents.length < 2) {
        this.next = c;
        break;
      }
    } else if (this.screen.fields.filter(function(field) {
        return field.current && field.current.data == c.data;
      }).length) {
      // Same content already displayed on other field, avoid if enough content
      if (this.contents.length < 3) {
        this.next = c;
        break;
      }
    } else {
      this.next = c;
      break;
    }
  }

  if (!this.next && this.contents.length > 0) {
    this.next = this.contents[0];
  }
  this.display();
}

/**
 * Display next content in field html
 */
Field.prototype.display = function() {
  if (this.next && this.next.duration > 0) {
    this.current = this.next
    this.next = null;
    this.$field.html(this.current.data);
    this.$field.show();
    if (this.$field.text() != '') {
      this.$field.textfill({
        maxFontPixels: 0,
      });
    }
    if (this.timeout) {
      clearTimeout(this.timeout);
    }
    var f = this;
    this.timeout = setTimeout(function() {
      f.pickNext();
    }, this.current.duration * 1000);
  } else {
    this.timeout = null;
    console.error('Cannot set content', this);
  }
}

/**
 * jQuery.load event
 * Initialize Screen and Fields
 * Setup updates interval timeouts
 */
function onLoad() {
  var screen = new Screen(updateScreenUrl);
  // Init
  $('.field').each(function() {
    var f = new Field($(this), screen);
    f.getContents();
    screen.fields.push(f);
  });

  // Setup content updates loop
  setInterval(function() {
    for (var f in screen.fields) {
      screen.fields[f].getContents();
    }
    screen.checkUpdates();
  }, 60000);
  screen.checkUpdates();
}

// Run
$(onLoad);
