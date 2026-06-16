<?php
declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $errors = [];

    public function __construct(private array $data, private array $rules)
    {
        $this->run();
    }

    private function run(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;
            foreach (explode('|', $ruleString) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $this->apply($field, $value, $name, $param);
            }
        }
    }

    private function apply(string $field, $value, string $rule, ?string $param): void
    {
        $missing = $value === null || $value === '';
        switch ($rule) {
            case 'required':
                if ($missing) {
                    $this->add($field, 'is required');
                }
                break;
            case 'email':
                if (!$missing && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->add($field, 'must be a valid email');
                }
                break;
            case 'numeric':
                if (!$missing && !is_numeric($value)) {
                    $this->add($field, 'must be numeric');
                }
                break;
            case 'integer':
                if (!$missing && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->add($field, 'must be an integer');
                }
                break;
            case 'min':
                if (!$missing && is_numeric($value) && $value < (float) $param) {
                    $this->add($field, "must be at least $param");
                } elseif (!$missing && !is_numeric($value) && mb_strlen((string) $value) < (int) $param) {
                    $this->add($field, "must be at least $param characters");
                }
                break;
            case 'max':
                if (!$missing && is_numeric($value) && $value > (float) $param) {
                    $this->add($field, "must be at most $param");
                } elseif (!$missing && !is_numeric($value) && mb_strlen((string) $value) > (int) $param) {
                    $this->add($field, "must be at most $param characters");
                }
                break;
            case 'in':
                $opts = explode(',', (string) $param);
                if (!$missing && !in_array((string) $value, $opts, true)) {
                    $this->add($field, 'has an invalid value');
                }
                break;
        }
    }

    private function add(string $field, string $msg): void
    {
        $this->errors[$field][] = $msg;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
