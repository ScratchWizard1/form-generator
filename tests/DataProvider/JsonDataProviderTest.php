<?php

declare(strict_types=1);

namespace FormGenerator\Tests\DataProvider;

use FormGenerator\Form;
use PHPUnit\Framework\TestCase;
use FormGenerator\Tests\Traits\StaticUtilityTestTrait;
use FormGenerator\Config\Globals;
use FormGenerator\DataProvider\JsonDataProvider;
use FormGenerator\Components\BasicComponent;
use FormGenerator\Components\HTML;
use FormGenerator\Components\Inputs\Text;
use FormGenerator\Components\Inputs\Password;
use FormGenerator\Components\Inputs\Image;
use FormGenerator\Components\Inputs\Submit;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]

#[\PHPUnit\Framework\Attributes\CoversClass(JsonDataProvider::class)]

#[\PHPUnit\Framework\Attributes\CoversClass(BasicComponent::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(HTML::class)]

#[\PHPUnit\Framework\Attributes\CoversClass(Text::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Password::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Image::class)]

#[\PHPUnit\Framework\Attributes\CoversClass(Submit::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Form::class)]
final class JsonDataProviderTest extends TestCase
{
    use StaticUtilityTestTrait;

    protected string $staticUtilityClass = \FormGenerator\DataProvider\JsonDataProvider::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('jsonProvider')]
    public function testJson(
        string|array $json,
        bool|string $expected
    ): void {

        Form::captchaInit("projectid", "sitekey");
        $form = Form::new("/submit", "simpleForm");
        $form->captcha("action");
        $form->text("username")->id("text")->required();
        $form->password("password")->label("password");
        $form->image("name", "src", "alt");
        $form->submit("send");
        $form->html("<input>")->setAttributes(["type" => "text", "name" => "input"]);
        $form->html("<div>")->class(" div")->name("divName");
        $form->html("<p>p</p>");
        $form->html("</div>");
        $result = Form::json(
            $json,
            true
        );
        if (is_string($expected)) {
            $result = json_decode($result, true);

            $this->assertIsArray($result);

            $expectedKeys = ['error', 'message', 'path', 'id', 'code'];

            foreach ($expectedKeys as $key) {
                $this->assertArrayHasKey($key, $result);
            }
            $this->assertIsBool($result['error']);
            $this->assertIsString($result['message']);
            $this->assertIsString($result['path']);
            $this->assertIsString($result['id']);
            $this->assertIsInt($result['code']);
        } else {
            $result = json_decode($result, true)['form'];
            $this->assertSame($form->getHTML(), $result);
        }
    }

    public static function jsonProvider(): array
    {
        return [
            'valid Json' => [
                '{
                    "security": true,
                    "captcha": {
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                            "type": "text",
                            "attributes": {
                                "name": "username",
                                "id": "text",
                                "required":true
                            }
                        },
                        {
                            "type": "password",
                            "attributes": {
                                "name": "password",
                                "label": "password"
                            }
                        },
                        {
                            "type": "image",
                            "attributes": {
                                "name": "name",
                                "src": "src",
                                "alt": "alt"
                            }
                        },
                        {
                            "type": "submit",
                            "attributes": {
                                "name": "send"
                            }
                        },
                        {
                            "type": "input",
                            "attributes": {
                                "name": "input",
                                "type": "text"
                            }
                        },
                        {
                            "type": "div",
                            "attributes": {
                                "class": "div",
                                "name":"divName"
                            },
                            "children": [
                                {
                                    "type": "p",
                                    "children": ["p"]
                                }
                            ]
                        }
                    ]
                }
                ',
                true,
            ],
            'valid Array' => [
                [
                    "security" => true,
                    "captcha" => [
                        "sitekey" => "sitekey",
                        "projectid" => "projectid",
                        "action" => "action",
                    ],
                    "attributes" => [
                        "id" => "simpleForm",
                        "action" => "/submit",
                    ],
                    "children" => [
                        [
                            "type" => "text",
                            "attributes" => [
                                "name" => "username",
                                "id" => "text",
                                "required" => true
                            ],
                        ],
                        [
                            "type" => "password",
                            "attributes" => [
                                "name" => "password",
                                "label" => "password"
                            ],
                        ],
                        [
                            "type" => "image",
                            "attributes" => [
                                "name" => "name",
                                "src" => "src",
                                "alt" => "alt"
                            ],
                        ],
                        [
                            "type" => "submit",
                            "attributes" => [
                                "name" => "send",
                            ],
                        ],
                        [
                            "type" => "input",
                            "attributes" => [
                                "name" => "input",
                                "type" => "text"
                            ],
                        ],
                        [
                            "type" => "div",
                            "attributes" => [
                                "class" => "div",
                                "name" => "divName"
                            ],
                            "children" => [
                                [
                                    "type" => "p",
                                    "children" => ["p"]
                                ]
                            ]
                        ]
                    ],
                ],
                true
            ],
            'invalid Json Error jE001' => [
                '{
                    "security":true
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE002' => [
                'true',
                "getError",
            ],
            'invalid Json Error jE003' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE004' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": "attributes",
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE005' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE006' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": 5
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE007' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE008' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": 5,
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE009' => [
                '{
                    "security":"string",
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE010' => [
                '{
                    "security":true,
                    "captcha":{},
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE011' => [
                '{
                    "security":true,
                    "captcha":"string",
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE012' => [
                '{
                    "security":true,
                    "captcha":{
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE013' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": 5,
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE014' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE015' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": 5,
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE016' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": 5
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE017' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": 5,
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE018' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password"
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE019' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": "username",
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": 5
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE020' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE021' => [
                '{
                    "security":true,
                    "captcha":{
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                        "type": "text",
                        "attributes": {
                            "name": 5,
                            "id": "text"
                        }
                        },
                        {
                        "type": "password",
                        "attributes": {
                            "name": "password"
                        }
                        },
                        {
                        "type": "submit",
                        "attributes": {
                            "name": "send"
                        }
                        }
                    ]
                }',
                "getError",
            ],
            'invalid Json Error jE022' => [
                '{
                    "security": true,
                    "captcha": {
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                            "type": "text",
                            "attributes": {
                                "name": "username",
                                "id": "text",
                                "required":true
                            }
                        },
                        {
                            "type": "password",
                            "attributes": {
                                "name": "password",
                                "label": "password"
                            }
                        },
                        {
                            "type": "image",
                            "attributes": {
                                "name": "name",
                                "alt": "alt"
                            }
                        },
                        {
                            "type": "submit",
                            "attributes": {
                                "name": "send"
                            }
                        },
                        {
                            "type": "div",
                            "attributes": {
                                "class": "div",
                                "name":"divName"
                            },
                            "children": [
                                {
                                    "type": "p",
                                    "children": ["p"]
                                }
                            ]
                        }
                    ]
                }
                ',
                "getError",
            ],
            'invalid Json Error jE023' => [
                '{
                    "security": true,
                    "captcha": {
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                            "type": "text",
                            "attributes": {
                                "name": "username",
                                "id": "text",
                                "required":true
                            }
                        },
                        {
                            "type": "password",
                            "attributes": {
                                "name": "password",
                                "label": "password"
                            }
                        },
                        {
                            "type": "image",
                            "attributes": {
                                "name": "name",
                                "src": 5,
                                "alt": "alt"
                            }
                        },
                        {
                            "type": "submit",
                            "attributes": {
                                "name": "send"
                            }
                        },
                        {
                            "type": "div",
                            "attributes": {
                                "class": "div",
                                "name":"divName"
                            },
                            "children": [
                                {
                                    "type": "p",
                                    "children": ["p"]
                                }
                            ]
                        }
                    ]
                }
                ',
                "getError",
            ],
            'invalid Json Error jE024' => [
                '{
                    "security": true,
                    "captcha": {
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                            "type": "text",
                            "attributes": {
                                "name": "username",
                                "id": "text",
                                "required":true
                            }
                        },
                        {
                            "type": "password",
                            "attributes": {
                                "name": "password",
                                "label": "password"
                            }
                        },
                        {
                            "type": "image",
                            "attributes": {
                                "name": "name",
                                "src": "src"
                            }
                        },
                        {
                            "type": "submit",
                            "attributes": {
                                "name": "send"
                            }
                        },
                        {
                            "type": "div",
                            "attributes": {
                                "class": "div",
                                "name":"divName"
                            },
                            "children": [
                                {
                                    "type": "p",
                                    "children": ["p"]
                                }
                            ]
                        }
                    ]
                }
                ',
                "getError",
            ],
            'invalid Json Error jE025' => [
                '{
                    "security": true,
                    "captcha": {
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                            "type": "text",
                            "attributes": {
                                "name": "username",
                                "id": "text",
                                "required":true
                            }
                        },
                        {
                            "type": "password",
                            "attributes": {
                                "name": "password",
                                "label": "password"
                            }
                        },
                        {
                            "type": "image",
                            "attributes": {
                                "name": "name",
                                "src": "src",
                                "alt": 5
                            }
                        },
                        {
                            "type": "submit",
                            "attributes": {
                                "name": "send"
                            }
                        },
                        {
                            "type": "div",
                            "attributes": {
                                "class": "div",
                                "name":"divName"
                            },
                            "children": [
                                {
                                    "type": "p",
                                    "children": ["p"]
                                }
                            ]
                        }
                    ]
                }
                ',
                "getError",
            ],
            'invalid Json Error jE026' => [
                '{
                    "security": true,
                    "captcha": {
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                            "type": "text",
                            "attributes": {
                                "name": "username",
                                "id": "text",
                                "required":true
                            }
                        },
                        {
                            "type": "password",
                            "attributes": {
                                "name": "password",
                                "label": "password"
                            }
                        },
                        {
                            "type": "image",
                            "attributes": {
                                "name": "name",
                                "src": "src",
                                "alt": "alt"
                            }
                        },
                        {
                            "type": "submit",
                            "attributes": {
                                "name": "send"
                            }
                        },
                        {
                            "type": "div",
                            "attributes": "string",
                            "children": [
                                {
                                    "type": "p",
                                    "children": ["p"]
                                }
                            ]
                        }
                    ]
                }
                ',
                "getError",
            ],
            'invalid Json Error jE027' => [
                '{
                    "security": true,
                    "captcha": {
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                            "type": "text",
                            "attributes": {
                                "name": "username",
                                "id": "text",
                                "required":true
                            }
                        },
                        {
                            "type": "password",
                            "attributes": {
                                "name": "password",
                                "label": 5
                            }
                        },
                        {
                            "type": "image",
                            "attributes": {
                                "name": "name",
                                "src": "src",
                                "alt": "alt"
                            }
                        },
                        {
                            "type": "submit",
                            "attributes": {
                                "name": "send"
                            }
                        },
                        {
                            "type": "div",
                            "attributes": {
                                "class": "div",
                                "name":"divName"
                            },
                            "children": [
                                {
                                    "type": "p",
                                    "children": ["p"]
                                }
                            ]
                        }
                    ]
                }
                ',
                "getError",
            ],
            'invalid Json Error jE028' => [
                '{
                    "security": true,
                    "captcha": {
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": "string"
                }
                ',
                "getError",
            ],
            'invalid Json Error jE029' => [
                '{
                    "security": true,
                    "captcha": {
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                        "action": "action"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        5,
                        {
                            "type": "password",
                            "attributes": {
                                "name": "password",
                                "label": "password"
                            }
                        },
                        {
                            "type": "image",
                            "attributes": {
                                "name": "name",
                                "src": "src",
                                "alt": "alt"
                            }
                        },
                        {
                            "type": "submit",
                            "attributes": {
                                "name": "send"
                            }
                        },
                        {
                            "type": "div",
                            "attributes": {
                                "class": "div",
                                "name":"divName"
                            },
                            "children": [
                                {
                                    "type": "p",
                                    "children": ["p"]
                                }
                            ]
                        }
                    ]
                }
                ',
                "getError",
            ],
        ];
    }
}
