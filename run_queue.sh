SESSION_NAME="laravel_queue"
COMMAND="php /home/nsobh/domains/new1.sobhesahel.com/cms/artisan queue:work --queue=high,default,low"

# Wrapper that runs the command and kills the tmux session when it exits
WRAPPED_COMMAND="($COMMAND); tmux kill-session -t $SESSION_NAME"

# Check if session already exists
if ! tmux has-session -t "$SESSION_NAME" 2>/dev/null; then
	    echo "Creating tmux session: $SESSION_NAME"
	        tmux new-session -d -s "$SESSION_NAME"
		    tmux send-keys -t "$SESSION_NAME" "$WRAPPED_COMMAND" C-m
	    else
		        echo "Session $SESSION_NAME already running."
fi
