from distutils import spawn
import subprocess
from command_output import CommandOutput

class Command:
    executable = ''

    def __init__(self, executable):
        self.executable = executable

    def get_output(self, command):
        error = False
        try:
            output = subprocess.check_output([self.executable] + command)
        except subprocess.CalledProcessError as e:
            output = e.output
            error = True

        return CommandOutput(output, error)

    def run(self, command):
        return subprocess.call([self.executable] + command) > 0
