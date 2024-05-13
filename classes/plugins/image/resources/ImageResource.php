<?php namespace Grch\Editor\Classes\Plugins\Image\Resources;


use Grch\Editor\Classes\Plugins\Attaches\Resources\AttachResource;

/**
 * Class ImageResource
 * @package Grch\Editor\Classes\Plugins\Image\Resources
 */
class ImageResource extends AttachResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'url' => $this->resource
        ];
    }
}
