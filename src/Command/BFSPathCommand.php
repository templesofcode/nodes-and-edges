<?php

namespace NodesAndEdges\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use NodesAndEdges\Graph;
use NodesAndEdges\BreadthFirstPath;

/**
 * Class BFSPathCommand
 */ 
class BFSPathCommand extends Command
{
    /**
     * The name of the command (the part after "bin/console")
     * @var string
     */
    protected static $defaultName = 'nae:bfs-path';

    protected function configure()
    {
        $this
        ->setDescription('Print connected vertices and find shortest path to given source vertex')
        ->setHelp('This command allows you to load a file that contains graph information and print the shortest path to source vertex')
        ->addArgument(
            'file',
            InputArgument::REQUIRED,
            'The full path of the graph file'
        )->addArgument(
            'sourceVertex',
            InputArgument::REQUIRED,
            'Source vertex'
        );
    }


    /**
     * @param InputInterface $input
     * @param 
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // scope in the argument
        $file = $input->getArgument('file');
        // scope in the argument
        $sourceVertex = (int)$input->getArgument('sourceVertex');
        // build the graph
        $graph = Graph::fromFile($file);
        // create an instance
        $bfs = new BreadthFirstPath($graph, $sourceVertex);
        // init
        $marked = [];
        // iterate over the set of graph vertices
        for ($vertex = 0; $vertex < $graph->getVertices(); $vertex++) {
            // is this connected to the source vertex
            if ($bfs->hasPathTo($vertex)) {
                // print to screen
                $output->write(sprintf(
                    '%d to %d (%d):  ', 
                    $sourceVertex, 
                    $vertex,
                    $bfs->distTo($vertex)
                ));

                foreach ($bfs->pathTo($vertex) as $x) {
                    if ($x == $sourceVertex) {
                        $output->write($x);
                    } else {
                        $output->write("-" . $x);
                    }
                }
                $output->writeln('');


            } else {
                $output->writeln(sprintf(
                    '%d to %d (-):  not connected',
                    $sourceVertex,
                    $vertex
                ));
            }
        }
    }
}