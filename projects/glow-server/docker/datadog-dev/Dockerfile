FROM datadog/agent:7

ARG DD_CONTAINER_EXCLUDE
ENV DD_CONTAINER_EXCLUDE=${DD_CONTAINER_EXCLUDE}
ARG DD_CONTAINER_INCLUDE
ENV DD_CONTAINER_INCLUDE=${DD_CONTAINER_INCLUDE}
ARG DD_REDIS_HOST
ARG DD_REDIS_PORT

COPY ./datadog.yaml /etc/datadog-agent/datadog.yaml
COPY ./redisdb.yaml /etc/datadog-agent/conf.d/redisdb.yaml

RUN sed -i -e "s/DD_REDIS_HOST/${DD_REDIS_HOST}/g" /etc/datadog-agent/conf.d/redisdb.yaml
RUN sed -i -e "s/DD_REDIS_PORT/${DD_REDIS_PORT}/g" /etc/datadog-agent/conf.d/redisdb.yaml
