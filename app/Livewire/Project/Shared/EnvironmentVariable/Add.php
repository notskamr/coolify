<?php

namespace App\Livewire\Project\Shared\EnvironmentVariable;

use Livewire\Component;

class Add extends Component
{
    public $parameters;
    public bool $is_preview = false;
    public string $key;
    public ?string $value = null;
    public bool $is_build_time = false;

    protected $listeners = ['clearAddEnv' => 'clear'];
    protected $rules = [
        'key' => 'required|string',
        'value' => 'nullable',
        'is_build_time' => 'required|boolean',
    ];
    protected $validationAttributes = [
        'key' => 'key',
        'value' => 'value',
        'is_build_time' => 'build',
    ];

    public function mount()
    {
        $this->parameters = get_route_parameters();
    }

    public function submit()
    {
        $this->validate();
        if (str($this->value)->startsWith('{{') && str($this->value)->endsWith('}}')) {
            $type = str($this->value)->after("{{")->before(".")->value;
            if (!collect(SHARED_VARIABLE_TYPES)->contains($type)) {
                $this->dispatch('error', 'Invalid  shared variable type.', "Valid types are: team, project, environment.");
                return;
            }
        }
        $this->dispatch('saveKey', [
            'key' => $this->key,
            'value' => $this->value,
            'is_build_time' => $this->is_build_time,
            'is_preview' => $this->is_preview,
        ]);
        $this->clear();
    }

    public function clear()
    {
        $this->key = '';
        $this->value = '';
        $this->is_build_time = false;
    }
}
