#!/bin/bash

while true
do
  php $(pwd)/app.php --preset=lame
  sleep 60
done
