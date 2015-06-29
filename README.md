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
 
### OpenStack
#### Heat Command Line Client
 1. Make file '/heat/heat-zurmo-stack-create' executable
  * `$ chmod +x heat-zurmo-stack-create`
 2. Change Parameters in 'heat-zurmo-stack-create' file to your liking
 3. Create Zurmo Stack with command: `$ source heat-zurmo-stack-create <stack-name>`
  
### Amazon EC2

...

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
