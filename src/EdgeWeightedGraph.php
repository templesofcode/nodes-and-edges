<?php

namespace TemplesOfCode\NodesAndEdges;

use InvalidArgumentException;

/**
 * Class EdgeWeightedGraph
 * @package TemplesOfCode\NodesAndEdges
 */
class EdgeWeightedGraph extends Graph
{
    /**
     * @param Edge $e
     */
    public function addEdge(Edge $e)
    {
        // get one side of edge (a vertex)
        $v = $e->either();
        // get the other side (a vertex)
        $w = $e->other($v);
        // validate the vertex
        Graph::validateVertex($v, $this->vertices);
        // validate the vertex
        Graph::validateVertex($v, $this->vertices);
        // link the edge to v
        $this->adjacencyList[$v][] = $e;
        // link the edge to w
        $this->adjacencyList[$w][] = $e;
        // increment the tracker
        $this->edges++;
    }

    /**
     * Returns all edges in this edge-weighted graph.
     *
     * @return array all edges in this edge-weighted graph, as an iterable
     */
    public function getAllEdges()
    {
        // init
        $allEdges = [];
        // iterate over the set of vertices
        for ($vertex = 0; $vertex < $this->getVertices(); $vertex++) {
            // init
            /** @var array $neighbors */
            $neighbors = $this->adjacencyList[$vertex];
            // iterate over the set of neighbors
            foreach ($neighbors as $neighbor) {
                /** @var Edge $neighbor */
                // get v
                $v = $neighbor->either();
                // get w
                $w = $neighbor->other($v);
                // get weight
                $weight = $neighbor->weight();
                // add to the list
                $allEdges[] = new Edge($v, $w, $weight);
            }
        }
        return $allEdges;
    }

    /**
     * Initializes a graph from the specified file.
     *
     * @param string $filePath
     * @return EdgeWeightedGraph
     * @throws InvalidArgumentException
     */
    public static function fromFile(string $filePath)
    {
        // open the file for reading
        if (!$handle = fopen($filePath, 'r')) {
            throw new InvalidArgumentException('could not open file');
        }
        // parse V and E
        list(
            $vertices,
            $edges
        ) = self::parseGraphVEFromResource($handle);
        // instantiate a new graph
        $graph = new EdgeWeightedGraph($vertices);
        self::buildWeightedEdgesFromHandle($graph, $vertices, $edges, $handle);
        // close the stream
        fclose($handle);
        // return the built graph
        return $graph;
    }

    /**
     * Initializes a new graph that is a deep copy of $g
     *
     * @param EdgeWeightedGraph $g
     * @return EdgeWeightedGraph
     */
    public static function fromGraph(EdgeWeightedGraph $g)
    {
        // get the number of vertices
        $vertices = $g->getVertices();
        // init
        $adjacencyList = [];
        // iterate over the vertices
        for ($vertex = 0; $vertex < $vertices; $vertex++) {
            // get the adjacent vertices
            $neighbors = $g->adjacent($vertex);
            // init
            $myAdjacencyList = [];
            // iterate over them
            foreach ($neighbors as $edge) {
                /** @var Edge $e */
                // get one side of edge (a vertex)
                $v = $edge->either();
                // get the other side (a vertex)
                $w = $edge->other($v);
                // get the weight
                $weight = $edge->weight();
                // create a new edge and set it
                $e = new Edge($v, $w, $weight);
                // add the edge to the list
                $myAdjacencyList[] = $e;
            }
            // set this set
            $adjacencyList[$vertex] = $myAdjacencyList;
        }
        // return the new graph
        return new EdgeWeightedGraph($vertices, $adjacencyList);
    }

    /**
     * @param string $graph
     * @return EdgeWeightedGraph
     */
    public static function fromString(string $graph)
    {
        // parse the lines
        $lines = explode("\n", $graph);
        // extract V and E
        list (
            $vertices,
            $edges
        ) = self::parseGraphVEFromString($lines[0], $lines[1]);
        // instantiate a new graph
        $graph = new EdgeWeightedGraph($vertices);
        // remove first two lines
        $lines = array_slice($lines, 2);
        // process it
        self::buildWeightedEdgesFromString($graph, $vertices, $edges, $lines);
        // return the built graph
        return $graph;
    }

    /**
     * Initializes a random edge-weighted graph with $v vertices and $e edges.
     *
     * @param int $vertices the number of vertices
     * @param int $edges the number of edges
     * @return EdgeWeightedGraph
     */
    public static function fromRandom(int $vertices, int $edges)
    {
        // sanity check
        if ($edges < 0) {
            // not acceptable
            throw new InvalidArgumentException(
                'Number of edges must be non-negative'
            );
        }
        // instantiate a new graph
        $graph = new EdgeWeightedGraph($vertices);
        // init
        $taken = [];
        // iterate over the edges
        for ($i = 0; $i <$edges; $i++) {
            // generate an edge
            do {
                // generate
                $v = mt_rand(0, $vertices);
                // generate
                $w = mt_rand(0, $vertices);
                // check
                $pairTaken = in_array(
                    sprintf('%d-%d', $v, $w),
                    $taken
                );
            } while ($v == $w && !$pairTaken);
            // add to the set
            $taken[] = $pairTaken;
            // generate weight
            $weight = ((float)(mt_rand(0, 100)));
            // create the edge
            $edge = new Edge($v, $w, $weight);
            // add it to the graph
            $graph->addEdge($edge);
        }
        // return the graph
        return $graph;
    }

    /**
     * Initializes an edge-weighted graph from an input stream.
     * The format is the number of $vertices,
     * followed by the number of $edges ,
     * followed by $edges pairs of vertices and edge weights,
     * with each entry separated by whitespace.
     *
     * @param resource $handle the input stream
     * @return EdgeWeightedGraph
     */
    protected static function fromStream($handle)
    {
        // sanity check
        if (!is_resource($handle) || $handle === null) {
            // bad state
            throw new InvalidArgumentException(
                'argument is null'
            );
        }
        // parse V and E
        list(
            $vertices,
            $edges
        ) = self::parseGraphVEFromResource($handle);
        // instantiate a new graph
        $graph = new EdgeWeightedGraph($vertices);
        // read in the edges
        for ($i = 0; $i < $edges; $i++) {
            // fet from source
            $raw = fgets($handle);
            // parse data
            list (
                $v,
                $w,
                $weight
            ) = self::parseEdge($raw, $vertices, true);
            // create the edge
            $edge = new Edge($v, $w, $weight);
            // add it to the graph
            $graph->addEdge($edge);
        }
        // rewind the stream
        rewind($handle);
        // return the built graph
        return $graph;
    }
}
