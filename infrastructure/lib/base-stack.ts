import * as cdk from '@aws-cdk/core';
import * as ec2 from '@aws-cdk/aws-ec2';
import * as elb from '@aws-cdk/aws-elasticloadbalancingv2';
import * as rds from '@aws-cdk/aws-rds';

export class BaseStack extends cdk.Stack {

  private static vpcCidr = '10.0.0.0/16';

  private static importedKeyPairName = 'mbp-george';

  public static appName = 'bee-health-data'

  public readonly vpc: ec2.Vpc;

  public readonly bastion: ec2.BastionHostLinux;

  public readonly loadBalancer: elb.ApplicationLoadBalancer;

  public readonly database: rds.DatabaseCluster;

  constructor(scope: cdk.Construct, id: string, props?: cdk.StackProps) {
    super(scope, id, props);

    this.vpc = this.createAndConfigureVpc();

    this.bastion = this.createBastion(this.vpc, BaseStack.importedKeyPairName);
    this.database = this.createDatabase(this.vpc);
    this.database.connections.allowFrom(this.bastion, ec2.Port.tcp(5432));
  }

  private createDatabase(vpc: ec2.Vpc): rds.DatabaseCluster {
    return new rds.DatabaseCluster(this, 'Database', {
      engine: rds.DatabaseClusterEngine.auroraPostgres({
        version: rds.AuroraPostgresEngineVersion.VER_12_4
      }),
      instances: 1,
      instanceProps: {
        instanceType: ec2.InstanceType.of(ec2.InstanceClass.BURSTABLE3, ec2.InstanceSize.MEDIUM),
        vpc,
        vpcSubnets: {
          subnetGroupName: 'Data',
        },
      },
      defaultDatabaseName: 'production'
    });
  }

  private createAndConfigureVpc(): ec2.Vpc {
    const vpc = new ec2.Vpc(this, 'Vpc', {
      cidr: BaseStack.vpcCidr,
      maxAzs: 3,
      subnetConfiguration: [{
        name: 'Ingress',
        subnetType: ec2.SubnetType.PUBLIC,
        cidrMask: 24,
      },
        {
          name: 'Egress',
          subnetType: ec2.SubnetType.PUBLIC,
          cidrMask: 24,
        },
        {
          name: 'Application',
          subnetType: ec2.SubnetType.PRIVATE,
          cidrMask: 24,
        },
        {
          name: 'Data',
          subnetType: ec2.SubnetType.ISOLATED,
          cidrMask: 24,
        }],
      natGateways: 1,
      natGatewaySubnets: {
        subnetGroupName: 'Egress',
        availabilityZones: ['eu-central-1a'],
      },
    });

    this.createAndConfigureAcls(vpc);

    return vpc;
  }

