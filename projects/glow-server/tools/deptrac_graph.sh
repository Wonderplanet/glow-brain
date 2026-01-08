#!/bin/bash

cd $(dirname $0)

./bin/sail-wp deptrac --formatter=graphviz-image --output=deptrac/layer/graph.png --config-file=deptrac.yaml
./bin/sail-wp deptrac --formatter=graphviz-image --output=deptrac/module/graph.png --config-file=deptrac.module.yaml
