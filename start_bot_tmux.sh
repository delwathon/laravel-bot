#!/bin/bash

# Name of the tmux session
SESSION="crypto-bot"

# Navigate to your project
PROJECT_DIR="/home/delwathon/Desktop/crypto-trading-bot"

# Check if the session already exists
tmux has-session -t $SESSION 2>/dev/null

if [ $? != 0 ]; then
    echo "Creating tmux session: $SESSION"
    tmux new-session -d -s $SESSION -c $PROJECT_DIR

    # Window 1: Laravel server
    tmux rename-window -t $SESSION:0 'server'
    tmux send-keys -t $SESSION:0 "php artisan serve" C-m

    # Window 2: schedule:run
    tmux new-window -t $SESSION:1 -n 'schedule-run' -c $PROJECT_DIR
    tmux send-keys -t $SESSION:1 "php artisan schedule:run" C-m

    # Window 3: queue:work
    tmux new-window -t $SESSION:2 -n 'queue-work' -c $PROJECT_DIR
    tmux send-keys -t $SESSION:2 "php artisan queue:work --tries=1" C-m

    # Window 4: schedule:work
    tmux new-window -t $SESSION:3 -n 'schedule-work' -c $PROJECT_DIR
    tmux send-keys -t $SESSION:3 "php artisan schedule:work" C-m

    echo "All Laravel processes started in tmux session: $SESSION"
else
    echo "Tmux session $SESSION already exists."
fi

# Attach to the tmux session
tmux attach -t $SESSION
