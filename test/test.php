<?php

echo 'Testing......' . PHP_EOL . PHP_EOL;

$functions = [

    //'get_distance'
];

ini_set('memory_limit','1600M');

$classes = [
    'coordinate'     => ['lat', 'lng', 'set_lat', 'set_lng', 'ele', 'set_ele', 'get_bearing_to', 'timestamp', 'gridref'],
    'coordinate_set' => [
        '__construct',
        'set',
        'get',
        'count',
        'trim',
        'set_section',
        'repair',
        'has_height_data',
        'set_graph_values',
        'set_ranges',
        'first',
        'last',
        'simplify',
        'part_length',
        'part_duration',
    ],
    'distance_map'   => [
        '__construct',
        'get',
        'score_open_distance_3tp',
        'score_out_and_return',
        'score_triangle'
    ],
    'task' => [
        'get_distance',
        'get_gridref',
        'get_coordinate_ids',
        'get_duration',
        'completes_task'
    ],
    'formatter_kml' => [
        'output'
    ],
    'formatter_kml_earth' => [
        'output'
    ],
    'formatter_kml_split' => [
        'output'
    ],
    'formatter_js' => [
        'output'
    ],
];

$pass = true;

foreach ($functions as $function) {
    $pass &= function_exists($function);
    echo pass_fail('Function ' . $function . ' exists' . PHP_EOL, $pass);
}

foreach ($classes as $class => $methods) {
    $pass &= class_exists($class);
    echo pass_fail('Class ' . $class . ' exists' . PHP_EOL, $pass);
    foreach ($methods as $method) {
        $pass &= method_exists($class, $method);
        echo pass_fail('----- ' . $class . '->' . $method . '() exists' . PHP_EOL, $pass);
    }
    echo PHP_EOL;
}

if (!$pass) {
    die('Extension elements don\'t work :(' . PHP_EOL);
}

$coordinate_1 = new coordinate(52.3033, -0.79195, 1);
$coordinate_2 = new coordinate(52.296616666666665, -0.6392833333333333, 3);
$coordinate_3 = new coordinate(52.19661, -0.62928333, 3, 19);

$set = new coordinate_set(2);
$set->set($coordinate_1);
$set->set($coordinate_2);

$tests = [
    '52.3033'               => $coordinate_1->lat(),
    '-0.79195'              => $coordinate_1->lng(),
    '1'                     => $coordinate_1->ele(),
    '94.0239771393205'      => $coordinate_1->get_bearing_to($coordinate_2),
    '10.407709313555'       => $coordinate_1->get_distance_to($coordinate_2),
    '10.441137'             => $coordinate_1->get_distance_to($coordinate_2, true),
    '52.3033'               => $set->first()->lat(),
    '-0.6392833333333333'   => $set->last()->lng(),
    '19'                    => $coordinate_3->timestamp(),
];
foreach ($tests as $key => $value) {
    echo pass_fail(sprintf('----> (%010.6f) = %010.6f', $key, $value), round($key, 6) == round($value, 6)) . PHP_EOL;
}

echo 'Loop test: ' . PHP_EOL;
for($i = 0; $i < $set->count(); $i++) {
    echo $i . ': ' . $set->get($i)->gridref() . PHP_EOL;
}
echo pass("Looping successful");


echo PHP_EOL;

score_track('test0.igc', [
    'OD: 014.50 -> 140,346,642,771,813,',
    'OR: 007.88 -> 352,642,771,',
    'TR: 009.23 -> 191,257,345,446,755,'
]);
// score_track('test1.igc', [
//     'OD: 014.50 -> 140,346,642,771,813,',
//     'OR: 007.88 -> 352,642,771,',
//     'TR: 009.23 -> 191,257,345,446,755,'
// ]);
// score_track('test2.igc', [
//     'OD: 028.92 -> 3,126,975,1014,1597,',
//     'OR: 002.68 -> 892,975,1013,',
//     'TR: 002.80 -> 2,121,293,431,436,'
// ]);
// score_track('test3.igc', [
//     'OD: 060.30 -> 1,581,1293,1719,1963,',
//     'OR: 030.08 -> 414,1293,1875,',
//     'TR: 053.07 -> 409,581,1286,1592,1862,'
// ]);
// score_track('test4.igc', [
//     'OD: 121.63 -> 333,599,885,2108,4901,',
//     'OR: 003.25 -> 599,884,958,',
//     'TR: 003.71 -> 12,67,487,965,1000,'
// ]);
// score_track('test5.igc', [
//     'OD: 060.30 -> 1,581,1293,1719,1963,',
//     'OR: 030.08 -> 414,1293,1875,',
//     'TR: 053.07 -> 409,581,1286,1592,1862,'
// ]);
// score_track('test6.igc', [
//     'OD: 308.32 -> 201,943,2113,4110,4414,',
//     'OR: 001.64 -> 4398,4414,4423,',
//     'TR: 001.38 -> 4199,4200,4214,4230,4231,'
// ]);
// score_track('test7.igc', [
//     'OD: 110.27 -> 11,206,4025,5300,7613,',
//     'OR: 105.24 -> 615,4025,7614,',
//     'TR: 033.63 -> 1841,2636,3429,5166,6657,'
// ]);

