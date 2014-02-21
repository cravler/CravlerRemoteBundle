
(function(window) {
    "use strict";

    var createScript = function(src, onload) {
        var headTag = window.document.getElementsByTagName("head")[0];
        var scriptTag = window.document.createElement('script');
        scriptTag.type = 'text/javascript';
        scriptTag.src = src;
        scriptTag.onload = onload;
        headTag.appendChild(scriptTag);
    };

    var initScripts = function(scripts, fn) {
        var script = scripts.shift();
        if (script.type == 'undefined') {
            createScript(script.url, function() {
                if (scripts.length > 0) {
                    initScripts(scripts, fn);
                } else {
                    fn();
                }
            });
        }
    };

    var createEndpoint = function(ename, primus) {
        var parts = ename.split('.');
        var method = parts.pop();
        var endpoint = jUtil.ns(parts.join('.'));
        endpoint[method] = function() {
            var args = Array.prototype.slice.call(arguments);
            args.unshift(ename)
            primus.send.apply(primus, args);
        };
    };

    var initPrimus = function(params, onEndpoints, onMessage) {
        var getToken = params['token'] || function(data, callback) {
            throw new Error('Token callback must be defined!');
        };

        var primus = Primus.connect(params['url'], params['options'] || {
            transformer: 'sockjs', parser: 'JSON',
            strategy: [ 'online', 'timeout', 'diScoNNect' ],
            network: true,
            reconnect: {
                maxDelay: Infinity, // Number: The max delay for a reconnect retry.
                minDelay: 300, // Number: The minimum delay before we reconnect.
                retries: Infinity // Number: How many times should we attempt to reconnect.
            }
        });
        primus.on('error', function (err) {
            console.error('Error', err, err.message);
        });
        primus.on('reconnect', function () {
            console.log('Reconnect attempt started');
        });
        primus.on('reconnecting', function (opts) {
            console.log('Reconnecting in %d ms', opts.timeout);
            console.log('This is attempt %d out of %d', opts.attempt, opts.retries);
        });
        primus.on('end', function () {
            console.log('Connection closed');
        });
        primus.on('open', function() {
            primus.on('message', function(message) {
                onMessage(message);
            });
            primus.on('endpoints', function(endpoints) {
                for (var i in endpoints) {
                    var ename = endpoints[i];
                    createEndpoint(ename, primus);
                }
                onEndpoints(endpoints);
            });

            primus.send('join', params['rooms']);
            primus.send('init', params['session'], function(client) {
                getToken(client, function(token) {
                    primus.send('authorize', token);
                });
            });
        });
    };

    if (typeof window === "object" && typeof window.document === "object") {
        window.CravlerRemote = new function() {
            var endpoints = [], events = {}, promise;

            promise = jUtil.createPromise(this, 'endpoints');
            promise.then(function(data) {
                endpoints = data;
            });

            this.init = function(params) {
                initScripts([
                    { type: typeof SockJS, url: 'http://cdn.sockjs.org/sockjs-0.3.min.js' },
                    { type: typeof Primus, url: params['url'] + '/primus/primus.js' }
                ], function () {
                    initPrimus(params, function(endpoints) {
                        try {
                            promise.resolve(endpoints);
                        } catch (e) {
                            console.info(e.message);
                        }
                    }, function(message) {
                        var ce;
                        if (ce = events[message['type']] || false) {
                            ce.fire(message);
                        }
                        if (ce = events[message['type'] + '::' + message['name']] || false) {
                            ce.fire(message);
                        }
                    });
                });
            };

            this.endpointExists = function(ename) {
                return endpoints.indexOf(ename) !== -1;
            };

            this.endpointsReady = function(fn) {
                promise.then(fn);
            };

            this.onMessage = function(eventName, fn, scope) {
                eventName = eventName.toLowerCase();
                var ce = events[eventName] || true;
                if (typeof ce === 'boolean') {
                    events[eventName] = ce = jUtil.createEvent(this, eventName);
                }
                ce.addListener(fn, scope);
            };
        };
    }

})(window);
