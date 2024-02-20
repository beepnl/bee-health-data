<?php

namespace App\Repositories;

class ChartRepository {

    private $labels = [];
    private $datasets = [];

    public function reset()
    {
        $this->labels = [];
        $this->datasets = [];
    }

    public function setLabels(array $labels)
    {
        $this->labels = $labels;
    }
    
    public function addDataset(array $dataset)
    {
        $this->datasets[] = $dataset;
    }
    
    public function toJson()
    {
        $data = [
            "labels" => $this->labels,
            "datasets" => $this->datasets,
        ];
        return json_encode($data);
    }
}
