# AWS S3 Writer

[![Build Status](https://travis-ci.org/keboola/aws-s3-writer.svg?branch=master)](https://travis-ci.org/keboola/aws-s3-writer)

Upload all files in `/data/in/files` to the specified bucket and prefix.

# Usage

> fill in usage instructions

## Configuration options

- `accessKeyId` (required) -- AWS Access Key ID
- `#secretAccessKey` (required) -- AWS Secret Access Key
- `bucket` (required) -- AWS S3 bucket name, it's region will be autodetected
- `prefix` (optional) -- Path prefix

### Sample configurations

#### Upload to bucket root

```json
{
    "parameters": {
        "accessKeyId": "AKIA****",
        "#secretAccessKey": "****",
        "bucket": "myBucket"
    }
}
```

#### Upload with prefix bucket root

```json
{
    "parameters": {
        "accessKeyId": "AKIA****",
        "#secretAccessKey": "****",
        "bucket": "myBucket",
        "prefix": "myPath/"
    }
}
```

## Development

Clone this repository and init the workspace with following command:

```
git clone https://github.com/keboola/aws-s3-writer
cd aws-s3-writer
docker-compose build
docker-compose run --rm dev composer install --no-scripts
```

Run the test suite using this command:

```
docker-compose run --rm dev composer tests
```

# Integration

For information about deployment and integration with KBC, please refer to the [deployment section of developers documentation](https://developers.keboola.com/extend/component/deployment/)
