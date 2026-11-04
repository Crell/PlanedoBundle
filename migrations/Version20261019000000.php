<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Converts the "authors" columns from the removed Doctrine "array" type
 * (PHP-serialized text) to the "json" type.
 *
 * Doctrine DBAL 4, required by Symfony 8 era DoctrineBundle, no longer ships
 * the "array" type. The column contents are converted row by row in
 * postUp()/postDown(), since the serialization formats differ.
 */
final class Version20261019000000 extends AbstractMigration
{
    private const TABLES = [
        'feed' => 'id',
        'feed_entry' => 'link',
    ];

    public function getDescription(): string
    {
        return 'Convert feed.authors and feed_entry.authors from the Doctrine "array" type to "json".';
    }

    public function up(Schema $schema): void
    {
        foreach (self::TABLES as $table => $pk) {
            $this->addSql(sprintf('ALTER TABLE %s ADD authors_json JSON DEFAULT NULL', $table));
        }
    }

    public function postUp(Schema $schema): void
    {
        foreach (self::TABLES as $table => $pk) {
            $rows = $this->connection->fetchAllAssociative(sprintf('SELECT %s AS pk, authors FROM %s', $pk, $table));
            foreach ($rows as $row) {
                $authors = @unserialize((string) $row['authors'], ['allowed_classes' => false]);
                $this->connection->executeStatement(
                    sprintf('UPDATE %s SET authors_json = :authors WHERE %s = :pk', $table, $pk),
                    ['authors' => json_encode(is_array($authors) ? array_values($authors) : [], JSON_THROW_ON_ERROR), 'pk' => $row['pk']],
                );
            }

            $this->connection->executeStatement(sprintf('ALTER TABLE %s DROP authors', $table));
            $this->connection->executeStatement(sprintf('ALTER TABLE %s CHANGE authors_json authors JSON NOT NULL', $table));
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::TABLES as $table => $pk) {
            $this->addSql(sprintf('ALTER TABLE %s ADD authors_array LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'', $table));
        }
    }

    public function postDown(Schema $schema): void
    {
        foreach (self::TABLES as $table => $pk) {
            $rows = $this->connection->fetchAllAssociative(sprintf('SELECT %s AS pk, authors FROM %s', $pk, $table));
            foreach ($rows as $row) {
                $authors = json_decode((string) $row['authors'], true);
                $this->connection->executeStatement(
                    sprintf('UPDATE %s SET authors_array = :authors WHERE %s = :pk', $table, $pk),
                    ['authors' => serialize(is_array($authors) ? $authors : []), 'pk' => $row['pk']],
                );
            }

            $this->connection->executeStatement(sprintf('ALTER TABLE %s DROP authors', $table));
            $this->connection->executeStatement(sprintf('ALTER TABLE %s CHANGE authors_array authors LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'', $table));
        }
    }
}
