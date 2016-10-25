/**
 * Screen class constructor
 * @param {string} updateScreenUrl global screen update checks url
 */
function Screen(updateScreenUrl) {
  this.fields = [];
  this.url = updateScreenUrl;
  this.lastChanges = null;
  this.endAt = null;
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
  for (var i in this.fields) {
    var f = this.fields[i];
    if (f.timeout && (this.endAt == null || f.endAt > this.endAt)) {
      this.endAt = f.endAt;
    }
  }

  if (this.endAt != null) {
    console.log('Screen will reload in', this.endAt - Date.now(), 'ms');
  } else {
    this.doReload();
  }
}

/**
 * Actual Screen reload action
 */
Screen.prototype.doReload = function() {
  window.location.reload();
}

/**
 * Content class constructor
 * @param {array} c content attributes
 */
function Content(c) {
  this.id = c.id;
  this.data = c.data;
  this.duration = c.duration * 1000;
  this.type = c.type;
  this.displayCount = 0;
}

/**
 * Field class constructor
 * @param {jQuery.Object} $f field object
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
  this.endAt = null;
  this.screen = screen;
}

/**
 * Retrieves contents from backend for this field
 */
Field.prototype.getContents = function() {
  if (!this.canUpdate || this.screen.stopping) {
    return;
  }

  var f = this;
  $.get(this.url, function(j) {
    if (j.success) {
      f.contents = j.next.map(function(c) {
        return new Content(c);
      });
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
 * Sort by displayCount and randomize order when equal displayCount
 */
Field.prototype.randomizeSortContents = function() {
  this.contents = this.contents.sort(function(a, b) {
    if (a.displayCount === b.displayCount) {
      return Math.random() - 0.5;
    }
    return a.displayCount - b.displayCount;
  });
}

/**
 * Loop through field contents to pick next displayable content
 */
Field.prototype.pickNext = function() {
  if (this.screen.stopping) { // Stoping screen
    if (this.screen.endAt < Date.now()) {
      return this.screen.doReload();
    }
  }

  this.previous = this.current;
  this.current = null;
  var pData = this.previous && this.previous.data;
  // Avoid repeat & other field same content
  this.randomizeSortContents();
  for (var i = 0; i < this.contents.length; i++) {
    var c = this.contents[i];
    // Skip too long content
    if (this.screen.endAt != null && c.duration + Date.now() > this.screen.endAt) {
      continue;
    }

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

  this.display();
}

/**
 * Display next content in field html
 */
Field.prototype.display = function() {
  if (this.next && this.next.duration > 0) {
    this.current = this.next
    this.current.displayCount++;
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
    }, this.current.duration);
    this.endAt = this.current.duration + Date.now()
  } else {
    this.timeout = null;
    console.error('No content to display for', this);
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
