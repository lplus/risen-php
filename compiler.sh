#!/bin/sh

# compiler for risen-php
# author: riki

file=$1

enable_trace=$2
class_alias_file=build/risen/trace/class_alias.php


if [ $enable_trace = true ]; then
	if [ ! -f $class_alias_file ]; then
		mkdir -p $class_alias_file
		rmdir $class_alias_file
		echo '<?php' > $class_alias_file
	fi
fi

echo compile $file for release version
dir=${file//src/build}
if [ ! -f $dir ]; then
	mkdir -p $dir
	rmdir $dir
fi

sed '/^#trace/,/^#endtrace/d;s/<?php #release/<?php/' $file > $dir 
firstline=`head -1 $file`
if [ "$firstline" == '<?php #release' ]; then exit 0; fi

if [ $enable_trace = true ]; then
	echo compile $file for trace version 
	dir=build/${file//src\/risen/"risen/trace"}
	if [ ! -f $dir ]; then
		mkdir -p $dir
		rmdir $dir
	fi
	sed 's/^namespace risen/namespace risen\\trace/;/^#/d' $file > $dir
	class=${file:4: ${#file} - 8}
	class=${class//\//'\'} 
	echo "class_alias('"${class//risen/'risen\\trace'}"', '$class');" >> $class_alias_file
fi

