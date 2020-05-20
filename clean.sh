#!/usr/bin/env bash

php bin/console cac:cl --env=prod && chmod -R 777 ./var && chmod -R 777 ./web/reports