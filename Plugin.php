<?php namespace Grch\Editor;

use Backend, Event;
use System\Classes\PluginBase;
use Grch\Editor\Console\RefreshStaticPages;
use Illuminate\Contracts\Routing\ResponseFactory;
use Grch\Editor\Classes\Event\ProcessMLFields;
use Grch\Editor\Classes\Exceptions\PluginErrorException;

use Grch\Editor\Behaviors\ConvertToHtml;
use Grch\Editor\Classes\Event\ExtendRainLabBlog;
use Grch\Editor\Classes\Event\ExtendIndicatorNews;
use Grch\Editor\Classes\Event\ExtendLovataGoodNews;
use Grch\Editor\Classes\Event\ExtendRainLabStaticPages;

/**
 * Editor Plugin Information File
 * @package Grch\Editor
 * @author Nick Khaetsky, nick@reazzon.ru
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'grch.editor::lang.plugin.name',
            'description' => 'grch.editor::lang.plugin.description',
            'author' => 'Nick Khaetsky',
            'icon' => 'icon-pencil-square-o',
            'homepage' => 'https://github.com/FlusherDock1/EditorJS'
        ];
    }

    /**
     *
     */
    public function register()
    {
        $this->registerConsoleCommand('editor:refresh.static-pages', RefreshStaticPages::class);
        $this->registerErrorHandler();
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array|void
     */
    public function boot()
    {
        Event::subscribe(ExtendRainLabStaticPages::class);
        Event::subscribe(ProcessMLFields::class);
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'grch.editor.access_settings' => [
                'tab' => 'grch.editor::lang.plugin.name',
                'label' => 'grch.editor::lang.permission.access_settings'
            ],
        ];
    }

    /**
     * Registers settings for this plugin
     *
     * @return array
     */
    public function registerSettings()
    {
        return [];
    }

    /**
     * Registers formWidgets.
     *
     * @return array
     */
    public function registerFormWidgets()
    {
        return [
            'Grch\Editor\FormWidgets\EditorJS' => 'editorjs',
            'Grch\Editor\FormWidgets\MLEditorJS' => 'mleditorjs',
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'editorjs' => [$this, 'convertJsonToHtml'],
                'convertBytes' => [$this, 'convertBytes'],
            ],
        ];
    }

    public function convertJsonToHtml($field)
    {
        return (new ConvertToHtml)->convertJsonToHtml($field);
    }

    /**
     * Converts bytes to more sensible string
     *
     * @param int $bytes
     * @return string
     * @see \File::sizeToString($bytes);
     */
    public function convertBytes($bytes)
    {
        return \File::sizeToString($bytes);
    }

    /**
     * Registers additional blocks for EditorJS
     * @return array
     */
    public function registerEditorBlocks()
    {
        return [
            'paragraph' => [
                'validation' => [
                    'text' => [
                        'type' => 'string',
                        'allowedTags' => 'i,b,u,a[href],span[class],code[class],mark[class]'
                    ]
                ],
                'view' => 'grch.editor::blocks.paragraph'
            ],
            'header' => [
                'settings' => [
                    'class' => 'Header',
                    'shortcut' => 'CMD+SHIFT+H',
                ],
                'validation' => [
                    'text' => [
                        'type' => 'string',
                    ],
                    'level' => [
                        'type' => 'int',
                        'canBeOnly' => [1, 2, 3, 4, 5]
                    ]
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/header.js',
                ],
                'view' => 'grch.editor::blocks.heading'
            ],
            'Marker' => [
                'settings' => [
                    'class' => 'Marker',
                    'shortcut' => 'CMD+SHIFT+M',
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/marker.js',
                ]
            ],
            'image' => [
                'settings' => [
                    'class' => 'ImageTool',
                    'config' => [
                        'endpoints' => [
                            'byFile' => config('app.url') . '/editorjs/plugins/image/uploadFile',
                            'byUrl' => config('app.url') . '/editorjs/plugins/image/fetchUrl',
                        ]
                    ]
                ],
                'validation' => [
                    'file' => [
                        'type' => 'array',
                        'data' => [
                            'url' => [
                                'type' => 'string',
                            ],
                            'thumbnails' => [
                                'type' => 'array',
                                'required' => false,
                                'data' => [
                                    '-' => [
                                        'type' => 'string',
                                    ]
                                ],
                            ]
                        ],
                    ],
                    'caption' => [
                        'type' => 'string'
                    ],
                    'withBorder' => [
                        'type' => 'boolean'
                    ],
                    'withBackground' => [
                        'type' => 'boolean'
                    ],
                    'stretched' => [
                        'type' => 'boolean'
                    ]
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/image.js',
                ],
                'view' => 'grch.editor::blocks.image'
            ],
            'attaches' => [
                'settings' => [
                    'class' => 'AttachesTool',
                    'config' => [
                        'endpoint' => config('app.url') . '/editorjs/plugins/attaches',
                    ]
                ],
                'validation' => [
                    'file' => [
                        'type' => 'array',
                        'data' => [
                            'url' => [
                                'type' => 'string',
                            ],
                            'size' => [
                                'type' => 'int',
                            ],
                            'name' => [
                                'type' => 'string',
                            ],
                            'extension' => [
                                'type' => 'string',
                            ],
                        ]
                    ],
                    'title' => [
                        'type' => 'string',
                    ]
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/attaches.js',
                ],
                'view' => 'grch.editor::blocks.attaches'
            ],
            'linkTool' => [
                'settings' => [
                    'class' => 'LinkTool',
                    'config' => [
                        'endpoint' => '/editorjs/plugins/linktool',
                    ]
                ],
                'validation' => [
                    'link' => [
                        'type' => 'string'
                    ],
                    'meta' => [
                        'type' => 'array',
                        'data' => [
                            'title' => [
                                'type' => 'string',
                            ],
                            'description' => [
                                'type' => 'string',
                            ],
                            'image' => [
                                'type' => 'array',
                                'data' => [
                                    'url' => [
                                        'type' => 'string',
                                    ],
                                ]
                            ]
                        ]
                    ]
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/link.js',
                ],
                'view' => 'grch.editor::blocks.link'
            ],
            'list' => [
                'settings' => [
                    'class' => 'List',
                    'inlineToolbar' => true,
                ],
                'validation' => [
                    'style' => [
                        'type' => 'string',
                        'canBeOnly' =>
                            [
                                0 => 'ordered',
                                1 => 'unordered',
                            ],
                    ],
                    'items' => [
                        'type' => 'array',
                        'data' => [
                            '-' => [
                                'type' => 'string',
                                'allowedTags' => 'i,b,u',
                            ],
                        ],
                    ],
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/list.js',
                ],
                'view' => 'grch.editor::blocks.list'
            ],
            'checklist' => [
                'settings' => [
                    'class' => 'Checklist',
                    'inlineToolbar' => true,
                ],
                'validation' => [
                    'items' => [
                        'type' => 'array',
                        'data' => [
                            '-' => [
                                'type' => 'array',
                                'data' => [
                                    'text' => [
                                        'type' => 'string',
                                        'required' => false
                                    ],
                                    'checked' => [
                                        'type' => 'boolean',
                                        'required' => false
                                    ],
                                ],

                            ],
                        ],
                    ],
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/checklist.js',
                ],
                'view' => 'grch.editor::blocks.checklist'
            ],
            'table' => [
                'settings' => [
                    'class' => 'Table',
                    'inlineToolbar' => true,
                    'config' => [
                        'rows' => 2,
                        'cols' => 3,
                    ],
                ],
                'validation' => [
                    'content' => [
                        'type' => 'array',
                        'data' => [
                            '-' => [
                                'type' => 'array',
                                'data' => [
                                    '-' => [
                                        'type' => 'string',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/table.js',
                ],
                'view' => 'grch.editor::blocks.table'
            ],
            'quote' => [
                'settings' => [
                    'class' => 'Quote',
                    'inlineToolbar' => true,
                    'shortcut' => 'CMD+SHIFT+O',
                    'config' => [
                        'quotePlaceholder' => 'Enter a quote',
                        'captionPlaceholder' => 'Quote\'s author',
                    ],
                ],
                'validation' => [
                    'text' => [
                        'type' => 'string',
                    ],
                    'alignment' => [
                        'type' => 'string',
                    ],
                    'caption' => [
                        'type' => 'string',
                    ],
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/quote.js',
                ],
                'view' => 'grch.editor::blocks.quote'
            ],
            'code' => [
                'settings' => [
                    'class' => 'CodeTool',
                ],
                'validation' => [
                    'code' => [
                        'type' => 'string'
                    ]
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/code.js',
                ],
                'view' => 'grch.editor::blocks.code'
            ],
            'embed' => [
                'settings' => [
                    'class' => 'Embed',
                ],
                'validation' => [
                    'service' => [
                        'type' => 'string'
                    ],
                    'source' => [
                        'type' => 'string'
                    ],
                    'embed' => [
                        'type' => 'string'
                    ],
                    'width' => [
                        'type' => 'int'
                    ],
                    'height' => [
                        'type' => 'int'
                    ],
                    'caption' => [
                        'type' => 'string',
                        'required' => false,
                    ],
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/embed.js',
                ],
                'view' => 'grch.editor::blocks.embed'
            ],
            'raw' => [
                'settings' => [
                    'class' => 'RawTool'
                ],
                'validation' => [
                    'html' => [
                        'type' => 'string',
                        'allowedTags' => '*',
                    ]
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/raw.js',
                ],
                'view' => 'grch.editor::blocks.raw'
            ],
            'delimiter' => [
                'settings' => [
                    'class' => 'Delimiter'
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/delimiter.js',
                ],
                'validation' => [],
                'view' => 'grch.editor::blocks.delimiter'
            ],
            'underline' => [
                'settings' => [
                    'class' => 'Underline'
                ],
                'scripts' => [
                    '/plugins/grch/editor/formwidgets/editorjs/assets/js/tools/underline.js',
                ]
            ]
        ];
    }

    public function registerEditorTunes()
    {
        return [];
    }

    public function registerEditorInlineToolbar()
    {
        return [];
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function registerErrorHandler(): void
    {
        \App::error(function (PluginErrorException $exception) {
            return app(ResponseFactory::class)->make(
                $exception->render(),
                $exception->getCode()
            );
        });
    }
}