  private createAndConfigureAcls(vpc: ec2.Vpc) {
    const ingressAcl = new ec2.NetworkAcl(this, 'IngressAcl', {
      vpc,
      subnetSelection: {
        subnetGroupName: 'Ingress'
      },
      networkAclName: 'production-ingress',
    });

    const egressAcl = new ec2.NetworkAcl(this, 'EgressAcl', {
      vpc,
      subnetSelection: {
        subnetGroupName: 'Egress'
      },
      networkAclName: 'production-egress',
    });

    const applicationAcl = new ec2.NetworkAcl(this, 'ApplicationAcl', {
      vpc,
      subnetSelection: {
        subnetGroupName: 'Application'
      },
      networkAclName: 'production-application',
    });

    const dataAcl = new ec2.NetworkAcl(this, 'DataAcl', {
      vpc,
      subnetSelection: {
        subnetGroupName: 'Data'
      },
      networkAclName: 'production-data',
    });

    ingressAcl.addEntry('IngressPublicHttp', {
      ruleNumber: 1,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.tcpPort(80),
    });

    ingressAcl.addEntry('IngressPublicHttps', {
      ruleNumber: 2,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.tcpPort(443),
    });

    ingressAcl.addEntry('IngressPublicEphemeral', {
      ruleNumber: 3,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.tcpPortRange(1024, 65535),
    });

    ingressAcl.addEntry('IngressAlbToEcs', {
      ruleNumber: 4,
      cidr: ec2.AclCidr.ipv4(BaseStack.vpcCidr),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.tcpPort(8080),
    });

    ingressAcl.addEntry('IngressAlbToEcsEphemeral', {
      ruleNumber: 5,
      cidr: ec2.AclCidr.ipv4(BaseStack.vpcCidr),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.tcpPortRange(1024, 65535),
    });

    applicationAcl.addEntry('ApplicationAlbToEcs', {
      ruleNumber: 1,
      cidr: ec2.AclCidr.ipv4(BaseStack.vpcCidr),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.tcpPort(8080)
    });

    applicationAcl.addEntry('ApplicationAlbToEcsEphemeral', {
      ruleNumber: 2,
      cidr: ec2.AclCidr.ipv4(BaseStack.vpcCidr),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.tcpPortRange(1024, 65535),
    });

    applicationAcl.addEntry('ApplicationEcsToNatHttp', {
      ruleNumber: 3,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.tcpPort(80),
    });

    applicationAcl.addEntry('ApplicationEcsToNatHttps', {
      ruleNumber: 4,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.tcpPort(443),
    });

    applicationAcl.addEntry('ApplicationEcsToNatSsh', {
      ruleNumber: 5,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.tcpPort(22),
    });

    applicationAcl.addEntry('ApplicationEcsToNatNtp', {
      ruleNumber: 6,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.udpPort(123),
    });

    applicationAcl.addEntry('ApplicationEcsToNatEphemeral', {
      ruleNumber: 7,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.tcpPortRange(32768, 61000),
    });

    applicationAcl.addEntry('ApplicationEcsToNatNtpReturn', {
      ruleNumber: 8,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.udpPort(123),
    });

    egressAcl.addEntry('EgressEcsToNatHttp', {
      ruleNumber: 1,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.tcpPort(80),
    });

    egressAcl.addEntry('EgressEcsToNatHttps', {
      ruleNumber: 2,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.tcpPort(443),
    });

    egressAcl.addEntry('EgressEcsToNatSsh', {
      ruleNumber: 3,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.tcpPort(22),
    });

    egressAcl.addEntry('EgressEcsToNatNtp', {
      ruleNumber: 4,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.udpPort(123),
    });

    egressAcl.addEntry('EgressEcsToNatEphemeral', {
      ruleNumber: 5,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.tcpPortRange(32768, 61000),
    });

    egressAcl.addEntry('EgressEcsToNatNtpReturn', {
      ruleNumber: 6,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.udpPort(123),
    });

    egressAcl.addEntry('EgressHttp', {
      ruleNumber: 7,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.tcpPort(80),
    });

    egressAcl.addEntry('EgressHttps', {
      ruleNumber: 8,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.tcpPort(443),
    });

    egressAcl.addEntry('EgressSsh', {
      ruleNumber: 9,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.tcpPort(22),
    });

    egressAcl.addEntry('EgressNtp', {
      ruleNumber: 10,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.udpPort(123),
    });

    egressAcl.addEntry('EgressEphemeral', {
      ruleNumber: 11,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.tcpPortRange(1024, 65535),
    });

    egressAcl.addEntry('EgressNtpReturn', {
      ruleNumber: 12,
      cidr: ec2.AclCidr.anyIpv4(),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.udpPort(123),
    });

    applicationAcl.addEntry('ApplicationEcsToRds', {
      ruleNumber: 9,
      cidr: ec2.AclCidr.ipv4(BaseStack.vpcCidr),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.tcpPort(5432),
    });

    applicationAcl.addEntry('ApplicationEcsToRdsEphemeral', {
      ruleNumber: 10,
      cidr: ec2.AclCidr.ipv4(BaseStack.vpcCidr),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.tcpPortRange(32768, 61000),
    });

    dataAcl.addEntry('DataEcsToRds', {
      ruleNumber: 1,
      cidr: ec2.AclCidr.ipv4(BaseStack.vpcCidr),
      direction: ec2.TrafficDirection.INGRESS,
      traffic: ec2.AclTraffic.tcpPort(5432),
    });

    dataAcl.addEntry('DataEcsToRdsEphemeral', {
      ruleNumber: 2,
      cidr: ec2.AclCidr.ipv4(BaseStack.vpcCidr),
      direction: ec2.TrafficDirection.EGRESS,
      traffic: ec2.AclTraffic.tcpPortRange(32768, 61000),
    });
  }

  private createBastion(vpc: ec2.Vpc, importedSshKeyName: string) {
    const bastion = new ec2.BastionHostLinux(this, 'Bastion', {
      vpc,
      subnetSelection: {
        subnetGroupName: 'Application',
        availabilityZones: ['eu-central-1a']
      },

    });

    bastion.instance.instance.addPropertyOverride('KeyName', importedSshKeyName);

    return bastion;
  }
}


