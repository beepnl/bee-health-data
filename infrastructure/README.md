# Bee health data portal infrastructure


## Prerequisite

You must have docker installed locally. We use Docker to run all other commands such as npm, cdk, etc. 
Always use the commands below, do **NOT** execute commands through your local npm or cdk installation.
By running all commands through Docker, we isolate our environment from the underlying developer OS'es.

## Running for the first time

When you have checked out this project for the first time, you will not have a `node_modules` directory locally.
You must run npm install, using the above basic invocation, to install npm dependencies into your project:

```shell
docker run -it --rm  -v $PWD:/usr/src/app -w /usr/src/app node:lts-alpine npm install
```

## NPM

When you want to install new packages, run your npm command **through the container**. This standardizes on the current
LTS and prevents side effects resulting from using different node versions by different developers.

Installing a package:
```shell
docker run -it --rm -v $PWD:/usr/src/app -w /usr/src/app node:lts-alpine npm install <package name>
```

Updating all packages:
```shell
docker run -it --rm -v $PWD:/usr/src/app -w /usr/src/app node:lts-alpine npm update
```

## CDK

### Basic invocation

In order to execute the CDK, we need a node container. We then need to mount our working directory into it, as well as our AWS credentials so that it can access AWS resources. The basic invocation therefore looks like this:

```shell
docker run -it --rm -v ~/.aws:/root/.aws -v $PWD:/usr/src/app -w /usr/src/app node:lts-alpine npx cdk
```

This basic invocation is a drop in replacement for the `cdk` command.

### Diff

Before running a deployment, you should run a diff to see if the changes are what you expect.

```shell
docker run -it --rm -v ~/.aws:/root/.aws -v $PWD:/usr/src/app -w /usr/src/app \
-e AWS_PROFILE=beep \
node:lts-alpine /bin/sh -c "npm run build && npx cdk diff"
```

### Deployment

```shell
docker run -it --rm -v ~/.aws:/root/.aws -v $PWD:/usr/src/app -w /usr/src/app \
-e AWS_PROFILE=beep \
node:lts-alpine /bin/sh -c "npm run build && npx cdk deploy"
```