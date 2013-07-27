<?php
/**
 * Pandoc PHP
 * Copyright (c) Ryan Kadwell <ryan@riaka.ca>
 */

namespace Pandoc;

/**
 * Naive wrapper for haskell's pandoc utility
 *
 * @author Ryan Kadwell <ryan@riaka.ca>
 */
class Pandoc
{
    /**
     * Where is the executable located
     * @var string
     */
    private $executable;

    /**
     * Where to take the content for pandoc from
     * @var string
     */
    private $tmpFile;

    /**
     * List of valid input types
     * @var array
     */
    private $inputFormats = [
        "native",
        "json",
        "markdown",
        "markdown_strict",
        "markdown_phpextra",
        "markdown_github",
        "markdown_mmd",
        "rst",
        "mediawiki",
        "docbook",
        "textile",
        "html",
        "latex"
    ];

    /**
     * List of valid output types
     * @var array
     */
    private $outputFormats = [
        "native",
        "json",
        "docx",
        "odt",
        "epub",
        "epub3",
        "fb2",
        "html",
        "html5",
        "s5",
        "slidy",
        "slideous",
        "dzslides",
        "docbook",
        "opendocument",
        "latex",
        "beamer",
        "context",
        "texinfo",
        "man",
        "markdown",
        "markdown_strict",
        "markdown_phpextra",
        "markdown_github",
        "markdown_mmd",
        "plain",
        "rst",
        "mediawiki",
        "textile",
        "rtf",
        "org",
        "asciidoc"
    ];

    /**
     * Setup path to the pandoc binary
     *
     * @param string $executable Path to the pandoc executable
     */
    public function __construct($executable = null)
    {
        $this->tmpFile = sprintf(
            "%s/%s", sys_get_temp_dir(), uniqid("pandoc")
        );

        // Since we can not validate that the command that they give us is
        // *really* pandoc we will just check that its something. Otherwise
        // we will use the unix which command to find what we are looking
        // for.
        if ( ! $executable) {
            $this->executable = system('which pandoc', $returnVar);
        } else {
            $this->executable = $executable;
            system(sprintf('type %s &>/dev/null', $executable), $returnVar);
        }

        if ($returnVar !== 0) {
            throw new PandocException('Unable to locate pandoc');
        }
    }

    /**
     * Run the conversion from one type to another
     *
     * @param string $from The type we are converting from
     * @param string $to   The type we want to convert the document to
     *
     * @return string
     */
    public function convert($content, $from, $to)
    {
        if ( ! in_array($from, $this->inputFormats)) {
            throw new PandocException(
                sprintf('%s is not a valid input format for pandoc', $to)
            );
        }

        if ( ! in_array($to, $this->outputFormats)) {
            throw new PandocException(
                sprintf('%s is not a valid output format for pandoc', $to)
            );
        }

        file_put_contents($this->tmpFile, $content);

        $command = sprintf(
            '%s --from=%s --to=%s %s',
            $this->executable,
            $from,
            $to,
            $this->tmpFile
        );

        exec($command, $output);

        return implode("\n", $output);
    }

    /**
     * Remove the temporary files that were created
     */
    public function __destruct()
    {
        if (file_exists($this->tmpFile)) {
            @unlink($this->tmpFile);
        }
    }
}