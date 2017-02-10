/** global: updateScreenUrl, navigator */

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
  this.cache = new Preload(navigator.userAgent.toLowerCase().indexOf('kweb') == -1);
  this.runOnce = false;
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
        // Remote screen updated, we should reload as soon as possible
        s.nextUrl = null;
        s.reloadIn(0);
        return;
      }

      if (j.data.nextScreenUrl != null) {
        // Setup next screen
        s.nextUrl = j.data.nextScreenUrl;
        if (j.data.duration > 0) {
          s.reloadIn(j.data.duration * 1000);
        } else {
          s.runOnce = true;
        }
      }
    } else if (j.message == 'Unauthorized') {
      // Cookie/session gone bad, try to refresh with full screen reload
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
    // Already going to reload sooner than asked
    return;
  }

  if (this.cache.hasPreloadingContent(true)) {
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

  this.reloadOnTimeout();
}

/**
 * Check if we're past the screen.endAt timeout and reload if necessary
 * @return {boolean} going to reload
 */
Screen.prototype.reloadOnTimeout = function() {
  if (screen.runOnce && screen.hasRanOnce() && !this.cache.hasPreloadingContent(true)) {
    // Every content has been shown, reload
    this.reloadNow();
    return true;
  }

  if (this.endAt != null && Date.now() >= this.endAt) {
    // No content to delay reload, do it now
    this.reloadNow();
    return true;
  }

  return false;
}

/**
 * Actual Screen reload/change screen action
 */
Screen.prototype.reloadNow = function() {
  if (this.nextUrl) {
    window.location = this.nextUrl;
  } else {
    window.location.reload();
  }
}

/**
 * Check every field if contents have been all played at least once
 * @return {Boolean} has every content been played
 */