ini_set('memory_limit', '512M');
function score_track($file, $answers = []) {
    global $coordinate_1, $coordinate_2, $coordinate_3;

    $time = microtime(true);
    echo "------------------------" . PHP_EOL;
    echo "Memory: " . memory_get_usage(true) . PHP_EOL;
    echo "Track: " . $file . PHP_EOL;

    echo 'Creating set:';
    $set_2 = new coordinate_set();
    echo get_time($time) . PHP_EOL;

    echo 'Parsing file:';
    $records = $set_2->parse_igc(file_get_contents($file));
    echo get_time($time) . PHP_EOL;
    echo "Date: " . $set_2->date() . PHP_EOL;

    $intial = $set_2->count();

    echo 'Trimming file:';
    $set_2->trim();
    echo get_time($time) . PHP_EOL;

    // echo 'Simplifing file:';
    // $set_2->simplify();
    // echo get_time($time) . PHP_EOL;

    echo 'Points:' . $set_2->count() . " (" . $intial . ")" . PHP_EOL;
    echo 'Parts:' . $set_2->part_count() . PHP_EOL;

    echo 'Repairing track';
    $set_2->repair();
    echo get_time($time) . PHP_EOL;

    echo 'Graphing track';
    $set_2->set_graph_values();
    echo get_time($time) . PHP_EOL;

    echo 'Ranging track';
    $set_2->set_ranges();
    echo get_time($time) . PHP_EOL;

    $set_2->set_section(1);

    echo 'Points:' . $set_2->count() . " (" . $intial . ")" . PHP_EOL;
    echo 'Parts:' . $set_2->part_count() . PHP_EOL;

    echo 'Building map';
    $map_2 = new distance_map($set_2);
    echo get_time($time) . PHP_EOL;

    $od = $map_2->score_open_distance_3tp();
    echo get_score($map_2, $od, $answers[0], 'OD') . get_time($time) . PHP_EOL;
    $or = $map_2->score_out_and_return();
    echo get_score($map_2, $or, $answers[1], 'OR') . get_time($time) . PHP_EOL;
    $tr = $map_2->score_triangle();
    echo get_score($map_2, $tr, $answers[2], 'TR') . get_time($time) . PHP_EOL;
    echo 'Coordinates: ' . $od->get_gridref() . PHP_EOL;
    echo 'Duration: ' . ($set_2->last()->timestamp() - $set_2->first()->timestamp()) . 's' . PHP_EOL;


    $task = new task($coordinate_1, $coordinate_2, $coordinate_3);
    echo 'Checking task:' . PHP_EOL;
    echo pass_fail('Valid task is found' . PHP_EOL, $od->completes_task($set_2) == true);
    echo pass_fail('Invalid task not found' . PHP_EOL, $task->completes_task($set_2) == false);

    echo 'Outputting kml:';
    $formatter = new formatter_kml($set_2, $file, $od, $or, $tr, $od);
    file_put_contents(str_replace('.igc', '.kml', $file), $formatter->output());
    echo get_time($time) . PHP_EOL;

    echo 'Outputting js:';
    $formatter = new formatter_js($set_2, 10);
    $formatter->output();
    echo get_time($time) . PHP_EOL;

    echo 'Outputting kml (Split):';
    $formatter = new formatter_kml_split($set_2);
    $formatter->output();
    echo get_time($time) . PHP_EOL;

    echo 'Outputting KML (Earth):';
    $formatter = new formatter_kml_earth($set_2, $file, $od, $or, $tr);
    $formatter->output();
    echo get_time($time) . PHP_EOL;

    unset($set_2);
    unset($formatter);

    echo "Memory: " . memory_get_usage(true) . PHP_EOL;
}

function get_time(&$time) {
    $new_time = microtime(true);
    $delta = $new_time - $time;
    $time = $new_time;
    return ' (' . round($delta * 1000, 5) . 'ms)';
}

function get_score($map_2, $od, $correct, $type) {
    $distance = $od->get_distance();
    $string = sprintf('%s: %06.2f -> %s', $type, $distance, $od->get_coordinate_ids());
    if ($string != $correct) {
        return fail($string . PHP_EOL .  str_repeat('=', 6) . ' ' . $correct);
    }
    return pass($string);
}

function pass_fail($string, $bool) {
    return $bool ? pass($string) : fail($string);
}

function fail($string) {
    return "\033[0;31m[FAIL] " . $string . "\033[0m";
}

function pass($string) {
    return "\033[0;32m[PASS] " . $string . "\033[0m";
}