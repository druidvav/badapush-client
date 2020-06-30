<?php
namespace Druidvav\BadapushClient\Payload;

class CallerPayload implements PayloadInterface
{
    protected $phoneNumber;
    protected $payload;
    protected $externalId;
    protected $group = 'Очередь';
    protected $timezone = null;
    protected $data = [ ];
    protected $resultOptions = [
        'ok' => 'OK!',
    ];

    public static function create($phoneNumber): CallerPayload
    {
        return new CallerPayload($phoneNumber);
    }

    public function __construct($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function isDevelopment(): bool
    {
        return false;
    }

    public function getPayload()
    {
        return [
            'job_group' => $this->group,
            'job_data' => $this->data,
            'job_results' => $this->resultOptions,
            'timezone' => $this->timezone,
        ];
    }

    public function getDeviceId(): string
    {
        return $this->phoneNumber;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * По этой строке вы сможете идентифицировать результат обзвона
     *
     * @param string|null $externalId
     * @return $this
     */
    public function setExternalId(?string $externalId): CallerPayload
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * По этой строке вы можете разбить обзвоны на группы.
     *
     * @param string $group
     * @return $this
     */
    public function setGroup(string $group): CallerPayload
    {
        $this->group = $group;
        return $this;
    }

    public function getResultOptions(): array
    {
        return $this->resultOptions;
    }

    /**
     * Варианты ответа на вопрос в обзвоне. Пример:
     *  [ 'ok' => 'Готово', 'received' => 'Уже забрал посылку' ]
     *
     * @param array $resultOptions
     * @return $this
     */
    public function setResultOptions(array $resultOptions): CallerPayload
    {
        $this->resultOptions = $resultOptions;
        return $this;
    }

    public function getDataFields(): array
    {
        return $this->data;
    }

    /**
     * Ассоциативный массив значений подставляющихся в поля задачи на обзвон
     *
     * @param array $data
     * @return $this
     */
    public function setDataFields(array $data): CallerPayload
    {
        $this->data = $data;
        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): CallerPayload
    {
        $this->timezone = $timezone;
        return $this;
    }
}
