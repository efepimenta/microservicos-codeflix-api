#!/usr/bin/env bash

docker-compose up -d
sleep 5
docker-compose exec app bash
