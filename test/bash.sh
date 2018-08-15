#!/bin/bash

RESTORE=$(echo -en '\033[0m')
RED=$(echo -en '\033[00;31m')
GREEN=$(echo -en '\033[00;32m')
YELLOW=$(echo -en '\033[00;33m')
BLUE=$(echo -en '\033[00;34m')
MAGENTA=$(echo -en '\033[00;35m')
PURPLE=$(echo -en '\033[00;35m')
CYAN=$(echo -en '\033[00;36m')
LIGHTGRAY=$(echo -en '\033[00;37m')
LRED=$(echo -en '\033[01;31m')
LGREEN=$(echo -en '\033[01;32m')
LYELLOW=$(echo -en '\033[01;33m')
LBLUE=$(echo -en '\033[01;34m')
LMAGENTA=$(echo -en '\033[01;35m')
LPURPLE=$(echo -en '\033[01;35m')
LCYAN=$(echo -en '\033[01;36m')
WHITE=$(echo -en '\033[01;37m')
cliread=$1
if [ "$cliread" = "-y" ]; then
  uc="y"
else
  echo "${RED}Warning!! when run this test, remove your all data!"
  echo "${YELLOW}Are you ready for continue? (y/n)";
  read uc
fi

if [ "$uc" = "y" ]; then
  if [[ -z "${IS_TRAVIS_CI}" ]]; then
    echo "It's not travis CI"
  else
    echo "It's travis CI"
    #mysql problem
    #echo '{"type":"mysql","socket":"/var/run/mysqld/mysqld.sock","username":"root","password":""}' > /home/travis/build/lastfw/udms/addons/mysql/test/tc.jso
  fi
  echo '#!/bin/bash' > src/lexe.sh
  php -f ./src/run.php > src/bexe.sh
  ./src/lexe.sh
  d=`cat ./src/lexe.sh`
  d=`echo "$d" | tail -n1`
  d1=${d: 0:4}
  d2=${d: -1}
  if [ "$d1" = "exit" ]; then
    if [ "$d2" = "0" ]; then
      ecode="0"
    else
      ecode="$d2"
    fi
  else
    ecode="1"
  fi
  if [ "$ecode" != "0" ]; then
    d=`cat ./src/bexe.sh`
    echo "${RED}$d"
  fi
  echo '' > src/bexe.sh
  echo '' > src/lexe.sh
else
  echo "${WHITE}exit";
  ecode="1"
fi
exit $(($ecode + 0))
