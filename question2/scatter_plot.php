<?php
// Function to read CSV file and normalize data
function readAndNormalizeData($filename) {
    $data = [];
    $x_min = PHP_INT_MAX;
    $x_max = PHP_INT_MIN;
    $y_min = PHP_INT_MAX;
    $y_max = PHP_INT_MIN;

    if (($handle = fopen($filename, "r")) !== FALSE) {
        // Skip header row
        fgetcsv($handle);
        
        // Read data and find min/max values
        while (($row = fgetcsv($handle)) !== FALSE) {
            $x = (int)$row[0];
            $y = (int)$row[1];
            
            $x_min = min($x_min, $x);
            $x_max = max($x_max, $x);
            $y_min = min($y_min, $y);
            $y_max = max($y_max, $y);
            
            $data[] = ['x' => $x, 'y' => $y];
        }
        fclose($handle);
    }
    
    return [
        'data' => $data,
        'bounds' => [
            'x_min' => $x_min,
            'x_max' => $x_max,
            'y_min' => $y_min,
            'y_max' => $y_max
        ]
    ];
}

// Function to create scatter plot
function createScatterPlot($filename) {
    // Read and normalize data
    $result = readAndNormalizeData($filename);
    $data = $result['data'];
    $bounds = $result['bounds'];
    
    // Image dimensions and margins
    $width = 1000;
    $height = 800;
    $margin = 50;
    
    // Create image
    $image = imagecreatetruecolor($width, $height);
    
    // Colors
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 200, 200, 200);
    $blue = imagecolorallocate($image, 0, 0, 255);
    
    // Fill background
    imagefill($image, 0, 0, $white);
    
    // Calculate scaling factors
    $x_scale = ($width - 2 * $margin) / ($bounds['x_max'] - $bounds['x_min']);
    $y_scale = ($height - 2 * $margin) / ($bounds['y_max'] - $bounds['y_min']);
    
    // Draw grid lines
    $grid_spacing = 50;
    for ($i = $margin; $i < $width - $margin; $i += $grid_spacing) {
        imageline($image, $i, $margin, $i, $height - $margin, $gray);
    }
    for ($i = $margin; $i < $height - $margin; $i += $grid_spacing) {
        imageline($image, $margin, $i, $width - $margin, $i, $gray);
    }
    
    // Draw axes
    imageline($image, $margin, $height - $margin, $width - $margin, $height - $margin, $black); // X axis
    imageline($image, $margin, $margin, $margin, $height - $margin, $black); // Y axis
    
    // Plot points
    foreach ($data as $point) {
        $x = $margin + ($point['x'] - $bounds['x_min']) * $x_scale;
        $y = $height - $margin - ($point['y'] - $bounds['y_min']) * $y_scale;
        
        // Draw small circle for each point
        imagefilledellipse($image, (int)$x, (int)$y, 3, 3, $blue);
    }
    
    // Add axis labels
    $font_size = 3;
    imagestring($image, $font_size, $width/2, $height - 20, "X Axis", $black);
    imagestringup($image, $font_size, 15, $height/2, "Y Axis", $black);
    
    // Add scale markers
    for ($i = 0; $i <= 5; $i++) {
        $x_val = $bounds['x_min'] + ($bounds['x_max'] - $bounds['x_min']) * $i / 5;
        $y_val = $bounds['y_min'] + ($bounds['y_max'] - $bounds['y_min']) * $i / 5;
        
        $x_pos = $margin + ($width - 2 * $margin) * $i / 5;
        $y_pos = $height - $margin - ($height - 2 * $margin) * $i / 5;
        
        imagestring($image, 2, (int)$x_pos - 15, $height - $margin + 5, (int)$x_val, $black);
        imagestring($image, 2, $margin - 40, (int)$y_pos - 7, (int)$y_val, $black);
    }
    
    // Output image
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// Analyze data patterns
function analyzeData($filename) {
    $result = readAndNormalizeData($filename);
    $data = $result['data'];
    $bounds = $result['bounds'];
    
    // Calculate basic statistics
    $sum_x = 0;
    $sum_y = 0;
    $count = count($data);
    
    foreach ($data as $point) {
        $sum_x += $point['x'];
        $sum_y += $point['y'];
    }
    
    $mean_x = $sum_x / $count;
    $mean_y = $sum_y / $count;
    
    return [
        'count' => $count,
        'x_range' => [$bounds['x_min'], $bounds['x_max']],
        'y_range' => [$bounds['y_min'], $bounds['y_max']],
        'mean_x' => $mean_x,
        'mean_y' => $mean_y
    ];
}

// Usage
$filename = 'Q2.csv';

// Create plot
createScatterPlot($filename);

// Analyze data 

// $analysis = analyzeData($filename);
// echo "Data Analysis:\n";
// echo "Number of points: " . $analysis['count'] . "\n";
// echo "X range: " . $analysis['x_range'][0] . " to " . $analysis['x_range'][1] . "\n";
// echo "Y range: " . $analysis['y_range'][0] . " to " . $analysis['y_range'][1] . "\n";
// echo "Mean X: " . $analysis['mean_x'] . "\n";
// echo "Mean Y: " . $analysis['mean_y'] . "\n";


/*
This is a shape of banana using 6377 points with a x-range from 147 to 837 and y-range from -946 to -48.


*/

?>