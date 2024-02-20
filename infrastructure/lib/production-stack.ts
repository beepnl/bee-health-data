import * as cdk from '@aws-cdk/core';
import * as secretsManager from '@aws-cdk/aws-secretsmanager';
import { DeploymentEnvironment, FargateStack, FargateStackProps } from './fargate-stack';

export class ProductionStack extends FargateStack {

    constructor(scope: cdk.Construct, id: string, props: FargateStackProps) {
        super(scope, id, props);
    }

    protected appEnv(): string {
        return 'production';
    }

    protected deploymentEnvironment(): DeploymentEnvironment {
        return 'production';
    }

    protected branchName(): string {
        return 'master';
    }

    protected awsCredentials(): secretsManager.ISecret {
        return secretsManager.Secret.fromSecretNameV2(this, 'ProductionAwsCredentials', 'BeeHealthDataProd');
    }

    protected domainName(): string {
        return 'beehealthdata.org';
    }
}