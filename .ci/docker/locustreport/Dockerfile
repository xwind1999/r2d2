FROM python:3.5-alpine

RUN apk add --update curl gcc g++ \
    && rm -rf /var/cache/apk/*

RUN ln -s /usr/include/locale.h /usr/include/xlocale.h

RUN pip install bottle numpy cython pandas

RUN pip install plotly==4.8.1