FROM node:20-alpine

RUN apk add --no-cache bash

WORKDIR /app

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN chown -R node:node /app
USER node

EXPOSE 5173

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
