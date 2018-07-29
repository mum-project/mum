webpackJsonp([1],[
/* 0 */,
/* 1 */,
/* 2 */,
/* 3 */
/***/ (function(module, exports) {

/* globals __VUE_SSR_CONTEXT__ */

// IMPORTANT: Do NOT use ES2015 features in this file.
// This module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle.

module.exports = function normalizeComponent (
  rawScriptExports,
  compiledTemplate,
  functionalTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier /* server only */
) {
  var esModule
  var scriptExports = rawScriptExports = rawScriptExports || {}

  // ES6 modules interop
  var type = typeof rawScriptExports.default
  if (type === 'object' || type === 'function') {
    esModule = rawScriptExports
    scriptExports = rawScriptExports.default
  }

  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (compiledTemplate) {
    options.render = compiledTemplate.render
    options.staticRenderFns = compiledTemplate.staticRenderFns
    options._compiled = true
  }

  // functional template
  if (functionalTemplate) {
    options.functional = true
  }

  // scopedId
  if (scopeId) {
    options._scopeId = scopeId
  }

  var hook
  if (moduleIdentifier) { // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = injectStyles
  }

  if (hook) {
    var functional = options.functional
    var existing = functional
      ? options.render
      : options.beforeCreate

    if (!functional) {
      // inject component registration as beforeCreate hook
      options.beforeCreate = existing
        ? [].concat(existing, hook)
        : [hook]
    } else {
      // for template-only hot-reload because in that case the render fn doesn't
      // go through the normalizer
      options._injectStyles = hook
      // register for functioal component in vue file
      options.render = function renderWithStyleInjection (h, context) {
        hook.call(context)
        return existing(h, context)
      }
    }
  }

  return {
    esModule: esModule,
    exports: scriptExports,
    options: options
  }
}


/***/ }),
/* 4 */,
/* 5 */,
/* 6 */,
/* 7 */,
/* 8 */,
/* 9 */,
/* 10 */
/***/ (function(module, exports) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
module.exports = function(useSourceMap) {
	var list = [];

	// return the list of modules as css string
	list.toString = function toString() {
		return this.map(function (item) {
			var content = cssWithMappingToString(item, useSourceMap);
			if(item[2]) {
				return "@media " + item[2] + "{" + content + "}";
			} else {
				return content;
			}
		}).join("");
	};

	// import a list of modules into the list
	list.i = function(modules, mediaQuery) {
		if(typeof modules === "string")
			modules = [[null, modules, ""]];
		var alreadyImportedModules = {};
		for(var i = 0; i < this.length; i++) {
			var id = this[i][0];
			if(typeof id === "number")
				alreadyImportedModules[id] = true;
		}
		for(i = 0; i < modules.length; i++) {
			var item = modules[i];
			// skip already imported module
			// this implementation is not 100% perfect for weird media query combinations
			//  when a module is imported multiple times with different media queries.
			//  I hope this will never occur (Hey this way we have smaller bundles)
			if(typeof item[0] !== "number" || !alreadyImportedModules[item[0]]) {
				if(mediaQuery && !item[2]) {
					item[2] = mediaQuery;
				} else if(mediaQuery) {
					item[2] = "(" + item[2] + ") and (" + mediaQuery + ")";
				}
				list.push(item);
			}
		}
	};
	return list;
};

function cssWithMappingToString(item, useSourceMap) {
	var content = item[1] || '';
	var cssMapping = item[3];
	if (!cssMapping) {
		return content;
	}

	if (useSourceMap && typeof btoa === 'function') {
		var sourceMapping = toComment(cssMapping);
		var sourceURLs = cssMapping.sources.map(function (source) {
			return '/*# sourceURL=' + cssMapping.sourceRoot + source + ' */'
		});

		return [content].concat(sourceURLs).concat([sourceMapping]).join('\n');
	}

	return [content].join('\n');
}

// Adapted from convert-source-map (MIT)
function toComment(sourceMap) {
	// eslint-disable-next-line no-undef
	var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap))));
	var data = 'sourceMappingURL=data:application/json;charset=utf-8;base64,' + base64;

	return '/*# ' + data + ' */';
}


/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
  Modified by Evan You @yyx990803
*/

var hasDocument = typeof document !== 'undefined'

if (typeof DEBUG !== 'undefined' && DEBUG) {
  if (!hasDocument) {
    throw new Error(
    'vue-style-loader cannot be used in a non-browser environment. ' +
    "Use { target: 'node' } in your Webpack config to indicate a server-rendering environment."
  ) }
}

var listToStyles = __webpack_require__(192)

/*
type StyleObject = {
  id: number;
  parts: Array<StyleObjectPart>
}

type StyleObjectPart = {
  css: string;
  media: string;
  sourceMap: ?string
}
*/

var stylesInDom = {/*
  [id: number]: {
    id: number,
    refs: number,
    parts: Array<(obj?: StyleObjectPart) => void>
  }
*/}

var head = hasDocument && (document.head || document.getElementsByTagName('head')[0])
var singletonElement = null
var singletonCounter = 0
var isProduction = false
var noop = function () {}
var options = null
var ssrIdKey = 'data-vue-ssr-id'

// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
// tags it will allow on a page
var isOldIE = typeof navigator !== 'undefined' && /msie [6-9]\b/.test(navigator.userAgent.toLowerCase())

module.exports = function (parentId, list, _isProduction, _options) {
  isProduction = _isProduction

  options = _options || {}

  var styles = listToStyles(parentId, list)
  addStylesToDom(styles)

  return function update (newList) {
    var mayRemove = []
    for (var i = 0; i < styles.length; i++) {
      var item = styles[i]
      var domStyle = stylesInDom[item.id]
      domStyle.refs--
      mayRemove.push(domStyle)
    }
    if (newList) {
      styles = listToStyles(parentId, newList)
      addStylesToDom(styles)
    } else {
      styles = []
    }
    for (var i = 0; i < mayRemove.length; i++) {
      var domStyle = mayRemove[i]
      if (domStyle.refs === 0) {
        for (var j = 0; j < domStyle.parts.length; j++) {
          domStyle.parts[j]()
        }
        delete stylesInDom[domStyle.id]
      }
    }
  }
}

function addStylesToDom (styles /* Array<StyleObject> */) {
  for (var i = 0; i < styles.length; i++) {
    var item = styles[i]
    var domStyle = stylesInDom[item.id]
    if (domStyle) {
      domStyle.refs++
      for (var j = 0; j < domStyle.parts.length; j++) {
        domStyle.parts[j](item.parts[j])
      }
      for (; j < item.parts.length; j++) {
        domStyle.parts.push(addStyle(item.parts[j]))
      }
      if (domStyle.parts.length > item.parts.length) {
        domStyle.parts.length = item.parts.length
      }
    } else {
      var parts = []
      for (var j = 0; j < item.parts.length; j++) {
        parts.push(addStyle(item.parts[j]))
      }
      stylesInDom[item.id] = { id: item.id, refs: 1, parts: parts }
    }
  }
}

function createStyleElement () {
  var styleElement = document.createElement('style')
  styleElement.type = 'text/css'
  head.appendChild(styleElement)
  return styleElement
}

function addStyle (obj /* StyleObjectPart */) {
  var update, remove
  var styleElement = document.querySelector('style[' + ssrIdKey + '~="' + obj.id + '"]')

  if (styleElement) {
    if (isProduction) {
      // has SSR styles and in production mode.
      // simply do nothing.
      return noop
    } else {
      // has SSR styles but in dev mode.
      // for some reason Chrome can't handle source map in server-rendered
      // style tags - source maps in <style> only works if the style tag is
      // created and inserted dynamically. So we remove the server rendered
      // styles and inject new ones.
      styleElement.parentNode.removeChild(styleElement)
    }
  }

  if (isOldIE) {
    // use singleton mode for IE9.
    var styleIndex = singletonCounter++
    styleElement = singletonElement || (singletonElement = createStyleElement())
    update = applyToSingletonTag.bind(null, styleElement, styleIndex, false)
    remove = applyToSingletonTag.bind(null, styleElement, styleIndex, true)
  } else {
    // use multi-style-tag mode in all other cases
    styleElement = createStyleElement()
    update = applyToTag.bind(null, styleElement)
    remove = function () {
      styleElement.parentNode.removeChild(styleElement)
    }
  }

  update(obj)

  return function updateStyle (newObj /* StyleObjectPart */) {
    if (newObj) {
      if (newObj.css === obj.css &&
          newObj.media === obj.media &&
          newObj.sourceMap === obj.sourceMap) {
        return
      }
      update(obj = newObj)
    } else {
      remove()
    }
  }
}

var replaceText = (function () {
  var textStore = []

  return function (index, replacement) {
    textStore[index] = replacement
    return textStore.filter(Boolean).join('\n')
  }
})()

function applyToSingletonTag (styleElement, index, remove, obj) {
  var css = remove ? '' : obj.css

  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = replaceText(index, css)
  } else {
    var cssNode = document.createTextNode(css)
    var childNodes = styleElement.childNodes
    if (childNodes[index]) styleElement.removeChild(childNodes[index])
    if (childNodes.length) {
      styleElement.insertBefore(cssNode, childNodes[index])
    } else {
      styleElement.appendChild(cssNode)
    }
  }
}

function applyToTag (styleElement, obj) {
  var css = obj.css
  var media = obj.media
  var sourceMap = obj.sourceMap

  if (media) {
    styleElement.setAttribute('media', media)
  }
  if (options.ssrId) {
    styleElement.setAttribute(ssrIdKey, obj.id)
  }

  if (sourceMap) {
    // https://developer.chrome.com/devtools/docs/javascript-debugging
    // this makes source maps inside style tags work properly in Chrome
    css += '\n/*# sourceURL=' + sourceMap.sources[0] + ' */'
    // http://stackoverflow.com/a/26603875
    css += '\n/*# sourceMappingURL=data:application/json;base64,' + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + ' */'
  }

  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = css
  } else {
    while (styleElement.firstChild) {
      styleElement.removeChild(styleElement.firstChild)
    }
    styleElement.appendChild(document.createTextNode(css))
  }
}


/***/ }),
/* 12 */,
/* 13 */,
/* 14 */,
/* 15 */,
/* 16 */,
/* 17 */,
/* 18 */,
/* 19 */,
/* 20 */,
/* 21 */,
/* 22 */,
/* 23 */,
/* 24 */,
/* 25 */,
/* 26 */,
/* 27 */,
/* 28 */,
/* 29 */,
/* 30 */,
/* 31 */,
/* 32 */,
/* 33 */,
/* 34 */,
/* 35 */,
/* 36 */,
/* 37 */,
/* 38 */,
/* 39 */,
/* 40 */,
/* 41 */,
/* 42 */,
/* 43 */,
/* 44 */,
/* 45 */,
/* 46 */,
/* 47 */,
/* 48 */,
/* 49 */,
/* 50 */,
/* 51 */,
/* 52 */,
/* 53 */,
/* 54 */,
/* 55 */,
/* 56 */,
/* 57 */,
/* 58 */,
/* 59 */,
/* 60 */,
/* 61 */,
/* 62 */,
/* 63 */,
/* 64 */,
/* 65 */,
/* 66 */,
/* 67 */,
/* 68 */,
/* 69 */,
/* 70 */,
/* 71 */,
/* 72 */,
/* 73 */,
/* 74 */,
/* 75 */,
/* 76 */,
/* 77 */,
/* 78 */,
/* 79 */,
/* 80 */,
/* 81 */,
/* 82 */,
/* 83 */,
/* 84 */,
/* 85 */,
/* 86 */,
/* 87 */,
/* 88 */,
/* 89 */,
/* 90 */,
/* 91 */,
/* 92 */,
/* 93 */,
/* 94 */,
/* 95 */,
/* 96 */,
/* 97 */,
/* 98 */,
/* 99 */,
/* 100 */,
/* 101 */,
/* 102 */,
/* 103 */,
/* 104 */,
/* 105 */,
/* 106 */,
/* 107 */,
/* 108 */,
/* 109 */,
/* 110 */,
/* 111 */,
/* 112 */,
/* 113 */,
/* 114 */,
/* 115 */,
/* 116 */,
/* 117 */,
/* 118 */,
/* 119 */,
/* 120 */,
/* 121 */,
/* 122 */,
/* 123 */,
/* 124 */,
/* 125 */,
/* 126 */,
/* 127 */,
/* 128 */,
/* 129 */,
/* 130 */,
/* 131 */,
/* 132 */,
/* 133 */,
/* 134 */,
/* 135 */,
/* 136 */,
/* 137 */,
/* 138 */,
/* 139 */,
/* 140 */,
/* 141 */,
/* 142 */,
/* 143 */,
/* 144 */,
/* 145 */,
/* 146 */,
/* 147 */,
/* 148 */,
/* 149 */,
/* 150 */,
/* 151 */,
/* 152 */,
/* 153 */,
/* 154 */,
/* 155 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(156);
module.exports = __webpack_require__(287);


/***/ }),
/* 156 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue_select__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue_select___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_vue_select__);
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

__webpack_require__(157);

window.Vue = __webpack_require__(24);



window.Fuse = __webpack_require__(178);

window.moment = __webpack_require__(0);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
Vue.use(__webpack_require__(149));
Vue.component('v-select', __WEBPACK_IMPORTED_MODULE_0_vue_select___default.a);

Vue.component('mu-alert', __webpack_require__(180));
Vue.component('custom-password', __webpack_require__(183));
Vue.component('ajax-select', __webpack_require__(186));
Vue.component('alias-deactivate-at-input', __webpack_require__(189));
Vue.component('popup-modal', __webpack_require__(195));
Vue.component('integrations-form', __webpack_require__(200));
Vue.component('edit-integration-parameters', __webpack_require__(203));
Vue.component('size-measurements-chart', __webpack_require__(206));
Vue.component('input-with-random-generator', __webpack_require__(254));
Vue.component('alias-senders-recipients-form', __webpack_require__(265));
Vue.component('modal-content-provider', __webpack_require__(271));
Vue.component('index-search', __webpack_require__(282));

var app = new Vue({
  el: '#root',
  data: {
    showPopupModal: false,
    modalContentIdentifier: null,
    modalContentPayload: null
  },
  methods: {
    setModalContentIdentifier: function setModalContentIdentifier(identifier) {
      this.modalContentIdentifier = identifier;
      this.showPopupModal = identifier != null;
    },
    setModalContentPayload: function setModalContentPayload(payload) {
      this.modalContentPayload = payload;
    }
  }
});

/***/ }),
/* 157 */
/***/ (function(module, exports, __webpack_require__) {

window._ = __webpack_require__(15);
window.Popper = __webpack_require__(17).default;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = __webpack_require__(18);

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

var token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
  console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

var baseUrl = document.head.querySelector('meta[name="api-base-url"]');

if (baseUrl) {
  window.axios.defaults.baseURL = baseUrl.content;
} else {
  console.error('API Base URL not found: meta[name="api-base-url"]');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo'

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted: true
// });

/***/ }),
/* 158 */,
/* 159 */,
/* 160 */,
/* 161 */,
/* 162 */,
/* 163 */,
/* 164 */,
/* 165 */,
/* 166 */,
/* 167 */,
/* 168 */,
/* 169 */,
/* 170 */,
/* 171 */,
/* 172 */,
/* 173 */,
/* 174 */,
/* 175 */,
/* 176 */,
/* 177 */,
/* 178 */
/***/ (function(module, exports, __webpack_require__) {

/*!
 * Fuse.js v3.2.1 - Lightweight fuzzy-search (http://fusejs.io)
 * 
 * Copyright (c) 2012-2017 Kirollos Risk (http://kiro.me)
 * All Rights Reserved. Apache Software License 2.0
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 */
(function webpackUniversalModuleDefinition(root, factory) {
	if(true)
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define("Fuse", [], factory);
	else if(typeof exports === 'object')
		exports["Fuse"] = factory();
	else
		root["Fuse"] = factory();
})(this, function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 8);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = function (obj) {
  return !Array.isArray ? Object.prototype.toString.call(obj) === '[object Array]' : Array.isArray(obj);
};

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var bitapRegexSearch = __webpack_require__(5);
var bitapSearch = __webpack_require__(7);
var patternAlphabet = __webpack_require__(4);

var Bitap = function () {
  function Bitap(pattern, _ref) {
    var _ref$location = _ref.location,
        location = _ref$location === undefined ? 0 : _ref$location,
        _ref$distance = _ref.distance,
        distance = _ref$distance === undefined ? 100 : _ref$distance,
        _ref$threshold = _ref.threshold,
        threshold = _ref$threshold === undefined ? 0.6 : _ref$threshold,
        _ref$maxPatternLength = _ref.maxPatternLength,
        maxPatternLength = _ref$maxPatternLength === undefined ? 32 : _ref$maxPatternLength,
        _ref$isCaseSensitive = _ref.isCaseSensitive,
        isCaseSensitive = _ref$isCaseSensitive === undefined ? false : _ref$isCaseSensitive,
        _ref$tokenSeparator = _ref.tokenSeparator,
        tokenSeparator = _ref$tokenSeparator === undefined ? / +/g : _ref$tokenSeparator,
        _ref$findAllMatches = _ref.findAllMatches,
        findAllMatches = _ref$findAllMatches === undefined ? false : _ref$findAllMatches,
        _ref$minMatchCharLeng = _ref.minMatchCharLength,
        minMatchCharLength = _ref$minMatchCharLeng === undefined ? 1 : _ref$minMatchCharLeng;

    _classCallCheck(this, Bitap);

    this.options = {
      location: location,
      distance: distance,
      threshold: threshold,
      maxPatternLength: maxPatternLength,
      isCaseSensitive: isCaseSensitive,
      tokenSeparator: tokenSeparator,
      findAllMatches: findAllMatches,
      minMatchCharLength: minMatchCharLength
    };

    this.pattern = this.options.isCaseSensitive ? pattern : pattern.toLowerCase();

    if (this.pattern.length <= maxPatternLength) {
      this.patternAlphabet = patternAlphabet(this.pattern);
    }
  }

  _createClass(Bitap, [{
    key: 'search',
    value: function search(text) {
      if (!this.options.isCaseSensitive) {
        text = text.toLowerCase();
      }

      // Exact match
      if (this.pattern === text) {
        return {
          isMatch: true,
          score: 0,
          matchedIndices: [[0, text.length - 1]]
        };
      }

      // When pattern length is greater than the machine word length, just do a a regex comparison
      var _options = this.options,
          maxPatternLength = _options.maxPatternLength,
          tokenSeparator = _options.tokenSeparator;

      if (this.pattern.length > maxPatternLength) {
        return bitapRegexSearch(text, this.pattern, tokenSeparator);
      }

      // Otherwise, use Bitap algorithm
      var _options2 = this.options,
          location = _options2.location,
          distance = _options2.distance,
          threshold = _options2.threshold,
          findAllMatches = _options2.findAllMatches,
          minMatchCharLength = _options2.minMatchCharLength;

      return bitapSearch(text, this.pattern, this.patternAlphabet, {
        location: location,
        distance: distance,
        threshold: threshold,
        findAllMatches: findAllMatches,
        minMatchCharLength: minMatchCharLength
      });
    }
  }]);

  return Bitap;
}();

// let x = new Bitap("od mn war", {})
// let result = x.search("Old Man's War")
// console.log(result)

module.exports = Bitap;

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var isArray = __webpack_require__(0);

var deepValue = function deepValue(obj, path, list) {
  if (!path) {
    // If there's no path left, we've gotten to the object we care about.
    list.push(obj);
  } else {
    var dotIndex = path.indexOf('.');
    var firstSegment = path;
    var remaining = null;

    if (dotIndex !== -1) {
      firstSegment = path.slice(0, dotIndex);
      remaining = path.slice(dotIndex + 1);
    }

    var value = obj[firstSegment];

    if (value !== null && value !== undefined) {
      if (!remaining && (typeof value === 'string' || typeof value === 'number')) {
        list.push(value.toString());
      } else if (isArray(value)) {
        // Search each item in the array.
        for (var i = 0, len = value.length; i < len; i += 1) {
          deepValue(value[i], remaining, list);
        }
      } else if (remaining) {
        // An object. Recurse further.
        deepValue(value, remaining, list);
      }
    }
  }

  return list;
};

module.exports = function (obj, path) {
  return deepValue(obj, path, []);
};

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = function () {
  var matchmask = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
  var minMatchCharLength = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;

  var matchedIndices = [];
  var start = -1;
  var end = -1;
  var i = 0;

  for (var len = matchmask.length; i < len; i += 1) {
    var match = matchmask[i];
    if (match && start === -1) {
      start = i;
    } else if (!match && start !== -1) {
      end = i - 1;
      if (end - start + 1 >= minMatchCharLength) {
        matchedIndices.push([start, end]);
      }
      start = -1;
    }
  }

  // (i-1 - start) + 1 => i - start
  if (matchmask[i - 1] && i - start >= minMatchCharLength) {
    matchedIndices.push([start, i - 1]);
  }

  return matchedIndices;
};

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = function (pattern) {
  var mask = {};
  var len = pattern.length;

  for (var i = 0; i < len; i += 1) {
    mask[pattern.charAt(i)] = 0;
  }

  for (var _i = 0; _i < len; _i += 1) {
    mask[pattern.charAt(_i)] |= 1 << len - _i - 1;
  }

  return mask;
};

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var SPECIAL_CHARS_REGEX = /[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g;

module.exports = function (text, pattern) {
  var tokenSeparator = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : / +/g;

  var regex = new RegExp(pattern.replace(SPECIAL_CHARS_REGEX, '\\$&').replace(tokenSeparator, '|'));
  var matches = text.match(regex);
  var isMatch = !!matches;
  var matchedIndices = [];

  if (isMatch) {
    for (var i = 0, matchesLen = matches.length; i < matchesLen; i += 1) {
      var match = matches[i];
      matchedIndices.push([text.indexOf(match), match.length - 1]);
    }
  }

  return {
    // TODO: revisit this score
    score: isMatch ? 0.5 : 1,
    isMatch: isMatch,
    matchedIndices: matchedIndices
  };
};

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = function (pattern, _ref) {
  var _ref$errors = _ref.errors,
      errors = _ref$errors === undefined ? 0 : _ref$errors,
      _ref$currentLocation = _ref.currentLocation,
      currentLocation = _ref$currentLocation === undefined ? 0 : _ref$currentLocation,
      _ref$expectedLocation = _ref.expectedLocation,
      expectedLocation = _ref$expectedLocation === undefined ? 0 : _ref$expectedLocation,
      _ref$distance = _ref.distance,
      distance = _ref$distance === undefined ? 100 : _ref$distance;

  var accuracy = errors / pattern.length;
  var proximity = Math.abs(expectedLocation - currentLocation);

  if (!distance) {
    // Dodge divide by zero error.
    return proximity ? 1.0 : accuracy;
  }

  return accuracy + proximity / distance;
};

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var bitapScore = __webpack_require__(6);
var matchedIndices = __webpack_require__(3);

module.exports = function (text, pattern, patternAlphabet, _ref) {
  var _ref$location = _ref.location,
      location = _ref$location === undefined ? 0 : _ref$location,
      _ref$distance = _ref.distance,
      distance = _ref$distance === undefined ? 100 : _ref$distance,
      _ref$threshold = _ref.threshold,
      threshold = _ref$threshold === undefined ? 0.6 : _ref$threshold,
      _ref$findAllMatches = _ref.findAllMatches,
      findAllMatches = _ref$findAllMatches === undefined ? false : _ref$findAllMatches,
      _ref$minMatchCharLeng = _ref.minMatchCharLength,
      minMatchCharLength = _ref$minMatchCharLeng === undefined ? 1 : _ref$minMatchCharLeng;

  var expectedLocation = location;
  // Set starting location at beginning text and initialize the alphabet.
  var textLen = text.length;
  // Highest score beyond which we give up.
  var currentThreshold = threshold;
  // Is there a nearby exact match? (speedup)
  var bestLocation = text.indexOf(pattern, expectedLocation);

  var patternLen = pattern.length;

  // a mask of the matches
  var matchMask = [];
  for (var i = 0; i < textLen; i += 1) {
    matchMask[i] = 0;
  }

  if (bestLocation !== -1) {
    var score = bitapScore(pattern, {
      errors: 0,
      currentLocation: bestLocation,
      expectedLocation: expectedLocation,
      distance: distance
    });
    currentThreshold = Math.min(score, currentThreshold);

    // What about in the other direction? (speed up)
    bestLocation = text.lastIndexOf(pattern, expectedLocation + patternLen);

    if (bestLocation !== -1) {
      var _score = bitapScore(pattern, {
        errors: 0,
        currentLocation: bestLocation,
        expectedLocation: expectedLocation,
        distance: distance
      });
      currentThreshold = Math.min(_score, currentThreshold);
    }
  }

  // Reset the best location
  bestLocation = -1;

  var lastBitArr = [];
  var finalScore = 1;
  var binMax = patternLen + textLen;

  var mask = 1 << patternLen - 1;

  for (var _i = 0; _i < patternLen; _i += 1) {
    // Scan for the best match; each iteration allows for one more error.
    // Run a binary search to determine how far from the match location we can stray
    // at this error level.
    var binMin = 0;
    var binMid = binMax;

    while (binMin < binMid) {
      var _score3 = bitapScore(pattern, {
        errors: _i,
        currentLocation: expectedLocation + binMid,
        expectedLocation: expectedLocation,
        distance: distance
      });

      if (_score3 <= currentThreshold) {
        binMin = binMid;
      } else {
        binMax = binMid;
      }

      binMid = Math.floor((binMax - binMin) / 2 + binMin);
    }

    // Use the result from this iteration as the maximum for the next.
    binMax = binMid;

    var start = Math.max(1, expectedLocation - binMid + 1);
    var finish = findAllMatches ? textLen : Math.min(expectedLocation + binMid, textLen) + patternLen;

    // Initialize the bit array
    var bitArr = Array(finish + 2);

    bitArr[finish + 1] = (1 << _i) - 1;

    for (var j = finish; j >= start; j -= 1) {
      var currentLocation = j - 1;
      var charMatch = patternAlphabet[text.charAt(currentLocation)];

      if (charMatch) {
        matchMask[currentLocation] = 1;
      }

      // First pass: exact match
      bitArr[j] = (bitArr[j + 1] << 1 | 1) & charMatch;

      // Subsequent passes: fuzzy match
      if (_i !== 0) {
        bitArr[j] |= (lastBitArr[j + 1] | lastBitArr[j]) << 1 | 1 | lastBitArr[j + 1];
      }

      if (bitArr[j] & mask) {
        finalScore = bitapScore(pattern, {
          errors: _i,
          currentLocation: currentLocation,
          expectedLocation: expectedLocation,
          distance: distance
        });

        // This match will almost certainly be better than any existing match.
        // But check anyway.
        if (finalScore <= currentThreshold) {
          // Indeed it is
          currentThreshold = finalScore;
          bestLocation = currentLocation;

          // Already passed `loc`, downhill from here on in.
          if (bestLocation <= expectedLocation) {
            break;
          }

          // When passing `bestLocation`, don't exceed our current distance from `expectedLocation`.
          start = Math.max(1, 2 * expectedLocation - bestLocation);
        }
      }
    }

    // No hope for a (better) match at greater error levels.
    var _score2 = bitapScore(pattern, {
      errors: _i + 1,
      currentLocation: expectedLocation,
      expectedLocation: expectedLocation,
      distance: distance
    });

    // console.log('score', score, finalScore)

    if (_score2 > currentThreshold) {
      break;
    }

    lastBitArr = bitArr;
  }

  // console.log('FINAL SCORE', finalScore)

  // Count exact matches (those with a score of 0) to be "almost" exact
  return {
    isMatch: bestLocation >= 0,
    score: finalScore === 0 ? 0.001 : finalScore,
    matchedIndices: matchedIndices(matchMask, minMatchCharLength)
  };
};

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Bitap = __webpack_require__(1);
var deepValue = __webpack_require__(2);
var isArray = __webpack_require__(0);

var Fuse = function () {
  function Fuse(list, _ref) {
    var _ref$location = _ref.location,
        location = _ref$location === undefined ? 0 : _ref$location,
        _ref$distance = _ref.distance,
        distance = _ref$distance === undefined ? 100 : _ref$distance,
        _ref$threshold = _ref.threshold,
        threshold = _ref$threshold === undefined ? 0.6 : _ref$threshold,
        _ref$maxPatternLength = _ref.maxPatternLength,
        maxPatternLength = _ref$maxPatternLength === undefined ? 32 : _ref$maxPatternLength,
        _ref$caseSensitive = _ref.caseSensitive,
        caseSensitive = _ref$caseSensitive === undefined ? false : _ref$caseSensitive,
        _ref$tokenSeparator = _ref.tokenSeparator,
        tokenSeparator = _ref$tokenSeparator === undefined ? / +/g : _ref$tokenSeparator,
        _ref$findAllMatches = _ref.findAllMatches,
        findAllMatches = _ref$findAllMatches === undefined ? false : _ref$findAllMatches,
        _ref$minMatchCharLeng = _ref.minMatchCharLength,
        minMatchCharLength = _ref$minMatchCharLeng === undefined ? 1 : _ref$minMatchCharLeng,
        _ref$id = _ref.id,
        id = _ref$id === undefined ? null : _ref$id,
        _ref$keys = _ref.keys,
        keys = _ref$keys === undefined ? [] : _ref$keys,
        _ref$shouldSort = _ref.shouldSort,
        shouldSort = _ref$shouldSort === undefined ? true : _ref$shouldSort,
        _ref$getFn = _ref.getFn,
        getFn = _ref$getFn === undefined ? deepValue : _ref$getFn,
        _ref$sortFn = _ref.sortFn,
        sortFn = _ref$sortFn === undefined ? function (a, b) {
      return a.score - b.score;
    } : _ref$sortFn,
        _ref$tokenize = _ref.tokenize,
        tokenize = _ref$tokenize === undefined ? false : _ref$tokenize,
        _ref$matchAllTokens = _ref.matchAllTokens,
        matchAllTokens = _ref$matchAllTokens === undefined ? false : _ref$matchAllTokens,
        _ref$includeMatches = _ref.includeMatches,
        includeMatches = _ref$includeMatches === undefined ? false : _ref$includeMatches,
        _ref$includeScore = _ref.includeScore,
        includeScore = _ref$includeScore === undefined ? false : _ref$includeScore,
        _ref$verbose = _ref.verbose,
        verbose = _ref$verbose === undefined ? false : _ref$verbose;

    _classCallCheck(this, Fuse);

    this.options = {
      location: location,
      distance: distance,
      threshold: threshold,
      maxPatternLength: maxPatternLength,
      isCaseSensitive: caseSensitive,
      tokenSeparator: tokenSeparator,
      findAllMatches: findAllMatches,
      minMatchCharLength: minMatchCharLength,
      id: id,
      keys: keys,
      includeMatches: includeMatches,
      includeScore: includeScore,
      shouldSort: shouldSort,
      getFn: getFn,
      sortFn: sortFn,
      verbose: verbose,
      tokenize: tokenize,
      matchAllTokens: matchAllTokens
    };

    this.setCollection(list);
  }

  _createClass(Fuse, [{
    key: 'setCollection',
    value: function setCollection(list) {
      this.list = list;
      return list;
    }
  }, {
    key: 'search',
    value: function search(pattern) {
      this._log('---------\nSearch pattern: "' + pattern + '"');

      var _prepareSearchers2 = this._prepareSearchers(pattern),
          tokenSearchers = _prepareSearchers2.tokenSearchers,
          fullSearcher = _prepareSearchers2.fullSearcher;

      var _search2 = this._search(tokenSearchers, fullSearcher),
          weights = _search2.weights,
          results = _search2.results;

      this._computeScore(weights, results);

      if (this.options.shouldSort) {
        this._sort(results);
      }

      return this._format(results);
    }
  }, {
    key: '_prepareSearchers',
    value: function _prepareSearchers() {
      var pattern = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';

      var tokenSearchers = [];

      if (this.options.tokenize) {
        // Tokenize on the separator
        var tokens = pattern.split(this.options.tokenSeparator);
        for (var i = 0, len = tokens.length; i < len; i += 1) {
          tokenSearchers.push(new Bitap(tokens[i], this.options));
        }
      }

      var fullSearcher = new Bitap(pattern, this.options);

      return { tokenSearchers: tokenSearchers, fullSearcher: fullSearcher };
    }
  }, {
    key: '_search',
    value: function _search() {
      var tokenSearchers = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
      var fullSearcher = arguments[1];

      var list = this.list;
      var resultMap = {};
      var results = [];

      // Check the first item in the list, if it's a string, then we assume
      // that every item in the list is also a string, and thus it's a flattened array.
      if (typeof list[0] === 'string') {
        // Iterate over every item
        for (var i = 0, len = list.length; i < len; i += 1) {
          this._analyze({
            key: '',
            value: list[i],
            record: i,
            index: i
          }, {
            resultMap: resultMap,
            results: results,
            tokenSearchers: tokenSearchers,
            fullSearcher: fullSearcher
          });
        }

        return { weights: null, results: results };
      }

      // Otherwise, the first item is an Object (hopefully), and thus the searching
      // is done on the values of the keys of each item.
      var weights = {};
      for (var _i = 0, _len = list.length; _i < _len; _i += 1) {
        var item = list[_i];
        // Iterate over every key
        for (var j = 0, keysLen = this.options.keys.length; j < keysLen; j += 1) {
          var key = this.options.keys[j];
          if (typeof key !== 'string') {
            weights[key.name] = {
              weight: 1 - key.weight || 1
            };
            if (key.weight <= 0 || key.weight > 1) {
              throw new Error('Key weight has to be > 0 and <= 1');
            }
            key = key.name;
          } else {
            weights[key] = {
              weight: 1
            };
          }

          this._analyze({
            key: key,
            value: this.options.getFn(item, key),
            record: item,
            index: _i
          }, {
            resultMap: resultMap,
            results: results,
            tokenSearchers: tokenSearchers,
            fullSearcher: fullSearcher
          });
        }
      }

      return { weights: weights, results: results };
    }
  }, {
    key: '_analyze',
    value: function _analyze(_ref2, _ref3) {
      var key = _ref2.key,
          _ref2$arrayIndex = _ref2.arrayIndex,
          arrayIndex = _ref2$arrayIndex === undefined ? -1 : _ref2$arrayIndex,
          value = _ref2.value,
          record = _ref2.record,
          index = _ref2.index;
      var _ref3$tokenSearchers = _ref3.tokenSearchers,
          tokenSearchers = _ref3$tokenSearchers === undefined ? [] : _ref3$tokenSearchers,
          _ref3$fullSearcher = _ref3.fullSearcher,
          fullSearcher = _ref3$fullSearcher === undefined ? [] : _ref3$fullSearcher,
          _ref3$resultMap = _ref3.resultMap,
          resultMap = _ref3$resultMap === undefined ? {} : _ref3$resultMap,
          _ref3$results = _ref3.results,
          results = _ref3$results === undefined ? [] : _ref3$results;

      // Check if the texvaluet can be searched
      if (value === undefined || value === null) {
        return;
      }

      var exists = false;
      var averageScore = -1;
      var numTextMatches = 0;

      if (typeof value === 'string') {
        this._log('\nKey: ' + (key === '' ? '-' : key));

        var mainSearchResult = fullSearcher.search(value);
        this._log('Full text: "' + value + '", score: ' + mainSearchResult.score);

        if (this.options.tokenize) {
          var words = value.split(this.options.tokenSeparator);
          var scores = [];

          for (var i = 0; i < tokenSearchers.length; i += 1) {
            var tokenSearcher = tokenSearchers[i];

            this._log('\nPattern: "' + tokenSearcher.pattern + '"');

            // let tokenScores = []
            var hasMatchInText = false;

            for (var j = 0; j < words.length; j += 1) {
              var word = words[j];
              var tokenSearchResult = tokenSearcher.search(word);
              var obj = {};
              if (tokenSearchResult.isMatch) {
                obj[word] = tokenSearchResult.score;
                exists = true;
                hasMatchInText = true;
                scores.push(tokenSearchResult.score);
              } else {
                obj[word] = 1;
                if (!this.options.matchAllTokens) {
                  scores.push(1);
                }
              }
              this._log('Token: "' + word + '", score: ' + obj[word]);
              // tokenScores.push(obj)
            }

            if (hasMatchInText) {
              numTextMatches += 1;
            }
          }

          averageScore = scores[0];
          var scoresLen = scores.length;
          for (var _i2 = 1; _i2 < scoresLen; _i2 += 1) {
            averageScore += scores[_i2];
          }
          averageScore = averageScore / scoresLen;

          this._log('Token score average:', averageScore);
        }

        var finalScore = mainSearchResult.score;
        if (averageScore > -1) {
          finalScore = (finalScore + averageScore) / 2;
        }

        this._log('Score average:', finalScore);

        var checkTextMatches = this.options.tokenize && this.options.matchAllTokens ? numTextMatches >= tokenSearchers.length : true;

        this._log('\nCheck Matches: ' + checkTextMatches);

        // If a match is found, add the item to <rawResults>, including its score
        if ((exists || mainSearchResult.isMatch) && checkTextMatches) {
          // Check if the item already exists in our results
          var existingResult = resultMap[index];
          if (existingResult) {
            // Use the lowest score
            // existingResult.score, bitapResult.score
            existingResult.output.push({
              key: key,
              arrayIndex: arrayIndex,
              value: value,
              score: finalScore,
              matchedIndices: mainSearchResult.matchedIndices
            });
          } else {
            // Add it to the raw result list
            resultMap[index] = {
              item: record,
              output: [{
                key: key,
                arrayIndex: arrayIndex,
                value: value,
                score: finalScore,
                matchedIndices: mainSearchResult.matchedIndices
              }]
            };

            results.push(resultMap[index]);
          }
        }
      } else if (isArray(value)) {
        for (var _i3 = 0, len = value.length; _i3 < len; _i3 += 1) {
          this._analyze({
            key: key,
            arrayIndex: _i3,
            value: value[_i3],
            record: record,
            index: index
          }, {
            resultMap: resultMap,
            results: results,
            tokenSearchers: tokenSearchers,
            fullSearcher: fullSearcher
          });
        }
      }
    }
  }, {
    key: '_computeScore',
    value: function _computeScore(weights, results) {
      this._log('\n\nComputing score:\n');

      for (var i = 0, len = results.length; i < len; i += 1) {
        var output = results[i].output;
        var scoreLen = output.length;

        var currScore = 1;
        var bestScore = 1;

        for (var j = 0; j < scoreLen; j += 1) {
          var weight = weights ? weights[output[j].key].weight : 1;
          var score = weight === 1 ? output[j].score : output[j].score || 0.001;
          var nScore = score * weight;

          if (weight !== 1) {
            bestScore = Math.min(bestScore, nScore);
          } else {
            output[j].nScore = nScore;
            currScore *= nScore;
          }
        }

        results[i].score = bestScore === 1 ? currScore : bestScore;

        this._log(results[i]);
      }
    }
  }, {
    key: '_sort',
    value: function _sort(results) {
      this._log('\n\nSorting....');
      results.sort(this.options.sortFn);
    }
  }, {
    key: '_format',
    value: function _format(results) {
      var finalOutput = [];

      if (this.options.verbose) {
        this._log('\n\nOutput:\n\n', JSON.stringify(results));
      }

      var transformers = [];

      if (this.options.includeMatches) {
        transformers.push(function (result, data) {
          var output = result.output;
          data.matches = [];

          for (var i = 0, len = output.length; i < len; i += 1) {
            var item = output[i];

            if (item.matchedIndices.length === 0) {
              continue;
            }

            var obj = {
              indices: item.matchedIndices,
              value: item.value
            };
            if (item.key) {
              obj.key = item.key;
            }
            if (item.hasOwnProperty('arrayIndex') && item.arrayIndex > -1) {
              obj.arrayIndex = item.arrayIndex;
            }
            data.matches.push(obj);
          }
        });
      }

      if (this.options.includeScore) {
        transformers.push(function (result, data) {
          data.score = result.score;
        });
      }

      for (var i = 0, len = results.length; i < len; i += 1) {
        var result = results[i];

        if (this.options.id) {
          result.item = this.options.getFn(result.item, this.options.id)[0];
        }

        if (!transformers.length) {
          finalOutput.push(result.item);
          continue;
        }

        var data = {
          item: result.item
        };

        for (var j = 0, _len2 = transformers.length; j < _len2; j += 1) {
          transformers[j](result, data);
        }

        finalOutput.push(data);
      }

      return finalOutput;
    }
  }, {
    key: '_log',
    value: function _log() {
      if (this.options.verbose) {
        var _console;

        (_console = console).log.apply(_console, arguments);
      }
    }
  }]);

  return Fuse;
}();

module.exports = Fuse;

/***/ })
/******/ ]);
});
//# sourceMappingURL=fuse.js.map

/***/ }),
/* 179 */,
/* 180 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(181)
/* template */
var __vue_template__ = __webpack_require__(182)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/Alert.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-1f8bf896", Component.options)
  } else {
    hotAPI.reload("data-v-1f8bf896", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 181 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    title: {
      type: String
    },
    type: {
      type: String,
      required: true,
      validator: function validator(value) {
        var validValues = ['error', 'warning', 'info', 'success'];
        return validValues.includes(value);
      }
    },
    dismissible: {
      type: Boolean,
      default: true
    },
    showTitle: {
      type: Boolean,
      default: true
    },
    iconClass: {
      type: String
    }
  },
  data: function data() {
    return {
      show: true
    };
  },

  methods: {
    getColor: function getColor() {
      switch (this.type) {
        case 'error':
          return 'red';
        case 'warning':
          return 'orange';
        case 'info':
          return 'blue';
        case 'success':
          return 'green';
      }
    },
    getIconName: function getIconName() {
      if (this.iconClass != null) {
        return this.iconClass;
      }
      switch (this.type) {
        case 'error':
          return 'fa-times-circle';
        case 'warning':
          return 'fa-exclamation-circle';
        case 'info':
          return 'fa-info-circle';
        case 'success':
          return 'fa-check-circle';
      }
    },
    getTitle: function getTitle() {
      var defaultTitle = void 0;
      switch (this.type) {
        case 'error':
          defaultTitle = 'Something went wrong!';
          break;
        case 'warning':
          defaultTitle = 'Attention!';
          break;
        case 'info':
          defaultTitle = 'Did you know?';
          break;
        case 'success':
          defaultTitle = 'Good news!';
          break;
      }
      return this.title ? this.title : defaultTitle;
    }
  }
});

/***/ }),
/* 182 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      directives: [
        { name: "show", rawName: "v-show", value: _vm.show, expression: "show" }
      ],
      class:
        "relative rounded px-6 py-4 border-t-4 border-" +
        _vm.getColor() +
        " bg-" +
        _vm.getColor() +
        "-lightest shadow"
    },
    [
      _c("div", { staticClass: "flex flex-row items-center" }, [
        _c("i", {
          class:
            "fas " +
            _vm.getIconName() +
            " fa-fw text-xl text-" +
            _vm.getColor() +
            " mr-6"
        }),
        _vm._v(" "),
        _c("div", { class: "text-" + _vm.getColor() + "-darkest" }, [
          _vm.showTitle
            ? _c("h3", { staticClass: "font-bold mb-2" }, [
                _vm._v(_vm._s(_vm.getTitle()))
              ])
            : _vm._e(),
          _vm._v(" "),
          _c("p", [_vm._t("default")], 2)
        ])
      ]),
      _vm._v(" "),
      _vm.dismissible
        ? _c("div", { staticClass: "absolute pin-t pin-r mt-1 mr-3 text-xl" }, [
            _c(
              "a",
              {
                class:
                  "no-underline text-" +
                  _vm.getColor() +
                  "-darkest hover:text-" +
                  _vm.getColor() +
                  "-dark",
                attrs: { href: "", "aria-label": "close" },
                on: {
                  click: function($event) {
                    $event.preventDefault()
                    _vm.show = false
                  }
                }
              },
              [_vm._v("×")]
            )
          ])
        : _vm._e()
    ]
  )
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-1f8bf896", module.exports)
  }
}

/***/ }),
/* 183 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(184)
/* template */
var __vue_template__ = __webpack_require__(185)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/PasswordInput.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-1dc6bdc8", Component.options)
  } else {
    hotAPI.reload("data-v-1dc6bdc8", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 184 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    name: '',
    id: '',
    classes: '',
    required: {
      type: Boolean
    },
    placeholder: '',
    minlength: ''
  },
  data: function data() {
    return {
      showPassword: false,
      password: ''
    };
  },

  computed: {
    inputType: function inputType() {
      return this.showPassword ? 'text' : 'password';
    }
  }
});

/***/ }),
/* 185 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { class: [_vm.classes, "form-addon-wrapper"] }, [
    _vm.inputType === "checkbox"
      ? _c("input", {
          directives: [
            {
              name: "model",
              rawName: "v-model",
              value: _vm.password,
              expression: "password"
            }
          ],
          staticClass: "form-addon-input",
          attrs: {
            name: _vm.name,
            id: _vm.id,
            placeholder: _vm.placeholder,
            required: _vm.required,
            minlength: _vm.minlength,
            type: "checkbox"
          },
          domProps: {
            checked: Array.isArray(_vm.password)
              ? _vm._i(_vm.password, null) > -1
              : _vm.password
          },
          on: {
            change: function($event) {
              var $$a = _vm.password,
                $$el = $event.target,
                $$c = $$el.checked ? true : false
              if (Array.isArray($$a)) {
                var $$v = null,
                  $$i = _vm._i($$a, $$v)
                if ($$el.checked) {
                  $$i < 0 && (_vm.password = $$a.concat([$$v]))
                } else {
                  $$i > -1 &&
                    (_vm.password = $$a
                      .slice(0, $$i)
                      .concat($$a.slice($$i + 1)))
                }
              } else {
                _vm.password = $$c
              }
            }
          }
        })
      : _vm.inputType === "radio"
        ? _c("input", {
            directives: [
              {
                name: "model",
                rawName: "v-model",
                value: _vm.password,
                expression: "password"
              }
            ],
            staticClass: "form-addon-input",
            attrs: {
              name: _vm.name,
              id: _vm.id,
              placeholder: _vm.placeholder,
              required: _vm.required,
              minlength: _vm.minlength,
              type: "radio"
            },
            domProps: { checked: _vm._q(_vm.password, null) },
            on: {
              change: function($event) {
                _vm.password = null
              }
            }
          })
        : _c("input", {
            directives: [
              {
                name: "model",
                rawName: "v-model",
                value: _vm.password,
                expression: "password"
              }
            ],
            staticClass: "form-addon-input",
            attrs: {
              name: _vm.name,
              id: _vm.id,
              placeholder: _vm.placeholder,
              required: _vm.required,
              minlength: _vm.minlength,
              type: _vm.inputType
            },
            domProps: { value: _vm.password },
            on: {
              input: function($event) {
                if ($event.target.composing) {
                  return
                }
                _vm.password = $event.target.value
              }
            }
          }),
    _vm._v(" "),
    _c("div", { staticClass: "form-addon text-grey" }, [
      _c("i", {
        class: {
          fas: true,
          "cursor-pointer": true,
          "fa-eye": !_vm.showPassword,
          "fa-eye-slash": _vm.showPassword
        },
        attrs: { "aria-label": "Show password" },
        on: {
          click: function($event) {
            _vm.showPassword = !_vm.showPassword
          }
        }
      })
    ])
  ])
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-1dc6bdc8", module.exports)
  }
}

/***/ }),
/* 186 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(187)
/* template */
var __vue_template__ = __webpack_require__(188)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/AjaxSelect.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-07cdea9a", Component.options)
  } else {
    hotAPI.reload("data-v-07cdea9a", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 187 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    name: '',
    id: '',
    placeholder: '',
    required: {
      type: Boolean,
      default: false
    },
    apiUrl: {
      type: String,
      required: true
    },
    apiMethod: {
      type: String
    },
    mapApiValues: {
      type: Function,
      required: true
    },
    selectedValue: String
  },
  data: function data() {
    return {
      options: []
    };
  },
  mounted: function mounted() {
    var _this = this;

    axios({
      method: this.apiMethod || 'get',
      url: this.apiUrl
    }).then(function (response) {
      response.data.data.forEach(function (d) {
        _this.mapApiValues(_this.options, d);
      });
      if (_this.selectedValue == null) return;
      var selectedOption = _this.options.find(function (option) {
        return option.value == _this.selectedValue;
      });
      if (selectedOption == null) return;
      selectedOption.selected = true;
    });
  }
});

/***/ }),
/* 188 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "inline-block relative w-full" }, [
    _c(
      "select",
      {
        staticClass: "form-input",
        attrs: { required: _vm.required, name: _vm.name, id: _vm.id }
      },
      [
        _c("option", { attrs: { disabled: "", selected: "" } }, [
          _vm._v(_vm._s(_vm.placeholder))
        ]),
        _vm._v(" "),
        _vm._l(_vm.options, function(option) {
          return _c(
            "option",
            { domProps: { value: option.value, selected: option.selected } },
            [_vm._v(_vm._s(option.label))]
          )
        })
      ],
      2
    ),
    _vm._v(" "),
    _c(
      "div",
      {
        staticClass:
          "pointer-events-none absolute pin-y pin-r flex items-center px-2 text-grey-darker"
      },
      [
        _c(
          "svg",
          {
            staticClass: "fill-current h-4 w-4",
            attrs: { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 20 20" }
          },
          [
            _c("path", {
              attrs: {
                d:
                  "M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"
              }
            })
          ]
        )
      ]
    )
  ])
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-07cdea9a", module.exports)
  }
}

/***/ }),
/* 189 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(190)
}
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(193)
/* template */
var __vue_template__ = __webpack_require__(194)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-1bdf094a"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/AliasDeactivateAtInput.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-1bdf094a", Component.options)
  } else {
    hotAPI.reload("data-v-1bdf094a", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 190 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(191);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(11)("14c997ee", content, false, {});
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../node_modules/css-loader/index.js!../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"vue\":true,\"id\":\"data-v-1bdf094a\",\"scoped\":true,\"hasInlineConfig\":true}!../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./AliasDeactivateAtInput.vue", function() {
     var newContent = require("!!../../../../node_modules/css-loader/index.js!../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"vue\":true,\"id\":\"data-v-1bdf094a\",\"scoped\":true,\"hasInlineConfig\":true}!../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./AliasDeactivateAtInput.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 191 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(10)(false);
// imports


// module
exports.push([module.i, "\n.fade-enter-active[data-v-1bdf094a],\n.fade-leave-active[data-v-1bdf094a] {\n  -webkit-transition: opacity .3s;\n  transition: opacity .3s;\n}\n.fade-enter[data-v-1bdf094a],\n.fade-leave-to[data-v-1bdf094a] {\n  opacity: 0;\n}\n", ""]);

// exports


/***/ }),
/* 192 */
/***/ (function(module, exports) {

/**
 * Translates the list format produced by css-loader into something
 * easier to manipulate.
 */
module.exports = function listToStyles (parentId, list) {
  var styles = []
  var newStyles = {}
  for (var i = 0; i < list.length; i++) {
    var item = list[i]
    var id = item[0]
    var css = item[1]
    var media = item[2]
    var sourceMap = item[3]
    var part = {
      id: parentId + ':' + i,
      css: css,
      media: media,
      sourceMap: sourceMap
    }
    if (!newStyles[id]) {
      styles.push(newStyles[id] = { id: id, parts: [part] })
    } else {
      newStyles[id].parts.push(part)
    }
  }
  return styles
}


/***/ }),
/* 193 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  props: ['standardDays', 'standardHours', 'standardMinutes'],
  data: function data() {
    return {
      deactivateAlias: false,
      days: 0,
      hours: 0,
      minutes: 0
    };
  },

  methods: {
    chooseSuggestion: function chooseSuggestion(days, hours, minutes) {
      this.days = days;
      this.hours = hours;
      this.minutes = minutes;
    }
  },
  created: function created() {
    this.days = this.standardDays;
    this.hours = this.standardHours;
    this.minutes = this.standardMinutes;
  }
});

/***/ }),
/* 194 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      staticClass:
        "flex flex-col border-t border-b border-grey-lighter py-6 overflow-hidden"
    },
    [
      _c("transition", { attrs: { name: "fade", mode: "out-in" } }, [
        !_vm.deactivateAlias
          ? _c("div", { key: "false", staticClass: "text-center" }, [
              _c(
                "a",
                {
                  staticClass: "text-link text-grey-dark",
                  attrs: { href: "#" },
                  on: {
                    click: function($event) {
                      $event.preventDefault()
                      _vm.deactivateAlias = true
                    }
                  }
                },
                [
                  _vm._v(
                    "\n                Deactivate this alias automatically"
                  )
                ]
              )
            ])
          : _c("div", { key: "true" }, [
              _c("h4", { staticClass: "mb-4" }, [
                _vm._v("Deactivate Alias automatically in...")
              ]),
              _vm._v(" "),
              _c(
                "div",
                {
                  staticClass:
                    "flex flex-row items-start flex-wrap text-grey-dark mb-4 leading-normal"
                },
                [
                  _c("p", { staticClass: "mr-4" }, [
                    _vm._v("Some suggestions:")
                  ]),
                  _vm._v(" "),
                  _c(
                    "a",
                    {
                      staticClass: "mr-4 link-text text-grey-dark",
                      attrs: { href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.chooseSuggestion(0, 0, 10)
                        }
                      }
                    },
                    [_vm._v("10\n                    minutes")]
                  ),
                  _vm._v(" "),
                  _c(
                    "a",
                    {
                      staticClass: "mr-4 link-text text-grey-dark",
                      attrs: { href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.chooseSuggestion(0, 0, 30)
                        }
                      }
                    },
                    [_vm._v("30\n                    minutes")]
                  ),
                  _vm._v(" "),
                  _c(
                    "a",
                    {
                      staticClass: "mr-4 link-text text-grey-dark",
                      attrs: { href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.chooseSuggestion(0, 1, 0)
                        }
                      }
                    },
                    [_vm._v("1\n                    hour")]
                  ),
                  _vm._v(" "),
                  _c(
                    "a",
                    {
                      staticClass: "link-text text-grey-dark",
                      attrs: { href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.chooseSuggestion(1, 0, 0)
                        }
                      }
                    },
                    [_vm._v("1 day")]
                  )
                ]
              ),
              _vm._v(" "),
              _c("div", { staticClass: "form-multi-row md:mb-0" }, [
                _c("div", { staticClass: "form-group w-full md:w-1/3" }, [
                  _c("div", { staticClass: "form-addon-wrapper" }, [
                    _c("input", {
                      directives: [
                        {
                          name: "model",
                          rawName: "v-model",
                          value: _vm.days,
                          expression: "days"
                        }
                      ],
                      staticClass: "form-addon-input text-right",
                      attrs: {
                        type: "number",
                        step: "1",
                        name: "deactivate_at_days"
                      },
                      domProps: { value: _vm.days },
                      on: {
                        input: function($event) {
                          if ($event.target.composing) {
                            return
                          }
                          _vm.days = $event.target.value
                        }
                      }
                    }),
                    _vm._v(" "),
                    _c("label", { staticClass: "form-addon" }, [_vm._v("Days")])
                  ])
                ]),
                _vm._v(" "),
                _c("div", { staticClass: "form-group w-full md:w-1/3" }, [
                  _c("div", { staticClass: "form-addon-wrapper" }, [
                    _c("input", {
                      directives: [
                        {
                          name: "model",
                          rawName: "v-model",
                          value: _vm.hours,
                          expression: "hours"
                        }
                      ],
                      staticClass: "form-addon-input text-right",
                      attrs: {
                        type: "number",
                        step: "1",
                        name: "deactivate_at_hours"
                      },
                      domProps: { value: _vm.hours },
                      on: {
                        input: function($event) {
                          if ($event.target.composing) {
                            return
                          }
                          _vm.hours = $event.target.value
                        }
                      }
                    }),
                    _vm._v(" "),
                    _c("label", { staticClass: "form-addon" }, [
                      _vm._v("Hours")
                    ])
                  ])
                ]),
                _vm._v(" "),
                _c("div", { staticClass: "form-group w-full md:w-1/3" }, [
                  _c("div", { staticClass: "form-addon-wrapper" }, [
                    _c("input", {
                      directives: [
                        {
                          name: "model",
                          rawName: "v-model",
                          value: _vm.minutes,
                          expression: "minutes"
                        }
                      ],
                      staticClass: "form-addon-input text-right",
                      attrs: {
                        type: "number",
                        step: "1",
                        name: "deactivate_at_minutes"
                      },
                      domProps: { value: _vm.minutes },
                      on: {
                        input: function($event) {
                          if ($event.target.composing) {
                            return
                          }
                          _vm.minutes = $event.target.value
                        }
                      }
                    }),
                    _vm._v(" "),
                    _c("label", { staticClass: "form-addon" }, [
                      _vm._v("Minutes")
                    ])
                  ])
                ])
              ]),
              _vm._v(" "),
              _c("div", { staticClass: "mt-8 text-center" }, [
                _c(
                  "a",
                  {
                    staticClass: "text-link text-grey-dark",
                    attrs: { href: "#" },
                    on: {
                      click: function($event) {
                        $event.preventDefault()
                        _vm.deactivateAlias = false
                      }
                    }
                  },
                  [
                    _vm._v(
                      "\n                    Don't deactivate this alias automatically"
                    )
                  ]
                )
              ])
            ])
      ])
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-1bdf094a", module.exports)
  }
}

/***/ }),
/* 195 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(196)
}
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(198)
/* template */
var __vue_template__ = __webpack_require__(199)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/PopupModal.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-5a2bda58", Component.options)
  } else {
    hotAPI.reload("data-v-5a2bda58", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 196 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(197);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(11)("64d9052a", content, false, {});
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../node_modules/css-loader/index.js!../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"vue\":true,\"id\":\"data-v-5a2bda58\",\"scoped\":false,\"hasInlineConfig\":true}!../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./PopupModal.vue", function() {
     var newContent = require("!!../../../../node_modules/css-loader/index.js!../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"vue\":true,\"id\":\"data-v-5a2bda58\",\"scoped\":false,\"hasInlineConfig\":true}!../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./PopupModal.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 197 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(10)(false);
// imports


// module
exports.push([module.i, "\n", ""]);

// exports


/***/ }),
/* 198 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({});

/***/ }),
/* 199 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      staticClass:
        "bg-white rounded shadow-lg px-8 py-8 mx-auto my-auto w-screen relative max-w-sm overflow-hidden"
    },
    [
      _c("div", { staticClass: "absolute pin-t pin-r pt-2 pr-4" }, [
        _c(
          "a",
          {
            staticClass: "text-xl text-grey-dark no-underline",
            attrs: { href: "#" },
            on: {
              click: function($event) {
                $event.preventDefault()
                _vm.$emit("close")
              }
            }
          },
          [_vm._v("×")]
        )
      ]),
      _vm._v(" "),
      _vm._t("default")
    ],
    2
  )
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-5a2bda58", module.exports)
  }
}

/***/ }),
/* 200 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(201)
/* template */
var __vue_template__ = __webpack_require__(202)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/integrations/IntegrationsForm.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-ca8a14e8", Component.options)
  } else {
    hotAPI.reload("data-v-ca8a14e8", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 201 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    modelClass: {
      type: String
    },
    integrationType: {
      type: String
    }
  },
  data: function data() {
    return {
      selectedModelClass: {
        value: ''
      },
      selectedIntegrationType: {
        value: ''
      }
    };
  },
  created: function created() {
    this.selectedModelClass.value = this.modelClass;
    this.selectedIntegrationType.value = this.integrationType;
  },

  computed: {
    availablePlaceholders: function availablePlaceholders() {
      switch (this.selectedModelClass.value) {
        case 'App\\Domain':
          return ['%{id}', '%{domain}', '%{description}', '%{quota}', '%{max_quota}', '%{max_aliases}', '%{max_mailboxes}', '%{active}'];
          break;
        case 'App\\Mailbox':
          return ['%{id}', '%{local_part}', '%{name}', '%{domain}', '%{alternative_email}', '%{quota}', '%{homedir}', '%{maildir}', '%{is_super_admin}', '%{address}', '%{send_only}', '%{active}'];
          break;
        case 'App\\Alias':
          return ['%{id}', '%{local_part}', '%{address}', '%{description}', '%{domain}', '%{active}'];
          break;
        default:
          return null;
      }
    }
  }
});

/***/ }),
/* 202 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    [
      _vm._t("default", null, {
        modelClass: _vm.selectedModelClass,
        availablePlaceholders: _vm.availablePlaceholders,
        integrationType: _vm.selectedIntegrationType
      })
    ],
    2
  )
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-ca8a14e8", module.exports)
  }
}

/***/ }),
/* 203 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(204)
/* template */
var __vue_template__ = __webpack_require__(205)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/integrations/IntegrationParametersForm.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-0a043f89", Component.options)
  } else {
    hotAPI.reload("data-v-0a043f89", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 204 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    availablePlaceholders: {
      type: Array
    },
    oldParameters: {
      type: Array,
      default: function _default() {
        return [];
      }
    }
  },
  data: function data() {
    return {
      parameters: [],
      showForm: false
    };
  },

  methods: {
    addParameter: function addParameter(parameter) {
      this.parameters.push(parameter);
    },
    deleteParameter: function deleteParameter(parameter) {
      this.parameters.splice(this.parameters.indexOf(parameter), 1);
    },
    parameterString: function parameterString(parameter) {
      var delimiter = parameter.use_equal_sign ? '=' : ' ';
      var option = parameter.option != null ? parameter.option + delimiter : '';
      return option + '\'' + parameter.value + '\'';
    },
    emitModalContentData: function emitModalContentData() {
      var _callback = this.addParameter;
      this.$emit('set-modal-content-payload', {
        availablePlaceholders: this.availablePlaceholders,
        modalWidthLarge: true,
        callback: function callback(data) {
          _callback(data);
        }
      });
      this.$emit('set-modal-content-identifier', 'new-integration-parameter-form');
    }
  },
  created: function created() {
    this.parameters = this.oldParameters;
  }
});

/***/ }),
/* 205 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [
    _c("div", { staticClass: "mb-2 flex flex-row items-center" }, [
      _c("h3", [_vm._v("Parameters")]),
      _vm._v(" "),
      _vm.availablePlaceholders
        ? _c("div", { staticClass: "ml-2 text-xs" }, [
            _c(
              "a",
              {
                staticClass:
                  "text-grey no-underline hover:text-grey-dark focus:text-grey-dark",
                attrs: { title: "Add new Parameter", href: "#" },
                on: {
                  click: function($event) {
                    $event.preventDefault()
                    return _vm.emitModalContentData($event)
                  }
                }
              },
              [
                _c("i", {
                  staticClass: "fas fa-plus mr-2",
                  attrs: { "aria-hidden": "true" }
                })
              ]
            )
          ])
        : _vm._e()
    ]),
    _vm._v(" "),
    _c(
      "div",
      [
        _vm.parameters.length < 1 && _vm.availablePlaceholders
          ? _c("p", { staticClass: "text-grey-dark italic text-sm" }, [
              _vm._v(
                "\n            You haven't configured any parameters for this integration.\n        "
              )
            ])
          : _vm._e(),
        _vm._v(" "),
        _vm.parameters.length < 1 && !_vm.availablePlaceholders
          ? _c("p", { staticClass: "text-grey-dark italic text-sm" }, [
              _vm._v(
                "\n            Please select a model to add new parameters.\n        "
              )
            ])
          : _vm._e(),
        _vm._v(" "),
        _vm._l(_vm.parameters, function(parameter) {
          return _c("div", { staticClass: "inline-code my-1 mr-2" }, [
            _c("code", [_vm._v(_vm._s(_vm.parameterString(parameter)))]),
            _vm._v(" "),
            _c(
              "button",
              {
                staticClass: "ml-2 text-grey-dark hover:text-black",
                attrs: { title: "Remove Parameter" },
                on: {
                  click: function($event) {
                    $event.preventDefault()
                    _vm.deleteParameter(parameter)
                  }
                }
              },
              [_vm._v("×\n            ")]
            )
          ])
        })
      ],
      2
    ),
    _vm._v(" "),
    _c(
      "div",
      [
        _vm._l(_vm.parameters, function(parameter) {
          return _c("input", {
            attrs: {
              type: "hidden",
              name:
                "parameters[" + _vm.parameters.indexOf(parameter) + "][option]"
            },
            domProps: { value: parameter.option }
          })
        }),
        _vm._v(" "),
        _vm._l(_vm.parameters, function(parameter) {
          return _c("input", {
            attrs: {
              type: "hidden",
              name:
                "parameters[" + _vm.parameters.indexOf(parameter) + "][value]"
            },
            domProps: { value: parameter.value }
          })
        }),
        _vm._v(" "),
        _vm._l(_vm.parameters, function(parameter) {
          return _c("input", {
            attrs: {
              type: "hidden",
              name:
                "parameters[" +
                _vm.parameters.indexOf(parameter) +
                "][use_equal_sign]"
            },
            domProps: { value: parameter.use_equal_sign ? 1 : 0 }
          })
        })
      ],
      2
    )
  ])
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-0a043f89", module.exports)
  }
}

/***/ }),
/* 206 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(207)
/* template */
var __vue_template__ = __webpack_require__(253)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/SizeMeasurementsChart.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-4c6dc982", Component.options)
  } else {
    hotAPI.reload("data-v-4c6dc982", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 207 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_chart_js__ = __webpack_require__(150);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_chart_js___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_chart_js__);
//
//
//
//



/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    labels: {
      type: Array,
      required: true
    },
    values: {
      type: Array,
      required: true
    },
    colorRGB: {
      default: function _default() {
        return {
          r: 52,
          g: 144,
          b: 220
        };
      }
    },
    showGridLines: {
      type: Object,
      default: function _default() {
        return {
          x: true,
          y: true
        };
      }
    },
    showAxisLabels: {
      type: Object,
      default: function _default() {
        return {
          x: true,
          y: true
        };
      }
    },
    ratio: {
      type: Object,
      default: function _default() {
        return {
          x: 16,
          y: 9
        };
      }
    },
    showLegend: {
      type: Boolean,
      default: false
    },
    padding: {
      type: Object,
      default: function _default() {
        return {
          t: 25,
          r: 10,
          b: 10,
          l: 10
        };
      }
    },
    showPoints: {
      type: Boolean,
      default: true
    },
    lineThickness: {
      type: Number,
      default: 1
    }
  },
  methods: {
    roundFunction: function roundFunction(value, divider) {
      return Math.round(value / divider * 10) / 10;
    },
    getPrettyDataSizeString: function getPrettyDataSizeString(value) {
      if (value > 1024 * 1024 * 1024) {
        return this.roundFunction(value, 1024 * 1024 * 1024) + ' TiB';
      }
      if (value > 1024 * 1024) {
        return this.roundFunction(value, 1024 * 1024) + ' GiB';
      }
      if (value > 1024) {
        return this.roundFunction(value, 1024) + ' MiB';
      }
      return value + ' KiB';
    }
  },
  mounted: function mounted() {
    var _this = this;

    var ctx = this.$el.getContext('2d');
    new __WEBPACK_IMPORTED_MODULE_0_chart_js___default.a(ctx, {
      type: 'line',
      data: {
        labels: this.labels,
        datasets: [{
          data: this.values,
          backgroundColor: 'rgba(' + this.colorRGB.r + ',' + this.colorRGB.g + ',' + this.colorRGB.b + ',0.3)',
          borderColor: 'rgba(' + this.colorRGB.r + ',' + this.colorRGB.g + ',' + this.colorRGB.b + ',1)',
          pointBackgroundColor: 'rgba(' + this.colorRGB.r + ',' + this.colorRGB.g + ',' + this.colorRGB.b + ',0.3)',
          pointBorderColor: 'rgba(' + this.colorRGB.r + ',' + this.colorRGB.g + ',' + this.colorRGB.b + ',1)',
          pointHoverBackgroundColor: 'rgba(' + this.colorRGB.r + ',' + this.colorRGB.g + ',' + this.colorRGB.b + ',1)',
          pointHoverBorderColor: 'rgba(' + this.colorRGB.r + ',' + this.colorRGB.g + ',' + this.colorRGB.b + ',1)',
          cubicInterpolationMode: 'monotone',
          pointRadius: this.showPoints ? 4 : 0,
          borderWidth: this.lineThickness,
          pointHoverRadius: this.showPoints ? 4 : 0
        }]
      },
      options: {
        layout: {
          padding: {
            left: this.padding.l,
            right: this.padding.r,
            top: this.padding.t,
            bottom: this.padding.b
          }
        },
        legend: {
          display: this.showLegend
        },
        scales: {
          xAxes: [{
            type: 'time',
            ticks: {
              padding: this.showAxisLabels.x ? 4 : 0,
              display: this.showAxisLabels.x
            },
            gridLines: {
              display: this.showGridLines.x,
              color: '#f1f5f8',
              drawBorder: this.showAxisLabels.x
            }
          }],
          yAxes: [{
            ticks: {
              callback: function callback(value, index, values) {
                return _this.getPrettyDataSizeString(value);
              },
              padding: this.showAxisLabels.y ? 4 : 0,
              display: this.showAxisLabels.y
            },
            gridLines: {
              display: this.showGridLines.y,
              color: '#f1f5f8',
              drawBorder: this.showAxisLabels.y
            }
          }]
        },
        tooltips: {
          enabled: this.showPoints,
          callbacks: {
            label: function label(tooltipItem, data) {
              return _this.getPrettyDataSizeString(tooltipItem.yLabel);
            }
          }
        }
      }
    });
  }
});

/***/ }),
/* 208 */,
/* 209 */,
/* 210 */,
/* 211 */,
/* 212 */,
/* 213 */,
/* 214 */,
/* 215 */,
/* 216 */,
/* 217 */,
/* 218 */,
/* 219 */,
/* 220 */,
/* 221 */,
/* 222 */,
/* 223 */,
/* 224 */,
/* 225 */,
/* 226 */,
/* 227 */,
/* 228 */,
/* 229 */,
/* 230 */,
/* 231 */,
/* 232 */,
/* 233 */,
/* 234 */,
/* 235 */,
/* 236 */,
/* 237 */,
/* 238 */,
/* 239 */,
/* 240 */,
/* 241 */,
/* 242 */,
/* 243 */,
/* 244 */,
/* 245 */,
/* 246 */,
/* 247 */,
/* 248 */,
/* 249 */,
/* 250 */,
/* 251 */,
/* 252 */,
/* 253 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("canvas", { attrs: { width: _vm.ratio.x, height: _vm.ratio.y } })
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-4c6dc982", module.exports)
  }
}

/***/ }),
/* 254 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(255)
/* template */
var __vue_template__ = __webpack_require__(264)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/InputWithRandomGenerator.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-ea201292", Component.options)
  } else {
    hotAPI.reload("data-v-ea201292", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 255 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

var dwGen = __webpack_require__(256);
var en = __webpack_require__(263);
/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    name: '',
    id: '',
    classes: '',
    required: {
      type: Boolean
    },
    oldValue: {
      type: String,
      default: null
    },
    addon: '',
    placeholder: '',
    validationError: '',
    formHelp: '',
    inputType: {
      type: String,
      default: 'text'
    },
    randomProvider: {
      type: String,
      validator: function validator(val) {
        return ['diceware', 'insecureRandom'].includes(val);
      },
      required: true
    },
    dicewareWordCount: {
      type: Number,
      default: 6
    },
    dicewareSeparator: {
      type: String,
      default: '-'
    },
    insecureRandomCharCount: {
      type: Number,
      default: 20
    }
  },
  data: function data() {
    return {
      inputText: ''
    };
  },

  methods: {
    setRandomValue: function setRandomValue() {
      this.inputText = this.generateRandomString();
    },
    generateRandomString: function generateRandomString() {
      if (this.randomProvider === 'insecureRandom') {
        return _.times(this.insecureRandomCharCount, function () {
          return _.random(35).toString(36);
        }).join('');
      }
      var options = {
        language: en,
        wordcount: this.dicewareWordCount,
        format: 'array'
      };
      return dwGen(options).join(this.dicewareSeparator);
    }
  },
  created: function created() {
    if (this.oldValue) {
      this.inputText = this.oldValue;
    }
  }
});

/***/ }),
/* 256 */
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;(function (global, factory) {
  if (true) {
    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [module, __webpack_require__(257)], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
  } else if (typeof exports !== "undefined") {
    factory(module, require('secure-random'));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod, global.secureRandom);
    global.index = mod.exports;
  }
})(this, function (module, secureRandom) {
  'use strict';

  var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) {
    return typeof obj;
  } : function (obj) {
    return obj && typeof Symbol === "function" && obj.constructor === Symbol ? "symbol" : typeof obj;
  };

  var getRandomInt = function getRandomInt(min, max) {
    // Create byte array and fill with 1 random number
    var byteArray = secureRandom(1, { type: 'Uint8Array' });
    var r = max - min + 1;
    var maxRange = 256;
    if (byteArray[0] >= Math.floor(maxRange / r) * r) return getRandomInt(min, max);
    return min + byteArray[0] % r;
  };

  var diceRoll = function diceRoll() {
    return getRandomInt(1, 6);
  };

  var range = function range(max) {
    return Array.apply(null, Array(max)).map(function (_, i) {
      return i;
    });
  };

  var diceSeq = function diceSeq(count) {
    return range(count).map(function () {
      return diceRoll();
    }).join('');
  };

  var getDices = function getDices() {
    return diceSeq(5);
  };

  var getRandomWord = function getRandomWord(language) {
    return language[getDices()];
  };

  var getRandomPassword = function getRandomPassword(options) {
    options = Object.assign({
      'wordcount': 6,
      'format': 'string'
    }, options);
    if (_typeof(options.language) !== 'object') {
      throw new Error('Language empty');
    }
    if (Object.keys(options.language).length !== 7776) {
      throw new Error('Language length wrong');
    }
    var words = range(options.wordcount).map(function () {
      return getRandomWord(options.language);
    });
    return options.format === 'array' ? words : words.join(' ');
  };

  module.exports = getRandomPassword;
});
//# sourceMappingURL=index.js.map

/***/ }),
/* 257 */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(process, Buffer) {var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!function(globals){
'use strict'

//*** UMD BEGIN
if (true) { //require.js / AMD
  !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = (function() {
    return secureRandom
  }).apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__))
} else if (typeof module !== 'undefined' && module.exports) { //CommonJS
  module.exports = secureRandom
} else { //script / browser
  globals.secureRandom = secureRandom
}
//*** UMD END

//options.type is the only valid option
function secureRandom(count, options) {
  options = options || {type: 'Array'}
  //we check for process.pid to prevent browserify from tricking us
  if (typeof process != 'undefined' && typeof process.pid == 'number') {
    return nodeRandom(count, options)
  } else {
    var crypto = window.crypto || window.msCrypto
    if (!crypto) throw new Error("Your browser does not support window.crypto.")
    return browserRandom(count, options)
  }
}

function nodeRandom(count, options) {
  var crypto = __webpack_require__(262)
  var buf = crypto.randomBytes(count)

  switch (options.type) {
    case 'Array':
      return [].slice.call(buf)
    case 'Buffer':
      return buf
    case 'Uint8Array':
      var arr = new Uint8Array(count)
      for (var i = 0; i < count; ++i) { arr[i] = buf.readUInt8(i) }
      return arr
    default:
      throw new Error(options.type + " is unsupported.")
  }
}

function browserRandom(count, options) {
  var nativeArr = new Uint8Array(count)
  var crypto = window.crypto || window.msCrypto
  crypto.getRandomValues(nativeArr)

  switch (options.type) {
    case 'Array':
      return [].slice.call(nativeArr)
    case 'Buffer':
      try { var b = new Buffer(1) } catch(e) { throw new Error('Buffer not supported in this environment. Use Node.js or Browserify for browser support.')}
      return new Buffer(nativeArr)
    case 'Uint8Array':
      return nativeArr
    default:
      throw new Error(options.type + " is unsupported.")
  }
}

secureRandom.randomArray = function(byteCount) {
  return secureRandom(byteCount, {type: 'Array'})
}

secureRandom.randomUint8Array = function(byteCount) {
  return secureRandom(byteCount, {type: 'Uint8Array'})
}

secureRandom.randomBuffer = function(byteCount) {
  return secureRandom(byteCount, {type: 'Buffer'})
}


}(this);

/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(14), __webpack_require__(258).Buffer))

/***/ }),
/* 258 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(global) {/*!
 * The buffer module from node.js, for the browser.
 *
 * @author   Feross Aboukhadijeh <feross@feross.org> <http://feross.org>
 * @license  MIT
 */
/* eslint-disable no-proto */



var base64 = __webpack_require__(259)
var ieee754 = __webpack_require__(260)
var isArray = __webpack_require__(261)

exports.Buffer = Buffer
exports.SlowBuffer = SlowBuffer
exports.INSPECT_MAX_BYTES = 50

/**
 * If `Buffer.TYPED_ARRAY_SUPPORT`:
 *   === true    Use Uint8Array implementation (fastest)
 *   === false   Use Object implementation (most compatible, even IE6)
 *
 * Browsers that support typed arrays are IE 10+, Firefox 4+, Chrome 7+, Safari 5.1+,
 * Opera 11.6+, iOS 4.2+.
 *
 * Due to various browser bugs, sometimes the Object implementation will be used even
 * when the browser supports typed arrays.
 *
 * Note:
 *
 *   - Firefox 4-29 lacks support for adding new properties to `Uint8Array` instances,
 *     See: https://bugzilla.mozilla.org/show_bug.cgi?id=695438.
 *
 *   - Chrome 9-10 is missing the `TypedArray.prototype.subarray` function.
 *
 *   - IE10 has a broken `TypedArray.prototype.subarray` function which returns arrays of
 *     incorrect length in some situations.

 * We detect these buggy browsers and set `Buffer.TYPED_ARRAY_SUPPORT` to `false` so they
 * get the Object implementation, which is slower but behaves correctly.
 */
Buffer.TYPED_ARRAY_SUPPORT = global.TYPED_ARRAY_SUPPORT !== undefined
  ? global.TYPED_ARRAY_SUPPORT
  : typedArraySupport()

/*
 * Export kMaxLength after typed array support is determined.
 */
exports.kMaxLength = kMaxLength()

function typedArraySupport () {
  try {
    var arr = new Uint8Array(1)
    arr.__proto__ = {__proto__: Uint8Array.prototype, foo: function () { return 42 }}
    return arr.foo() === 42 && // typed array instances can be augmented
        typeof arr.subarray === 'function' && // chrome 9-10 lack `subarray`
        arr.subarray(1, 1).byteLength === 0 // ie10 has broken `subarray`
  } catch (e) {
    return false
  }
}

function kMaxLength () {
  return Buffer.TYPED_ARRAY_SUPPORT
    ? 0x7fffffff
    : 0x3fffffff
}

function createBuffer (that, length) {
  if (kMaxLength() < length) {
    throw new RangeError('Invalid typed array length')
  }
  if (Buffer.TYPED_ARRAY_SUPPORT) {
    // Return an augmented `Uint8Array` instance, for best performance
    that = new Uint8Array(length)
    that.__proto__ = Buffer.prototype
  } else {
    // Fallback: Return an object instance of the Buffer class
    if (that === null) {
      that = new Buffer(length)
    }
    that.length = length
  }

  return that
}

/**
 * The Buffer constructor returns instances of `Uint8Array` that have their
 * prototype changed to `Buffer.prototype`. Furthermore, `Buffer` is a subclass of
 * `Uint8Array`, so the returned instances will have all the node `Buffer` methods
 * and the `Uint8Array` methods. Square bracket notation works as expected -- it
 * returns a single octet.
 *
 * The `Uint8Array` prototype remains unmodified.
 */

function Buffer (arg, encodingOrOffset, length) {
  if (!Buffer.TYPED_ARRAY_SUPPORT && !(this instanceof Buffer)) {
    return new Buffer(arg, encodingOrOffset, length)
  }

  // Common case.
  if (typeof arg === 'number') {
    if (typeof encodingOrOffset === 'string') {
      throw new Error(
        'If encoding is specified then the first argument must be a string'
      )
    }
    return allocUnsafe(this, arg)
  }
  return from(this, arg, encodingOrOffset, length)
}

Buffer.poolSize = 8192 // not used by this implementation

// TODO: Legacy, not needed anymore. Remove in next major version.
Buffer._augment = function (arr) {
  arr.__proto__ = Buffer.prototype
  return arr
}

function from (that, value, encodingOrOffset, length) {
  if (typeof value === 'number') {
    throw new TypeError('"value" argument must not be a number')
  }

  if (typeof ArrayBuffer !== 'undefined' && value instanceof ArrayBuffer) {
    return fromArrayBuffer(that, value, encodingOrOffset, length)
  }

  if (typeof value === 'string') {
    return fromString(that, value, encodingOrOffset)
  }

  return fromObject(that, value)
}

/**
 * Functionally equivalent to Buffer(arg, encoding) but throws a TypeError
 * if value is a number.
 * Buffer.from(str[, encoding])
 * Buffer.from(array)
 * Buffer.from(buffer)
 * Buffer.from(arrayBuffer[, byteOffset[, length]])
 **/
Buffer.from = function (value, encodingOrOffset, length) {
  return from(null, value, encodingOrOffset, length)
}

if (Buffer.TYPED_ARRAY_SUPPORT) {
  Buffer.prototype.__proto__ = Uint8Array.prototype
  Buffer.__proto__ = Uint8Array
  if (typeof Symbol !== 'undefined' && Symbol.species &&
      Buffer[Symbol.species] === Buffer) {
    // Fix subarray() in ES2016. See: https://github.com/feross/buffer/pull/97
    Object.defineProperty(Buffer, Symbol.species, {
      value: null,
      configurable: true
    })
  }
}

function assertSize (size) {
  if (typeof size !== 'number') {
    throw new TypeError('"size" argument must be a number')
  } else if (size < 0) {
    throw new RangeError('"size" argument must not be negative')
  }
}

function alloc (that, size, fill, encoding) {
  assertSize(size)
  if (size <= 0) {
    return createBuffer(that, size)
  }
  if (fill !== undefined) {
    // Only pay attention to encoding if it's a string. This
    // prevents accidentally sending in a number that would
    // be interpretted as a start offset.
    return typeof encoding === 'string'
      ? createBuffer(that, size).fill(fill, encoding)
      : createBuffer(that, size).fill(fill)
  }
  return createBuffer(that, size)
}

/**
 * Creates a new filled Buffer instance.
 * alloc(size[, fill[, encoding]])
 **/
Buffer.alloc = function (size, fill, encoding) {
  return alloc(null, size, fill, encoding)
}

function allocUnsafe (that, size) {
  assertSize(size)
  that = createBuffer(that, size < 0 ? 0 : checked(size) | 0)
  if (!Buffer.TYPED_ARRAY_SUPPORT) {
    for (var i = 0; i < size; ++i) {
      that[i] = 0
    }
  }
  return that
}

/**
 * Equivalent to Buffer(num), by default creates a non-zero-filled Buffer instance.
 * */
Buffer.allocUnsafe = function (size) {
  return allocUnsafe(null, size)
}
/**
 * Equivalent to SlowBuffer(num), by default creates a non-zero-filled Buffer instance.
 */
Buffer.allocUnsafeSlow = function (size) {
  return allocUnsafe(null, size)
}

function fromString (that, string, encoding) {
  if (typeof encoding !== 'string' || encoding === '') {
    encoding = 'utf8'
  }

  if (!Buffer.isEncoding(encoding)) {
    throw new TypeError('"encoding" must be a valid string encoding')
  }

  var length = byteLength(string, encoding) | 0
  that = createBuffer(that, length)

  var actual = that.write(string, encoding)

  if (actual !== length) {
    // Writing a hex string, for example, that contains invalid characters will
    // cause everything after the first invalid character to be ignored. (e.g.
    // 'abxxcd' will be treated as 'ab')
    that = that.slice(0, actual)
  }

  return that
}

function fromArrayLike (that, array) {
  var length = array.length < 0 ? 0 : checked(array.length) | 0
  that = createBuffer(that, length)
  for (var i = 0; i < length; i += 1) {
    that[i] = array[i] & 255
  }
  return that
}

function fromArrayBuffer (that, array, byteOffset, length) {
  array.byteLength // this throws if `array` is not a valid ArrayBuffer

  if (byteOffset < 0 || array.byteLength < byteOffset) {
    throw new RangeError('\'offset\' is out of bounds')
  }

  if (array.byteLength < byteOffset + (length || 0)) {
    throw new RangeError('\'length\' is out of bounds')
  }

  if (byteOffset === undefined && length === undefined) {
    array = new Uint8Array(array)
  } else if (length === undefined) {
    array = new Uint8Array(array, byteOffset)
  } else {
    array = new Uint8Array(array, byteOffset, length)
  }

  if (Buffer.TYPED_ARRAY_SUPPORT) {
    // Return an augmented `Uint8Array` instance, for best performance
    that = array
    that.__proto__ = Buffer.prototype
  } else {
    // Fallback: Return an object instance of the Buffer class
    that = fromArrayLike(that, array)
  }
  return that
}

function fromObject (that, obj) {
  if (Buffer.isBuffer(obj)) {
    var len = checked(obj.length) | 0
    that = createBuffer(that, len)

    if (that.length === 0) {
      return that
    }

    obj.copy(that, 0, 0, len)
    return that
  }

  if (obj) {
    if ((typeof ArrayBuffer !== 'undefined' &&
        obj.buffer instanceof ArrayBuffer) || 'length' in obj) {
      if (typeof obj.length !== 'number' || isnan(obj.length)) {
        return createBuffer(that, 0)
      }
      return fromArrayLike(that, obj)
    }

    if (obj.type === 'Buffer' && isArray(obj.data)) {
      return fromArrayLike(that, obj.data)
    }
  }

  throw new TypeError('First argument must be a string, Buffer, ArrayBuffer, Array, or array-like object.')
}

function checked (length) {
  // Note: cannot use `length < kMaxLength()` here because that fails when
  // length is NaN (which is otherwise coerced to zero.)
  if (length >= kMaxLength()) {
    throw new RangeError('Attempt to allocate Buffer larger than maximum ' +
                         'size: 0x' + kMaxLength().toString(16) + ' bytes')
  }
  return length | 0
}

function SlowBuffer (length) {
  if (+length != length) { // eslint-disable-line eqeqeq
    length = 0
  }
  return Buffer.alloc(+length)
}

Buffer.isBuffer = function isBuffer (b) {
  return !!(b != null && b._isBuffer)
}

Buffer.compare = function compare (a, b) {
  if (!Buffer.isBuffer(a) || !Buffer.isBuffer(b)) {
    throw new TypeError('Arguments must be Buffers')
  }

  if (a === b) return 0

  var x = a.length
  var y = b.length

  for (var i = 0, len = Math.min(x, y); i < len; ++i) {
    if (a[i] !== b[i]) {
      x = a[i]
      y = b[i]
      break
    }
  }

  if (x < y) return -1
  if (y < x) return 1
  return 0
}

Buffer.isEncoding = function isEncoding (encoding) {
  switch (String(encoding).toLowerCase()) {
    case 'hex':
    case 'utf8':
    case 'utf-8':
    case 'ascii':
    case 'latin1':
    case 'binary':
    case 'base64':
    case 'ucs2':
    case 'ucs-2':
    case 'utf16le':
    case 'utf-16le':
      return true
    default:
      return false
  }
}

Buffer.concat = function concat (list, length) {
  if (!isArray(list)) {
    throw new TypeError('"list" argument must be an Array of Buffers')
  }

  if (list.length === 0) {
    return Buffer.alloc(0)
  }

  var i
  if (length === undefined) {
    length = 0
    for (i = 0; i < list.length; ++i) {
      length += list[i].length
    }
  }

  var buffer = Buffer.allocUnsafe(length)
  var pos = 0
  for (i = 0; i < list.length; ++i) {
    var buf = list[i]
    if (!Buffer.isBuffer(buf)) {
      throw new TypeError('"list" argument must be an Array of Buffers')
    }
    buf.copy(buffer, pos)
    pos += buf.length
  }
  return buffer
}

function byteLength (string, encoding) {
  if (Buffer.isBuffer(string)) {
    return string.length
  }
  if (typeof ArrayBuffer !== 'undefined' && typeof ArrayBuffer.isView === 'function' &&
      (ArrayBuffer.isView(string) || string instanceof ArrayBuffer)) {
    return string.byteLength
  }
  if (typeof string !== 'string') {
    string = '' + string
  }

  var len = string.length
  if (len === 0) return 0

  // Use a for loop to avoid recursion
  var loweredCase = false
  for (;;) {
    switch (encoding) {
      case 'ascii':
      case 'latin1':
      case 'binary':
        return len
      case 'utf8':
      case 'utf-8':
      case undefined:
        return utf8ToBytes(string).length
      case 'ucs2':
      case 'ucs-2':
      case 'utf16le':
      case 'utf-16le':
        return len * 2
      case 'hex':
        return len >>> 1
      case 'base64':
        return base64ToBytes(string).length
      default:
        if (loweredCase) return utf8ToBytes(string).length // assume utf8
        encoding = ('' + encoding).toLowerCase()
        loweredCase = true
    }
  }
}
Buffer.byteLength = byteLength

function slowToString (encoding, start, end) {
  var loweredCase = false

  // No need to verify that "this.length <= MAX_UINT32" since it's a read-only
  // property of a typed array.

  // This behaves neither like String nor Uint8Array in that we set start/end
  // to their upper/lower bounds if the value passed is out of range.
  // undefined is handled specially as per ECMA-262 6th Edition,
  // Section 13.3.3.7 Runtime Semantics: KeyedBindingInitialization.
  if (start === undefined || start < 0) {
    start = 0
  }
  // Return early if start > this.length. Done here to prevent potential uint32
  // coercion fail below.
  if (start > this.length) {
    return ''
  }

  if (end === undefined || end > this.length) {
    end = this.length
  }

  if (end <= 0) {
    return ''
  }

  // Force coersion to uint32. This will also coerce falsey/NaN values to 0.
  end >>>= 0
  start >>>= 0

  if (end <= start) {
    return ''
  }

  if (!encoding) encoding = 'utf8'

  while (true) {
    switch (encoding) {
      case 'hex':
        return hexSlice(this, start, end)

      case 'utf8':
      case 'utf-8':
        return utf8Slice(this, start, end)

      case 'ascii':
        return asciiSlice(this, start, end)

      case 'latin1':
      case 'binary':
        return latin1Slice(this, start, end)

      case 'base64':
        return base64Slice(this, start, end)

      case 'ucs2':
      case 'ucs-2':
      case 'utf16le':
      case 'utf-16le':
        return utf16leSlice(this, start, end)

      default:
        if (loweredCase) throw new TypeError('Unknown encoding: ' + encoding)
        encoding = (encoding + '').toLowerCase()
        loweredCase = true
    }
  }
}

// The property is used by `Buffer.isBuffer` and `is-buffer` (in Safari 5-7) to detect
// Buffer instances.
Buffer.prototype._isBuffer = true

function swap (b, n, m) {
  var i = b[n]
  b[n] = b[m]
  b[m] = i
}

Buffer.prototype.swap16 = function swap16 () {
  var len = this.length
  if (len % 2 !== 0) {
    throw new RangeError('Buffer size must be a multiple of 16-bits')
  }
  for (var i = 0; i < len; i += 2) {
    swap(this, i, i + 1)
  }
  return this
}

Buffer.prototype.swap32 = function swap32 () {
  var len = this.length
  if (len % 4 !== 0) {
    throw new RangeError('Buffer size must be a multiple of 32-bits')
  }
  for (var i = 0; i < len; i += 4) {
    swap(this, i, i + 3)
    swap(this, i + 1, i + 2)
  }
  return this
}

Buffer.prototype.swap64 = function swap64 () {
  var len = this.length
  if (len % 8 !== 0) {
    throw new RangeError('Buffer size must be a multiple of 64-bits')
  }
  for (var i = 0; i < len; i += 8) {
    swap(this, i, i + 7)
    swap(this, i + 1, i + 6)
    swap(this, i + 2, i + 5)
    swap(this, i + 3, i + 4)
  }
  return this
}

Buffer.prototype.toString = function toString () {
  var length = this.length | 0
  if (length === 0) return ''
  if (arguments.length === 0) return utf8Slice(this, 0, length)
  return slowToString.apply(this, arguments)
}

Buffer.prototype.equals = function equals (b) {
  if (!Buffer.isBuffer(b)) throw new TypeError('Argument must be a Buffer')
  if (this === b) return true
  return Buffer.compare(this, b) === 0
}

Buffer.prototype.inspect = function inspect () {
  var str = ''
  var max = exports.INSPECT_MAX_BYTES
  if (this.length > 0) {
    str = this.toString('hex', 0, max).match(/.{2}/g).join(' ')
    if (this.length > max) str += ' ... '
  }
  return '<Buffer ' + str + '>'
}

Buffer.prototype.compare = function compare (target, start, end, thisStart, thisEnd) {
  if (!Buffer.isBuffer(target)) {
    throw new TypeError('Argument must be a Buffer')
  }

  if (start === undefined) {
    start = 0
  }
  if (end === undefined) {
    end = target ? target.length : 0
  }
  if (thisStart === undefined) {
    thisStart = 0
  }
  if (thisEnd === undefined) {
    thisEnd = this.length
  }

  if (start < 0 || end > target.length || thisStart < 0 || thisEnd > this.length) {
    throw new RangeError('out of range index')
  }

  if (thisStart >= thisEnd && start >= end) {
    return 0
  }
  if (thisStart >= thisEnd) {
    return -1
  }
  if (start >= end) {
    return 1
  }

  start >>>= 0
  end >>>= 0
  thisStart >>>= 0
  thisEnd >>>= 0

  if (this === target) return 0

  var x = thisEnd - thisStart
  var y = end - start
  var len = Math.min(x, y)

  var thisCopy = this.slice(thisStart, thisEnd)
  var targetCopy = target.slice(start, end)

  for (var i = 0; i < len; ++i) {
    if (thisCopy[i] !== targetCopy[i]) {
      x = thisCopy[i]
      y = targetCopy[i]
      break
    }
  }

  if (x < y) return -1
  if (y < x) return 1
  return 0
}

// Finds either the first index of `val` in `buffer` at offset >= `byteOffset`,
// OR the last index of `val` in `buffer` at offset <= `byteOffset`.
//
// Arguments:
// - buffer - a Buffer to search
// - val - a string, Buffer, or number
// - byteOffset - an index into `buffer`; will be clamped to an int32
// - encoding - an optional encoding, relevant is val is a string
// - dir - true for indexOf, false for lastIndexOf
function bidirectionalIndexOf (buffer, val, byteOffset, encoding, dir) {
  // Empty buffer means no match
  if (buffer.length === 0) return -1

  // Normalize byteOffset
  if (typeof byteOffset === 'string') {
    encoding = byteOffset
    byteOffset = 0
  } else if (byteOffset > 0x7fffffff) {
    byteOffset = 0x7fffffff
  } else if (byteOffset < -0x80000000) {
    byteOffset = -0x80000000
  }
  byteOffset = +byteOffset  // Coerce to Number.
  if (isNaN(byteOffset)) {
    // byteOffset: it it's undefined, null, NaN, "foo", etc, search whole buffer
    byteOffset = dir ? 0 : (buffer.length - 1)
  }

  // Normalize byteOffset: negative offsets start from the end of the buffer
  if (byteOffset < 0) byteOffset = buffer.length + byteOffset
  if (byteOffset >= buffer.length) {
    if (dir) return -1
    else byteOffset = buffer.length - 1
  } else if (byteOffset < 0) {
    if (dir) byteOffset = 0
    else return -1
  }

  // Normalize val
  if (typeof val === 'string') {
    val = Buffer.from(val, encoding)
  }

  // Finally, search either indexOf (if dir is true) or lastIndexOf
  if (Buffer.isBuffer(val)) {
    // Special case: looking for empty string/buffer always fails
    if (val.length === 0) {
      return -1
    }
    return arrayIndexOf(buffer, val, byteOffset, encoding, dir)
  } else if (typeof val === 'number') {
    val = val & 0xFF // Search for a byte value [0-255]
    if (Buffer.TYPED_ARRAY_SUPPORT &&
        typeof Uint8Array.prototype.indexOf === 'function') {
      if (dir) {
        return Uint8Array.prototype.indexOf.call(buffer, val, byteOffset)
      } else {
        return Uint8Array.prototype.lastIndexOf.call(buffer, val, byteOffset)
      }
    }
    return arrayIndexOf(buffer, [ val ], byteOffset, encoding, dir)
  }

  throw new TypeError('val must be string, number or Buffer')
}

function arrayIndexOf (arr, val, byteOffset, encoding, dir) {
  var indexSize = 1
  var arrLength = arr.length
  var valLength = val.length

  if (encoding !== undefined) {
    encoding = String(encoding).toLowerCase()
    if (encoding === 'ucs2' || encoding === 'ucs-2' ||
        encoding === 'utf16le' || encoding === 'utf-16le') {
      if (arr.length < 2 || val.length < 2) {
        return -1
      }
      indexSize = 2
      arrLength /= 2
      valLength /= 2
      byteOffset /= 2
    }
  }

  function read (buf, i) {
    if (indexSize === 1) {
      return buf[i]
    } else {
      return buf.readUInt16BE(i * indexSize)
    }
  }

  var i
  if (dir) {
    var foundIndex = -1
    for (i = byteOffset; i < arrLength; i++) {
      if (read(arr, i) === read(val, foundIndex === -1 ? 0 : i - foundIndex)) {
        if (foundIndex === -1) foundIndex = i
        if (i - foundIndex + 1 === valLength) return foundIndex * indexSize
      } else {
        if (foundIndex !== -1) i -= i - foundIndex
        foundIndex = -1
      }
    }
  } else {
    if (byteOffset + valLength > arrLength) byteOffset = arrLength - valLength
    for (i = byteOffset; i >= 0; i--) {
      var found = true
      for (var j = 0; j < valLength; j++) {
        if (read(arr, i + j) !== read(val, j)) {
          found = false
          break
        }
      }
      if (found) return i
    }
  }

  return -1
}

Buffer.prototype.includes = function includes (val, byteOffset, encoding) {
  return this.indexOf(val, byteOffset, encoding) !== -1
}

Buffer.prototype.indexOf = function indexOf (val, byteOffset, encoding) {
  return bidirectionalIndexOf(this, val, byteOffset, encoding, true)
}

Buffer.prototype.lastIndexOf = function lastIndexOf (val, byteOffset, encoding) {
  return bidirectionalIndexOf(this, val, byteOffset, encoding, false)
}

function hexWrite (buf, string, offset, length) {
  offset = Number(offset) || 0
  var remaining = buf.length - offset
  if (!length) {
    length = remaining
  } else {
    length = Number(length)
    if (length > remaining) {
      length = remaining
    }
  }

  // must be an even number of digits
  var strLen = string.length
  if (strLen % 2 !== 0) throw new TypeError('Invalid hex string')

  if (length > strLen / 2) {
    length = strLen / 2
  }
  for (var i = 0; i < length; ++i) {
    var parsed = parseInt(string.substr(i * 2, 2), 16)
    if (isNaN(parsed)) return i
    buf[offset + i] = parsed
  }
  return i
}

function utf8Write (buf, string, offset, length) {
  return blitBuffer(utf8ToBytes(string, buf.length - offset), buf, offset, length)
}

function asciiWrite (buf, string, offset, length) {
  return blitBuffer(asciiToBytes(string), buf, offset, length)
}

function latin1Write (buf, string, offset, length) {
  return asciiWrite(buf, string, offset, length)
}

function base64Write (buf, string, offset, length) {
  return blitBuffer(base64ToBytes(string), buf, offset, length)
}

function ucs2Write (buf, string, offset, length) {
  return blitBuffer(utf16leToBytes(string, buf.length - offset), buf, offset, length)
}

Buffer.prototype.write = function write (string, offset, length, encoding) {
  // Buffer#write(string)
  if (offset === undefined) {
    encoding = 'utf8'
    length = this.length
    offset = 0
  // Buffer#write(string, encoding)
  } else if (length === undefined && typeof offset === 'string') {
    encoding = offset
    length = this.length
    offset = 0
  // Buffer#write(string, offset[, length][, encoding])
  } else if (isFinite(offset)) {
    offset = offset | 0
    if (isFinite(length)) {
      length = length | 0
      if (encoding === undefined) encoding = 'utf8'
    } else {
      encoding = length
      length = undefined
    }
  // legacy write(string, encoding, offset, length) - remove in v0.13
  } else {
    throw new Error(
      'Buffer.write(string, encoding, offset[, length]) is no longer supported'
    )
  }

  var remaining = this.length - offset
  if (length === undefined || length > remaining) length = remaining

  if ((string.length > 0 && (length < 0 || offset < 0)) || offset > this.length) {
    throw new RangeError('Attempt to write outside buffer bounds')
  }

  if (!encoding) encoding = 'utf8'

  var loweredCase = false
  for (;;) {
    switch (encoding) {
      case 'hex':
        return hexWrite(this, string, offset, length)

      case 'utf8':
      case 'utf-8':
        return utf8Write(this, string, offset, length)

      case 'ascii':
        return asciiWrite(this, string, offset, length)

      case 'latin1':
      case 'binary':
        return latin1Write(this, string, offset, length)

      case 'base64':
        // Warning: maxLength not taken into account in base64Write
        return base64Write(this, string, offset, length)

      case 'ucs2':
      case 'ucs-2':
      case 'utf16le':
      case 'utf-16le':
        return ucs2Write(this, string, offset, length)

      default:
        if (loweredCase) throw new TypeError('Unknown encoding: ' + encoding)
        encoding = ('' + encoding).toLowerCase()
        loweredCase = true
    }
  }
}

Buffer.prototype.toJSON = function toJSON () {
  return {
    type: 'Buffer',
    data: Array.prototype.slice.call(this._arr || this, 0)
  }
}

function base64Slice (buf, start, end) {
  if (start === 0 && end === buf.length) {
    return base64.fromByteArray(buf)
  } else {
    return base64.fromByteArray(buf.slice(start, end))
  }
}

function utf8Slice (buf, start, end) {
  end = Math.min(buf.length, end)
  var res = []

  var i = start
  while (i < end) {
    var firstByte = buf[i]
    var codePoint = null
    var bytesPerSequence = (firstByte > 0xEF) ? 4
      : (firstByte > 0xDF) ? 3
      : (firstByte > 0xBF) ? 2
      : 1

    if (i + bytesPerSequence <= end) {
      var secondByte, thirdByte, fourthByte, tempCodePoint

      switch (bytesPerSequence) {
        case 1:
          if (firstByte < 0x80) {
            codePoint = firstByte
          }
          break
        case 2:
          secondByte = buf[i + 1]
          if ((secondByte & 0xC0) === 0x80) {
            tempCodePoint = (firstByte & 0x1F) << 0x6 | (secondByte & 0x3F)
            if (tempCodePoint > 0x7F) {
              codePoint = tempCodePoint
            }
          }
          break
        case 3:
          secondByte = buf[i + 1]
          thirdByte = buf[i + 2]
          if ((secondByte & 0xC0) === 0x80 && (thirdByte & 0xC0) === 0x80) {
            tempCodePoint = (firstByte & 0xF) << 0xC | (secondByte & 0x3F) << 0x6 | (thirdByte & 0x3F)
            if (tempCodePoint > 0x7FF && (tempCodePoint < 0xD800 || tempCodePoint > 0xDFFF)) {
              codePoint = tempCodePoint
            }
          }
          break
        case 4:
          secondByte = buf[i + 1]
          thirdByte = buf[i + 2]
          fourthByte = buf[i + 3]
          if ((secondByte & 0xC0) === 0x80 && (thirdByte & 0xC0) === 0x80 && (fourthByte & 0xC0) === 0x80) {
            tempCodePoint = (firstByte & 0xF) << 0x12 | (secondByte & 0x3F) << 0xC | (thirdByte & 0x3F) << 0x6 | (fourthByte & 0x3F)
            if (tempCodePoint > 0xFFFF && tempCodePoint < 0x110000) {
              codePoint = tempCodePoint
            }
          }
      }
    }

    if (codePoint === null) {
      // we did not generate a valid codePoint so insert a
      // replacement char (U+FFFD) and advance only 1 byte
      codePoint = 0xFFFD
      bytesPerSequence = 1
    } else if (codePoint > 0xFFFF) {
      // encode to utf16 (surrogate pair dance)
      codePoint -= 0x10000
      res.push(codePoint >>> 10 & 0x3FF | 0xD800)
      codePoint = 0xDC00 | codePoint & 0x3FF
    }

    res.push(codePoint)
    i += bytesPerSequence
  }

  return decodeCodePointsArray(res)
}

// Based on http://stackoverflow.com/a/22747272/680742, the browser with
// the lowest limit is Chrome, with 0x10000 args.
// We go 1 magnitude less, for safety
var MAX_ARGUMENTS_LENGTH = 0x1000

function decodeCodePointsArray (codePoints) {
  var len = codePoints.length
  if (len <= MAX_ARGUMENTS_LENGTH) {
    return String.fromCharCode.apply(String, codePoints) // avoid extra slice()
  }

  // Decode in chunks to avoid "call stack size exceeded".
  var res = ''
  var i = 0
  while (i < len) {
    res += String.fromCharCode.apply(
      String,
      codePoints.slice(i, i += MAX_ARGUMENTS_LENGTH)
    )
  }
  return res
}

function asciiSlice (buf, start, end) {
  var ret = ''
  end = Math.min(buf.length, end)

  for (var i = start; i < end; ++i) {
    ret += String.fromCharCode(buf[i] & 0x7F)
  }
  return ret
}

function latin1Slice (buf, start, end) {
  var ret = ''
  end = Math.min(buf.length, end)

  for (var i = start; i < end; ++i) {
    ret += String.fromCharCode(buf[i])
  }
  return ret
}

function hexSlice (buf, start, end) {
  var len = buf.length

  if (!start || start < 0) start = 0
  if (!end || end < 0 || end > len) end = len

  var out = ''
  for (var i = start; i < end; ++i) {
    out += toHex(buf[i])
  }
  return out
}

function utf16leSlice (buf, start, end) {
  var bytes = buf.slice(start, end)
  var res = ''
  for (var i = 0; i < bytes.length; i += 2) {
    res += String.fromCharCode(bytes[i] + bytes[i + 1] * 256)
  }
  return res
}

Buffer.prototype.slice = function slice (start, end) {
  var len = this.length
  start = ~~start
  end = end === undefined ? len : ~~end

  if (start < 0) {
    start += len
    if (start < 0) start = 0
  } else if (start > len) {
    start = len
  }

  if (end < 0) {
    end += len
    if (end < 0) end = 0
  } else if (end > len) {
    end = len
  }

  if (end < start) end = start

  var newBuf
  if (Buffer.TYPED_ARRAY_SUPPORT) {
    newBuf = this.subarray(start, end)
    newBuf.__proto__ = Buffer.prototype
  } else {
    var sliceLen = end - start
    newBuf = new Buffer(sliceLen, undefined)
    for (var i = 0; i < sliceLen; ++i) {
      newBuf[i] = this[i + start]
    }
  }

  return newBuf
}

/*
 * Need to make sure that buffer isn't trying to write out of bounds.
 */
function checkOffset (offset, ext, length) {
  if ((offset % 1) !== 0 || offset < 0) throw new RangeError('offset is not uint')
  if (offset + ext > length) throw new RangeError('Trying to access beyond buffer length')
}

Buffer.prototype.readUIntLE = function readUIntLE (offset, byteLength, noAssert) {
  offset = offset | 0
  byteLength = byteLength | 0
  if (!noAssert) checkOffset(offset, byteLength, this.length)

  var val = this[offset]
  var mul = 1
  var i = 0
  while (++i < byteLength && (mul *= 0x100)) {
    val += this[offset + i] * mul
  }

  return val
}

Buffer.prototype.readUIntBE = function readUIntBE (offset, byteLength, noAssert) {
  offset = offset | 0
  byteLength = byteLength | 0
  if (!noAssert) {
    checkOffset(offset, byteLength, this.length)
  }

  var val = this[offset + --byteLength]
  var mul = 1
  while (byteLength > 0 && (mul *= 0x100)) {
    val += this[offset + --byteLength] * mul
  }

  return val
}

Buffer.prototype.readUInt8 = function readUInt8 (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 1, this.length)
  return this[offset]
}

Buffer.prototype.readUInt16LE = function readUInt16LE (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 2, this.length)
  return this[offset] | (this[offset + 1] << 8)
}

Buffer.prototype.readUInt16BE = function readUInt16BE (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 2, this.length)
  return (this[offset] << 8) | this[offset + 1]
}

Buffer.prototype.readUInt32LE = function readUInt32LE (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 4, this.length)

  return ((this[offset]) |
      (this[offset + 1] << 8) |
      (this[offset + 2] << 16)) +
      (this[offset + 3] * 0x1000000)
}

Buffer.prototype.readUInt32BE = function readUInt32BE (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 4, this.length)

  return (this[offset] * 0x1000000) +
    ((this[offset + 1] << 16) |
    (this[offset + 2] << 8) |
    this[offset + 3])
}

Buffer.prototype.readIntLE = function readIntLE (offset, byteLength, noAssert) {
  offset = offset | 0
  byteLength = byteLength | 0
  if (!noAssert) checkOffset(offset, byteLength, this.length)

  var val = this[offset]
  var mul = 1
  var i = 0
  while (++i < byteLength && (mul *= 0x100)) {
    val += this[offset + i] * mul
  }
  mul *= 0x80

  if (val >= mul) val -= Math.pow(2, 8 * byteLength)

  return val
}

Buffer.prototype.readIntBE = function readIntBE (offset, byteLength, noAssert) {
  offset = offset | 0
  byteLength = byteLength | 0
  if (!noAssert) checkOffset(offset, byteLength, this.length)

  var i = byteLength
  var mul = 1
  var val = this[offset + --i]
  while (i > 0 && (mul *= 0x100)) {
    val += this[offset + --i] * mul
  }
  mul *= 0x80

  if (val >= mul) val -= Math.pow(2, 8 * byteLength)

  return val
}

Buffer.prototype.readInt8 = function readInt8 (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 1, this.length)
  if (!(this[offset] & 0x80)) return (this[offset])
  return ((0xff - this[offset] + 1) * -1)
}

Buffer.prototype.readInt16LE = function readInt16LE (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 2, this.length)
  var val = this[offset] | (this[offset + 1] << 8)
  return (val & 0x8000) ? val | 0xFFFF0000 : val
}

Buffer.prototype.readInt16BE = function readInt16BE (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 2, this.length)
  var val = this[offset + 1] | (this[offset] << 8)
  return (val & 0x8000) ? val | 0xFFFF0000 : val
}

Buffer.prototype.readInt32LE = function readInt32LE (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 4, this.length)

  return (this[offset]) |
    (this[offset + 1] << 8) |
    (this[offset + 2] << 16) |
    (this[offset + 3] << 24)
}

Buffer.prototype.readInt32BE = function readInt32BE (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 4, this.length)

  return (this[offset] << 24) |
    (this[offset + 1] << 16) |
    (this[offset + 2] << 8) |
    (this[offset + 3])
}

Buffer.prototype.readFloatLE = function readFloatLE (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 4, this.length)
  return ieee754.read(this, offset, true, 23, 4)
}

Buffer.prototype.readFloatBE = function readFloatBE (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 4, this.length)
  return ieee754.read(this, offset, false, 23, 4)
}

Buffer.prototype.readDoubleLE = function readDoubleLE (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 8, this.length)
  return ieee754.read(this, offset, true, 52, 8)
}

Buffer.prototype.readDoubleBE = function readDoubleBE (offset, noAssert) {
  if (!noAssert) checkOffset(offset, 8, this.length)
  return ieee754.read(this, offset, false, 52, 8)
}

function checkInt (buf, value, offset, ext, max, min) {
  if (!Buffer.isBuffer(buf)) throw new TypeError('"buffer" argument must be a Buffer instance')
  if (value > max || value < min) throw new RangeError('"value" argument is out of bounds')
  if (offset + ext > buf.length) throw new RangeError('Index out of range')
}

Buffer.prototype.writeUIntLE = function writeUIntLE (value, offset, byteLength, noAssert) {
  value = +value
  offset = offset | 0
  byteLength = byteLength | 0
  if (!noAssert) {
    var maxBytes = Math.pow(2, 8 * byteLength) - 1
    checkInt(this, value, offset, byteLength, maxBytes, 0)
  }

  var mul = 1
  var i = 0
  this[offset] = value & 0xFF
  while (++i < byteLength && (mul *= 0x100)) {
    this[offset + i] = (value / mul) & 0xFF
  }

  return offset + byteLength
}

Buffer.prototype.writeUIntBE = function writeUIntBE (value, offset, byteLength, noAssert) {
  value = +value
  offset = offset | 0
  byteLength = byteLength | 0
  if (!noAssert) {
    var maxBytes = Math.pow(2, 8 * byteLength) - 1
    checkInt(this, value, offset, byteLength, maxBytes, 0)
  }

  var i = byteLength - 1
  var mul = 1
  this[offset + i] = value & 0xFF
  while (--i >= 0 && (mul *= 0x100)) {
    this[offset + i] = (value / mul) & 0xFF
  }

  return offset + byteLength
}

Buffer.prototype.writeUInt8 = function writeUInt8 (value, offset, noAssert) {
  value = +value
  offset = offset | 0
  if (!noAssert) checkInt(this, value, offset, 1, 0xff, 0)
  if (!Buffer.TYPED_ARRAY_SUPPORT) value = Math.floor(value)
  this[offset] = (value & 0xff)
  return offset + 1
}

function objectWriteUInt16 (buf, value, offset, littleEndian) {
  if (value < 0) value = 0xffff + value + 1
  for (var i = 0, j = Math.min(buf.length - offset, 2); i < j; ++i) {
    buf[offset + i] = (value & (0xff << (8 * (littleEndian ? i : 1 - i)))) >>>
      (littleEndian ? i : 1 - i) * 8
  }
}

Buffer.prototype.writeUInt16LE = function writeUInt16LE (value, offset, noAssert) {
  value = +value
  offset = offset | 0
  if (!noAssert) checkInt(this, value, offset, 2, 0xffff, 0)
  if (Buffer.TYPED_ARRAY_SUPPORT) {
    this[offset] = (value & 0xff)
    this[offset + 1] = (value >>> 8)
  } else {
    objectWriteUInt16(this, value, offset, true)
  }
  return offset + 2
}

Buffer.prototype.writeUInt16BE = function writeUInt16BE (value, offset, noAssert) {
  value = +value
  offset = offset | 0
  if (!noAssert) checkInt(this, value, offset, 2, 0xffff, 0)
  if (Buffer.TYPED_ARRAY_SUPPORT) {
    this[offset] = (value >>> 8)
    this[offset + 1] = (value & 0xff)
  } else {
    objectWriteUInt16(this, value, offset, false)
  }
  return offset + 2
}

function objectWriteUInt32 (buf, value, offset, littleEndian) {
  if (value < 0) value = 0xffffffff + value + 1
  for (var i = 0, j = Math.min(buf.length - offset, 4); i < j; ++i) {
    buf[offset + i] = (value >>> (littleEndian ? i : 3 - i) * 8) & 0xff
  }
}

Buffer.prototype.writeUInt32LE = function writeUInt32LE (value, offset, noAssert) {
  value = +value
  offset = offset | 0
  if (!noAssert) checkInt(this, value, offset, 4, 0xffffffff, 0)
  if (Buffer.TYPED_ARRAY_SUPPORT) {
    this[offset + 3] = (value >>> 24)
    this[offset + 2] = (value >>> 16)
    this[offset + 1] = (value >>> 8)
    this[offset] = (value & 0xff)
  } else {
    objectWriteUInt32(this, value, offset, true)
  }
  return offset + 4
}

Buffer.prototype.writeUInt32BE = function writeUInt32BE (value, offset, noAssert) {
  value = +value
  offset = offset | 0
  if (!noAssert) checkInt(this, value, offset, 4, 0xffffffff, 0)
  if (Buffer.TYPED_ARRAY_SUPPORT) {
    this[offset] = (value >>> 24)
    this[offset + 1] = (value >>> 16)
    this[offset + 2] = (value >>> 8)
    this[offset + 3] = (value & 0xff)
  } else {
    objectWriteUInt32(this, value, offset, false)
  }
  return offset + 4
}

Buffer.prototype.writeIntLE = function writeIntLE (value, offset, byteLength, noAssert) {
  value = +value
  offset = offset | 0
  if (!noAssert) {
    var limit = Math.pow(2, 8 * byteLength - 1)

    checkInt(this, value, offset, byteLength, limit - 1, -limit)
  }

  var i = 0
  var mul = 1
  var sub = 0
  this[offset] = value & 0xFF
  while (++i < byteLength && (mul *= 0x100)) {
    if (value < 0 && sub === 0 && this[offset + i - 1] !== 0) {
      sub = 1
    }
    this[offset + i] = ((value / mul) >> 0) - sub & 0xFF
  }

  return offset + byteLength
}

Buffer.prototype.writeIntBE = function writeIntBE (value, offset, byteLength, noAssert) {
  value = +value
  offset = offset | 0
  if (!noAssert) {
    var limit = Math.pow(2, 8 * byteLength - 1)

    checkInt(this, value, offset, byteLength, limit - 1, -limit)
  }

  var i = byteLength - 1
  var mul = 1
  var sub = 0
  this[offset + i] = value & 0xFF
  while (--i >= 0 && (mul *= 0x100)) {
    if (value < 0 && sub === 0 && this[offset + i + 1] !== 0) {
      sub = 1
    }
    this[offset + i] = ((value / mul) >> 0) - sub & 0xFF
  }

  return offset + byteLength
}

Buffer.prototype.writeInt8 = function writeInt8 (value, offset, noAssert) {
  value = +value
  offset = offset | 0
  if (!noAssert) checkInt(this, value, offset, 1, 0x7f, -0x80)
  if (!Buffer.TYPED_ARRAY_SUPPORT) value = Math.floor(value)
  if (value < 0) value = 0xff + value + 1
  this[offset] = (value & 0xff)
  return offset + 1
}

Buffer.prototype.writeInt16LE = function writeInt16LE (value, offset, noAssert) {
  value = +value
  offset = offset | 0
  if (!noAssert) checkInt(this, value, offset, 2, 0x7fff, -0x8000)
  if (Buffer.TYPED_ARRAY_SUPPORT) {
    this[offset] = (value & 0xff)
    this[offset + 1] = (value >>> 8)
  } else {
    objectWriteUInt16(this, value, offset, true)
  }
  return offset + 2
}

Buffer.prototype.writeInt16BE = function writeInt16BE (value, offset, noAssert) {
  value = +value
  offset = offset | 0
  if (!noAssert) checkInt(this, value, offset, 2, 0x7fff, -0x8000)
  if (Buffer.TYPED_ARRAY_SUPPORT) {
    this[offset] = (value >>> 8)
    this[offset + 1] = (value & 0xff)
  } else {
    objectWriteUInt16(this, value, offset, false)
  }
  return offset + 2
}

Buffer.prototype.writeInt32LE = function writeInt32LE (value, offset, noAssert) {
  value = +value
  offset = offset | 0
  if (!noAssert) checkInt(this, value, offset, 4, 0x7fffffff, -0x80000000)
  if (Buffer.TYPED_ARRAY_SUPPORT) {
    this[offset] = (value & 0xff)
    this[offset + 1] = (value >>> 8)
    this[offset + 2] = (value >>> 16)
    this[offset + 3] = (value >>> 24)
  } else {
    objectWriteUInt32(this, value, offset, true)
  }
  return offset + 4
}

Buffer.prototype.writeInt32BE = function writeInt32BE (value, offset, noAssert) {
  value = +value
  offset = offset | 0
  if (!noAssert) checkInt(this, value, offset, 4, 0x7fffffff, -0x80000000)
  if (value < 0) value = 0xffffffff + value + 1
  if (Buffer.TYPED_ARRAY_SUPPORT) {
    this[offset] = (value >>> 24)
    this[offset + 1] = (value >>> 16)
    this[offset + 2] = (value >>> 8)
    this[offset + 3] = (value & 0xff)
  } else {
    objectWriteUInt32(this, value, offset, false)
  }
  return offset + 4
}

function checkIEEE754 (buf, value, offset, ext, max, min) {
  if (offset + ext > buf.length) throw new RangeError('Index out of range')
  if (offset < 0) throw new RangeError('Index out of range')
}

function writeFloat (buf, value, offset, littleEndian, noAssert) {
  if (!noAssert) {
    checkIEEE754(buf, value, offset, 4, 3.4028234663852886e+38, -3.4028234663852886e+38)
  }
  ieee754.write(buf, value, offset, littleEndian, 23, 4)
  return offset + 4
}

Buffer.prototype.writeFloatLE = function writeFloatLE (value, offset, noAssert) {
  return writeFloat(this, value, offset, true, noAssert)
}

Buffer.prototype.writeFloatBE = function writeFloatBE (value, offset, noAssert) {
  return writeFloat(this, value, offset, false, noAssert)
}

function writeDouble (buf, value, offset, littleEndian, noAssert) {
  if (!noAssert) {
    checkIEEE754(buf, value, offset, 8, 1.7976931348623157E+308, -1.7976931348623157E+308)
  }
  ieee754.write(buf, value, offset, littleEndian, 52, 8)
  return offset + 8
}

Buffer.prototype.writeDoubleLE = function writeDoubleLE (value, offset, noAssert) {
  return writeDouble(this, value, offset, true, noAssert)
}

Buffer.prototype.writeDoubleBE = function writeDoubleBE (value, offset, noAssert) {
  return writeDouble(this, value, offset, false, noAssert)
}

// copy(targetBuffer, targetStart=0, sourceStart=0, sourceEnd=buffer.length)
Buffer.prototype.copy = function copy (target, targetStart, start, end) {
  if (!start) start = 0
  if (!end && end !== 0) end = this.length
  if (targetStart >= target.length) targetStart = target.length
  if (!targetStart) targetStart = 0
  if (end > 0 && end < start) end = start

  // Copy 0 bytes; we're done
  if (end === start) return 0
  if (target.length === 0 || this.length === 0) return 0

  // Fatal error conditions
  if (targetStart < 0) {
    throw new RangeError('targetStart out of bounds')
  }
  if (start < 0 || start >= this.length) throw new RangeError('sourceStart out of bounds')
  if (end < 0) throw new RangeError('sourceEnd out of bounds')

  // Are we oob?
  if (end > this.length) end = this.length
  if (target.length - targetStart < end - start) {
    end = target.length - targetStart + start
  }

  var len = end - start
  var i

  if (this === target && start < targetStart && targetStart < end) {
    // descending copy from end
    for (i = len - 1; i >= 0; --i) {
      target[i + targetStart] = this[i + start]
    }
  } else if (len < 1000 || !Buffer.TYPED_ARRAY_SUPPORT) {
    // ascending copy from start
    for (i = 0; i < len; ++i) {
      target[i + targetStart] = this[i + start]
    }
  } else {
    Uint8Array.prototype.set.call(
      target,
      this.subarray(start, start + len),
      targetStart
    )
  }

  return len
}

// Usage:
//    buffer.fill(number[, offset[, end]])
//    buffer.fill(buffer[, offset[, end]])
//    buffer.fill(string[, offset[, end]][, encoding])
Buffer.prototype.fill = function fill (val, start, end, encoding) {
  // Handle string cases:
  if (typeof val === 'string') {
    if (typeof start === 'string') {
      encoding = start
      start = 0
      end = this.length
    } else if (typeof end === 'string') {
      encoding = end
      end = this.length
    }
    if (val.length === 1) {
      var code = val.charCodeAt(0)
      if (code < 256) {
        val = code
      }
    }
    if (encoding !== undefined && typeof encoding !== 'string') {
      throw new TypeError('encoding must be a string')
    }
    if (typeof encoding === 'string' && !Buffer.isEncoding(encoding)) {
      throw new TypeError('Unknown encoding: ' + encoding)
    }
  } else if (typeof val === 'number') {
    val = val & 255
  }

  // Invalid ranges are not set to a default, so can range check early.
  if (start < 0 || this.length < start || this.length < end) {
    throw new RangeError('Out of range index')
  }

  if (end <= start) {
    return this
  }

  start = start >>> 0
  end = end === undefined ? this.length : end >>> 0

  if (!val) val = 0

  var i
  if (typeof val === 'number') {
    for (i = start; i < end; ++i) {
      this[i] = val
    }
  } else {
    var bytes = Buffer.isBuffer(val)
      ? val
      : utf8ToBytes(new Buffer(val, encoding).toString())
    var len = bytes.length
    for (i = 0; i < end - start; ++i) {
      this[i + start] = bytes[i % len]
    }
  }

  return this
}

// HELPER FUNCTIONS
// ================

var INVALID_BASE64_RE = /[^+\/0-9A-Za-z-_]/g

function base64clean (str) {
  // Node strips out invalid characters like \n and \t from the string, base64-js does not
  str = stringtrim(str).replace(INVALID_BASE64_RE, '')
  // Node converts strings with length < 2 to ''
  if (str.length < 2) return ''
  // Node allows for non-padded base64 strings (missing trailing ===), base64-js does not
  while (str.length % 4 !== 0) {
    str = str + '='
  }
  return str
}

function stringtrim (str) {
  if (str.trim) return str.trim()
  return str.replace(/^\s+|\s+$/g, '')
}

function toHex (n) {
  if (n < 16) return '0' + n.toString(16)
  return n.toString(16)
}

function utf8ToBytes (string, units) {
  units = units || Infinity
  var codePoint
  var length = string.length
  var leadSurrogate = null
  var bytes = []

  for (var i = 0; i < length; ++i) {
    codePoint = string.charCodeAt(i)

    // is surrogate component
    if (codePoint > 0xD7FF && codePoint < 0xE000) {
      // last char was a lead
      if (!leadSurrogate) {
        // no lead yet
        if (codePoint > 0xDBFF) {
          // unexpected trail
          if ((units -= 3) > -1) bytes.push(0xEF, 0xBF, 0xBD)
          continue
        } else if (i + 1 === length) {
          // unpaired lead
          if ((units -= 3) > -1) bytes.push(0xEF, 0xBF, 0xBD)
          continue
        }

        // valid lead
        leadSurrogate = codePoint

        continue
      }

      // 2 leads in a row
      if (codePoint < 0xDC00) {
        if ((units -= 3) > -1) bytes.push(0xEF, 0xBF, 0xBD)
        leadSurrogate = codePoint
        continue
      }

      // valid surrogate pair
      codePoint = (leadSurrogate - 0xD800 << 10 | codePoint - 0xDC00) + 0x10000
    } else if (leadSurrogate) {
      // valid bmp char, but last char was a lead
      if ((units -= 3) > -1) bytes.push(0xEF, 0xBF, 0xBD)
    }

    leadSurrogate = null

    // encode utf8
    if (codePoint < 0x80) {
      if ((units -= 1) < 0) break
      bytes.push(codePoint)
    } else if (codePoint < 0x800) {
      if ((units -= 2) < 0) break
      bytes.push(
        codePoint >> 0x6 | 0xC0,
        codePoint & 0x3F | 0x80
      )
    } else if (codePoint < 0x10000) {
      if ((units -= 3) < 0) break
      bytes.push(
        codePoint >> 0xC | 0xE0,
        codePoint >> 0x6 & 0x3F | 0x80,
        codePoint & 0x3F | 0x80
      )
    } else if (codePoint < 0x110000) {
      if ((units -= 4) < 0) break
      bytes.push(
        codePoint >> 0x12 | 0xF0,
        codePoint >> 0xC & 0x3F | 0x80,
        codePoint >> 0x6 & 0x3F | 0x80,
        codePoint & 0x3F | 0x80
      )
    } else {
      throw new Error('Invalid code point')
    }
  }

  return bytes
}

function asciiToBytes (str) {
  var byteArray = []
  for (var i = 0; i < str.length; ++i) {
    // Node's code seems to be doing this and not & 0x7F..
    byteArray.push(str.charCodeAt(i) & 0xFF)
  }
  return byteArray
}

function utf16leToBytes (str, units) {
  var c, hi, lo
  var byteArray = []
  for (var i = 0; i < str.length; ++i) {
    if ((units -= 2) < 0) break

    c = str.charCodeAt(i)
    hi = c >> 8
    lo = c % 256
    byteArray.push(lo)
    byteArray.push(hi)
  }

  return byteArray
}

function base64ToBytes (str) {
  return base64.toByteArray(base64clean(str))
}

function blitBuffer (src, dst, offset, length) {
  for (var i = 0; i < length; ++i) {
    if ((i + offset >= dst.length) || (i >= src.length)) break
    dst[i + offset] = src[i]
  }
  return i
}

function isnan (val) {
  return val !== val // eslint-disable-line no-self-compare
}

/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(7)))

/***/ }),
/* 259 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.byteLength = byteLength
exports.toByteArray = toByteArray
exports.fromByteArray = fromByteArray

var lookup = []
var revLookup = []
var Arr = typeof Uint8Array !== 'undefined' ? Uint8Array : Array

var code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'
for (var i = 0, len = code.length; i < len; ++i) {
  lookup[i] = code[i]
  revLookup[code.charCodeAt(i)] = i
}

// Support decoding URL-safe base64 strings, as Node.js does.
// See: https://en.wikipedia.org/wiki/Base64#URL_applications
revLookup['-'.charCodeAt(0)] = 62
revLookup['_'.charCodeAt(0)] = 63

function getLens (b64) {
  var len = b64.length

  if (len % 4 > 0) {
    throw new Error('Invalid string. Length must be a multiple of 4')
  }

  // Trim off extra bytes after placeholder bytes are found
  // See: https://github.com/beatgammit/base64-js/issues/42
  var validLen = b64.indexOf('=')
  if (validLen === -1) validLen = len

  var placeHoldersLen = validLen === len
    ? 0
    : 4 - (validLen % 4)

  return [validLen, placeHoldersLen]
}

// base64 is 4/3 + up to two characters of the original data
function byteLength (b64) {
  var lens = getLens(b64)
  var validLen = lens[0]
  var placeHoldersLen = lens[1]
  return ((validLen + placeHoldersLen) * 3 / 4) - placeHoldersLen
}

function _byteLength (b64, validLen, placeHoldersLen) {
  return ((validLen + placeHoldersLen) * 3 / 4) - placeHoldersLen
}

function toByteArray (b64) {
  var tmp
  var lens = getLens(b64)
  var validLen = lens[0]
  var placeHoldersLen = lens[1]

  var arr = new Arr(_byteLength(b64, validLen, placeHoldersLen))

  var curByte = 0

  // if there are placeholders, only get up to the last complete 4 chars
  var len = placeHoldersLen > 0
    ? validLen - 4
    : validLen

  for (var i = 0; i < len; i += 4) {
    tmp =
      (revLookup[b64.charCodeAt(i)] << 18) |
      (revLookup[b64.charCodeAt(i + 1)] << 12) |
      (revLookup[b64.charCodeAt(i + 2)] << 6) |
      revLookup[b64.charCodeAt(i + 3)]
    arr[curByte++] = (tmp >> 16) & 0xFF
    arr[curByte++] = (tmp >> 8) & 0xFF
    arr[curByte++] = tmp & 0xFF
  }

  if (placeHoldersLen === 2) {
    tmp =
      (revLookup[b64.charCodeAt(i)] << 2) |
      (revLookup[b64.charCodeAt(i + 1)] >> 4)
    arr[curByte++] = tmp & 0xFF
  }

  if (placeHoldersLen === 1) {
    tmp =
      (revLookup[b64.charCodeAt(i)] << 10) |
      (revLookup[b64.charCodeAt(i + 1)] << 4) |
      (revLookup[b64.charCodeAt(i + 2)] >> 2)
    arr[curByte++] = (tmp >> 8) & 0xFF
    arr[curByte++] = tmp & 0xFF
  }

  return arr
}

function tripletToBase64 (num) {
  return lookup[num >> 18 & 0x3F] +
    lookup[num >> 12 & 0x3F] +
    lookup[num >> 6 & 0x3F] +
    lookup[num & 0x3F]
}

function encodeChunk (uint8, start, end) {
  var tmp
  var output = []
  for (var i = start; i < end; i += 3) {
    tmp =
      ((uint8[i] << 16) & 0xFF0000) +
      ((uint8[i + 1] << 8) & 0xFF00) +
      (uint8[i + 2] & 0xFF)
    output.push(tripletToBase64(tmp))
  }
  return output.join('')
}

function fromByteArray (uint8) {
  var tmp
  var len = uint8.length
  var extraBytes = len % 3 // if we have 1 byte left, pad 2 bytes
  var parts = []
  var maxChunkLength = 16383 // must be multiple of 3

  // go through the array every three bytes, we'll deal with trailing stuff later
  for (var i = 0, len2 = len - extraBytes; i < len2; i += maxChunkLength) {
    parts.push(encodeChunk(
      uint8, i, (i + maxChunkLength) > len2 ? len2 : (i + maxChunkLength)
    ))
  }

  // pad the end with zeros, but make sure to not forget the extra bytes
  if (extraBytes === 1) {
    tmp = uint8[len - 1]
    parts.push(
      lookup[tmp >> 2] +
      lookup[(tmp << 4) & 0x3F] +
      '=='
    )
  } else if (extraBytes === 2) {
    tmp = (uint8[len - 2] << 8) + uint8[len - 1]
    parts.push(
      lookup[tmp >> 10] +
      lookup[(tmp >> 4) & 0x3F] +
      lookup[(tmp << 2) & 0x3F] +
      '='
    )
  }

  return parts.join('')
}


/***/ }),
/* 260 */
/***/ (function(module, exports) {

exports.read = function (buffer, offset, isLE, mLen, nBytes) {
  var e, m
  var eLen = (nBytes * 8) - mLen - 1
  var eMax = (1 << eLen) - 1
  var eBias = eMax >> 1
  var nBits = -7
  var i = isLE ? (nBytes - 1) : 0
  var d = isLE ? -1 : 1
  var s = buffer[offset + i]

  i += d

  e = s & ((1 << (-nBits)) - 1)
  s >>= (-nBits)
  nBits += eLen
  for (; nBits > 0; e = (e * 256) + buffer[offset + i], i += d, nBits -= 8) {}

  m = e & ((1 << (-nBits)) - 1)
  e >>= (-nBits)
  nBits += mLen
  for (; nBits > 0; m = (m * 256) + buffer[offset + i], i += d, nBits -= 8) {}

  if (e === 0) {
    e = 1 - eBias
  } else if (e === eMax) {
    return m ? NaN : ((s ? -1 : 1) * Infinity)
  } else {
    m = m + Math.pow(2, mLen)
    e = e - eBias
  }
  return (s ? -1 : 1) * m * Math.pow(2, e - mLen)
}

exports.write = function (buffer, value, offset, isLE, mLen, nBytes) {
  var e, m, c
  var eLen = (nBytes * 8) - mLen - 1
  var eMax = (1 << eLen) - 1
  var eBias = eMax >> 1
  var rt = (mLen === 23 ? Math.pow(2, -24) - Math.pow(2, -77) : 0)
  var i = isLE ? 0 : (nBytes - 1)
  var d = isLE ? 1 : -1
  var s = value < 0 || (value === 0 && 1 / value < 0) ? 1 : 0

  value = Math.abs(value)

  if (isNaN(value) || value === Infinity) {
    m = isNaN(value) ? 1 : 0
    e = eMax
  } else {
    e = Math.floor(Math.log(value) / Math.LN2)
    if (value * (c = Math.pow(2, -e)) < 1) {
      e--
      c *= 2
    }
    if (e + eBias >= 1) {
      value += rt / c
    } else {
      value += rt * Math.pow(2, 1 - eBias)
    }
    if (value * c >= 2) {
      e++
      c /= 2
    }

    if (e + eBias >= eMax) {
      m = 0
      e = eMax
    } else if (e + eBias >= 1) {
      m = ((value * c) - 1) * Math.pow(2, mLen)
      e = e + eBias
    } else {
      m = value * Math.pow(2, eBias - 1) * Math.pow(2, mLen)
      e = 0
    }
  }

  for (; mLen >= 8; buffer[offset + i] = m & 0xff, i += d, m /= 256, mLen -= 8) {}

  e = (e << mLen) | m
  eLen += mLen
  for (; eLen > 0; buffer[offset + i] = e & 0xff, i += d, e /= 256, eLen -= 8) {}

  buffer[offset + i - d] |= s * 128
}


/***/ }),
/* 261 */
/***/ (function(module, exports) {

var toString = {}.toString;

module.exports = Array.isArray || function (arr) {
  return toString.call(arr) == '[object Array]';
};


/***/ }),
/* 262 */
/***/ (function(module, exports) {

/* (ignored) */

/***/ }),
/* 263 */
/***/ (function(module, exports) {

module.exports = {
  '11111': 'abacus',
  '11112': 'abdomen',
  '11113': 'abdominal',
  '11114': 'abide',
  '11115': 'abiding',
  '11116': 'ability',
  '11121': 'ablaze',
  '11122': 'able',
  '11123': 'abnormal',
  '11124': 'abrasion',
  '11125': 'abrasive',
  '11126': 'abreast',
  '11131': 'abridge',
  '11132': 'abroad',
  '11133': 'abruptly',
  '11134': 'absence',
  '11135': 'absentee',
  '11136': 'absently',
  '11141': 'absinthe',
  '11142': 'absolute',
  '11143': 'absolve',
  '11144': 'abstain',
  '11145': 'abstract',
  '11146': 'absurd',
  '11151': 'accent',
  '11152': 'acclaim',
  '11153': 'acclimate',
  '11154': 'accompany',
  '11155': 'account',
  '11156': 'accuracy',
  '11161': 'accurate',
  '11162': 'accustom',
  '11163': 'acetone',
  '11164': 'achiness',
  '11165': 'aching',
  '11166': 'acid',
  '11211': 'acorn',
  '11212': 'acquaint',
  '11213': 'acquire',
  '11214': 'acre',
  '11215': 'acrobat',
  '11216': 'acronym',
  '11221': 'acting',
  '11222': 'action',
  '11223': 'activate',
  '11224': 'activator',
  '11225': 'active',
  '11226': 'activism',
  '11231': 'activist',
  '11232': 'activity',
  '11233': 'actress',
  '11234': 'acts',
  '11235': 'acutely',
  '11236': 'acuteness',
  '11241': 'aeration',
  '11242': 'aerobics',
  '11243': 'aerosol',
  '11244': 'aerospace',
  '11245': 'afar',
  '11246': 'affair',
  '11251': 'affected',
  '11252': 'affecting',
  '11253': 'affection',
  '11254': 'affidavit',
  '11255': 'affiliate',
  '11256': 'affirm',
  '11261': 'affix',
  '11262': 'afflicted',
  '11263': 'affluent',
  '11264': 'afford',
  '11265': 'affront',
  '11266': 'aflame',
  '11311': 'afloat',
  '11312': 'aflutter',
  '11313': 'afoot',
  '11314': 'afraid',
  '11315': 'afterglow',
  '11316': 'afterlife',
  '11321': 'aftermath',
  '11322': 'aftermost',
  '11323': 'afternoon',
  '11324': 'aged',
  '11325': 'ageless',
  '11326': 'agency',
  '11331': 'agenda',
  '11332': 'agent',
  '11333': 'aggregate',
  '11334': 'aghast',
  '11335': 'agile',
  '11336': 'agility',
  '11341': 'aging',
  '11342': 'agnostic',
  '11343': 'agonize',
  '11344': 'agonizing',
  '11345': 'agony',
  '11346': 'agreeable',
  '11351': 'agreeably',
  '11352': 'agreed',
  '11353': 'agreeing',
  '11354': 'agreement',
  '11355': 'aground',
  '11356': 'ahead',
  '11361': 'ahoy',
  '11362': 'aide',
  '11363': 'aids',
  '11364': 'aim',
  '11365': 'ajar',
  '11366': 'alabaster',
  '11411': 'alarm',
  '11412': 'albatross',
  '11413': 'album',
  '11414': 'alfalfa',
  '11415': 'algebra',
  '11416': 'algorithm',
  '11421': 'alias',
  '11422': 'alibi',
  '11423': 'alienable',
  '11424': 'alienate',
  '11425': 'aliens',
  '11426': 'alike',
  '11431': 'alive',
  '11432': 'alkaline',
  '11433': 'alkalize',
  '11434': 'almanac',
  '11435': 'almighty',
  '11436': 'almost',
  '11441': 'aloe',
  '11442': 'aloft',
  '11443': 'aloha',
  '11444': 'alone',
  '11445': 'alongside',
  '11446': 'aloof',
  '11451': 'alphabet',
  '11452': 'alright',
  '11453': 'although',
  '11454': 'altitude',
  '11455': 'alto',
  '11456': 'aluminum',
  '11461': 'alumni',
  '11462': 'always',
  '11463': 'amaretto',
  '11464': 'amaze',
  '11465': 'amazingly',
  '11466': 'amber',
  '11511': 'ambiance',
  '11512': 'ambiguity',
  '11513': 'ambiguous',
  '11514': 'ambition',
  '11515': 'ambitious',
  '11516': 'ambulance',
  '11521': 'ambush',
  '11522': 'amendable',
  '11523': 'amendment',
  '11524': 'amends',
  '11525': 'amenity',
  '11526': 'amiable',
  '11531': 'amicably',
  '11532': 'amid',
  '11533': 'amigo',
  '11534': 'amino',
  '11535': 'amiss',
  '11536': 'ammonia',
  '11541': 'ammonium',
  '11542': 'amnesty',
  '11543': 'amniotic',
  '11544': 'among',
  '11545': 'amount',
  '11546': 'amperage',
  '11551': 'ample',
  '11552': 'amplifier',
  '11553': 'amplify',
  '11554': 'amply',
  '11555': 'amuck',
  '11556': 'amulet',
  '11561': 'amusable',
  '11562': 'amused',
  '11563': 'amusement',
  '11564': 'amuser',
  '11565': 'amusing',
  '11566': 'anaconda',
  '11611': 'anaerobic',
  '11612': 'anagram',
  '11613': 'anatomist',
  '11614': 'anatomy',
  '11615': 'anchor',
  '11616': 'anchovy',
  '11621': 'ancient',
  '11622': 'android',
  '11623': 'anemia',
  '11624': 'anemic',
  '11625': 'aneurism',
  '11626': 'anew',
  '11631': 'angelfish',
  '11632': 'angelic',
  '11633': 'anger',
  '11634': 'angled',
  '11635': 'angler',
  '11636': 'angles',
  '11641': 'angling',
  '11642': 'angrily',
  '11643': 'angriness',
  '11644': 'anguished',
  '11645': 'angular',
  '11646': 'animal',
  '11651': 'animate',
  '11652': 'animating',
  '11653': 'animation',
  '11654': 'animator',
  '11655': 'anime',
  '11656': 'animosity',
  '11661': 'ankle',
  '11662': 'annex',
  '11663': 'annotate',
  '11664': 'announcer',
  '11665': 'annoying',
  '11666': 'annually',
  '12111': 'annuity',
  '12112': 'anointer',
  '12113': 'another',
  '12114': 'answering',
  '12115': 'antacid',
  '12116': 'antarctic',
  '12121': 'anteater',
  '12122': 'antelope',
  '12123': 'antennae',
  '12124': 'anthem',
  '12125': 'anthill',
  '12126': 'anthology',
  '12131': 'antibody',
  '12132': 'antics',
  '12133': 'antidote',
  '12134': 'antihero',
  '12135': 'antiquely',
  '12136': 'antiques',
  '12141': 'antiquity',
  '12142': 'antirust',
  '12143': 'antitoxic',
  '12144': 'antitrust',
  '12145': 'antiviral',
  '12146': 'antivirus',
  '12151': 'antler',
  '12152': 'antonym',
  '12153': 'antsy',
  '12154': 'anvil',
  '12155': 'anybody',
  '12156': 'anyhow',
  '12161': 'anymore',
  '12162': 'anyone',
  '12163': 'anyplace',
  '12164': 'anything',
  '12165': 'anytime',
  '12166': 'anyway',
  '12211': 'anywhere',
  '12212': 'aorta',
  '12213': 'apache',
  '12214': 'apostle',
  '12215': 'appealing',
  '12216': 'appear',
  '12221': 'appease',
  '12222': 'appeasing',
  '12223': 'appendage',
  '12224': 'appendix',
  '12225': 'appetite',
  '12226': 'appetizer',
  '12231': 'applaud',
  '12232': 'applause',
  '12233': 'apple',
  '12234': 'appliance',
  '12235': 'applicant',
  '12236': 'applied',
  '12241': 'apply',
  '12242': 'appointee',
  '12243': 'appraisal',
  '12244': 'appraiser',
  '12245': 'apprehend',
  '12246': 'approach',
  '12251': 'approval',
  '12252': 'approve',
  '12253': 'apricot',
  '12254': 'april',
  '12255': 'apron',
  '12256': 'aptitude',
  '12261': 'aptly',
  '12262': 'aqua',
  '12263': 'aqueduct',
  '12264': 'arbitrary',
  '12265': 'arbitrate',
  '12266': 'ardently',
  '12311': 'area',
  '12312': 'arena',
  '12313': 'arguable',
  '12314': 'arguably',
  '12315': 'argue',
  '12316': 'arise',
  '12321': 'armadillo',
  '12322': 'armband',
  '12323': 'armchair',
  '12324': 'armed',
  '12325': 'armful',
  '12326': 'armhole',
  '12331': 'arming',
  '12332': 'armless',
  '12333': 'armoire',
  '12334': 'armored',
  '12335': 'armory',
  '12336': 'armrest',
  '12341': 'army',
  '12342': 'aroma',
  '12343': 'arose',
  '12344': 'around',
  '12345': 'arousal',
  '12346': 'arrange',
  '12351': 'array',
  '12352': 'arrest',
  '12353': 'arrival',
  '12354': 'arrive',
  '12355': 'arrogance',
  '12356': 'arrogant',
  '12361': 'arson',
  '12362': 'art',
  '12363': 'ascend',
  '12364': 'ascension',
  '12365': 'ascent',
  '12366': 'ascertain',
  '12411': 'ashamed',
  '12412': 'ashen',
  '12413': 'ashes',
  '12414': 'ashy',
  '12415': 'aside',
  '12416': 'askew',
  '12421': 'asleep',
  '12422': 'asparagus',
  '12423': 'aspect',
  '12424': 'aspirate',
  '12425': 'aspire',
  '12426': 'aspirin',
  '12431': 'astonish',
  '12432': 'astound',
  '12433': 'astride',
  '12434': 'astrology',
  '12435': 'astronaut',
  '12436': 'astronomy',
  '12441': 'astute',
  '12442': 'atlantic',
  '12443': 'atlas',
  '12444': 'atom',
  '12445': 'atonable',
  '12446': 'atop',
  '12451': 'atrium',
  '12452': 'atrocious',
  '12453': 'atrophy',
  '12454': 'attach',
  '12455': 'attain',
  '12456': 'attempt',
  '12461': 'attendant',
  '12462': 'attendee',
  '12463': 'attention',
  '12464': 'attentive',
  '12465': 'attest',
  '12466': 'attic',
  '12511': 'attire',
  '12512': 'attitude',
  '12513': 'attractor',
  '12514': 'attribute',
  '12515': 'atypical',
  '12516': 'auction',
  '12521': 'audacious',
  '12522': 'audacity',
  '12523': 'audible',
  '12524': 'audibly',
  '12525': 'audience',
  '12526': 'audio',
  '12531': 'audition',
  '12532': 'augmented',
  '12533': 'august',
  '12534': 'authentic',
  '12535': 'author',
  '12536': 'autism',
  '12541': 'autistic',
  '12542': 'autograph',
  '12543': 'automaker',
  '12544': 'automated',
  '12545': 'automatic',
  '12546': 'autopilot',
  '12551': 'available',
  '12552': 'avalanche',
  '12553': 'avatar',
  '12554': 'avenge',
  '12555': 'avenging',
  '12556': 'avenue',
  '12561': 'average',
  '12562': 'aversion',
  '12563': 'avert',
  '12564': 'aviation',
  '12565': 'aviator',
  '12566': 'avid',
  '12611': 'avoid',
  '12612': 'await',
  '12613': 'awaken',
  '12614': 'award',
  '12615': 'aware',
  '12616': 'awhile',
  '12621': 'awkward',
  '12622': 'awning',
  '12623': 'awoke',
  '12624': 'awry',
  '12625': 'axis',
  '12626': 'babble',
  '12631': 'babbling',
  '12632': 'babied',
  '12633': 'baboon',
  '12634': 'backache',
  '12635': 'backboard',
  '12636': 'backboned',
  '12641': 'backdrop',
  '12642': 'backed',
  '12643': 'backer',
  '12644': 'backfield',
  '12645': 'backfire',
  '12646': 'backhand',
  '12651': 'backing',
  '12652': 'backlands',
  '12653': 'backlash',
  '12654': 'backless',
  '12655': 'backlight',
  '12656': 'backlit',
  '12661': 'backlog',
  '12662': 'backpack',
  '12663': 'backpedal',
  '12664': 'backrest',
  '12665': 'backroom',
  '12666': 'backshift',
  '13111': 'backside',
  '13112': 'backslid',
  '13113': 'backspace',
  '13114': 'backspin',
  '13115': 'backstab',
  '13116': 'backstage',
  '13121': 'backtalk',
  '13122': 'backtrack',
  '13123': 'backup',
  '13124': 'backward',
  '13125': 'backwash',
  '13126': 'backwater',
  '13131': 'backyard',
  '13132': 'bacon',
  '13133': 'bacteria',
  '13134': 'bacterium',
  '13135': 'badass',
  '13136': 'badge',
  '13141': 'badland',
  '13142': 'badly',
  '13143': 'badness',
  '13144': 'baffle',
  '13145': 'baffling',
  '13146': 'bagel',
  '13151': 'bagful',
  '13152': 'baggage',
  '13153': 'bagged',
  '13154': 'baggie',
  '13155': 'bagginess',
  '13156': 'bagging',
  '13161': 'baggy',
  '13162': 'bagpipe',
  '13163': 'baguette',
  '13164': 'baked',
  '13165': 'bakery',
  '13166': 'bakeshop',
  '13211': 'baking',
  '13212': 'balance',
  '13213': 'balancing',
  '13214': 'balcony',
  '13215': 'balmy',
  '13216': 'balsamic',
  '13221': 'bamboo',
  '13222': 'banana',
  '13223': 'banish',
  '13224': 'banister',
  '13225': 'banjo',
  '13226': 'bankable',
  '13231': 'bankbook',
  '13232': 'banked',
  '13233': 'banker',
  '13234': 'banking',
  '13235': 'banknote',
  '13236': 'bankroll',
  '13241': 'banner',
  '13242': 'bannister',
  '13243': 'banshee',
  '13244': 'banter',
  '13245': 'barbecue',
  '13246': 'barbed',
  '13251': 'barbell',
  '13252': 'barber',
  '13253': 'barcode',
  '13254': 'barge',
  '13255': 'bargraph',
  '13256': 'barista',
  '13261': 'baritone',
  '13262': 'barley',
  '13263': 'barmaid',
  '13264': 'barman',
  '13265': 'barn',
  '13266': 'barometer',
  '13311': 'barrack',
  '13312': 'barracuda',
  '13313': 'barrel',
  '13314': 'barrette',
  '13315': 'barricade',
  '13316': 'barrier',
  '13321': 'barstool',
  '13322': 'bartender',
  '13323': 'barterer',
  '13324': 'bash',
  '13325': 'basically',
  '13326': 'basics',
  '13331': 'basil',
  '13332': 'basin',
  '13333': 'basis',
  '13334': 'basket',
  '13335': 'batboy',
  '13336': 'batch',
  '13341': 'bath',
  '13342': 'baton',
  '13343': 'bats',
  '13344': 'battalion',
  '13345': 'battered',
  '13346': 'battering',
  '13351': 'battery',
  '13352': 'batting',
  '13353': 'battle',
  '13354': 'bauble',
  '13355': 'bazooka',
  '13356': 'blabber',
  '13361': 'bladder',
  '13362': 'blade',
  '13363': 'blah',
  '13364': 'blame',
  '13365': 'blaming',
  '13366': 'blanching',
  '13411': 'blandness',
  '13412': 'blank',
  '13413': 'blaspheme',
  '13414': 'blasphemy',
  '13415': 'blast',
  '13416': 'blatancy',
  '13421': 'blatantly',
  '13422': 'blazer',
  '13423': 'blazing',
  '13424': 'bleach',
  '13425': 'bleak',
  '13426': 'bleep',
  '13431': 'blemish',
  '13432': 'blend',
  '13433': 'bless',
  '13434': 'blighted',
  '13435': 'blimp',
  '13436': 'bling',
  '13441': 'blinked',
  '13442': 'blinker',
  '13443': 'blinking',
  '13444': 'blinks',
  '13445': 'blip',
  '13446': 'blissful',
  '13451': 'blitz',
  '13452': 'blizzard',
  '13453': 'bloated',
  '13454': 'bloating',
  '13455': 'blob',
  '13456': 'blog',
  '13461': 'bloomers',
  '13462': 'blooming',
  '13463': 'blooper',
  '13464': 'blot',
  '13465': 'blouse',
  '13466': 'blubber',
  '13511': 'bluff',
  '13512': 'bluish',
  '13513': 'blunderer',
  '13514': 'blunt',
  '13515': 'blurb',
  '13516': 'blurred',
  '13521': 'blurry',
  '13522': 'blurt',
  '13523': 'blush',
  '13524': 'blustery',
  '13525': 'boaster',
  '13526': 'boastful',
  '13531': 'boasting',
  '13532': 'boat',
  '13533': 'bobbed',
  '13534': 'bobbing',
  '13535': 'bobble',
  '13536': 'bobcat',
  '13541': 'bobsled',
  '13542': 'bobtail',
  '13543': 'bodacious',
  '13544': 'body',
  '13545': 'bogged',
  '13546': 'boggle',
  '13551': 'bogus',
  '13552': 'boil',
  '13553': 'bok',
  '13554': 'bolster',
  '13555': 'bolt',
  '13556': 'bonanza',
  '13561': 'bonded',
  '13562': 'bonding',
  '13563': 'bondless',
  '13564': 'boned',
  '13565': 'bonehead',
  '13566': 'boneless',
  '13611': 'bonelike',
  '13612': 'boney',
  '13613': 'bonfire',
  '13614': 'bonnet',
  '13615': 'bonsai',
  '13616': 'bonus',
  '13621': 'bony',
  '13622': 'boogeyman',
  '13623': 'boogieman',
  '13624': 'book',
  '13625': 'boondocks',
  '13626': 'booted',
  '13631': 'booth',
  '13632': 'bootie',
  '13633': 'booting',
  '13634': 'bootlace',
  '13635': 'bootleg',
  '13636': 'boots',
  '13641': 'boozy',
  '13642': 'borax',
  '13643': 'boring',
  '13644': 'borough',
  '13645': 'borrower',
  '13646': 'borrowing',
  '13651': 'boss',
  '13652': 'botanical',
  '13653': 'botanist',
  '13654': 'botany',
  '13655': 'botch',
  '13656': 'both',
  '13661': 'bottle',
  '13662': 'bottling',
  '13663': 'bottom',
  '13664': 'bounce',
  '13665': 'bouncing',
  '13666': 'bouncy',
  '14111': 'bounding',
  '14112': 'boundless',
  '14113': 'bountiful',
  '14114': 'bovine',
  '14115': 'boxcar',
  '14116': 'boxer',
  '14121': 'boxing',
  '14122': 'boxlike',
  '14123': 'boxy',
  '14124': 'breach',
  '14125': 'breath',
  '14126': 'breeches',
  '14131': 'breeching',
  '14132': 'breeder',
  '14133': 'breeding',
  '14134': 'breeze',
  '14135': 'breezy',
  '14136': 'brethren',
  '14141': 'brewery',
  '14142': 'brewing',
  '14143': 'briar',
  '14144': 'bribe',
  '14145': 'brick',
  '14146': 'bride',
  '14151': 'bridged',
  '14152': 'brigade',
  '14153': 'bright',
  '14154': 'brilliant',
  '14155': 'brim',
  '14156': 'bring',
  '14161': 'brink',
  '14162': 'brisket',
  '14163': 'briskly',
  '14164': 'briskness',
  '14165': 'bristle',
  '14166': 'brittle',
  '14211': 'broadband',
  '14212': 'broadcast',
  '14213': 'broaden',
  '14214': 'broadly',
  '14215': 'broadness',
  '14216': 'broadside',
  '14221': 'broadways',
  '14222': 'broiler',
  '14223': 'broiling',
  '14224': 'broken',
  '14225': 'broker',
  '14226': 'bronchial',
  '14231': 'bronco',
  '14232': 'bronze',
  '14233': 'bronzing',
  '14234': 'brook',
  '14235': 'broom',
  '14236': 'brought',
  '14241': 'browbeat',
  '14242': 'brownnose',
  '14243': 'browse',
  '14244': 'browsing',
  '14245': 'bruising',
  '14246': 'brunch',
  '14251': 'brunette',
  '14252': 'brunt',
  '14253': 'brush',
  '14254': 'brussels',
  '14255': 'brute',
  '14256': 'brutishly',
  '14261': 'bubble',
  '14262': 'bubbling',
  '14263': 'bubbly',
  '14264': 'buccaneer',
  '14265': 'bucked',
  '14266': 'bucket',
  '14311': 'buckle',
  '14312': 'buckshot',
  '14313': 'buckskin',
  '14314': 'bucktooth',
  '14315': 'buckwheat',
  '14316': 'buddhism',
  '14321': 'buddhist',
  '14322': 'budding',
  '14323': 'buddy',
  '14324': 'budget',
  '14325': 'buffalo',
  '14326': 'buffed',
  '14331': 'buffer',
  '14332': 'buffing',
  '14333': 'buffoon',
  '14334': 'buggy',
  '14335': 'bulb',
  '14336': 'bulge',
  '14341': 'bulginess',
  '14342': 'bulgur',
  '14343': 'bulk',
  '14344': 'bulldog',
  '14345': 'bulldozer',
  '14346': 'bullfight',
  '14351': 'bullfrog',
  '14352': 'bullhorn',
  '14353': 'bullion',
  '14354': 'bullish',
  '14355': 'bullpen',
  '14356': 'bullring',
  '14361': 'bullseye',
  '14362': 'bullwhip',
  '14363': 'bully',
  '14364': 'bunch',
  '14365': 'bundle',
  '14366': 'bungee',
  '14411': 'bunion',
  '14412': 'bunkbed',
  '14413': 'bunkhouse',
  '14414': 'bunkmate',
  '14415': 'bunny',
  '14416': 'bunt',
  '14421': 'busboy',
  '14422': 'bush',
  '14423': 'busily',
  '14424': 'busload',
  '14425': 'bust',
  '14426': 'busybody',
  '14431': 'buzz',
  '14432': 'cabana',
  '14433': 'cabbage',
  '14434': 'cabbie',
  '14435': 'cabdriver',
  '14436': 'cable',
  '14441': 'caboose',
  '14442': 'cache',
  '14443': 'cackle',
  '14444': 'cacti',
  '14445': 'cactus',
  '14446': 'caddie',
  '14451': 'caddy',
  '14452': 'cadet',
  '14453': 'cadillac',
  '14454': 'cadmium',
  '14455': 'cage',
  '14456': 'cahoots',
  '14461': 'cake',
  '14462': 'calamari',
  '14463': 'calamity',
  '14464': 'calcium',
  '14465': 'calculate',
  '14466': 'calculus',
  '14511': 'caliber',
  '14512': 'calibrate',
  '14513': 'calm',
  '14514': 'caloric',
  '14515': 'calorie',
  '14516': 'calzone',
  '14521': 'camcorder',
  '14522': 'cameo',
  '14523': 'camera',
  '14524': 'camisole',
  '14525': 'camper',
  '14526': 'campfire',
  '14531': 'camping',
  '14532': 'campsite',
  '14533': 'campus',
  '14534': 'canal',
  '14535': 'canary',
  '14536': 'cancel',
  '14541': 'candied',
  '14542': 'candle',
  '14543': 'candy',
  '14544': 'cane',
  '14545': 'canine',
  '14546': 'canister',
  '14551': 'cannabis',
  '14552': 'canned',
  '14553': 'canning',
  '14554': 'cannon',
  '14555': 'cannot',
  '14556': 'canola',
  '14561': 'canon',
  '14562': 'canopener',
  '14563': 'canopy',
  '14564': 'canteen',
  '14565': 'canyon',
  '14566': 'capable',
  '14611': 'capably',
  '14612': 'capacity',
  '14613': 'cape',
  '14614': 'capillary',
  '14615': 'capital',
  '14616': 'capitol',
  '14621': 'capped',
  '14622': 'capricorn',
  '14623': 'capsize',
  '14624': 'capsule',
  '14625': 'caption',
  '14626': 'captivate',
  '14631': 'captive',
  '14632': 'captivity',
  '14633': 'capture',
  '14634': 'caramel',
  '14635': 'carat',
  '14636': 'caravan',
  '14641': 'carbon',
  '14642': 'cardboard',
  '14643': 'carded',
  '14644': 'cardiac',
  '14645': 'cardigan',
  '14646': 'cardinal',
  '14651': 'cardstock',
  '14652': 'carefully',
  '14653': 'caregiver',
  '14654': 'careless',
  '14655': 'caress',
  '14656': 'caretaker',
  '14661': 'cargo',
  '14662': 'caring',
  '14663': 'carless',
  '14664': 'carload',
  '14665': 'carmaker',
  '14666': 'carnage',
  '15111': 'carnation',
  '15112': 'carnival',
  '15113': 'carnivore',
  '15114': 'carol',
  '15115': 'carpenter',
  '15116': 'carpentry',
  '15121': 'carpool',
  '15122': 'carport',
  '15123': 'carried',
  '15124': 'carrot',
  '15125': 'carrousel',
  '15126': 'carry',
  '15131': 'cartel',
  '15132': 'cartload',
  '15133': 'carton',
  '15134': 'cartoon',
  '15135': 'cartridge',
  '15136': 'cartwheel',
  '15141': 'carve',
  '15142': 'carving',
  '15143': 'carwash',
  '15144': 'cascade',
  '15145': 'case',
  '15146': 'cash',
  '15151': 'casing',
  '15152': 'casino',
  '15153': 'casket',
  '15154': 'cassette',
  '15155': 'casually',
  '15156': 'casualty',
  '15161': 'catacomb',
  '15162': 'catalog',
  '15163': 'catalyst',
  '15164': 'catalyze',
  '15165': 'catapult',
  '15166': 'cataract',
  '15211': 'catatonic',
  '15212': 'catcall',
  '15213': 'catchable',
  '15214': 'catcher',
  '15215': 'catching',
  '15216': 'catchy',
  '15221': 'caterer',
  '15222': 'catering',
  '15223': 'catfight',
  '15224': 'catfish',
  '15225': 'cathedral',
  '15226': 'cathouse',
  '15231': 'catlike',
  '15232': 'catnap',
  '15233': 'catnip',
  '15234': 'catsup',
  '15235': 'cattail',
  '15236': 'cattishly',
  '15241': 'cattle',
  '15242': 'catty',
  '15243': 'catwalk',
  '15244': 'caucasian',
  '15245': 'caucus',
  '15246': 'causal',
  '15251': 'causation',
  '15252': 'cause',
  '15253': 'causing',
  '15254': 'cauterize',
  '15255': 'caution',
  '15256': 'cautious',
  '15261': 'cavalier',
  '15262': 'cavalry',
  '15263': 'caviar',
  '15264': 'cavity',
  '15265': 'cedar',
  '15266': 'celery',
  '15311': 'celestial',
  '15312': 'celibacy',
  '15313': 'celibate',
  '15314': 'celtic',
  '15315': 'cement',
  '15316': 'census',
  '15321': 'ceramics',
  '15322': 'ceremony',
  '15323': 'certainly',
  '15324': 'certainty',
  '15325': 'certified',
  '15326': 'certify',
  '15331': 'cesarean',
  '15332': 'cesspool',
  '15333': 'chafe',
  '15334': 'chaffing',
  '15335': 'chain',
  '15336': 'chair',
  '15341': 'chalice',
  '15342': 'challenge',
  '15343': 'chamber',
  '15344': 'chamomile',
  '15345': 'champion',
  '15346': 'chance',
  '15351': 'change',
  '15352': 'channel',
  '15353': 'chant',
  '15354': 'chaos',
  '15355': 'chaperone',
  '15356': 'chaplain',
  '15361': 'chapped',
  '15362': 'chaps',
  '15363': 'chapter',
  '15364': 'character',
  '15365': 'charbroil',
  '15366': 'charcoal',
  '15411': 'charger',
  '15412': 'charging',
  '15413': 'chariot',
  '15414': 'charity',
  '15415': 'charm',
  '15416': 'charred',
  '15421': 'charter',
  '15422': 'charting',
  '15423': 'chase',
  '15424': 'chasing',
  '15425': 'chaste',
  '15426': 'chastise',
  '15431': 'chastity',
  '15432': 'chatroom',
  '15433': 'chatter',
  '15434': 'chatting',
  '15435': 'chatty',
  '15436': 'cheating',
  '15441': 'cheddar',
  '15442': 'cheek',
  '15443': 'cheer',
  '15444': 'cheese',
  '15445': 'cheesy',
  '15446': 'chef',
  '15451': 'chemicals',
  '15452': 'chemist',
  '15453': 'chemo',
  '15454': 'cherisher',
  '15455': 'cherub',
  '15456': 'chess',
  '15461': 'chest',
  '15462': 'chevron',
  '15463': 'chevy',
  '15464': 'chewable',
  '15465': 'chewer',
  '15466': 'chewing',
  '15511': 'chewy',
  '15512': 'chief',
  '15513': 'chihuahua',
  '15514': 'childcare',
  '15515': 'childhood',
  '15516': 'childish',
  '15521': 'childless',
  '15522': 'childlike',
  '15523': 'chili',
  '15524': 'chill',
  '15525': 'chimp',
  '15526': 'chip',
  '15531': 'chirping',
  '15532': 'chirpy',
  '15533': 'chitchat',
  '15534': 'chivalry',
  '15535': 'chive',
  '15536': 'chloride',
  '15541': 'chlorine',
  '15542': 'choice',
  '15543': 'chokehold',
  '15544': 'choking',
  '15545': 'chomp',
  '15546': 'chooser',
  '15551': 'choosing',
  '15552': 'choosy',
  '15553': 'chop',
  '15554': 'chosen',
  '15555': 'chowder',
  '15556': 'chowtime',
  '15561': 'chrome',
  '15562': 'chubby',
  '15563': 'chuck',
  '15564': 'chug',
  '15565': 'chummy',
  '15566': 'chump',
  '15611': 'chunk',
  '15612': 'churn',
  '15613': 'chute',
  '15614': 'cider',
  '15615': 'cilantro',
  '15616': 'cinch',
  '15621': 'cinema',
  '15622': 'cinnamon',
  '15623': 'circle',
  '15624': 'circling',
  '15625': 'circular',
  '15626': 'circulate',
  '15631': 'circus',
  '15632': 'citable',
  '15633': 'citadel',
  '15634': 'citation',
  '15635': 'citizen',
  '15636': 'citric',
  '15641': 'citrus',
  '15642': 'city',
  '15643': 'civic',
  '15644': 'civil',
  '15645': 'clad',
  '15646': 'claim',
  '15651': 'clambake',
  '15652': 'clammy',
  '15653': 'clamor',
  '15654': 'clamp',
  '15655': 'clamshell',
  '15656': 'clang',
  '15661': 'clanking',
  '15662': 'clapped',
  '15663': 'clapper',
  '15664': 'clapping',
  '15665': 'clarify',
  '15666': 'clarinet',
  '16111': 'clarity',
  '16112': 'clash',
  '16113': 'clasp',
  '16114': 'class',
  '16115': 'clatter',
  '16116': 'clause',
  '16121': 'clavicle',
  '16122': 'claw',
  '16123': 'clay',
  '16124': 'clean',
  '16125': 'clear',
  '16126': 'cleat',
  '16131': 'cleaver',
  '16132': 'cleft',
  '16133': 'clench',
  '16134': 'clergyman',
  '16135': 'clerical',
  '16136': 'clerk',
  '16141': 'clever',
  '16142': 'clicker',
  '16143': 'client',
  '16144': 'climate',
  '16145': 'climatic',
  '16146': 'cling',
  '16151': 'clinic',
  '16152': 'clinking',
  '16153': 'clip',
  '16154': 'clique',
  '16155': 'cloak',
  '16156': 'clobber',
  '16161': 'clock',
  '16162': 'clone',
  '16163': 'cloning',
  '16164': 'closable',
  '16165': 'closure',
  '16166': 'clothes',
  '16211': 'clothing',
  '16212': 'cloud',
  '16213': 'clover',
  '16214': 'clubbed',
  '16215': 'clubbing',
  '16216': 'clubhouse',
  '16221': 'clump',
  '16222': 'clumsily',
  '16223': 'clumsy',
  '16224': 'clunky',
  '16225': 'clustered',
  '16226': 'clutch',
  '16231': 'clutter',
  '16232': 'coach',
  '16233': 'coagulant',
  '16234': 'coastal',
  '16235': 'coaster',
  '16236': 'coasting',
  '16241': 'coastland',
  '16242': 'coastline',
  '16243': 'coat',
  '16244': 'coauthor',
  '16245': 'cobalt',
  '16246': 'cobbler',
  '16251': 'cobweb',
  '16252': 'cocoa',
  '16253': 'coconut',
  '16254': 'cod',
  '16255': 'coeditor',
  '16256': 'coerce',
  '16261': 'coexist',
  '16262': 'coffee',
  '16263': 'cofounder',
  '16264': 'cognition',
  '16265': 'cognitive',
  '16266': 'cogwheel',
  '16311': 'coherence',
  '16312': 'coherent',
  '16313': 'cohesive',
  '16314': 'coil',
  '16315': 'coke',
  '16316': 'cola',
  '16321': 'cold',
  '16322': 'coleslaw',
  '16323': 'coliseum',
  '16324': 'collage',
  '16325': 'collapse',
  '16326': 'collar',
  '16331': 'collected',
  '16332': 'collector',
  '16333': 'collide',
  '16334': 'collie',
  '16335': 'collision',
  '16336': 'colonial',
  '16341': 'colonist',
  '16342': 'colonize',
  '16343': 'colony',
  '16344': 'colossal',
  '16345': 'colt',
  '16346': 'coma',
  '16351': 'come',
  '16352': 'comfort',
  '16353': 'comfy',
  '16354': 'comic',
  '16355': 'coming',
  '16356': 'comma',
  '16361': 'commence',
  '16362': 'commend',
  '16363': 'comment',
  '16364': 'commerce',
  '16365': 'commode',
  '16366': 'commodity',
  '16411': 'commodore',
  '16412': 'common',
  '16413': 'commotion',
  '16414': 'commute',
  '16415': 'commuting',
  '16416': 'compacted',
  '16421': 'compacter',
  '16422': 'compactly',
  '16423': 'compactor',
  '16424': 'companion',
  '16425': 'company',
  '16426': 'compare',
  '16431': 'compel',
  '16432': 'compile',
  '16433': 'comply',
  '16434': 'component',
  '16435': 'composed',
  '16436': 'composer',
  '16441': 'composite',
  '16442': 'compost',
  '16443': 'composure',
  '16444': 'compound',
  '16445': 'compress',
  '16446': 'comprised',
  '16451': 'computer',
  '16452': 'computing',
  '16453': 'comrade',
  '16454': 'concave',
  '16455': 'conceal',
  '16456': 'conceded',
  '16461': 'concept',
  '16462': 'concerned',
  '16463': 'concert',
  '16464': 'conch',
  '16465': 'concierge',
  '16466': 'concise',
  '16511': 'conclude',
  '16512': 'concrete',
  '16513': 'concur',
  '16514': 'condense',
  '16515': 'condiment',
  '16516': 'condition',
  '16521': 'condone',
  '16522': 'conducive',
  '16523': 'conductor',
  '16524': 'conduit',
  '16525': 'cone',
  '16526': 'confess',
  '16531': 'confetti',
  '16532': 'confidant',
  '16533': 'confident',
  '16534': 'confider',
  '16535': 'confiding',
  '16536': 'configure',
  '16541': 'confined',
  '16542': 'confining',
  '16543': 'confirm',
  '16544': 'conflict',
  '16545': 'conform',
  '16546': 'confound',
  '16551': 'confront',
  '16552': 'confused',
  '16553': 'confusing',
  '16554': 'confusion',
  '16555': 'congenial',
  '16556': 'congested',
  '16561': 'congrats',
  '16562': 'congress',
  '16563': 'conical',
  '16564': 'conjoined',
  '16565': 'conjure',
  '16566': 'conjuror',
  '16611': 'connected',
  '16612': 'connector',
  '16613': 'consensus',
  '16614': 'consent',
  '16615': 'console',
  '16616': 'consoling',
  '16621': 'consonant',
  '16622': 'constable',
  '16623': 'constant',
  '16624': 'constrain',
  '16625': 'constrict',
  '16626': 'construct',
  '16631': 'consult',
  '16632': 'consumer',
  '16633': 'consuming',
  '16634': 'contact',
  '16635': 'container',
  '16636': 'contempt',
  '16641': 'contend',
  '16642': 'contented',
  '16643': 'contently',
  '16644': 'contents',
  '16645': 'contest',
  '16646': 'context',
  '16651': 'contort',
  '16652': 'contour',
  '16653': 'contrite',
  '16654': 'control',
  '16655': 'contusion',
  '16656': 'convene',
  '16661': 'convent',
  '16662': 'copartner',
  '16663': 'cope',
  '16664': 'copied',
  '16665': 'copier',
  '16666': 'copilot',
  '21111': 'coping',
  '21112': 'copious',
  '21113': 'copper',
  '21114': 'copy',
  '21115': 'coral',
  '21116': 'cork',
  '21121': 'cornball',
  '21122': 'cornbread',
  '21123': 'corncob',
  '21124': 'cornea',
  '21125': 'corned',
  '21126': 'corner',
  '21131': 'cornfield',
  '21132': 'cornflake',
  '21133': 'cornhusk',
  '21134': 'cornmeal',
  '21135': 'cornstalk',
  '21136': 'corny',
  '21141': 'coronary',
  '21142': 'coroner',
  '21143': 'corporal',
  '21144': 'corporate',
  '21145': 'corral',
  '21146': 'correct',
  '21151': 'corridor',
  '21152': 'corrode',
  '21153': 'corroding',
  '21154': 'corrosive',
  '21155': 'corsage',
  '21156': 'corset',
  '21161': 'cortex',
  '21162': 'cosigner',
  '21163': 'cosmetics',
  '21164': 'cosmic',
  '21165': 'cosmos',
  '21166': 'cosponsor',
  '21211': 'cost',
  '21212': 'cottage',
  '21213': 'cotton',
  '21214': 'couch',
  '21215': 'cough',
  '21216': 'could',
  '21221': 'countable',
  '21222': 'countdown',
  '21223': 'counting',
  '21224': 'countless',
  '21225': 'country',
  '21226': 'county',
  '21231': 'courier',
  '21232': 'covenant',
  '21233': 'cover',
  '21234': 'coveted',
  '21235': 'coveting',
  '21236': 'coyness',
  '21241': 'cozily',
  '21242': 'coziness',
  '21243': 'cozy',
  '21244': 'crabbing',
  '21245': 'crabgrass',
  '21246': 'crablike',
  '21251': 'crabmeat',
  '21252': 'cradle',
  '21253': 'cradling',
  '21254': 'crafter',
  '21255': 'craftily',
  '21256': 'craftsman',
  '21261': 'craftwork',
  '21262': 'crafty',
  '21263': 'cramp',
  '21264': 'cranberry',
  '21265': 'crane',
  '21266': 'cranial',
  '21311': 'cranium',
  '21312': 'crank',
  '21313': 'crate',
  '21314': 'crave',
  '21315': 'craving',
  '21316': 'crawfish',
  '21321': 'crawlers',
  '21322': 'crawling',
  '21323': 'crayfish',
  '21324': 'crayon',
  '21325': 'crazed',
  '21326': 'crazily',
  '21331': 'craziness',
  '21332': 'crazy',
  '21333': 'creamed',
  '21334': 'creamer',
  '21335': 'creamlike',
  '21336': 'crease',
  '21341': 'creasing',
  '21342': 'creatable',
  '21343': 'create',
  '21344': 'creation',
  '21345': 'creative',
  '21346': 'creature',
  '21351': 'credible',
  '21352': 'credibly',
  '21353': 'credit',
  '21354': 'creed',
  '21355': 'creme',
  '21356': 'creole',
  '21361': 'crepe',
  '21362': 'crept',
  '21363': 'crescent',
  '21364': 'crested',
  '21365': 'cresting',
  '21366': 'crestless',
  '21411': 'crevice',
  '21412': 'crewless',
  '21413': 'crewman',
  '21414': 'crewmate',
  '21415': 'crib',
  '21416': 'cricket',
  '21421': 'cried',
  '21422': 'crier',
  '21423': 'crimp',
  '21424': 'crimson',
  '21425': 'cringe',
  '21426': 'cringing',
  '21431': 'crinkle',
  '21432': 'crinkly',
  '21433': 'crisped',
  '21434': 'crisping',
  '21435': 'crisply',
  '21436': 'crispness',
  '21441': 'crispy',
  '21442': 'criteria',
  '21443': 'critter',
  '21444': 'croak',
  '21445': 'crock',
  '21446': 'crook',
  '21451': 'croon',
  '21452': 'crop',
  '21453': 'cross',
  '21454': 'crouch',
  '21455': 'crouton',
  '21456': 'crowbar',
  '21461': 'crowd',
  '21462': 'crown',
  '21463': 'crucial',
  '21464': 'crudely',
  '21465': 'crudeness',
  '21466': 'cruelly',
  '21511': 'cruelness',
  '21512': 'cruelty',
  '21513': 'crumb',
  '21514': 'crummiest',
  '21515': 'crummy',
  '21516': 'crumpet',
  '21521': 'crumpled',
  '21522': 'cruncher',
  '21523': 'crunching',
  '21524': 'crunchy',
  '21525': 'crusader',
  '21526': 'crushable',
  '21531': 'crushed',
  '21532': 'crusher',
  '21533': 'crushing',
  '21534': 'crust',
  '21535': 'crux',
  '21536': 'crying',
  '21541': 'cryptic',
  '21542': 'crystal',
  '21543': 'cubbyhole',
  '21544': 'cube',
  '21545': 'cubical',
  '21546': 'cubicle',
  '21551': 'cucumber',
  '21552': 'cuddle',
  '21553': 'cuddly',
  '21554': 'cufflink',
  '21555': 'culinary',
  '21556': 'culminate',
  '21561': 'culpable',
  '21562': 'culprit',
  '21563': 'cultivate',
  '21564': 'cultural',
  '21565': 'culture',
  '21566': 'cupbearer',
  '21611': 'cupcake',
  '21612': 'cupid',
  '21613': 'cupped',
  '21614': 'cupping',
  '21615': 'curable',
  '21616': 'curator',
  '21621': 'curdle',
  '21622': 'cure',
  '21623': 'curfew',
  '21624': 'curing',
  '21625': 'curled',
  '21626': 'curler',
  '21631': 'curliness',
  '21632': 'curling',
  '21633': 'curly',
  '21634': 'curry',
  '21635': 'curse',
  '21636': 'cursive',
  '21641': 'cursor',
  '21642': 'curtain',
  '21643': 'curtly',
  '21644': 'curtsy',
  '21645': 'curvature',
  '21646': 'curve',
  '21651': 'curvy',
  '21652': 'cushy',
  '21653': 'cusp',
  '21654': 'cussed',
  '21655': 'custard',
  '21656': 'custodian',
  '21661': 'custody',
  '21662': 'customary',
  '21663': 'customer',
  '21664': 'customize',
  '21665': 'customs',
  '21666': 'cut',
  '22111': 'cycle',
  '22112': 'cyclic',
  '22113': 'cycling',
  '22114': 'cyclist',
  '22115': 'cylinder',
  '22116': 'cymbal',
  '22121': 'cytoplasm',
  '22122': 'cytoplast',
  '22123': 'dab',
  '22124': 'dad',
  '22125': 'daffodil',
  '22126': 'dagger',
  '22131': 'daily',
  '22132': 'daintily',
  '22133': 'dainty',
  '22134': 'dairy',
  '22135': 'daisy',
  '22136': 'dallying',
  '22141': 'dance',
  '22142': 'dancing',
  '22143': 'dandelion',
  '22144': 'dander',
  '22145': 'dandruff',
  '22146': 'dandy',
  '22151': 'danger',
  '22152': 'dangle',
  '22153': 'dangling',
  '22154': 'daredevil',
  '22155': 'dares',
  '22156': 'daringly',
  '22161': 'darkened',
  '22162': 'darkening',
  '22163': 'darkish',
  '22164': 'darkness',
  '22165': 'darkroom',
  '22166': 'darling',
  '22211': 'darn',
  '22212': 'dart',
  '22213': 'darwinism',
  '22214': 'dash',
  '22215': 'dastardly',
  '22216': 'data',
  '22221': 'datebook',
  '22222': 'dating',
  '22223': 'daughter',
  '22224': 'daunting',
  '22225': 'dawdler',
  '22226': 'dawn',
  '22231': 'daybed',
  '22232': 'daybreak',
  '22233': 'daycare',
  '22234': 'daydream',
  '22235': 'daylight',
  '22236': 'daylong',
  '22241': 'dayroom',
  '22242': 'daytime',
  '22243': 'dazzler',
  '22244': 'dazzling',
  '22245': 'deacon',
  '22246': 'deafening',
  '22251': 'deafness',
  '22252': 'dealer',
  '22253': 'dealing',
  '22254': 'dealmaker',
  '22255': 'dealt',
  '22256': 'dean',
  '22261': 'debatable',
  '22262': 'debate',
  '22263': 'debating',
  '22264': 'debit',
  '22265': 'debrief',
  '22266': 'debtless',
  '22311': 'debtor',
  '22312': 'debug',
  '22313': 'debunk',
  '22314': 'decade',
  '22315': 'decaf',
  '22316': 'decal',
  '22321': 'decathlon',
  '22322': 'decay',
  '22323': 'deceased',
  '22324': 'deceit',
  '22325': 'deceiver',
  '22326': 'deceiving',
  '22331': 'december',
  '22332': 'decency',
  '22333': 'decent',
  '22334': 'deception',
  '22335': 'deceptive',
  '22336': 'decibel',
  '22341': 'decidable',
  '22342': 'decimal',
  '22343': 'decimeter',
  '22344': 'decipher',
  '22345': 'deck',
  '22346': 'declared',
  '22351': 'decline',
  '22352': 'decode',
  '22353': 'decompose',
  '22354': 'decorated',
  '22355': 'decorator',
  '22356': 'decoy',
  '22361': 'decrease',
  '22362': 'decree',
  '22363': 'dedicate',
  '22364': 'dedicator',
  '22365': 'deduce',
  '22366': 'deduct',
  '22411': 'deed',
  '22412': 'deem',
  '22413': 'deepen',
  '22414': 'deeply',
  '22415': 'deepness',
  '22416': 'deface',
  '22421': 'defacing',
  '22422': 'defame',
  '22423': 'default',
  '22424': 'defeat',
  '22425': 'defection',
  '22426': 'defective',
  '22431': 'defendant',
  '22432': 'defender',
  '22433': 'defense',
  '22434': 'defensive',
  '22435': 'deferral',
  '22436': 'deferred',
  '22441': 'defiance',
  '22442': 'defiant',
  '22443': 'defile',
  '22444': 'defiling',
  '22445': 'define',
  '22446': 'definite',
  '22451': 'deflate',
  '22452': 'deflation',
  '22453': 'deflator',
  '22454': 'deflected',
  '22455': 'deflector',
  '22456': 'defog',
  '22461': 'deforest',
  '22462': 'defraud',
  '22463': 'defrost',
  '22464': 'deftly',
  '22465': 'defuse',
  '22466': 'defy',
  '22511': 'degraded',
  '22512': 'degrading',
  '22513': 'degrease',
  '22514': 'degree',
  '22515': 'dehydrate',
  '22516': 'deity',
  '22521': 'dejected',
  '22522': 'delay',
  '22523': 'delegate',
  '22524': 'delegator',
  '22525': 'delete',
  '22526': 'deletion',
  '22531': 'delicacy',
  '22532': 'delicate',
  '22533': 'delicious',
  '22534': 'delighted',
  '22535': 'delirious',
  '22536': 'delirium',
  '22541': 'deliverer',
  '22542': 'delivery',
  '22543': 'delouse',
  '22544': 'delta',
  '22545': 'deluge',
  '22546': 'delusion',
  '22551': 'deluxe',
  '22552': 'demanding',
  '22553': 'demeaning',
  '22554': 'demeanor',
  '22555': 'demise',
  '22556': 'democracy',
  '22561': 'democrat',
  '22562': 'demote',
  '22563': 'demotion',
  '22564': 'demystify',
  '22565': 'denatured',
  '22566': 'deniable',
  '22611': 'denial',
  '22612': 'denim',
  '22613': 'denote',
  '22614': 'dense',
  '22615': 'density',
  '22616': 'dental',
  '22621': 'dentist',
  '22622': 'denture',
  '22623': 'deny',
  '22624': 'deodorant',
  '22625': 'deodorize',
  '22626': 'departed',
  '22631': 'departure',
  '22632': 'depict',
  '22633': 'deplete',
  '22634': 'depletion',
  '22635': 'deplored',
  '22636': 'deploy',
  '22641': 'deport',
  '22642': 'depose',
  '22643': 'depraved',
  '22644': 'depravity',
  '22645': 'deprecate',
  '22646': 'depress',
  '22651': 'deprive',
  '22652': 'depth',
  '22653': 'deputize',
  '22654': 'deputy',
  '22655': 'derail',
  '22656': 'deranged',
  '22661': 'derby',
  '22662': 'derived',
  '22663': 'desecrate',
  '22664': 'deserve',
  '22665': 'deserving',
  '22666': 'designate',
  '23111': 'designed',
  '23112': 'designer',
  '23113': 'designing',
  '23114': 'deskbound',
  '23115': 'desktop',
  '23116': 'deskwork',
  '23121': 'desolate',
  '23122': 'despair',
  '23123': 'despise',
  '23124': 'despite',
  '23125': 'destiny',
  '23126': 'destitute',
  '23131': 'destruct',
  '23132': 'detached',
  '23133': 'detail',
  '23134': 'detection',
  '23135': 'detective',
  '23136': 'detector',
  '23141': 'detention',
  '23142': 'detergent',
  '23143': 'detest',
  '23144': 'detonate',
  '23145': 'detonator',
  '23146': 'detoxify',
  '23151': 'detract',
  '23152': 'deuce',
  '23153': 'devalue',
  '23154': 'deviancy',
  '23155': 'deviant',
  '23156': 'deviate',
  '23161': 'deviation',
  '23162': 'deviator',
  '23163': 'device',
  '23164': 'devious',
  '23165': 'devotedly',
  '23166': 'devotee',
  '23211': 'devotion',
  '23212': 'devourer',
  '23213': 'devouring',
  '23214': 'devoutly',
  '23215': 'dexterity',
  '23216': 'dexterous',
  '23221': 'diabetes',
  '23222': 'diabetic',
  '23223': 'diabolic',
  '23224': 'diagnoses',
  '23225': 'diagnosis',
  '23226': 'diagram',
  '23231': 'dial',
  '23232': 'diameter',
  '23233': 'diaper',
  '23234': 'diaphragm',
  '23235': 'diary',
  '23236': 'dice',
  '23241': 'dicing',
  '23242': 'dictate',
  '23243': 'dictation',
  '23244': 'dictator',
  '23245': 'difficult',
  '23246': 'diffused',
  '23251': 'diffuser',
  '23252': 'diffusion',
  '23253': 'diffusive',
  '23254': 'dig',
  '23255': 'dilation',
  '23256': 'diligence',
  '23261': 'diligent',
  '23262': 'dill',
  '23263': 'dilute',
  '23264': 'dime',
  '23265': 'diminish',
  '23266': 'dimly',
  '23311': 'dimmed',
  '23312': 'dimmer',
  '23313': 'dimness',
  '23314': 'dimple',
  '23315': 'diner',
  '23316': 'dingbat',
  '23321': 'dinghy',
  '23322': 'dinginess',
  '23323': 'dingo',
  '23324': 'dingy',
  '23325': 'dining',
  '23326': 'dinner',
  '23331': 'diocese',
  '23332': 'dioxide',
  '23333': 'diploma',
  '23334': 'dipped',
  '23335': 'dipper',
  '23336': 'dipping',
  '23341': 'directed',
  '23342': 'direction',
  '23343': 'directive',
  '23344': 'directly',
  '23345': 'directory',
  '23346': 'direness',
  '23351': 'dirtiness',
  '23352': 'disabled',
  '23353': 'disagree',
  '23354': 'disallow',
  '23355': 'disarm',
  '23356': 'disarray',
  '23361': 'disaster',
  '23362': 'disband',
  '23363': 'disbelief',
  '23364': 'disburse',
  '23365': 'discard',
  '23366': 'discern',
  '23411': 'discharge',
  '23412': 'disclose',
  '23413': 'discolor',
  '23414': 'discount',
  '23415': 'discourse',
  '23416': 'discover',
  '23421': 'discuss',
  '23422': 'disdain',
  '23423': 'disengage',
  '23424': 'disfigure',
  '23425': 'disgrace',
  '23426': 'dish',
  '23431': 'disinfect',
  '23432': 'disjoin',
  '23433': 'disk',
  '23434': 'dislike',
  '23435': 'disliking',
  '23436': 'dislocate',
  '23441': 'dislodge',
  '23442': 'disloyal',
  '23443': 'dismantle',
  '23444': 'dismay',
  '23445': 'dismiss',
  '23446': 'dismount',
  '23451': 'disobey',
  '23452': 'disorder',
  '23453': 'disown',
  '23454': 'disparate',
  '23455': 'disparity',
  '23456': 'dispatch',
  '23461': 'dispense',
  '23462': 'dispersal',
  '23463': 'dispersed',
  '23464': 'disperser',
  '23465': 'displace',
  '23466': 'display',
  '23511': 'displease',
  '23512': 'disposal',
  '23513': 'dispose',
  '23514': 'disprove',
  '23515': 'dispute',
  '23516': 'disregard',
  '23521': 'disrupt',
  '23522': 'dissuade',
  '23523': 'distance',
  '23524': 'distant',
  '23525': 'distaste',
  '23526': 'distill',
  '23531': 'distinct',
  '23532': 'distort',
  '23533': 'distract',
  '23534': 'distress',
  '23535': 'district',
  '23536': 'distrust',
  '23541': 'ditch',
  '23542': 'ditto',
  '23543': 'ditzy',
  '23544': 'dividable',
  '23545': 'divided',
  '23546': 'dividend',
  '23551': 'dividers',
  '23552': 'dividing',
  '23553': 'divinely',
  '23554': 'diving',
  '23555': 'divinity',
  '23556': 'divisible',
  '23561': 'divisibly',
  '23562': 'division',
  '23563': 'divisive',
  '23564': 'divorcee',
  '23565': 'dizziness',
  '23566': 'dizzy',
  '23611': 'doable',
  '23612': 'docile',
  '23613': 'dock',
  '23614': 'doctrine',
  '23615': 'document',
  '23616': 'dodge',
  '23621': 'dodgy',
  '23622': 'doily',
  '23623': 'doing',
  '23624': 'dole',
  '23625': 'dollar',
  '23626': 'dollhouse',
  '23631': 'dollop',
  '23632': 'dolly',
  '23633': 'dolphin',
  '23634': 'domain',
  '23635': 'domelike',
  '23636': 'domestic',
  '23641': 'dominion',
  '23642': 'dominoes',
  '23643': 'donated',
  '23644': 'donation',
  '23645': 'donator',
  '23646': 'donor',
  '23651': 'donut',
  '23652': 'doodle',
  '23653': 'doorbell',
  '23654': 'doorframe',
  '23655': 'doorknob',
  '23656': 'doorman',
  '23661': 'doormat',
  '23662': 'doornail',
  '23663': 'doorpost',
  '23664': 'doorstep',
  '23665': 'doorstop',
  '23666': 'doorway',
  '24111': 'doozy',
  '24112': 'dork',
  '24113': 'dormitory',
  '24114': 'dorsal',
  '24115': 'dosage',
  '24116': 'dose',
  '24121': 'dotted',
  '24122': 'doubling',
  '24123': 'douche',
  '24124': 'dove',
  '24125': 'down',
  '24126': 'dowry',
  '24131': 'doze',
  '24132': 'drab',
  '24133': 'dragging',
  '24134': 'dragonfly',
  '24135': 'dragonish',
  '24136': 'dragster',
  '24141': 'drainable',
  '24142': 'drainage',
  '24143': 'drained',
  '24144': 'drainer',
  '24145': 'drainpipe',
  '24146': 'dramatic',
  '24151': 'dramatize',
  '24152': 'drank',
  '24153': 'drapery',
  '24154': 'drastic',
  '24155': 'draw',
  '24156': 'dreaded',
  '24161': 'dreadful',
  '24162': 'dreadlock',
  '24163': 'dreamboat',
  '24164': 'dreamily',
  '24165': 'dreamland',
  '24166': 'dreamless',
  '24211': 'dreamlike',
  '24212': 'dreamt',
  '24213': 'dreamy',
  '24214': 'drearily',
  '24215': 'dreary',
  '24216': 'drench',
  '24221': 'dress',
  '24222': 'drew',
  '24223': 'dribble',
  '24224': 'dried',
  '24225': 'drier',
  '24226': 'drift',
  '24231': 'driller',
  '24232': 'drilling',
  '24233': 'drinkable',
  '24234': 'drinking',
  '24235': 'dripping',
  '24236': 'drippy',
  '24241': 'drivable',
  '24242': 'driven',
  '24243': 'driver',
  '24244': 'driveway',
  '24245': 'driving',
  '24246': 'drizzle',
  '24251': 'drizzly',
  '24252': 'drone',
  '24253': 'drool',
  '24254': 'droop',
  '24255': 'drop-down',
  '24256': 'dropbox',
  '24261': 'dropkick',
  '24262': 'droplet',
  '24263': 'dropout',
  '24264': 'dropper',
  '24265': 'drove',
  '24266': 'drown',
  '24311': 'drowsily',
  '24312': 'drudge',
  '24313': 'drum',
  '24314': 'dry',
  '24315': 'dubbed',
  '24316': 'dubiously',
  '24321': 'duchess',
  '24322': 'duckbill',
  '24323': 'ducking',
  '24324': 'duckling',
  '24325': 'ducktail',
  '24326': 'ducky',
  '24331': 'duct',
  '24332': 'dude',
  '24333': 'duffel',
  '24334': 'dugout',
  '24335': 'duh',
  '24336': 'duke',
  '24341': 'duller',
  '24342': 'dullness',
  '24343': 'duly',
  '24344': 'dumping',
  '24345': 'dumpling',
  '24346': 'dumpster',
  '24351': 'duo',
  '24352': 'dupe',
  '24353': 'duplex',
  '24354': 'duplicate',
  '24355': 'duplicity',
  '24356': 'durable',
  '24361': 'durably',
  '24362': 'duration',
  '24363': 'duress',
  '24364': 'during',
  '24365': 'dusk',
  '24366': 'dust',
  '24411': 'dutiful',
  '24412': 'duty',
  '24413': 'duvet',
  '24414': 'dwarf',
  '24415': 'dweeb',
  '24416': 'dwelled',
  '24421': 'dweller',
  '24422': 'dwelling',
  '24423': 'dwindle',
  '24424': 'dwindling',
  '24425': 'dynamic',
  '24426': 'dynamite',
  '24431': 'dynasty',
  '24432': 'dyslexia',
  '24433': 'dyslexic',
  '24434': 'each',
  '24435': 'eagle',
  '24436': 'earache',
  '24441': 'eardrum',
  '24442': 'earflap',
  '24443': 'earful',
  '24444': 'earlobe',
  '24445': 'early',
  '24446': 'earmark',
  '24451': 'earmuff',
  '24452': 'earphone',
  '24453': 'earpiece',
  '24454': 'earplugs',
  '24455': 'earring',
  '24456': 'earshot',
  '24461': 'earthen',
  '24462': 'earthlike',
  '24463': 'earthling',
  '24464': 'earthly',
  '24465': 'earthworm',
  '24466': 'earthy',
  '24511': 'earwig',
  '24512': 'easeful',
  '24513': 'easel',
  '24514': 'easiest',
  '24515': 'easily',
  '24516': 'easiness',
  '24521': 'easing',
  '24522': 'eastbound',
  '24523': 'eastcoast',
  '24524': 'easter',
  '24525': 'eastward',
  '24526': 'eatable',
  '24531': 'eaten',
  '24532': 'eatery',
  '24533': 'eating',
  '24534': 'eats',
  '24535': 'ebay',
  '24536': 'ebony',
  '24541': 'ebook',
  '24542': 'ecard',
  '24543': 'eccentric',
  '24544': 'echo',
  '24545': 'eclair',
  '24546': 'eclipse',
  '24551': 'ecologist',
  '24552': 'ecology',
  '24553': 'economic',
  '24554': 'economist',
  '24555': 'economy',
  '24556': 'ecosphere',
  '24561': 'ecosystem',
  '24562': 'edge',
  '24563': 'edginess',
  '24564': 'edging',
  '24565': 'edgy',
  '24566': 'edition',
  '24611': 'editor',
  '24612': 'educated',
  '24613': 'education',
  '24614': 'educator',
  '24615': 'eel',
  '24616': 'effective',
  '24621': 'effects',
  '24622': 'efficient',
  '24623': 'effort',
  '24624': 'eggbeater',
  '24625': 'egging',
  '24626': 'eggnog',
  '24631': 'eggplant',
  '24632': 'eggshell',
  '24633': 'egomaniac',
  '24634': 'egotism',
  '24635': 'egotistic',
  '24636': 'either',
  '24641': 'eject',
  '24642': 'elaborate',
  '24643': 'elastic',
  '24644': 'elated',
  '24645': 'elbow',
  '24646': 'eldercare',
  '24651': 'elderly',
  '24652': 'eldest',
  '24653': 'electable',
  '24654': 'election',
  '24655': 'elective',
  '24656': 'elephant',
  '24661': 'elevate',
  '24662': 'elevating',
  '24663': 'elevation',
  '24664': 'elevator',
  '24665': 'eleven',
  '24666': 'elf',
  '25111': 'eligible',
  '25112': 'eligibly',
  '25113': 'eliminate',
  '25114': 'elite',
  '25115': 'elitism',
  '25116': 'elixir',
  '25121': 'elk',
  '25122': 'ellipse',
  '25123': 'elliptic',
  '25124': 'elm',
  '25125': 'elongated',
  '25126': 'elope',
  '25131': 'eloquence',
  '25132': 'eloquent',
  '25133': 'elsewhere',
  '25134': 'elude',
  '25135': 'elusive',
  '25136': 'elves',
  '25141': 'email',
  '25142': 'embargo',
  '25143': 'embark',
  '25144': 'embassy',
  '25145': 'embattled',
  '25146': 'embellish',
  '25151': 'ember',
  '25152': 'embezzle',
  '25153': 'emblaze',
  '25154': 'emblem',
  '25155': 'embody',
  '25156': 'embolism',
  '25161': 'emboss',
  '25162': 'embroider',
  '25163': 'emcee',
  '25164': 'emerald',
  '25165': 'emergency',
  '25166': 'emission',
  '25211': 'emit',
  '25212': 'emote',
  '25213': 'emoticon',
  '25214': 'emotion',
  '25215': 'empathic',
  '25216': 'empathy',
  '25221': 'emperor',
  '25222': 'emphases',
  '25223': 'emphasis',
  '25224': 'emphasize',
  '25225': 'emphatic',
  '25226': 'empirical',
  '25231': 'employed',
  '25232': 'employee',
  '25233': 'employer',
  '25234': 'emporium',
  '25235': 'empower',
  '25236': 'emptier',
  '25241': 'emptiness',
  '25242': 'empty',
  '25243': 'emu',
  '25244': 'enable',
  '25245': 'enactment',
  '25246': 'enamel',
  '25251': 'enchanted',
  '25252': 'enchilada',
  '25253': 'encircle',
  '25254': 'enclose',
  '25255': 'enclosure',
  '25256': 'encode',
  '25261': 'encore',
  '25262': 'encounter',
  '25263': 'encourage',
  '25264': 'encroach',
  '25265': 'encrust',
  '25266': 'encrypt',
  '25311': 'endanger',
  '25312': 'endeared',
  '25313': 'endearing',
  '25314': 'ended',
  '25315': 'ending',
  '25316': 'endless',
  '25321': 'endnote',
  '25322': 'endocrine',
  '25323': 'endorphin',
  '25324': 'endorse',
  '25325': 'endowment',
  '25326': 'endpoint',
  '25331': 'endurable',
  '25332': 'endurance',
  '25333': 'enduring',
  '25334': 'energetic',
  '25335': 'energize',
  '25336': 'energy',
  '25341': 'enforced',
  '25342': 'enforcer',
  '25343': 'engaged',
  '25344': 'engaging',
  '25345': 'engine',
  '25346': 'engorge',
  '25351': 'engraved',
  '25352': 'engraver',
  '25353': 'engraving',
  '25354': 'engross',
  '25355': 'engulf',
  '25356': 'enhance',
  '25361': 'enigmatic',
  '25362': 'enjoyable',
  '25363': 'enjoyably',
  '25364': 'enjoyer',
  '25365': 'enjoying',
  '25366': 'enjoyment',
  '25411': 'enlarged',
  '25412': 'enlarging',
  '25413': 'enlighten',
  '25414': 'enlisted',
  '25415': 'enquirer',
  '25416': 'enrage',
  '25421': 'enrich',
  '25422': 'enroll',
  '25423': 'enslave',
  '25424': 'ensnare',
  '25425': 'ensure',
  '25426': 'entail',
  '25431': 'entangled',
  '25432': 'entering',
  '25433': 'entertain',
  '25434': 'enticing',
  '25435': 'entire',
  '25436': 'entitle',
  '25441': 'entity',
  '25442': 'entomb',
  '25443': 'entourage',
  '25444': 'entrap',
  '25445': 'entree',
  '25446': 'entrench',
  '25451': 'entrust',
  '25452': 'entryway',
  '25453': 'entwine',
  '25454': 'enunciate',
  '25455': 'envelope',
  '25456': 'enviable',
  '25461': 'enviably',
  '25462': 'envious',
  '25463': 'envision',
  '25464': 'envoy',
  '25465': 'envy',
  '25466': 'enzyme',
  '25511': 'epic',
  '25512': 'epidemic',
  '25513': 'epidermal',
  '25514': 'epidermis',
  '25515': 'epidural',
  '25516': 'epilepsy',
  '25521': 'epileptic',
  '25522': 'epilogue',
  '25523': 'epiphany',
  '25524': 'episode',
  '25525': 'equal',
  '25526': 'equate',
  '25531': 'equation',
  '25532': 'equator',
  '25533': 'equinox',
  '25534': 'equipment',
  '25535': 'equity',
  '25536': 'equivocal',
  '25541': 'eradicate',
  '25542': 'erasable',
  '25543': 'erased',
  '25544': 'eraser',
  '25545': 'erasure',
  '25546': 'ergonomic',
  '25551': 'errand',
  '25552': 'errant',
  '25553': 'erratic',
  '25554': 'error',
  '25555': 'erupt',
  '25556': 'escalate',
  '25561': 'escalator',
  '25562': 'escapable',
  '25563': 'escapade',
  '25564': 'escapist',
  '25565': 'escargot',
  '25566': 'eskimo',
  '25611': 'esophagus',
  '25612': 'espionage',
  '25613': 'espresso',
  '25614': 'esquire',
  '25615': 'essay',
  '25616': 'essence',
  '25621': 'essential',
  '25622': 'establish',
  '25623': 'estate',
  '25624': 'esteemed',
  '25625': 'estimate',
  '25626': 'estimator',
  '25631': 'estranged',
  '25632': 'estrogen',
  '25633': 'etching',
  '25634': 'eternal',
  '25635': 'eternity',
  '25636': 'ethanol',
  '25641': 'ether',
  '25642': 'ethically',
  '25643': 'ethics',
  '25644': 'euphemism',
  '25645': 'evacuate',
  '25646': 'evacuee',
  '25651': 'evade',
  '25652': 'evaluate',
  '25653': 'evaluator',
  '25654': 'evaporate',
  '25655': 'evasion',
  '25656': 'evasive',
  '25661': 'even',
  '25662': 'everglade',
  '25663': 'evergreen',
  '25664': 'everybody',
  '25665': 'everyday',
  '25666': 'everyone',
  '26111': 'evict',
  '26112': 'evidence',
  '26113': 'evident',
  '26114': 'evil',
  '26115': 'evoke',
  '26116': 'evolution',
  '26121': 'evolve',
  '26122': 'exact',
  '26123': 'exalted',
  '26124': 'example',
  '26125': 'excavate',
  '26126': 'excavator',
  '26131': 'exceeding',
  '26132': 'exception',
  '26133': 'excess',
  '26134': 'exchange',
  '26135': 'excitable',
  '26136': 'exciting',
  '26141': 'exclaim',
  '26142': 'exclude',
  '26143': 'excluding',
  '26144': 'exclusion',
  '26145': 'exclusive',
  '26146': 'excretion',
  '26151': 'excretory',
  '26152': 'excursion',
  '26153': 'excusable',
  '26154': 'excusably',
  '26155': 'excuse',
  '26156': 'exemplary',
  '26161': 'exemplify',
  '26162': 'exemption',
  '26163': 'exerciser',
  '26164': 'exert',
  '26165': 'exes',
  '26166': 'exfoliate',
  '26211': 'exhale',
  '26212': 'exhaust',
  '26213': 'exhume',
  '26214': 'exile',
  '26215': 'existing',
  '26216': 'exit',
  '26221': 'exodus',
  '26222': 'exonerate',
  '26223': 'exorcism',
  '26224': 'exorcist',
  '26225': 'expand',
  '26226': 'expanse',
  '26231': 'expansion',
  '26232': 'expansive',
  '26233': 'expectant',
  '26234': 'expedited',
  '26235': 'expediter',
  '26236': 'expel',
  '26241': 'expend',
  '26242': 'expenses',
  '26243': 'expensive',
  '26244': 'expert',
  '26245': 'expire',
  '26246': 'expiring',
  '26251': 'explain',
  '26252': 'expletive',
  '26253': 'explicit',
  '26254': 'explode',
  '26255': 'exploit',
  '26256': 'explore',
  '26261': 'exploring',
  '26262': 'exponent',
  '26263': 'exporter',
  '26264': 'exposable',
  '26265': 'expose',
  '26266': 'exposure',
  '26311': 'express',
  '26312': 'expulsion',
  '26313': 'exquisite',
  '26314': 'extended',
  '26315': 'extending',
  '26316': 'extent',
  '26321': 'extenuate',
  '26322': 'exterior',
  '26323': 'external',
  '26324': 'extinct',
  '26325': 'extortion',
  '26326': 'extradite',
  '26331': 'extras',
  '26332': 'extrovert',
  '26333': 'extrude',
  '26334': 'extruding',
  '26335': 'exuberant',
  '26336': 'fable',
  '26341': 'fabric',
  '26342': 'fabulous',
  '26343': 'facebook',
  '26344': 'facecloth',
  '26345': 'facedown',
  '26346': 'faceless',
  '26351': 'facelift',
  '26352': 'faceplate',
  '26353': 'faceted',
  '26354': 'facial',
  '26355': 'facility',
  '26356': 'facing',
  '26361': 'facsimile',
  '26362': 'faction',
  '26363': 'factoid',
  '26364': 'factor',
  '26365': 'factsheet',
  '26366': 'factual',
  '26411': 'faculty',
  '26412': 'fade',
  '26413': 'fading',
  '26414': 'failing',
  '26415': 'falcon',
  '26416': 'fall',
  '26421': 'false',
  '26422': 'falsify',
  '26423': 'fame',
  '26424': 'familiar',
  '26425': 'family',
  '26426': 'famine',
  '26431': 'famished',
  '26432': 'fanatic',
  '26433': 'fancied',
  '26434': 'fanciness',
  '26435': 'fancy',
  '26436': 'fanfare',
  '26441': 'fang',
  '26442': 'fanning',
  '26443': 'fantasize',
  '26444': 'fantastic',
  '26445': 'fantasy',
  '26446': 'fascism',
  '26451': 'fastball',
  '26452': 'faster',
  '26453': 'fasting',
  '26454': 'fastness',
  '26455': 'faucet',
  '26456': 'favorable',
  '26461': 'favorably',
  '26462': 'favored',
  '26463': 'favoring',
  '26464': 'favorite',
  '26465': 'fax',
  '26466': 'feast',
  '26511': 'federal',
  '26512': 'fedora',
  '26513': 'feeble',
  '26514': 'feed',
  '26515': 'feel',
  '26516': 'feisty',
  '26521': 'feline',
  '26522': 'felt-tip',
  '26523': 'feminine',
  '26524': 'feminism',
  '26525': 'feminist',
  '26526': 'feminize',
  '26531': 'femur',
  '26532': 'fence',
  '26533': 'fencing',
  '26534': 'fender',
  '26535': 'ferment',
  '26536': 'fernlike',
  '26541': 'ferocious',
  '26542': 'ferocity',
  '26543': 'ferret',
  '26544': 'ferris',
  '26545': 'ferry',
  '26546': 'fervor',
  '26551': 'fester',
  '26552': 'festival',
  '26553': 'festive',
  '26554': 'festivity',
  '26555': 'fetal',
  '26556': 'fetch',
  '26561': 'fever',
  '26562': 'fiber',
  '26563': 'fiction',
  '26564': 'fiddle',
  '26565': 'fiddling',
  '26566': 'fidelity',
  '26611': 'fidgeting',
  '26612': 'fidgety',
  '26613': 'fifteen',
  '26614': 'fifth',
  '26615': 'fiftieth',
  '26616': 'fifty',
  '26621': 'figment',
  '26622': 'figure',
  '26623': 'figurine',
  '26624': 'filing',
  '26625': 'filled',
  '26626': 'filler',
  '26631': 'filling',
  '26632': 'film',
  '26633': 'filter',
  '26634': 'filth',
  '26635': 'filtrate',
  '26636': 'finale',
  '26641': 'finalist',
  '26642': 'finalize',
  '26643': 'finally',
  '26644': 'finance',
  '26645': 'financial',
  '26646': 'finch',
  '26651': 'fineness',
  '26652': 'finer',
  '26653': 'finicky',
  '26654': 'finished',
  '26655': 'finisher',
  '26656': 'finishing',
  '26661': 'finite',
  '26662': 'finless',
  '26663': 'finlike',
  '26664': 'fiscally',
  '26665': 'fit',
  '26666': 'five',
  '31111': 'flaccid',
  '31112': 'flagman',
  '31113': 'flagpole',
  '31114': 'flagship',
  '31115': 'flagstick',
  '31116': 'flagstone',
  '31121': 'flail',
  '31122': 'flakily',
  '31123': 'flaky',
  '31124': 'flame',
  '31125': 'flammable',
  '31126': 'flanked',
  '31131': 'flanking',
  '31132': 'flannels',
  '31133': 'flap',
  '31134': 'flaring',
  '31135': 'flashback',
  '31136': 'flashbulb',
  '31141': 'flashcard',
  '31142': 'flashily',
  '31143': 'flashing',
  '31144': 'flashy',
  '31145': 'flask',
  '31146': 'flatbed',
  '31151': 'flatfoot',
  '31152': 'flatly',
  '31153': 'flatness',
  '31154': 'flatten',
  '31155': 'flattered',
  '31156': 'flatterer',
  '31161': 'flattery',
  '31162': 'flattop',
  '31163': 'flatware',
  '31164': 'flatworm',
  '31165': 'flavored',
  '31166': 'flavorful',
  '31211': 'flavoring',
  '31212': 'flaxseed',
  '31213': 'fled',
  '31214': 'fleshed',
  '31215': 'fleshy',
  '31216': 'flick',
  '31221': 'flier',
  '31222': 'flight',
  '31223': 'flinch',
  '31224': 'fling',
  '31225': 'flint',
  '31226': 'flip',
  '31231': 'flirt',
  '31232': 'float',
  '31233': 'flock',
  '31234': 'flogging',
  '31235': 'flop',
  '31236': 'floral',
  '31241': 'florist',
  '31242': 'floss',
  '31243': 'flounder',
  '31244': 'flyable',
  '31245': 'flyaway',
  '31246': 'flyer',
  '31251': 'flying',
  '31252': 'flyover',
  '31253': 'flypaper',
  '31254': 'foam',
  '31255': 'foe',
  '31256': 'fog',
  '31261': 'foil',
  '31262': 'folic',
  '31263': 'folk',
  '31264': 'follicle',
  '31265': 'follow',
  '31266': 'fondling',
  '31311': 'fondly',
  '31312': 'fondness',
  '31313': 'fondue',
  '31314': 'font',
  '31315': 'food',
  '31316': 'fool',
  '31321': 'footage',
  '31322': 'football',
  '31323': 'footbath',
  '31324': 'footboard',
  '31325': 'footer',
  '31326': 'footgear',
  '31331': 'foothill',
  '31332': 'foothold',
  '31333': 'footing',
  '31334': 'footless',
  '31335': 'footman',
  '31336': 'footnote',
  '31341': 'footpad',
  '31342': 'footpath',
  '31343': 'footprint',
  '31344': 'footrest',
  '31345': 'footsie',
  '31346': 'footsore',
  '31351': 'footwear',
  '31352': 'footwork',
  '31353': 'fossil',
  '31354': 'foster',
  '31355': 'founder',
  '31356': 'founding',
  '31361': 'fountain',
  '31362': 'fox',
  '31363': 'foyer',
  '31364': 'fraction',
  '31365': 'fracture',
  '31366': 'fragile',
  '31411': 'fragility',
  '31412': 'fragment',
  '31413': 'fragrance',
  '31414': 'fragrant',
  '31415': 'frail',
  '31416': 'frame',
  '31421': 'framing',
  '31422': 'frantic',
  '31423': 'fraternal',
  '31424': 'frayed',
  '31425': 'fraying',
  '31426': 'frays',
  '31431': 'freckled',
  '31432': 'freckles',
  '31433': 'freebase',
  '31434': 'freebee',
  '31435': 'freebie',
  '31436': 'freedom',
  '31441': 'freefall',
  '31442': 'freehand',
  '31443': 'freeing',
  '31444': 'freeload',
  '31445': 'freely',
  '31446': 'freemason',
  '31451': 'freeness',
  '31452': 'freestyle',
  '31453': 'freeware',
  '31454': 'freeway',
  '31455': 'freewill',
  '31456': 'freezable',
  '31461': 'freezing',
  '31462': 'freight',
  '31463': 'french',
  '31464': 'frenzied',
  '31465': 'frenzy',
  '31466': 'frequency',
  '31511': 'frequent',
  '31512': 'fresh',
  '31513': 'fretful',
  '31514': 'fretted',
  '31515': 'friction',
  '31516': 'friday',
  '31521': 'fridge',
  '31522': 'fried',
  '31523': 'friend',
  '31524': 'frighten',
  '31525': 'frightful',
  '31526': 'frigidity',
  '31531': 'frigidly',
  '31532': 'frill',
  '31533': 'fringe',
  '31534': 'frisbee',
  '31535': 'frisk',
  '31536': 'fritter',
  '31541': 'frivolous',
  '31542': 'frolic',
  '31543': 'from',
  '31544': 'front',
  '31545': 'frostbite',
  '31546': 'frosted',
  '31551': 'frostily',
  '31552': 'frosting',
  '31553': 'frostlike',
  '31554': 'frosty',
  '31555': 'froth',
  '31556': 'frown',
  '31561': 'frozen',
  '31562': 'fructose',
  '31563': 'frugality',
  '31564': 'frugally',
  '31565': 'fruit',
  '31566': 'frustrate',
  '31611': 'frying',
  '31612': 'gab',
  '31613': 'gaffe',
  '31614': 'gag',
  '31615': 'gainfully',
  '31616': 'gaining',
  '31621': 'gains',
  '31622': 'gala',
  '31623': 'gallantly',
  '31624': 'galleria',
  '31625': 'gallery',
  '31626': 'galley',
  '31631': 'gallon',
  '31632': 'gallows',
  '31633': 'gallstone',
  '31634': 'galore',
  '31635': 'galvanize',
  '31636': 'gambling',
  '31641': 'game',
  '31642': 'gaming',
  '31643': 'gamma',
  '31644': 'gander',
  '31645': 'gangly',
  '31646': 'gangrene',
  '31651': 'gangway',
  '31652': 'gap',
  '31653': 'garage',
  '31654': 'garbage',
  '31655': 'garden',
  '31656': 'gargle',
  '31661': 'garland',
  '31662': 'garlic',
  '31663': 'garment',
  '31664': 'garnet',
  '31665': 'garnish',
  '31666': 'garter',
  '32111': 'gas',
  '32112': 'gatherer',
  '32113': 'gathering',
  '32114': 'gating',
  '32115': 'gauging',
  '32116': 'gauntlet',
  '32121': 'gauze',
  '32122': 'gave',
  '32123': 'gawk',
  '32124': 'gazing',
  '32125': 'gear',
  '32126': 'gecko',
  '32131': 'geek',
  '32132': 'geiger',
  '32133': 'gem',
  '32134': 'gender',
  '32135': 'generic',
  '32136': 'generous',
  '32141': 'genetics',
  '32142': 'genre',
  '32143': 'gentile',
  '32144': 'gentleman',
  '32145': 'gently',
  '32146': 'gents',
  '32151': 'geography',
  '32152': 'geologic',
  '32153': 'geologist',
  '32154': 'geology',
  '32155': 'geometric',
  '32156': 'geometry',
  '32161': 'geranium',
  '32162': 'gerbil',
  '32163': 'geriatric',
  '32164': 'germicide',
  '32165': 'germinate',
  '32166': 'germless',
  '32211': 'germproof',
  '32212': 'gestate',
  '32213': 'gestation',
  '32214': 'gesture',
  '32215': 'getaway',
  '32216': 'getting',
  '32221': 'getup',
  '32222': 'giant',
  '32223': 'gibberish',
  '32224': 'giblet',
  '32225': 'giddily',
  '32226': 'giddiness',
  '32231': 'giddy',
  '32232': 'gift',
  '32233': 'gigabyte',
  '32234': 'gigahertz',
  '32235': 'gigantic',
  '32236': 'giggle',
  '32241': 'giggling',
  '32242': 'giggly',
  '32243': 'gigolo',
  '32244': 'gilled',
  '32245': 'gills',
  '32246': 'gimmick',
  '32251': 'girdle',
  '32252': 'giveaway',
  '32253': 'given',
  '32254': 'giver',
  '32255': 'giving',
  '32256': 'gizmo',
  '32261': 'gizzard',
  '32262': 'glacial',
  '32263': 'glacier',
  '32264': 'glade',
  '32265': 'gladiator',
  '32266': 'gladly',
  '32311': 'glamorous',
  '32312': 'glamour',
  '32313': 'glance',
  '32314': 'glancing',
  '32315': 'glandular',
  '32316': 'glare',
  '32321': 'glaring',
  '32322': 'glass',
  '32323': 'glaucoma',
  '32324': 'glazing',
  '32325': 'gleaming',
  '32326': 'gleeful',
  '32331': 'glider',
  '32332': 'gliding',
  '32333': 'glimmer',
  '32334': 'glimpse',
  '32335': 'glisten',
  '32336': 'glitch',
  '32341': 'glitter',
  '32342': 'glitzy',
  '32343': 'gloater',
  '32344': 'gloating',
  '32345': 'gloomily',
  '32346': 'gloomy',
  '32351': 'glorified',
  '32352': 'glorifier',
  '32353': 'glorify',
  '32354': 'glorious',
  '32355': 'glory',
  '32356': 'gloss',
  '32361': 'glove',
  '32362': 'glowing',
  '32363': 'glowworm',
  '32364': 'glucose',
  '32365': 'glue',
  '32366': 'gluten',
  '32411': 'glutinous',
  '32412': 'glutton',
  '32413': 'gnarly',
  '32414': 'gnat',
  '32415': 'goal',
  '32416': 'goatskin',
  '32421': 'goes',
  '32422': 'goggles',
  '32423': 'going',
  '32424': 'goldfish',
  '32425': 'goldmine',
  '32426': 'goldsmith',
  '32431': 'golf',
  '32432': 'goliath',
  '32433': 'gonad',
  '32434': 'gondola',
  '32435': 'gone',
  '32436': 'gong',
  '32441': 'good',
  '32442': 'gooey',
  '32443': 'goofball',
  '32444': 'goofiness',
  '32445': 'goofy',
  '32446': 'google',
  '32451': 'goon',
  '32452': 'gopher',
  '32453': 'gore',
  '32454': 'gorged',
  '32455': 'gorgeous',
  '32456': 'gory',
  '32461': 'gosling',
  '32462': 'gossip',
  '32463': 'gothic',
  '32464': 'gotten',
  '32465': 'gout',
  '32466': 'gown',
  '32511': 'grab',
  '32512': 'graceful',
  '32513': 'graceless',
  '32514': 'gracious',
  '32515': 'gradation',
  '32516': 'graded',
  '32521': 'grader',
  '32522': 'gradient',
  '32523': 'grading',
  '32524': 'gradually',
  '32525': 'graduate',
  '32526': 'graffiti',
  '32531': 'grafted',
  '32532': 'grafting',
  '32533': 'grain',
  '32534': 'granddad',
  '32535': 'grandkid',
  '32536': 'grandly',
  '32541': 'grandma',
  '32542': 'grandpa',
  '32543': 'grandson',
  '32544': 'granite',
  '32545': 'granny',
  '32546': 'granola',
  '32551': 'grant',
  '32552': 'granular',
  '32553': 'grape',
  '32554': 'graph',
  '32555': 'grapple',
  '32556': 'grappling',
  '32561': 'grasp',
  '32562': 'grass',
  '32563': 'gratified',
  '32564': 'gratify',
  '32565': 'grating',
  '32566': 'gratitude',
  '32611': 'gratuity',
  '32612': 'gravel',
  '32613': 'graveness',
  '32614': 'graves',
  '32615': 'graveyard',
  '32616': 'gravitate',
  '32621': 'gravity',
  '32622': 'gravy',
  '32623': 'gray',
  '32624': 'grazing',
  '32625': 'greasily',
  '32626': 'greedily',
  '32631': 'greedless',
  '32632': 'greedy',
  '32633': 'green',
  '32634': 'greeter',
  '32635': 'greeting',
  '32636': 'grew',
  '32641': 'greyhound',
  '32642': 'grid',
  '32643': 'grief',
  '32644': 'grievance',
  '32645': 'grieving',
  '32646': 'grievous',
  '32651': 'grill',
  '32652': 'grimace',
  '32653': 'grimacing',
  '32654': 'grime',
  '32655': 'griminess',
  '32656': 'grimy',
  '32661': 'grinch',
  '32662': 'grinning',
  '32663': 'grip',
  '32664': 'gristle',
  '32665': 'grit',
  '32666': 'groggily',
  '33111': 'groggy',
  '33112': 'groin',
  '33113': 'groom',
  '33114': 'groove',
  '33115': 'grooving',
  '33116': 'groovy',
  '33121': 'grope',
  '33122': 'ground',
  '33123': 'grouped',
  '33124': 'grout',
  '33125': 'grove',
  '33126': 'grower',
  '33131': 'growing',
  '33132': 'growl',
  '33133': 'grub',
  '33134': 'grudge',
  '33135': 'grudging',
  '33136': 'grueling',
  '33141': 'gruffly',
  '33142': 'grumble',
  '33143': 'grumbling',
  '33144': 'grumbly',
  '33145': 'grumpily',
  '33146': 'grunge',
  '33151': 'grunt',
  '33152': 'guacamole',
  '33153': 'guidable',
  '33154': 'guidance',
  '33155': 'guide',
  '33156': 'guiding',
  '33161': 'guileless',
  '33162': 'guise',
  '33163': 'gulf',
  '33164': 'gullible',
  '33165': 'gully',
  '33166': 'gulp',
  '33211': 'gumball',
  '33212': 'gumdrop',
  '33213': 'gumminess',
  '33214': 'gumming',
  '33215': 'gummy',
  '33216': 'gurgle',
  '33221': 'gurgling',
  '33222': 'guru',
  '33223': 'gush',
  '33224': 'gusto',
  '33225': 'gusty',
  '33226': 'gutless',
  '33231': 'guts',
  '33232': 'gutter',
  '33233': 'guy',
  '33234': 'guzzler',
  '33235': 'gyration',
  '33236': 'habitable',
  '33241': 'habitant',
  '33242': 'habitat',
  '33243': 'habitual',
  '33244': 'hacked',
  '33245': 'hacker',
  '33246': 'hacking',
  '33251': 'hacksaw',
  '33252': 'had',
  '33253': 'haggler',
  '33254': 'haiku',
  '33255': 'half',
  '33256': 'halogen',
  '33261': 'halt',
  '33262': 'halved',
  '33263': 'halves',
  '33264': 'hamburger',
  '33265': 'hamlet',
  '33266': 'hammock',
  '33311': 'hamper',
  '33312': 'hamster',
  '33313': 'hamstring',
  '33314': 'handbag',
  '33315': 'handball',
  '33316': 'handbook',
  '33321': 'handbrake',
  '33322': 'handcart',
  '33323': 'handclap',
  '33324': 'handclasp',
  '33325': 'handcraft',
  '33326': 'handcuff',
  '33331': 'handed',
  '33332': 'handful',
  '33333': 'handgrip',
  '33334': 'handgun',
  '33335': 'handheld',
  '33336': 'handiness',
  '33341': 'handiwork',
  '33342': 'handlebar',
  '33343': 'handled',
  '33344': 'handler',
  '33345': 'handling',
  '33346': 'handmade',
  '33351': 'handoff',
  '33352': 'handpick',
  '33353': 'handprint',
  '33354': 'handrail',
  '33355': 'handsaw',
  '33356': 'handset',
  '33361': 'handsfree',
  '33362': 'handshake',
  '33363': 'handstand',
  '33364': 'handwash',
  '33365': 'handwork',
  '33366': 'handwoven',
  '33411': 'handwrite',
  '33412': 'handyman',
  '33413': 'hangnail',
  '33414': 'hangout',
  '33415': 'hangover',
  '33416': 'hangup',
  '33421': 'hankering',
  '33422': 'hankie',
  '33423': 'hanky',
  '33424': 'haphazard',
  '33425': 'happening',
  '33426': 'happier',
  '33431': 'happiest',
  '33432': 'happily',
  '33433': 'happiness',
  '33434': 'happy',
  '33435': 'harbor',
  '33436': 'hardcopy',
  '33441': 'hardcore',
  '33442': 'hardcover',
  '33443': 'harddisk',
  '33444': 'hardened',
  '33445': 'hardener',
  '33446': 'hardening',
  '33451': 'hardhat',
  '33452': 'hardhead',
  '33453': 'hardiness',
  '33454': 'hardly',
  '33455': 'hardness',
  '33456': 'hardship',
  '33461': 'hardware',
  '33462': 'hardwired',
  '33463': 'hardwood',
  '33464': 'hardy',
  '33465': 'harmful',
  '33466': 'harmless',
  '33511': 'harmonica',
  '33512': 'harmonics',
  '33513': 'harmonize',
  '33514': 'harmony',
  '33515': 'harness',
  '33516': 'harpist',
  '33521': 'harsh',
  '33522': 'harvest',
  '33523': 'hash',
  '33524': 'hassle',
  '33525': 'haste',
  '33526': 'hastily',
  '33531': 'hastiness',
  '33532': 'hasty',
  '33533': 'hatbox',
  '33534': 'hatchback',
  '33535': 'hatchery',
  '33536': 'hatchet',
  '33541': 'hatching',
  '33542': 'hatchling',
  '33543': 'hate',
  '33544': 'hatless',
  '33545': 'hatred',
  '33546': 'haunt',
  '33551': 'haven',
  '33552': 'hazard',
  '33553': 'hazelnut',
  '33554': 'hazily',
  '33555': 'haziness',
  '33556': 'hazing',
  '33561': 'hazy',
  '33562': 'headache',
  '33563': 'headband',
  '33564': 'headboard',
  '33565': 'headcount',
  '33566': 'headdress',
  '33611': 'headed',
  '33612': 'header',
  '33613': 'headfirst',
  '33614': 'headgear',
  '33615': 'heading',
  '33616': 'headlamp',
  '33621': 'headless',
  '33622': 'headlock',
  '33623': 'headphone',
  '33624': 'headpiece',
  '33625': 'headrest',
  '33626': 'headroom',
  '33631': 'headscarf',
  '33632': 'headset',
  '33633': 'headsman',
  '33634': 'headstand',
  '33635': 'headstone',
  '33636': 'headway',
  '33641': 'headwear',
  '33642': 'heap',
  '33643': 'heat',
  '33644': 'heave',
  '33645': 'heavily',
  '33646': 'heaviness',
  '33651': 'heaving',
  '33652': 'hedge',
  '33653': 'hedging',
  '33654': 'heftiness',
  '33655': 'hefty',
  '33656': 'helium',
  '33661': 'helmet',
  '33662': 'helper',
  '33663': 'helpful',
  '33664': 'helping',
  '33665': 'helpless',
  '33666': 'helpline',
  '34111': 'hemlock',
  '34112': 'hemstitch',
  '34113': 'hence',
  '34114': 'henchman',
  '34115': 'henna',
  '34116': 'herald',
  '34121': 'herbal',
  '34122': 'herbicide',
  '34123': 'herbs',
  '34124': 'heritage',
  '34125': 'hermit',
  '34126': 'heroics',
  '34131': 'heroism',
  '34132': 'herring',
  '34133': 'herself',
  '34134': 'hertz',
  '34135': 'hesitancy',
  '34136': 'hesitant',
  '34141': 'hesitate',
  '34142': 'hexagon',
  '34143': 'hexagram',
  '34144': 'hubcap',
  '34145': 'huddle',
  '34146': 'huddling',
  '34151': 'huff',
  '34152': 'hug',
  '34153': 'hula',
  '34154': 'hulk',
  '34155': 'hull',
  '34156': 'human',
  '34161': 'humble',
  '34162': 'humbling',
  '34163': 'humbly',
  '34164': 'humid',
  '34165': 'humiliate',
  '34166': 'humility',
  '34211': 'humming',
  '34212': 'hummus',
  '34213': 'humongous',
  '34214': 'humorist',
  '34215': 'humorless',
  '34216': 'humorous',
  '34221': 'humpback',
  '34222': 'humped',
  '34223': 'humvee',
  '34224': 'hunchback',
  '34225': 'hundredth',
  '34226': 'hunger',
  '34231': 'hungrily',
  '34232': 'hungry',
  '34233': 'hunk',
  '34234': 'hunter',
  '34235': 'hunting',
  '34236': 'huntress',
  '34241': 'huntsman',
  '34242': 'hurdle',
  '34243': 'hurled',
  '34244': 'hurler',
  '34245': 'hurling',
  '34246': 'hurray',
  '34251': 'hurricane',
  '34252': 'hurried',
  '34253': 'hurry',
  '34254': 'hurt',
  '34255': 'husband',
  '34256': 'hush',
  '34261': 'husked',
  '34262': 'huskiness',
  '34263': 'hut',
  '34264': 'hybrid',
  '34265': 'hydrant',
  '34266': 'hydrated',
  '34311': 'hydration',
  '34312': 'hydrogen',
  '34313': 'hydroxide',
  '34314': 'hyperlink',
  '34315': 'hypertext',
  '34316': 'hyphen',
  '34321': 'hypnoses',
  '34322': 'hypnosis',
  '34323': 'hypnotic',
  '34324': 'hypnotism',
  '34325': 'hypnotist',
  '34326': 'hypnotize',
  '34331': 'hypocrisy',
  '34332': 'hypocrite',
  '34333': 'ibuprofen',
  '34334': 'ice',
  '34335': 'iciness',
  '34336': 'icing',
  '34341': 'icky',
  '34342': 'icon',
  '34343': 'icy',
  '34344': 'idealism',
  '34345': 'idealist',
  '34346': 'idealize',
  '34351': 'ideally',
  '34352': 'idealness',
  '34353': 'identical',
  '34354': 'identify',
  '34355': 'identity',
  '34356': 'ideology',
  '34361': 'idiocy',
  '34362': 'idiom',
  '34363': 'idly',
  '34364': 'igloo',
  '34365': 'ignition',
  '34366': 'ignore',
  '34411': 'iguana',
  '34412': 'illicitly',
  '34413': 'illusion',
  '34414': 'illusive',
  '34415': 'image',
  '34416': 'imaginary',
  '34421': 'imagines',
  '34422': 'imaging',
  '34423': 'imbecile',
  '34424': 'imitate',
  '34425': 'imitation',
  '34426': 'immature',
  '34431': 'immerse',
  '34432': 'immersion',
  '34433': 'imminent',
  '34434': 'immobile',
  '34435': 'immodest',
  '34436': 'immorally',
  '34441': 'immortal',
  '34442': 'immovable',
  '34443': 'immovably',
  '34444': 'immunity',
  '34445': 'immunize',
  '34446': 'impaired',
  '34451': 'impale',
  '34452': 'impart',
  '34453': 'impatient',
  '34454': 'impeach',
  '34455': 'impeding',
  '34456': 'impending',
  '34461': 'imperfect',
  '34462': 'imperial',
  '34463': 'impish',
  '34464': 'implant',
  '34465': 'implement',
  '34466': 'implicate',
  '34511': 'implicit',
  '34512': 'implode',
  '34513': 'implosion',
  '34514': 'implosive',
  '34515': 'imply',
  '34516': 'impolite',
  '34521': 'important',
  '34522': 'importer',
  '34523': 'impose',
  '34524': 'imposing',
  '34525': 'impotence',
  '34526': 'impotency',
  '34531': 'impotent',
  '34532': 'impound',
  '34533': 'imprecise',
  '34534': 'imprint',
  '34535': 'imprison',
  '34536': 'impromptu',
  '34541': 'improper',
  '34542': 'improve',
  '34543': 'improving',
  '34544': 'improvise',
  '34545': 'imprudent',
  '34546': 'impulse',
  '34551': 'impulsive',
  '34552': 'impure',
  '34553': 'impurity',
  '34554': 'iodine',
  '34555': 'iodize',
  '34556': 'ion',
  '34561': 'ipad',
  '34562': 'iphone',
  '34563': 'ipod',
  '34564': 'irate',
  '34565': 'irk',
  '34566': 'iron',
  '34611': 'irregular',
  '34612': 'irrigate',
  '34613': 'irritable',
  '34614': 'irritably',
  '34615': 'irritant',
  '34616': 'irritate',
  '34621': 'islamic',
  '34622': 'islamist',
  '34623': 'isolated',
  '34624': 'isolating',
  '34625': 'isolation',
  '34626': 'isotope',
  '34631': 'issue',
  '34632': 'issuing',
  '34633': 'italicize',
  '34634': 'italics',
  '34635': 'item',
  '34636': 'itinerary',
  '34641': 'itunes',
  '34642': 'ivory',
  '34643': 'ivy',
  '34644': 'jab',
  '34645': 'jackal',
  '34646': 'jacket',
  '34651': 'jackknife',
  '34652': 'jackpot',
  '34653': 'jailbird',
  '34654': 'jailbreak',
  '34655': 'jailer',
  '34656': 'jailhouse',
  '34661': 'jalapeno',
  '34662': 'jam',
  '34663': 'janitor',
  '34664': 'january',
  '34665': 'jargon',
  '34666': 'jarring',
  '35111': 'jasmine',
  '35112': 'jaundice',
  '35113': 'jaunt',
  '35114': 'java',
  '35115': 'jawed',
  '35116': 'jawless',
  '35121': 'jawline',
  '35122': 'jaws',
  '35123': 'jaybird',
  '35124': 'jaywalker',
  '35125': 'jazz',
  '35126': 'jeep',
  '35131': 'jeeringly',
  '35132': 'jellied',
  '35133': 'jelly',
  '35134': 'jersey',
  '35135': 'jester',
  '35136': 'jet',
  '35141': 'jiffy',
  '35142': 'jigsaw',
  '35143': 'jimmy',
  '35144': 'jingle',
  '35145': 'jingling',
  '35146': 'jinx',
  '35151': 'jitters',
  '35152': 'jittery',
  '35153': 'job',
  '35154': 'jockey',
  '35155': 'jockstrap',
  '35156': 'jogger',
  '35161': 'jogging',
  '35162': 'john',
  '35163': 'joining',
  '35164': 'jokester',
  '35165': 'jokingly',
  '35166': 'jolliness',
  '35211': 'jolly',
  '35212': 'jolt',
  '35213': 'jot',
  '35214': 'jovial',
  '35215': 'joyfully',
  '35216': 'joylessly',
  '35221': 'joyous',
  '35222': 'joyride',
  '35223': 'joystick',
  '35224': 'jubilance',
  '35225': 'jubilant',
  '35226': 'judge',
  '35231': 'judgingly',
  '35232': 'judicial',
  '35233': 'judiciary',
  '35234': 'judo',
  '35235': 'juggle',
  '35236': 'juggling',
  '35241': 'jugular',
  '35242': 'juice',
  '35243': 'juiciness',
  '35244': 'juicy',
  '35245': 'jujitsu',
  '35246': 'jukebox',
  '35251': 'july',
  '35252': 'jumble',
  '35253': 'jumbo',
  '35254': 'jump',
  '35255': 'junction',
  '35256': 'juncture',
  '35261': 'june',
  '35262': 'junior',
  '35263': 'juniper',
  '35264': 'junkie',
  '35265': 'junkman',
  '35266': 'junkyard',
  '35311': 'jurist',
  '35312': 'juror',
  '35313': 'jury',
  '35314': 'justice',
  '35315': 'justifier',
  '35316': 'justify',
  '35321': 'justly',
  '35322': 'justness',
  '35323': 'juvenile',
  '35324': 'kabob',
  '35325': 'kangaroo',
  '35326': 'karaoke',
  '35331': 'karate',
  '35332': 'karma',
  '35333': 'kebab',
  '35334': 'keenly',
  '35335': 'keenness',
  '35336': 'keep',
  '35341': 'keg',
  '35342': 'kelp',
  '35343': 'kennel',
  '35344': 'kept',
  '35345': 'kerchief',
  '35346': 'kerosene',
  '35351': 'kettle',
  '35352': 'kick',
  '35353': 'kiln',
  '35354': 'kilobyte',
  '35355': 'kilogram',
  '35356': 'kilometer',
  '35361': 'kilowatt',
  '35362': 'kilt',
  '35363': 'kimono',
  '35364': 'kindle',
  '35365': 'kindling',
  '35366': 'kindly',
  '35411': 'kindness',
  '35412': 'kindred',
  '35413': 'kinetic',
  '35414': 'kinfolk',
  '35415': 'king',
  '35416': 'kinship',
  '35421': 'kinsman',
  '35422': 'kinswoman',
  '35423': 'kissable',
  '35424': 'kisser',
  '35425': 'kissing',
  '35426': 'kitchen',
  '35431': 'kite',
  '35432': 'kitten',
  '35433': 'kitty',
  '35434': 'kiwi',
  '35435': 'kleenex',
  '35436': 'knapsack',
  '35441': 'knee',
  '35442': 'knelt',
  '35443': 'knickers',
  '35444': 'knoll',
  '35445': 'koala',
  '35446': 'kooky',
  '35451': 'kosher',
  '35452': 'krypton',
  '35453': 'kudos',
  '35454': 'kung',
  '35455': 'labored',
  '35456': 'laborer',
  '35461': 'laboring',
  '35462': 'laborious',
  '35463': 'labrador',
  '35464': 'ladder',
  '35465': 'ladies',
  '35466': 'ladle',
  '35511': 'ladybug',
  '35512': 'ladylike',
  '35513': 'lagged',
  '35514': 'lagging',
  '35515': 'lagoon',
  '35516': 'lair',
  '35521': 'lake',
  '35522': 'lance',
  '35523': 'landed',
  '35524': 'landfall',
  '35525': 'landfill',
  '35526': 'landing',
  '35531': 'landlady',
  '35532': 'landless',
  '35533': 'landline',
  '35534': 'landlord',
  '35535': 'landmark',
  '35536': 'landmass',
  '35541': 'landmine',
  '35542': 'landowner',
  '35543': 'landscape',
  '35544': 'landside',
  '35545': 'landslide',
  '35546': 'language',
  '35551': 'lankiness',
  '35552': 'lanky',
  '35553': 'lantern',
  '35554': 'lapdog',
  '35555': 'lapel',
  '35556': 'lapped',
  '35561': 'lapping',
  '35562': 'laptop',
  '35563': 'lard',
  '35564': 'large',
  '35565': 'lark',
  '35566': 'lash',
  '35611': 'lasso',
  '35612': 'last',
  '35613': 'latch',
  '35614': 'late',
  '35615': 'lather',
  '35616': 'latitude',
  '35621': 'latrine',
  '35622': 'latter',
  '35623': 'latticed',
  '35624': 'launch',
  '35625': 'launder',
  '35626': 'laundry',
  '35631': 'laurel',
  '35632': 'lavender',
  '35633': 'lavish',
  '35634': 'laxative',
  '35635': 'lazily',
  '35636': 'laziness',
  '35641': 'lazy',
  '35642': 'lecturer',
  '35643': 'left',
  '35644': 'legacy',
  '35645': 'legal',
  '35646': 'legend',
  '35651': 'legged',
  '35652': 'leggings',
  '35653': 'legible',
  '35654': 'legibly',
  '35655': 'legislate',
  '35656': 'lego',
  '35661': 'legroom',
  '35662': 'legume',
  '35663': 'legwarmer',
  '35664': 'legwork',
  '35665': 'lemon',
  '35666': 'lend',
  '36111': 'length',
  '36112': 'lens',
  '36113': 'lent',
  '36114': 'leotard',
  '36115': 'lesser',
  '36116': 'letdown',
  '36121': 'lethargic',
  '36122': 'lethargy',
  '36123': 'letter',
  '36124': 'lettuce',
  '36125': 'level',
  '36126': 'leverage',
  '36131': 'levers',
  '36132': 'levitate',
  '36133': 'levitator',
  '36134': 'liability',
  '36135': 'liable',
  '36136': 'liberty',
  '36141': 'librarian',
  '36142': 'library',
  '36143': 'licking',
  '36144': 'licorice',
  '36145': 'lid',
  '36146': 'life',
  '36151': 'lifter',
  '36152': 'lifting',
  '36153': 'liftoff',
  '36154': 'ligament',
  '36155': 'likely',
  '36156': 'likeness',
  '36161': 'likewise',
  '36162': 'liking',
  '36163': 'lilac',
  '36164': 'lilly',
  '36165': 'lily',
  '36166': 'limb',
  '36211': 'limeade',
  '36212': 'limelight',
  '36213': 'limes',
  '36214': 'limit',
  '36215': 'limping',
  '36216': 'limpness',
  '36221': 'line',
  '36222': 'lingo',
  '36223': 'linguini',
  '36224': 'linguist',
  '36225': 'lining',
  '36226': 'linked',
  '36231': 'linoleum',
  '36232': 'linseed',
  '36233': 'lint',
  '36234': 'lion',
  '36235': 'lip',
  '36236': 'liquefy',
  '36241': 'liqueur',
  '36242': 'liquid',
  '36243': 'lisp',
  '36244': 'list',
  '36245': 'litigate',
  '36246': 'litigator',
  '36251': 'litmus',
  '36252': 'litter',
  '36253': 'little',
  '36254': 'livable',
  '36255': 'lived',
  '36256': 'lively',
  '36261': 'liver',
  '36262': 'livestock',
  '36263': 'lividly',
  '36264': 'living',
  '36265': 'lizard',
  '36266': 'lubricant',
  '36311': 'lubricate',
  '36312': 'lucid',
  '36313': 'luckily',
  '36314': 'luckiness',
  '36315': 'luckless',
  '36316': 'lucrative',
  '36321': 'ludicrous',
  '36322': 'lugged',
  '36323': 'lukewarm',
  '36324': 'lullaby',
  '36325': 'lumber',
  '36326': 'luminance',
  '36331': 'luminous',
  '36332': 'lumpiness',
  '36333': 'lumping',
  '36334': 'lumpish',
  '36335': 'lunacy',
  '36336': 'lunar',
  '36341': 'lunchbox',
  '36342': 'luncheon',
  '36343': 'lunchroom',
  '36344': 'lunchtime',
  '36345': 'lung',
  '36346': 'lurch',
  '36351': 'lure',
  '36352': 'luridness',
  '36353': 'lurk',
  '36354': 'lushly',
  '36355': 'lushness',
  '36356': 'luster',
  '36361': 'lustfully',
  '36362': 'lustily',
  '36363': 'lustiness',
  '36364': 'lustrous',
  '36365': 'lusty',
  '36366': 'luxurious',
  '36411': 'luxury',
  '36412': 'lying',
  '36413': 'lyrically',
  '36414': 'lyricism',
  '36415': 'lyricist',
  '36416': 'lyrics',
  '36421': 'macarena',
  '36422': 'macaroni',
  '36423': 'macaw',
  '36424': 'mace',
  '36425': 'machine',
  '36426': 'machinist',
  '36431': 'magazine',
  '36432': 'magenta',
  '36433': 'maggot',
  '36434': 'magical',
  '36435': 'magician',
  '36436': 'magma',
  '36441': 'magnesium',
  '36442': 'magnetic',
  '36443': 'magnetism',
  '36444': 'magnetize',
  '36445': 'magnifier',
  '36446': 'magnify',
  '36451': 'magnitude',
  '36452': 'magnolia',
  '36453': 'mahogany',
  '36454': 'maimed',
  '36455': 'majestic',
  '36456': 'majesty',
  '36461': 'majorette',
  '36462': 'majority',
  '36463': 'makeover',
  '36464': 'maker',
  '36465': 'makeshift',
  '36466': 'making',
  '36511': 'malformed',
  '36512': 'malt',
  '36513': 'mama',
  '36514': 'mammal',
  '36515': 'mammary',
  '36516': 'mammogram',
  '36521': 'manager',
  '36522': 'managing',
  '36523': 'manatee',
  '36524': 'mandarin',
  '36525': 'mandate',
  '36526': 'mandatory',
  '36531': 'mandolin',
  '36532': 'manger',
  '36533': 'mangle',
  '36534': 'mango',
  '36535': 'mangy',
  '36536': 'manhandle',
  '36541': 'manhole',
  '36542': 'manhood',
  '36543': 'manhunt',
  '36544': 'manicotti',
  '36545': 'manicure',
  '36546': 'manifesto',
  '36551': 'manila',
  '36552': 'mankind',
  '36553': 'manlike',
  '36554': 'manliness',
  '36555': 'manly',
  '36556': 'manmade',
  '36561': 'manned',
  '36562': 'mannish',
  '36563': 'manor',
  '36564': 'manpower',
  '36565': 'mantis',
  '36566': 'mantra',
  '36611': 'manual',
  '36612': 'many',
  '36613': 'map',
  '36614': 'marathon',
  '36615': 'marauding',
  '36616': 'marbled',
  '36621': 'marbles',
  '36622': 'marbling',
  '36623': 'march',
  '36624': 'mardi',
  '36625': 'margarine',
  '36626': 'margarita',
  '36631': 'margin',
  '36632': 'marigold',
  '36633': 'marina',
  '36634': 'marine',
  '36635': 'marital',
  '36636': 'maritime',
  '36641': 'marlin',
  '36642': 'marmalade',
  '36643': 'maroon',
  '36644': 'married',
  '36645': 'marrow',
  '36646': 'marry',
  '36651': 'marshland',
  '36652': 'marshy',
  '36653': 'marsupial',
  '36654': 'marvelous',
  '36655': 'marxism',
  '36656': 'mascot',
  '36661': 'masculine',
  '36662': 'mashed',
  '36663': 'mashing',
  '36664': 'massager',
  '36665': 'masses',
  '36666': 'massive',
  '41111': 'mastiff',
  '41112': 'matador',
  '41113': 'matchbook',
  '41114': 'matchbox',
  '41115': 'matcher',
  '41116': 'matching',
  '41121': 'matchless',
  '41122': 'material',
  '41123': 'maternal',
  '41124': 'maternity',
  '41125': 'math',
  '41126': 'mating',
  '41131': 'matriarch',
  '41132': 'matrimony',
  '41133': 'matrix',
  '41134': 'matron',
  '41135': 'matted',
  '41136': 'matter',
  '41141': 'maturely',
  '41142': 'maturing',
  '41143': 'maturity',
  '41144': 'mauve',
  '41145': 'maverick',
  '41146': 'maximize',
  '41151': 'maximum',
  '41152': 'maybe',
  '41153': 'mayday',
  '41154': 'mayflower',
  '41155': 'moaner',
  '41156': 'moaning',
  '41161': 'mobile',
  '41162': 'mobility',
  '41163': 'mobilize',
  '41164': 'mobster',
  '41165': 'mocha',
  '41166': 'mocker',
  '41211': 'mockup',
  '41212': 'modified',
  '41213': 'modify',
  '41214': 'modular',
  '41215': 'modulator',
  '41216': 'module',
  '41221': 'moisten',
  '41222': 'moistness',
  '41223': 'moisture',
  '41224': 'molar',
  '41225': 'molasses',
  '41226': 'mold',
  '41231': 'molecular',
  '41232': 'molecule',
  '41233': 'molehill',
  '41234': 'mollusk',
  '41235': 'mom',
  '41236': 'monastery',
  '41241': 'monday',
  '41242': 'monetary',
  '41243': 'monetize',
  '41244': 'moneybags',
  '41245': 'moneyless',
  '41246': 'moneywise',
  '41251': 'mongoose',
  '41252': 'mongrel',
  '41253': 'monitor',
  '41254': 'monkhood',
  '41255': 'monogamy',
  '41256': 'monogram',
  '41261': 'monologue',
  '41262': 'monopoly',
  '41263': 'monorail',
  '41264': 'monotone',
  '41265': 'monotype',
  '41266': 'monoxide',
  '41311': 'monsieur',
  '41312': 'monsoon',
  '41313': 'monstrous',
  '41314': 'monthly',
  '41315': 'monument',
  '41316': 'moocher',
  '41321': 'moodiness',
  '41322': 'moody',
  '41323': 'mooing',
  '41324': 'moonbeam',
  '41325': 'mooned',
  '41326': 'moonlight',
  '41331': 'moonlike',
  '41332': 'moonlit',
  '41333': 'moonrise',
  '41334': 'moonscape',
  '41335': 'moonshine',
  '41336': 'moonstone',
  '41341': 'moonwalk',
  '41342': 'mop',
  '41343': 'morale',
  '41344': 'morality',
  '41345': 'morally',
  '41346': 'morbidity',
  '41351': 'morbidly',
  '41352': 'morphine',
  '41353': 'morphing',
  '41354': 'morse',
  '41355': 'mortality',
  '41356': 'mortally',
  '41361': 'mortician',
  '41362': 'mortified',
  '41363': 'mortify',
  '41364': 'mortuary',
  '41365': 'mosaic',
  '41366': 'mossy',
  '41411': 'most',
  '41412': 'mothball',
  '41413': 'mothproof',
  '41414': 'motion',
  '41415': 'motivate',
  '41416': 'motivator',
  '41421': 'motive',
  '41422': 'motocross',
  '41423': 'motor',
  '41424': 'motto',
  '41425': 'mountable',
  '41426': 'mountain',
  '41431': 'mounted',
  '41432': 'mounting',
  '41433': 'mourner',
  '41434': 'mournful',
  '41435': 'mouse',
  '41436': 'mousiness',
  '41441': 'moustache',
  '41442': 'mousy',
  '41443': 'mouth',
  '41444': 'movable',
  '41445': 'move',
  '41446': 'movie',
  '41451': 'moving',
  '41452': 'mower',
  '41453': 'mowing',
  '41454': 'much',
  '41455': 'muck',
  '41456': 'mud',
  '41461': 'mug',
  '41462': 'mulberry',
  '41463': 'mulch',
  '41464': 'mule',
  '41465': 'mulled',
  '41466': 'mullets',
  '41511': 'multiple',
  '41512': 'multiply',
  '41513': 'multitask',
  '41514': 'multitude',
  '41515': 'mumble',
  '41516': 'mumbling',
  '41521': 'mumbo',
  '41522': 'mummified',
  '41523': 'mummify',
  '41524': 'mummy',
  '41525': 'mumps',
  '41526': 'munchkin',
  '41531': 'mundane',
  '41532': 'municipal',
  '41533': 'muppet',
  '41534': 'mural',
  '41535': 'murkiness',
  '41536': 'murky',
  '41541': 'murmuring',
  '41542': 'muscular',
  '41543': 'museum',
  '41544': 'mushily',
  '41545': 'mushiness',
  '41546': 'mushroom',
  '41551': 'mushy',
  '41552': 'music',
  '41553': 'musket',
  '41554': 'muskiness',
  '41555': 'musky',
  '41556': 'mustang',
  '41561': 'mustard',
  '41562': 'muster',
  '41563': 'mustiness',
  '41564': 'musty',
  '41565': 'mutable',
  '41566': 'mutate',
  '41611': 'mutation',
  '41612': 'mute',
  '41613': 'mutilated',
  '41614': 'mutilator',
  '41615': 'mutiny',
  '41616': 'mutt',
  '41621': 'mutual',
  '41622': 'muzzle',
  '41623': 'myself',
  '41624': 'myspace',
  '41625': 'mystified',
  '41626': 'mystify',
  '41631': 'myth',
  '41632': 'nacho',
  '41633': 'nag',
  '41634': 'nail',
  '41635': 'name',
  '41636': 'naming',
  '41641': 'nanny',
  '41642': 'nanometer',
  '41643': 'nape',
  '41644': 'napkin',
  '41645': 'napped',
  '41646': 'napping',
  '41651': 'nappy',
  '41652': 'narrow',
  '41653': 'nastily',
  '41654': 'nastiness',
  '41655': 'national',
  '41656': 'native',
  '41661': 'nativity',
  '41662': 'natural',
  '41663': 'nature',
  '41664': 'naturist',
  '41665': 'nautical',
  '41666': 'navigate',
  '42111': 'navigator',
  '42112': 'navy',
  '42113': 'nearby',
  '42114': 'nearest',
  '42115': 'nearly',
  '42116': 'nearness',
  '42121': 'neatly',
  '42122': 'neatness',
  '42123': 'nebula',
  '42124': 'nebulizer',
  '42125': 'nectar',
  '42126': 'negate',
  '42131': 'negation',
  '42132': 'negative',
  '42133': 'neglector',
  '42134': 'negligee',
  '42135': 'negligent',
  '42136': 'negotiate',
  '42141': 'nemeses',
  '42142': 'nemesis',
  '42143': 'neon',
  '42144': 'nephew',
  '42145': 'nerd',
  '42146': 'nervous',
  '42151': 'nervy',
  '42152': 'nest',
  '42153': 'net',
  '42154': 'neurology',
  '42155': 'neuron',
  '42156': 'neurosis',
  '42161': 'neurotic',
  '42162': 'neuter',
  '42163': 'neutron',
  '42164': 'never',
  '42165': 'next',
  '42166': 'nibble',
  '42211': 'nickname',
  '42212': 'nicotine',
  '42213': 'niece',
  '42214': 'nifty',
  '42215': 'nimble',
  '42216': 'nimbly',
  '42221': 'nineteen',
  '42222': 'ninetieth',
  '42223': 'ninja',
  '42224': 'nintendo',
  '42225': 'ninth',
  '42226': 'nuclear',
  '42231': 'nuclei',
  '42232': 'nucleus',
  '42233': 'nugget',
  '42234': 'nullify',
  '42235': 'number',
  '42236': 'numbing',
  '42241': 'numbly',
  '42242': 'numbness',
  '42243': 'numeral',
  '42244': 'numerate',
  '42245': 'numerator',
  '42246': 'numeric',
  '42251': 'numerous',
  '42252': 'nuptials',
  '42253': 'nursery',
  '42254': 'nursing',
  '42255': 'nurture',
  '42256': 'nutcase',
  '42261': 'nutlike',
  '42262': 'nutmeg',
  '42263': 'nutrient',
  '42264': 'nutshell',
  '42265': 'nuttiness',
  '42266': 'nutty',
  '42311': 'nuzzle',
  '42312': 'nylon',
  '42313': 'oaf',
  '42314': 'oak',
  '42315': 'oasis',
  '42316': 'oat',
  '42321': 'obedience',
  '42322': 'obedient',
  '42323': 'obituary',
  '42324': 'object',
  '42325': 'obligate',
  '42326': 'obliged',
  '42331': 'oblivion',
  '42332': 'oblivious',
  '42333': 'oblong',
  '42334': 'obnoxious',
  '42335': 'oboe',
  '42336': 'obscure',
  '42341': 'obscurity',
  '42342': 'observant',
  '42343': 'observer',
  '42344': 'observing',
  '42345': 'obsessed',
  '42346': 'obsession',
  '42351': 'obsessive',
  '42352': 'obsolete',
  '42353': 'obstacle',
  '42354': 'obstinate',
  '42355': 'obstruct',
  '42356': 'obtain',
  '42361': 'obtrusive',
  '42362': 'obtuse',
  '42363': 'obvious',
  '42364': 'occultist',
  '42365': 'occupancy',
  '42366': 'occupant',
  '42411': 'occupier',
  '42412': 'occupy',
  '42413': 'ocean',
  '42414': 'ocelot',
  '42415': 'octagon',
  '42416': 'octane',
  '42421': 'october',
  '42422': 'octopus',
  '42423': 'ogle',
  '42424': 'oil',
  '42425': 'oink',
  '42426': 'ointment',
  '42431': 'okay',
  '42432': 'old',
  '42433': 'olive',
  '42434': 'olympics',
  '42435': 'omega',
  '42436': 'omen',
  '42441': 'ominous',
  '42442': 'omission',
  '42443': 'omit',
  '42444': 'omnivore',
  '42445': 'onboard',
  '42446': 'oncoming',
  '42451': 'ongoing',
  '42452': 'onion',
  '42453': 'online',
  '42454': 'onlooker',
  '42455': 'only',
  '42456': 'onscreen',
  '42461': 'onset',
  '42462': 'onshore',
  '42463': 'onslaught',
  '42464': 'onstage',
  '42465': 'onto',
  '42466': 'onward',
  '42511': 'onyx',
  '42512': 'oops',
  '42513': 'ooze',
  '42514': 'oozy',
  '42515': 'opacity',
  '42516': 'opal',
  '42521': 'open',
  '42522': 'operable',
  '42523': 'operate',
  '42524': 'operating',
  '42525': 'operation',
  '42526': 'operative',
  '42531': 'operator',
  '42532': 'opium',
  '42533': 'opossum',
  '42534': 'opponent',
  '42535': 'oppose',
  '42536': 'opposing',
  '42541': 'opposite',
  '42542': 'oppressed',
  '42543': 'oppressor',
  '42544': 'opt',
  '42545': 'opulently',
  '42546': 'osmosis',
  '42551': 'other',
  '42552': 'otter',
  '42553': 'ouch',
  '42554': 'ought',
  '42555': 'ounce',
  '42556': 'outage',
  '42561': 'outback',
  '42562': 'outbid',
  '42563': 'outboard',
  '42564': 'outbound',
  '42565': 'outbreak',
  '42566': 'outburst',
  '42611': 'outcast',
  '42612': 'outclass',
  '42613': 'outcome',
  '42614': 'outdated',
  '42615': 'outdoors',
  '42616': 'outer',
  '42621': 'outfield',
  '42622': 'outfit',
  '42623': 'outflank',
  '42624': 'outgoing',
  '42625': 'outgrow',
  '42626': 'outhouse',
  '42631': 'outing',
  '42632': 'outlast',
  '42633': 'outlet',
  '42634': 'outline',
  '42635': 'outlook',
  '42636': 'outlying',
  '42641': 'outmatch',
  '42642': 'outmost',
  '42643': 'outnumber',
  '42644': 'outplayed',
  '42645': 'outpost',
  '42646': 'outpour',
  '42651': 'output',
  '42652': 'outrage',
  '42653': 'outrank',
  '42654': 'outreach',
  '42655': 'outright',
  '42656': 'outscore',
  '42661': 'outsell',
  '42662': 'outshine',
  '42663': 'outshoot',
  '42664': 'outsider',
  '42665': 'outskirts',
  '42666': 'outsmart',
  '43111': 'outsource',
  '43112': 'outspoken',
  '43113': 'outtakes',
  '43114': 'outthink',
  '43115': 'outward',
  '43116': 'outweigh',
  '43121': 'outwit',
  '43122': 'oval',
  '43123': 'ovary',
  '43124': 'oven',
  '43125': 'overact',
  '43126': 'overall',
  '43131': 'overarch',
  '43132': 'overbid',
  '43133': 'overbill',
  '43134': 'overbite',
  '43135': 'overblown',
  '43136': 'overboard',
  '43141': 'overbook',
  '43142': 'overbuilt',
  '43143': 'overcast',
  '43144': 'overcoat',
  '43145': 'overcome',
  '43146': 'overcook',
  '43151': 'overcrowd',
  '43152': 'overdraft',
  '43153': 'overdrawn',
  '43154': 'overdress',
  '43155': 'overdrive',
  '43156': 'overdue',
  '43161': 'overeager',
  '43162': 'overeater',
  '43163': 'overexert',
  '43164': 'overfed',
  '43165': 'overfeed',
  '43166': 'overfill',
  '43211': 'overflow',
  '43212': 'overfull',
  '43213': 'overgrown',
  '43214': 'overhand',
  '43215': 'overhang',
  '43216': 'overhaul',
  '43221': 'overhead',
  '43222': 'overhear',
  '43223': 'overheat',
  '43224': 'overhung',
  '43225': 'overjoyed',
  '43226': 'overkill',
  '43231': 'overlabor',
  '43232': 'overlaid',
  '43233': 'overlap',
  '43234': 'overlay',
  '43235': 'overload',
  '43236': 'overlook',
  '43241': 'overlord',
  '43242': 'overlying',
  '43243': 'overnight',
  '43244': 'overpass',
  '43245': 'overpay',
  '43246': 'overplant',
  '43251': 'overplay',
  '43252': 'overpower',
  '43253': 'overprice',
  '43254': 'overrate',
  '43255': 'overreach',
  '43256': 'overreact',
  '43261': 'override',
  '43262': 'overripe',
  '43263': 'overrule',
  '43264': 'overrun',
  '43265': 'overshoot',
  '43266': 'overshot',
  '43311': 'oversight',
  '43312': 'oversized',
  '43313': 'oversleep',
  '43314': 'oversold',
  '43315': 'overspend',
  '43316': 'overstate',
  '43321': 'overstay',
  '43322': 'overstep',
  '43323': 'overstock',
  '43324': 'overstuff',
  '43325': 'oversweet',
  '43326': 'overtake',
  '43331': 'overthrow',
  '43332': 'overtime',
  '43333': 'overtly',
  '43334': 'overtone',
  '43335': 'overture',
  '43336': 'overturn',
  '43341': 'overuse',
  '43342': 'overvalue',
  '43343': 'overview',
  '43344': 'overwrite',
  '43345': 'owl',
  '43346': 'oxford',
  '43351': 'oxidant',
  '43352': 'oxidation',
  '43353': 'oxidize',
  '43354': 'oxidizing',
  '43355': 'oxygen',
  '43356': 'oxymoron',
  '43361': 'oyster',
  '43362': 'ozone',
  '43363': 'paced',
  '43364': 'pacemaker',
  '43365': 'pacific',
  '43366': 'pacifier',
  '43411': 'pacifism',
  '43412': 'pacifist',
  '43413': 'pacify',
  '43414': 'padded',
  '43415': 'padding',
  '43416': 'paddle',
  '43421': 'paddling',
  '43422': 'padlock',
  '43423': 'pagan',
  '43424': 'pager',
  '43425': 'paging',
  '43426': 'pajamas',
  '43431': 'palace',
  '43432': 'palatable',
  '43433': 'palm',
  '43434': 'palpable',
  '43435': 'palpitate',
  '43436': 'paltry',
  '43441': 'pampered',
  '43442': 'pamperer',
  '43443': 'pampers',
  '43444': 'pamphlet',
  '43445': 'panama',
  '43446': 'pancake',
  '43451': 'pancreas',
  '43452': 'panda',
  '43453': 'pandemic',
  '43454': 'pang',
  '43455': 'panhandle',
  '43456': 'panic',
  '43461': 'panning',
  '43462': 'panorama',
  '43463': 'panoramic',
  '43464': 'panther',
  '43465': 'pantomime',
  '43466': 'pantry',
  '43511': 'pants',
  '43512': 'pantyhose',
  '43513': 'paparazzi',
  '43514': 'papaya',
  '43515': 'paper',
  '43516': 'paprika',
  '43521': 'papyrus',
  '43522': 'parabola',
  '43523': 'parachute',
  '43524': 'parade',
  '43525': 'paradox',
  '43526': 'paragraph',
  '43531': 'parakeet',
  '43532': 'paralegal',
  '43533': 'paralyses',
  '43534': 'paralysis',
  '43535': 'paralyze',
  '43536': 'paramedic',
  '43541': 'parameter',
  '43542': 'paramount',
  '43543': 'parasail',
  '43544': 'parasite',
  '43545': 'parasitic',
  '43546': 'parcel',
  '43551': 'parched',
  '43552': 'parchment',
  '43553': 'pardon',
  '43554': 'parish',
  '43555': 'parka',
  '43556': 'parking',
  '43561': 'parkway',
  '43562': 'parlor',
  '43563': 'parmesan',
  '43564': 'parole',
  '43565': 'parrot',
  '43566': 'parsley',
  '43611': 'parsnip',
  '43612': 'partake',
  '43613': 'parted',
  '43614': 'parting',
  '43615': 'partition',
  '43616': 'partly',
  '43621': 'partner',
  '43622': 'partridge',
  '43623': 'party',
  '43624': 'passable',
  '43625': 'passably',
  '43626': 'passage',
  '43631': 'passcode',
  '43632': 'passenger',
  '43633': 'passerby',
  '43634': 'passing',
  '43635': 'passion',
  '43636': 'passive',
  '43641': 'passivism',
  '43642': 'passover',
  '43643': 'passport',
  '43644': 'password',
  '43645': 'pasta',
  '43646': 'pasted',
  '43651': 'pastel',
  '43652': 'pastime',
  '43653': 'pastor',
  '43654': 'pastrami',
  '43655': 'pasture',
  '43656': 'pasty',
  '43661': 'patchwork',
  '43662': 'patchy',
  '43663': 'paternal',
  '43664': 'paternity',
  '43665': 'path',
  '43666': 'patience',
  '44111': 'patient',
  '44112': 'patio',
  '44113': 'patriarch',
  '44114': 'patriot',
  '44115': 'patrol',
  '44116': 'patronage',
  '44121': 'patronize',
  '44122': 'pauper',
  '44123': 'pavement',
  '44124': 'paver',
  '44125': 'pavestone',
  '44126': 'pavilion',
  '44131': 'paving',
  '44132': 'pawing',
  '44133': 'payable',
  '44134': 'payback',
  '44135': 'paycheck',
  '44136': 'payday',
  '44141': 'payee',
  '44142': 'payer',
  '44143': 'paying',
  '44144': 'payment',
  '44145': 'payphone',
  '44146': 'payroll',
  '44151': 'pebble',
  '44152': 'pebbly',
  '44153': 'pecan',
  '44154': 'pectin',
  '44155': 'peculiar',
  '44156': 'peddling',
  '44161': 'pediatric',
  '44162': 'pedicure',
  '44163': 'pedigree',
  '44164': 'pedometer',
  '44165': 'pegboard',
  '44166': 'pelican',
  '44211': 'pellet',
  '44212': 'pelt',
  '44213': 'pelvis',
  '44214': 'penalize',
  '44215': 'penalty',
  '44216': 'pencil',
  '44221': 'pendant',
  '44222': 'pending',
  '44223': 'penholder',
  '44224': 'penknife',
  '44225': 'pennant',
  '44226': 'penniless',
  '44231': 'penny',
  '44232': 'penpal',
  '44233': 'pension',
  '44234': 'pentagon',
  '44235': 'pentagram',
  '44236': 'pep',
  '44241': 'perceive',
  '44242': 'percent',
  '44243': 'perch',
  '44244': 'percolate',
  '44245': 'perennial',
  '44246': 'perfected',
  '44251': 'perfectly',
  '44252': 'perfume',
  '44253': 'periscope',
  '44254': 'perish',
  '44255': 'perjurer',
  '44256': 'perjury',
  '44261': 'perkiness',
  '44262': 'perky',
  '44263': 'perm',
  '44264': 'peroxide',
  '44265': 'perpetual',
  '44266': 'perplexed',
  '44311': 'persecute',
  '44312': 'persevere',
  '44313': 'persuaded',
  '44314': 'persuader',
  '44315': 'pesky',
  '44316': 'peso',
  '44321': 'pessimism',
  '44322': 'pessimist',
  '44323': 'pester',
  '44324': 'pesticide',
  '44325': 'petal',
  '44326': 'petite',
  '44331': 'petition',
  '44332': 'petri',
  '44333': 'petroleum',
  '44334': 'petted',
  '44335': 'petticoat',
  '44336': 'pettiness',
  '44341': 'petty',
  '44342': 'petunia',
  '44343': 'phantom',
  '44344': 'phobia',
  '44345': 'phoenix',
  '44346': 'phonebook',
  '44351': 'phoney',
  '44352': 'phonics',
  '44353': 'phoniness',
  '44354': 'phony',
  '44355': 'phosphate',
  '44356': 'photo',
  '44361': 'phrase',
  '44362': 'phrasing',
  '44363': 'placard',
  '44364': 'placate',
  '44365': 'placidly',
  '44366': 'plank',
  '44411': 'planner',
  '44412': 'plant',
  '44413': 'plasma',
  '44414': 'plaster',
  '44415': 'plastic',
  '44416': 'plated',
  '44421': 'platform',
  '44422': 'plating',
  '44423': 'platinum',
  '44424': 'platonic',
  '44425': 'platter',
  '44426': 'platypus',
  '44431': 'plausible',
  '44432': 'plausibly',
  '44433': 'playable',
  '44434': 'playback',
  '44435': 'player',
  '44436': 'playful',
  '44441': 'playgroup',
  '44442': 'playhouse',
  '44443': 'playing',
  '44444': 'playlist',
  '44445': 'playmaker',
  '44446': 'playmate',
  '44451': 'playoff',
  '44452': 'playpen',
  '44453': 'playroom',
  '44454': 'playset',
  '44455': 'plaything',
  '44456': 'playtime',
  '44461': 'plaza',
  '44462': 'pleading',
  '44463': 'pleat',
  '44464': 'pledge',
  '44465': 'plentiful',
  '44466': 'plenty',
  '44511': 'plethora',
  '44512': 'plexiglas',
  '44513': 'pliable',
  '44514': 'plod',
  '44515': 'plop',
  '44516': 'plot',
  '44521': 'plow',
  '44522': 'ploy',
  '44523': 'pluck',
  '44524': 'plug',
  '44525': 'plunder',
  '44526': 'plunging',
  '44531': 'plural',
  '44532': 'plus',
  '44533': 'plutonium',
  '44534': 'plywood',
  '44535': 'poach',
  '44536': 'pod',
  '44541': 'poem',
  '44542': 'poet',
  '44543': 'pogo',
  '44544': 'pointed',
  '44545': 'pointer',
  '44546': 'pointing',
  '44551': 'pointless',
  '44552': 'pointy',
  '44553': 'poise',
  '44554': 'poison',
  '44555': 'poker',
  '44556': 'poking',
  '44561': 'polar',
  '44562': 'police',
  '44563': 'policy',
  '44564': 'polio',
  '44565': 'polish',
  '44566': 'politely',
  '44611': 'polka',
  '44612': 'polo',
  '44613': 'polyester',
  '44614': 'polygon',
  '44615': 'polygraph',
  '44616': 'polymer',
  '44621': 'poncho',
  '44622': 'pond',
  '44623': 'pony',
  '44624': 'popcorn',
  '44625': 'pope',
  '44626': 'poplar',
  '44631': 'popper',
  '44632': 'poppy',
  '44633': 'popsicle',
  '44634': 'populace',
  '44635': 'popular',
  '44636': 'populate',
  '44641': 'porcupine',
  '44642': 'pork',
  '44643': 'porous',
  '44644': 'porridge',
  '44645': 'portable',
  '44646': 'portal',
  '44651': 'portfolio',
  '44652': 'porthole',
  '44653': 'portion',
  '44654': 'portly',
  '44655': 'portside',
  '44656': 'poser',
  '44661': 'posh',
  '44662': 'posing',
  '44663': 'possible',
  '44664': 'possibly',
  '44665': 'possum',
  '44666': 'postage',
  '45111': 'postal',
  '45112': 'postbox',
  '45113': 'postcard',
  '45114': 'posted',
  '45115': 'poster',
  '45116': 'posting',
  '45121': 'postnasal',
  '45122': 'posture',
  '45123': 'postwar',
  '45124': 'pouch',
  '45125': 'pounce',
  '45126': 'pouncing',
  '45131': 'pound',
  '45132': 'pouring',
  '45133': 'pout',
  '45134': 'powdered',
  '45135': 'powdering',
  '45136': 'powdery',
  '45141': 'power',
  '45142': 'powwow',
  '45143': 'pox',
  '45144': 'praising',
  '45145': 'prance',
  '45146': 'prancing',
  '45151': 'pranker',
  '45152': 'prankish',
  '45153': 'prankster',
  '45154': 'prayer',
  '45155': 'praying',
  '45156': 'preacher',
  '45161': 'preaching',
  '45162': 'preachy',
  '45163': 'preamble',
  '45164': 'precinct',
  '45165': 'precise',
  '45166': 'precision',
  '45211': 'precook',
  '45212': 'precut',
  '45213': 'predator',
  '45214': 'predefine',
  '45215': 'predict',
  '45216': 'preface',
  '45221': 'prefix',
  '45222': 'preflight',
  '45223': 'preformed',
  '45224': 'pregame',
  '45225': 'pregnancy',
  '45226': 'pregnant',
  '45231': 'preheated',
  '45232': 'prelaunch',
  '45233': 'prelaw',
  '45234': 'prelude',
  '45235': 'premiere',
  '45236': 'premises',
  '45241': 'premium',
  '45242': 'prenatal',
  '45243': 'preoccupy',
  '45244': 'preorder',
  '45245': 'prepaid',
  '45246': 'prepay',
  '45251': 'preplan',
  '45252': 'preppy',
  '45253': 'preschool',
  '45254': 'prescribe',
  '45255': 'preseason',
  '45256': 'preset',
  '45261': 'preshow',
  '45262': 'president',
  '45263': 'presoak',
  '45264': 'press',
  '45265': 'presume',
  '45266': 'presuming',
  '45311': 'preteen',
  '45312': 'pretended',
  '45313': 'pretender',
  '45314': 'pretense',
  '45315': 'pretext',
  '45316': 'pretty',
  '45321': 'pretzel',
  '45322': 'prevail',
  '45323': 'prevalent',
  '45324': 'prevent',
  '45325': 'preview',
  '45326': 'previous',
  '45331': 'prewar',
  '45332': 'prewashed',
  '45333': 'prideful',
  '45334': 'pried',
  '45335': 'primal',
  '45336': 'primarily',
  '45341': 'primary',
  '45342': 'primate',
  '45343': 'primer',
  '45344': 'primp',
  '45345': 'princess',
  '45346': 'print',
  '45351': 'prior',
  '45352': 'prism',
  '45353': 'prison',
  '45354': 'prissy',
  '45355': 'pristine',
  '45356': 'privacy',
  '45361': 'private',
  '45362': 'privatize',
  '45363': 'prize',
  '45364': 'proactive',
  '45365': 'probable',
  '45366': 'probably',
  '45411': 'probation',
  '45412': 'probe',
  '45413': 'probing',
  '45414': 'probiotic',
  '45415': 'problem',
  '45416': 'procedure',
  '45421': 'process',
  '45422': 'proclaim',
  '45423': 'procreate',
  '45424': 'procurer',
  '45425': 'prodigal',
  '45426': 'prodigy',
  '45431': 'produce',
  '45432': 'product',
  '45433': 'profane',
  '45434': 'profanity',
  '45435': 'professed',
  '45436': 'professor',
  '45441': 'profile',
  '45442': 'profound',
  '45443': 'profusely',
  '45444': 'progeny',
  '45445': 'prognosis',
  '45446': 'program',
  '45451': 'progress',
  '45452': 'projector',
  '45453': 'prologue',
  '45454': 'prolonged',
  '45455': 'promenade',
  '45456': 'prominent',
  '45461': 'promoter',
  '45462': 'promotion',
  '45463': 'prompter',
  '45464': 'promptly',
  '45465': 'prone',
  '45466': 'prong',
  '45511': 'pronounce',
  '45512': 'pronto',
  '45513': 'proofing',
  '45514': 'proofread',
  '45515': 'proofs',
  '45516': 'propeller',
  '45521': 'properly',
  '45522': 'property',
  '45523': 'proponent',
  '45524': 'proposal',
  '45525': 'propose',
  '45526': 'props',
  '45531': 'prorate',
  '45532': 'protector',
  '45533': 'protegee',
  '45534': 'proton',
  '45535': 'prototype',
  '45536': 'protozoan',
  '45541': 'protract',
  '45542': 'protrude',
  '45543': 'proud',
  '45544': 'provable',
  '45545': 'proved',
  '45546': 'proven',
  '45551': 'provided',
  '45552': 'provider',
  '45553': 'providing',
  '45554': 'province',
  '45555': 'proving',
  '45556': 'provoke',
  '45561': 'provoking',
  '45562': 'provolone',
  '45563': 'prowess',
  '45564': 'prowler',
  '45565': 'prowling',
  '45566': 'proximity',
  '45611': 'proxy',
  '45612': 'prozac',
  '45613': 'prude',
  '45614': 'prudishly',
  '45615': 'prune',
  '45616': 'pruning',
  '45621': 'pry',
  '45622': 'psychic',
  '45623': 'public',
  '45624': 'publisher',
  '45625': 'pucker',
  '45626': 'pueblo',
  '45631': 'pug',
  '45632': 'pull',
  '45633': 'pulmonary',
  '45634': 'pulp',
  '45635': 'pulsate',
  '45636': 'pulse',
  '45641': 'pulverize',
  '45642': 'puma',
  '45643': 'pumice',
  '45644': 'pummel',
  '45645': 'punch',
  '45646': 'punctual',
  '45651': 'punctuate',
  '45652': 'punctured',
  '45653': 'pungent',
  '45654': 'punisher',
  '45655': 'punk',
  '45656': 'pupil',
  '45661': 'puppet',
  '45662': 'puppy',
  '45663': 'purchase',
  '45664': 'pureblood',
  '45665': 'purebred',
  '45666': 'purely',
  '46111': 'pureness',
  '46112': 'purgatory',
  '46113': 'purge',
  '46114': 'purging',
  '46115': 'purifier',
  '46116': 'purify',
  '46121': 'purist',
  '46122': 'puritan',
  '46123': 'purity',
  '46124': 'purple',
  '46125': 'purplish',
  '46126': 'purposely',
  '46131': 'purr',
  '46132': 'purse',
  '46133': 'pursuable',
  '46134': 'pursuant',
  '46135': 'pursuit',
  '46136': 'purveyor',
  '46141': 'pushcart',
  '46142': 'pushchair',
  '46143': 'pusher',
  '46144': 'pushiness',
  '46145': 'pushing',
  '46146': 'pushover',
  '46151': 'pushpin',
  '46152': 'pushup',
  '46153': 'pushy',
  '46154': 'putdown',
  '46155': 'putt',
  '46156': 'puzzle',
  '46161': 'puzzling',
  '46162': 'pyramid',
  '46163': 'pyromania',
  '46164': 'python',
  '46165': 'quack',
  '46166': 'quadrant',
  '46211': 'quail',
  '46212': 'quaintly',
  '46213': 'quake',
  '46214': 'quaking',
  '46215': 'qualified',
  '46216': 'qualifier',
  '46221': 'qualify',
  '46222': 'quality',
  '46223': 'qualm',
  '46224': 'quantum',
  '46225': 'quarrel',
  '46226': 'quarry',
  '46231': 'quartered',
  '46232': 'quarterly',
  '46233': 'quarters',
  '46234': 'quartet',
  '46235': 'quench',
  '46236': 'query',
  '46241': 'quicken',
  '46242': 'quickly',
  '46243': 'quickness',
  '46244': 'quicksand',
  '46245': 'quickstep',
  '46246': 'quiet',
  '46251': 'quill',
  '46252': 'quilt',
  '46253': 'quintet',
  '46254': 'quintuple',
  '46255': 'quirk',
  '46256': 'quit',
  '46261': 'quiver',
  '46262': 'quizzical',
  '46263': 'quotable',
  '46264': 'quotation',
  '46265': 'quote',
  '46266': 'rabid',
  '46311': 'race',
  '46312': 'racing',
  '46313': 'racism',
  '46314': 'rack',
  '46315': 'racoon',
  '46316': 'radar',
  '46321': 'radial',
  '46322': 'radiance',
  '46323': 'radiantly',
  '46324': 'radiated',
  '46325': 'radiation',
  '46326': 'radiator',
  '46331': 'radio',
  '46332': 'radish',
  '46333': 'raffle',
  '46334': 'raft',
  '46335': 'rage',
  '46336': 'ragged',
  '46341': 'raging',
  '46342': 'ragweed',
  '46343': 'raider',
  '46344': 'railcar',
  '46345': 'railing',
  '46346': 'railroad',
  '46351': 'railway',
  '46352': 'raisin',
  '46353': 'rake',
  '46354': 'raking',
  '46355': 'rally',
  '46356': 'ramble',
  '46361': 'rambling',
  '46362': 'ramp',
  '46363': 'ramrod',
  '46364': 'ranch',
  '46365': 'rancidity',
  '46366': 'random',
  '46411': 'ranged',
  '46412': 'ranger',
  '46413': 'ranging',
  '46414': 'ranked',
  '46415': 'ranking',
  '46416': 'ransack',
  '46421': 'ranting',
  '46422': 'rants',
  '46423': 'rare',
  '46424': 'rarity',
  '46425': 'rascal',
  '46426': 'rash',
  '46431': 'rasping',
  '46432': 'ravage',
  '46433': 'raven',
  '46434': 'ravine',
  '46435': 'raving',
  '46436': 'ravioli',
  '46441': 'ravishing',
  '46442': 'reabsorb',
  '46443': 'reach',
  '46444': 'reacquire',
  '46445': 'reaction',
  '46446': 'reactive',
  '46451': 'reactor',
  '46452': 'reaffirm',
  '46453': 'ream',
  '46454': 'reanalyze',
  '46455': 'reappear',
  '46456': 'reapply',
  '46461': 'reappoint',
  '46462': 'reapprove',
  '46463': 'rearrange',
  '46464': 'rearview',
  '46465': 'reason',
  '46466': 'reassign',
  '46511': 'reassure',
  '46512': 'reattach',
  '46513': 'reawake',
  '46514': 'rebalance',
  '46515': 'rebate',
  '46516': 'rebel',
  '46521': 'rebirth',
  '46522': 'reboot',
  '46523': 'reborn',
  '46524': 'rebound',
  '46525': 'rebuff',
  '46526': 'rebuild',
  '46531': 'rebuilt',
  '46532': 'reburial',
  '46533': 'rebuttal',
  '46534': 'recall',
  '46535': 'recant',
  '46536': 'recapture',
  '46541': 'recast',
  '46542': 'recede',
  '46543': 'recent',
  '46544': 'recess',
  '46545': 'recharger',
  '46546': 'recipient',
  '46551': 'recital',
  '46552': 'recite',
  '46553': 'reckless',
  '46554': 'reclaim',
  '46555': 'recliner',
  '46556': 'reclining',
  '46561': 'recluse',
  '46562': 'reclusive',
  '46563': 'recognize',
  '46564': 'recoil',
  '46565': 'recollect',
  '46566': 'recolor',
  '46611': 'reconcile',
  '46612': 'reconfirm',
  '46613': 'reconvene',
  '46614': 'recopy',
  '46615': 'record',
  '46616': 'recount',
  '46621': 'recoup',
  '46622': 'recovery',
  '46623': 'recreate',
  '46624': 'rectal',
  '46625': 'rectangle',
  '46626': 'rectified',
  '46631': 'rectify',
  '46632': 'recycled',
  '46633': 'recycler',
  '46634': 'recycling',
  '46635': 'reemerge',
  '46636': 'reenact',
  '46641': 'reenter',
  '46642': 'reentry',
  '46643': 'reexamine',
  '46644': 'referable',
  '46645': 'referee',
  '46646': 'reference',
  '46651': 'refill',
  '46652': 'refinance',
  '46653': 'refined',
  '46654': 'refinery',
  '46655': 'refining',
  '46656': 'refinish',
  '46661': 'reflected',
  '46662': 'reflector',
  '46663': 'reflex',
  '46664': 'reflux',
  '46665': 'refocus',
  '46666': 'refold',
  '51111': 'reforest',
  '51112': 'reformat',
  '51113': 'reformed',
  '51114': 'reformer',
  '51115': 'reformist',
  '51116': 'refract',
  '51121': 'refrain',
  '51122': 'refreeze',
  '51123': 'refresh',
  '51124': 'refried',
  '51125': 'refueling',
  '51126': 'refund',
  '51131': 'refurbish',
  '51132': 'refurnish',
  '51133': 'refusal',
  '51134': 'refuse',
  '51135': 'refusing',
  '51136': 'refutable',
  '51141': 'refute',
  '51142': 'regain',
  '51143': 'regalia',
  '51144': 'regally',
  '51145': 'reggae',
  '51146': 'regime',
  '51151': 'region',
  '51152': 'register',
  '51153': 'registrar',
  '51154': 'registry',
  '51155': 'regress',
  '51156': 'regretful',
  '51161': 'regroup',
  '51162': 'regular',
  '51163': 'regulate',
  '51164': 'regulator',
  '51165': 'rehab',
  '51166': 'reheat',
  '51211': 'rehire',
  '51212': 'rehydrate',
  '51213': 'reimburse',
  '51214': 'reissue',
  '51215': 'reiterate',
  '51216': 'rejoice',
  '51221': 'rejoicing',
  '51222': 'rejoin',
  '51223': 'rekindle',
  '51224': 'relapse',
  '51225': 'relapsing',
  '51226': 'relatable',
  '51231': 'related',
  '51232': 'relation',
  '51233': 'relative',
  '51234': 'relax',
  '51235': 'relay',
  '51236': 'relearn',
  '51241': 'release',
  '51242': 'relenting',
  '51243': 'reliable',
  '51244': 'reliably',
  '51245': 'reliance',
  '51246': 'reliant',
  '51251': 'relic',
  '51252': 'relieve',
  '51253': 'relieving',
  '51254': 'relight',
  '51255': 'relish',
  '51256': 'relive',
  '51261': 'reload',
  '51262': 'relocate',
  '51263': 'relock',
  '51264': 'reluctant',
  '51265': 'rely',
  '51266': 'remake',
  '51311': 'remark',
  '51312': 'remarry',
  '51313': 'rematch',
  '51314': 'remedial',
  '51315': 'remedy',
  '51316': 'remember',
  '51321': 'reminder',
  '51322': 'remindful',
  '51323': 'remission',
  '51324': 'remix',
  '51325': 'remnant',
  '51326': 'remodeler',
  '51331': 'remold',
  '51332': 'remorse',
  '51333': 'remote',
  '51334': 'removable',
  '51335': 'removal',
  '51336': 'removed',
  '51341': 'remover',
  '51342': 'removing',
  '51343': 'rename',
  '51344': 'renderer',
  '51345': 'rendering',
  '51346': 'rendition',
  '51351': 'renegade',
  '51352': 'renewable',
  '51353': 'renewably',
  '51354': 'renewal',
  '51355': 'renewed',
  '51356': 'renounce',
  '51361': 'renovate',
  '51362': 'renovator',
  '51363': 'rentable',
  '51364': 'rental',
  '51365': 'rented',
  '51366': 'renter',
  '51411': 'reoccupy',
  '51412': 'reoccur',
  '51413': 'reopen',
  '51414': 'reorder',
  '51415': 'repackage',
  '51416': 'repacking',
  '51421': 'repaint',
  '51422': 'repair',
  '51423': 'repave',
  '51424': 'repaying',
  '51425': 'repayment',
  '51426': 'repeal',
  '51431': 'repeated',
  '51432': 'repeater',
  '51433': 'repent',
  '51434': 'rephrase',
  '51435': 'replace',
  '51436': 'replay',
  '51441': 'replica',
  '51442': 'reply',
  '51443': 'reporter',
  '51444': 'repose',
  '51445': 'repossess',
  '51446': 'repost',
  '51451': 'repressed',
  '51452': 'reprimand',
  '51453': 'reprint',
  '51454': 'reprise',
  '51455': 'reproach',
  '51456': 'reprocess',
  '51461': 'reproduce',
  '51462': 'reprogram',
  '51463': 'reps',
  '51464': 'reptile',
  '51465': 'reptilian',
  '51466': 'repugnant',
  '51511': 'repulsion',
  '51512': 'repulsive',
  '51513': 'repurpose',
  '51514': 'reputable',
  '51515': 'reputably',
  '51516': 'request',
  '51521': 'require',
  '51522': 'requisite',
  '51523': 'reroute',
  '51524': 'rerun',
  '51525': 'resale',
  '51526': 'resample',
  '51531': 'rescuer',
  '51532': 'reseal',
  '51533': 'research',
  '51534': 'reselect',
  '51535': 'reseller',
  '51536': 'resemble',
  '51541': 'resend',
  '51542': 'resent',
  '51543': 'reset',
  '51544': 'reshape',
  '51545': 'reshoot',
  '51546': 'reshuffle',
  '51551': 'residence',
  '51552': 'residency',
  '51553': 'resident',
  '51554': 'residual',
  '51555': 'residue',
  '51556': 'resigned',
  '51561': 'resilient',
  '51562': 'resistant',
  '51563': 'resisting',
  '51564': 'resize',
  '51565': 'resolute',
  '51566': 'resolved',
  '51611': 'resonant',
  '51612': 'resonate',
  '51613': 'resort',
  '51614': 'resource',
  '51615': 'respect',
  '51616': 'resubmit',
  '51621': 'result',
  '51622': 'resume',
  '51623': 'resupply',
  '51624': 'resurface',
  '51625': 'resurrect',
  '51626': 'retail',
  '51631': 'retainer',
  '51632': 'retaining',
  '51633': 'retake',
  '51634': 'retaliate',
  '51635': 'retention',
  '51636': 'rethink',
  '51641': 'retinal',
  '51642': 'retired',
  '51643': 'retiree',
  '51644': 'retiring',
  '51645': 'retold',
  '51646': 'retool',
  '51651': 'retorted',
  '51652': 'retouch',
  '51653': 'retrace',
  '51654': 'retract',
  '51655': 'retrain',
  '51656': 'retread',
  '51661': 'retreat',
  '51662': 'retrial',
  '51663': 'retrieval',
  '51664': 'retriever',
  '51665': 'retry',
  '51666': 'return',
  '52111': 'retying',
  '52112': 'retype',
  '52113': 'reunion',
  '52114': 'reunite',
  '52115': 'reusable',
  '52116': 'reuse',
  '52121': 'reveal',
  '52122': 'reveler',
  '52123': 'revenge',
  '52124': 'revenue',
  '52125': 'reverb',
  '52126': 'revered',
  '52131': 'reverence',
  '52132': 'reverend',
  '52133': 'reversal',
  '52134': 'reverse',
  '52135': 'reversing',
  '52136': 'reversion',
  '52141': 'revert',
  '52142': 'revisable',
  '52143': 'revise',
  '52144': 'revision',
  '52145': 'revisit',
  '52146': 'revivable',
  '52151': 'revival',
  '52152': 'reviver',
  '52153': 'reviving',
  '52154': 'revocable',
  '52155': 'revoke',
  '52156': 'revolt',
  '52161': 'revolver',
  '52162': 'revolving',
  '52163': 'reward',
  '52164': 'rewash',
  '52165': 'rewind',
  '52166': 'rewire',
  '52211': 'reword',
  '52212': 'rework',
  '52213': 'rewrap',
  '52214': 'rewrite',
  '52215': 'rhyme',
  '52216': 'ribbon',
  '52221': 'ribcage',
  '52222': 'rice',
  '52223': 'riches',
  '52224': 'richly',
  '52225': 'richness',
  '52226': 'rickety',
  '52231': 'ricotta',
  '52232': 'riddance',
  '52233': 'ridden',
  '52234': 'ride',
  '52235': 'riding',
  '52236': 'rifling',
  '52241': 'rift',
  '52242': 'rigging',
  '52243': 'rigid',
  '52244': 'rigor',
  '52245': 'rimless',
  '52246': 'rimmed',
  '52251': 'rind',
  '52252': 'rink',
  '52253': 'rinse',
  '52254': 'rinsing',
  '52255': 'riot',
  '52256': 'ripcord',
  '52261': 'ripeness',
  '52262': 'ripening',
  '52263': 'ripping',
  '52264': 'ripple',
  '52265': 'rippling',
  '52266': 'riptide',
  '52311': 'rise',
  '52312': 'rising',
  '52313': 'risk',
  '52314': 'risotto',
  '52315': 'ritalin',
  '52316': 'ritzy',
  '52321': 'rival',
  '52322': 'riverbank',
  '52323': 'riverbed',
  '52324': 'riverboat',
  '52325': 'riverside',
  '52326': 'riveter',
  '52331': 'riveting',
  '52332': 'roamer',
  '52333': 'roaming',
  '52334': 'roast',
  '52335': 'robbing',
  '52336': 'robe',
  '52341': 'robin',
  '52342': 'robotics',
  '52343': 'robust',
  '52344': 'rockband',
  '52345': 'rocker',
  '52346': 'rocket',
  '52351': 'rockfish',
  '52352': 'rockiness',
  '52353': 'rocking',
  '52354': 'rocklike',
  '52355': 'rockslide',
  '52356': 'rockstar',
  '52361': 'rocky',
  '52362': 'rogue',
  '52363': 'roman',
  '52364': 'romp',
  '52365': 'rope',
  '52366': 'roping',
  '52411': 'roster',
  '52412': 'rosy',
  '52413': 'rotten',
  '52414': 'rotting',
  '52415': 'rotunda',
  '52416': 'roulette',
  '52421': 'rounding',
  '52422': 'roundish',
  '52423': 'roundness',
  '52424': 'roundup',
  '52425': 'roundworm',
  '52426': 'routine',
  '52431': 'routing',
  '52432': 'rover',
  '52433': 'roving',
  '52434': 'royal',
  '52435': 'rubbed',
  '52436': 'rubber',
  '52441': 'rubbing',
  '52442': 'rubble',
  '52443': 'rubdown',
  '52444': 'ruby',
  '52445': 'ruckus',
  '52446': 'rudder',
  '52451': 'rug',
  '52452': 'ruined',
  '52453': 'rule',
  '52454': 'rumble',
  '52455': 'rumbling',
  '52456': 'rummage',
  '52461': 'rumor',
  '52462': 'runaround',
  '52463': 'rundown',
  '52464': 'runner',
  '52465': 'running',
  '52466': 'runny',
  '52511': 'runt',
  '52512': 'runway',
  '52513': 'rupture',
  '52514': 'rural',
  '52515': 'ruse',
  '52516': 'rush',
  '52521': 'rust',
  '52522': 'rut',
  '52523': 'sabbath',
  '52524': 'sabotage',
  '52525': 'sacrament',
  '52526': 'sacred',
  '52531': 'sacrifice',
  '52532': 'sadden',
  '52533': 'saddlebag',
  '52534': 'saddled',
  '52535': 'saddling',
  '52536': 'sadly',
  '52541': 'sadness',
  '52542': 'safari',
  '52543': 'safeguard',
  '52544': 'safehouse',
  '52545': 'safely',
  '52546': 'safeness',
  '52551': 'saffron',
  '52552': 'saga',
  '52553': 'sage',
  '52554': 'sagging',
  '52555': 'saggy',
  '52556': 'said',
  '52561': 'saint',
  '52562': 'sake',
  '52563': 'salad',
  '52564': 'salami',
  '52565': 'salaried',
  '52566': 'salary',
  '52611': 'saline',
  '52612': 'salon',
  '52613': 'saloon',
  '52614': 'salsa',
  '52615': 'salt',
  '52616': 'salutary',
  '52621': 'salute',
  '52622': 'salvage',
  '52623': 'salvaging',
  '52624': 'salvation',
  '52625': 'same',
  '52626': 'sample',
  '52631': 'sampling',
  '52632': 'sanction',
  '52633': 'sanctity',
  '52634': 'sanctuary',
  '52635': 'sandal',
  '52636': 'sandbag',
  '52641': 'sandbank',
  '52642': 'sandbar',
  '52643': 'sandblast',
  '52644': 'sandbox',
  '52645': 'sanded',
  '52646': 'sandfish',
  '52651': 'sanding',
  '52652': 'sandlot',
  '52653': 'sandpaper',
  '52654': 'sandpit',
  '52655': 'sandstone',
  '52656': 'sandstorm',
  '52661': 'sandworm',
  '52662': 'sandy',
  '52663': 'sanitary',
  '52664': 'sanitizer',
  '52665': 'sank',
  '52666': 'santa',
  '53111': 'sapling',
  '53112': 'sappiness',
  '53113': 'sappy',
  '53114': 'sarcasm',
  '53115': 'sarcastic',
  '53116': 'sardine',
  '53121': 'sash',
  '53122': 'sasquatch',
  '53123': 'sassy',
  '53124': 'satchel',
  '53125': 'satiable',
  '53126': 'satin',
  '53131': 'satirical',
  '53132': 'satisfied',
  '53133': 'satisfy',
  '53134': 'saturate',
  '53135': 'saturday',
  '53136': 'sauciness',
  '53141': 'saucy',
  '53142': 'sauna',
  '53143': 'savage',
  '53144': 'savanna',
  '53145': 'saved',
  '53146': 'savings',
  '53151': 'savior',
  '53152': 'savor',
  '53153': 'saxophone',
  '53154': 'say',
  '53155': 'scabbed',
  '53156': 'scabby',
  '53161': 'scalded',
  '53162': 'scalding',
  '53163': 'scale',
  '53164': 'scaling',
  '53165': 'scallion',
  '53166': 'scallop',
  '53211': 'scalping',
  '53212': 'scam',
  '53213': 'scandal',
  '53214': 'scanner',
  '53215': 'scanning',
  '53216': 'scant',
  '53221': 'scapegoat',
  '53222': 'scarce',
  '53223': 'scarcity',
  '53224': 'scarecrow',
  '53225': 'scared',
  '53226': 'scarf',
  '53231': 'scarily',
  '53232': 'scariness',
  '53233': 'scarring',
  '53234': 'scary',
  '53235': 'scavenger',
  '53236': 'scenic',
  '53241': 'schedule',
  '53242': 'schematic',
  '53243': 'scheme',
  '53244': 'scheming',
  '53245': 'schilling',
  '53246': 'schnapps',
  '53251': 'scholar',
  '53252': 'science',
  '53253': 'scientist',
  '53254': 'scion',
  '53255': 'scoff',
  '53256': 'scolding',
  '53261': 'scone',
  '53262': 'scoop',
  '53263': 'scooter',
  '53264': 'scope',
  '53265': 'scorch',
  '53266': 'scorebook',
  '53311': 'scorecard',
  '53312': 'scored',
  '53313': 'scoreless',
  '53314': 'scorer',
  '53315': 'scoring',
  '53316': 'scorn',
  '53321': 'scorpion',
  '53322': 'scotch',
  '53323': 'scoundrel',
  '53324': 'scoured',
  '53325': 'scouring',
  '53326': 'scouting',
  '53331': 'scouts',
  '53332': 'scowling',
  '53333': 'scrabble',
  '53334': 'scraggly',
  '53335': 'scrambled',
  '53336': 'scrambler',
  '53341': 'scrap',
  '53342': 'scratch',
  '53343': 'scrawny',
  '53344': 'screen',
  '53345': 'scribble',
  '53346': 'scribe',
  '53351': 'scribing',
  '53352': 'scrimmage',
  '53353': 'script',
  '53354': 'scroll',
  '53355': 'scrooge',
  '53356': 'scrounger',
  '53361': 'scrubbed',
  '53362': 'scrubber',
  '53363': 'scruffy',
  '53364': 'scrunch',
  '53365': 'scrutiny',
  '53366': 'scuba',
  '53411': 'scuff',
  '53412': 'sculptor',
  '53413': 'sculpture',
  '53414': 'scurvy',
  '53415': 'scuttle',
  '53416': 'secluded',
  '53421': 'secluding',
  '53422': 'seclusion',
  '53423': 'second',
  '53424': 'secrecy',
  '53425': 'secret',
  '53426': 'sectional',
  '53431': 'sector',
  '53432': 'secular',
  '53433': 'securely',
  '53434': 'security',
  '53435': 'sedan',
  '53436': 'sedate',
  '53441': 'sedation',
  '53442': 'sedative',
  '53443': 'sediment',
  '53444': 'seduce',
  '53445': 'seducing',
  '53446': 'segment',
  '53451': 'seismic',
  '53452': 'seizing',
  '53453': 'seldom',
  '53454': 'selected',
  '53455': 'selection',
  '53456': 'selective',
  '53461': 'selector',
  '53462': 'self',
  '53463': 'seltzer',
  '53464': 'semantic',
  '53465': 'semester',
  '53466': 'semicolon',
  '53511': 'semifinal',
  '53512': 'seminar',
  '53513': 'semisoft',
  '53514': 'semisweet',
  '53515': 'senate',
  '53516': 'senator',
  '53521': 'send',
  '53522': 'senior',
  '53523': 'senorita',
  '53524': 'sensation',
  '53525': 'sensitive',
  '53526': 'sensitize',
  '53531': 'sensually',
  '53532': 'sensuous',
  '53533': 'sepia',
  '53534': 'september',
  '53535': 'septic',
  '53536': 'septum',
  '53541': 'sequel',
  '53542': 'sequence',
  '53543': 'sequester',
  '53544': 'series',
  '53545': 'sermon',
  '53546': 'serotonin',
  '53551': 'serpent',
  '53552': 'serrated',
  '53553': 'serve',
  '53554': 'service',
  '53555': 'serving',
  '53556': 'sesame',
  '53561': 'sessions',
  '53562': 'setback',
  '53563': 'setting',
  '53564': 'settle',
  '53565': 'settling',
  '53566': 'setup',
  '53611': 'sevenfold',
  '53612': 'seventeen',
  '53613': 'seventh',
  '53614': 'seventy',
  '53615': 'severity',
  '53616': 'shabby',
  '53621': 'shack',
  '53622': 'shaded',
  '53623': 'shadily',
  '53624': 'shadiness',
  '53625': 'shading',
  '53626': 'shadow',
  '53631': 'shady',
  '53632': 'shaft',
  '53633': 'shakable',
  '53634': 'shakily',
  '53635': 'shakiness',
  '53636': 'shaking',
  '53641': 'shaky',
  '53642': 'shale',
  '53643': 'shallot',
  '53644': 'shallow',
  '53645': 'shame',
  '53646': 'shampoo',
  '53651': 'shamrock',
  '53652': 'shank',
  '53653': 'shanty',
  '53654': 'shape',
  '53655': 'shaping',
  '53656': 'share',
  '53661': 'sharpener',
  '53662': 'sharper',
  '53663': 'sharpie',
  '53664': 'sharply',
  '53665': 'sharpness',
  '53666': 'shawl',
  '54111': 'sheath',
  '54112': 'shed',
  '54113': 'sheep',
  '54114': 'sheet',
  '54115': 'shelf',
  '54116': 'shell',
  '54121': 'shelter',
  '54122': 'shelve',
  '54123': 'shelving',
  '54124': 'sherry',
  '54125': 'shield',
  '54126': 'shifter',
  '54131': 'shifting',
  '54132': 'shiftless',
  '54133': 'shifty',
  '54134': 'shimmer',
  '54135': 'shimmy',
  '54136': 'shindig',
  '54141': 'shine',
  '54142': 'shingle',
  '54143': 'shininess',
  '54144': 'shining',
  '54145': 'shiny',
  '54146': 'ship',
  '54151': 'shirt',
  '54152': 'shivering',
  '54153': 'shock',
  '54154': 'shone',
  '54155': 'shoplift',
  '54156': 'shopper',
  '54161': 'shopping',
  '54162': 'shoptalk',
  '54163': 'shore',
  '54164': 'shortage',
  '54165': 'shortcake',
  '54166': 'shortcut',
  '54211': 'shorten',
  '54212': 'shorter',
  '54213': 'shorthand',
  '54214': 'shortlist',
  '54215': 'shortly',
  '54216': 'shortness',
  '54221': 'shorts',
  '54222': 'shortwave',
  '54223': 'shorty',
  '54224': 'shout',
  '54225': 'shove',
  '54226': 'showbiz',
  '54231': 'showcase',
  '54232': 'showdown',
  '54233': 'shower',
  '54234': 'showgirl',
  '54235': 'showing',
  '54236': 'showman',
  '54241': 'shown',
  '54242': 'showoff',
  '54243': 'showpiece',
  '54244': 'showplace',
  '54245': 'showroom',
  '54246': 'showy',
  '54251': 'shrank',
  '54252': 'shrapnel',
  '54253': 'shredder',
  '54254': 'shredding',
  '54255': 'shrewdly',
  '54256': 'shriek',
  '54261': 'shrill',
  '54262': 'shrimp',
  '54263': 'shrine',
  '54264': 'shrink',
  '54265': 'shrivel',
  '54266': 'shrouded',
  '54311': 'shrubbery',
  '54312': 'shrubs',
  '54313': 'shrug',
  '54314': 'shrunk',
  '54315': 'shucking',
  '54316': 'shudder',
  '54321': 'shuffle',
  '54322': 'shuffling',
  '54323': 'shun',
  '54324': 'shush',
  '54325': 'shut',
  '54326': 'shy',
  '54331': 'siamese',
  '54332': 'siberian',
  '54333': 'sibling',
  '54334': 'siding',
  '54335': 'sierra',
  '54336': 'siesta',
  '54341': 'sift',
  '54342': 'sighing',
  '54343': 'silenced',
  '54344': 'silencer',
  '54345': 'silent',
  '54346': 'silica',
  '54351': 'silicon',
  '54352': 'silk',
  '54353': 'silliness',
  '54354': 'silly',
  '54355': 'silo',
  '54356': 'silt',
  '54361': 'silver',
  '54362': 'similarly',
  '54363': 'simile',
  '54364': 'simmering',
  '54365': 'simple',
  '54366': 'simplify',
  '54411': 'simply',
  '54412': 'sincere',
  '54413': 'sincerity',
  '54414': 'singer',
  '54415': 'singing',
  '54416': 'single',
  '54421': 'singular',
  '54422': 'sinister',
  '54423': 'sinless',
  '54424': 'sinner',
  '54425': 'sinuous',
  '54426': 'sip',
  '54431': 'siren',
  '54432': 'sister',
  '54433': 'sitcom',
  '54434': 'sitter',
  '54435': 'sitting',
  '54436': 'situated',
  '54441': 'situation',
  '54442': 'sixfold',
  '54443': 'sixteen',
  '54444': 'sixth',
  '54445': 'sixties',
  '54446': 'sixtieth',
  '54451': 'sixtyfold',
  '54452': 'sizable',
  '54453': 'sizably',
  '54454': 'size',
  '54455': 'sizing',
  '54456': 'sizzle',
  '54461': 'sizzling',
  '54462': 'skater',
  '54463': 'skating',
  '54464': 'skedaddle',
  '54465': 'skeletal',
  '54466': 'skeleton',
  '54511': 'skeptic',
  '54512': 'sketch',
  '54513': 'skewed',
  '54514': 'skewer',
  '54515': 'skid',
  '54516': 'skied',
  '54521': 'skier',
  '54522': 'skies',
  '54523': 'skiing',
  '54524': 'skilled',
  '54525': 'skillet',
  '54526': 'skillful',
  '54531': 'skimmed',
  '54532': 'skimmer',
  '54533': 'skimming',
  '54534': 'skimpily',
  '54535': 'skincare',
  '54536': 'skinhead',
  '54541': 'skinless',
  '54542': 'skinning',
  '54543': 'skinny',
  '54544': 'skintight',
  '54545': 'skipper',
  '54546': 'skipping',
  '54551': 'skirmish',
  '54552': 'skirt',
  '54553': 'skittle',
  '54554': 'skydiver',
  '54555': 'skylight',
  '54556': 'skyline',
  '54561': 'skype',
  '54562': 'skyrocket',
  '54563': 'skyward',
  '54564': 'slab',
  '54565': 'slacked',
  '54566': 'slacker',
  '54611': 'slacking',
  '54612': 'slackness',
  '54613': 'slacks',
  '54614': 'slain',
  '54615': 'slam',
  '54616': 'slander',
  '54621': 'slang',
  '54622': 'slapping',
  '54623': 'slapstick',
  '54624': 'slashed',
  '54625': 'slashing',
  '54626': 'slate',
  '54631': 'slather',
  '54632': 'slaw',
  '54633': 'sled',
  '54634': 'sleek',
  '54635': 'sleep',
  '54636': 'sleet',
  '54641': 'sleeve',
  '54642': 'slept',
  '54643': 'sliceable',
  '54644': 'sliced',
  '54645': 'slicer',
  '54646': 'slicing',
  '54651': 'slick',
  '54652': 'slider',
  '54653': 'slideshow',
  '54654': 'sliding',
  '54655': 'slighted',
  '54656': 'slighting',
  '54661': 'slightly',
  '54662': 'slimness',
  '54663': 'slimy',
  '54664': 'slinging',
  '54665': 'slingshot',
  '54666': 'slinky',
  '55111': 'slip',
  '55112': 'slit',
  '55113': 'sliver',
  '55114': 'slobbery',
  '55115': 'slogan',
  '55116': 'sloped',
  '55121': 'sloping',
  '55122': 'sloppily',
  '55123': 'sloppy',
  '55124': 'slot',
  '55125': 'slouching',
  '55126': 'slouchy',
  '55131': 'sludge',
  '55132': 'slug',
  '55133': 'slum',
  '55134': 'slurp',
  '55135': 'slush',
  '55136': 'sly',
  '55141': 'small',
  '55142': 'smartly',
  '55143': 'smartness',
  '55144': 'smasher',
  '55145': 'smashing',
  '55146': 'smashup',
  '55151': 'smell',
  '55152': 'smelting',
  '55153': 'smile',
  '55154': 'smilingly',
  '55155': 'smirk',
  '55156': 'smite',
  '55161': 'smith',
  '55162': 'smitten',
  '55163': 'smock',
  '55164': 'smog',
  '55165': 'smoked',
  '55166': 'smokeless',
  '55211': 'smokiness',
  '55212': 'smoking',
  '55213': 'smoky',
  '55214': 'smolder',
  '55215': 'smooth',
  '55216': 'smother',
  '55221': 'smudge',
  '55222': 'smudgy',
  '55223': 'smuggler',
  '55224': 'smuggling',
  '55225': 'smugly',
  '55226': 'smugness',
  '55231': 'snack',
  '55232': 'snagged',
  '55233': 'snaking',
  '55234': 'snap',
  '55235': 'snare',
  '55236': 'snarl',
  '55241': 'snazzy',
  '55242': 'sneak',
  '55243': 'sneer',
  '55244': 'sneeze',
  '55245': 'sneezing',
  '55246': 'snide',
  '55251': 'sniff',
  '55252': 'snippet',
  '55253': 'snipping',
  '55254': 'snitch',
  '55255': 'snooper',
  '55256': 'snooze',
  '55261': 'snore',
  '55262': 'snoring',
  '55263': 'snorkel',
  '55264': 'snort',
  '55265': 'snout',
  '55266': 'snowbird',
  '55311': 'snowboard',
  '55312': 'snowbound',
  '55313': 'snowcap',
  '55314': 'snowdrift',
  '55315': 'snowdrop',
  '55316': 'snowfall',
  '55321': 'snowfield',
  '55322': 'snowflake',
  '55323': 'snowiness',
  '55324': 'snowless',
  '55325': 'snowman',
  '55326': 'snowplow',
  '55331': 'snowshoe',
  '55332': 'snowstorm',
  '55333': 'snowsuit',
  '55334': 'snowy',
  '55335': 'snub',
  '55336': 'snuff',
  '55341': 'snuggle',
  '55342': 'snugly',
  '55343': 'snugness',
  '55344': 'speak',
  '55345': 'spearfish',
  '55346': 'spearhead',
  '55351': 'spearman',
  '55352': 'spearmint',
  '55353': 'species',
  '55354': 'specimen',
  '55355': 'specked',
  '55356': 'speckled',
  '55361': 'specks',
  '55362': 'spectacle',
  '55363': 'spectator',
  '55364': 'spectrum',
  '55365': 'speculate',
  '55366': 'speech',
  '55411': 'speed',
  '55412': 'spellbind',
  '55413': 'speller',
  '55414': 'spelling',
  '55415': 'spendable',
  '55416': 'spender',
  '55421': 'spending',
  '55422': 'spent',
  '55423': 'spew',
  '55424': 'sphere',
  '55425': 'spherical',
  '55426': 'sphinx',
  '55431': 'spider',
  '55432': 'spied',
  '55433': 'spiffy',
  '55434': 'spill',
  '55435': 'spilt',
  '55436': 'spinach',
  '55441': 'spinal',
  '55442': 'spindle',
  '55443': 'spinner',
  '55444': 'spinning',
  '55445': 'spinout',
  '55446': 'spinster',
  '55451': 'spiny',
  '55452': 'spiral',
  '55453': 'spirited',
  '55454': 'spiritism',
  '55455': 'spirits',
  '55456': 'spiritual',
  '55461': 'splashed',
  '55462': 'splashing',
  '55463': 'splashy',
  '55464': 'splatter',
  '55465': 'spleen',
  '55466': 'splendid',
  '55511': 'splendor',
  '55512': 'splice',
  '55513': 'splicing',
  '55514': 'splinter',
  '55515': 'splotchy',
  '55516': 'splurge',
  '55521': 'spoilage',
  '55522': 'spoiled',
  '55523': 'spoiler',
  '55524': 'spoiling',
  '55525': 'spoils',
  '55526': 'spoken',
  '55531': 'spokesman',
  '55532': 'sponge',
  '55533': 'spongy',
  '55534': 'sponsor',
  '55535': 'spoof',
  '55536': 'spookily',
  '55541': 'spooky',
  '55542': 'spool',
  '55543': 'spoon',
  '55544': 'spore',
  '55545': 'sporting',
  '55546': 'sports',
  '55551': 'sporty',
  '55552': 'spotless',
  '55553': 'spotlight',
  '55554': 'spotted',
  '55555': 'spotter',
  '55556': 'spotting',
  '55561': 'spotty',
  '55562': 'spousal',
  '55563': 'spouse',
  '55564': 'spout',
  '55565': 'sprain',
  '55566': 'sprang',
  '55611': 'sprawl',
  '55612': 'spray',
  '55613': 'spree',
  '55614': 'sprig',
  '55615': 'spring',
  '55616': 'sprinkled',
  '55621': 'sprinkler',
  '55622': 'sprint',
  '55623': 'sprite',
  '55624': 'sprout',
  '55625': 'spruce',
  '55626': 'sprung',
  '55631': 'spry',
  '55632': 'spud',
  '55633': 'spur',
  '55634': 'sputter',
  '55635': 'spyglass',
  '55636': 'squabble',
  '55641': 'squad',
  '55642': 'squall',
  '55643': 'squander',
  '55644': 'squash',
  '55645': 'squatted',
  '55646': 'squatter',
  '55651': 'squatting',
  '55652': 'squeak',
  '55653': 'squealer',
  '55654': 'squealing',
  '55655': 'squeamish',
  '55656': 'squeegee',
  '55661': 'squeeze',
  '55662': 'squeezing',
  '55663': 'squid',
  '55664': 'squiggle',
  '55665': 'squiggly',
  '55666': 'squint',
  '56111': 'squire',
  '56112': 'squirt',
  '56113': 'squishier',
  '56114': 'squishy',
  '56115': 'stability',
  '56116': 'stabilize',
  '56121': 'stable',
  '56122': 'stack',
  '56123': 'stadium',
  '56124': 'staff',
  '56125': 'stage',
  '56126': 'staging',
  '56131': 'stagnant',
  '56132': 'stagnate',
  '56133': 'stainable',
  '56134': 'stained',
  '56135': 'staining',
  '56136': 'stainless',
  '56141': 'stalemate',
  '56142': 'staleness',
  '56143': 'stalling',
  '56144': 'stallion',
  '56145': 'stamina',
  '56146': 'stammer',
  '56151': 'stamp',
  '56152': 'stand',
  '56153': 'stank',
  '56154': 'staple',
  '56155': 'stapling',
  '56156': 'starboard',
  '56161': 'starch',
  '56162': 'stardom',
  '56163': 'stardust',
  '56164': 'starfish',
  '56165': 'stargazer',
  '56166': 'staring',
  '56211': 'stark',
  '56212': 'starless',
  '56213': 'starlet',
  '56214': 'starlight',
  '56215': 'starlit',
  '56216': 'starring',
  '56221': 'starry',
  '56222': 'starship',
  '56223': 'starter',
  '56224': 'starting',
  '56225': 'startle',
  '56226': 'startling',
  '56231': 'startup',
  '56232': 'starved',
  '56233': 'starving',
  '56234': 'stash',
  '56235': 'state',
  '56236': 'static',
  '56241': 'statistic',
  '56242': 'statue',
  '56243': 'stature',
  '56244': 'status',
  '56245': 'statute',
  '56246': 'statutory',
  '56251': 'staunch',
  '56252': 'stays',
  '56253': 'steadfast',
  '56254': 'steadier',
  '56255': 'steadily',
  '56256': 'steadying',
  '56261': 'steam',
  '56262': 'steed',
  '56263': 'steep',
  '56264': 'steerable',
  '56265': 'steering',
  '56266': 'steersman',
  '56311': 'stegosaur',
  '56312': 'stellar',
  '56313': 'stem',
  '56314': 'stench',
  '56315': 'stencil',
  '56316': 'step',
  '56321': 'stereo',
  '56322': 'sterile',
  '56323': 'sterility',
  '56324': 'sterilize',
  '56325': 'sterling',
  '56326': 'sternness',
  '56331': 'sternum',
  '56332': 'stew',
  '56333': 'stick',
  '56334': 'stiffen',
  '56335': 'stiffly',
  '56336': 'stiffness',
  '56341': 'stifle',
  '56342': 'stifling',
  '56343': 'stillness',
  '56344': 'stilt',
  '56345': 'stimulant',
  '56346': 'stimulate',
  '56351': 'stimuli',
  '56352': 'stimulus',
  '56353': 'stinger',
  '56354': 'stingily',
  '56355': 'stinging',
  '56356': 'stingray',
  '56361': 'stingy',
  '56362': 'stinking',
  '56363': 'stinky',
  '56364': 'stipend',
  '56365': 'stipulate',
  '56366': 'stir',
  '56411': 'stitch',
  '56412': 'stock',
  '56413': 'stoic',
  '56414': 'stoke',
  '56415': 'stole',
  '56416': 'stomp',
  '56421': 'stonewall',
  '56422': 'stoneware',
  '56423': 'stonework',
  '56424': 'stoning',
  '56425': 'stony',
  '56426': 'stood',
  '56431': 'stooge',
  '56432': 'stool',
  '56433': 'stoop',
  '56434': 'stoplight',
  '56435': 'stoppable',
  '56436': 'stoppage',
  '56441': 'stopped',
  '56442': 'stopper',
  '56443': 'stopping',
  '56444': 'stopwatch',
  '56445': 'storable',
  '56446': 'storage',
  '56451': 'storeroom',
  '56452': 'storewide',
  '56453': 'storm',
  '56454': 'stout',
  '56455': 'stove',
  '56456': 'stowaway',
  '56461': 'stowing',
  '56462': 'straddle',
  '56463': 'straggler',
  '56464': 'strained',
  '56465': 'strainer',
  '56466': 'straining',
  '56511': 'strangely',
  '56512': 'stranger',
  '56513': 'strangle',
  '56514': 'strategic',
  '56515': 'strategy',
  '56516': 'stratus',
  '56521': 'straw',
  '56522': 'stray',
  '56523': 'streak',
  '56524': 'stream',
  '56525': 'street',
  '56526': 'strength',
  '56531': 'strenuous',
  '56532': 'strep',
  '56533': 'stress',
  '56534': 'stretch',
  '56535': 'strewn',
  '56536': 'stricken',
  '56541': 'strict',
  '56542': 'stride',
  '56543': 'strife',
  '56544': 'strike',
  '56545': 'striking',
  '56546': 'strive',
  '56551': 'striving',
  '56552': 'strobe',
  '56553': 'strode',
  '56554': 'stroller',
  '56555': 'strongbox',
  '56556': 'strongly',
  '56561': 'strongman',
  '56562': 'struck',
  '56563': 'structure',
  '56564': 'strudel',
  '56565': 'struggle',
  '56566': 'strum',
  '56611': 'strung',
  '56612': 'strut',
  '56613': 'stubbed',
  '56614': 'stubble',
  '56615': 'stubbly',
  '56616': 'stubborn',
  '56621': 'stucco',
  '56622': 'stuck',
  '56623': 'student',
  '56624': 'studied',
  '56625': 'studio',
  '56626': 'study',
  '56631': 'stuffed',
  '56632': 'stuffing',
  '56633': 'stuffy',
  '56634': 'stumble',
  '56635': 'stumbling',
  '56636': 'stump',
  '56641': 'stung',
  '56642': 'stunned',
  '56643': 'stunner',
  '56644': 'stunning',
  '56645': 'stunt',
  '56646': 'stupor',
  '56651': 'sturdily',
  '56652': 'sturdy',
  '56653': 'styling',
  '56654': 'stylishly',
  '56655': 'stylist',
  '56656': 'stylized',
  '56661': 'stylus',
  '56662': 'suave',
  '56663': 'subarctic',
  '56664': 'subatomic',
  '56665': 'subdivide',
  '56666': 'subdued',
  '61111': 'subduing',
  '61112': 'subfloor',
  '61113': 'subgroup',
  '61114': 'subheader',
  '61115': 'subject',
  '61116': 'sublease',
  '61121': 'sublet',
  '61122': 'sublevel',
  '61123': 'sublime',
  '61124': 'submarine',
  '61125': 'submerge',
  '61126': 'submersed',
  '61131': 'submitter',
  '61132': 'subpanel',
  '61133': 'subpar',
  '61134': 'subplot',
  '61135': 'subprime',
  '61136': 'subscribe',
  '61141': 'subscript',
  '61142': 'subsector',
  '61143': 'subside',
  '61144': 'subsiding',
  '61145': 'subsidize',
  '61146': 'subsidy',
  '61151': 'subsoil',
  '61152': 'subsonic',
  '61153': 'substance',
  '61154': 'subsystem',
  '61155': 'subtext',
  '61156': 'subtitle',
  '61161': 'subtly',
  '61162': 'subtotal',
  '61163': 'subtract',
  '61164': 'subtype',
  '61165': 'suburb',
  '61166': 'subway',
  '61211': 'subwoofer',
  '61212': 'subzero',
  '61213': 'succulent',
  '61214': 'such',
  '61215': 'suction',
  '61216': 'sudden',
  '61221': 'sudoku',
  '61222': 'suds',
  '61223': 'sufferer',
  '61224': 'suffering',
  '61225': 'suffice',
  '61226': 'suffix',
  '61231': 'suffocate',
  '61232': 'suffrage',
  '61233': 'sugar',
  '61234': 'suggest',
  '61235': 'suing',
  '61236': 'suitable',
  '61241': 'suitably',
  '61242': 'suitcase',
  '61243': 'suitor',
  '61244': 'sulfate',
  '61245': 'sulfide',
  '61246': 'sulfite',
  '61251': 'sulfur',
  '61252': 'sulk',
  '61253': 'sullen',
  '61254': 'sulphate',
  '61255': 'sulphuric',
  '61256': 'sultry',
  '61261': 'superbowl',
  '61262': 'superglue',
  '61263': 'superhero',
  '61264': 'superior',
  '61265': 'superjet',
  '61266': 'superman',
  '61311': 'supermom',
  '61312': 'supernova',
  '61313': 'supervise',
  '61314': 'supper',
  '61315': 'supplier',
  '61316': 'supply',
  '61321': 'support',
  '61322': 'supremacy',
  '61323': 'supreme',
  '61324': 'surcharge',
  '61325': 'surely',
  '61326': 'sureness',
  '61331': 'surface',
  '61332': 'surfacing',
  '61333': 'surfboard',
  '61334': 'surfer',
  '61335': 'surgery',
  '61336': 'surgical',
  '61341': 'surging',
  '61342': 'surname',
  '61343': 'surpass',
  '61344': 'surplus',
  '61345': 'surprise',
  '61346': 'surreal',
  '61351': 'surrender',
  '61352': 'surrogate',
  '61353': 'surround',
  '61354': 'survey',
  '61355': 'survival',
  '61356': 'survive',
  '61361': 'surviving',
  '61362': 'survivor',
  '61363': 'sushi',
  '61364': 'suspect',
  '61365': 'suspend',
  '61366': 'suspense',
  '61411': 'sustained',
  '61412': 'sustainer',
  '61413': 'swab',
  '61414': 'swaddling',
  '61415': 'swagger',
  '61416': 'swampland',
  '61421': 'swan',
  '61422': 'swapping',
  '61423': 'swarm',
  '61424': 'sway',
  '61425': 'swear',
  '61426': 'sweat',
  '61431': 'sweep',
  '61432': 'swell',
  '61433': 'swept',
  '61434': 'swerve',
  '61435': 'swifter',
  '61436': 'swiftly',
  '61441': 'swiftness',
  '61442': 'swimmable',
  '61443': 'swimmer',
  '61444': 'swimming',
  '61445': 'swimsuit',
  '61446': 'swimwear',
  '61451': 'swinger',
  '61452': 'swinging',
  '61453': 'swipe',
  '61454': 'swirl',
  '61455': 'switch',
  '61456': 'swivel',
  '61461': 'swizzle',
  '61462': 'swooned',
  '61463': 'swoop',
  '61464': 'swoosh',
  '61465': 'swore',
  '61466': 'sworn',
  '61511': 'swung',
  '61512': 'sycamore',
  '61513': 'sympathy',
  '61514': 'symphonic',
  '61515': 'symphony',
  '61516': 'symptom',
  '61521': 'synapse',
  '61522': 'syndrome',
  '61523': 'synergy',
  '61524': 'synopses',
  '61525': 'synopsis',
  '61526': 'synthesis',
  '61531': 'synthetic',
  '61532': 'syrup',
  '61533': 'system',
  '61534': 't-shirt',
  '61535': 'tabasco',
  '61536': 'tabby',
  '61541': 'tableful',
  '61542': 'tables',
  '61543': 'tablet',
  '61544': 'tableware',
  '61545': 'tabloid',
  '61546': 'tackiness',
  '61551': 'tacking',
  '61552': 'tackle',
  '61553': 'tackling',
  '61554': 'tacky',
  '61555': 'taco',
  '61556': 'tactful',
  '61561': 'tactical',
  '61562': 'tactics',
  '61563': 'tactile',
  '61564': 'tactless',
  '61565': 'tadpole',
  '61566': 'taekwondo',
  '61611': 'tag',
  '61612': 'tainted',
  '61613': 'take',
  '61614': 'taking',
  '61615': 'talcum',
  '61616': 'talisman',
  '61621': 'tall',
  '61622': 'talon',
  '61623': 'tamale',
  '61624': 'tameness',
  '61625': 'tamer',
  '61626': 'tamper',
  '61631': 'tank',
  '61632': 'tanned',
  '61633': 'tannery',
  '61634': 'tanning',
  '61635': 'tantrum',
  '61636': 'tapeless',
  '61641': 'tapered',
  '61642': 'tapering',
  '61643': 'tapestry',
  '61644': 'tapioca',
  '61645': 'tapping',
  '61646': 'taps',
  '61651': 'tarantula',
  '61652': 'target',
  '61653': 'tarmac',
  '61654': 'tarnish',
  '61655': 'tarot',
  '61656': 'tartar',
  '61661': 'tartly',
  '61662': 'tartness',
  '61663': 'task',
  '61664': 'tassel',
  '61665': 'taste',
  '61666': 'tastiness',
  '62111': 'tasting',
  '62112': 'tasty',
  '62113': 'tattered',
  '62114': 'tattle',
  '62115': 'tattling',
  '62116': 'tattoo',
  '62121': 'taunt',
  '62122': 'tavern',
  '62123': 'thank',
  '62124': 'that',
  '62125': 'thaw',
  '62126': 'theater',
  '62131': 'theatrics',
  '62132': 'thee',
  '62133': 'theft',
  '62134': 'theme',
  '62135': 'theology',
  '62136': 'theorize',
  '62141': 'thermal',
  '62142': 'thermos',
  '62143': 'thesaurus',
  '62144': 'these',
  '62145': 'thesis',
  '62146': 'thespian',
  '62151': 'thicken',
  '62152': 'thicket',
  '62153': 'thickness',
  '62154': 'thieving',
  '62155': 'thievish',
  '62156': 'thigh',
  '62161': 'thimble',
  '62162': 'thing',
  '62163': 'think',
  '62164': 'thinly',
  '62165': 'thinner',
  '62166': 'thinness',
  '62211': 'thinning',
  '62212': 'thirstily',
  '62213': 'thirsting',
  '62214': 'thirsty',
  '62215': 'thirteen',
  '62216': 'thirty',
  '62221': 'thong',
  '62222': 'thorn',
  '62223': 'those',
  '62224': 'thousand',
  '62225': 'thrash',
  '62226': 'thread',
  '62231': 'threaten',
  '62232': 'threefold',
  '62233': 'thrift',
  '62234': 'thrill',
  '62235': 'thrive',
  '62236': 'thriving',
  '62241': 'throat',
  '62242': 'throbbing',
  '62243': 'throng',
  '62244': 'throttle',
  '62245': 'throwaway',
  '62246': 'throwback',
  '62251': 'thrower',
  '62252': 'throwing',
  '62253': 'thud',
  '62254': 'thumb',
  '62255': 'thumping',
  '62256': 'thursday',
  '62261': 'thus',
  '62262': 'thwarting',
  '62263': 'thyself',
  '62264': 'tiara',
  '62265': 'tibia',
  '62266': 'tidal',
  '62311': 'tidbit',
  '62312': 'tidiness',
  '62313': 'tidings',
  '62314': 'tidy',
  '62315': 'tiger',
  '62316': 'tighten',
  '62321': 'tightly',
  '62322': 'tightness',
  '62323': 'tightrope',
  '62324': 'tightwad',
  '62325': 'tigress',
  '62326': 'tile',
  '62331': 'tiling',
  '62332': 'till',
  '62333': 'tilt',
  '62334': 'timid',
  '62335': 'timing',
  '62336': 'timothy',
  '62341': 'tinderbox',
  '62342': 'tinfoil',
  '62343': 'tingle',
  '62344': 'tingling',
  '62345': 'tingly',
  '62346': 'tinker',
  '62351': 'tinkling',
  '62352': 'tinsel',
  '62353': 'tinsmith',
  '62354': 'tint',
  '62355': 'tinwork',
  '62356': 'tiny',
  '62361': 'tipoff',
  '62362': 'tipped',
  '62363': 'tipper',
  '62364': 'tipping',
  '62365': 'tiptoeing',
  '62366': 'tiptop',
  '62411': 'tiring',
  '62412': 'tissue',
  '62413': 'trace',
  '62414': 'tracing',
  '62415': 'track',
  '62416': 'traction',
  '62421': 'tractor',
  '62422': 'trade',
  '62423': 'trading',
  '62424': 'tradition',
  '62425': 'traffic',
  '62426': 'tragedy',
  '62431': 'trailing',
  '62432': 'trailside',
  '62433': 'train',
  '62434': 'traitor',
  '62435': 'trance',
  '62436': 'tranquil',
  '62441': 'transfer',
  '62442': 'transform',
  '62443': 'translate',
  '62444': 'transpire',
  '62445': 'transport',
  '62446': 'transpose',
  '62451': 'trapdoor',
  '62452': 'trapeze',
  '62453': 'trapezoid',
  '62454': 'trapped',
  '62455': 'trapper',
  '62456': 'trapping',
  '62461': 'traps',
  '62462': 'trash',
  '62463': 'travel',
  '62464': 'traverse',
  '62465': 'travesty',
  '62466': 'tray',
  '62511': 'treachery',
  '62512': 'treading',
  '62513': 'treadmill',
  '62514': 'treason',
  '62515': 'treat',
  '62516': 'treble',
  '62521': 'tree',
  '62522': 'trekker',
  '62523': 'tremble',
  '62524': 'trembling',
  '62525': 'tremor',
  '62526': 'trench',
  '62531': 'trend',
  '62532': 'trespass',
  '62533': 'triage',
  '62534': 'trial',
  '62535': 'triangle',
  '62536': 'tribesman',
  '62541': 'tribunal',
  '62542': 'tribune',
  '62543': 'tributary',
  '62544': 'tribute',
  '62545': 'triceps',
  '62546': 'trickery',
  '62551': 'trickily',
  '62552': 'tricking',
  '62553': 'trickle',
  '62554': 'trickster',
  '62555': 'tricky',
  '62556': 'tricolor',
  '62561': 'tricycle',
  '62562': 'trident',
  '62563': 'tried',
  '62564': 'trifle',
  '62565': 'trifocals',
  '62566': 'trillion',
  '62611': 'trilogy',
  '62612': 'trimester',
  '62613': 'trimmer',
  '62614': 'trimming',
  '62615': 'trimness',
  '62616': 'trinity',
  '62621': 'trio',
  '62622': 'tripod',
  '62623': 'tripping',
  '62624': 'triumph',
  '62625': 'trivial',
  '62626': 'trodden',
  '62631': 'trolling',
  '62632': 'trombone',
  '62633': 'trophy',
  '62634': 'tropical',
  '62635': 'tropics',
  '62636': 'trouble',
  '62641': 'troubling',
  '62642': 'trough',
  '62643': 'trousers',
  '62644': 'trout',
  '62645': 'trowel',
  '62646': 'truce',
  '62651': 'truck',
  '62652': 'truffle',
  '62653': 'trump',
  '62654': 'trunks',
  '62655': 'trustable',
  '62656': 'trustee',
  '62661': 'trustful',
  '62662': 'trusting',
  '62663': 'trustless',
  '62664': 'truth',
  '62665': 'try',
  '62666': 'tubby',
  '63111': 'tubeless',
  '63112': 'tubular',
  '63113': 'tucking',
  '63114': 'tuesday',
  '63115': 'tug',
  '63116': 'tuition',
  '63121': 'tulip',
  '63122': 'tumble',
  '63123': 'tumbling',
  '63124': 'tummy',
  '63125': 'turban',
  '63126': 'turbine',
  '63131': 'turbofan',
  '63132': 'turbojet',
  '63133': 'turbulent',
  '63134': 'turf',
  '63135': 'turkey',
  '63136': 'turmoil',
  '63141': 'turret',
  '63142': 'turtle',
  '63143': 'tusk',
  '63144': 'tutor',
  '63145': 'tutu',
  '63146': 'tux',
  '63151': 'tweak',
  '63152': 'tweed',
  '63153': 'tweet',
  '63154': 'tweezers',
  '63155': 'twelve',
  '63156': 'twentieth',
  '63161': 'twenty',
  '63162': 'twerp',
  '63163': 'twice',
  '63164': 'twiddle',
  '63165': 'twiddling',
  '63166': 'twig',
  '63211': 'twilight',
  '63212': 'twine',
  '63213': 'twins',
  '63214': 'twirl',
  '63215': 'twistable',
  '63216': 'twisted',
  '63221': 'twister',
  '63222': 'twisting',
  '63223': 'twisty',
  '63224': 'twitch',
  '63225': 'twitter',
  '63226': 'tycoon',
  '63231': 'tying',
  '63232': 'tyke',
  '63233': 'udder',
  '63234': 'ultimate',
  '63235': 'ultimatum',
  '63236': 'ultra',
  '63241': 'umbilical',
  '63242': 'umbrella',
  '63243': 'umpire',
  '63244': 'unabashed',
  '63245': 'unable',
  '63246': 'unadorned',
  '63251': 'unadvised',
  '63252': 'unafraid',
  '63253': 'unaired',
  '63254': 'unaligned',
  '63255': 'unaltered',
  '63256': 'unarmored',
  '63261': 'unashamed',
  '63262': 'unaudited',
  '63263': 'unawake',
  '63264': 'unaware',
  '63265': 'unbaked',
  '63266': 'unbalance',
  '63311': 'unbeaten',
  '63312': 'unbend',
  '63313': 'unbent',
  '63314': 'unbiased',
  '63315': 'unbitten',
  '63316': 'unblended',
  '63321': 'unblessed',
  '63322': 'unblock',
  '63323': 'unbolted',
  '63324': 'unbounded',
  '63325': 'unboxed',
  '63326': 'unbraided',
  '63331': 'unbridle',
  '63332': 'unbroken',
  '63333': 'unbuckled',
  '63334': 'unbundle',
  '63335': 'unburned',
  '63336': 'unbutton',
  '63341': 'uncanny',
  '63342': 'uncapped',
  '63343': 'uncaring',
  '63344': 'uncertain',
  '63345': 'unchain',
  '63346': 'unchanged',
  '63351': 'uncharted',
  '63352': 'uncheck',
  '63353': 'uncivil',
  '63354': 'unclad',
  '63355': 'unclaimed',
  '63356': 'unclamped',
  '63361': 'unclasp',
  '63362': 'uncle',
  '63363': 'unclip',
  '63364': 'uncloak',
  '63365': 'unclog',
  '63366': 'unclothed',
  '63411': 'uncoated',
  '63412': 'uncoiled',
  '63413': 'uncolored',
  '63414': 'uncombed',
  '63415': 'uncommon',
  '63416': 'uncooked',
  '63421': 'uncork',
  '63422': 'uncorrupt',
  '63423': 'uncounted',
  '63424': 'uncouple',
  '63425': 'uncouth',
  '63426': 'uncover',
  '63431': 'uncross',
  '63432': 'uncrown',
  '63433': 'uncrushed',
  '63434': 'uncured',
  '63435': 'uncurious',
  '63436': 'uncurled',
  '63441': 'uncut',
  '63442': 'undamaged',
  '63443': 'undated',
  '63444': 'undaunted',
  '63445': 'undead',
  '63446': 'undecided',
  '63451': 'undefined',
  '63452': 'underage',
  '63453': 'underarm',
  '63454': 'undercoat',
  '63455': 'undercook',
  '63456': 'undercut',
  '63461': 'underdog',
  '63462': 'underdone',
  '63463': 'underfed',
  '63464': 'underfeed',
  '63465': 'underfoot',
  '63466': 'undergo',
  '63511': 'undergrad',
  '63512': 'underhand',
  '63513': 'underline',
  '63514': 'underling',
  '63515': 'undermine',
  '63516': 'undermost',
  '63521': 'underpaid',
  '63522': 'underpass',
  '63523': 'underpay',
  '63524': 'underrate',
  '63525': 'undertake',
  '63526': 'undertone',
  '63531': 'undertook',
  '63532': 'undertow',
  '63533': 'underuse',
  '63534': 'underwear',
  '63535': 'underwent',
  '63536': 'underwire',
  '63541': 'undesired',
  '63542': 'undiluted',
  '63543': 'undivided',
  '63544': 'undocked',
  '63545': 'undoing',
  '63546': 'undone',
  '63551': 'undrafted',
  '63552': 'undress',
  '63553': 'undrilled',
  '63554': 'undusted',
  '63555': 'undying',
  '63556': 'unearned',
  '63561': 'unearth',
  '63562': 'unease',
  '63563': 'uneasily',
  '63564': 'uneasy',
  '63565': 'uneatable',
  '63566': 'uneaten',
  '63611': 'unedited',
  '63612': 'unelected',
  '63613': 'unending',
  '63614': 'unengaged',
  '63615': 'unenvied',
  '63616': 'unequal',
  '63621': 'unethical',
  '63622': 'uneven',
  '63623': 'unexpired',
  '63624': 'unexposed',
  '63625': 'unfailing',
  '63626': 'unfair',
  '63631': 'unfasten',
  '63632': 'unfazed',
  '63633': 'unfeeling',
  '63634': 'unfiled',
  '63635': 'unfilled',
  '63636': 'unfitted',
  '63641': 'unfitting',
  '63642': 'unfixable',
  '63643': 'unfixed',
  '63644': 'unflawed',
  '63645': 'unfocused',
  '63646': 'unfold',
  '63651': 'unfounded',
  '63652': 'unframed',
  '63653': 'unfreeze',
  '63654': 'unfrosted',
  '63655': 'unfrozen',
  '63656': 'unfunded',
  '63661': 'unglazed',
  '63662': 'ungloved',
  '63663': 'unglue',
  '63664': 'ungodly',
  '63665': 'ungraded',
  '63666': 'ungreased',
  '64111': 'unguarded',
  '64112': 'unguided',
  '64113': 'unhappily',
  '64114': 'unhappy',
  '64115': 'unharmed',
  '64116': 'unhealthy',
  '64121': 'unheard',
  '64122': 'unhearing',
  '64123': 'unheated',
  '64124': 'unhelpful',
  '64125': 'unhidden',
  '64126': 'unhinge',
  '64131': 'unhitched',
  '64132': 'unholy',
  '64133': 'unhook',
  '64134': 'unicorn',
  '64135': 'unicycle',
  '64136': 'unified',
  '64141': 'unifier',
  '64142': 'uniformed',
  '64143': 'uniformly',
  '64144': 'unify',
  '64145': 'unimpeded',
  '64146': 'uninjured',
  '64151': 'uninstall',
  '64152': 'uninsured',
  '64153': 'uninvited',
  '64154': 'union',
  '64155': 'uniquely',
  '64156': 'unisexual',
  '64161': 'unison',
  '64162': 'unissued',
  '64163': 'unit',
  '64164': 'universal',
  '64165': 'universe',
  '64166': 'unjustly',
  '64211': 'unkempt',
  '64212': 'unkind',
  '64213': 'unknotted',
  '64214': 'unknowing',
  '64215': 'unknown',
  '64216': 'unlaced',
  '64221': 'unlatch',
  '64222': 'unlawful',
  '64223': 'unleaded',
  '64224': 'unlearned',
  '64225': 'unleash',
  '64226': 'unless',
  '64231': 'unleveled',
  '64232': 'unlighted',
  '64233': 'unlikable',
  '64234': 'unlimited',
  '64235': 'unlined',
  '64236': 'unlinked',
  '64241': 'unlisted',
  '64242': 'unlit',
  '64243': 'unlivable',
  '64244': 'unloaded',
  '64245': 'unloader',
  '64246': 'unlocked',
  '64251': 'unlocking',
  '64252': 'unlovable',
  '64253': 'unloved',
  '64254': 'unlovely',
  '64255': 'unloving',
  '64256': 'unluckily',
  '64261': 'unlucky',
  '64262': 'unmade',
  '64263': 'unmanaged',
  '64264': 'unmanned',
  '64265': 'unmapped',
  '64266': 'unmarked',
  '64311': 'unmasked',
  '64312': 'unmasking',
  '64313': 'unmatched',
  '64314': 'unmindful',
  '64315': 'unmixable',
  '64316': 'unmixed',
  '64321': 'unmolded',
  '64322': 'unmoral',
  '64323': 'unmovable',
  '64324': 'unmoved',
  '64325': 'unmoving',
  '64326': 'unnamable',
  '64331': 'unnamed',
  '64332': 'unnatural',
  '64333': 'unneeded',
  '64334': 'unnerve',
  '64335': 'unnerving',
  '64336': 'unnoticed',
  '64341': 'unopened',
  '64342': 'unopposed',
  '64343': 'unpack',
  '64344': 'unpadded',
  '64345': 'unpaid',
  '64346': 'unpainted',
  '64351': 'unpaired',
  '64352': 'unpaved',
  '64353': 'unpeeled',
  '64354': 'unpicked',
  '64355': 'unpiloted',
  '64356': 'unpinned',
  '64361': 'unplanned',
  '64362': 'unplanted',
  '64363': 'unpleased',
  '64364': 'unpledged',
  '64365': 'unplowed',
  '64366': 'unplug',
  '64411': 'unpopular',
  '64412': 'unproven',
  '64413': 'unquote',
  '64414': 'unranked',
  '64415': 'unrated',
  '64416': 'unraveled',
  '64421': 'unreached',
  '64422': 'unread',
  '64423': 'unreal',
  '64424': 'unreeling',
  '64425': 'unrefined',
  '64426': 'unrelated',
  '64431': 'unrented',
  '64432': 'unrest',
  '64433': 'unretired',
  '64434': 'unrevised',
  '64435': 'unrigged',
  '64436': 'unripe',
  '64441': 'unrivaled',
  '64442': 'unroasted',
  '64443': 'unrobed',
  '64444': 'unroll',
  '64445': 'unruffled',
  '64446': 'unruly',
  '64451': 'unrushed',
  '64452': 'unsaddle',
  '64453': 'unsafe',
  '64454': 'unsaid',
  '64455': 'unsalted',
  '64456': 'unsaved',
  '64461': 'unsavory',
  '64462': 'unscathed',
  '64463': 'unscented',
  '64464': 'unscrew',
  '64465': 'unsealed',
  '64466': 'unseated',
  '64511': 'unsecured',
  '64512': 'unseeing',
  '64513': 'unseemly',
  '64514': 'unseen',
  '64515': 'unselect',
  '64516': 'unselfish',
  '64521': 'unsent',
  '64522': 'unsettled',
  '64523': 'unshackle',
  '64524': 'unshaken',
  '64525': 'unshaved',
  '64526': 'unshaven',
  '64531': 'unsheathe',
  '64532': 'unshipped',
  '64533': 'unsightly',
  '64534': 'unsigned',
  '64535': 'unskilled',
  '64536': 'unsliced',
  '64541': 'unsmooth',
  '64542': 'unsnap',
  '64543': 'unsocial',
  '64544': 'unsoiled',
  '64545': 'unsold',
  '64546': 'unsolved',
  '64551': 'unsorted',
  '64552': 'unspoiled',
  '64553': 'unspoken',
  '64554': 'unstable',
  '64555': 'unstaffed',
  '64556': 'unstamped',
  '64561': 'unsteady',
  '64562': 'unsterile',
  '64563': 'unstirred',
  '64564': 'unstitch',
  '64565': 'unstopped',
  '64566': 'unstuck',
  '64611': 'unstuffed',
  '64612': 'unstylish',
  '64613': 'unsubtle',
  '64614': 'unsubtly',
  '64615': 'unsuited',
  '64616': 'unsure',
  '64621': 'unsworn',
  '64622': 'untagged',
  '64623': 'untainted',
  '64624': 'untaken',
  '64625': 'untamed',
  '64626': 'untangled',
  '64631': 'untapped',
  '64632': 'untaxed',
  '64633': 'unthawed',
  '64634': 'unthread',
  '64635': 'untidy',
  '64636': 'untie',
  '64641': 'until',
  '64642': 'untimed',
  '64643': 'untimely',
  '64644': 'untitled',
  '64645': 'untoasted',
  '64646': 'untold',
  '64651': 'untouched',
  '64652': 'untracked',
  '64653': 'untrained',
  '64654': 'untreated',
  '64655': 'untried',
  '64656': 'untrimmed',
  '64661': 'untrue',
  '64662': 'untruth',
  '64663': 'unturned',
  '64664': 'untwist',
  '64665': 'untying',
  '64666': 'unusable',
  '65111': 'unused',
  '65112': 'unusual',
  '65113': 'unvalued',
  '65114': 'unvaried',
  '65115': 'unvarying',
  '65116': 'unveiled',
  '65121': 'unveiling',
  '65122': 'unvented',
  '65123': 'unviable',
  '65124': 'unvisited',
  '65125': 'unvocal',
  '65126': 'unwanted',
  '65131': 'unwarlike',
  '65132': 'unwary',
  '65133': 'unwashed',
  '65134': 'unwatched',
  '65135': 'unweave',
  '65136': 'unwed',
  '65141': 'unwelcome',
  '65142': 'unwell',
  '65143': 'unwieldy',
  '65144': 'unwilling',
  '65145': 'unwind',
  '65146': 'unwired',
  '65151': 'unwitting',
  '65152': 'unwomanly',
  '65153': 'unworldly',
  '65154': 'unworn',
  '65155': 'unworried',
  '65156': 'unworthy',
  '65161': 'unwound',
  '65162': 'unwoven',
  '65163': 'unwrapped',
  '65164': 'unwritten',
  '65165': 'unzip',
  '65166': 'upbeat',
  '65211': 'upchuck',
  '65212': 'upcoming',
  '65213': 'upcountry',
  '65214': 'update',
  '65215': 'upfront',
  '65216': 'upgrade',
  '65221': 'upheaval',
  '65222': 'upheld',
  '65223': 'uphill',
  '65224': 'uphold',
  '65225': 'uplifted',
  '65226': 'uplifting',
  '65231': 'upload',
  '65232': 'upon',
  '65233': 'upper',
  '65234': 'upright',
  '65235': 'uprising',
  '65236': 'upriver',
  '65241': 'uproar',
  '65242': 'uproot',
  '65243': 'upscale',
  '65244': 'upside',
  '65245': 'upstage',
  '65246': 'upstairs',
  '65251': 'upstart',
  '65252': 'upstate',
  '65253': 'upstream',
  '65254': 'upstroke',
  '65255': 'upswing',
  '65256': 'uptake',
  '65261': 'uptight',
  '65262': 'uptown',
  '65263': 'upturned',
  '65264': 'upward',
  '65265': 'upwind',
  '65266': 'uranium',
  '65311': 'urban',
  '65312': 'urchin',
  '65313': 'urethane',
  '65314': 'urgency',
  '65315': 'urgent',
  '65316': 'urging',
  '65321': 'urologist',
  '65322': 'urology',
  '65323': 'usable',
  '65324': 'usage',
  '65325': 'useable',
  '65326': 'used',
  '65331': 'uselessly',
  '65332': 'user',
  '65333': 'usher',
  '65334': 'usual',
  '65335': 'utensil',
  '65336': 'utility',
  '65341': 'utilize',
  '65342': 'utmost',
  '65343': 'utopia',
  '65344': 'utter',
  '65345': 'vacancy',
  '65346': 'vacant',
  '65351': 'vacate',
  '65352': 'vacation',
  '65353': 'vagabond',
  '65354': 'vagrancy',
  '65355': 'vagrantly',
  '65356': 'vaguely',
  '65361': 'vagueness',
  '65362': 'valiant',
  '65363': 'valid',
  '65364': 'valium',
  '65365': 'valley',
  '65366': 'valuables',
  '65411': 'value',
  '65412': 'vanilla',
  '65413': 'vanish',
  '65414': 'vanity',
  '65415': 'vanquish',
  '65416': 'vantage',
  '65421': 'vaporizer',
  '65422': 'variable',
  '65423': 'variably',
  '65424': 'varied',
  '65425': 'variety',
  '65426': 'various',
  '65431': 'varmint',
  '65432': 'varnish',
  '65433': 'varsity',
  '65434': 'varying',
  '65435': 'vascular',
  '65436': 'vaseline',
  '65441': 'vastly',
  '65442': 'vastness',
  '65443': 'veal',
  '65444': 'vegan',
  '65445': 'veggie',
  '65446': 'vehicular',
  '65451': 'velcro',
  '65452': 'velocity',
  '65453': 'velvet',
  '65454': 'vendetta',
  '65455': 'vending',
  '65456': 'vendor',
  '65461': 'veneering',
  '65462': 'vengeful',
  '65463': 'venomous',
  '65464': 'ventricle',
  '65465': 'venture',
  '65466': 'venue',
  '65511': 'venus',
  '65512': 'verbalize',
  '65513': 'verbally',
  '65514': 'verbose',
  '65515': 'verdict',
  '65516': 'verify',
  '65521': 'verse',
  '65522': 'version',
  '65523': 'versus',
  '65524': 'vertebrae',
  '65525': 'vertical',
  '65526': 'vertigo',
  '65531': 'very',
  '65532': 'vessel',
  '65533': 'vest',
  '65534': 'veteran',
  '65535': 'veto',
  '65536': 'vexingly',
  '65541': 'viability',
  '65542': 'viable',
  '65543': 'vibes',
  '65544': 'vice',
  '65545': 'vicinity',
  '65546': 'victory',
  '65551': 'video',
  '65552': 'viewable',
  '65553': 'viewer',
  '65554': 'viewing',
  '65555': 'viewless',
  '65556': 'viewpoint',
  '65561': 'vigorous',
  '65562': 'village',
  '65563': 'villain',
  '65564': 'vindicate',
  '65565': 'vineyard',
  '65566': 'vintage',
  '65611': 'violate',
  '65612': 'violation',
  '65613': 'violator',
  '65614': 'violet',
  '65615': 'violin',
  '65616': 'viper',
  '65621': 'viral',
  '65622': 'virtual',
  '65623': 'virtuous',
  '65624': 'virus',
  '65625': 'visa',
  '65626': 'viscosity',
  '65631': 'viscous',
  '65632': 'viselike',
  '65633': 'visible',
  '65634': 'visibly',
  '65635': 'vision',
  '65636': 'visiting',
  '65641': 'visitor',
  '65642': 'visor',
  '65643': 'vista',
  '65644': 'vitality',
  '65645': 'vitalize',
  '65646': 'vitally',
  '65651': 'vitamins',
  '65652': 'vivacious',
  '65653': 'vividly',
  '65654': 'vividness',
  '65655': 'vixen',
  '65656': 'vocalist',
  '65661': 'vocalize',
  '65662': 'vocally',
  '65663': 'vocation',
  '65664': 'voice',
  '65665': 'voicing',
  '65666': 'void',
  '66111': 'volatile',
  '66112': 'volley',
  '66113': 'voltage',
  '66114': 'volumes',
  '66115': 'voter',
  '66116': 'voting',
  '66121': 'voucher',
  '66122': 'vowed',
  '66123': 'vowel',
  '66124': 'voyage',
  '66125': 'wackiness',
  '66126': 'wad',
  '66131': 'wafer',
  '66132': 'waffle',
  '66133': 'waged',
  '66134': 'wager',
  '66135': 'wages',
  '66136': 'waggle',
  '66141': 'wagon',
  '66142': 'wake',
  '66143': 'waking',
  '66144': 'walk',
  '66145': 'walmart',
  '66146': 'walnut',
  '66151': 'walrus',
  '66152': 'waltz',
  '66153': 'wand',
  '66154': 'wannabe',
  '66155': 'wanted',
  '66156': 'wanting',
  '66161': 'wasabi',
  '66162': 'washable',
  '66163': 'washbasin',
  '66164': 'washboard',
  '66165': 'washbowl',
  '66166': 'washcloth',
  '66211': 'washday',
  '66212': 'washed',
  '66213': 'washer',
  '66214': 'washhouse',
  '66215': 'washing',
  '66216': 'washout',
  '66221': 'washroom',
  '66222': 'washstand',
  '66223': 'washtub',
  '66224': 'wasp',
  '66225': 'wasting',
  '66226': 'watch',
  '66231': 'water',
  '66232': 'waviness',
  '66233': 'waving',
  '66234': 'wavy',
  '66235': 'whacking',
  '66236': 'whacky',
  '66241': 'wham',
  '66242': 'wharf',
  '66243': 'wheat',
  '66244': 'whenever',
  '66245': 'whiff',
  '66246': 'whimsical',
  '66251': 'whinny',
  '66252': 'whiny',
  '66253': 'whisking',
  '66254': 'whoever',
  '66255': 'whole',
  '66256': 'whomever',
  '66261': 'whoopee',
  '66262': 'whooping',
  '66263': 'whoops',
  '66264': 'why',
  '66265': 'wick',
  '66266': 'widely',
  '66311': 'widen',
  '66312': 'widget',
  '66313': 'widow',
  '66314': 'width',
  '66315': 'wieldable',
  '66316': 'wielder',
  '66321': 'wife',
  '66322': 'wifi',
  '66323': 'wikipedia',
  '66324': 'wildcard',
  '66325': 'wildcat',
  '66326': 'wilder',
  '66331': 'wildfire',
  '66332': 'wildfowl',
  '66333': 'wildland',
  '66334': 'wildlife',
  '66335': 'wildly',
  '66336': 'wildness',
  '66341': 'willed',
  '66342': 'willfully',
  '66343': 'willing',
  '66344': 'willow',
  '66345': 'willpower',
  '66346': 'wilt',
  '66351': 'wimp',
  '66352': 'wince',
  '66353': 'wincing',
  '66354': 'wind',
  '66355': 'wing',
  '66356': 'winking',
  '66361': 'winner',
  '66362': 'winnings',
  '66363': 'winter',
  '66364': 'wipe',
  '66365': 'wired',
  '66366': 'wireless',
  '66411': 'wiring',
  '66412': 'wiry',
  '66413': 'wisdom',
  '66414': 'wise',
  '66415': 'wish',
  '66416': 'wisplike',
  '66421': 'wispy',
  '66422': 'wistful',
  '66423': 'wizard',
  '66424': 'wobble',
  '66425': 'wobbling',
  '66426': 'wobbly',
  '66431': 'wok',
  '66432': 'wolf',
  '66433': 'wolverine',
  '66434': 'womanhood',
  '66435': 'womankind',
  '66436': 'womanless',
  '66441': 'womanlike',
  '66442': 'womanly',
  '66443': 'womb',
  '66444': 'woof',
  '66445': 'wooing',
  '66446': 'wool',
  '66451': 'woozy',
  '66452': 'word',
  '66453': 'work',
  '66454': 'worried',
  '66455': 'worrier',
  '66456': 'worrisome',
  '66461': 'worry',
  '66462': 'worsening',
  '66463': 'worshiper',
  '66464': 'worst',
  '66465': 'wound',
  '66466': 'woven',
  '66511': 'wow',
  '66512': 'wrangle',
  '66513': 'wrath',
  '66514': 'wreath',
  '66515': 'wreckage',
  '66516': 'wrecker',
  '66521': 'wrecking',
  '66522': 'wrench',
  '66523': 'wriggle',
  '66524': 'wriggly',
  '66525': 'wrinkle',
  '66526': 'wrinkly',
  '66531': 'wrist',
  '66532': 'writing',
  '66533': 'written',
  '66534': 'wrongdoer',
  '66535': 'wronged',
  '66536': 'wrongful',
  '66541': 'wrongly',
  '66542': 'wrongness',
  '66543': 'wrought',
  '66544': 'xbox',
  '66545': 'xerox',
  '66546': 'yahoo',
  '66551': 'yam',
  '66552': 'yanking',
  '66553': 'yapping',
  '66554': 'yard',
  '66555': 'yarn',
  '66556': 'yeah',
  '66561': 'yearbook',
  '66562': 'yearling',
  '66563': 'yearly',
  '66564': 'yearning',
  '66565': 'yeast',
  '66566': 'yelling',
  '66611': 'yelp',
  '66612': 'yen',
  '66613': 'yesterday',
  '66614': 'yiddish',
  '66615': 'yield',
  '66616': 'yin',
  '66621': 'yippee',
  '66622': 'yo-yo',
  '66623': 'yodel',
  '66624': 'yoga',
  '66625': 'yogurt',
  '66626': 'yonder',
  '66631': 'yoyo',
  '66632': 'yummy',
  '66633': 'zap',
  '66634': 'zealous',
  '66635': 'zebra',
  '66636': 'zen',
  '66641': 'zeppelin',
  '66642': 'zero',
  '66643': 'zestfully',
  '66644': 'zesty',
  '66645': 'zigzagged',
  '66646': 'zipfile',
  '66651': 'zipping',
  '66652': 'zippy',
  '66653': 'zips',
  '66654': 'zit',
  '66655': 'zodiac',
  '66656': 'zombie',
  '66661': 'zone',
  '66662': 'zoning',
  '66663': 'zookeeper',
  '66664': 'zoologist',
  '66665': 'zoology',
  '66666': 'zoom'
}


/***/ }),
/* 264 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [
    _c("div", { class: [_vm.classes, _vm.addon ? "form-addon-wrapper" : ""] }, [
      _vm.inputType === "checkbox"
        ? _c("input", {
            directives: [
              {
                name: "model",
                rawName: "v-model",
                value: _vm.inputText,
                expression: "inputText"
              }
            ],
            class: _vm.addon ? "form-addon-input" : "",
            attrs: {
              name: _vm.name,
              id: _vm.id,
              placeholder: _vm.placeholder,
              required: _vm.required,
              type: "checkbox"
            },
            domProps: {
              checked: Array.isArray(_vm.inputText)
                ? _vm._i(_vm.inputText, null) > -1
                : _vm.inputText
            },
            on: {
              change: function($event) {
                var $$a = _vm.inputText,
                  $$el = $event.target,
                  $$c = $$el.checked ? true : false
                if (Array.isArray($$a)) {
                  var $$v = null,
                    $$i = _vm._i($$a, $$v)
                  if ($$el.checked) {
                    $$i < 0 && (_vm.inputText = $$a.concat([$$v]))
                  } else {
                    $$i > -1 &&
                      (_vm.inputText = $$a
                        .slice(0, $$i)
                        .concat($$a.slice($$i + 1)))
                  }
                } else {
                  _vm.inputText = $$c
                }
              }
            }
          })
        : _vm.inputType === "radio"
          ? _c("input", {
              directives: [
                {
                  name: "model",
                  rawName: "v-model",
                  value: _vm.inputText,
                  expression: "inputText"
                }
              ],
              class: _vm.addon ? "form-addon-input" : "",
              attrs: {
                name: _vm.name,
                id: _vm.id,
                placeholder: _vm.placeholder,
                required: _vm.required,
                type: "radio"
              },
              domProps: { checked: _vm._q(_vm.inputText, null) },
              on: {
                change: function($event) {
                  _vm.inputText = null
                }
              }
            })
          : _c("input", {
              directives: [
                {
                  name: "model",
                  rawName: "v-model",
                  value: _vm.inputText,
                  expression: "inputText"
                }
              ],
              class: _vm.addon ? "form-addon-input" : "",
              attrs: {
                name: _vm.name,
                id: _vm.id,
                placeholder: _vm.placeholder,
                required: _vm.required,
                type: _vm.inputType
              },
              domProps: { value: _vm.inputText },
              on: {
                input: function($event) {
                  if ($event.target.composing) {
                    return
                  }
                  _vm.inputText = $event.target.value
                }
              }
            }),
      _vm._v(" "),
      _c("div", { staticClass: "form-addon text-grey-dark" }, [
        _vm._v("\n            " + _vm._s(_vm.addon) + "\n        ")
      ])
    ]),
    _vm._v(" "),
    _vm.validationError
      ? _c("p", { staticClass: "form-help text-red" }, [
          _vm._v(_vm._s(_vm.validationError))
        ])
      : _vm._e(),
    _vm._v(" "),
    _c(
      "p",
      {
        staticClass: "form-help",
        attrs: { id: _vm.id ? _vm.id : _vm.name + "-form-help" }
      },
      [
        _vm._v(_vm._s(_vm.formHelp) + "\n        "),
        _vm.formHelp ? _c("br") : _vm._e(),
        _vm._v(" "),
        _c(
          "button",
          {
            staticClass: "link-text",
            on: {
              click: function($event) {
                $event.preventDefault()
                return _vm.setRandomValue($event)
              }
            }
          },
          [_vm._v("Generate")]
        ),
        _vm._v("\n        a random string for this value.\n    ")
      ]
    )
  ])
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-ea201292", module.exports)
  }
}

/***/ }),
/* 265 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(266)
/* template */
var __vue_template__ = __webpack_require__(270)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/aliasSendersRecipients/AliasSendersRecipientsForm.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-62ce80ec", Component.options)
  } else {
    hotAPI.reload("data-v-62ce80ec", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 266 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__AddressPillView__ = __webpack_require__(267);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__AddressPillView___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__AddressPillView__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["default"] = ({
  components: {
    AddressPillView: __WEBPACK_IMPORTED_MODULE_0__AddressPillView___default.a
  },
  props: {
    defaultTab: {
      type: String,
      validator: function validator(val) {
        return ['easy', 'advanced'].includes(val);
      },
      default: 'easy'
    },
    oldSenderMailboxes: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    oldRecipientMailboxes: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    oldExternalRecipients: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    validationErrorsProp: {
      type: Object,
      default: {
        senderMailboxes: null,
        recipientMailboxes: null,
        externalRecipients: null
      }
    }
  },
  data: function data() {
    return {
      allMailboxes: [],
      senderMailboxes: [],
      recipientMailboxes: [],
      externalRecipients: [],
      tabSetting: 'easy',
      formModalOptions: {
        show: false
      },
      validationErrors: {
        senderMailboxes: null,
        recipientMailboxes: null,
        externalRecipients: null
      }
    };
  },

  computed: {
    senderAndRecipientMailboxes: function senderAndRecipientMailboxes() {
      var senderMailboxes = this.senderMailboxes;
      var filteredMailboxes = this.recipientMailboxes.filter(function (mailbox) {
        return !senderMailboxes.some(function (m) {
          return m.id === mailbox.id;
        });
      });
      return _.union(senderMailboxes, filteredMailboxes);
    },
    needsAdvancedView: function needsAdvancedView() {
      return this.senderAndRecipientMailboxes.length !== this.senderMailboxes.length || this.senderAndRecipientMailboxes.length !== this.recipientMailboxes.length || this.externalRecipients.length > 0;
    },
    currentTab: function currentTab() {
      if (this.needsAdvancedView) {
        this.tabSetting = 'advanced';
        return 'advanced';
      }
      return this.tabSetting;
    },
    validationErrorsSendersAndRecipients: function validationErrorsSendersAndRecipients() {
      if (this.validationErrors.senderMailboxes && this.validationErrors.recipientMailboxes) {
        return this.validationErrors.senderMailboxes + ' ' + this.validationErrors.recipientMailboxes;
      }
      if (this.validationErrors.senderMailboxes) {
        return this.validationErrors.senderMailboxes;
      }
      return this.validationErrors.recipientMailboxes;
    }
  },
  methods: {
    removeMailboxFromSendersAndRecipients: function removeMailboxFromSendersAndRecipients(mailbox) {
      this.senderMailboxes.splice(this.senderMailboxes.indexOf(mailbox), 1);
      this.recipientMailboxes.splice(this.recipientMailboxes.indexOf(mailbox), 1);
    },
    removeMailboxFromSenders: function removeMailboxFromSenders(mailbox) {
      this.senderMailboxes.splice(this.senderMailboxes.indexOf(mailbox), 1);
      this.validationErrors.senderMailboxes = null;
    },
    removeMailboxFromRecipients: function removeMailboxFromRecipients(mailbox) {
      this.recipientMailboxes.splice(this.recipientMailboxes.indexOf(mailbox), 1);
      this.validationErrors.recipientMailboxes = null;
    },
    removeFromExternalRecipients: function removeFromExternalRecipients(recipient) {
      this.externalRecipients.splice(this.externalRecipients.indexOf(recipient), 1);
      this.validationErrors.externalRecipients = null;
    },
    addMailbox: function addMailbox(options, mailbox) {
      if (options.isSender && this.senderMailboxes.filter(function (mb) {
        return mailbox.id === mb.id;
      }).length === 0) {
        this.senderMailboxes.push(mailbox);
        this.validationErrors.senderMailboxes = null;
      }
      if (options.isRecipient && this.recipientMailboxes.filter(function (mb) {
        return mailbox.id === mb.id;
      }).length === 0) {
        this.recipientMailboxes.push(mailbox);
        this.validationErrors.recipientMailboxes = null;
      }
    },
    addExternalRecipient: function addExternalRecipient(address) {
      this.externalRecipients.push({ address: address });
      this.validationErrors.externalRecipients = null;
    },
    emitModalContentData: function emitModalContentData(isSender, isRecipient, isExternal) {
      var options = {
        isSender: isSender,
        isRecipient: isRecipient,
        isExternal: isExternal
      };
      var _callback = this.modalCallback;
      this.$emit('set-modal-content-payload', {
        options: options,
        callback: function callback(o, d) {
          _callback(o, d);
        }
      });
      this.$emit('set-modal-content-identifier', 'alias-mailbox-form');
    },
    modalCallback: function modalCallback(options, data) {
      if (options.isExternal) {
        this.addExternalRecipient(data);
        return;
      }
      this.addMailbox(options, data);
    }
  },
  created: function created() {
    this.tabSetting = this.defaultTab;
    this.senderMailboxes = this.oldSenderMailboxes;
    this.recipientMailboxes = this.oldRecipientMailboxes;
    this.externalRecipients = this.oldExternalRecipients;
    this.validationErrors = this.validationErrorsProp;
  }
});

/***/ }),
/* 267 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(268)
/* template */
var __vue_template__ = __webpack_require__(269)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/aliasSendersRecipients/AddressPillView.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-6c4a1b42", Component.options)
  } else {
    hotAPI.reload("data-v-6c4a1b42", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 268 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    title: {
      type: String,
      required: true
    },
    accounts: {
      type: Array,
      required: true
    },
    noneSelectedText: {
      type: String,
      default: "You haven't selected any addresses yet."
    },
    allowDeleting: {
      type: Boolean,
      default: true
    },
    areAccountsMailboxes: {
      type: Boolean,
      default: true
    },
    validationError: {
      type: String,
      default: null
    }
  },
  methods: {
    remove: function remove(account) {
      this.$emit('remove', account);
    }
  }
});

/***/ }),
/* 269 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [
    _c("div", { staticClass: "flex flex-row items-center" }, [
      _c("div", { staticClass: "form-label" }, [_vm._v(_vm._s(_vm.title))]),
      _vm._v(" "),
      _c("div", { staticClass: "ml-2 text-xs" }, [
        _c(
          "a",
          {
            staticClass:
              "text-grey no-underline hover:text-grey-dark focus:text-grey-dark",
            attrs: { title: "Create " + _vm.title, href: "#" },
            on: {
              click: function($event) {
                $event.preventDefault()
                _vm.$emit("create")
              }
            }
          },
          [
            _c("i", {
              staticClass: "fas fa-plus mr-2",
              attrs: { "aria-hidden": "true" }
            })
          ]
        )
      ])
    ]),
    _vm._v(" "),
    _vm.accounts.length > 0
      ? _c(
          "div",
          { staticClass: "flex flex-row flex-wrap -mx-1 mt-1" },
          _vm._l(_vm.accounts, function(account) {
            return _c("div", { staticClass: "address-pill" }, [
              _vm.areAccountsMailboxes
                ? _c("i", { staticClass: "fas fa-inbox text-grey mr-2" })
                : _vm._e(),
              _vm._v(
                "\n            " + _vm._s(account.address) + "\n            "
              ),
              _vm.allowDeleting
                ? _c(
                    "a",
                    {
                      staticClass:
                        "inline px-2 -mr-1 text-grey-dark hover:text-black focus:text-black no-underline select-none",
                      attrs: { title: "Remove " + account.address, href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.remove(account)
                        }
                      }
                    },
                    [_vm._v("\n                ×\n            ")]
                  )
                : _vm._e()
            ])
          })
        )
      : _c("div", { staticClass: "text-sm italic text-grey-dark py-2" }, [
          _vm._v(_vm._s(_vm.noneSelectedText))
        ]),
    _vm._v(" "),
    _vm.validationError
      ? _c("div", { staticClass: "form-help text-red py-2" }, [
          _vm._v(_vm._s(_vm.validationError))
        ])
      : _vm._e()
  ])
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-6c4a1b42", module.exports)
  }
}

/***/ }),
/* 270 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "relative" },
    [
      _c(
        "div",
        {
          staticClass:
            "flex flex-row items-start justify-between flex-wrap mb-2"
        },
        [
          _c("h4", { staticClass: "font-extrabold" }, [
            _vm._v("Senders and Recipients")
          ]),
          _vm._v(" "),
          _c("div", { staticClass: "tab-bar justify-end" }, [
            _c(
              "button",
              {
                class: { "tab-title": true, active: _vm.currentTab === "easy" },
                attrs: { disabled: _vm.needsAdvancedView },
                on: {
                  click: function($event) {
                    $event.preventDefault()
                    _vm.tabSetting = "easy"
                  }
                }
              },
              [_vm._v("\n                Easy\n            ")]
            ),
            _vm._v(" "),
            _c(
              "button",
              {
                class: {
                  "tab-title": true,
                  active: _vm.currentTab === "advanced"
                },
                on: {
                  click: function($event) {
                    $event.preventDefault()
                    _vm.tabSetting = "advanced"
                  }
                }
              },
              [_vm._v("\n                Advanced\n            ")]
            )
          ])
        ]
      ),
      _vm._v(" "),
      _c("transition", { attrs: { name: "fade", mode: "out-in" } }, [
        _vm.currentTab === "easy"
          ? _c(
              "div",
              { key: "easy" },
              [
                _c("address-pill-view", {
                  attrs: {
                    title: "Sender and Recipient Mailboxes",
                    accounts: _vm.senderAndRecipientMailboxes,
                    "validation-error": _vm.validationErrorsSendersAndRecipients
                  },
                  on: {
                    remove: _vm.removeMailboxFromSendersAndRecipients,
                    create: function($event) {
                      _vm.emitModalContentData(true, true, false)
                    }
                  }
                })
              ],
              1
            )
          : _c("div", { key: "advanced" }, [
              _c(
                "div",
                [
                  _c("address-pill-view", {
                    attrs: {
                      title: "Sender Mailboxes",
                      accounts: _vm.senderMailboxes,
                      "validation-error": _vm.validationErrors.senderMailboxes
                    },
                    on: {
                      remove: _vm.removeMailboxFromSenders,
                      create: function($event) {
                        _vm.emitModalContentData(true, false, false)
                      }
                    }
                  })
                ],
                1
              ),
              _vm._v(" "),
              _c(
                "div",
                { staticClass: "mt-4" },
                [
                  _c("address-pill-view", {
                    attrs: {
                      title: "Recipient Mailboxes",
                      accounts: _vm.recipientMailboxes,
                      "validation-error":
                        _vm.validationErrors.recipientMailboxes
                    },
                    on: {
                      remove: _vm.removeMailboxFromRecipients,
                      create: function($event) {
                        _vm.emitModalContentData(false, true, false)
                      }
                    }
                  })
                ],
                1
              ),
              _vm._v(" "),
              _c(
                "div",
                { staticClass: "mt-4" },
                [
                  _c("address-pill-view", {
                    attrs: {
                      title: "External Recipients",
                      accounts: _vm.externalRecipients,
                      "are-accounts-mailboxes": false,
                      "validation-error":
                        _vm.validationErrors.externalRecipients
                    },
                    on: {
                      remove: _vm.removeFromExternalRecipients,
                      create: function($event) {
                        _vm.emitModalContentData(false, false, true)
                      }
                    }
                  })
                ],
                1
              )
            ])
      ]),
      _vm._v(" "),
      _c(
        "div",
        [
          _vm._l(_vm.senderMailboxes, function(mailbox) {
            return _c("input", {
              attrs: {
                type: "hidden",
                name:
                  "sender_mailboxes[" +
                  _vm.senderMailboxes.indexOf(mailbox) +
                  "][id]"
              },
              domProps: { value: mailbox.id }
            })
          }),
          _vm._v(" "),
          _vm._l(_vm.senderMailboxes, function(mailbox) {
            return _c("input", {
              attrs: {
                type: "hidden",
                name:
                  "sender_mailboxes[" +
                  _vm.senderMailboxes.indexOf(mailbox) +
                  "][address]"
              },
              domProps: { value: mailbox.address }
            })
          }),
          _vm._v(" "),
          _vm._l(_vm.recipientMailboxes, function(mailbox) {
            return _c("input", {
              attrs: {
                type: "hidden",
                name:
                  "recipient_mailboxes[" +
                  _vm.recipientMailboxes.indexOf(mailbox) +
                  "][id]"
              },
              domProps: { value: mailbox.id }
            })
          }),
          _vm._v(" "),
          _vm._l(_vm.recipientMailboxes, function(mailbox) {
            return _c("input", {
              attrs: {
                type: "hidden",
                name:
                  "recipient_mailboxes[" +
                  _vm.recipientMailboxes.indexOf(mailbox) +
                  "][address]"
              },
              domProps: { value: mailbox.address }
            })
          }),
          _vm._v(" "),
          _vm._l(_vm.externalRecipients, function(recipient) {
            return _c("input", {
              attrs: {
                type: "hidden",
                name:
                  "external_recipients[" +
                  _vm.externalRecipients.indexOf(recipient) +
                  "][address]"
              },
              domProps: { value: recipient.address }
            })
          })
        ],
        2
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-62ce80ec", module.exports)
  }
}

/***/ }),
/* 271 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(272)
/* template */
var __vue_template__ = __webpack_require__(281)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/ModalContentProvider.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-bbe7a158", Component.options)
  } else {
    hotAPI.reload("data-v-bbe7a158", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 272 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__aliasSendersRecipients_AliasAddSenderRecipientForm__ = __webpack_require__(273);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__aliasSendersRecipients_AliasAddSenderRecipientForm___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__aliasSendersRecipients_AliasAddSenderRecipientForm__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__integrations_NewIntegrationParameterForm__ = __webpack_require__(278);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__integrations_NewIntegrationParameterForm___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__integrations_NewIntegrationParameterForm__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    modalContentIdentifier: {
      type: String,
      validator: function validator(val) {
        return ['alias-mailbox-form', 'new-integration-parameter-form'].includes(val);
      },
      required: true
    },
    modalContentPayload: {}
  },
  components: {
    AliasAddSenderRecipientForm: __WEBPACK_IMPORTED_MODULE_0__aliasSendersRecipients_AliasAddSenderRecipientForm___default.a,
    NewIntegrationParameterForm: __WEBPACK_IMPORTED_MODULE_1__integrations_NewIntegrationParameterForm___default.a
  }
});

/***/ }),
/* 273 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(274)
}
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(276)
/* template */
var __vue_template__ = __webpack_require__(277)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-c535a0f2"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/aliasSendersRecipients/AliasAddSenderRecipientForm.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-c535a0f2", Component.options)
  } else {
    hotAPI.reload("data-v-c535a0f2", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 274 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(275);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(11)("1b707777", content, false, {});
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"vue\":true,\"id\":\"data-v-c535a0f2\",\"scoped\":true,\"hasInlineConfig\":true}!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./AliasAddSenderRecipientForm.vue", function() {
     var newContent = require("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"vue\":true,\"id\":\"data-v-c535a0f2\",\"scoped\":true,\"hasInlineConfig\":true}!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./AliasAddSenderRecipientForm.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 275 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(10)(false);
// imports


// module
exports.push([module.i, "\n.popfade-enter-active[data-v-c535a0f2],\n.popfade-leave-active[data-v-c535a0f2] {\n  -webkit-transition: all .2s;\n  transition: all .2s;\n  -webkit-transform: scale(1);\n          transform: scale(1);\n}\n.popfade-enter[data-v-c535a0f2],\n.popfade-leave-to[data-v-c535a0f2] {\n  opacity: 0;\n  -webkit-transform: scale(.9) translateY(2em);\n          transform: scale(.9) translateY(2em);\n}\n", ""]);

// exports


/***/ }),
/* 276 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    payload: {
      type: Object,
      required: true
    }
  },
  data: function data() {
    return {
      searchInput: '',
      mailboxSearchResponse: null,
      validationError: ''
    };
  },

  methods: {
    triggerFetch: _.debounce(function () {
      this.fetchMailboxSearchResults();
    }, 300),
    fetchMailboxSearchResults: function fetchMailboxSearchResults() {
      var _this = this;

      axios.get('/mailboxes', {
        params: {
          search: this.searchInput
        }
      }).then(function (response) {
        _this.mailboxSearchResponse = response.data;
      }).catch(function (fail) {
        console.log(fail.response.data);
      });
    },
    addMailbox: function addMailbox(mailbox) {
      this.payload.options.show = false;
      this.payload.callback(this.payload.options, mailbox);
      this.$emit('close');
    },
    addExternalRecipient: function addExternalRecipient() {
      var re = /^\S+@[^@.\s]+[^@.\s]+(\.[^@.\s]+)*$/;
      if (!re.test(this.searchInput)) {
        this.validationError = 'Please enter a valid email address.';
        return;
      }
      this.payload.options.show = false;
      this.payload.callback(this.payload.options, this.searchInput);
      this.$emit('close');
    }
  },
  mounted: function mounted() {
    if (!this.payload.options.external) {
      this.fetchMailboxSearchResults();
    }
  }
});

/***/ }),
/* 277 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [
    !_vm.payload.options.isExternal
      ? _c("div", [
          _c("div", { staticClass: "form-label mb-2" }, [
            _vm._v("Search for a Mailbox")
          ]),
          _vm._v(" "),
          _c("div", { staticClass: "form-addon-wrapper" }, [
            _c("input", {
              directives: [
                {
                  name: "model",
                  rawName: "v-model",
                  value: _vm.searchInput,
                  expression: "searchInput"
                }
              ],
              staticClass: "form-addon-input",
              attrs: { type: "text", placeholder: "jon.doe@example.com" },
              domProps: { value: _vm.searchInput },
              on: {
                keydown: [
                  _vm.triggerFetch,
                  function($event) {
                    if (
                      !("button" in $event) &&
                      _vm._k($event.keyCode, "enter", 13, $event.key, "Enter")
                    ) {
                      return null
                    }
                    $event.preventDefault()
                    return _vm.fetchMailboxSearchResults($event)
                  }
                ],
                input: function($event) {
                  if ($event.target.composing) {
                    return
                  }
                  _vm.searchInput = $event.target.value
                }
              }
            }),
            _vm._v(" "),
            _c("div", { staticClass: "form-addon" }, [
              _c(
                "button",
                {
                  staticClass:
                    "text-grey hover:text-grey-darker focus:text-grey-darker",
                  on: {
                    click: function($event) {
                      $event.preventDefault()
                      return _vm.fetchMailboxSearchResults($event)
                    }
                  }
                },
                [_c("i", { staticClass: "fas fa-search" })]
              )
            ])
          ]),
          _vm._v(" "),
          _c(
            "div",
            {
              staticClass: "flex flex-row flex-wrap justify-center -mx-4 my-2"
            },
            [
              _c("label", { staticClass: "checkbox-label mx-4 my-1" }, [
                _vm._v("\n                Add to Senders\n                "),
                _c("input", {
                  directives: [
                    {
                      name: "model",
                      rawName: "v-model",
                      value: _vm.payload.options.isSender,
                      expression: "payload.options.isSender"
                    }
                  ],
                  attrs: { type: "checkbox" },
                  domProps: {
                    checked: Array.isArray(_vm.payload.options.isSender)
                      ? _vm._i(_vm.payload.options.isSender, null) > -1
                      : _vm.payload.options.isSender
                  },
                  on: {
                    change: function($event) {
                      var $$a = _vm.payload.options.isSender,
                        $$el = $event.target,
                        $$c = $$el.checked ? true : false
                      if (Array.isArray($$a)) {
                        var $$v = null,
                          $$i = _vm._i($$a, $$v)
                        if ($$el.checked) {
                          $$i < 0 &&
                            _vm.$set(
                              _vm.payload.options,
                              "isSender",
                              $$a.concat([$$v])
                            )
                        } else {
                          $$i > -1 &&
                            _vm.$set(
                              _vm.payload.options,
                              "isSender",
                              $$a.slice(0, $$i).concat($$a.slice($$i + 1))
                            )
                        }
                      } else {
                        _vm.$set(_vm.payload.options, "isSender", $$c)
                      }
                    }
                  }
                }),
                _vm._v(" "),
                _c("span", { staticClass: "checkmark" })
              ]),
              _vm._v(" "),
              _c("label", { staticClass: "checkbox-label mx-4 my-1" }, [
                _vm._v("\n                Add to Recipients\n                "),
                _c("input", {
                  directives: [
                    {
                      name: "model",
                      rawName: "v-model",
                      value: _vm.payload.options.isRecipient,
                      expression: "payload.options.isRecipient"
                    }
                  ],
                  attrs: { type: "checkbox" },
                  domProps: {
                    checked: Array.isArray(_vm.payload.options.isRecipient)
                      ? _vm._i(_vm.payload.options.isRecipient, null) > -1
                      : _vm.payload.options.isRecipient
                  },
                  on: {
                    change: function($event) {
                      var $$a = _vm.payload.options.isRecipient,
                        $$el = $event.target,
                        $$c = $$el.checked ? true : false
                      if (Array.isArray($$a)) {
                        var $$v = null,
                          $$i = _vm._i($$a, $$v)
                        if ($$el.checked) {
                          $$i < 0 &&
                            _vm.$set(
                              _vm.payload.options,
                              "isRecipient",
                              $$a.concat([$$v])
                            )
                        } else {
                          $$i > -1 &&
                            _vm.$set(
                              _vm.payload.options,
                              "isRecipient",
                              $$a.slice(0, $$i).concat($$a.slice($$i + 1))
                            )
                        }
                      } else {
                        _vm.$set(_vm.payload.options, "isRecipient", $$c)
                      }
                    }
                  }
                }),
                _vm._v(" "),
                _c("span", { staticClass: "checkmark" })
              ])
            ]
          ),
          _vm._v(" "),
          _c(
            "div",
            { staticClass: "my-2 text-grey-dark text-sm text-center italic" },
            [
              _vm._v(
                "\n            Please select one of the mailboxes below:\n        "
              )
            ]
          ),
          _vm._v(" "),
          _c(
            "div",
            { staticClass: "border-grey-lighter border rounded relative" },
            [
              !_vm.payload.options.isSender && !_vm.payload.options.isRecipient
                ? _c("div", {
                    staticClass:
                      "absolute pin-x pin-y cursor-not-allowed bg-grey-light opacity-25"
                  })
                : _vm._e(),
              _vm._v(" "),
              _c("div", { staticClass: "h-48 overflow-y-scroll" }, [
                _vm.mailboxSearchResponse
                  ? _c(
                      "div",
                      _vm._l(_vm.mailboxSearchResponse.data, function(mailbox) {
                        return _c(
                          "div",
                          {
                            staticClass:
                              "py-1 px-3 border-b border-grey-lightest"
                          },
                          [
                            _c(
                              "a",
                              {
                                staticClass:
                                  "block py-3 no-underline text-grey-darker hover:text-black group",
                                attrs: { href: "#" },
                                on: {
                                  click: function($event) {
                                    $event.preventDefault()
                                    _vm.addMailbox(mailbox)
                                  }
                                }
                              },
                              [
                                _c("i", {
                                  staticClass:
                                    "fas fa-inbox mr-2 text-grey group-hover:text-grey-darker"
                                }),
                                _vm._v(
                                  "\n                            " +
                                    _vm._s(mailbox.address) +
                                    "\n                        "
                                )
                              ]
                            )
                          ]
                        )
                      })
                    )
                  : _c(
                      "div",
                      {
                        staticClass:
                          "flex flex-col h-full items-center justify-center"
                      },
                      [
                        _c(
                          "div",
                          { staticClass: "text-center italic text-grey-dark" },
                          [
                            _vm._v(
                              "\n                        Loading...\n                    "
                            )
                          ]
                        )
                      ]
                    )
              ])
            ]
          )
        ])
      : _c("div", {}, [
          _c("div", { staticClass: "form-label mb-2" }, [
            _vm._v("Add External Recipient")
          ]),
          _vm._v(" "),
          _c(
            "div",
            {
              class: {
                "form-addon-wrapper": true,
                "border-red": _vm.validationError
              }
            },
            [
              _c("input", {
                directives: [
                  {
                    name: "model",
                    rawName: "v-model",
                    value: _vm.searchInput,
                    expression: "searchInput"
                  }
                ],
                staticClass: "form-addon-input",
                attrs: { type: "email", placeholder: "jon.doe@example.com" },
                domProps: { value: _vm.searchInput },
                on: {
                  keydown: function($event) {
                    if (
                      !("button" in $event) &&
                      _vm._k($event.keyCode, "enter", 13, $event.key, "Enter")
                    ) {
                      return null
                    }
                    $event.preventDefault()
                    return _vm.addExternalRecipient($event)
                  },
                  input: function($event) {
                    if ($event.target.composing) {
                      return
                    }
                    _vm.searchInput = $event.target.value
                  }
                }
              }),
              _vm._v(" "),
              _c("div", { staticClass: "form-addon" }, [
                _c(
                  "button",
                  {
                    staticClass:
                      "text-grey hover:text-grey-darker focus:text-grey-darker",
                    on: {
                      click: function($event) {
                        $event.preventDefault()
                        return _vm.addExternalRecipient($event)
                      }
                    }
                  },
                  [_c("i", { staticClass: "fas fa-plus" })]
                )
              ])
            ]
          ),
          _vm._v(" "),
          _vm.validationError
            ? _c("p", { staticClass: "form-help text-red mt-2" }, [
                _vm._v(_vm._s(_vm.validationError))
              ])
            : _vm._e(),
          _vm._v(" "),
          _c("p", { staticClass: "form-help mt-2" }, [
            _vm._v(
              "\n            Please specify the external address that should receive all incoming emails\n            for this alias.\n        "
            )
          ])
        ])
  ])
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-c535a0f2", module.exports)
  }
}

/***/ }),
/* 278 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(279)
/* template */
var __vue_template__ = __webpack_require__(280)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/integrations/NewIntegrationParameterForm.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-2aaf47e0", Component.options)
  } else {
    hotAPI.reload("data-v-2aaf47e0", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 279 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    payload: {
      type: Object,
      required: true
    }
  },
  data: function data() {
    return {
      inputOption: '',
      inputUseEqualSign: false,
      inputValue: ''
    };
  },

  computed: {
    previewString: function previewString() {
      var delimiter = this.inputUseEqualSign ? '=' : ' ';
      var option = this.inputOption.length > 0 ? this.inputOption + delimiter : '';
      return option + (this.inputValue.length > 0 ? '"' + this.inputValue + '"' : '');
    },
    availablePlaceholders: function availablePlaceholders() {
      return this.payload.availablePlaceholders;
    }
  },
  methods: {
    addParameter: function addParameter() {
      var parameter = {
        option: this.inputOption,
        value: this.inputValue,
        use_equal_sign: this.inputUseEqualSign
      };
      this.payload.callback(parameter);
      this.$emit('close');
    }
  }
});

/***/ }),
/* 280 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [
    _c("h2", { staticClass: "font-extrabold mb-6" }, [_vm._v("New Parameter")]),
    _vm._v(" "),
    _c("div", { staticClass: "mb-4" }, [
      _vm._v("\n            Preview:\n            "),
      _c("code", { staticClass: "inline-code leading-normal" }, [
        _vm._v(_vm._s(_vm.previewString.length > 1 ? _vm.previewString : " "))
      ])
    ]),
    _vm._v(" "),
    _c("div", { staticClass: "form-multi-row mb-3" }, [
      _c("div", { staticClass: "form-group sm:w-1/2" }, [
        _c("label", { staticClass: "mb-3 form-label" }, [_vm._v("Option")]),
        _vm._v(" "),
        _c("input", {
          directives: [
            {
              name: "model",
              rawName: "v-model",
              value: _vm.inputOption,
              expression: "inputOption"
            }
          ],
          staticClass: "mb-3 form-input",
          attrs: { type: "text", placeholder: "--option" },
          domProps: { value: _vm.inputOption },
          on: {
            input: function($event) {
              if ($event.target.composing) {
                return
              }
              _vm.inputOption = $event.target.value
            }
          }
        })
      ]),
      _vm._v(" "),
      _c("div", { staticClass: "form-group sm:w-1/2" }, [
        _c("label", { staticClass: "mb-3 form-label" }, [_vm._v("Value")]),
        _vm._v(" "),
        _c("input", {
          directives: [
            {
              name: "model",
              rawName: "v-model",
              value: _vm.inputValue,
              expression: "inputValue"
            }
          ],
          staticClass: "mb-3 form-input",
          attrs: { type: "text", placeholder: "value" },
          domProps: { value: _vm.inputValue },
          on: {
            input: function($event) {
              if ($event.target.composing) {
                return
              }
              _vm.inputValue = $event.target.value
            }
          }
        }),
        _vm._v(" "),
        _c("p", { staticClass: "form-help" }, [
          _vm._v("You may use placeholders in this field.")
        ])
      ])
    ]),
    _vm._v(" "),
    _c("div", { staticClass: "form-row" }, [
      _c("label", { staticClass: "checkbox-label" }, [
        _vm._v(
          "\n                Use equal sign between option and value\n                "
        ),
        _c("input", {
          directives: [
            {
              name: "model",
              rawName: "v-model",
              value: _vm.inputUseEqualSign,
              expression: "inputUseEqualSign"
            }
          ],
          attrs: { type: "checkbox" },
          domProps: {
            checked: Array.isArray(_vm.inputUseEqualSign)
              ? _vm._i(_vm.inputUseEqualSign, null) > -1
              : _vm.inputUseEqualSign
          },
          on: {
            change: function($event) {
              var $$a = _vm.inputUseEqualSign,
                $$el = $event.target,
                $$c = $$el.checked ? true : false
              if (Array.isArray($$a)) {
                var $$v = null,
                  $$i = _vm._i($$a, $$v)
                if ($$el.checked) {
                  $$i < 0 && (_vm.inputUseEqualSign = $$a.concat([$$v]))
                } else {
                  $$i > -1 &&
                    (_vm.inputUseEqualSign = $$a
                      .slice(0, $$i)
                      .concat($$a.slice($$i + 1)))
                }
              } else {
                _vm.inputUseEqualSign = $$c
              }
            }
          }
        }),
        _vm._v(" "),
        _c("span", { staticClass: "checkmark" })
      ])
    ]),
    _vm._v(" "),
    _c("div", { staticClass: "form-footer" }, [
      _c(
        "button",
        {
          staticClass: "btn btn-primary",
          on: {
            click: function($event) {
              $event.preventDefault()
              return _vm.addParameter($event)
            }
          }
        },
        [_vm._v("Add Parameter")]
      )
    ]),
    _vm._v(" "),
    _vm.availablePlaceholders != null
      ? _c(
          "div",
          {
            staticClass:
              "mt-6 border-t-2 border-grey-lighter pt-4 text-grey-darker"
          },
          [
            _c(
              "p",
              [
                _vm._v("Available Placeholders:\n            "),
                _vm._l(_vm.availablePlaceholders, function(placeholder) {
                  return _c("code", { staticClass: "inline-code mx-1 my-1" }, [
                    _vm._v(
                      "\n                " +
                        _vm._s(placeholder) +
                        "\n            "
                    )
                  ])
                })
              ],
              2
            )
          ]
        )
      : _vm._e()
  ])
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-2aaf47e0", module.exports)
  }
}

/***/ }),
/* 281 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "popup-modal",
    {
      class: {
        "max-w-md":
          _vm.modalContentPayload &&
          _vm.modalContentPayload.hasOwnProperty("modalWidthLarge")
      },
      on: {
        close: function($event) {
          _vm.$emit("close")
        }
      }
    },
    [
      _vm.modalContentIdentifier === "alias-mailbox-form"
        ? _c("alias-add-sender-recipient-form", {
            attrs: { payload: _vm.modalContentPayload },
            on: {
              close: function($event) {
                _vm.$emit("close")
              }
            }
          })
        : _vm._e(),
      _vm._v(" "),
      _vm.modalContentIdentifier === "new-integration-parameter-form"
        ? _c("new-integration-parameter-form", {
            attrs: { payload: _vm.modalContentPayload },
            on: {
              close: function($event) {
                _vm.$emit("close")
              }
            }
          })
        : _vm._e()
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-bbe7a158", module.exports)
  }
}

/***/ }),
/* 282 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(283)
}
var normalizeComponent = __webpack_require__(3)
/* script */
var __vue_script__ = __webpack_require__(285)
/* template */
var __vue_template__ = __webpack_require__(286)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-7e7f289a"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/assets/js/components/IndexSearch.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-7e7f289a", Component.options)
  } else {
    hotAPI.reload("data-v-7e7f289a", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 283 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(284);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(11)("2960524b", content, false, {});
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../node_modules/css-loader/index.js!../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"vue\":true,\"id\":\"data-v-7e7f289a\",\"scoped\":true,\"hasInlineConfig\":true}!../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./IndexSearch.vue", function() {
     var newContent = require("!!../../../../node_modules/css-loader/index.js!../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"vue\":true,\"id\":\"data-v-7e7f289a\",\"scoped\":true,\"hasInlineConfig\":true}!../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./IndexSearch.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 284 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(10)(false);
// imports


// module
exports.push([module.i, "\n.pin-t-100[data-v-7e7f289a] {\n  top: 100%;\n}\n", ""]);

// exports


/***/ }),
/* 285 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    apiUrl: {
      type: String,
      required: true
    },
    resultLinkUrlBase: {
      type: String,
      required: true
    },
    oldValue: {
      type: String
    },
    outputTextFunction: {
      type: Function,
      required: true
    },
    outputIdFunction: {
      type: Function,
      default: function _default(r) {
        return r.id;
      }
    },
    hiddenInputValues: {
      type: Array,
      default: function _default() {
        return [];
      }
    }
  },
  data: function data() {
    return {
      searchInput: '',
      hasFocus: false,
      hasHover: false,
      searchResults: null,
      hasMoreResults: false
    };
  },

  computed: {
    shouldShowSuggestions: function shouldShowSuggestions() {
      return this.hasFocus || this.hasHover;
    }
  },
  methods: {
    mouseOver: function mouseOver(bool) {
      console.log('mouse over ' + bool);
    },
    fetchApi: function fetchApi() {
      var _this = this;

      if (this.searchInput.length < 1) {
        return;
      }
      var params = {};
      this.hiddenInputValues.forEach(function (hidden) {
        return params[hidden.name] = hidden.value;
      });
      params.search = this.searchInput;
      axios.get(this.apiUrl, {
        params: params
      }).then(function (response) {
        _this.searchResults = response.data.data.slice(0, 4);
        _this.hasMoreResults = response.data.data.length > 4;
      }).catch(function (fail) {
        console.log(fail.response);
      });
    },

    triggerFetch: _.debounce(function () {
      this.fetchApi();
    }, 100),
    triggerLooseFocus: _.debounce(function () {
      this.hasFocus = false;
    }, 300)
  },
  created: function created() {
    if (this.oldValue) {
      this.searchInput = this.oldValue;
    }
  },

  watch: {
    searchInput: function searchInput() {
      if (this.searchInput.length < 1) {
        this.searchResults = null;
      }
    }
  }
});

/***/ }),
/* 286 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "relative" }, [
    _c(
      "form",
      { attrs: { method: "GET" } },
      [
        _vm._l(_vm.hiddenInputValues, function(hidden) {
          return _c("input", {
            attrs: { type: "hidden", name: hidden.name },
            domProps: { value: hidden.value }
          })
        }),
        _vm._v(" "),
        _c("div", { staticClass: "form-addon-wrapper" }, [
          _c("input", {
            directives: [
              {
                name: "model",
                rawName: "v-model",
                value: _vm.searchInput,
                expression: "searchInput"
              }
            ],
            staticClass: "form-addon-input py-2",
            attrs: { type: "search", name: "search", placeholder: "Search..." },
            domProps: { value: _vm.searchInput },
            on: {
              keydown: _vm.triggerFetch,
              focus: function($event) {
                _vm.hasFocus = true
              },
              blur: _vm.triggerLooseFocus,
              input: function($event) {
                if ($event.target.composing) {
                  return
                }
                _vm.searchInput = $event.target.value
              }
            }
          }),
          _vm._v(" "),
          _vm._m(0)
        ]),
        _vm._v(" "),
        _c("transition", { attrs: { name: "fade" } }, [
          _vm.searchResults &&
          _vm.searchResults.length > 0 &&
          _vm.shouldShowSuggestions
            ? _c("div", { staticClass: "absolute pin-x pin-t-100" }, [
                _c(
                  "div",
                  {
                    staticClass:
                      "overflow-y-scroll w-auto rounded max-w-full bg-white shadow-lg"
                  },
                  [
                    _c(
                      "div",
                      { staticClass: "flex flex-col w-auto overflow-hidden" },
                      [
                        _vm._l(_vm.searchResults, function(result) {
                          return _c(
                            "a",
                            {
                              staticClass:
                                "px-4 py-3 border-b border-grey-lighter text-grey-darker hover:text-black focus:text-black no-underline truncate",
                              attrs: {
                                href:
                                  _vm.resultLinkUrlBase +
                                  "/" +
                                  _vm.outputIdFunction(result),
                                title: _vm.outputTextFunction(result)
                              }
                            },
                            [_vm._v(_vm._s(_vm.outputTextFunction(result)))]
                          )
                        }),
                        _vm._v(" "),
                        _vm.hasMoreResults
                          ? _c(
                              "button",
                              {
                                staticClass:
                                  "text-center bg-grey-lightest text-sm px-4 py-2 text-grey-darker hover:text-black focus:text-black",
                                attrs: { type: "submit" }
                              },
                              [
                                _vm._v(
                                  "\n                            See all results\n                        "
                                )
                              ]
                            )
                          : _vm._e()
                      ],
                      2
                    )
                  ]
                )
              ])
            : _vm._e()
        ])
      ],
      2
    )
  ])
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "form-addon py-2" }, [
      _c(
        "button",
        {
          staticClass:
            "text-grey-dark hover:text-grey-darkest focus:text-grey-darkest",
          attrs: { type: "submit" }
        },
        [_c("i", { staticClass: "fas fa-search", attrs: { title: "Search" } })]
      )
    ])
  }
]
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-7e7f289a", module.exports)
  }
}

/***/ }),
/* 287 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ })
],[155]);