<?php


namespace flexycms\FlexyTemplatesBundle\Service;

// TODO: Переделать так, чтобы список файлов брался не из массива, а прямо из папки /templates
// Возможно, стоит добавить методы add и exclude??


class TemplatesService
{
    private $templateList;

    public function __construct()
    {
        $this->updateList();
    }

    private function updateList()
    {
        $fileList = array(
            '/templates/main/base.html.twig',
            '/templates/main/list.html.twig',
            '/templates/main/articles.html.twig',
            '/templates/main/article.html.twig',
            '/public/assets/main/js/scripts.js',
            '/public/assets/main/css/styles.scss',
        );

        $this->templateList = array();
        foreach($fileList as $filename)
        {
            if (!is_file($_SERVER['DOCUMENT_ROOT'] . $filename)) continue;

            //Определим тип файла (по расширению)
            $ext = substr($filename, strrpos($filename, '.') + 1);
            $mode = 'htmlmixed';
            switch($ext)
            {
                case 'js': $mode = 'javascript'; break;
                case 'scss': $mode = 'text/x-scss'; break;
                case 'css': $mode = 'css'; break;
            }

            $size = filesize($_SERVER['DOCUMENT_ROOT'] . $filename);
            $mdate = filemtime($_SERVER['DOCUMENT_ROOT'] . $filename);

            $date = new \DateTime();
            $date->setTimestamp($mdate);

            $this->templateList[$filename] = [
                'name' => $filename,
                'mode' => $mode,
                'size' => $size,
                'mdate' => $mdate,
            ];
        }
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->templateList;
    }

    /**
     * @param string $filename
     * @return array|null
     */
    public function getOne(string $filename):?array
    {
        if (!isset($this->templateList[$filename]) || !is_file($_SERVER['DOCUMENT_ROOT'] . $filename)) return null;

        $template = $this->templateList[$filename];
        $template['content'] = $this->getContent($filename);

        return $template;
    }


    private function getContent($filename)
    {
        if (!is_file($_SERVER['DOCUMENT_ROOT'] . $filename)) return null;
        return file_get_contents($_SERVER['DOCUMENT_ROOT'] . $filename);
    }

    /**
     * @param string $filename
     * @param string $content
     * @return null
     */
    public function setContent(string $filename, string $content)
    {
        if (!is_file($_SERVER['DOCUMENT_ROOT'] . $filename)) return null;
        file_put_contents($_SERVER['DOCUMENT_ROOT']. $filename, $content);
        $this->updateList();
    }

}