#!/bin/bash

# sftp -P 2022 $UNAME@1.studio.boardgamearena.com

lftp -u $UNAME sftp://1.studio.boardgamearena.com:2022 -e "set sftp:connect-program 'ssh -a -x -i $PATH_TO_SSH';"

# mirror -R --include '(img|modules|^calypso.*|dbmodel.sql|.*\.php|game.*/.json)' --exclude '^(\.git|\.env|\.vscode)(/|$)' . /calypso 
