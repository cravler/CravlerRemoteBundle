
var window = window || {};

(function(undefined) {
    "use strict";

    var jUtil = {};

    /**
     * @param v
     * @returns {boolean}
     */
    jUtil.isObject = function(v) {
        return !!v && Object.prototype.toString.call(v) === '[object Object]';
    };

    /**
     * @type {isArray|jQuery.isArray|Function|exports.isArray|utils.isArray|b.isArray|*}
     */
    jUtil.isArray = Array.isArray || function(v) {
        return !!v && Object.prototype.toString.call(v) === '[object Array]';
    };

    /**
     * @param v
     * @returns {boolean}
     */
    jUtil.isNumber = function(v) {
        return typeof v === 'number' && isFinite(v);
    };

    /**
     * @param v
     * @param allowBlank
     * @returns {boolean}
     */
    jUtil.isEmpty = function(v, allowBlank) {
        return v === null
            || v === undefined
            || ((jUtil.isArray(v) && !v.length))
            || (!allowBlank ? v === '' : false);
    };

    /**
     * @param v
     * @returns {boolean}
     */
    jUtil.isDefined = function(v) {
        return typeof v !== 'undefined';
    };

    /**
     * @type {namespace}
     */
    jUtil.ns = jUtil.namespace = function() {
        var o, d;
        for (var i = 0; i < arguments.length; i++) {
            d = arguments[i].split('.');
            o = window[d[0]] = window[d[0]] || {};
            var a = d.slice(1);
            for (var j = 0; j < a.length; j++) {
                o = o[a[j]] = o[a[j]] || {};
            };
        }
        return o;
    };

    /**
     * @param obj
     * @param name
     * @constructor
     */
    var Promise = function(obj, name) {
        this.name = name;
        this.obj = obj;
        this.callbacks = [];
        this.resolved = undefined;
    };
    Promise.prototype = {
        then: function(fn, scope) {
            if (this.resolved !== undefined) {
                fn.apply(scope || this.obj || window, this.resolved);
            } else {
                this.callbacks.push({
                    fn: fn,
                    scope: scope
                });
            }
        },

        resolve: function() {
            if (this.resolved) {
                throw new Error('Promise "' + this.name + '" already resolved');
            }

            this.resolved = arguments;

            for (var i in this.callbacks) {
                var c = this.callbacks[i];
                c.fn.apply(c.scope || this.obj || window, this.resolved);
            }
        }
    };
    /**
     * @param obj
     * @param name
     * @returns {Promise}
     */
    jUtil.createPromise = function(obj, name) {
        return new Promise(obj, name);
    };

    /**
     * @param obj
     * @param name
     * @constructor
     */
    var Event = function(obj, name) {
        this.name = name;
        this.obj = obj;
        this.listeners = [];
    };
    Event.prototype = {
        addListener: function(fn, scope) {
            var me = this, l;
            scope = scope || me.obj;
            if (!me.isListening(fn, scope)) {
                l = me.createListener(fn, scope);
                if (me.firing) {
                    // if we are currently firing this event, don't disturb the listener loop
                    me.listeners = me.listeners.slice(0);
                }
                me.listeners.push(l);
            }
        },

        createListener: function(fn, scope) {
            scope = scope || this.obj;
            return {
                fn: fn,
                scope: scope
            };
        },

        isListening: function(fn, scope) {
            return this.findListener(fn, scope) != -1;
        },

        findListener: function(fn, scope) {
            var list = this.listeners, i = list.length, l;

            scope = scope || this.obj;
            while (i--) {
                l = list[i];
                if (l) {
                    if (l.fn == fn && l.scope == scope) {
                        return i;
                    }
                }
            }
            return -1;
        },

        removeListener: function(fn, scope) {
            var index, l, k, me = this, ret = false;
            if ((index = me.findListener(fn, scope)) != -1) {
                if (me.firing) {
                    me.listeners = me.listeners.slice(0);
                }
                me.listeners.splice(index, 1);
                ret = true;
            }
            return ret;
        },

        // Iterate to stop any buffered/delayed events
        clearListeners: function() {
            var me = this, l = me.listeners, i = l.length;
            while (i--) {
                me.removeListener(l[i].fn, l[i].scope);
            }
        },

        fire: function() {
            var me = this,
                args = Array.prototype.slice.call(arguments),
                listeners = me.listeners,
                len = listeners.length,
                i = 0,
                l;

            if (len > 0) {
                me.firing = true;
                for (; i < len; i++) {
                    l = listeners[i];
                    if (l && l.fn.apply(l.scope || me.obj || window, args) === false) {
                        return (me.firing = false);
                    }
                }
            }
            me.firing = false;
            return true;
        }
    };
    /**
     * @param obj
     * @param name
     * @returns {Event}
     */
    jUtil.createEvent = function(obj, name) {
        return new Event(obj, name);
    };

    if (typeof window === "object" && typeof window.document === "object") {
        window.jUtil = jUtil;
    } else if( typeof module !== "undefined" && ('exports' in module)) {
        module.exports = jUtil;
    }

})();