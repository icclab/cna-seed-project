cd /tmp
wget https://raw.githubusercontent.com/icclab/cna-seed-project/master/init/fleet-files
wget -i fleet-files
for unit in $(find . -name "*.service" ); do grep -o -e "icclabcna/.*:" $unit; done | sort | uniq > units.txt
cat units.txt | xargs -I \{\} docker pull \{\}master
sudo systemctl stop etcd2
sudo rm -rf /var/lib/etcd2/*
sudo rm -f /etc/systemd/system/etcd*
sudo rm /etc/machine-id
sudo rm -rf /var/log/*
history -c
