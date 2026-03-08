#!/bin/bash
set -e

echo "============================================"
echo "Soft ERP Frontend - Starting..."
echo "============================================"

if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    npm install
    echo "npm dependencies installed"
else
    echo "npm dependencies already installed"
fi

echo "============================================"
echo "Frontend is ready!"
echo "Starting Vite development server..."
echo "============================================"

exec npm run dev -- --host 0.0.0.0
