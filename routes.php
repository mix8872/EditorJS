<?php
Route::group(['prefix' => 'editorjs'], function () {
    Route::group([
        'prefix' => 'plugins',
        'middleware' => ['web', \Grch\Editor\Classes\Middlewares\PluginGroupMiddleware::class]
    ], function () {
        Route::any('linktool', \Grch\Editor\Classes\Plugins\LinkTool\Plugin::class);
        Route::any('image/{type}', \Grch\Editor\Classes\Plugins\Image\Plugin::class);
        Route::any('attaches', \Grch\Editor\Classes\Plugins\Attaches\Plugin::class);
    });
});
