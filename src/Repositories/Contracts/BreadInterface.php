<?php
namespace PhpSoft\Base\Repositories\Contracts;

interface BreadInterface
{
    public function browse($filter = [], $options = []);
    public function read($id, $filter = [], $options = []);
    public function edit($id, $data);
    public function add($data);
    public function delete($id);
    public function getModel();
}
