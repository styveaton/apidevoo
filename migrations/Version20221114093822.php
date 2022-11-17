<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221114093822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vitrine ADD theme_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vitrine ADD CONSTRAINT FK_BE56502859027487 FOREIGN KEY (theme_id) REFERENCES Theme (id)');
        $this->addSql('CREATE INDEX IDX_BE56502859027487 ON vitrine (theme_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Vitrine DROP FOREIGN KEY FK_BE56502859027487');
        $this->addSql('DROP INDEX IDX_BE56502859027487 ON Vitrine');
        $this->addSql('ALTER TABLE Vitrine DROP theme_id');
    }
}
