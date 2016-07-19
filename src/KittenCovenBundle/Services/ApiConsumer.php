<?php
/**
 * Created by Merkury (Víctor Moreno)
 * Date: 12/07/2016
 * Time: 21:57
 */

namespace KittenCovenBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

use GuzzleHttp\Client;


/**
 * Class ApiConsumer
 * @package KittenCovenBundle\Services
 */
class ApiConsumer
{
    protected $entityManager;

    protected $rootDir;

    public function __construct(EntityManager $entityManager, $rootDir)
    {
        $this->entityManager = $entityManager;
        $this->rootDir = $rootDir;
        $this->files = ['xbox_one' => 'xbox_grimoire.csv'];
        $this->client = new Client();
    }

    public function summonBlackGrimoire()
    {

        $gameFilesLocation = $this->rootDir . '/../web/secret_basement';

        if (!is_dir($gameFilesLocation)) {
            mkdir($gameFilesLocation, 0755);
        }

        foreach ($this->files as $key => $file) {
            $thisPlatformFolder = $gameFilesLocation . '/' . $key;

            if (!is_dir($thisPlatformFolder)) {
                mkdir($thisPlatformFolder, 0755);
            }

            $dataFile = $this->getSourceData($file);

            $infoBoxArrayKeys = [];
            foreach ($dataFile['data'] as $entry) {
                $client = new Client();

                $arrResult = json_decode($client->request('GET', 'https://en.wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&format=json&titles=' . urlencode($entry[$dataFile['header']]) . '&rvsection=0')->getBody()->getContents(), true);
                $extractRes = json_decode($client->request('GET', 'https://en.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&exintro=&titles=' . urlencode($entry[$dataFile['header']]))->getBody()->getContents(), true);

                $results = [];

                if (!array_key_exists(-1, $extractRes['query']['pages'])) {
                    $results['extract'] = strip_tags($this->getRelevantInformation($extractRes, "extract"));
                }


                if (!array_key_exists(-1, $arrResult['query']['pages'])) {
                    $preParsedResult = trim(preg_replace('/\s+/', ' ', $this->getRelevantInformation($arrResult, "*")));
                    preg_match("/{{Infobox.+ }}/", $preParsedResult, $infoBoxWikiFormat);

                    if (count($infoBoxWikiFormat) > 0) {
                        $infoBox = preg_replace('/(<!---)(.*?)(--->)/', '', strip_tags($infoBoxWikiFormat[0]));

                        $infoBox = preg_replace('/(\s|)\|(\s|)(titlestyle.*?=.*?)(?=(\s|)\|(\s|))/', '', $infoBox);
                        $infoBox = str_replace('{{{{', '{{ {{', $infoBox);
                        $infoBox = str_replace('}}}}', '}} }}', $infoBox);

                        //ORIGINAL ONE (\w+\s=\s)({{Plain list\s\|\s)(.*?)(\s}}|}})
                        //(\w+\s=\s)({{.*?list\|\s)(.*?)(\s}}|}})
                        preg_match_all('/(\w+\s=\s)({{.*?list(\s|)\|(\s|))(.*?)(\s}}|}})/', $infoBox, $lists);

                        foreach($lists[0] as $m){
                            $infoBox = str_replace('| '.$m.' ','',$infoBox);
                        }

                        preg_match_all('/(?<=\|\s)(.*?)(?=\|\s)|(?<=\|\s)(.*?)(?=\}})/', strip_tags($infoBox), $individualValues);

                        $results['infoBox'] = array_merge(
                            $this->makeANiceArray($individualValues[0]),
                            $this->makeANiceArray($lists[0])
                        );

                    }
                }

                if (!empty($results) && (isset($results['infoBox']) && !empty($results['infoBox']))) {
                    $fp = fopen($thisPlatformFolder . '/' . str_replace(' ', '-', $entry[$dataFile['header']]) . '.json', 'w');
                    fwrite($fp, json_encode($results, JSON_PRETTY_PRINT));
                    fclose($fp);
                    print_r('Saved: ' . $entry[$dataFile['header']] . "\n");

                    $infoBoxArrayKeys = array_merge(array_unique($infoBoxArrayKeys), array_keys($results['infoBox']));
                }

                $fp2 = fopen($gameFilesLocation.'/indexes.json', 'w');
                fwrite($fp2, json_encode($infoBoxArrayKeys, JSON_PRETTY_PRINT));
                fclose($fp2);
            }
        }
    }

    /**
     * Function to retrieve all the contents from the CSV file as an associative array
     * @param bool $getFile
     * @return array|mixed
     */
    private function getSourceData($filename, $getFile = false)
    {
        $finder = new Finder();
        $finder->name($filename);

        $files = $finder->files()->in($this->rootDir . '/../web/forbidden_library/')->getIterator();

        $files->rewind();

        if ($files->current() == null) {
            throw new NotFoundResourceException("The data source file is missing. \nPlease add the CSV file to /web/sources/ and configure the file name parameter (source_file_name) in the parameters file.");
        }

        if ($getFile) {
            return $files->current();
        }

        $data_array = array_map('str_getcsv', file($files->current()->getRealPath()));

        $header = $data_array[0][0];

        array_walk($data_array, function (&$a) use ($data_array) {
            $a = array_combine($data_array[0], $a);
        });

        array_shift($data_array);

        return ['data' => $data_array, 'header' => $header];
    }

    /**
     * @param $array
     * @param $indexToSearch
     * @return bool
     */
    private function getRelevantInformation($array, $indexToSearch)
    {
        $res = false;

        array_walk_recursive($array, function ($value, $key) use (&$res, $indexToSearch) {
            if ($key == $indexToSearch) {
                $res = $value;
            }
        });

        return $res;
    }

    /**
     * @param $array
     * @return array
     */
    private function makeANiceArray($array)
    {

        $result = [];

        array_walk($array, function ($value) use (&$result, $array) {
            $explodedString = explode('=', $value);

            if (str_replace(' ', '', $explodedString[0]) == 'released') {
                unset($explodedString[0]);
                $result['released'] = '';
                foreach ($explodedString as $value) {
                    $result['released'] .= $value;
                }
            } else {
                if(str_word_count(trim($explodedString[0])) == 1 && isset($explodedString[1])){
                    $result[trim($explodedString[0])] = trim($explodedString[1]);
                }else{
                    if(!array_key_exists('extra', $result)){
                        $result['extra'] = $explodedString[0];
                    }
                    $result['extra'] .= $explodedString[0];
                }
            }
        });

        return $result;

    }


}