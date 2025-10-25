<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use FetchMeditation\JFT;
use FetchMeditation\JFTSettings;
use FetchMeditation\JFTLanguage;
use FetchMeditation\SPAD;
use FetchMeditation\SPADSettings;
use FetchMeditation\SPADLanguage;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Fetch Meditation API",
    description: "API for fetching daily meditations from Just For Today (JFT) and Spiritual Principle A Day (SPAD) books in multiple languages"
)]
#[OA\Server(
    url: "https://fetch-laravel-test-main-bh3tju.laravel.cloud",
    description: "Laravel Cloud production server"
)]
#[OA\Server(
    url: "http://127.0.0.1:8000",
    description: "Local development server"
)]
#[OA\Tag(
    name: "Meditations",
    description: "Daily meditation content"
)]
class MeditationController extends Controller
{
    /**
     * Format meditation data for response
     */
    private function formatMeditation($data, $returnJson = false)
    {
        if ($returnJson || request()->wantsJson()) {
            return response($data->toJson())->header('Content-Type', 'application/json');
        }

        $parts = collect([
            $data->date ?? null,
            $data->title ?? null,
            $data->page ?? null,
            $data->quote ?? null,
            $data->source ?? null,
            ...collect($data->content ?? [])->map(fn($p) => strip_tags(html_entity_decode($p))),
            $data->thought ?? null,
            $data->copyright ?? null
        ])
            ->filter(fn($part) => !empty(trim($part)))
            ->values();
        return $parts->join('<br /><br />');
    }

