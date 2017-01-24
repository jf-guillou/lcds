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
        // Remote screen updated, we should reload
        s.reloadIn(0);
        s.nextUrl = null;
        return;
      }

      if (j.data.duration > 0) {
        // Setup next screen
        s.reloadIn(j.data.duration * 1000);
        s.nextUrl = j.data.nextScreenUrl;
      }
    } else if (j.message == 'Unauthorized') {
      screen.reloadIn(0);
    }
  });
}

/**
 * Start Screen reload procedure, checking for every field timeout
 */
Screen.prototype.reloadIn = function(minDuration) {
  var endAt = Date.now() + minDuration;
  if (this.endAt != null && this.endAt < endAt) {
    return;
  }

  if (this.hasPreloadingContent()) {
    // Do not break preloading
    return;
  }

  this.endAt = Date.now() + minDuration;
  for (var i in this.fields) {
    if (!this.fields.hasOwnProperty(i)) {
      continue;
    }
    var f = this.fields[i];
    if (f.timeout && f.endAt > this.endAt) {
      // Always wait for content display end
      this.endAt = f.endAt;
    }
  }

  if (Date.now() >= this.endAt) {
    this.reloadNow();
  }
}

/**
 * Actual Screen reload action
 */
Screen.prototype.reloadNow = function() {
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
 * Search in cache for current preloading content
 * @return {Boolean} has preloading content
 */
Screen.prototype.hasPreloadingContent = function() {
  for (var f in this.fields) {
    if (!this.fields.hasOwnProperty(f)) {
      continue
    }
    for (var c in this.fields[f].contents) {
      if (!this.fields[f].contents.hasOwnProperty(c)) {
        continue
      }
      if (this.fields[f].contents[c].isPreloading()) {
        return true;
      }
    }
  }

  return false;
}

/**
 * Pick next content to preload from cache queue
 */
Screen.prototype.nextPreloadQueue = function() {
  for (var f in this.fields) {
    if (!this.fields.hasOwnProperty(f)) {
      continue
    }
    for (var c in this.fields[f].contents) {
      if (!this.fields[f].contents.hasOwnProperty(c)) {
        continue
      }
      if (this.fields[f].contents[c].isInPreloadQueue()) {
        this.fields[f].contents[c].preload();
      }
    }
  }
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
  this.src = null;

  if (this.shouldPreload()) {
    this.queuePreload();
  }
}

/**
 * Check if content should be ajax preloaded
 * @return {boolean}
 */
Content.prototype.shouldPreload = function() {
  return this.canPreload() && !this.isPreloading() && !this.isPreloaded();
}

/**
 * Check if content has pre-loadable material
 * @return {boolean} 
 */
Content.prototype.canPreload = function() {
  return this.getResource() && this.type.search(/Video|Image|Agenda/) != -1;
}

/**
 * Check if content is displayable (preloaded and not too long)
 * @return {Boolean} can display
 */
Content.prototype.canDisplay = function() {
  return (screen.endAt == null || Date.now() + this.duration < screen.endAt) && this.isPreloaded();
}

/**
 * Extract url from contant data
 * @return {string} resource url
 */
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
  src = src.replace(/#.*/g, '');

  this.src = src;
  return src;
}

/**
 * Set content cache status
 * @param {string} expires header
 */
Content.prototype.setPreloadState = function(expires) {
  if (expires === null || expires == '') {
    expires = Preload.state.NO_EXPIRE_HEADER;
  }

  screen.cache[this.getResource()] = expires < -1 ? expires : Preload.state.OK
}

/**
 * Check cache for preload status of content
 * @return {Boolean} 
 */
Content.prototype.isPreloaded = function() {
  if (!this.canPreload()) {
    return true;
  }

  var state = screen.cache[this.getResource()];

  return state === Preload.state.OK || state === Preload.state.NO_EXPIRE_HEADER;
}

/**
 * Check cache for in progress preloading
 * @return {Boolean} is preloading
 */
Content.prototype.isPreloading = function() {
  return screen.cache[this.getResource()] === Preload.state.PRELOADING;
}

/**
 * Check cache for queued preloading
 * @return {Boolean} is in preload queue
 */
Content.prototype.isInPreloadQueue = function() {
  return screen.cache[this.getResource()] === Preload.state.PRELOADING_QUEUE;
}

/**
 * Ajax call to preload content
 */
Content.prototype.preload = function() {
  var src = this.getResource();
  if (!src) {
    return;
  }

  this.setPreloadState(Preload.state.PRELOADING);

  var c = this;
  $.ajax(src).done(function(data, textStatus, jqXHR) {
    c.setPreloadState(jqXHR.getResponseHeader('Expires'));
  }).fail(function() {
    c.setPreloadState(Preload.state.HTTP_FAIL);
  }).always(function() {
    screen.nextPreloadQueue();
  });
}

/**
 * Preload content or add to preload queue
 */
Content.prototype.queuePreload = function() {
  var src = this.getResource();
  if (!src) {
    return;
  }

  if (screen.hasPreloadingContent()) {
    this.setPreloadState(Preload.state.PRELOADING_QUEUE);
  } else {
    this.preload();
  }
}


/**
 * Preload class constructor
 * Mostly used to store constants
 */
function Preload() {}

/**
 * Preload states
 */
Preload.state = {
  PRELOADING: -2,
  PRELOADING_QUEUE: -3,
  HTTP_FAIL: -4,
  NO_EXPIRE_HEADER: -5,
  OK: -6,
}


/**
 * Field class constructor
 * @param {jQuery.Object} $f field object
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
Field.prototype.fetchContents = function() {
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
  this.display(err);
}

/**
 * Randomize order
 */
Field.prototype.randomizeSortContents = function() {
  this.contents = this.contents.sort(function() {
    return Math.random() - 0.5;
  });
}

/**
 * Loop through field contents to pick next displayable content
 */
Field.prototype.pickNext = function() {
  if (screen.endAt != null && Date.now() >= screen.endAt) { // Stoping screen
    screen.reloadNow();
    return;
  }

  var f = this;
  this.previous = this.current;
  this.current = null;
  var pData = this.previous && this.previous.data;
  // Avoid repeat & other field same content
  this.randomizeSortContents();
  for (var i = 0; i < this.contents.length; i++) {
    var c = this.contents[i];
    // Skip too long or not preloaded content 
    if (!c.canDisplay()) {
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

    this.next = c;
    break
  }

  if (this.next) {
    this.displayNext();
  } else {
    setTimeout(function() {
      f.pickNext();
    }, 600);
  }
}

/**
 * Setup next content for field and display it
 */
Field.prototype.displayNext = function() {
  var f = this;
  if (this.next && this.next.duration > 0) {
    this.current = this.next
    this.next = null;
    this.display(this.current.data);
    if (this.timeout) {
      clearTimeout(this.timeout);
    }
    this.endAt = Date.now() + this.current.duration;
    this.timeout = setTimeout(function() {
      f.pickNext();
    }, this.current.duration);
  }
}

/**
 * Display data in field HTML
 * @param  {string} data 
 */
Field.prototype.display = function(data) {
  this.$field.html(data);
  this.$field.show();
  if (this.$field.text() != '') {
    this.$field.textfill({
      maxFontPixels: 0,
    });
  }
}

// Global screen instance
var screen = null;

/**
 * jQuery.load event
 * Initialize Screen and Fields
 * Setup updates interval timeouts
 */
function onLoad() {
  screen = new Screen(updateScreenUrl);
  // Init
  $('.field').each(function() {
    var f = new Field($(this));
    f.fetchContents();
    screen.fields.push(f);
  });

  if (screen.url) {
    // Setup content updates loop
    setInterval(function() {
      for (var f in screen.fields) {
        if (screen.fields.hasOwnProperty(f)) {
          screen.fields[f].fetchContents();
        }
      }
      screen.checkUpdates();
    }, 30000);
    screen.checkUpdates();
  }
}

// Run
$(onLoad);
