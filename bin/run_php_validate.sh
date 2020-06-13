#!/bin/bash
./vendor/bin/phpcs --standard=phpcs.xml -d memory_limit=512M -s -n -p
