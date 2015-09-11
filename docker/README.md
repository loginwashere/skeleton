# Bluz multy container docker setup

Previously I've tried to use docker to setup one container environment and in this one I've tried to split it into several containers.

# Usage

In order to use it you need docker installed. More info can be found [here](http://docs.docker.com/mac/started/)
Also you need docker-compose installed. More info can be found [here](https://docs.docker.com/compose/install/)

If you have docker and docker-compose installed then run `docker-compose -f docker/` in this directory.
Instead of image name you can use something else.

After image has been built you can tun it using command `docker run -p 80:80 loginwashere/bluz-one-dockerfile`.

Now you can access skeleton in your browser on this url - http://localhost/ .
