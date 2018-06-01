<?php

declare(strict_types=1);

/*
 * Purges S3 bucket
 */

date_default_timezone_set('Europe/Prague');
ini_set('display_errors', 'true');
error_reporting(E_ALL);

$basedir = dirname(__DIR__);

require_once $basedir . '/../vendor/autoload.php';

$client =  new \Aws\S3\S3Client([
    'region' => getenv('AWS_REGION'),
    'version' => '2006-03-01',
    'credentials' => [
        'key' => getenv('FIXTURES_AWS_ACCESS_KEY_ID'),
        'secret' => getenv('FIXTURES_AWS_SECRET_ACCESS_KEY'),
    ],
]);

// Where the files will be transferred to
$bucket = getenv('AWS_S3_BUCKET');
$dest = 's3://' . $bucket;

// clear bucket
$result = $client->listObjects([
    'Bucket' => $bucket,
]);

$objects = $result->get('Contents');
if ($objects) {
    $client->deleteObjects([
        'Bucket' => $bucket,
        'Delete' => [
            'Objects' => array_map(function ($object) {
                return [
                    'Key' => $object['Key'],
                ];
            }, $objects),
        ],
    ]);
}
