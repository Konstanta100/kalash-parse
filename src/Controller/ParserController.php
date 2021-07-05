<?php


namespace App\Controller;


use App\Entity\Section;
use App\Service\ParserToXml;
use App\Service\SectionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParserController extends AbstractController
{
    private ParserToXml $parser;

    private SectionService $sectionService;

    /**
     * ParserController constructor.
     * @param ParserToXml $parser
     * @param SectionService $sectionService
     */
    public function __construct(ParserToXml $parser, SectionService $sectionService)
    {
        $this->parser = $parser;
        $this->sectionService = $sectionService;
    }

    /**
     * @Route("/parseNews", name="parseNews")
     * @throws \Exception
     */
    public function parseNewsAction(): Response
    {

        $key = 17000;
        $urls = [];

        for($i = 1; $i <= 12; $i++){
            if($i < 10){
                $i = "0{$i}";
            }
            $months[] = (string)$i;
        }

        for($year = 2012; $year <= date('Y');$year++){
            foreach ($months as $month){
                $key += 8;
                $urls[$key] = "/index.htm?year={$year}&month={$month}";
            }

        }



        $this->parser->getNews($year, $urls, '/presscenter/','news', true);

        return new Response();
    }

    /**
     * @Route("/parseSmi", name="parseSmi")
     * @throws \Exception
     */
    public function parseSmiAction(): Response
    {
        $urls = [];
        $months = [];
        
        for($i = 1; $i <= 12; $i++){
            if($i < 10){
                $i = "0{$i}"; 
            }
            $months[] = (string)$i; 
        }
        $key = 11000;

        for($year = 2005; $year < date('Y');$year++){
            if(!(2013 < $year && $year < 2018)){
                foreach ($months as $month){
                    $key += 8;
                    $urls[$key] = "/index.htm?year={$year}&month={$month}";
                }
            }
        }


        $this->parser->getNews($year, $urls, '/presscenter/','smi', true);

        return new Response();
    }

    /**
     * @Route("/parseExhibitions", name="parseExhibitionsAction")
     * @return Response
     */
    public function parseExhibitionsAction(): Response
    {
        $urls = [];
        $key = 20000;

        for ($year = 2005; $year <= date('Y'); $year++){
            $key+=25;
            $urls[$key] = "/index.htm?year={$year}";
        }

        $this->parser->getExhibitions($year, $urls, '/exhibitions/','past', true);
        return new Response();
    }

    /**
     * @Route("/parseArmourers", name="parseArmourers")
     * @param int $firstYear
     * @param int|null $lastYear
     * @return Response
     */
    public function parseArmourersAction(): Response
    {
        $urls = [
            'М.Т. Калашников' => '/index.htm?group=724644',
            'Оружие' => '/index.htm?group=724646',
            'История завода' => '/index.htm?group=724647',
            'Е.Ф. Драгунов' => '/index.htm?group=724649',
            'Г. Н. Никонов' => '/index.htm?group=724652'
        ];

        $this->parser->getArmourers('bibliography', $urls, '/armourers/','bibliography');
        return new Response();
    }



    /**
     * @Route("/parseSiteMap/{section}", name="parseSiteMap")
     * @throws \Exception
     */
    public function parseSiteMapAction(?string $section): Response
    {
        $response = new  Response();

        switch ($section) {
            case 'about':
                $html = /** @lang text */
                    <<<HTML
                    <ul>
                        <li><a href="/about/osnovne_svedeniya/">Сведения об организации</a></li>
                    
                        <li><a href="/about/history/">История</a></li>
                    
                        <li><a href="/about/structure/">Структура</a></li>
                    
                        <li><a href="/about/dostupnaya_sreda/">Доступная среда</a></li>
                    
                        <li><a href="/about/partners/">Друзья&nbsp;и&nbsp;партнеры</a></li>
                    
                        <li><a href="/about/contribution/">Сотрудничество</a></li>
                    
                        <li><a href="/about/contacts/">Контакты</a></li>
                    
                        <li><a href="/about/dokument/">Документы</a></li>
                        <ul>
                            <li><a href="/about/dokument/1.10.07._materialnoe_obespechenie/">Материально-техническое обеспечение и оснащенность образовательного процесса</a></li>
                        </ul>
                    </ul>
                HTML;

                $section = (new Section())
                    ->setHtml($html)
                    ->setColor('field_60a64f8f347d1')
                    ->setImage('field_60a77275462f1')
                    ->setPostId(300)
                    ->setFirstParentId(35)
                    ->setParentName('about')
                    ->setParentNameRus('О Музее')
                    ->setPostDate('2021-05-31 15:28:25');

                break;
            case 'noch_muzeev-2021':
                $html = /** @lang text */
                    <<<HTML
                    <ul>
                        <li><a href="/noch_muzeev-2021/01/">Музей как визитная карточка</a></li>
                    
                        <li><a href="/noch_muzeev-2021/02_muzej_kak_interaktivnaya_laboratoriya/">Музей как интерактивная лаборатория</a></li>
                    
                        <li><a href="/noch_muzeev-2021/03/">Музей как учебный класс для детей</a></li>
                    
                        <li><a href="/noch_muzeev-2021/urok_strela/">Музей как демонстрационный зал</a></li>
                    
                        <li><a href="/noch_muzeev-2021/05/">Музей как учебный класс для взрослых</a></li>
                    
                        <li><a href="/noch_muzeev-2021/06/">Музей как сувенирная лавка</a></li>
                    
                        <li><a href="/noch_muzeev-2021/07/">Музей как библиотека</a></li>
                    
                        <li><a href="/noch_muzeev-2021/08/">Музей как концертная площадка</a></li>
                    
                        <li><a href="/noch_muzeev-2021/09/">Музей как кинозал</a></li>
                    
                        <li><a href="/noch_muzeev-2021/11/">Проект «Музейные читки»</a></li>
                    </ul>
                HTML;

                $section = (new Section())
                    ->setHtml($html)
                    ->setColor('field_60a64f8f347d1')
                    ->setImage('field_60a77275462f1')
                    ->setPostId(400)
                    ->setFirstParentId(393)
                    ->setParentName('noch_muzeev-2021')
                    ->setParentNameRus('Ночь музеев-2021!')
                    ->setPostDate('2021-05-31 15:28:25');

                break;
            case 'luchshedoma':

                $html = /** @lang text */
                    <<<HTML
                    <ul>
                        <li><a href="/luchshedoma/virtualne_vstavki/">Виртуальные выставки</a></li>
                    
                        <li><a href="/luchshedoma/muzejne_chitki/">Проект «Музейные читки»</a></li>
                    
                        <li><a href="/luchshedoma/priotkrvaya_fond/">Приоткрывая фонды</a></li>
                    
                        <li><a href="/luchshedoma/muzejne_bajki/">Музейные байки</a></li>
                    </ul>
                HTML;

                $section = (new Section())
                    ->setHtml($html)
                    ->setColor('field_60a64f8f347d1')
                    ->setImage('field_60a77275462f1')
                    ->setPostId(500)
                    ->setFirstParentId(389)
                    ->setParentName('luchshedoma')
                    ->setParentNameRus('#ЛучшеДома')
                    ->setPostDate('2021-05-31 15:27:40');

                break;
            case 'kalashnikov100':

                $html = /** @lang text */
                    <<<HTML
                <ul>
                    <li><a href="/kalashnikov100/vstavki_v_yubilejnom_godu/">Выставки в юбилейном году</a></li>
                
                    <li><a href="/kalashnikov100/aktsiya_dembelskij_albom/">Акция «Фотографии из армейского альбома»</a></li>
                
                    <li><a href="/kalashnikov100/traektoriya_sudb/">Траектория судьбы: 100 главных событий</a></li>
                    <ul>
                        <li><a href="/kalashnikov100/traektoriya_sudb/traektoriya_sudb/"></a></li>
                    
                        <li><a href="/kalashnikov100/traektoriya_sudb/0.2.1_yanvar/">Траектория судьбы : Январь</a></li>
                    
                        <li><a href="/kalashnikov100/traektoriya_sudb/0.2.2_fevral/">Траектория судьбы : Февраль</a></li>
                    
                        <li><a href="/kalashnikov100/traektoriya_sudb/0.2.3_mart/">Траектория судьбы : Март</a></li>
                    
                        <li><a href="/kalashnikov100/traektoriya_sudb/0.2.4_aprel/">Траектория судьбы : Апрель</a></li>
                    
                        <li><a href="/kalashnikov100/traektoriya_sudb/0.2.5_maj/">Траектория судьбы : Май</a></li>
                    
                        <li><a href="/kalashnikov100/traektoriya_sudb/0.2.6_iyun/">Траектория судьбы : Июнь</a></li>
                    
                        <li><a href="/kalashnikov100/traektoriya_sudb/0.2.7_iyul/">Траектория судьбы : Июль</a></li>
                    
                        <li><a href="/kalashnikov100/traektoriya_sudb/0.2.8_avgust/">Траектория судьбы : Август</a></li>
                    
                        <li><a href="/kalashnikov100/traektoriya_sudb/0.2.9_sentyabr/">Траектория судьбы : Сентябрь</a></li>
                    
                        <li><a href="/kalashnikov100/traektoriya_sudb/0.2.10_oktyabr/">Траектория судьбы : Октябрь</a></li>
                    
                        <li><a href="/kalashnikov100/traektoriya_sudb/0.2.11_noyabr/">Траектория судьбы : Ноябрь</a></li>
                    
                        <li><a href="/kalashnikov100/traektoriya_sudb/0.2.12_dekabr/">Траектория судьбы : Декабрь</a></li>
                    </ul>
                    <li><a href="/kalashnikov100/konferentsiya_s_imenem_kalashnikova/">Конференция «С именем Калашникова»</a></li>
                </ul>
                HTML;

                $section = (new Section())
                    ->setHtml($html)
                    ->setColor('field_60a64f8f347d1')
                    ->setImage('field_60a77275462f1')
                    ->setPostId(600)
                    ->setFirstParentId(380)
                    ->setParentName('kalashnikov100')
                    ->setParentNameRus('100 лет со дня рождения М.Т.Калашникова')
                    ->setPostDate('2021-05-31 15:20:52');

                break;

            case 'dragunov100':

                $html = /** @lang text */
                    <<<HTML
                    <ul>
                        <li><a href="/presscenter/Arkhiv_meropriyatij/dragunov100/vstavka_vizhu_tsel/">Выставка «Вижу цель!»</a></li>
                    
                        <li><a href="/presscenter/Arkhiv_meropriyatij/dragunov100/otkrtie_yubilejnoj_vstavki/">Открытие юбилейной выставки</a></li>
                    
                        <li><a href="/presscenter/Arkhiv_meropriyatij/dragunov100/vecher_pamyati_e.f._dragunova/">Вечер памяти Е.Ф. Драгунова</a></li>
                    
                        <li><a href="/presscenter/Arkhiv_meropriyatij/dragunov100/viktorina_vizhu_tsel/">Он-лайн викторина «Вижу цель!»</a></li>
                    
                        <li><a href="/presscenter/Arkhiv_meropriyatij/dragunov100/respublikanskij_konkurs_esse_tsel_v_zhizni/">Республиканский конкурс эссе «Цель в жизни»</a></li>
                    
                        <li><a href="/presscenter/Arkhiv_meropriyatij/dragunov100/zanyatie_dlya_shkolnikov/">Интерактивное занятие для дошкольников «Конструктор оружия Драгунов»</a></li>
                    </ul>
                HTML;

                $section = (new Section())
                    ->setHtml($html)
                    ->setColor('field_60a64f8f347d1')
                    ->setImage('field_60a77275462f1')
                    ->setPostId(700)
                    ->setFirstParentId(376)
                    ->setParentName('dragunov100')
                    ->setParentNameRus('100 лет со дня рождения Е.Ф.Драгунова')
                    ->setPostDate('2021-05-31 15:19:52');

                break;
            case 'education':

                $html = /** @lang text */
                    <<<HTML
                    <ul>
                        <li><a href="/education/svedeniya_ob_obrazovatelnoj_organizatsii/">Сведения об образовательной организации</a></li>
                        <ul>
                            <li><a href="/education/svedeniya_ob_obrazovatelnoj_organizatsii/platne_obrazovatelne_uslugi/">Платные образовательные услуги</a></li>
                        
                            <li><a href="/education/svedeniya_ob_obrazovatelnoj_organizatsii/Nok/">Независимая оценка качества образования</a></li>
                        </ul>
                        
                        <li><a href="/education/excursions/">Тематические экскурсии</a></li>
                        
                        <li><a href="/education/lections/">Лекции</a></li>
                        
                        <li><a href="/education/actions/">Мероприятия</a></li>
                        
                        <li><a href="/education/programs/">Образовательные программы и циклы занятий</a></li>
                        
                        <ul>
                            <li><a href="/education/programs/1.10.11._platne_obrazovatelne_uslugi/">Платные образовательные услуги</a></li>
                        </ul>
                        
                        <li><a href="/education/dop/">Доступная образовательная программа</a></li>
                        
                        <li><a href="/education/library/">Библиотека</a></li>
                    </ul>
                HTML;

                $section = (new Section())
                    ->setHtml($html)
                    ->setColor('field_60a64f8f347d1')
                    ->setImage('field_60a77275462f1')
                    ->setPostId(800)
                    ->setFirstParentId(59)
                    ->setParentName('education')
                    ->setParentNameRus('Просвещение и образование')
                    ->setPostDate('2021-05-19 16:24:10');

                break;
            default:
                return new Response($response);

        }

        $xmlData = $this->parser->parseSiteMap($section);
        if (file_put_contents($section->getParentName() . '.xml', $xmlData->asXML())) {
            $response->setContent('Файл создан');
        } else {
            $response->setStatusCode(400);
            $response->setContent('Не удалось создать файл');
        }


        return $response;
    }
}


