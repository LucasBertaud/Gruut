<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230204120356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE component (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, provider VARCHAR(255) NOT NULL, stock INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE component_product (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_634685844584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE component_product_component (component_product_id INT NOT NULL, component_id INT NOT NULL, INDEX IDX_92E271235936FEA4 (component_product_id), INDEX IDX_92E27123E2ABAFFF (component_id), PRIMARY KEY(component_product_id, component_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE component_product ADD CONSTRAINT FK_634685844584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE component_product_component ADD CONSTRAINT FK_92E271235936FEA4 FOREIGN KEY (component_product_id) REFERENCES component_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE component_product_component ADD CONSTRAINT FK_92E27123E2ABAFFF FOREIGN KEY (component_id) REFERENCES component (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE component_product DROP FOREIGN KEY FK_634685844584665A');
        $this->addSql('ALTER TABLE component_product_component DROP FOREIGN KEY FK_92E271235936FEA4');
        $this->addSql('ALTER TABLE component_product_component DROP FOREIGN KEY FK_92E27123E2ABAFFF');
        $this->addSql('DROP TABLE component');
        $this->addSql('DROP TABLE component_product');
        $this->addSql('DROP TABLE component_product_component');
    }
}
