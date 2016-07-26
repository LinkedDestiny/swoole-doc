#!/usr/bin/env bash

ps aux | grep simple_route_master | awk  '{print $2}' | xargs kill -USR1