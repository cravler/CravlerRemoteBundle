var http = require('http'),
    express = require('express'),
    Primus = require('primus'),
    upnode = require('upnode'),
    crypto = require('crypto'),
    fs = require('fs');

var env = process.env.NODE_ENV || 'development';
var appPort = process.env.NODE_APP_PORT || 8080;
var remotePort = process.env.NODE_REMOTE_PORT || 8081;
var serverPort = process.env.NODE_SERVER_PORT || 8082;
var secret = process.env.NODE_SECRET || 'ThisTokenIsNotSoSecretChangeIt';

var up = null;
var authToken = null;
var app = express();
var server = http.createServer(app);

var primus = new Primus(server, { transformer: 'sockjs', parser: 'JSON' });
//primus.use('multiplex', 'primus-multiplex');
primus.use('emitter', 'primus-emitter');
primus.use('rooms', 'primus-rooms');

server.listen(appPort);
console.log('App started:');
console.log('    app-port: ' + appPort);
console.log('    remote-port: ' + remotePort);
console.log('    server-port: ' + serverPort);
console.log('    secret: ' + secret);

/////////////////////////////////////////////////////////////////////////////////////////////////////

var createHash = function(v) {
    return crypto.createHash('md5').update(v).digest('hex');
};

var createRemoteKey = function(sparkId, sessionId) {
    return {
        id: sparkId,
        session: sessionId,
        hash: createHash([sparkId, secret, sessionId].join(';'))
    }
};

var assignListeners = function(ename, spark) {
    spark.on(ename, function() {
        var args = arguments;
        up(function(remote) {
            remote['call'](authToken, spark.storage.remoteKey, ename, args);
        });
    });
};

var combineFiles = function(files, cb, content) {
    var file = files.shift();
    content = content || '';
    fs.readFile(file, function(error, _content) {
        if (error) {
            console.log(error);
        } else {
            content += _content;
            if (files.length > 0) {
                combineFiles(files, cb, content);
            } else {
                cb(content);
            }
        }
    });
};

/////////////////////////////////////////////////////////////////////////////////////////////////////

combineFiles([

    __dirname + '/util.js',
    __dirname + '/browser.js'

], function(content) {
    if ('development' == env) {
        app.get('/browser.js', function (req, res) {
            res.writeHead(200, {"Content-Type": "text/javascript"});
            res.write(content);
            res.end();
        });
    } else {
        fs.writeFile(__dirname + '/combined.js', content, function(err) {
            if(err) {
                console.log(err);
            } else {
                app.get('/browser.js', function (req, res) {
                    res.sendfile(__dirname + '/combined.js');
                });
            }
        });
    }
});

/////////////////////////////////////////////////////////////////////////////////////////////////////

var disconnections = Object.create(null);
var api = {
    auth: function(id, cb) {
        authToken = {
            id: id,
            hash: createHash([secret, id].join(';'))
        };
        cb(authToken);
    },
    wait: function(cb, seconds) {
        setTimeout(cb, seconds * 1000);
    },
    userToken: function(remoteKey, cb) {
        var userToken = null;
        if (JSON.stringify(remoteKey) === JSON.stringify(createRemoteKey(remoteKey.id, remoteKey.session))) {
            try {
                if (disconnections[remoteKey.id]) {
                    var sparkStorage = disconnections[remoteKey.id];
                    if (JSON.stringify(remoteKey) === JSON.stringify(sparkStorage.remoteKey)) {
                        userToken = sparkStorage.userToken;
                        throw {};
                    }
                }

                primus.forEach(function(spark, id, connections) {
                    if (
                        id == remoteKey.id
                        && JSON.stringify(remoteKey) === JSON.stringify(spark.storage.remoteKey)
                        ) {
                        userToken = spark.storage.userToken;
                        throw {};
                    }
                });
            } catch(e) {}
        }

        cb(userToken);
    },
    joinRoom: function(remoteKey, room) {
        if (JSON.stringify(remoteKey) === JSON.stringify(createRemoteKey(remoteKey.id, remoteKey.session))) {
            try {
                primus.forEach(function (spark, id, connections) {
                    if (
                        id == remoteKey.id
                            && JSON.stringify(remoteKey) === JSON.stringify(spark.storage.remoteKey)
                        ) {
                        spark.join(room, function() {
                            //console.log(spark.id + ' joined room ' + room);
                        });
                        throw {};
                    }
                });
            } catch(e) {}
        }
    },
    dispatch: function(room, obj) {
        if (!obj) {
            primus.send('message', room);
        } else {
            primus.room(room).send('message', obj);
        }
    }
};
up = upnode(api).connect(serverPort);

if (remotePort) {
    upnode(function(client, conn) {
        this.dispatch = api.dispatch;
    }).listen(remotePort);
}

primus.on('connection', function(spark) {

    spark.storage = {
        session: null,
        userToken: null,
        remoteKey: null
    };

    spark.on('init', function(session, callback) {
        spark.storage.session = session;
        spark.storage.remoteKey = createRemoteKey(spark.id, spark.storage.session);
        callback(spark.storage.remoteKey);
    });

    spark.on('join', function(data) {
        if (data && data['ids'] && data['hash']) {
            var hash = createHash(data.ids.join(';') + ';' + secret);
            if (hash == data['hash']) {
                for (var i in data.ids) {
                    var room = data.ids[i];
                    spark.join(room, function() {
                        //console.log(spark.id + ' joined room ' + room);
                    });
                }
            }
        }
    });

    spark.on('authorize', function(token) {
        if (token && token['remoteKey']) {
            if (
                spark.id === token['remoteKey']['id']
             && spark.storage.session === token['remoteKey']['session']
            ) {
                if (JSON.stringify(spark.storage.remoteKey) === JSON.stringify(token['remoteKey'])) {
                    spark.storage.userToken = token;

                    up(function(remote) {
                        remote.handle(authToken, spark.storage.remoteKey, 'connect');

                        remote.endpoints(authToken, spark.storage.remoteKey, function(endpoints) {
                            for (var endpoint in endpoints) {
                                var methods = endpoints[endpoint];
                                for (var i in methods) {
                                    var ename = endpoint + '.' + methods[i];
                                    assignListeners(ename, spark);
                                }
                            }

                            spark.send('endpoints', endpoints);
                        });
                    });
                }
            }
        }
    });
});

primus.on('disconnection', function(spark) {
    if (spark.storage.remoteKey && spark.storage.userToken) {
        var sparkId = spark.id;
        disconnections[sparkId] = {
            session: spark.storage.session,
            userToken: spark.storage.userToken,
            remoteKey: spark.storage.remoteKey
        };
        up(function(remote) {
            remote.handle(authToken, spark.storage.remoteKey, 'disconnect', function() {
                delete disconnections[sparkId];
            });
        });
    }
});