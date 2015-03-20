# Cloud optimized Zurmo
 
This repository contains the source files for the cloud optimized version of zurmo. This Application is part of the Cloud-Native Application Seed Project where we try to migrate an existing OpenSource Business Application to the cloud.  
More information about the project and the whole cloud-native application research topic can be found on our [blog](http://blog.zhaw.ch/icclab/category/research-approach/themes/cloud-native-applications/).
 
The folders prefixed with *zurmo_* contain the source files for the docker images. The repository also contains files to start a cluster of CoreOS machines on *Vagrant* or *OpenStack*.
 
## Start a cluster
 
The most simple way to try the whole application is to start a cluster.  
We provide both a vagrant and a heat file to start a cluster on your local computer or on your OpenStack cloud:
 
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
 
How to start a cluster with OpenStack/Heat:
 1. Launch a stack on OpenStack
 2. Use the heat template as Template Source. You can directly use the URL:   
 `https://raw.githubusercontent.com/icclab/cna-seed-project/master/heat/coreos-heat.yaml`
 3. Use the URL under 'CoreOS Cluster Discovery URL to generate a discovery url and write it back to this field
 4. Modify the rest of the parameters as you like
 

## Use the built docker images

If you want to use the prebuilt docker images just head over to our [Docker Hub page](https://registry.hub.docker.com/repos/icclabcna/).  
Read the Readme files of the containers to see how to use them.

## Build the docker images on your own
You can clone this repository, modify the source as you like and build the docker images from it.