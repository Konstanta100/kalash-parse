<?php


namespace App\Service;

use App\Entity\Page;
use App\Entity\Post;
use App\Entity\Section;
use DOMElement;
use SimpleXMLElement;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DomCrawler\Crawler;


class ParserToXml
{
    private const XMLNS_EXCERPT = 'http://wordpress.org/export/1.2/excerpt/';

    private const XMLNS_WP = 'http://wordpress.org/export/1.2/';

    private const XMLNS_DC = 'http://purl.org/dc/elements/1.1/';

    private const XMLNS_CONTENT = 'http://purl.org/rss/1.0/modules/content/';

    private const XMLNS_WFW = 'http://wellformedweb.org/CommentAPI/';

    /**
     * @var string
     */
    private const XMLSTR = /** @lang text */
        <<<XML
        <?xml version="1.0" encoding="UTF-8" ?>
        
        <rss version="2.0"
             xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
             xmlns:content="http://purl.org/rss/1.0/modules/content/"
             xmlns:wfw="http://wellformedweb.org/CommentAPI/"
             xmlns:dc="http://purl.org/dc/elements/1.1/"
             xmlns:wp="http://wordpress.org/export/1.2/"
        >
            <channel>
                <title>Музей им. М. Т. Калашникова</title>
                <link>http://kalash</link>
                <pubDate>Wed, 26 May 2021 09:47:12 +0000</pubDate>
                <language>ru-RU</language>
                <wp:wxr_version>1.2</wp:wxr_version>
                <wp:base_site_url>http://kalash</wp:base_site_url>
                <wp:base_blog_url>http://kalash</wp:base_blog_url>
        
                <wp:author></wp:author>
                <generator>https://wordpress.org/?v=5.7.2</generator>
            </channel>
        </rss>
        XML;
    private static $countPosts;

    /**
     * @var Crawler
     */
    private Crawler $domCrawler;

    /**
     * @var Post[]
     */
    private array $posts = [];

    /**
     * @var Page[]
     */
    private array $pages = [];

    private SimpleXMLElement $xmlElement;
    private static int $postId;
    private string $host = 'http://kalash';
    private string $parsedHost = 'http://en.museum-mtk.ru';//http://museum-mtk.ru';

    private int $firstParentId;
    private int $secondParent;

    private string $parentName;

    private string $parentNameRus;
    private string $postDate;
    private string $postfix;
    private string $category;
    private string $prefix = '';

    public function __construct(Crawler $domCrawler)
    {
        $this->domCrawler = $domCrawler;
        $this->xmlElement = new SimpleXMLElement(self::XMLSTR);
    }

    /**
     * @param string $name
     * @param array $urls
     * @param string $category
     * @param string $postfix
     * @param bool $paramAll
     * @return void
     */
    public function getNews(string $name, array $urls = [], string $category, string $postfix, bool $paramAll): void
    {
        $this->postfix = $postfix;
        $this->category = $category; //Инициализируем сеанс

        foreach ($urls as $key => $url) {
            self::$postId = $key;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->parsedHost . $this->category . $postfix . $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

            $i = 0;

            do {
                $html = curl_exec($curl);
                $i++;
                if ($i > 5) {
                    break;
                }
            } while ($html === false);

            if ($i > 5) {
                var_dump('Не получается подключиться к файлу:' . $url);
                die();
            }

            $this->posts = [];
            echo '<p>' . $url . '</p>';
            $this->extractFromHtml((string)$html);

            if (count($this->posts) === 0) {
                continue;
            }
            foreach ($this->posts as $post) {

                if ($post->getPostName()) {
                    $this->prefix = "{$post->getPostName()}_";
                }

                $html = $this->changeLinks($post->getHtml());
                $this->createItem($post->setHtml($html));
            }
            if ($paramAll === false) {
                $xmlFile = $this->xmlElement->asXML();
                $xmlFile = str_replace('&lt;![C', '<![C', $xmlFile);
                $xmlFile = str_replace(']]&gt;<', ']]><', $xmlFile);
                if (file_put_contents($file = "{$name}_" . substr($url, -2) . "_$postfix.xml", $xmlFile)) {
                    var_dump("Записан: {$file}");
                } else {
                    var_dump("Не удалось записать: {$file}");
                    die();
                }
                $this->xmlElement = new SimpleXMLElement(self::XMLSTR);
            }

        }
        if ($paramAll === true) {
            $xmlFile = $this->xmlElement->asXML();
            $xmlFile = str_replace('&lt;![C', '<![C', $xmlFile);
            $xmlFile = str_replace(']]&gt;<', ']]><', $xmlFile);
            if (file_put_contents($file = "{$name}_$postfix.xml", $xmlFile)) {
                var_dump("Записан: {$file}");
            } else {
                var_dump("Не удалось записать: {$file}");
                die();
            }
        }
    }

