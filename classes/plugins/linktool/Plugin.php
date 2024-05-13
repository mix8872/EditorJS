<?php namespace Grch\Editor\Classes\Plugins\LinkTool;

use Illuminate\Http\Request;
use Grch\Editor\Classes\Exceptions\PluginErrorException;
use Grch\Editor\Classes\Plugins\LinkTool\Resources\LinkResource;

/**
 * LinkTool Plugin
 * @package Grch\Editor\Classes\Plugins\LinkTool
 * @author Nick Khaetsky, nick@reazzon.ru
 */
class Plugin
{
    /**
     * LinkTool constructor
     */
    public function __invoke(Request $request)
    {
        $url = $request->get('url');
        if (empty($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new PluginErrorException;
        }

        $link = OpenGraph::fetch($url);
        return new LinkResource($link);
    }
}
