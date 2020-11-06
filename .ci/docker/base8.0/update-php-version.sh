input=sudo find composer.json -type f -print0 | xargs -0 sed -i -e 's/\"php\": \"\^7.4.1\"/\"php\": \"\^8.0\"/';
