ETH-RLY API
====

Send signals to a connected ETH RLY devices.

API
---

See public methods in `src/Ethrly8.php`.

Versions
---

ETH RLY devices exist in 2 versions/firmwares. Use `Ethrly8` for v1, and `Ethrly20` for v2.

Demo
---

Use the scripts in `node/` to run a limited ETH RLY server with NodeJS.

	$ node node/ethrly8 17493
	$ node node/ethrly20 17494

To do
---

* Password support for fake server
