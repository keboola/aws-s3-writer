# AWS S3 Writer

Upload all files in `/data/in/files` to the specified bucket and prefix. Existing files are overwritten.

# Usage

## Configuration options

- `loginType` (required) -- Login type (credentials/role)
- `accessKeyId` (required if your choose loginType "credentials") -- AWS Access Key ID
- `#secretAccessKey` (required if your choose loginType "credentials") -- AWS Secret Access Key
- `accountId` (required if your choose loginType "role") - AWS Account Id
- `bucket` (required) -- AWS S3 bucket name, it's region will be autodetected
- `prefix` (optional) -- Path prefix

## Sample configurations

### Upload to bucket root

#### Using credentials

```json
{
    "parameters": {
        "loginType": "credentials",
        "accessKeyId": "AKIA****",
        "#secretAccessKey": "****",
        "bucket": "myBucket"
    }
}
```

#### Using role

```json
{
    "parameters": {
        "loginType": "role",
        "accountId": "123456789",
        "bucket": "myBucket"
    }
}
```


### Upload with prefix bucket root

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

### Preparation

- Create AWS S3 bucket and IAM user using [`aws-services.json`](./aws-services.json) CloudFormation template.
- Create `.env` file. Use output of `aws-services` CloudFront stack to fill the variables and your S3 credentials.

```
AWS_S3_BUCKET=
AWS_REGION=
WRITER_AWS_ACCESS_KEY_ID=
WRITER_AWS_SECRET_ACCESS_KEY=
FIXTURES_AWS_ACCESS_KEY_ID=
FIXTURES_AWS_SECRET_ACCESS_KEY=
KEBOOLA_USER_AWS_ACCESS_KEY=
KEBOOLA_USER_AWS_SECRET_KEY=
ACCOUNT_ID=
ROLE_NAME=
KBC_PROJECTID=
KBC_STACKID=
```

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

## License

MIT licensed, see [LICENSE](./LICENSE) file.
