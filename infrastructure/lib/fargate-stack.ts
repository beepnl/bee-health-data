import * as alias from '@aws-cdk/aws-route53-targets';
import * as cdk from '@aws-cdk/core';
import { RemovalPolicy } from '@aws-cdk/core';
import * as certificateManager from '@aws-cdk/aws-certificatemanager';
import * as codeBuild from '@aws-cdk/aws-codebuild';
import * as codePipeline from '@aws-cdk/aws-codepipeline';
import * as codePipelineActions from '@aws-cdk/aws-codepipeline-actions';
import * as ec2 from '@aws-cdk/aws-ec2';
import * as ecr from '@aws-cdk/aws-ecr';
import * as ecs from '@aws-cdk/aws-ecs';
import * as elb from '@aws-cdk/aws-elasticloadbalancingv2';
import * as logs from '@aws-cdk/aws-logs';
import * as rds from '@aws-cdk/aws-rds';
import * as route53 from '@aws-cdk/aws-route53';
import * as s3 from '@aws-cdk/aws-s3';
import * as secretsManager from '@aws-cdk/aws-secretsmanager';
import { Secret } from '@aws-cdk/aws-ecs/lib/container-definition';
import { ApplicationTargetGroup } from '@aws-cdk/aws-elasticloadbalancingv2/lib/alb/application-target-group';
import { BaseStack } from './base-stack';

export type DeploymentEnvironment = 'production' | 'acceptance';

export interface FargateStackProps extends cdk.StackProps {
    vpc: ec2.Vpc;
    database: rds.DatabaseCluster;
}

/**
 * Possible bucket IAM policy for storage:
 * {
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:ListBucket",
                "s3:ListAllMyBuckets",
                "s3:GetBucketLocation"
            ],
            "Resource": [
                "arn:aws:s3:::my-bucket"
            ]
        },
        {
            "Effect": "Allow",
            "Action": [
                "s3:PutObject",
                "s3:PutObjectAcl",
                "s3:GetObject",
                "s3:DeleteObject"
            ],
            "Resource": [
                "arn:aws:s3:::my-bucket/*"
            ]
        }
    ]
}
 */
export abstract class FargateStack extends cdk.Stack {

    protected readonly nginxRepository: ecr.Repository;

    protected readonly phpRepository: ecr.Repository;

    protected readonly workerRepository: ecr.Repository;

    protected readonly storageBucket: s3.Bucket;

    protected readonly loadBalancer: elb.ApplicationLoadBalancer;

    protected constructor(scope: cdk.Construct, id: string, props: FargateStackProps) {
        super(scope, id, props);

        this.nginxRepository = this.createRepository('NginxRepository', 'nginx');
        this.phpRepository = this.createRepository('PhpRepository', 'php');
        this.workerRepository = this.createRepository('WorkerRepository', 'worker');

        this.storageBucket = new s3.Bucket(this, 'Storage', {
            versioned: true,
            removalPolicy: this.deploymentEnvironment() === 'production' ? RemovalPolicy.RETAIN : RemovalPolicy.DESTROY,
        });

        const logGroup = new logs.LogGroup(this, 'Logs', {
            logGroupName: `BeeHealthData/${this.capitalizedDeploymentEnvironment()}`,
            retention: logs.RetentionDays.ONE_MONTH,
            removalPolicy: RemovalPolicy.DESTROY,
        });

        const cluster = new ecs.Cluster(this, 'Cluster', {
            clusterName: `${BaseStack.appName}-${this.deploymentEnvironment()}`,
            vpc: props.vpc,
        });

        this.loadBalancer = this.createLoadBalancer(props.vpc);

        const awsCredentials = this.awsCredentials();
        const appService = this.createAppService(cluster, this.nginxRepository, this.phpRepository, props.database, awsCredentials, this.storageBucket, logGroup);
        const workerService = this.createWorkerService(cluster, this.workerRepository, props.database, awsCredentials, this.storageBucket, logGroup);

        this.registerAppServiceWithLoadBalancer(appService, this.loadBalancer);

        this.createPipeline(props.vpc, this.nginxRepository, this.phpRepository, this.workerRepository, appService, workerService);
    }

    private createRepository(id: string, name: string) {
        return new ecr.Repository(this, id, {
            repositoryName: `${name}-${this.deploymentEnvironment()}`,
            removalPolicy: RemovalPolicy.DESTROY,
        });
    }

