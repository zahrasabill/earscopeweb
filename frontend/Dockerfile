# Stage 1: Build stage
FROM node:20-alpine AS build

WORKDIR /app

# Pastikan tidak ada cache lama
RUN rm -rf node_modules dist && npm cache clean --force

# Copy package.json dan package-lock.json lebih dulu
COPY package.json package-lock.json ./

# Install dependencies tanpa cache
RUN npm ci --no-cache

# Copy semua source code setelah dependencies terinstall
COPY . .

# Build aplikasi untuk production
RUN npm run build

# Stage 2: Production stage (final image)
FROM node:20-alpine AS production

# Install http-server tanpa cache
RUN npm install -g http-server --no-cache

WORKDIR /app

# Copy hasil build dari stage sebelumnya
COPY --from=build /app/dist /app/dist

# Jalankan server
CMD ["http-server", "dist"]
