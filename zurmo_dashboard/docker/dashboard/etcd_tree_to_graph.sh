#!/bin/bash
input=$1
mc=`grep -oE "cache/.*(\/)" $input | uniq | wc -l`
as=`grep -oE "webserver/.*(\/)" $input | uniq | wc -l`

echo "digraph G {"
for i in $(seq 1 $as); do 
    echo "HAProxy -> Zurmo$i;"
    echo "Zurmo$i -> MySQL;"
    for j in $(seq 1 $mc); do 
    echo "Zurmo$i -> Memcached$j;"
    echo "Memcached$j -> MySQL;"
done

done
echo "}"

#digraph G {

#lb -> as1;
#lb -> as2;
#as1 -> mc1;
#as1 -> mc2;
#as2 -> mc1;
#as2 -> mc2;
#mc1 -> db;
#mc2 -> db;
#as1 -> db;
#as2 -> db;

#}

