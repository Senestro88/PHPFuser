<?php

namespace PHPFuser;

use \PHPFuser\Utils;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @author Senestro
 */
class CommandExecutor {
    // PRIVATE VARIABLE
    // PUBLIC VARIABLES
    // PUBLIC METHODS

    /**
     * Prevent the constructor from being initialized
     */
    private function __construct() {
    }

    /**
     * Executes a command via popen and retrieves its output as a string.
     *
     * This method attempts to open a process handle to the specified command
     * using `popen`, reads the output, and returns it as a string. It ensures
     * that the provided command is non-empty and that the `popen` function is
     * available before proceeding.
     *
     * @param string $command The command to execute.
     * @return string The output of the command, or an empty string if an error occurred.
     */
    public static function usePopen(string $command): string {
        // Initialize the output as an empty string
        $output = "";
        // Ensure the command is a non-empty string and `popen` is available
        if (Utils::isNotEmptyString($command) && function_exists('popen')) {
            // Attempt to open a process handle to the command
            $handle = @popen($command, 'r');
            // Check if the handle is a valid resource
            if (Utils::isResource($handle)) {
                // Read the output from the process
                $content = @stream_get_contents($handle);
                // Ensure the content is a valid string
                if (Utils::isString($content)) {
                    $output = $content;
                }
                // Close the process handle
                @pclose($handle);
            }
        }
        // Return the command's output
        return $output;
    }


    /**
     * Executes a command via proc_open and retrieves its output as a string.
     *
     * This method attempts to execute a command using `proc_open`, creates 
     * temporary files for error logging, and reads the command's output from 
     * stdout. It ensures that the provided command is non-empty and that 
     * the `proc_open` function is available before proceeding.
     *
     * @param string $command The command to execute.
     * @return string The output of the command, or an empty string if an error occurred.
     */
    public static function useAdvanceProcOpen(string $command): string {
        // Initialize the output as an empty string
        $output = "";
        // Ensure the command is a non-empty string and `proc_open` is available
        if (Utils::isNotEmptyString($command) && function_exists('proc_open')) {
            // Create a temporary file for error logging
            $errorFilename = Utils::createTemporaryFilename("proc", "proc_open_command_error");
            Utils::delete($errorFilename);
            // Define the descriptor spec for stdin, stdout, and stderr
            $descriptor = [
                0 => ["pipe", "r"], // stdin
                1 => ["pipe", "w"], // stdout
                2 => ["file", $errorFilename, "a"] // stderr
            ];
            // Open a process for the command
            $process = @proc_open($command, $descriptor, $pipes);
            // Check if the process is a valid resource
            if (Utils::isResource($process)) {
                // Close the writable handle connected to child stdin
                if (isset($pipes[0])) {
                    @fclose($pipes[0]);
                }
                // Read from the readable handle connected to child stdout
                if (isset($pipes[1])) {
                    $content = @stream_get_contents($pipes[1]);
                    @fclose($pipes[1]);
                    // Ensure the content is a valid string
                    if (Utils::isString($content)) {
                        $output = $content;
                    }
                }
                // Close the process handle
                @proc_close($process);
            }
        }
        // Return the command's output
        return $output;
    }


    /**
     * Executes a command via exec and retrieves its output as an array.
     *
     * This method attempts to execute a command using `exec`, capturing both 
     * the output and the result code. It ensures that the provided command 
     * is non-empty and that the `exec` function is available before proceeding.
     *
     * @param string $command The command to execute.
     * @return array The output of the command as an array, or an empty array if an error occurred.
     */
    public static function useBasicExec(string $command): array {
        // Initialize the output as an empty array
        $output = [];
        // Ensure the command is a non-empty string and `exec` is available
        if (Utils::isNotEmptyString($command) && function_exists('exec')) {
            // Array to capture the command's output
            $content = [];
            unset($content);
            // Variable to capture the command's result code
            $code = 0;
            // Execute the command
            @exec($command, $content, $code);
            // Ensure the content is a valid array
            if (Utils::isArray($content)) {
                $output = array_values($content); // Flatten and sanitize the array
            }
        }
        // Return the command's output
        return $output;
    }

