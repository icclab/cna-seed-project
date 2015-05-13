$new_discovery_url='https://discovery.etcd.io/new'

$cloud_config_variables = {
	"$preload-docker-images$" => "True",
	"$zurmo-git-branch$" => "logging-dev",
	"$number-of-web-servers$" => "2",
	"$number-of-cache-servers$" => "2",
	"$zurmo-start-fleet-services$" => "True",
	"$enable_discovery_services$" => "True",
	"$download_fleet_files$" => "True"
}

def replace_cloud_config_variables(cloud_config_path)
	contents = File.read(cloud_config_path)
	
	# replace the occurences of the variables defined above
	$cloud_config_variables.each do |key,value|
		contents.gsub! key, value
	end
	
	cloud_config_path.gsub! ".tmpl", ""
	
	#write back to file
	File.open(cloud_config_path, 'w') { |file| file.write(contents) }
end

# To automatically replace the discovery token on 'vagrant up', uncomment
# the lines below:
#
if File.exists?('user-data.tmpl') && File.exists?('user-data-master.tmpl') && ARGV[0].eql?('up')
  require 'open-uri'
 
  etcd_token = open($new_discovery_url).read
  $cloud_config_variables["$etcd_token$"] = etcd_token
 
  replace_cloud_config_variables('user-data.tmpl')
  replace_cloud_config_variables('user-data-master.tmpl')
 
end


#
# coreos-vagrant is configured through a series of configuration
# options (global ruby variables) which are detailed below. To modify
# these options, first copy this file to "config.rb". Then simply
# uncomment the necessary lines, leaving the $, and replace everything
# after the equals sign..

# Size of the CoreOS cluster created by Vagrant
$num_instances=3

# Change basename of the VM
# The default value is "core", which results in VMs named starting with
# "core-01" through to "core-${num_instances}".
#$instance_name_prefix="core"

# Official CoreOS channel from which updates should be downloaded
#$update_channel='alpha'

# Log the serial consoles of CoreOS VMs to log/
# Enable by setting value to true, disable with false
# WARNING: Serial logging is known to result in extremely high CPU usage with
# VirtualBox, so should only be used in debugging situations
#$enable_serial_logging=false

# Enable port forwarding of Docker TCP socket
# Set to the TCP port you want exposed on the *host* machine, default is 2375
# If 2375 is used, Vagrant will auto-increment (e.g. in the case of $num_instances > 1)
# You can then use the docker tool locally by setting the following env var:
#   export DOCKER_HOST='tcp://127.0.0.1:2375'
$expose_docker_tcp=2375

# Enable NFS sharing of your home directory ($HOME) to CoreOS
# It will be mounted at the same path in the VM as on the host.
# Example: /Users/foobar -> /Users/foobar
#$share_home=false

# Customize VMs
#$vm_gui = false
#$vm_memory = 1024
#$vm_cpus = 1
