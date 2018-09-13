#!/bin/bash

str="1417,1418,1419,1420,1424,1426,1427,1429,1430,1432,1433,1403,1415,1421,1431,1678,1673,1677,1676,1675,1674,1683,1684,1685,1686,1687"

ids=$(echo $str | tr "," "\n")

for addr in $ids
do
    echo "Run over $addr"
    ./extractRateCurrents.py $addr
done
