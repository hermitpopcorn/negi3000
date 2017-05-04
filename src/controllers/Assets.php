<?php
namespace App\Controller;

class Assets extends BaseController
{
    private $mimeTypes = array(
        'js' => 'text/javascript',
        'css' => 'text/css'
    );

    public function get($request, $response, $args)
    {
        $file = __ROOT__ . '/templates/_assets/' . $args['type'] . '/' . $args['item'];

        if(file_exists($file) && in_array($args['type'], array_keys($this->mimeTypes))) {
            // Minify file
            $minifier = $this->container->get('minifier/'.$args['type']);
            $minifier->add($file);

            // Response
            $response = $response
                ->withHeader('Content-Type', $this->mimeTypes[$args['type']])
                ->write($minifier->minify())
            ;

            // Cache expiration
            $response = $this->cache->withExpires($response, time() + 86400);
            // ETag
            $response = $this->cache->withEtag($response, md5_file($file));
            // Last modified
            $response = $this->cache->withLastModified($response, filemtime($file));

            return $response;
        } else {
            return $this->notFoundHandler($request, $response);
        }
    }

    public function getBowerComponent($request, $response, $args)
    {
        $file = __ROOT__ . '/bower_components/' . $args['item'];

        if(!file_exists($file)) {
            return $this->notFoundHandler($request, $response);
        }

        $extension = explode(".", $args['item']);
        $extension = $extension[count($extension) - 1];

        // Response
        $response = $response
            ->withHeader('Content-Type', $this->mimeTypes[$extension])
            ->write(file_get_contents($file))
        ;

        // Cache expiration
        $response = $this->cache->withExpires($response, time() + 86400);
        // ETag
        $response = $this->cache->withEtag($response, md5_file($file));
        // Last modified
        $response = $this->cache->withLastModified($response, filemtime($file));

        return $response;
    }
}
