<?php
namespace Api\Framework\Communication\Utility;

class AuthenticationInterface {
    public function getHash($data = []);
    public function checkHash($hash, $data = []);
}