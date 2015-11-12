# Cloud-Native Applications Seed Project
 
This repository contains the source files for the cloud optimized version of [Zurmo CRM](http://zurmo.org/). This Application is part of the Cloud-Native Application Seed Project where we try to migrate an existing OpenSource Business Application to the cloud.  
More information about the project and the whole cloud-native application research topic can be found on our [blog](http://blog.zhaw.ch/icclab/category/research-approach/themes/cloud-native-applications/).
 
The folders prefixed with *zurmo_* contain the source files for the docker images. The repository also contains files to start a cluster of CoreOS machines on *Vagrant* or *OpenStack* or *Amazon EC2*.
 
## Start a cluster
 
The most simple way to try the whole application is to start a cluster. (In fact, it is the only way at the moment. Running a single-instance CNA is currently not supported.)
We provide a Vagrant file, an OpenStack Heat template and an EC2 Cloud Formation template to start a cluster on your local computer, on your OpenStack cloud or on public EC2:
 
### Vagrant
 
How to start a cluster with vagrant:

 1. Download and install [Vagrant](https://www.vagrantup.com/downloads.html)
 2. Clone this repository: `git clone https://github.com/icclab/cna-seed-project.git`
 3. Open a terminal and move to the *vagrant* folder
 4. Edit the *Vagrantfile* file if you want to change the number of nodes (VMs) to run
 5. Edit the *user-data-master* file to change parameters for the first VM of the cluster
 6. Edit the *user-data* file to change the parameters for all other VMs of the cluster
 7. run `vagrant up`
 
### Amazon EC2
 1. Login to your Amazon AWS console and make sure you have a registered key pair for accessing machines
 2. Download the AWS CloudFormation template from `https://raw.githubusercontent.com/icclab/cna-seed-project/master/aws/icclab_zurmo_aws.template`
 3. Go to the CloudFormation service and *choose the EU Frankfurt Region* (our Public AMI is only registered there for the moment)
 4. Click on "Create Stack"
 5. Select "Upload a template to Amazon S3", upload the `icclab_zurmo_aws.template` file
 6. Fill out the form configuring the deployment parameters. You will need to at least:
  * Choose a name for you deployed service
  * Grab an etcd discovery token from `https://discovery.etcd.io/new` and paste it in the DiscoveryURL field
  * Enter the exact name by which AWS knows your key pair in the KeyPair field
 7. Follow the next steps of the form all the way to create the stack. This will deploy a configurable number of VMs and an Elastic Load Balancer (ELB).
 8. Clicking on the created stack and selecting the "output" tab in the bottom of the screen will give you the URL at which you'll be able to reach your service once it is initialized (notice: it will take a few minutes to spawn VMs and configure components)
 9. Go to the "EC2" section of the AWS console and click "Running instances". You will see your VMs being spawned.
 10. Select any instance and use its Public IP to access it:
  * Add your key to the ssh-agent (this will allow to login to other VMs): `ssh-add path_to/your_key.pem`
  * Run `ssh -A core@<public_IP>`
 11. You can see the containers and where they run using fleetctl:
  * run `fleetctl list-units`
 12. An instance of Kibana is accessible to see the monitoring of the system:
  * Run: ```export kibana_priv=`fleetctl list-units | grep "zurmo_kibana@7000.service" | grep -o -e "[0-9]\{1,3\}\.[0-9]\{1,3\}\.[0-9]\{1,3\}\.[0-9]\{1,3\}"`; ssh $kibana_priv 'cat /etc/environment' | grep COREOS_PUBLIC_IPV4```
  * Access the Kibana at COREOS_PUBLIC_IPV4:7000
  * The first time you access Kibana you will have to choose @Timestamp as the 'Time-field name' to be used for time indexing
  * Go to "Dashboard" and click "Load saved dashboard", choose the dashboard called "zurmo_dashboard_req_rate" 
 13. Access the application at the URL provided from the ELB, you can use 'jim' 'jim' as username and password


### OpenStack
#### Heat Command Line Client
 1. Make file '/heat/heat-zurmo-stack-create' executable
  * `$ chmod +x heat-zurmo-stack-create`
 2. Change Parameters in 'heat-zurmo-stack-create' file to your liking
 3. Create Zurmo Stack with command: `$ source heat-zurmo-stack-create <stack-name>`
  


#### Horizon Dashboard
How to start a cluster with OpenStack/Heat:

 1. Launch a stack on OpenStack
 2. Use the heat template as Template Source. You can directly use the URL:   
 `https://raw.githubusercontent.com/icclab/cna-seed-project/master/heat/coreos-heat.yaml`
 3. Use the URL under *CoreOS Cluster Discovery URL* to generate a discovery url and write it back to this field
 4. Modify the rest of the parameters as you like
 
##### Heat Template Parameters
- **Flavor**:
 - Type of instance (flavor) to be used
 - default: m1.medium
- **Image**: 
 - Name of image to use
 - default: CoreOS-stable-557.2.0
- **Public Net ID**:
 - ID of public network for which floating IP addresses will be allocated
- **Private Net ID**:
 - ID of private network into which servers get deployed
- **Private Subnet ID**:
 - ID of private sub network into which servers get deployed
- **Key Name CoreOS**:
 - Name of key-pair to be used for the CoreOS VMs
- **CoreOS Cluster Discovery URL**:
 - URL of the Cluster-Discovery URL
- **Preload Docker Images**:
 - If set to true, downloads all necessary docker images when cluster starts
- **Number of Web Servers**:
 - Number of Apache / Zurmo Servers
- **Number of Cache Servers**:
 - Number of Memcached Servers
- **Zurmo Git Branch**:
 - Git Branch / Version of Cloud-Enabled Zurmo to use
 - default: master
- **Zurmo Start Fleet Services**:
 - If set to true, starts all zurmo services. If set to false, only downloads fleet unit-files (for testing/dev purposes

## Use the built docker images

If you want to use the prebuilt docker images just head over to our [Docker Hub page](https://registry.hub.docker.com/repos/icclabcna/).  
Please read the Readme files of the containers to see how to use them.

## Build the docker images on your own
You can clone this repository, modify the source as you like and build the docker images from it.
