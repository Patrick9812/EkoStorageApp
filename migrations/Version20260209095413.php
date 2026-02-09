<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209095413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(50) NOT NULL, unit VARCHAR(20) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE invoice_file (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, transaction_id INT NOT NULL, INDEX IDX_8E5A54752FC0CB0F (transaction_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, quantity NUMERIC(10, 3) NOT NULL, price_netto NUMERIC(10, 2) DEFAULT NULL, created_at DATETIME NOT NULL, vat NUMERIC(5, 2) DEFAULT NULL, unit VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, warehouse_id INT NOT NULL, article_id INT NOT NULL, INDEX IDX_723705D1A76ED395 (user_id), INDEX IDX_723705D15080ECDE (warehouse_id), INDEX IDX_723705D17294869C (article_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, full_name VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE warehouse_user (user_id INT NOT NULL, warehouse_id INT NOT NULL, INDEX IDX_52F9D69DA76ED395 (user_id), INDEX IDX_52F9D69D5080ECDE (warehouse_id), PRIMARY KEY (user_id, warehouse_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE warehouse (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE invoice_file ADD CONSTRAINT FK_8E5A54752FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D15080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D17294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE warehouse_user ADD CONSTRAINT FK_52F9D69DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE warehouse_user ADD CONSTRAINT FK_52F9D69D5080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_file DROP FOREIGN KEY FK_8E5A54752FC0CB0F');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D15080ECDE');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D17294869C');
        $this->addSql('ALTER TABLE warehouse_user DROP FOREIGN KEY FK_52F9D69DA76ED395');
        $this->addSql('ALTER TABLE warehouse_user DROP FOREIGN KEY FK_52F9D69D5080ECDE');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE invoice_file');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE warehouse_user');
        $this->addSql('DROP TABLE warehouse');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
