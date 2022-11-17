#!/bin/bash
service nginx status | grep 'active (running)' > /dev/null 2>&1 if [ $? != 0 ] then sudo service nginx restart > /dev/null fi