<?php
namespace App\Basic\Controller;

use Api\Framework\Basic\Controller\ApiController;
use Api\Framework\Basic\Objects\JSON;

class BasicController extends ApiController {
    public function show() {
        // Create simple JSON response
        $json = new JSON(array('message' => 'Hello world!'));
        return $this->respond($this->getResponse()->setObject($json));
    }
}