    public function extractFromHtml($html): void
    {
        $this->domCrawler->clear();
        $this->domCrawler->add($html);

        $this->domCrawler->filterXPath("//body//div[contains(@class, 'press-rubric')]")->each(
            function (Crawler $crawler) {
                $url = $this->parsedHost . $this->category . $this->postfix . '/';

                $nameRubric = trim($crawler->filterXPath("//h2")->text());

                $posts = [];

                foreach ($crawler->filterXPath("//h3") as $node) {
                    if ($node->firstChild instanceof DOMElement) {
                        $nodeValue = $node->firstChild->attributes->item(0)->nodeValue;
                        $postName = preg_replace('/^.*?=(\d+)$/i', 'id$1', $nodeValue);
                        $postUrl = $url . $nodeValue;
                    } else {
                        $postName = null;
                        $postUrl = '';
                    }
                    $posts[] = (new Post())->addRubric($nameRubric)->setUrl($postUrl)->setTitle($node->nodeValue)->setId(self::$postId++)->setPostName($postName);
                }

                $j = 0;
                foreach ($crawler->filterXPath("//p") as $node) {
                    $nodeValue = trim($node->nodeValue);

                    if (preg_match('/^\d\d\.\d\d\.\d{4}$/i', $nodeValue)) {
                        $posts[$j]->setDate($nodeValue);
                    } else {
                        $post = $posts[$j++]->setDescription($nodeValue);
                        $this->posts[] = $post;
                    }
                }
            }
        );
        self::$countPosts = count($this->posts);
        echo '<p> Постов всего:' . self::$countPosts . '</p>';

        foreach ($this->posts as $key => $post) {
            if ($post->getUrl()) {
                $this->domCrawler->clear();

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_URL, $post->getUrl());
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

                $i = 0;

                do {
                    $html = curl_exec($curl);
                    $i++;
                    if ($i > 5) {
                        break;
                    }
                } while ($html === false);

                if ($i > 5) {
                    var_dump('Не получается подключиться к файлу:' . $post->getUrl());
                    die();
                }

                $this->domCrawler->add($html);

                try {
                    $html = $this->domCrawler->filterXPath("//body//div[contains(@class, 'right-part')]")->html();
                } catch (\Exception $exception) {
                    unset($this->posts[$key]);
                    self::$countPosts--;
                    continue;
                }

                preg_match('/<p>\d{2}\.\d{2}.\d{4}<\/p>/i', $html, $times);
                preg_match('/<div class="statusbar">(\s*.*){0,4}<\/div>\s*<div class="overflow">(\s*.*){0,3}<\/div>/i', $html, $divs);
                preg_match('/<h1>.*<\/h1>/i', $html, $h1);

                $html = str_replace([$times[0], $divs[0], $h1[0]], '', $html);

                $post->setHtml($html);
            }
        }
        echo '<p> Постов осталось:' . self::$countPosts . '</p>';
    }


    private function createItem(Post $post): void
    {
        if ($post->getDate()) {
            $startDate = preg_replace("/^(\d{2})\.(\d{2})\.(\d{4})$/i", "$3-$2-$1", $post->getDate());
        } else {
            $startDate = '';
        }

        if ($post->getEndDate()) {
            $endDate = preg_replace("/^(\d{2})\.(\d{2})\.(\d{4})$/i", "$3-$2-$1", $post->getEndDate());
        } else {
            $endDate = '';
        }

        $item = $this->xmlElement->channel[0]->addChild('item');
        $item->addChild('title', $post->getTitle());
        $item->addChild('link', $this->host . '/?p=' . $post->getId());
        $item->addChild('guid', $this->host . '/?p=' . $post->getId())->addAttribute('isPermaLink', 'false');
        $item->addChild(
            'content:encoded',
            "<!-- wp:html -->{$post->getHtml()}<!-- /wp:html -->",
            self::XMLNS_CONTENT
        );
        $item->addChild('excerpt:encoded', $post->getDescription(), self::XMLNS_EXCERPT);
        $item->addChild('wp:post_id', $post->getId(), self::XMLNS_WP);
        $item->addChild('wp:post_date', date("Y-m-d ") . random_int(10, 11) . ":" . random_int(10, 59) . ":" . random_int(10, 59), self::XMLNS_WP);

        $postName = $post->getPostName() ?? 'id' . $post->getId();

        $item->addChild('wp:post_name', "<![CDATA[{$postName}]]>", self::XMLNS_WP);
        $item->addChild('wp:status', 'publish', self::XMLNS_WP);
        $item->addChild('wp:post_parent', 0, self::XMLNS_WP);
        $item->addChild('wp:post_type', 'post', self::XMLNS_WP);
        $item->addChild('wp:is_sticky', 0, self::XMLNS_WP);

        $arrayRub = ['kalashnikov' => 'М.Т. Калашников',
            'weapon' => 'Оружие',
            'history' => 'История завода',
            'drugunov' => 'Е.Ф. Драгунов',
            'niconov' => 'Г. Н. Никонов'
        ];
        $salt = null;

        foreach ($arrayRub as $key => $rub) {
            if (in_array($rub, $post->getRubrics())) {
                $salt = $key;
            }
        }

        foreach ($post->getRubrics() as $rubric) {
            $category = $item->addChild('category', $rubric);
            $category->addAttribute('domain', 'category');

            $rubric = $this->translateCategory($rubric);

            if (($rubric === 'books' || $rubric === 'articles') && $salt) {
                $rubric = $rubric . "-" . $salt;
            }

            $category->addAttribute('nicename', $rubric);
        }

        $postmeta = $item->addChild('wp:postmeta', null, self::XMLNS_WP);
        $postmeta->addChild('wp:meta_key', 'start_date', self::XMLNS_WP);
        $postmeta->addChild('wp:meta_value', "$startDate", self::XMLNS_WP);
        $postmeta = $item->addChild('wp:postmeta', null, self::XMLNS_WP);
        $postmeta->addChild('wp:meta_key', 'end_date', self::XMLNS_WP);
        $postmeta->addChild('wp:meta_value', "$endDate", self::XMLNS_WP);
    }


    /**
     * @param Section $section
     * @return SimpleXMLElement
     */
    public function parseSiteMap(Section $section): SimpleXMLElement
    {
        self::$postId = $section->getPostId();
        $this->firstParentId = $section->getFirstParentId();
        $this->parentName = $section->getParentName();
        $this->parentNameRus = $section->getParentNameRus();
        $this->postDate = $section->getPostDate();

        $this->getUrlPagesFromHtml($section->getHtml());


        $this->getHtmlFromPages();


        $this->createXmlForPages($section->getColor(), $section->getImage());

        return $this->xmlElement;
    }

    private function getUrlPagesFromHtml(string $html): void
    {
        $this->domCrawler->clear();
        $this->domCrawler->add($html);

        $this->domCrawler->filterXPath('//ul')->children()->each(
            function (Crawler $crawler) {

                if ($crawler->nodeName() === 'li') {
                    $attrValue = $crawler->children()->first()->extract(['href'])[0];
                    preg_match('/^(.+\/)(.+)$/', $attrValue, $matches);

                    $nodeValue = $crawler->first()->text();
                    $this->secondParent = self::$postId++;

                    $this->pages[] = (new Page())
                        ->setUrl($attrValue)
                        ->setParentId($this->firstParentId)
                        ->setPostId($this->secondParent)
                        ->setTitle($nodeValue)
                        ->setParentUrl($matches[1])
                        ->setUrl($matches[2]);
                }

                if ($crawler->nodeName() === 'ul') {
                    foreach ($crawler->children() as $node) {
                        if ($node->nodeName === 'li') {
                            $attrValue = $node->firstChild->attributes->item(0)->nodeValue;
                            preg_match('/^(.+\/)(.+\/)$/', $attrValue, $matches);

                            $nodeValue = $node->firstChild->nodeValue;
                            $thirdParent = self::$postId++;

                            $this->pages[] = (new Page())
                                ->setUrl($attrValue)
                                ->setParentId($this->secondParent)
                                ->setPostId($thirdParent)
                                ->setTitle($nodeValue)
                                ->setParentUrl($matches[1])
                                ->setUrl($matches[2]);
                        }

                        if ($node->nodeName === 'ul') {
                            foreach ($node->childNodes as $childNode) {
                                if ($childNode->nodeName === 'li') {
                                    $attrValue = $childNode->firstChild->attributes->item(0)->nodeValue;
                                    var_dump($attrValue);
                                    preg_match('/^(.+\/)(.+\/{0,1})$/', $attrValue, $matches);

                                    $nodeValue = $childNode->firstChild->nodeValue;
                                    var_dump($nodeValue);
                                    $fourthParent = self::$postId++;

                                    $this->pages[] = (new Page())
                                        ->setUrl($attrValue)
                                        ->setParentId($this->secondParent)
                                        ->setPostId($fourthParent)
                                        ->setTitle($nodeValue)
                                        ->setParentUrl($matches[1])
                                        ->setUrl($matches[2]);
                                }

                                if ($childNode->nodeName === 'ul') {
                                    var_dump('bug');
                                    die();
                                }
                            }
                        }
                    }
                }
            }
        );
    }

    private function getHtmlFromPages(): void
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $pages = [];

        foreach ($this->pages as $page) {
            $this->domCrawler->clear();
            curl_setopt($curl, CURLOPT_URL, $this->parsedHost . $page->getParentUrl() . $page->getUrl());
            $html = curl_exec($curl);
            $this->domCrawler->add($html);
            try {
                $rightPart = $this->domCrawler->filterXPath("//body//div[contains(@class, 'right-part')]");
            } catch (\Exception $exception) {
                continue;
            }

            $bodyPost = $rightPart->children()->reduce(
                function (Crawler $crawler) {
                    return !in_array(current($crawler->extract(['class'])), ['statusbar', 'overflow']);
                }
            );

            $html = '';
            foreach ($bodyPost as $node) {
                $html .= $node->ownerDocument->saveHTML($node);
            }

            $html = $this->changeLinks($html);

            $pages[] = $page->setContent($html);
        }

        $this->pages = $pages;
    }

    /**
     * @param $_color
     * @param $_image
     */
    private function createXmlForPages($_color, $_image): void
    {
        $item = $this->xmlElement->channel[0]->addChild('item');
        $item->addChild('title', "$this->parentNameRus");
        $item->addChild('link', $this->host . '/?page_id=' . $this->firstParentId);
        $item->addChild('post_id', $this->firstParentId, self::XMLNS_WP);
        $item->addChild('wp:post_name', "$this->parentName", self::XMLNS_WP);
        $item->addChild('wp:post_parent', 0, self::XMLNS_WP);
        $item->addChild('wp:post_type', 'page', self::XMLNS_WP);
        $item->addChild('wp:post_date', $this->postDate, self::XMLNS_WP);


        foreach ($this->pages as $page) {
            $postName = str_replace(' ', '-', $page->getTitle());
            $postName = str_replace(array('"', '/', ':', '.', ',', '[', ']', '“', '”'), '', strtolower($postName));
            $postName = str_replace('/', '', $page->getUrl());


            $item = $this->xmlElement->channel[0]->addChild('item');
            $item->addChild('title', $page->getTitle());
            $item->addChild('link', $this->host . '/?page_id=' . $page->getPostId());
            $item->addChild('pubDate');
            $item->addChild('dc:creator', null, self::XMLNS_DC);
            $item->addChild('guid', $this->host . '/?page_id=' . $page->getPostId())->addAttribute('isPermaLink', 'false');
            $item->addChild('description');
            $item->addChild(
                'content:encoded',
                "<!-- wp:html -->" . $page->getContent() . "<!-- /wp:html -->",
                self::XMLNS_CONTENT
            );
            $item->addChild('excerpt:encoded', null, self::XMLNS_EXCERPT);
            $item->addChild('wp:post_id', $page->getPostId(), self::XMLNS_WP);
            $item->addChild('wp:post_date', null, self::XMLNS_WP);
            $item->addChild('wp:post_date_gmt', null, self::XMLNS_WP);
            $item->addChild('wp:comment_status', 'closed', self::XMLNS_WP);
            $item->addChild('wp:ping_status', 'closed', self::XMLNS_WP);
            $item->addChild('wp:post_name', $postName, self::XMLNS_WP);
            $item->addChild('wp:status', 'publish', self::XMLNS_WP);
            $item->addChild('wp:post_parent', $page->getParentId(), self::XMLNS_WP);
            $item->addChild('wp:menu_order', 0, self::XMLNS_WP);
            $item->addChild('wp:post_type', 'page', self::XMLNS_WP);
            $item->addChild('wp:post_password', null, self::XMLNS_WP);
            $item->addChild('wp:is_sticky', 0, self::XMLNS_WP);

            $postmeta = $item->addChild('wp:postmeta', null, self::XMLNS_WP);
            $postmeta->addChild('wp:meta_key', '<![CDATA[_edit_last]]>', self::XMLNS_WP);
            $postmeta->addChild('wp:meta_value', '<![CDATA[1]]>', self::XMLNS_WP);

            $postmeta = $item->addChild('wp:postmeta', null, self::XMLNS_WP);
            $postmeta->addChild('wp:meta_key', '<![CDATA[_color]]>', self::XMLNS_WP);
            $postmeta->addChild('wp:meta_value', "<![CDATA[" . $_color . "]]>", self::XMLNS_WP);

            $postmeta = $item->addChild('wp:postmeta', null, self::XMLNS_WP);
            $postmeta->addChild('wp:meta_key', '<![CDATA[_image]]>', self::XMLNS_WP);
            $postmeta->addChild('wp:meta_value', "<![CDATA[" . $_image . "]]>", self::XMLNS_WP);
        }
    }

    /**
     * @param string $html
     * @return string
     */
    private function changeLinks(string $html): string
    {
        // 2. Переделать хост http://museum-mtk.ru/ на /
        $html = str_replace(['href="http://www.museum-mtk.ru"', 'href="http://museum-mtk.ru"'], 'href="/"', $html);

        $html = str_replace(['http://www.museum-mtk.ru', 'http://museum-mtk.ru', '/museum-mtk.ru'], '', $html);

        $html = str_replace(['detail.htm?id='], 'id', $html);

        // 1, Из ссылок вида armourers/kalashnikov добавить ведущий слеш

        preg_match_all('/ ?href="((?!mailto|http|\/)[\S.]+)?"/', $html, $links);

        foreach ($links[1] as $link) {
            $html = str_replace("$link", "/$link", $html);
        }

        // 3. exhibitions/past/detail.htm?id=731615 на post/detail/731615

//        preg_match_all('/ ?href="([\S.]+?\/detail\.htm\?id=\d+[\S.]+)?"/i', $html, $links);
//
//        foreach ($links[1] as $link){
//            $path = preg_replace('/^.+?detail\.htm\?id=(\d+[\S.]+)/i', "/post/detail/$1", $link);
//            $html = str_replace("$link", $path, $html);
//        }

        // 4. Архив документов и картинок сохранить /wp-content/uploads/2021/06/1.jpg (переделать ссылки на документы)

        preg_match_all('/="(\/(_common|_images|_galleries|_downloads)\/[^\s=]+)"/i', $html, $links);


        if (count($links[1]) > 0) {
            foreach ($links[1] as $link) {
                $path = preg_replace("/^.+?\/([^\s\/]+)$/i", "/wp-content/uploads/2021/06/{$this->prefix}$1", $link);

                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path) && !$this->prefix) {
                    $path = preg_replace("/^.+?\/([-,\(\)\{\}.\w]{1,55})?\/([^\s\/]+)$/i", "/wp-content/uploads/2021/06/$1_$2", $link);
                }

                if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                    if ($file = @file_get_contents($this->parsedHost . $link)) {
                        file_put_contents($_SERVER['DOCUMENT_ROOT'] . $path, $file);
                        echo '<p>' . $path . '</p>';
                    }
                }

                $html = str_replace("$link", $path, $html);
            }
        }

        return str_replace(['.jpg/?thu=1'], '-100x100.jpg', $html);
    }

    /**
     * @param string $rubric
     * @return string
     */
    private function translateCategory(string $rubric): string
    {
        switch ($rubric) {
            case 'Common news':
                $rubric = 'news-general-en';
                break;
            case 'Publications':
                $rubric = 'publications-presscenter-en';
                break;
            case 'Exhibitions':
                $rubric = 'exhibitions-presscenter-en';
                break;
            case 'Collections':
                $rubric = 'сollections-en';
                break;
            case 'Enlightment and education':
                $rubric = 'education-presscenter-en';
                break;
            case 'Tourism':
                $rubric = 'tourism-en';
                break;
            case 'Общие новости':
                $rubric = 'news-general';
                break;
            case 'Выставки':
                $rubric = 'exhibitions-presscenter';
                break;
            case 'Издания':
                $rubric = 'publications-presscenter';
                break;
            case 'Коллекции':
                $rubric = 'collections';
                break;
            case 'Просвещение и образование':
                $rubric = 'education-presscenter';
                break;
            case 'Туризм':
                $rubric = 'tourism';
                break;
            case 'Концерт':
                $rubric = 'concert';
                break;
            case 'Визиты':
                $rubric = 'sessions';
                break;
            case 'Пресс-релизы':
                $rubric = 'press-releases-smi';
                break;
            case 'Статьи':
                //Сми
                $rubric = 'articles';
                //БИБЛИОГРАФИЯ
                break;
            case 'Видеорепортажи':
                $rubric = 'video-reports-smi';
                break;
            case 'Книги':
                $rubric = 'books';
                break;
            case 'М.Т. Калашников':
                $rubric = 'kalashnikov-bibliography';
                break;
            case 'Оружие':
                $rubric = 'weapon-bibliography';
                break;
            case 'История завода':
                $rubric = 'factory-history';
                break;
            case 'Е.Ф. Драгунов':
                $rubric = 'dragunov-bibliography';
                break;
            case 'Г. Н. Никонов':
                $rubric = 'nikonov-bibliography';
                break;
            case 'bibliography':
                $rubric = 'bibliography';
                break;
            case 'History':
            case 'past':
                $rubric = 'past';
                break;
            default:
                var_dump('Категория не найдена');
                die();
        }

        return $rubric;
    }

    /**
     * @param string $name
     * @param array $urls
     * @param string $category
     * @param string $postfix
     * @return void
     */
    public function getArmourers(string $name, array $urls = [], string $category, string $postfix): void
    {
        $this->postfix = $postfix;
        $this->category = $category; //Инициализируем сеанс
        self::$postId = 13000;
        $curl = curl_init();
        var_dump($urls);
        foreach ($urls as $key => $url) { //Указываем адрес страницы
            curl_setopt($curl, CURLOPT_URL, $this->parsedHost . $this->category . $postfix . $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            $html = curl_exec($curl);

            $this->posts = [];
            $this->extractBibliographyHtml((string)$html);

            if (count($this->posts) === 0) {
                continue;
            }

            foreach ($this->posts as $post) {
                $this->createItem($post->addRubric($key)->addRubric($name));
            }
        }

        if (file_put_contents($file = "{$name}_{$postfix}.xml", $this->xmlElement->asXML())) {
            var_dump("Записан: {$file}");
        } else {
            var_dump("Не удалось записать: {$file}");
            die();
        }
    }

    public function extractBibliographyHtml(string $html): void
    {
        $this->domCrawler->clear();
        $this->domCrawler->add($html);

        $this->domCrawler->filterXPath("//body//div[contains(@id, 'bt')]")->each(
            function (Crawler $crawler) {

                $nameRubric = trim($crawler->filterXPath("//h3")->text());

                foreach ($crawler->filterXPath("//p") as $node) {
                    $lastChild = '';

                    if ($node->childNodes->length === 2) {
                        $lastChild = $node->lastChild->nodeValue;
                    }

                    $firstChild = $node->firstChild->nodeValue;

                    $post = (new Post())->addRubric($nameRubric);

                    if ($lastChild) {
                        $post->setTitle($firstChild);
                        $post->setHtml('<p>' . $lastChild . '</p>');
                        $post->setDescription("$lastChild");
                    } else {
                        $post->setTitle('');
                        $post->setHtml('<p>' . $firstChild . '</p>');
                        $post->setDescription("$firstChild");
                    }

                    $post->setId(self::$postId++)->setPostName(null);

                    $this->posts[] = $post;
                }
            }
        );
    }

    /**
     * @param string $name
     * @param array $urls
     * @param string $category
     * @param string $postfix
     * @param bool $paramAll
     * @return void
     */
    public function getExhibitions(string $name, array $urls = [], string $category, string $postfix, bool $paramAll): void
    {
        $this->postfix = $postfix;
        $this->category = $category; //Инициализируем сеанс

        foreach ($urls as $key => $url) {
            self::$postId = $key;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->parsedHost . $this->category . $postfix  . $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

            $i = 0;

            do {
                $html = curl_exec($curl);
                $i++;
                if ($i > 5) {
                    break;
                }
            } while ($html === false);

            if ($i > 5) {
                var_dump('Не получается подключиться к файлу:' . $url);
                die();
            }

            $this->posts = [];
            echo '<p>' . $url . '</p>';
            $this->extractFromHtmlExhibition((string)$html);

            if (count($this->posts) === 0) {
                continue;
            }
            foreach ($this->posts as $post) {
                if ($post->getPostName()) {
                    $this->prefix = "{$post->getPostName()}_";
                }
                $html = $this->changeLinks($post->getHtml());
                $this->createItem($post->setHtml($html));
            }
            if ($paramAll === false) {
                $xmlFile = $this->xmlElement->asXML();
                $xmlFile = str_replace('&lt;![C', '<![C', $xmlFile);
                $xmlFile = str_replace(']]&gt;<', ']]><', $xmlFile);
                if (file_put_contents($file = "{$name}_" . substr($url, -2) . "_$postfix.xml", $xmlFile)) {
                    var_dump("Записан: {$file}");
                } else {
                    var_dump("Не удалось записать: {$file}");
                    die();
                }
                $this->xmlElement = new SimpleXMLElement(self::XMLSTR);
            }

        }
        if ($paramAll === true) {
            $xmlFile = $this->xmlElement->asXML();
            $xmlFile = str_replace('&lt;![C', '<![C', $xmlFile);
            $xmlFile = str_replace(']]&gt;<', ']]><', $xmlFile);
            if (file_put_contents($file = "{$name}_$postfix.xml", $xmlFile)) {
                var_dump("Записан: {$file}");
            } else {
                var_dump("Не удалось записать: {$file}");
                die();
            }
        }
    }

    public function extractFromHtmlExhibition($html): void
    {
        $this->domCrawler->clear();
        $this->domCrawler->add($html);

        $this->domCrawler->filterXPath("//body//div[contains(@class, 'right-part')]")->each(
            function (Crawler $crawler) {
                $posts = [];

                foreach ($crawler->filterXPath("//h3") as $node) {
                    $url = $node->firstChild->attributes->item(0)->nodeValue;
                    $postName = preg_replace('/^.*?=(\d+)$/i', 'id$1', $url);
                    $posts[] = (new Post())->addRubric('History')->setTitle($node->nodeValue)->setId(self::$postId++)->setUrl($this->parsedHost . $this->category . $this->postfix . '/'  . $url)->setPostName($postName);
                };

                foreach ($crawler->filterXPath("//p") as $key => $node) {
                    $posts[$key]->setDescription($node->nodeValue);
                };

//                foreach ($crawler->filterXPath("//span[contains(@class, 'gallery ex-preview')]") as $node) {
//                    var_dump($node->firstChild);
//                };

                $key = 0;
                foreach ($crawler->filterXPath("//text()") as $node) {
                    if (preg_match('/^(\d\d\.\d\d\.\d\d\d\d).*?(\d\d\.\d\d\.\d\d\d\d)$/i', trim($node->nodeValue), $matches)) {
                        $post = $posts[$key++]->setDate($matches[1])->setEndDate($matches[2]);
                        $this->posts[] = $post;
                    }
                }
            }
        );


        self::$countPosts = count($this->posts);
        echo '<p> Постов всего:' . self::$countPosts . '</p>';
        var_dump($this->posts);

        foreach ($this->posts as $key => $post) {
            if ($post->getUrl()) {
                $this->domCrawler->clear();

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_URL, $post->getUrl());
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

                $i = 0;

                do {
                    $html = curl_exec($curl);
                    $i++;
                    if ($i > 5) {
                        break;
                    }
                } while ($html === false);

                if ($i > 5) {
                    var_dump('Не получается подключиться к файлу:' . $post->getUrl());
                    die();
                }

                $this->domCrawler->add($html);

                try {
                    $html = $this->domCrawler->filterXPath("//body//div[contains(@class, 'right-part')]")->html();
                } catch (\Exception $exception) {
                    unset($this->posts[$key]);
                    self::$countPosts--;
                    continue;
                }

                preg_match('/<p>\d\d\.\d\d\.\d\d\d\d.*?\d\d\.\d\d\.\d\d\d\d<\/p>/i', $html, $times);;
                preg_match('/<div class="statusbar">(\s*.*){0,4}<\/div>\s*<div class="overflow">(\s*.*){0,3}<\/div>/i', $html, $divs);
                preg_match('/<h1>.*<\/h1>/i', $html, $h1);

                $html = str_replace([$times[0], $divs[0], $h1[0]], '', $html);

                $post->setHtml($html);
            }
        }
        echo '<p> Постов осталось:' . self::$countPosts . '</p>';
    }
}
