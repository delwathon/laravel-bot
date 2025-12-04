#!/bin/bash

# Navigate to the project
cd /home/delwathon/Desktop/crypto-trading-bot

# Start Laravel development server
echo "Starting Laravel server..."
php artisan serve &

# Run scheduler once
echo "Running Laravel schedule:run..."
php artisan schedule:run &

# Start queue worker
echo "Starting queue:work..."
php artisan queue:work --tries=1 &

# Start scheduler worker (daemon)
echo "Starting schedule:work..."
php artisan schedule:work &

echo "All processes started!"
