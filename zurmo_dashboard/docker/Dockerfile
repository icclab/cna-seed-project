FROM ubuntu:14.04

ENV DEBIAN_FRONTEND noninteractive
RUN apt-get update && \
    apt-get -yq upgrade && \
    apt-get -yq install \
	  nodejs \
	  nodejs-legacy \
	  npm && \
    npm install socket.io && \
    npm install xml2json

ADD dashboard /dashboard

EXPOSE 8000
CMD nodejs /dashboard/server.js