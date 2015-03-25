#!/bin/sh 
find src/ -type d -name ".svn" | xargs rm -rf;
find . -type f -name "diff" | xargs rm -rf;
scp -r * lichengyin@www.qiwoo.org:/home/q/system/www/qiwu/beautify/vender/Fl;
scp -r * lichengyin@www.qiwoo.org:/home/q/system/www/qiwu/compressor/vender/Fl;
scp -r * welefen@www.welefen.com:/home/welefen/www/www.flkit.org/;
