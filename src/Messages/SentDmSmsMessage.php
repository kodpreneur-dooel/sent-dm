<?php

namespace Codepreneur\SentDm\Messages;

use SentDm\RequestOptions;

class SentDmSmsMessage
{
    /**
     * @param  list<string>  $channels
     * @param  array<string, mixed>|null  $template
     * @param  string|list<string>|null  $to
     * @param  RequestOptions|array<string, mixed>|null  $requestOptions
     */
    public function __construct(
        public ?string $text = null,
        public ?array $template = null,
        public string|array|null $to = null,
        public array $channels = ['sms'],
        public ?bool $sandbox = null,
        public ?string $idempotencyKey = null,
        public ?string $xProfileId = null,
        public RequestOptions|array|null $requestOptions = null,
    ) {}

    public static function make(string|self|null $message = null): self
    {
        if ($message instanceof self) {
            return $message;
        }

        return new self(text: $message);
    }

    public static function text(string $message): self
    {
        return new self(text: $message);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function forTemplate(?string $id = null, ?string $name = null, array $parameters = []): self
    {
        return (new self)->template($id, $name, $parameters);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function template(?string $id = null, ?string $name = null, array $parameters = []): self
    {
        $this->template = array_filter([
            'id' => $id,
            'name' => $name,
            'parameters' => $parameters,
        ], fn (mixed $value): bool => $value !== null && $value !== []);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $template
     */
    public function templateArray(array $template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @param  string|list<string>  $to
     */
    public function to(string|array $to): self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @param  list<string>  $channels
     */
    public function channels(array $channels): self
    {
        $this->channels = $channels;

        return $this;
    }

    public function sandbox(bool $sandbox = true): self
    {
        $this->sandbox = $sandbox;

        return $this;
    }

    public function idempotencyKey(?string $idempotencyKey): self
    {
        $this->idempotencyKey = $idempotencyKey;

        return $this;
    }

    public function xProfileId(?string $xProfileId): self
    {
        $this->xProfileId = $xProfileId;

        return $this;
    }

    /**
     * @param  RequestOptions|array<string, mixed>|null  $requestOptions
     */
    public function requestOptions(RequestOptions|array|null $requestOptions): self
    {
        $this->requestOptions = $requestOptions;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toTemplatePayload(?string $text = null): array
    {
        if ($this->template !== null) {
            return $this->template;
        }

        $text ??= $this->text;
        $parameter = config('sent-dm.sms.default_template.parameter', 'message');

        return array_filter([
            'id' => config('sent-dm.sms.default_template.id'),
            'name' => config('sent-dm.sms.default_template.name'),
            'parameters' => filled($text) ? [$parameter => $text] : [],
        ], fn (mixed $value): bool => $value !== null && $value !== []);
    }
}
