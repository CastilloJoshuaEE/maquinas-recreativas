<?php
/**
 * Trait para inyectar TestDatabase en repositorios sin usar Reflection
 * 
 * @package maquinas_recreativas\Tests
 */

namespace maquinas_recreativas\Tests;

trait TestDatabaseInjectionTrait
{
    /**
     * Inyecta TestDatabase en un repositorio
     */
    protected function injectTestDb(object $repository, TestDatabase $testDb): void
    {
        $reflection = new \ReflectionClass($repository);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($repository, $testDb);
    }
    
    /**
     * Crea un repositorio con TestDatabase inyectado
     */
    protected function createRepository(string $repositoryClass, TestDatabase $testDb): object
    {
        $repository = new $repositoryClass($testDb);
        return $repository;
    }
}