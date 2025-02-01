# Base image for building the app
FROM node:20.16.0-alpine AS base

WORKDIR /app

# Install build dependencies
RUN apk add --no-cache python3 make g++

# Include dev dependencies
COPY package*.json ./
#COPY frontend/package*.json ./
#RUN npm cache clean --force && \
#    rm -rf node_modules package-lock.json && \
#    npm install
RUN npm install

# Install TypeScript (devDependencies)
RUN npm install typescript --save-dev

# Copy application files
COPY . .

# Development stage
FROM base AS development
EXPOSE 5173
CMD ["npm", "run", "dev", "--", "--host"]
#Adding the --host flag binds the server to 0.0.0.0, making it accessible from your local machine and other services in your Docker network.

# Build stage for production
FROM base AS build
RUN npm run build

# Production stage
FROM nginx:alpine AS production
WORKDIR /usr/share/nginx/html
COPY --from=build /app/dist .

# Copy Nginx configuration from the correct location
#COPY frontend/nginx/nginx.conf.template /etc/nginx/conf.d/default.conf
COPY nginx/nginx.conf.template /etc/nginx/conf.d/default.conf
#COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
