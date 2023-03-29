#!/bin/bash

while true
do
  php $(pwd)/app.php --preset=prio
  sleep 60
done
