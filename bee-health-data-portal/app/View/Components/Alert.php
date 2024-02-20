<?php

namespace App\View\Components;

use Exception;
use Illuminate\View\Component;

class Alert extends Component
{
    public $type = 'info';

    private $availableTypes = [
        'primary',
        'secondary',
        'success',
        'danger',
        'warning',
        'info',
        'light',
        'dark',
    ];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($type=null)
    {
        if(!is_null($type)){
            if(!$this->isAvailableType($type)){
                throw new Exception('This attribute ('.$type.') is not available');
            }
            $this->type = $type;
        }
    }

    private function isAvailableType($value)
    {
        return in_array($value, $this->availableTypes, true);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.alert');
    }
}
