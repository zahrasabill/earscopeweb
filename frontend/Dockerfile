# Stage 1: Build stage
FROM node:lts-alpine

# Install http-server
RUN npm install -g http-server

# Workdir
WORKDIR /app

# rm cache
RUN rm -rf node_modules dist && npm cache clean --force

# Copy package
COPY package.json package-lock.json ./

# Install dependencies
RUN npm install --no-cache

# Copy
COPY . .

# Build
RUN npm run build


# Run
ENTRYPOINT ["http-server", "dist"]