    #[OA\Get(
        path: "/",
        summary: "List available meditation books and languages",
        tags: ["Meditations"],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of available books and languages",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "jft",
                            properties: [
                                new OA\Property(property: "name", type: "string", example: "Just For Today"),
                                new OA\Property(
                                    property: "languages",
                                    type: "object",
                                    example: ["english" => "English", "spanish" => "Spanish"]
                                ),
                                new OA\Property(property: "base_url", type: "string", example: "/jft")
                            ],
                            type: "object"
                        ),
                        new OA\Property(
                            property: "spad",
                            properties: [
                                new OA\Property(property: "name", type: "string", example: "Spiritual Principle A Day"),
                                new OA\Property(
                                    property: "languages",
                                    type: "object",
                                    example: ["english" => "English", "german" => "German"]
                                ),
                                new OA\Property(property: "base_url", type: "string", example: "/spad")
                            ],
                            type: "object"
                        )
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function index(Request $request)
    {
        $availableBooks = [
            'jft' => [
                'name' => 'Just For Today',
                'languages' => [
                    'english' => 'English',
                    'spanish' => 'Spanish',
                    'french' => 'French',
                    'german' => 'German',
                    'italian' => 'Italian',
                    'japanese' => 'Japanese',
                    'portuguese' => 'Portuguese (BR)',
                    'portuguese-pt' => 'Portuguese (PT)',
                    'russian' => 'Russian',
                    'swedish' => 'Swedish',
                ],
                'base_url' => '/jft'
            ],
            'spad' => [
                'name' => 'Spiritual Principle A Day',
                'languages' => [
                    'english' => 'English',
                    'german' => 'German',
                ],
                'base_url' => '/spad'
            ]
        ];

        if ($request->wantsJson() || $request->query('json')) {
            return response()->json($availableBooks);
        }

        $html = '<h1>Available Meditation Books</h1>';
        foreach ($availableBooks as $key => $book) {
            $html .= "<h2>{$book['name']} ({$key})</h2>";
            $html .= "<p>Available languages:</p><ul>";
            foreach ($book['languages'] as $code => $name) {
                $html .= "<li><a href=\"{$book['base_url']}/{$code}\">{$name}</a></li>";
            }
            $html .= "</ul>";
        }

        return $html;
    }

    #[OA\Get(
        path: "/jft",
        summary: "Get Just For Today meditation (English)",
        tags: ["Meditations"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Today's JFT meditation",
                content: [
                    new OA\MediaType(
                        mediaType: "application/json",
                        schema: new OA\Schema(
                            properties: [
                                new OA\Property(property: "date", type: "string", example: "October 25"),
                                new OA\Property(property: "title", type: "string", example: "Meditation Title"),
                                new OA\Property(property: "page", type: "string", example: "Page 299"),
                                new OA\Property(property: "quote", type: "string", example: "Opening quote"),
                                new OA\Property(property: "source", type: "string", example: "Quote source"),
                                new OA\Property(
                                    property: "content",
                                    type: "array",
                                    items: new OA\Items(type: "string")
                                ),
                                new OA\Property(property: "thought", type: "string", example: "Just for today..."),
                                new OA\Property(property: "copyright", type: "string")
                            ],
                            type: "object"
                        )
                    ),
                    new OA\MediaType(
                        mediaType: "text/html",
                        schema: new OA\Schema(type: "string")
                    )
                ]
            )
        ]
    )]
    public function jft(Request $request)
    {
        $settings = new JFTSettings(JFTLanguage::English);
        $jft = JFT::getInstance($settings);
        $data = $jft->fetch();
        return $this->formatMeditation($data, $request->query('json'));
    }

    #[OA\Get(
        path: "/jft/{language}",
        summary: "Get Just For Today meditation in specified language",
        tags: ["Meditations"],
        parameters: [
            new OA\Parameter(
                name: "language",
                description: "Language code (english, spanish, french, german, italian, japanese, portuguese, portuguese-pt, russian, swedish)",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    enum: ["english", "spanish", "french", "german", "italian", "japanese", "portuguese", "portuguese-pt", "russian", "swedish"]
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Today's JFT meditation in the specified language"
            ),
            new OA\Response(
                response: 404,
                description: "Language not supported"
            )
        ]
    )]
    public function jftLanguage(Request $request, string $language)
    {
        $langMap = [
            'english' => JFTLanguage::English,
            'spanish' => JFTLanguage::Spanish,
            'french' => JFTLanguage::French,
            'german' => JFTLanguage::German,
            'italian' => JFTLanguage::Italian,
            'japanese' => JFTLanguage::Japanese,
            'portuguese' => JFTLanguage::Portuguese,
            'portuguese-pt' => JFTLanguage::PortuguesePT,
            'russian' => JFTLanguage::Russian,
            'swedish' => JFTLanguage::Swedish,
        ];

        $language = strtolower($language);
        if (!isset($langMap[$language])) {
            abort(404, 'Language not supported');
        }

        $settings = new JFTSettings($langMap[$language]);
        $jft = JFT::getInstance($settings);
        $data = $jft->fetch();

        return $this->formatMeditation($data, $request->query('json'));
    }

    #[OA\Get(
        path: "/spad",
        summary: "Get Spiritual Principle A Day meditation (English)",
        tags: ["Meditations"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Today's SPAD meditation",
                content: [
                    new OA\MediaType(
                        mediaType: "application/json",
                        schema: new OA\Schema(
                            properties: [
                                new OA\Property(property: "date", type: "string", example: "October 25"),
                                new OA\Property(property: "title", type: "string", example: "Principle Title"),
                                new OA\Property(
                                    property: "content",
                                    type: "array",
                                    items: new OA\Items(type: "string")
                                ),
                                new OA\Property(property: "copyright", type: "string")
                            ],
                            type: "object"
                        )
                    ),
                    new OA\MediaType(
                        mediaType: "text/html",
                        schema: new OA\Schema(type: "string")
                    )
                ]
            )
        ]
    )]
    public function spad(Request $request)
    {
        $settings = new SPADSettings(SPADLanguage::English);
        $spad = SPAD::getInstance($settings);
        $meditation = $spad->fetch();

        return $this->formatMeditation($meditation, $request->query('json'));
    }

    #[OA\Get(
        path: "/spad/{language}",
        summary: "Get Spiritual Principle A Day meditation in specified language",
        tags: ["Meditations"],
        parameters: [
            new OA\Parameter(
                name: "language",
                description: "Language code (english, german)",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    enum: ["english", "german"]
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Today's SPAD meditation in the specified language"
            ),
            new OA\Response(
                response: 404,
                description: "Language not supported"
            )
        ]
    )]
    public function spadLanguage(Request $request, string $language)
    {
        $langMap = [
            'english' => SPADLanguage::English,
            'german' => SPADLanguage::German,
        ];

        $language = strtolower($language);
        if (!isset($langMap[$language])) {
            abort(404, 'Language not supported');
        }

        $settings = new SPADSettings($langMap[$language]);
        $spad = SPAD::getInstance($settings);
        $meditation = $spad->fetch();

        return $this->formatMeditation($meditation, $request->query('json'));
    }
}
