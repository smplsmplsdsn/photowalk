FROM node:20

WORKDIR /app

# gulp-cli をグローバルインストール
RUN npm install -g gulp-cli

# package.json と package-lock.json をコピーして依存関係インストール
COPY package*.json ./
RUN npm install
