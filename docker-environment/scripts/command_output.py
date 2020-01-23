class CommandOutput:
    output = b''
    error = False

    def __init__(self, output, error):
        self.output = output
        self.error = error
