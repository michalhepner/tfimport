# tfimport

A tool to help obtaining configurations of existing resources that were not created by Terraform.

## Requirements

1. `php` >= 8.1 - _(required)_
2. `terraform` >= 1 - _(required)_

## Quick start

1. Download the latest `tfimport.phar` binary from https://github.com/michalhepner/tfimport and put into a
   directory that is included in your $PATH.
2. Create an empty directory and jump into it.
3. Run `tfimport.phar init` . This will create a temporary Terraform module structure.
4. Edit the created `main.tf` file and setup all the providers and their required versions. 
   DO NOT CHANGE THE STATE CONFIGURATION, BACKEND MUST REMAIN "local"!
5. Run `terraform.phar run` and provide the resources that you want to obtain configurations for as arguments.

## Examples

An example workflow below:

```
mkdir some-dir && some-dir
tfimport.phar init
vi main.tf # Setup your providers here
tfimport.phar run \
  aws_instance:web_server:i-0123456789 \
  aws_secretsmanager_secret:my_secret:arn:aws:secretsmanager:eu-west-1:1234567890:secret:my_secret
# Configuration will be written to STDOUT
```

## Important

Configuration outputted by this tool are not production-ready and must not be just copy-pasted into existing modules.
It is likely that the tool will show unnecessary optional resource attributes that must be cleaned up manually.
Also any kind of sensitive properties may not be outputted correctly.
