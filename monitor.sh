#!/bin/bash

folder_to_monitor="/var/lib/docker/volumes/nextcloud_aio_nextcloud_data/_data/admin/files/Music"
log_file="access_logs.csv"
max_rows=50

previous_file=""
current_row_count=$(wc -l < "$log_file")

python server.py &

inotifywait -m -r -e access "$folder_to_monitor"  --include '\.m4a$' |
while read -r directory event file
do
    if [[ "$file" != "$previous_file" ]]; then
        # Get the last access time of the file
        last_access=$(date +"%Y-%m-%d %H:%M:%S")
        # Extract filename without the directory path
        filename=$(basename "$file")

        # Check if the file has already been mentioned in the log file
        if [[ $current_row_count -eq 0 || $(grep -c "^\"$filename\"," "$log_file") -eq 0 ]]; then
            # Get title using tageditor command
            title_raw=$(tageditor -e -f "$folder_to_monitor/$file" -n title 2>/dev/null)
            title_noparen=$(echo "$title_raw" | sed 's/([^)]*)//g')
            title=$(echo "$title_noparen" | sed 's/\[[^][]*\]//g')
            artist=$(tageditor -e -f "$folder_to_monitor/$file" -n artist 2>/dev/null)
            youtube=$(tageditor -e -f "$folder_to_monitor/$file" -n comment 2>/dev/null)

            # Log the event to the file
            echo "\"$title\",\"$artist\",\"$last_access\",\"$youtube\"" | tee -a "$log_file"

            # Increment row count
            current_row_count=$((current_row_count + 1))

            # Check if the log file exceeds the maximum rows
            if [[ $current_row_count -gt $max_rows ]]; then
                # Remove old entries
                sed -i '1d' "$log_file"
                current_row_count=$((current_row_count - 1))
            fi
        fi

        # Update the previous file
        previous_file="$file"
    fi
done
