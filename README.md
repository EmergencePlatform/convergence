# Convergence

Convergence is an [Emergence](http://emr.ge) based website used to manage other Emergence deployments. Easily create and update deployments on one or multiple hosts.

## Installation

Convergence is itself an Emergence website which can be used as a standalone website or can be used as the parent of another Emergence site. Follow the standard [Emergence + Github setup process](http://emr.ge/docs/setup) to create your server and then create a new instance using this repository as its source. Once finished follow these steps to complete setup:

1. Create Convergence Job sync cronjob

Create a new cron.d file
`touch /etc/cron.d/convergence`

Add the following to that file, swapping out HANDLE with the handle of the site hosting convergence.
`* * * * * root echo "Convergence\Job::syncActiveJobs();" | /usr/local/bin/emergence-shell HANDLE > /dev/null`

2. Set up initial Host
For the deployment process to work correctly, Convergence must have at least one available Host in its database. Navigate your browser to `/hosts/create` to create your first host. The Hostname field represents a hostname where your Emergence server can be reached, and the API Username and API Key are the login credentials used to access the Emergence manager at port 9083.
