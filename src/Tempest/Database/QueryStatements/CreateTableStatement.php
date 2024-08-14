<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final class CreateTableStatement implements QueryStatement
{
    public function __construct(
        private readonly string $tableName,
        private array $statements = [],
    ) {
    }

    public function primary(string $name = 'id'): self
    {
        $this->statements[] = new PrimaryKeyStatement($name);

        return $this;
    }

    public function belongsTo(
        string $local,
        string $foreign,
        OnDelete $onDelete = OnDelete::RESTRICT,
        OnUpdate $onUpdate = OnUpdate::NO_ACTION
    ): self {
        $this->statements[] = new BelongsToStatement(
            local: $local,
            foreign: $foreign,
            onDelete: $onDelete,
            onUpdate: $onUpdate,
        );

        return $this;
    }

    public function text(string $name, bool $nullable = false): self
    {
        $this->statements[] = new TextStatement(
            name: $name,
            nullable: $nullable,
        );

        return $this;
    }

    public function varchar(string $name, int $length = 255, bool $nullable = false): self
    {
        $this->statements[] = new VarcharStatement(
            name: $name,
            size: $length,
            nullable: $nullable,
        );

        return $this;
    }

    public function char(string $name, bool $nullable = false): self
    {
        $this->statements[] = new CharStatement(
            name: $name,
            nullable: $nullable,
        );

        return $this;
    }

    public function integer(string $name, bool $unsigned = false, bool $nullable = false): self
    {
        $this->statements[] = new IntegerStatement(
            name: $name,
            unsigned: $unsigned,
            nullable: $nullable,
        );

        return $this;
    }

    public function float(string $name, bool $nullable = false): self
    {
        $this->statements[] = new FloatStatement(
            name: $name,
            nullable: $nullable,
        );

        return $this;
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf(
            'CREATE TABLE %s (%s);',
            $this->tableName,
            implode(
                ', ',
                array_filter(
                    array_map(
                        fn (QueryStatement $queryStatement) => $queryStatement->compile($dialect),
                        $this->statements,
                    ),
                ),
            ),
        );
    }
}