    private createLoadBalancer(vpc: ec2.Vpc) {
        return new elb.ApplicationLoadBalancer(this, 'LoadBalancer', {
            deletionProtection: this.deploymentEnvironment() === 'production',
            internetFacing: true,
            vpc,
            vpcSubnets: {
                subnetGroupName: 'Ingress',
            },
            loadBalancerName: `${BaseStack.appName}-${this.deploymentEnvironment()}`,
        });
    }

    protected service(id: string, taskDefinition: ecs.FargateTaskDefinition, cluster: ecs.Cluster): ecs.FargateService {
        return new ecs.FargateService(this, id, {
            desiredCount: 1,
            minHealthyPercent: 100,
            maxHealthyPercent: 200,
            taskDefinition,
            cluster,
            circuitBreaker: {
                rollback: true,
            },
            deploymentController: {
                type: ecs.DeploymentControllerType.ECS,
            },
            assignPublicIp: false,
            vpcSubnets: {
                subnetGroupName: 'Application',
                availabilityZones: ['eu-central-1a'],
            },
        });
    }

    protected appTaskDefinition(id: string, nginxRepository: ecr.Repository, phpRepository: ecr.Repository, database: rds.DatabaseCluster, logGroup: logs.LogGroup, storageBucket: s3.Bucket, awsCredentials: secretsManager.ISecret): ecs.FargateTaskDefinition {
        const appTaskDefinition = new ecs.FargateTaskDefinition(this, id, {
            cpu: 1024,
            memoryLimitMiB: 8192,
            family: `${BaseStack.appName}-web-${this.deploymentEnvironment()}`,
        });

        appTaskDefinition.addContainer('Nginx', {
            portMappings: [
                {
                    containerPort: 8080,
                    hostPort: 8080,
                }
            ],
            essential: true,
            image: ecs.ContainerImage.fromEcrRepository(nginxRepository),
            logging: ecs.LogDriver.awsLogs({
                logGroup: logGroup,
                streamPrefix: 'Nginx',
            }),
            environment: {
                PHP_HOST: '127.0.0.1',
            },
        });

        appTaskDefinition.addContainer('Php', {
            essential: true,
            image: ecs.ContainerImage.fromEcrRepository(phpRepository),
            logging: ecs.LogDriver.awsLogs({
                logGroup: logGroup,
                streamPrefix: 'Php',
            }),
            environment: this.taskEnvironment(database, storageBucket),
            secrets: this.secrets(database, awsCredentials),
        });

        return appTaskDefinition
    }

    protected workerTaskDefinition(workerRepository: ecr.Repository, database: rds.DatabaseCluster, logGroup: logs.LogGroup, storageBucket: s3.Bucket, awsCredentials: secretsManager.ISecret): ecs.FargateTaskDefinition {
        const appTaskDefinition = new ecs.FargateTaskDefinition(this, 'WorkerTask', {
            cpu: 256,
            memoryLimitMiB: 2048,
            family: `${BaseStack.appName}-worker-${this.deploymentEnvironment()}`,
        });

        appTaskDefinition.addContainer('Worker', {
            essential: true,
            image: ecs.ContainerImage.fromEcrRepository(workerRepository),
            logging: ecs.LogDriver.awsLogs({
                logGroup: logGroup,
                streamPrefix: 'Worker',
            }),
            environment: this.taskEnvironment(database, storageBucket),
            secrets: this.secrets(database, awsCredentials),
        });

        return appTaskDefinition
    }

    protected taskEnvironment(database: rds.DatabaseCluster, storageBucket: s3.Bucket): { [key: string]: string } {
        return {
            ADMIN_EMAIL: 'admin@beep.nl',
            ADMIN_PASSWORD: 'secret',
            ADMIN_FIRSTNAME: 'Admin',
            ADMIN_LASTNAME: 'Beep',

            AWS_DEFAULT_REGION: this.region,

            BROADCAST_DRIVER: 'log',
            CACHE_DRIVER: 'file',
            QUEUE_CONNECTION: 'database',
            SESSION_DRIVER: 'database',
            SESSION_LIFETIME: '120',

            APP_NAME: 'Bee Health Data',
            APP_ENV: this.appEnv(),
            APP_KEY: 'base64:KPJwWyHhkQ07ZGkFzIwvpyq8X29eghKSFsAL9PtJapI=',
            APP_DEBUG: 'false',
            APP_URL: `https://${this.domainName()}`,

            FILESYSTEM_DRIVER: 's3',

            LOG_CHANNEL: 'stderr',

            DB_CONNECTION: 'pgsql',
            DB_PORT: database.secret?.secretValueFromJson('port').toString()!,
            DB_DATABASE: this.deploymentEnvironment(),
            DB_HOST: database.secret?.secretValueFromJson('host').toString()!,

            MAIL_CONTACT_NAME: 'Bee Health Data',
            MAIL_CONTACT_TO: 'support@beep.nl',
            MAIL_FROM_ADDRESS: 'noreply@beehealthdata.org',
            MAIL_FROM_NAME: 'Bee Health Data',
            MAIL_MAILER: 'ses',

            AWS_BUCKET: storageBucket.bucketName,
        };
    }

