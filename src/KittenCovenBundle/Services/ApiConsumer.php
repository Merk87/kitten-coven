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

        $gameFilesLocation = $this->rootDir.'/../web/secret_basement';

        foreach ($this->files as $key => $file) {
            $thisPlatformFolder = $gameFilesLocation.'/'.$key;
            $gameFile = $this->getSourceData($file);

            foreach($gameFile as $game){
                $client = new Client();
                $res = $client->request('GET','https://en.wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&format=json&titles='.urlencode($game['game_name']).'&rvsection=0');
                $fp = fopen($thisPlatformFolder.'/'.str_replace(' ', '-',$game['game_name']).'.json', 'w');
                fwrite($fp, $res->getBody()->getContents());
                fclose($fp);
                print_r('Saved: '.$game['game_name']."\n");
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

        array_walk($data_array, function (&$a) use ($data_array) {
            $a = array_combine($data_array[0], $a);
        });

        array_shift($data_array);

        return $data_array;
    }


}