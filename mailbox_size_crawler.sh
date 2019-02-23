#!/bin/bash
# MUM - Mailserver User Management
# Mailbox Size Crawler
# v0.1.1 / Author: Martin Bock

if [[ $# != 2 ]]; then
    >&2 echo "Usage: $0 </path/to/dovecot/home/root> </path/to/mum/artisan>"
    >&2 echo "Example: $0 /var/mail/mailboxes /var/www/mum/artisan"
    exit 1
fi

if [ ! -d $1 ]; then
    >&2 echo "Path to Dovecot's home root directory is invalid."
    exit 1
fi

if [ ! -f $2 ]; then
    >&2 echo "Path to MUM's artisan file is invalid."
    exit 1
fi

du -d 2 $1 |
while read size name
do
    sudo -u www-data /usr/bin/php $2 size-measurements:report ${name} ${size}
done