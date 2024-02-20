#!/usr/bin/env node
import 'source-map-support/register';
import * as cdk from '@aws-cdk/core';
import { BaseStack } from '../lib/base-stack';
import { ProductionStack } from '../lib/production-stack';
import { AcceptanceStack } from '../lib/acceptance-stack';

const app = new cdk.App();

const baseStack = new BaseStack(app, 'BaseStack', {
  env: { account: '038855593698', region: 'eu-central-1' },
});

new ProductionStack(app, 'Production', {
  env: { account: '038855593698', region: 'eu-central-1' },
  stackName: `${BaseStack.appName}-production`,
  vpc: baseStack.vpc,
  database: baseStack.database,
});

new AcceptanceStack(app, 'Acceptance', {
env: { account: '038855593698', region: 'eu-central-1' },
  stackName: `${BaseStack.appName}-acceptance`,
  vpc: baseStack.vpc,
  database: baseStack.database,
});
