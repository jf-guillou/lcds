/** global: updateScreenUrl */

/**
 * Screen class constructor
 * @param {string} updateScreenUrl global screen update checks url
 */
function Screen(updateScreenUrl) {
  this.fields = [];
  this.url = updateScreenUrl;
  this.lastChanges = null;
  this.endAt = null;
  this.nextUrl = null;
  this.stopping = false;
  this.cache = {};
}

/**
 * Ajax GET on updateScreenUrl to check lastChanges timestamp and reload if necessary
 */
Screen.prototype.checkUpdates = function() {
  var s = this;
  $.get(this.url, function(j) {
    if (j.success) {
      if (s.lastChanges == null) {
        s.lastChanges = j.data.lastChanges;
      } else if (s.lastChanges != j.data.lastChanges) {
        s.reload();
        s.nextUrl = null;
        return;
      }

      if (j.data.duration > 0) {
        // Setup next screen
        s.reload(j.data.duration * 1000);
        s.nextUrl = j.data.nextScreenUrl;
      }
    }
  });
}

/**
 * Start Screen reload procedure, checking for every field timeout
 */
Screen.prototype.reload = function(minDuration) {
  var endAt = Date.now() + (minDuration ? minDuration : 0);
  if (this.stopping && this.endAt < endAt) {
    return;
  }

  this.endAt = minDuration ? Date.now() + minDuration : 0;
  this.stopping = true;
  for (var i in this.fields) {
    if (!this.fields.hasOwnProperty(i)) {
      continue;
    }
    var f = this.fields[i];
    if (f.timeout && f.endAt > this.endAt) {
      this.endAt = f.endAt;
    }
  }

  if (this.endAt === 0) {
    this.doReload();
  }
}

/**
 * Actual Screen reload action
 */
Screen.prototype.doReload = function() {
  if (this.nextUrl) {
    window.location = this.nextUrl;
  } else {
    window.location.reload();
  }
}

/**
 * Check every field for content
 * @param  {Content} data 
 * @return {boolean} content is displayed
 */
Screen.prototype.displaysData = function(data) {
  return this.fields.filter(function(field) {
    return field.current && field.current.data == data;
  }).length > 0;
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
  this.src = null;

  if (this.shouldPreload()) {
    this.preload();
  }
}

Content.prototype.shouldPreload = function() {
  return this.canPreload() && !this.isPreloading() && !this.isPreloaded();
}

Content.prototype.canPreload = function() {
  return this.getResource() && this.type.search(/Video|Image|Agenda/) != -1;
}

Content.prototype.getResource = function() {
  if (this.src) {
    return this.src;
  }
  var srcMatch = this.data.match(/src="([^"]+)"/);
  if (!srcMatch) {
    return false;
  }
  var src = srcMatch[1];
  if (src.indexOf('/') === 0) {
    src = window.location.origin + src;
  }
  if (src.indexOf('http') !== 0) {
    return false;
  }

  this.src = src;
  return src;
}

Content.prototype.isPreloaded = function(expires) {
  if (!this.canPreload()) {
    return true;
  }

  if (expires === undefined) {
    var cache = screen.cache[this.getResource()]
    switch (cache) {
      case undefined: // unset
      case false: // preloading
        return false;
      case true: // preloaded without expire
        return true;
      default: // check expire
        return (new Date()).valueOf() < cache;
    }
  } else if (expires === null) {
    console.log(this.getResource() + ' has no Expires header');
    screen.cache[this.getResource()] = true;
  } else if (expires) {
    var exp = new Date(expires).valueOf();
    var diff = exp - (new Date()).valueOf();
    if (diff < 10000) {
      console.log(this.getResource() + ' should\'t have Expires header, too short: ' + diff / 1000 + ' sec');
      screen.cache[this.getResource()] = true;
    } else {
      console.log(this.getResource() + ' cached for: ' + diff / 1000 + ' sec');
      screen.cache[this.getResource()] = exp + 5000;
    }
  } else {
    console.log(this.getResource() + ' has been discarded');
    delete screen.cache[this.getResource()];
  }
}

Content.prototype.isPreloading = function(state) {
  if (state === undefined) {
    return screen.cache[this.getResource()] === false;
  } else if (state && !this.isPreloading()) {
    screen.cache[this.getResource] = false;
  } else if (this.isPreloading()) {
    delete screen.cache[this.getResource()];
  }
}

Content.prototype.preload = function() {
  var src = this.getResource();
  if (!src) {
    this.isPreloaded(true);
    return;
  }
  this.isPreloading(true);

  console.log('Preloading ' + src);
  var c = this;
  $.ajax({
    method: 'GET',
    url: src,
  }).done(function(data, textStatus, jqXHR) {
    c.isPreloaded(jqXHR.getResponseHeader('Expires'));
  }).fail(function(jqXHR, textStatus, errorThrown) {
    c.isPreloaded(false); // Discard until next Content init
  });
}

/**
 * Field class constructor
 * @param {jQuery.Object} $f field object
 * @param {Screen} screen parent screen object
 */
function Field($f) {
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
  if (screen.stopping) { // Stoping screen
    if (screen.endAt < Date.now()) {
      screen.doReload();
      return;
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
    if (screen.endAt != null && c.duration + Date.now() > screen.endAt) {
      continue;
    }

    if (c.data == pData) {
      // Will repeat, avoid if enough content
      if (this.contents.length < 2) {
        this.next = c;
        break;
      }
      continue;
    }

    if (screen.displaysData(c.data)) {
      // Same content already displayed on other field, avoid if enough content
      if (this.contents.length < 3) {
        this.next = c;
        break;
      }
      continue;
    }

    // Wait for resource preload
    if (!c.isPreloaded()) {
      continue;
    }

    this.next = c;
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
    var f = this;
    this.timeout = setTimeout(function() {
      f.pickNext();
    }, 2000);
    console.error('No content to display for', this);
  }
}

/**
 * jQuery.load event
 * Initialize Screen and Fields
 * Setup updates interval timeouts
 */
var screen = null;

function onLoad() {
  screen = new Screen(updateScreenUrl);
  // Init
  $('.field').each(function() {
    var f = new Field($(this));
    f.getContents();
    screen.fields.push(f);
  });

  // Setup content updates loop
  setInterval(function() {
    for (var f in screen.fields) {
      if (screen.fields.hasOwnProperty(f)) {
        screen.fields[f].getContents();
      }
    }
    screen.checkUpdates();
  }, 60000);
  screen.checkUpdates();
}

// Run
$(onLoad);
