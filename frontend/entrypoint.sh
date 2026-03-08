#!/bin/bash
set -e

echo "============================================"
echo "Soft ERP Frontend - Starting..."
echo "============================================"

chown -R node:node /app

if [ ! -f "node_modules/.bin/vite" ]; then
    echo "Installing npm dependencies..."
    su -s /bin/bash node -c "npm install"
    echo "npm dependencies installed"
else
    echo "npm dependencies already installed"
fi

echo "============================================"
echo "Frontend is ready!"
echo "Starting Vite development server..."
echo "============================================"

exec su -s /bin/bash node -c "npm run dev -- --host 0.0.0.0"
