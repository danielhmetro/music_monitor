<?php

$log_file_url = 'http://cloud.daniel.mesh/';

// Fetch CSV file content
$csv_content = file_get_contents($log_file_url);

// Check if content was retrieved successfully
if ($csv_content === false) {
    die('Failed to fetch CSV file.');
}

// Parse CSV content
$csv_data = [];
$header = array("Name", "Artist", "Listened", "YouTube ID");
$rows = explode(PHP_EOL, trim($csv_content));
foreach ($rows as $row) {
    $csv_row = str_getcsv($row);
    // Format datetime
    $csv_row[2] = date('n/j/Y h:i A', strtotime($csv_row[2]));
    // Extract YouTube video ID
    $csv_row[3] = preg_replace('/^https:\/\/www\.youtube\.com\/watch\?v=/', '', $csv_row[3]);
    $csv_data[] = $csv_row;
}

// Sort by last accessed time
usort($csv_data, function($a, $b) {
    return strtotime($b[2]) - strtotime($a[2]);
});

// Print as table
echo '<a href="#" onclick="location.reload()">Refresh</a>';
echo '<table border="1">';
echo '<tr>';
foreach ($header as $column) {
    echo '<th>' . $column . '</th>';
}
echo '</tr>';
foreach ($csv_data as $row) {
    echo '<tr>';
    foreach ($row as $index => $cell) {
        echo '<td>';
        // Create a link and iframe
        if ($index == 3) {
            echo '<a href="#" onclick="toggleVideo(\'video_' . $row[0] . '\')">Watch Video</a>';
            echo '<div id="video_' . $row[0] . '" style="display:none">';
            echo '<iframe width="420" height="315" src="https://www.youtube.com/embed/' . $cell . '"></iframe>';
            echo '</div>';
        } else {
            echo $cell;
        }
        echo '</td>';
    }
    echo '</tr>';
}
echo '</table>';

// JavaScript function to toggle iframe display
echo '<script>';
echo 'function toggleVideo(videoId) {';
echo 'var videoDiv = document.getElementById(videoId);';
echo 'videoDiv.style.display = (videoDiv.style.display == "none") ? "block" : "none";';
echo '}';
echo '</script>';
