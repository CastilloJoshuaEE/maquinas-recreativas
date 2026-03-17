<?php
// backend/helper/RateLimiter.php

class RateLimiter {
    private static $instance = null;
    private $limits = [];
    private $storageFile = __DIR__ . '/../storage/rate_limits.json';
    
    private function __construct() {
        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (file_exists($this->storageFile)) {
            $this->limits = json_decode(file_get_contents($this->storageFile), true) ?: [];
        }
        $this->cleanOldLimits();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function check($key, $maxRequests = 60, $timeWindow = 60) {
        $now = time();
        $windowKey = floor($now / $timeWindow);
        $storageKey = $key . '_' . $windowKey;
        
        if (!isset($this->limits[$storageKey])) {
            $this->limits[$storageKey] = [
                'count' => 1,
                'expires' => ($windowKey + 1) * $timeWindow
            ];
        } else {
            $this->limits[$storageKey]['count']++;
        }
        
        $this->save();
        return $this->limits[$storageKey]['count'] <= $maxRequests;
    }
    
    public function getRemaining($key, $maxRequests = 60, $timeWindow = 60) {
        $now = time();
        $windowKey = floor($now / $timeWindow);
        $storageKey = $key . '_' . $windowKey;
        $used = $this->limits[$storageKey]['count'] ?? 0;
        return max(0, $maxRequests - $used);
    }

    /**
     * Resetea los contadores de una IP específica (usado entre pruebas).
     */
    public function resetKey($key) {
        foreach (array_keys($this->limits) as $storageKey) {
            if (strpos($storageKey, $key . '_') === 0) {
                unset($this->limits[$storageKey]);
            }
        }
        $this->save();
    }

    /**
     * Resetea TODOS los contadores (solo para entorno de pruebas).
     */
    public function resetAll() {
        $this->limits = [];
        $this->save();
    }
    
    private function cleanOldLimits() {
        $now = time();
        foreach ($this->limits as $key => $data) {
            if ($data['expires'] < $now) {
                unset($this->limits[$key]);
            }
        }
    }
    
    private function save() {
        file_put_contents($this->storageFile, json_encode($this->limits));
    }
}