Screen.prototype.hasRanOnce = function() {
  for (var f in this.fields) {
    if (!this.fields.hasOwnProperty(f)) {
      continue;
    }
    f = this.fields[f];

    for (var c in f.contents) {
      if (!f.contents.hasOwnProperty(c)) {
        continue;
      }
      c = f.contents[c];

      if (f.playCount[c.id] < 1 && c.isPreloaded()) {
        return false;
      }
    }
  }

  return true;
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
 * Trigger pickNext on all fields
 */
Screen.prototype.newContentTrigger = function() {
  for (var f in this.fields) {
    if (!this.fields.hasOwnProperty(f)) {
      continue;
    }

    this.fields[f].pickNextIfNecessary();
  }
}

/**
 * Loop through all fields for stuckiness state
 * @return {boolean} are all fields stuck
 */
Screen.prototype.isAllFieldsStuck = function() {
  for (var f in this.fields) {
    if (!this.fields.hasOwnProperty(f)) {
      continue;
    }

    if (!this.fields[f].stuck && this.fields[f].canUpdate) {
      return false;
    }
  }

  return true;
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
 * @return {boolean} shoud preload
 */
Content.prototype.shouldPreload = function() {
  return this.canPreload() && !this.isPreloadingOrQueued() && !this.isPreloaded();
}

/**
 * Check if content has pre-loadable material
 * @return {boolean} can preload
 */
Content.prototype.canPreload = function() {
  return this.getResource() && this.type.search(/Video|Image/) != -1;
}

/**
 * Check if content is displayable (preloaded and not too long)
 * @return {boolean} can display
 */
Content.prototype.canDisplay = function() {
  return (screen.endAt == null || Date.now() + this.duration < screen.endAt) && this.isPreloaded() && this.data;
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
    // All preloadable content comes with a src attribute
    return false;
  }
  var src = srcMatch[1];
  if (src.indexOf('/') === 0) {
    src = window.location.origin + src;
  }
  if (src.indexOf('http') !== 0) {
    return false;
  }
  // Get rid of fragment
  src = src.replace(/#.*/g, '');

  this.src = src;
  return src;
}

/**
 * Set content cache status
 * @param {string} state preload state
 */
Content.prototype.setPreloadState = function(state) {
  screen.cache.setState(this.getResource(), state);
}

/**
 * Check cache for preload status of content
 * @return {boolean} is preloaded
 */
Content.prototype.isPreloaded = function() {
  if (!this.canPreload()) {
    return true;
  }

  return screen.cache.isPreloaded(this.getResource());
}

/**
 * Check cache for in progress or future preloading
 * @return {boolean} is preloading
 */
Content.prototype.isPreloadingOrQueued = function() {
  return this.isPreloading() || this.isInPreloadQueue();
}

/**
 * Check cache for in progress preloading
 * @return {boolean} is preloading
 */
Content.prototype.isPreloading = function() {
  return screen.cache.isPreloading(this.getResource());
}

/**
 * Check cache for queued preloading
 * @return {boolean} is in preload queue
 */
Content.prototype.isInPreloadQueue = function() {
  return screen.cache.isInPreloadQueue(this.getResource());
}

/**
 * Call to preload content
 */
Content.prototype.preload = function() {
  var src = this.getResource();
  if (!src) {
    return;
  }

  screen.cache.preload(src);
}

/**
 * Preload content or add to preload queue
 */
Content.prototype.queuePreload = function() {
  var src = this.getResource();
  if (!src) {
    return;
  }

  if (screen.cache.hasPreloadingContent(false)) {
    this.setPreloadState(Preload.state.PRELOADING_QUEUE);
  } else {
    this.preload();
  }
}


/**
 * Preload class constructor
 * Build cache map
 * @param {boolean} isModernBrowser does browser support modern tags
 */
function Preload(isModernBrowser) {
  this.cache = {};
  if (isModernBrowser) {
    this.preload = this.preloadPrefetch;
  } else {
    this.preload = this.preloadExternal;
  }
}

/**
 * Preload states
 */
Preload.state = {
  ERR: -1,
  WAIT_PRELOADER: 1,
  PRELOADING: 2,
  PRELOADING_QUEUE: 3,
  OK: 4,
  NO_CONTENT: 5,
  HTTP_FAIL: 6,
}

/**
 * Set resource cache state
 * @param {string} res   resource url
 * @param {int}    state preload state
 */
Preload.prototype.setState = function(res, state) {
  this.cache[res] = state;
}

/**
 * Check resource cache for readyness state
 * @param  {string}  res resource url
 * @return {boolean}     is preloaded
 */
Preload.prototype.isPreloaded = function(res) {
  return this.cache[res] === Preload.state.OK;
}

/**
 * Check resource cache for preloading state
 * @param  {string}  res resource url
 * @return {boolean}     is currently preloading
 */
Preload.prototype.isPreloading = function(res) {
  return this.cache[res] === Preload.state.PRELOADING;
}

/**
 * Check resource cache for queued preloading state
 * @param  {string}  res resource url
 * @return {boolean}     is in preload queue
 */
Preload.prototype.isInPreloadQueue = function(res) {
  return this.cache[res] === Preload.state.PRELOADING_QUEUE;
}

/**
 * Check resource cache for queued preloading state during preloader pick phase
 * @param  {string}  res resource url
 * @return {boolean}     is in preload queue
 */
Preload.prototype.isWaiting = function(res) {
  return this.cache[res] === Preload.state.WAIT_PRELOADER;
}

/**
 * Scan resource cache for preloading resources
 * @param  {boolean} withQueue also check preload queue
 * @return {boolean}           has any resource preloading/in preload queue
 */
Preload.prototype.hasPreloadingContent = function(withQueue) {
  for (var res in this.cache) {
    if (!this.cache.hasOwnProperty(res)) {
      continue;
    }

    if (this.isPreloading(res) || this.isWaiting(res) || (withQueue && this.isInPreloadQueue(res))) {
      return true;
    }
  }

  return false;
}

/**
 * Preload a resource
 * Default implementation waits for preloader pick
 * @param {string} res resource url
 */
Preload.prototype.preload = function(res) {
  this.setState(res, Preload.state.WAIT_PRELOADER);
}

/**
 * Triggered on preloader picked
 * Restarts preload queue processing
 */
Preload.prototype.preloaderReady = function() {
  for (var res in this.cache) {
    if (!this.cache.hasOwnProperty(res)) {
      continue;
    }

    if (this.isWaiting(res)) {
      this.preload(res);
    }
  }
}

/**
 * Preload a resource by calling external preloader
 * @param {string} res resource url
 */
Preload.prototype.preloadExternal = function(res) {
  this.setState(res, Preload.state.PRELOADING);
  $.ajax("http://127.0.0.1:8089/pf?res=" + res).done(function(j) {
    switch (j.state) {
      case Preload.state.OK:
      case Preload.state.NO_CONTENT:
        screen.cache.setState(res, j.state);
        screen.newContentTrigger();
        break;
      case Preload.state.HTTP_FAIL:
        screen.cache.setState(res, j.state);
        break;
      case Preload.state.ERR:
        screen.cache.setState(res, Preload.state.HTTP_FAIL);
        break;
      default:
        return;
    }
    screen.cache.preloadNext();
  }).fail(function() {
    screen.cache.preload = screen.cache.preloadAjax;
    screen.cache.preload(res);
  });
}

/**
 * Preload a resource by addinh a <link rel="prefetch"> tag
 * @param {string} res resource url
 */
Preload.prototype.preloadPrefetch = function(res) {
  this.setState(res, Preload.state.PRELOADING);
  $('body').append(
    $('<link>', {
      rel: 'prefetch',
      href: res
    }).load(function() {
      screen.cache.setState(res, Preload.state.OK);
      screen.newContentTrigger();
      screen.cache.preloadNext();
    }).error(function() {
      screen.cache.setState(res, Preload.state.HTTP_FAIL);
      screen.cache.preloadNext();
    })
  );
}

/**
 * Preload a resource by ajax get on the url
 * Check HTTP return state to validate proper cache
 * @param {string} res resource url
 */
Preload.prototype.preloadAjax = function(res) {
  this.setState(res, Preload.state.PRELOADING);
  $.ajax(res).done(function(data) {
    // Preload success
    if (data === '') {
      screen.cache.setState(res, Preload.state.NO_CONTENT);
    } else {
      screen.cache.setState(res, Preload.state.OK);
    }
    screen.newContentTrigger();
  }).fail(function() {
    // Preload failure
    screen.cache.setState(res, Preload.state.HTTP_FAIL);
  }).always(function() {
    screen.cache.preloadNext();
  });
}

/**
 * Try to preload next resource or trigger preload end event
 */
Preload.prototype.preloadNext = function() {
  var res = screen.cache.next();
  if (res) {
    // Preload ended, next resource
    screen.cache.preload(res);
    return;
  }
  // We've gone through all queued resources
  // Check if we should reload early
  if (screen.reloadOnTimeout()) {
    return;
  }
  // Trigger another update to calculate a proper screen.endAt value
  screen.checkUpdates();
}

/**
 * Get next resource to preload from queue
 * @return {string|null} next resource url
 */
Preload.prototype.next = function() {
  for (var res in this.cache) {
    if (!this.cache.hasOwnProperty(res)) {
      continue;
    }

    if (this.isInPreloadQueue(res)) {
      return res;
    }
  }
  return null;
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
  this.playCount = {};
  this.previous = null;
  this.current = null;
  this.next = null;
  this.timeout = null;
  this.endAt = null;
  this.stuck = false;
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
        if (!f.playCount[c.id]) {
          f.playCount[c.id] = 0;
        }
        return new Content(c);
      });
      f.pickNextIfNecessary();
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
 * PickNext if no content currently displayed and content is available
 */
Field.prototype.pickNextIfNecessary = function() {
  if (!this.timeout) {
    this.pickNext();
  }
}

/**
 * Loop through field contents to pick next displayable content
 */
Field.prototype.pickNext = function() {
  // Keep track of true previous content
  if (this.current != null) {
    this.previous = this.current;
    this.playCount[this.current.id]++;
  }

  if (screen.reloadOnTimeout()) {
    // Currently trying to reload, we're past threshold: reload now
    return;
  }

  this.current = null;
  var previousData = this.previous && this.previous.data;

  this.next = this.pickRandomContent(previousData) || this.pickRandomContent(previousData, true);

  if (this.next) {
    // Overwrite field with newly picked content
    this.displayNext();
    this.stuck = false;
  } else {
    // I am stuck, don't know what to display
    this.stuck = true;
    // Check other fields for stuckiness state
    if (screen.isAllFieldsStuck() && !screen.cache.hasPreloadingContent(true)) {
      // Nothing to do. Give up, reload now
      screen.reloadNow();
    }
  }
}

/**
 * Loop through field contents for any displayable content
 * @param  {string}  previousData previous content data
 * @param  {boolean} anyUsable    ignore constraints
 * @return {Content}              random usable content
 */
Field.prototype.pickRandomContent = function(previousData, anyUsable) {
  this.randomizeSortContents();
  for (var i = 0; i < this.contents.length; i++) {
    var c = this.contents[i];
    // Skip too long, not preloaded or empty content
    if (!c.canDisplay()) {
      continue;
    }

    if (anyUsable) {
      // Ignore repeat & same content constraints if necessary
      return c;
    }

    // Avoid repeat same content
    if (c.data == previousData) {
      // Not enough content, display anyway
      if (this.contents.length < 2) {
        return c;
      }
      continue;
    }

    // Avoid same content than already displayed on other fields
    if (screen.displaysData(c.data)) {
      // Not enough content, display anyway
      if (this.contents.length < 3) {
        return c;
      }
      continue;
    }

    // Nice content. Display it.
    return c;
  }
  return null;
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
 * @param {string} data
 */
Field.prototype.display = function(data) {
  this.$field.html(data);
  this.$field.show();
  var $bt = this.$field.find('.bigtext');
  // Only first data-min/max per field is respected
  var minPx = $bt.attr('data-min-px') || 4;
  var maxPx = $bt.attr('data-max-px') || 0;
  $bt.parent().textfill({
    minFontPixels: minPx,
    maxFontPixels: maxPx,
  });
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
    screen.fields.push(f);
    f.fetchContents();
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
    }, 60000); // 1 minute is enough alongside preload queue end trigger
    screen.checkUpdates();
  }
}

// Run
$(onLoad);