    protected secrets(database: rds.DatabaseCluster, awsCredentials: secretsManager.ISecret): { [key: string]: Secret } {
        return {
            DB_USERNAME: ecs.Secret.fromSecretsManager(database.secret!, 'username'),
            DB_PASSWORD: ecs.Secret.fromSecretsManager(database.secret!, 'password'),
            AWS_ACCESS_KEY_ID: ecs.Secret.fromSecretsManager(awsCredentials, 'accessKeyId'),
            AWS_SECRET_ACCESS_KEY: ecs.Secret.fromSecretsManager(awsCredentials, 'secretAccessKey'),
        }
    }

    protected createAppService(cluster: ecs.Cluster, nginxRepository: ecr.Repository, phpRepository: ecr.Repository, database: rds.DatabaseCluster, awsCredentials: secretsManager.ISecret, storageBucket: s3.Bucket, logGroup: logs.LogGroup): ecs.FargateService {
        const appTaskDefinition = this.appTaskDefinition('AppTask', nginxRepository, phpRepository, database, logGroup, storageBucket, awsCredentials);
        const service = this.service('AppService', appTaskDefinition, cluster);
        service.connections.allowTo(database, ec2.Port.tcp(5432));

        return service;
    }

    protected createWorkerService(cluster: ecs.Cluster, phpRepository: ecr.Repository, database: rds.DatabaseCluster, awsCredentials: secretsManager.ISecret, storageBucket: s3.Bucket, logGroup: logs.LogGroup): ecs.FargateService {
        const workerTaskDefinition = this.workerTaskDefinition(phpRepository, database, logGroup, storageBucket, awsCredentials);
        const service = this.service('WorkerService', workerTaskDefinition, cluster);
        service.connections.allowTo(database, ec2.Port.tcp(5432));

        return service;
    }

    protected registerAppServiceWithLoadBalancer(appService: ecs.FargateService, loadBalancer: elb.ApplicationLoadBalancer): ApplicationTargetGroup {
        const hostedZone = this.hostedZone();

        loadBalancer.addRedirect();

        const targetGroup = loadBalancer.addListener('HttpsListener', {
            certificates: [this.createCertificate(hostedZone, this.domainName())],
            open: true,
            port: 443,
            sslPolicy: elb.SslPolicy.FORWARD_SECRECY_TLS12_RES_GCM
        }).addTargets('BeeHealthApp', {
            targets: [appService],
            port: 8080,
            deregistrationDelay: cdk.Duration.minutes(1),
            healthCheck: {
                path: '/ping',
            }
        });

        this.createDnsRecord(hostedZone, loadBalancer);

        return targetGroup;
    }

    protected hostedZone(): route53.IHostedZone {
        return route53.HostedZone.fromLookup(this, 'HostedZone', {
            domainName: 'beehealthdata.org',
        });
    }

    protected createCertificate(hostedZone: route53.IHostedZone, domainName: string,): certificateManager.Certificate {
        return new certificateManager.Certificate(this, 'Certificate', {
            domainName,
            validation: certificateManager.CertificateValidation.fromDns(hostedZone),
        });
    }

    protected createDnsRecord(hostedZone: route53.IHostedZone, loadBalancer: elb.ApplicationLoadBalancer): route53.RecordSet {
        switch(this.deploymentEnvironment()) {
            case 'production':
                return new route53.ARecord(this, 'AliasRecord', {
                    zone: hostedZone,
                    target: route53.RecordTarget.fromAlias(new alias.LoadBalancerTarget(loadBalancer)),
                    ttl: cdk.Duration.minutes(5),
                });
            case 'acceptance':
                return new route53.ARecord(this, 'AliasRecord', {
                    zone: hostedZone,
                    recordName: this.deploymentEnvironment(),
                    target: route53.RecordTarget.fromAlias(new alias.LoadBalancerTarget(loadBalancer)),
                    ttl: cdk.Duration.minutes(5),
                });
        }
    }

