
var ports0 = process.argv.slice(2);
if ( !ports0.length ) {
	console.log('\nNeed port(s).\n\n');
	process.exit();
}

var ports = [];
for (var i=0; i<ports0.length; i++) {
	var port = parseInt(ports0[i]);
	if (isNaN(port)) {
		console.log('\nPorts must be ints.\n\n');
		process.exit();
	}

	ports.push(port);
}

var net = require('net');
ports.forEach(function(port) {
	var status = [0, 0, 0, 0, 0, 0, 0, 0];

	net.createServer(function(socket) {

		console.log('\nNew connection...');
		// socket.setEncoding('utf8');

		function writeStatus() {
			var dec = 0;
			status.forEach(function(on, i) {
				dec += on * Math.pow(2, i);
			});

			console.log('Writing status: ' + dec);
			var buffer = new Buffer(1);
			buffer[0] = dec;
			socket.write(buffer);
		}

		socket.on('error', function(e) {
			console.log(e);
		});

		socket.on('data', function(buffer) {
			var data = [...buffer];
			var cmd = data[0];
			console.log('Incoming data...', data);

			if ( cmd == 36 ) {
				// get status
			}

			else if ( cmd == 35 ) {
				// set all
				var all = data[1];
				status.forEach(function(on, i) {
					var check = Math.pow(2, i);
					status[i] = (all & check) == check ? 1 : 0;
				});
			}

			else if ( cmd == 32 ) {
				// one on
				var relay = data[1];
				var relayIndex = relay - 1;
				status[relayIndex] = 1;
			}
			else if ( cmd == 33 ) {
				// one off
				var relay = data[1];
				var relayIndex = relay - 1;
				status[relayIndex] = 0;
			}

			console.log('New status:', status.join(''));
			writeStatus();
		});

	}).listen(port);

	console.log('Listening op port ' + port);

});
