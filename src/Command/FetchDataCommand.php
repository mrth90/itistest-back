<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Controller\UserController;
use App\Controller\HeaderProcessController;


#[AsCommand(
    name: 'FetchDataCommand',
    description: 'Add a short description for your command',
)]
class FetchDataCommand extends Command
{
    protected static $defaultName = 'app:fetch-data'; // Command name

    protected function configure()
    {
        $this
            ->setDescription('Fetches data from API and processes it.')
            ->setHelp('This command allows you to fetch data from the API and process it.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $apiUrl = $_ENV['API_URL'];
        $data = file_get_contents($apiUrl);

        if ($data === false) {
            $output->writeln('<error>Failed to fetch data from API</error>');
            return Command::FAILURE;
        }

        $jsonData = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $output->writeln('<error>Failed to decode JSON from API</error>');
            return Command::FAILURE;
        }

        $this->transformDataToJson($jsonData);
        $this->transformDataToCsv($jsonData);
        $this->summarizeDataToCsv($jsonData);        

        return Command::SUCCESS;
    }
    
    // Create a method that transforms the jsonData to json file and save it
    private function transformDataToJson(array $jsonData): void
    {
        $jsonString = json_encode($jsonData, JSON_PRETTY_PRINT);
        $date = new \DateTime();
        $filename = 'data_' . $date->format('Ymd') . '.json';
        file_put_contents($filename, $jsonString);
        
    }
   
    // Add the additional data to the transformDataToCsv method
    private function transformDataToCsv(array $jsonData): void
    {
        $csvData = [];
        $csvData[] = [
            'id',
            'firstName',
            'lastName',
            'maidenName',
            'age',
            'gender',
            'email',
            'phone',
            'username',
            'password',
            'birthDate',
            'image',
            'bloodGroup',
            'height',
            'weight',
            'eyeColor',
            'hairColor',
            'hairType',
            'ip',
            'address',
            'city',
            'state',
            'stateCode',
            'postalCode',
            'country',
            'macAddress',
            'university',
            'cardExpire',
            'cardNumber',
            'cardType',
            'currency',
            'iban',
            'department',
            'companyName',
            'title',
            'companyAddress',
            'companyCity',
            'companyState',
            'companyStateCode',
            'companyPostalCode',
            'companyCountry',
            'ein',
            'ssn',
            'userAgent',
            'coin',
            'wallet',
            'network',
            'role',
        ];

        foreach ($jsonData['users'] as $user) {
            $csvData[] = [
                $user['id'],
                $user['firstName'],
                $user['lastName'],
                $user['maidenName'],
                $user['age'],
                $user['gender'],
                $user['email'],
                $user['phone'],
                $user['username'],
                $user['password'],
                $user['birthDate'],
                $user['image'],
                $user['bloodGroup'],
                $user['height'],
                $user['weight'],
                $user['eyeColor'],
                $user['hair']['color'],
                $user['hair']['type'],
                $user['ip'],
                $user['address']['address'],
                $user['address']['city'],
                $user['address']['state'],
                $user['address']['stateCode'],
                $user['address']['postalCode'],
                $user['address']['country'],
                $user['macAddress'],
                $user['university'],
                $user['bank']['cardExpire'],
                $user['bank']['cardNumber'],
                $user['bank']['cardType'],
                $user['bank']['currency'],
                $user['bank']['iban'],
                $user['company']['department'],
                $user['company']['name'],
                $user['company']['title'],
                $user['company']['address']['address'],
                $user['company']['address']['city'],
                $user['company']['address']['state'],
                $user['company']['address']['stateCode'],
                $user['company']['address']['postalCode'],
                $user['company']['address']['country'],
                $user['ein'],
                $user['ssn'],
                $user['userAgent'],
                $user['crypto']['coin'],
                $user['crypto']['wallet'],
                $user['crypto']['network'],
                $user['role'],
            ];
        }

        $date = new \DateTime();
        $filename = 'ETL_' . $date->format('Ymd') . '.csv';

        $file = fopen($filename, 'w');
        foreach ($csvData as $row) {
            fputcsv($file, $row);
            $this->saveDataToDatabase($row);
        }
        fclose($file);
    }

    private function summarizeDataToCsv(array $jsonData): void
    {
        $summaryData = [];
        $summaryData[] = [
            'registre',
            count($jsonData['users']),
        ];

        $date = new \DateTime();
        $filename = 'summary_' . $date->format('Ymd') . '.csv';

        $file = fopen($filename, 'w');
        fputcsv($file, $summaryData[0]);
        fclose($file);

        $genderCount = [];
        foreach ($jsonData['users'] as $user) {
            $gender = $user['gender'];
            if (!isset($genderCount[$gender])) {
            $genderCount[$gender] = 0;
            }
            $genderCount[$gender]++;
        }

        $file = fopen($filename, 'a');
        foreach ($genderCount as $gender => $count) {
            fputcsv($file, [$gender, $count]);
        }
        fclose($file);

        $ageGroups = [];
        foreach ($jsonData['users'] as $user) {
            $age = $user['age'];
            $group = floor($age / 10) * 10 . '-' . (floor($age / 10) * 10 + 10);
            if (!isset($ageGroups[$group])) {
            $ageGroups[$group] = [
                'count' => 0,
                'genderCount' => [
                'male' => 0,
                'female' => 0,
                'other' => 0,
                ],
            ];
            }
            $ageGroups[$group]['count']++;
            $gender = $user['gender'];
            $ageGroups[$group]['genderCount'][$gender]++;
        }

        
        $file = fopen($filename, 'a');
        fputcsv($file, []);
        fputcsv($file, ['Age', 'Male', 'Female', 'Other']);
        foreach ($ageGroups as $group => $data) {
            fputcsv($file, [$group, $data['genderCount']['male'], $data['genderCount']['female'], $data['genderCount']['other']]);
        }
        fclose($file);
        
        $cityGenderCount = [];
        foreach ($jsonData['users'] as $user) {
            $city = $user['address']['city'];
            $gender = $user['gender'];
            if (!isset($cityGenderCount[$city])) {
            $cityGenderCount[$city] = [
                'male' => 0,
                'female' => 0,
                'other' => 0,
            ];
            }
            $cityGenderCount[$city][$gender]++;
        }

        $file = fopen($filename, 'a');
        fputcsv($file, []);
        fputcsv($file, ['City', 'Male', 'Female', 'Other']);
        foreach ($cityGenderCount as $city => $genderCount) {
            fputcsv($file, [$city, $genderCount['male'], $genderCount['female'], $genderCount['other']]);
        }
        fclose($file);
    }

    // Add a function to save the header_process in the database and retrieve the auto-incremented id
    private function saveHeaderProcess(array $headerProcessData): int
    {
        // Assuming you have a HeaderProcessController class with a method to save the header_process and retrieve the id
        $headerProcessController = new HeaderProcessController();
       // $id = $headerProcessController->saveHeaderProcess($headerProcessData);
        $id=0;
        return $id;
    }


    // Add a function to save the data to the database using the userController
    private function saveDataToDatabase(array $jsonData): void
    {
        // Assuming you have a UserController class with a method to save data to the database
        $userController = new UserController();
        //$userController->saveUser($jsonData);
    }
   
}
