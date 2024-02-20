import * as cdk from '@aws-cdk/core';
import * as secretsManager from '@aws-cdk/aws-secretsmanager';
import { DeploymentEnvironment, FargateStack, FargateStackProps } from './fargate-stack';

export class AcceptanceStack extends FargateStack {

    constructor(scope: cdk.Construct, id: string, props: FargateStackProps) {
        super(scope, id, props);
    }

    protected appEnv(): string {
        return 'production';
    }

    protected deploymentEnvironment(): DeploymentEnvironment {
        return 'acceptance';
    }

    protected branchName(): string {
        return 'development';
    }

    protected awsCredentials(): secretsManager.ISecret {
        return secretsManager.Secret.fromSecretNameV2(this, 'AcceptanceAwsCredentials', 'BeeHealthDataAcceptance');
    }

    protected domainName(): string {
        return 'acceptance.beehealthdata.org';
    }
}