<?php

namespace App\Console;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Office;
use Illuminate\Support\Facades\Schema;
use Slim\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateDatabaseCommand extends Command
{
    private App $app;
    private $faker;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->faker = \Faker\Factory::create("fr_FR");
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('db:populate');
        $this->setDescription('Populate database');
    }

    private function insertCompanies(int $number): string
    {
        $faker = $this->faker;
        $string = 'INSERT INTO `companies` (name,phone,email,website,image,created_at,updated_at, head_office_id) VALUES ';
        for ($i = 1; $i <= $number; $i++) {
            $string .= "(\"". $faker->company. "\",\"". $faker->phoneNumber. "\",\"". $faker->email. "\",\"". 
            $faker->url. "\",\"". $faker->imageUrl(). "\", NOW(), NOW(), NULL),";
        }
        return rtrim($string, ",");
    }

    private function insertOffices(int $number): string
    {
        $faker = $this->faker;
        $string = 'INSERT INTO `offices` (name,address,city,zip_code,country,email,phone,company_id,created_at,updated_at) VALUES ';
        for ($i = 1; $i <= $number; $i++) {
            $string .= "(\"".$faker->company. "\",\"". $faker->streetAddress. "\",\"". $faker->city. "\",\"". 
            $faker->postcode. "\",\"". $faker->country. "\",\"". $faker->email. "\",\"". $faker->phoneNumber. "\",".rand(1, 3).", NOW(), NOW()),";
        }
        return rtrim($string, ",");
    }
    
    private function insertEmployees(int $number): string
    {
        $faker = $this->faker;
        $string = 'INSERT INTO `employees` (first_name,last_name,office_id,email,phone,job_title,created_at,updated_at) VALUES ';
        for ($i = 1; $i <= $number; $i++) {
            $string .= "(\"". $faker->firstName. "\",\"". $faker->lastName. "\",". rand(1, 3). ",\"". 
            $faker->email. "\",\"". $faker->phoneNumber()."\",\"". $faker->jobTitle. "\", NOW(), NOW()),";
        }
        return rtrim($string, ",");
    }

    protected function execute(InputInterface $input, OutputInterface $output ): int
    {
        $output->writeln('Populate database...');

        /** @var \Illuminate\Database\Capsule\Manager $db */
        $db = $this->app->getContainer()->get('db');

        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=0");
        $db->getConnection()->statement("TRUNCATE `employees`");
        $db->getConnection()->statement("TRUNCATE `offices`");
        $db->getConnection()->statement("TRUNCATE `companies`");
        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=1");


        $db->getConnection()->statement($this->insertCompanies(3));

        $db->getConnection()->statement($this->insertOffices(3));

        $db->getConnection()->statement($this->insertEmployees(10));

        // si id pair alors head_office_id = 1 sinon head_office_id = 2
        $db->getConnection()->statement("update companies set head_office_id = 1 where id % 2 = 0;");
        $db->getConnection()->statement("update companies set head_office_id = 2 where id % 2 = 1;");

        $output->writeln('Database created successfully!');
        return 0;
    }
}