    /**
     * Executes a command via shell_exec and retrieves its output as a string.
     *
     * This method attempts to execute a command using `shell_exec`, ensuring 
     * that the provided command is non-empty and that the `shell_exec` function 
     * is available before proceeding. It captures and returns the output of 
     * the command.
     *
     * @param string $command The command to execute.
     * @return string The output of the command, or an empty string if an error occurred.
     */
    public static function useBasicShellExec(string $command): string {
        // Initialize the output as an empty string
        $output = "";
        // Ensure the command is a non-empty string and `shell_exec` is available
        if (Utils::isNotEmptyString($command) && function_exists('shell_exec')) {
            // Execute the command and capture the output
            $content = shell_exec($command);
            // Ensure the content is a valid string
            if (Utils::isString($content)) {
                $output = $content;
            }
        }
        // Return the command's output
        return $output;
    }


    /**
     * Executes a command via system and retrieves its output as a string.
     *
     * This method attempts to execute a command using `system`, ensuring that 
     * the provided command is non-empty and that the `system` function is 
     * available before proceeding. It captures and returns the output of the 
     * command along with the result code.
     *
     * @param string $command The command to execute.
     * @return string The output of the command, or an empty string if an error occurred.
     */
    public static function useSystem(string $command): string {
        // Initialize the output as an empty string
        $output = "";
        // Ensure the command is a non-empty string and `system` is available
        if (Utils::isNotEmptyString($command) && function_exists('system')) {
            // Variable to capture the command's result code
            $code = 0;
            // Execute the command and capture the output
            $content = system($command, $code);
            // Ensure the content is a valid string
            if (Utils::isString($content)) {
                $output = $content;
            }
        }
        // Return the command's output
        return $output;
    }


    /**
     * Executes a command via passthru and retrieves its output as a string.
     *
     * This method attempts to execute a command using `passthru`, ensuring that 
     * the provided command is non-empty and that the `passthru` function is 
     * available before proceeding. It captures the output through output buffering
     * and returns the result as a string.
     *
     * @param string $command The command to execute.
     * @return string The output of the command, or an empty string if an error occurred.
     */
    public static function usePassthru(string $command): string {
        // Initialize the output as an empty string
        $output = "";
        // Ensure the command is a non-empty string and `passthru` is available
        if (Utils::isNotEmptyString($command) && function_exists('passthru')) {
            // Variable to capture the command's result code
            $code = 0;
            // Start output buffering
            ob_start();
            // Execute the command and capture its output
            passthru($command, $code);
            // Get the buffered content
            $content = ob_get_contents();
            // Ensure the content is a valid string
            if (Utils::isString($content)) {
                $output = $content;
            }
            // Clean and end the output buffer
            ob_end_clean();
        }
        // Return the command's output
        return $output;
    }

    /**
     * Executes a command using Symfony's Process component.
     *
     * @param array $command An array of command arguments. Example: ['ls', '-l']
     * @param string|null $cwd The working directory for the process. If null, uses the current working directory.
     *
     * @return string The output of the command if successful, or an empty string if the command fails.
     */
    public static function useSymphonyProcess(array $command, ?string $cwd = null): string {
        $result = "";
        // Create a new Process instance with the provided command and optional working directory
        $process = new Process($command, $cwd);
        try {
            // Execute the command and wait for it to finish
            $process->mustRun();
            // Check if the process was successful
            if ($process->isSuccessful()) {
                // Retrieve and store the output of the command
                $result = $process->getOutput();
            }
        } catch (ProcessFailedException $exception) {
            // Handle process failure, but currently no action is taken
            // Optionally, log the exception message if needed for debugging
        }
        // Return the output of the command (empty string if failed)
        return $result;
    }
}
