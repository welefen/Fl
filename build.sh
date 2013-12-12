#!/bin/sh 
find src/ -type d -name ".svn" | xargs rm -rf;
find . -type f -name "diff" | xargs rm -rf;
exit 0;
scp -r * lichengyin@10.16.15.155:/home/lichengyin/htdocs/compressor/vender/Fl/;
scp -r * lichengyin@10.16.15.155:/home/lichengyin/htdocs/beautify/vender/Fl/;
scp -r * welefen@www.kitgram.com:/home/welefen/www/www.flkit.org/;