    protected createPipeline(vpc: ec2.Vpc, nginxRepository: ecr.Repository, phpRepository: ecr.Repository, workerRepository: ecr.Repository, appService: ecs.FargateService, workerService: ecs.FargateService): codePipeline.Pipeline {
        const pipeline = new codePipeline.Pipeline(this, 'Pipeline', {
            pipelineName: this.deploymentEnvironment(),
        });

        const sourceArtifact = new codePipeline.Artifact('SourceArtifact');
        pipeline.addStage({
            stageName: 'Source',
            actions: [
                new codePipelineActions.GitHubSourceAction({
                    actionName: 'DownloadSource',
                    owner: 'beepnl',
                    repo: 'bee-health-data-portal',
                    branch: this.branchName(),
                    oauthToken: cdk.SecretValue.secretsManager('GithubToken'),
                    output: sourceArtifact,
                    trigger: codePipelineActions.GitHubTrigger.WEBHOOK,
                }),
            ],
        });

        const buildProject = new codeBuild.PipelineProject(this, 'BuildImages', {
            buildSpec: codeBuild.BuildSpec.fromSourceFilename('buildspecs/prod.yml'),
            cache: codeBuild.Cache.local(codeBuild.LocalCacheMode.DOCKER_LAYER),
            environment: {
                privileged: true,
                buildImage: codeBuild.LinuxBuildImage.AMAZON_LINUX_2_3,
                computeType: codeBuild.ComputeType.SMALL,
                environmentVariables: {
                    AWS_ACCOUNT: {
                        type: codeBuild.BuildEnvironmentVariableType.PLAINTEXT,
                        value: this.account,
                    },
                    REPOSITORY_URI_NGINX: {
                        type: codeBuild.BuildEnvironmentVariableType.PLAINTEXT,
                        value: nginxRepository.repositoryUri,
                    },
                    REPOSITORY_URI_PHP: {
                        type: codeBuild.BuildEnvironmentVariableType.PLAINTEXT,
                        value: phpRepository.repositoryUri,
                    },
                    REPOSITORY_URI_WORKER: {
                        type: codeBuild.BuildEnvironmentVariableType.PLAINTEXT,
                        value: workerRepository.repositoryUri,
                    }
                }
            },
            description: `Builds the Docker images for ${this.deploymentEnvironment()}.`,
            projectName: `build-${this.deploymentEnvironment()}-images`,
            vpc,
            subnetSelection: {
                subnetGroupName: 'Application',
                availabilityZones: ['eu-central-1a']
            },
        });

        nginxRepository.grantPullPush(buildProject);
        phpRepository.grantPullPush(buildProject);
        workerRepository.grantPullPush(buildProject);

        const buildArtifact = new codePipeline.Artifact('BuildArtifact')
        pipeline.addStage({
            stageName: 'Build',
            actions: [
                new codePipelineActions.CodeBuildAction({
                    actionName: 'BuildImages',
                    input: sourceArtifact,
                    type: codePipelineActions.CodeBuildActionType.BUILD,
                    project: buildProject,
                    outputs: [buildArtifact],
                }),
            ],
        });

        pipeline.addStage({
            stageName: 'Deploy',
            actions: [
                new codePipelineActions.EcsDeployAction({
                    runOrder: 1,
                    actionName: 'DeployAppToEcs',
                    input: buildArtifact,
                    service: appService,
                }),
                new codePipelineActions.EcsDeployAction({
                    runOrder: 2,
                    actionName: 'DeployWorkerToEcs',
                    service: workerService,
                    imageFile: buildArtifact.atPath('worker-imagedefinitions.json'),
                }),
            ]
        });

        return pipeline;
    }

    private capitalizedDeploymentEnvironment(): string {
        const deploymentEnvironment = this.deploymentEnvironment();

        return deploymentEnvironment.charAt(0).toUpperCase() + deploymentEnvironment.slice(1);
    }

    protected abstract appEnv(): string;

    protected abstract deploymentEnvironment(): DeploymentEnvironment;

    protected abstract branchName(): string;

    protected abstract awsCredentials(): secretsManager.ISecret;

    protected abstract domainName(): string;
}