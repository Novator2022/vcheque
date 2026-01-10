#!/bin/bash

FILE=/var/www/html/vcheque.bazatoday.com/reboot_queue.lock
if [[ -f "$FILE" ]]; then
	supervisorctl restart vcheque
    rm "$FILE"
fi