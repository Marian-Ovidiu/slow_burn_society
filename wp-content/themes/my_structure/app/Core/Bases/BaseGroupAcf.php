<?php

namespace Core\Bases;

use Core\FieldAcf;

class BaseGroupAcf
{
    protected $groupName;
    protected $groupKey;
    protected $fields = [];
    protected $postId;

    public function __construct($groupName, $postId = null)
    {
        $this->groupName = $groupName;
        $this->postId = $postId ?? $this->guessPostId();
    }

    public function addField($key_field)
    {
        $this->fields[$key_field] = new FieldAcf($key_field);
        return $this->fields[$key_field];
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getGroupName()
    {
        return $this->groupName;
    }

    public function getGroupKey()
    {
        return $this->groupKey;
    }

    public function getPostId()
    {
        return $this->postId;
    }

    public function setPostId($postId)
    {
        $this->postId = $postId;
    }

    public function setGroupKey($key)
    {
        $this->groupKey = $key;
    }

    public function setFields()
    {
        foreach ($this->fields as $key => &$field) {
            if ($this->postId) {
                $value = get_field($field->getKey(), $this->postId, true);
                if ($value !== false) {
                    $field->setValue($value);
                    $this->$key = $field->getValue();
                }
            }
        }
    }

    public static function get($postId = null)
    {
        $instance = new static($postId);
        $instance->setFields();
        return $instance;
    }

    public function __get($name)
    {
        $parts = explode('_', $name);
        $parts = array_map('ucfirst', $parts);
        $method = 'get' . implode('', $parts) . 'Attribute';
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }
    public static function getByLanguage($postType, $acfField = 'lingua', $language = null)
    {
        // $language = $language ?? pll_current_language();
        $language = $language ?? 'it';

        $args = [
            'post_type' => $postType,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => $acfField,
                    'value' => $language,
                    'compare' => '='
                ]
            ]
        ];

        $query = new \WP_Query($args);

        $results = [];
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $instance = new static(get_the_ID());
                $instance->setFields();
                $results[] = $instance;
            }
            wp_reset_postdata();
        }

        return $results;
    }
    protected function guessPostId()
    {
        if (str_contains(static::class, 'Prodotto')) {
            return 'option'; // oppure un post ID specifico
        }

        return 'option';
    }
}
