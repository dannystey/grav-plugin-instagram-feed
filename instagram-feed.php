<?php


namespace Grav\Plugin;

use Grav\Common\Config\Config;
use Grav\Common\GPM\Response;
use Grav\Common\Grav;
use Grav\Common\Page\Collection;
use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class InstagramFeedPlugin
 * @package Grav\Plugin
 */
class InstagramFeedPlugin extends Plugin
{

    /**
     * @var string
     */
    protected $username = null;

    /**
     * @var \Grav\Common\Cache
     */
    protected $cache;

    /**
     * @var string
     */
    protected $cache_id;

    /**
     * @var \Grav\Common\Data\Data
     */
    protected $config;


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Enable the main event we are interested in
        $this->enable([
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigInitialized' => ['onTwigInitialized', 0]
        ]);
    }

    /**
     * Add Twig Function.
     */
    public function onTwigInitialized()
    {
        $this->grav['twig']->twig->addFunction(new \Twig_SimpleFunction('instagram_feed', [$this, 'feed']));
        $this->grav['twig']->twig->addFunction(new \Twig_SimpleFunction('instagram_feed_of', [$this, 'feedOf']));
    }


    /**
     * Add the plugin templates directory to twig paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * gets the feed of a passed user
     *
     * @param string $username
     * @param string $templateFile
     * @return mixed
     */
    public function feedOf($username, $templateFile = 'partials/instagram-feed.html.twig') {
        $this->username = $username;
        return $this->feed($templateFile);
    }

    /**
     * returns a rendered instagram feed
     *
     * @param string $templateFile
     */
    public function feed($templateFile = 'partials/instagram-feed.html.twig') {
        // getting the feed from instagram
        $feed = $this->get($this->username);

        $count = $this->config->get('instagram_feed.count');

        // if you want to know, which fields are in the dataset, comment that following in!
        // dump($feed);

        // render the template
        return $this->grav['twig']->twig()->render($templateFile, compact('feed', 'count'));
    }

    /**
     * get the data of the given user's feed
     *
     * @param null $username
     * @return bool | Object
     */
    private function get($username = null) {

        // set the configurations
        $this->config = $this->mergeConfig($this->grav['page'], TRUE);

        // set the instagram username
        if(!is_null($username)) {
            $this->username = $username;
        }
        else {
            $this->username = $this->config->get('instagram_feed.username');
        }

        // get the Grav Cache instance
        $this->cache = Grav::instance()['cache'];

        // set general cache id
        $this->cache_id = md5(__NAMESPACE__ . '_' . $this->username);

        // first fetch the data from cache
        $result = $this->cache->fetch($this->cache_id);

        // if result is null, try to get it via curl request.
        if(empty($result)) {

            try {
                // creating the feed url
                $feed = 'https://www.instagram.com/'.$this->username.'/?__a=1';
                // using the Grav Response Class for the curl request to instagram.
                $result = Response::get($feed);

                $this->cache->save($this->cache_id, $result, $this->config->get('instagram_feed.cache_expires'));
            }
            catch(\Exception $e) {
                return false;
            }

        }

        return $this->parseData($result);

    }

    /**
     * parse json data;
     *
     * @param string $json
     * @return bool | Object
     */
    private function parseData($json) {
        if(!is_string($json)) return false;

        $data = json_decode($json);

        if($data->graphql->user->username == $this->username) {
            $result = $data->graphql->user->edge_owner_to_timeline_media->edges;
            // bring it back to the old syntax
            return array_map(function ($item) {
                $item->images = (object) array(
                    'thumbnail' => (object) array(
                        'url' => $item->node->thumbnail_src
                    )
                );
                $item->caption = $item->node->edge_media_to_caption->edges[0]->node->text;
                $item->date = $item->node->taken_at_timestamp;
                $item->link = 'https://www.instagram.com/p/' . $item->node->shortcode;
                return $item;
            }, $result);
        }
        else {
            return false;
        }

    }
}
