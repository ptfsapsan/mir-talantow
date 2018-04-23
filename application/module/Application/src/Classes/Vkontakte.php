<?php

namespace Application\Classes;

/**
 * Class Vkontakte
 *
 * @package Application\Classes
 */
class Vkontakte
{

    /**
     * @var int
     */
    private $count = -1;
//   private $app_id = 5596897;
//   private $key = 'H8DPIpK7F7sDKvXr9Aak';


    /** Для получения нужно перейти после авторизации по
     * https://oauth.vk.com/authorize?client_id=5596897&scope=groups,wall,offline,photos&redirect_uri=https://oauth.vk.com/blank.html&display=page&v=5.21&response_type=token,
     *
     * @var string
     */
    private $token =
        'adee597d5093d9b030ebabc4ec89638a8da4df4779507cae8b808a3e377687d740c8a0939b434270899d2';
    /**
     * @var string
     */
    private $group_id = '125592043';
    /**
     * @var null
     */
    private $user_id = null;//139604254;

    /**
     * @var null
     */
    private static $class = null;

    /**
     * Vkontakte constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        if (!$this->user_id && !$this->group_id) {
            throw new \Exception('Not found group or user');
        }
        $this->owner = [
            'type' => $this->user_id ? 'owner_id' : 'group_id',
            'value' => $this->user_id ? $this->user_id : $this->group_id,
        ];
        $this->owner['value'] = (int)preg_replace('/([^\d]+)/', '',
            $this->owner['value']
        );
    }


    /**
     * @param       $method
     * @param array $data
     *
     * @return array|mixed
     */
    public function get($method, array $data)
    {
        $this->count++;
        if ($this->count >= 3) {
            $this->count = 0;
            sleep(1);
        }
        $params = [];
        foreach ($data as $name => $val) {
            $params[$name] = $val;
            $params['access_token'] = $this->token;
        }
        try {
            $json = file_get_contents('https://api.vk.com/method/' . $method . '?' .
                http_build_query($params)
            );
            return json_decode($json);
        } catch (\Exception $e) {

        }
        return [];
    }

    /**
     * @param      $text
     * @param null $img
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function post($text, $img = null)
    {
        if ($img) {
            $data = $this->load($img);
            $img = empty($data->response) ? null : $data->response[0]->id;
        }
        $data = [
            'message' => $text,
            'owner_id' => $this->owner['value'],
            'v' => 5.21,
        ];
        if ($img) {
            $data['attachments'] = $img;
        }
        if ($this->owner['type'] == 'group_id') {
            $data['owner_id'] = '-' . $data['owner_id'];
            $data['from_group'] = 1;
        }
        $data = $this->get('wall.post', $data);
        if (isset($data->error)) {
            throw new \Exception($data->error->error_msg);
        }

        return $data;
    }

    /**
     * @param $src
     *
     * @return array|mixed
     */
    public function load($src)
    {
        $photo = (array)$this->getPhoto($src);
        $photo[$this->owner['type']] = $this->owner['value'];
        $data = $this->get('photos.saveWallPhoto', $photo);

        return $data;
    }

    /**
     * @param $src
     *
     * @return mixed
     */
    private function getPhoto($src)
    {
        $name = IMG . '/vk_temp.png';
        file_put_contents($name, file_get_contents($src));

        $ch = curl_init($this->getServer());
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => [
                'photo' => new  \CURLFile('/' . $name)
            ]
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }

    /**
     * @return null
     */
    private function getServer()
    {
        $data = $this->get('photos.getWallUploadServer', [
                $this->owner['type'] => $this->owner['value'],
            ]
        );

        return empty($data->response) ? null : $data->response->upload_url;
    }
}