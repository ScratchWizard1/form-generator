<?php

declare(strict_types=1);

namespace FormGenerator\DataProvider;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Components\HTML;
use FormGenerator\Config\Globals;
use FormGenerator\Form;

class JsonDataProvider
{
    /**
     * Private constructor to prevent instantiation.
     * @codeCoverageIgnore
     */
    private function __construct()
    {
        ; // This constructor is private and empty; no code is executed here.
    }

    /**
     * Decodes a JSON string or array into a FormGenerator form or error message.
     *
     * @param array<mixed>|string $json
     * @param bool $returnAsObject
     * @return string|false
     */
    public static function jsonDecoder(
        array|string $json,
        bool $returnAsObject = true
    ): string|false {
        if (is_string($json)) {
            $json = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = self::error(
                    "Invalid JSON string.",
                    'jE001',
                    "root"
                );
                return self::parseError($error, $returnAsObject);
            }
        }
        if (!is_array($json)) {
            $error = self::error(
                "The provided value must be valid JSON.",
                'jE002',
                "root"
            );
            return self::parseError($error, $returnAsObject);
        }
        if (!isset($json['attributes']) || empty($json['attributes'])) {
            $error = self::error(
                "Missing or empty required key attributes.",
                'jE003',
                "root.attributes"
            );
            return self::parseError($error, $returnAsObject);
        }
        if (!is_array($json['attributes'])) {
            $error = self::error(
                "Required key attributes must be an array.",
                'jE004',
                "root.attributes"
            );
            return self::parseError($error, $returnAsObject);
        }
        if (
            !isset($json['attributes']['action'])
            || empty($json['attributes']['action'])
        ) {
            $error = self::error(
                "Missing required action attributes.",
                'jE005',
                "root.attributes.action"
            );
            return self::parseError($error, $returnAsObject);
        }
        if (
            !is_string($json['attributes']['action'])
        ) {
            $error = self::error(
                "Required key action must be a string.",
                'jE006',
                "root.attributes.action"
            );
            return self::parseError($error, $returnAsObject);
        }
        if (
            !isset($json['attributes']['id'])
            || empty($json['attributes']['id'])
        ) {
            $error = self::error(
                "Missing required id attributes.",
                'jE007',
                "root.attributes.id"
            );
            return self::parseError($error, $returnAsObject);
        }
        if (
            !is_string($json['attributes']['id'])
        ) {
            $error = self::error(
                "Required key id must be a string.",
                'jE008',
                "root.attributes.id"
            );
            return self::parseError($error, $returnAsObject);
        }
        Globals::security([
            Globals::toStr($json['attributes']['id']) => false
        ]);
        if (
            isset($json['security'])
            && !empty($json['security'])
        ) {
            if (!is_bool($json['security'])) {
                $error = self::error(
                    "Key security must be a boolean.",
                    'jE009',
                    "root.security"
                );
                return self::parseError($error, $returnAsObject);
            }
            Globals::security([
                Globals::toStr($json['attributes']['id']) => $json['security']
            ]);
        }
        $form = Form::new(
            Globals::toStr($json['attributes']['action']),
            Globals::toStr($json['attributes']['id'])
        );
        $form = self::formJson($json, $form, $form, $json['attributes']['id']);
        if (is_array($form)) {
            /**
             * @var array{error: true, message: string, path:string, id:string, code:int} $form
             */
            return self::parseError($form, $returnAsObject);
        } else {
            if (!$form instanceof Form) {
                $error = self::error(
                    "Unknown error, in final output",
                    'jE030',
                    "root"
                );
                return self::parseError($error, $returnAsObject);
            }
            if (!$returnAsObject) {
                return $form->getHTML();
            }
            return json_encode([
                'error' => false,
                'form' => $form->getHTML()
            ]);
        }
    }
    /**
     * Recursively builds a Form object from a JSON array structure.
     *
     * @param array<mixed> $json
     * @param mixed $form
     * @param Form $parent
     * @param string $path
     * @param string $id
     * @return mixed
     */
    private static function formJson(
        array $json,
        mixed $form,
        Form $parent,
        string $id,
        string $path = 'root'
    ): mixed {

        $inputTypes = [
            'checkbox',
            'color',
            'date',
            'datetime',
            'email',
            'file',
            'hidden',
            'image',
            'month',
            'number',
            'password',
            'radio',
            'range',
            'reset',
            'search',
            'submit',
            'tel',
            'text',
            'textarea',
            'time',
            'url'
        ];

        $voidTags = [
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'param',
            'source',
            'track',
            'wbr'
        ];
        if ($form instanceof Form) {
            if (isset($json['captcha'])) {
                $captchaInfo = $json['captcha'];
                if (empty($captchaInfo)) {
                    return self::error(
                        "Missing or empty required value for key captcha",
                        "jE010",
                        $path . ".captcha"
                    );
                }
                if (!is_array($captchaInfo)) {
                    return self::error(
                        "Required key captcha must be a valid JSON.",
                        "jE011",
                        $path . ".captcha"
                    );
                }
                if (!isset($captchaInfo['sitekey']) || empty($captchaInfo['sitekey'])) {
                    return self::error(
                        "Missing or empty required key sitekey.",
                        "jE012",
                        $path . ".captcha.sitekey"
                    );
                }
                if (!is_string($captchaInfo['sitekey'])) {
                    return self::error(
                        "Required key sitekey must be a string.",
                        "jE013",
                        $path . ".captcha.sitekey"
                    );
                }
                if (!isset($captchaInfo['projectid']) || empty($captchaInfo['projectid'])) {
                    return self::error(
                        "Missing or empty required key projectid.",
                        "jE014",
                        $path . ".captcha.projectid"
                    );
                }
                if (!is_string($captchaInfo['projectid'])) {
                    return self::error(
                        "Required key projectid must be a string.",
                        "jE015",
                        $path . ".captcha.projectid"
                    );
                }
                Form::captchaInit($captchaInfo['projectid'], $captchaInfo['sitekey']);
                if (isset($captchaInfo['action'])) {
                    if (!is_string($captchaInfo['action'])) {
                        return self::error(
                            "Required key action must be a string.",
                            "jE016",
                            $path . ".captcha.action"
                        );
                    }
                    $parent->captcha($captchaInfo['action']);
                } else {
                    $parent->captcha();
                }
            }
        }

        if (isset($json['type'])) {
            if (!is_string($json['type'])) {
                return self::error(
                    "Invalid value format in type. The value must be string.",
                    'jE017',
                    $path . ".type"
                );
            }

            $type = strtolower($json['type']);

            if (in_array($type, $inputTypes)) {
                if (!isset($json['attributes']) || empty($json['attributes'])) {
                    return self::error(
                        "Missing or empty required key attributes.",
                        'jE018',
                        $path . ".attributes"
                    );
                }
                if (!is_array($json['attributes'])) {
                    return self::error(
                        "Required key attributes must be an array.",
                        'jE019',
                        $path . ".attributes"
                    );
                }
                if (empty($json['attributes']['name'])) {
                    return self::error(
                        "Missing name for input.",
                        'jE020',
                        $path . ".attributes.name"
                    );
                }
                if (!is_string($json['attributes']['name'])) {
                    return self::error(
                        "Required key name must be a string.",
                        "jE021",
                        $path . ".attributes.name"
                    );
                }
                if ($type == 'image') {
                    if (!isset($json['attributes']['src']) || empty($json['attributes']['src'])) {
                        return self::error(
                            "Missing or empty required key srt.",
                            "jE022",
                            $path . ".attributes.src"
                        );
                    }
                    if (!is_string($json['attributes']['src'])) {
                        return self::error(
                            "Required key srt must be a string.",
                            "jE023",
                            $path . ".attributes.src"
                        );
                    }
                    if (!isset($json['attributes']['alt']) || empty($json['attributes']['alt'])) {
                        return self::error(
                            "Missing or empty required key alt.",
                            "jE024",
                            $path . ".attributes.alt"
                        );
                    }
                    if (!is_string($json['attributes']['alt'])) {
                        return self::error(
                            "Required key alt must be a string.",
                            "jE025",
                            $path . ".attributes.alt"
                        );
                    }
                    $form = $parent->image(
                        Globals::toStr($json['attributes']['name']),
                        Globals::toStr($json['attributes']['src']),
                        Globals::toStr($json['attributes']['alt'])
                    );
                } else {
                    $form = $parent->$type(Globals::toStr(
                        $json['attributes']['name']
                    ));
                }
            } else {
                if ($form instanceof HTML || $form instanceof Form) {
                    $form = $form->html("<{$type}>");
                }
            }
        }

        if (!empty($json['attributes'])) {
            if (!is_array($json['attributes'])) {
                return self::error(
                    "Attributes must be array.",
                    'jE026',
                    $path . ".attributes"
                );
            }

            foreach ($json['attributes'] as $key => $value) {
                if ($form instanceof Form && in_array($key, ['id', 'action'])) {
                    continue;
                }
                if (!$form instanceof Form && in_array($key, ['label'])) {
                    if (!isset($value) || empty($value) || !is_string($value)) {
                        return self::error(
                            "Label in attributes must be a string.",
                            'jE027',
                            $path . ".attributes." . $key
                        );
                    }
                    if ($form instanceof BasicComponent) {
                        $form->label($value);
                        continue;
                    }
                }

                if (is_bool($value)) {
                    if ($form instanceof BasicComponent || $form instanceof Form) {
                        $form->setAttributes([$key => true]);
                    }
                } else {
                    if ($form instanceof BasicComponent || $form instanceof Form) {
                        if ($key == 'name') {
                            if (method_exists($form, 'name')) {
                                $form->name(Globals::toStr($value));
                            }
                        } else {
                            $form->setAttributes([$key => trim(Globals::toStr($value))]);
                        }
                    }
                }
            }
        }

        if (!empty($json['children'])) {
            if (!is_array($json['children'])) {
                return self::error(
                    "Children must be array.",
                    'jE028',
                    $path . ".children"
                );
            }

            foreach ($json['children'] as $index => $child) {
                if (is_string($child)) {
                    if ($form instanceof HTML || $form instanceof Form) {
                        $form->html($child);
                    }
                    continue;
                }

                if (!is_array($child)) {
                    $child = Globals::esc($child);
                    return self::error(
                        "Invalid child element.",
                        'jE029',
                        $path . "." . $child
                    );
                }

                $result = self::formJson(
                    $child,
                    $form,
                    $parent,
                    $id,
                    $path . ".children[$index]"
                );

                if (is_array($result)) {
                    return $result;
                }
            }
        }

        if (isset($json['type'])) {
            $type = strtolower(Globals::toStr($json['type']));

            $voidTags = array_values(array_unique([...$inputTypes, ...$voidTags]));

            if (!in_array($type, $voidTags)) {
                if ($form instanceof HTML || $form instanceof Form) {
                    $form->html("</{$type}>");
                }
            }
        }

        return $form;
    }


    /**
     * Generates a standardized error array.
     *
     * @param string $message
     * @param string $id
     * @param string $path
     * @return array{error: true, message: string, path:string, id:string, code:int}
     */
    private static function error(
        string $message,
        string $id,
        string $path = "unknown"
    ): array {
        return [
            'error' => true,
            'message' => $message,
            'path' => $path,
            'id' => $id,
            'code' => 400
        ];
    }

    /**
     * Converts a structured error array into either a string
     *
     * @param array{error: true, message: string, path:string, id:string, code:int} $error
     * @param bool $returnAsObject
     * @return string|false
     */
    private static function parseError(
        array $error,
        bool $returnAsObject = true
    ): string|false {
        if (!$returnAsObject) {
            $string = "";
            foreach ($error as $key => $value) {
                if ($key == "error") {
                    continue;
                }
                $string = Globals::toStr($string);
                $string .= Globals::toStr(
                    Globals::toStr($key)
                        . ": "
                        . Globals::toStr($value)
                        . " | "
                );
            }
            return $string;
        }
        return json_encode($error);
    }
}
