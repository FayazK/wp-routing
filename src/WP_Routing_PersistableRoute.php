<?php

/**
 * Class WP_Routing_PersistableRoute
 *
 * An extension of the WP_Routing_Route class to allow meta information about a route to be persisted to the database.
 */
class WP_Routing_PersistableRoute extends WP_Routing_Route
{
    /**
     * @var null|tad_Option An instance of the options wrapper class.
     */
    protected static $option = null;
    /**
     * All the meta information about all the routes will be stored to an array like value in the database. This is the `option_name`.
     */
    const OPTION_ID = '__wp_routing_routes_meta';

    /**
     * @param tad_FunctionsAdapterInterface $f
     * @param tad_Option $option
     */
    public function __construct(tad_FunctionsAdapterInterface $f = null, tad_Option $option = null)
    {
        if (is_null($option)) {
            $option = tad_Option::on(self::OPTION_ID);
        }
        $this->option = $option;
        parent::__construct($f);
    }

    /**
     * Override of the parent method to hook in the route generation process at a class level (in place of using the WP hook).
     *
     * @param string $routeId
     * @param array $args
     */
    protected  static function actOnRoute($routeId, Array $args)
    {
        // if the route should not be persisted return
        if (!isset($args['shouldBePersisted']) or !$args['shouldBePersisted']) {
            return;
        }

        // if the route title is not set return
        if (!isset($args['title']) or !is_string($args['title'])) {
            return;
        }

        // if the route permalink is not set return
        if (!isset($args['permalink']) or !is_string($args['permalink']) or !preg_match("/[\\/\\w]*/ui", $args['permalink'])) {
            return;
        }

        // persist the route using the id as the key and storing the title and the permalink
        self::$option->setValue($routeId, array('title' => $args['title'], 'permalink' => $args['permalink']));
    }

    /**
     * Sets the `permalink` key for the route starting from the path.
     *
     * A `path` specified in the route like `/^hello$/` will set the route permalink to `hello`.
     *
     * @param $patterns
     */
    protected function replacePatterns($patterns){
        parent::replacePatterns($patterns);
        // set the permalink to something like path
        // do not use the '/'
        if(!isset($this->args['path'])){
            return;
        }
        $this->args['permalink'] = rtrim(ltrim($this->args['path'], '/^'), '$/');
    }

    /**
     * Sugar method to set the `shouldBePersisted` meta for a route.
     *
     * @return WP_Routing_PersistableRoute $this
     */
    public function shouldBePersisted()
    {
        $this->args['shouldBePersisted'] = true;
        return $this;
    }
}