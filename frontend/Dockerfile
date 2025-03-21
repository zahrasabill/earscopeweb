# Stage 1: Build stage
FROM node:20-alpine AS build

WORKDIR /app

# Copy package.json dan package-lock.json untuk memanfaatkan caching layer Docker
COPY package.json package-lock.json ./

# Install dependencies dengan npm ci untuk memastikan versi yang tepat dari package-lock.json
RUN npm ci

# Copy semua source code setelah dependencies terinstall
COPY . .

# Build aplikasi untuk production
RUN npm run build

# Stage 2: Production stage (final image)
FROM node:20-alpine AS production

# Install http-server untuk serving aplikasi statis
RUN npm install -g http-server

WORKDIR /app

# Copy hasil build dari stage sebelumnya
COPY --from=build /app/dist /app/dist

# Jalankan server
CMD ["http-server", "dist"]
