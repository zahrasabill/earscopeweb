# Stage 1: Build stage
FROM node:lts-alpine

# Install http-server
RUN npm install -g http-server

# Workdir
WORKDIR /app

# Pastikan tidak ada cache lama
RUN rm -rf node_modules dist && npm cache clean --force

# Copy package.json dan package-lock.json lebih dulu
COPY package.json package-lock.json ./

# Install dependencies tanpa cache
RUN npm install --no-cache

# Copy semua source code setelah dependencies terinstall
COPY . .

# Build aplikasi untuk production
RUN npm run build


# Jalankan server
CMD ["http-server", "dist"]
