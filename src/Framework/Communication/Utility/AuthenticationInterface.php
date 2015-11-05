<?php
namespace Api\Framework\Communication\Utility;

interface AuthenticationInterface {
    public function getHash($data = []);
    public function checkHash($hash, $data = []);
}