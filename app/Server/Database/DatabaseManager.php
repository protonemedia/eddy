<?php

namespace App\Server\Database;

interface DatabaseManager
{
    /**
     * Get all databases from the database.
     *
     * @return array<string>
     */
    public function getDatabases(): array;

    /**
     * Get all users from the database.
     *
     * @return array<\App\Server\Database\UserHost>
     */
    public function getUsers(): array;

    /**
     * Get all tables from the database.
     *
     * @return array<string>
     */
    public function getTables(string $database): array;

    /**
     * Create a new user.
     */
    public function createUser(string $name, string $password): bool;

    /**
     * Update the password of the given user.
     */
    public function updateUserPassword(string $name, string $password): bool;

    /**
     * Grant all privileges to the given user on the given database.
     */
    public function grantAllPrivileges(string $user, string $database): bool;

    /**
     * Revoke all privileges to the given user.
     */
    public function revokeAllPrivileges(string $user, string $database): bool;

    /**
     * Create a new database.
     */
    public function createDatabase(string $name, string $charset = 'utf8mb4', string $collation = 'utf8mb4_unicode_ci'): bool;

    /**
     * Drop a database.
     */
    public function dropDatabase(string $name): bool;

    /**
     * Drop a user from the given host.
     */
    public function dropUser(string $user): bool;
}
