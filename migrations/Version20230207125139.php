<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230207125139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE componants (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, name VARCHAR(255) NOT NULL, quantity INT NOT NULL, INDEX IDX_612A62AA4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE componants ADD CONSTRAINT FK_612A62AA4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_component DROP FOREIGN KEY FK_275E17DA4584665A');
        $this->addSql('ALTER TABLE product_component DROP FOREIGN KEY FK_275E17DAE2ABAFFF');
        $this->addSql('DROP TABLE component');
        $this->addSql('DROP TABLE product_component');
        $this->addSql('ALTER TABLE `order` DROP bill, DROP billing_date');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE component (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, provider VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, quantity INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE product_component (product_id INT NOT NULL, component_id INT NOT NULL, INDEX IDX_275E17DA4584665A (product_id), INDEX IDX_275E17DAE2ABAFFF (component_id), PRIMARY KEY(product_id, component_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT FK_275E17DA4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT FK_275E17DAE2ABAFFF FOREIGN KEY (component_id) REFERENCES component (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE componants DROP FOREIGN KEY FK_612A62AA4584665A');
        $this->addSql('DROP TABLE componants');
        $this->addSql('ALTER TABLE `order` ADD bill VARCHAR(255) DEFAULT NULL, ADD billing_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
