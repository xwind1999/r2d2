import sys
import argparse
from distutils import spawn
from command import Command
from os.path import expanduser
home = expanduser('~')

parser = argparse.ArgumentParser()
parser.add_argument('-d', '--debug', action="store_true", help='enable debug (required for coverage test/infection)')
parser.add_argument('--githook', action="store_true", help='disable interactive mode on docker commands (required for githooks)')
parser.add_argument('task', choices=['start','stop','composer', 'console', 'build', 'phpunit', 'phpstan', 'destroy', 'install', 'psalm', 'infection', 'xdebug', 'phpcs'], help='tasks')
parser.add_argument('command', nargs=argparse.REMAINDER, help='commands to run')
args = parser.parse_args()

php_container = 'r2-d2-php'

docker = Command(spawn.find_executable('docker'))
docker_compose = Command(spawn.find_executable('docker-compose'))

def is_running(service):
    output = docker_compose.get_output(['ps', '--filter', 'status=running', '--services'])
    if output.error or not service in output.output.split():
        return False
    return True

def start(args):
    output = docker_compose.run(['up','-d'])

    # lets set the ssh keys so composer runs without issues
    try:
        known_hosts=open(home + '/.ssh/known_hosts', 'r')
        id_rsa=open(home + '/.ssh/id_rsa', 'r')
        docker.run(['exec', '-it', php_container, '/load-key.sh', known_hosts.read(), id_rsa.read()])
    except:
        print 'Could not load ssh keys into the php container'

    return output

def destroy(args):
    output = docker_compose.run(['down','-v'])
    return output

def run_php_command(args):
    if not is_running(php_container):
        print 'PHP is not running'
        exit(1)
    php_bin = ['php']
    docker_args = ['-it']
    if args['debug']:
        php_bin = ['phpdbg', '-qrr']
    if args['githook']:
        docker_args = ['-i']

    command = ''

    if args['task'] == 'console':
        command = 'bin/console'
    elif args['task'] == 'composer':
        php_bin = []
        command = 'composer'
    elif args['task'] == 'xdebug':
        php_bin = []
        command = '/toggle-xdebug.sh'
    elif args['task'] == 'phpunit':
        command = 'vendor/bin/phpunit'
    elif args['task'] == 'phpcs':
        command = 'vendor/bin/php-cs-fixer'
    elif args['task'] == 'infection':
        command = 'vendor/bin/infection'
    elif args['task'] == 'psalm':
        command = 'vendor/bin/psalm'
        if len(args['command']) == 0:
            args['command'] = ['--show-info=false']
    elif args['task'] == 'psalm':
        command = 'vendor/bin/psalm'
        if len(args['command']) == 0:
            args['command'] = ['--show-info=false']
    elif args['task'] == 'phpstan':
        command = 'vendor/bin/phpstan'
        if len(args['command']) == 0:
            args['command'] = ['analyse', '--level','8','src']

    output = docker.run(['exec'] + docker_args + [php_container] + php_bin + [command] + args['command'])
    return output

def build(args):
    output = docker_compose.run(['build'])
    return output

def stop(args):
    output = docker_compose.run(['down'])
    return output

def install(args):
    print '==== INSTALLING ===='
    build(args)
    start(args)
    run_php_command({'debug': None, 'task': 'composer', 'command': ['install' ,'-n']})
    run_php_command({'debug': None, 'task': 'console', 'command': ['d:m:m', '-n']})
    print '==== INSTALLED ===='

switcher = {
    'start': start,
    'composer': run_php_command,
    'console': run_php_command,
    'build': build,
    'phpstan': run_php_command,
    'phpunit': run_php_command,
    'destroy': destroy,
    'install': install,
    'psalm': run_php_command,
    'infection': run_php_command,
    'xdebug': run_php_command,
    'phpcs': run_php_command,
    'stop': stop,
}

output = switcher.get(args.task)(vars(args))

exit(output)
