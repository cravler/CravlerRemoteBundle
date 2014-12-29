
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

    var createEndpointMethod = function(endpoint, method, primus) {
        var _endpoint = jUtil.ns('Endpoints.' + endpoint);
        _endpoint[method] = function() {
            var args = Array.prototype.slice.call(arguments);
            args.unshift(endpoint + '.' + method);
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
                for (var endpoint in endpoints) {
                    var methods = endpoints[endpoint];
                    for (var i in methods) {
                        createEndpointMethod(endpoint, methods[i], primus);
                    }
                }
                onEndpoints(endpoints);
            });

            if (typeof params['rooms'] == 'function') {
                var getRooms = params['rooms'];
                getRooms(function(rooms) {
                    primus.send('join',rooms);
                });
            } else {
                primus.send('join', params['rooms']);
            }

            primus.send('init', params['session'], function(client) {
                getToken(client, function(token) {
                    primus.send('authorize', token);
                });
            });
        });
    };

    if (typeof window === "object" && typeof window.document === "object") {
        window.CravlerRemote = new function() {
            var events = {}, promise;

            promise = jUtil.createPromise(this, 'endpoints');

            this.init = function(params) {
                initScripts([
                    { type: typeof SockJS, url: 'http://cdn.jsdelivr.net/sockjs/0.3.4/sockjs.min.js' },
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

            this.endpointsReady = function(fn) {
                promise.then(fn);
            };

            this.getEndpoint = function(endpoint, fn) {
                this.endpointsReady(function(endpoints) {
                    if (typeof endpoints[endpoint] !== 'undefined') {
                        fn(jUtil.ns('Endpoints.' + endpoint));
                    } else {
                        fn(null);
                    }
                });
            };

            this.invoke = function() {
                var args = Array.prototype.slice.call(arguments);
                var ename = args.shift();
                var parts = ename.split('.');
                this.getEndpoint(parts[0], function(endpoint) {
                    if (endpoint) {
                        if (typeof endpoint[parts[1]] == 'function') {
                            var method = endpoint[parts[1]];
                            method.apply(method, args);
                        } else {
                            console.error('Method "' + ename + '" not defined!');
                        }
                    } else {
                        console.error('Endpoint "' + parts[0] + '" not defined!');
                    }
                });
            };

            this.onMessage = function(eventName, fn, scope) {
                eventName = eventName.toLowerCase();
                var ce = events[eventName] || true;
                if (typeof ce === 'boolean') {
                    events[eventName] = ce = jUtil.createEvent(this, eventName);
                }
                ce.addListener(fn, scope);
            };

            this.offMessage = function(eventName) {
                eventName = eventName.toLowerCase();
                var ce = events[eventName] || true;
                if (typeof ce !== 'boolean') {
                    ce.clearListeners();
                }
            };
        };
    }

})(window);
