#!/bin/bash

while true
do
  printf '%s' "executing... "
  start=`date +%s`;
  php $(pwd)/app.php
  end=`date +%s`;
  diff=$(expr $end - $start)
  printf '%s' "executed in $diff seconds"
  echo ""
  sleep 60

done
