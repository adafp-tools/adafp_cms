<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\DocumentCategory;
use App\Enums\MenuLocation;
use App\Enums\PageTemplate;
use App\Enums\PersonGroup;
use App\Enums\PostType;
use App\Enums\UserRole;
use App\Models\Document;
use App\Models\Event;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Person;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (config('cms.instance') !== 'demo') {
            return;
        }

        $this->seedSettings();
        $this->seedUser();
        $this->seedPages();
        $this->seedPosts();
        $this->seedEvents();
        $this->seedPeople();
        $this->seedDocuments();
        $this->seedMenus();
    }

    // ── Settings ──────────────────────────────────────────────────────────────

    private function seedSettings(): void
    {
        $settings = [
            'site_name'               => 'Progressive Citizens Party',
            'theme'                   => 'campaign',
            'primary_color'           => '#1a3f7a',
            'secondary_color'         => '#c8a84b',
            'text_on_primary'         => '#ffffff',
            'default_locale'          => 'en',
            'active_locales'          => 'en,fr,ar,pt',
            'maintenance_mode'        => '0',
            'noindex'                 => '1',
            'page_counter_enabled'    => '1',
            'contact_form_enabled'    => '1',
            'join_form_enabled'       => '1',
            'notification_email'      => '',
            'notification_from_name'  => 'Progressive Citizens Party',
            'notification_from_email' => '',
            'privacy_policy_url'      => '',
            'crm_push_enabled'        => '0',
            'crm_sso_enabled'         => '0',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }

    // ── Demo user ─────────────────────────────────────────────────────────────

    private function seedUser(): void
    {
        $email    = config('cms.demo_login_email', 'demo@example.org');
        $password = config('cms.demo_login_password', '');

        if (empty($password)) {
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => 'Demo Editor',
                'password' => Hash::make($password),
                'role'     => UserRole::Editor,
            ]
        );
    }

    // ── Pages ─────────────────────────────────────────────────────────────────

    private function seedPages(): void
    {
        $pages = [
            [
                'slug'         => 'home',
                'template'     => PageTemplate::Default,
                'status'       => ContentStatus::Published,
                'show_in_menu' => false,
                'translations' => [
                    'en' => [
                        'title'            => 'Home',
                        'body'             => '<p>Welcome to the Progressive Citizens Party. We are committed to building a fairer, more sustainable society for everyone.</p>',
                        'meta_description' => 'Official website of the Progressive Citizens Party.',
                    ],
                    'fr' => [
                        'title'            => 'Accueil',
                        'body'             => '<p>Bienvenue sur le site du Parti Progressiste des Citoyens. Nous nous engageons à construire une société plus juste et durable pour tous.</p>',
                        'meta_description' => 'Site officiel du Parti Progressiste des Citoyens.',
                    ],
                    'ar' => [
                        'title'            => 'الرئيسية',
                        'body'             => '<p>مرحباً بكم في موقع حزب المواطنين التقدمي. نلتزم ببناء مجتمع أكثر عدلاً واستدامةً للجميع.</p>',
                        'meta_description' => 'الموقع الرسمي لحزب المواطنين التقدمي.',
                    ],
                    'pt' => [
                        'title'            => 'Início',
                        'body'             => '<p>Bem-vindo ao Partido Progressista dos Cidadãos. Estamos comprometidos em construir uma sociedade mais justa e sustentável para todos.</p>',
                        'meta_description' => 'Site oficial do Partido Progressista dos Cidadãos.',
                    ],
                ],
            ],
            [
                'slug'         => 'about',
                'template'     => PageTemplate::About,
                'status'       => ContentStatus::Published,
                'show_in_menu' => true,
                'translations' => [
                    'en' => [
                        'title'            => 'About Us',
                        'body'             => '<h2>Who We Are</h2><p>The Progressive Citizens Party was founded on the principles of democratic participation, social justice, and environmental responsibility. Our members come from all walks of life, united by a shared vision of a better future.</p><h2>Our Values</h2><ul><li>Transparency and accountability</li><li>Social solidarity</li><li>Sustainable development</li><li>Digital rights and civic freedoms</li></ul>',
                        'meta_description' => 'Learn about the Progressive Citizens Party, our history, values, and mission.',
                    ],
                    'fr' => [
                        'title'            => 'À propos',
                        'body'             => '<h2>Qui sommes-nous</h2><p>Le Parti Progressiste des Citoyens a été fondé sur les principes de la participation démocratique, de la justice sociale et de la responsabilité environnementale.</p><h2>Nos valeurs</h2><ul><li>Transparence et responsabilité</li><li>Solidarité sociale</li><li>Développement durable</li><li>Droits numériques et libertés civiques</li></ul>',
                        'meta_description' => 'Découvrez le Parti Progressiste des Citoyens, son histoire, ses valeurs et sa mission.',
                    ],
                    'ar' => [
                        'title'            => 'من نحن',
                        'body'             => '<h2>من نحن</h2><p>تأسس حزب المواطنين التقدمي على مبادئ المشاركة الديمقراطية والعدالة الاجتماعية والمسؤولية البيئية.</p><h2>قيمنا</h2><ul><li>الشفافية والمساءلة</li><li>التضامن الاجتماعي</li><li>التنمية المستدامة</li><li>الحقوق الرقمية والحريات المدنية</li></ul>',
                        'meta_description' => 'تعرّف على حزب المواطنين التقدمي وتاريخه وقيمه ورسالته.',
                    ],
                    'pt' => [
                        'title'            => 'Sobre Nós',
                        'body'             => '<h2>Quem Somos</h2><p>O Partido Progressista dos Cidadãos foi fundado nos princípios da participação democrática, da justiça social e da responsabilidade ambiental.</p><h2>Os Nossos Valores</h2><ul><li>Transparência e responsabilidade</li><li>Solidariedade social</li><li>Desenvolvimento sustentável</li><li>Direitos digitais e liberdades cívicas</li></ul>',
                        'meta_description' => 'Conheça o Partido Progressista dos Cidadãos, a sua história, valores e missão.',
                    ],
                ],
            ],
            [
                'slug'         => 'contact',
                'template'     => PageTemplate::Contact,
                'status'       => ContentStatus::Published,
                'show_in_menu' => true,
                'translations' => [
                    'en' => [
                        'title'            => 'Contact',
                        'body'             => '<p>Have a question or want to get in touch? Fill in the form below and we will respond within two working days.</p>',
                        'meta_description' => 'Contact the Progressive Citizens Party.',
                    ],
                    'fr' => [
                        'title'            => 'Contact',
                        'body'             => '<p>Vous avez une question ou souhaitez nous contacter ? Remplissez le formulaire ci-dessous et nous vous répondrons sous deux jours ouvrés.</p>',
                        'meta_description' => 'Contactez le Parti Progressiste des Citoyens.',
                    ],
                    'ar' => [
                        'title'            => 'اتصل بنا',
                        'body'             => '<p>هل لديك سؤال أو تريد التواصل معنا؟ املأ النموذج أدناه وسنرد عليك خلال يومي عمل.</p>',
                        'meta_description' => 'تواصل مع حزب المواطنين التقدمي.',
                    ],
                    'pt' => [
                        'title'            => 'Contacto',
                        'body'             => '<p>Tem uma dúvida ou quer entrar em contacto? Preencha o formulário abaixo e responderemos em dois dias úteis.</p>',
                        'meta_description' => 'Contacte o Partido Progressista dos Cidadãos.',
                    ],
                ],
            ],
            [
                'slug'         => 'join',
                'template'     => PageTemplate::Join,
                'status'       => ContentStatus::Published,
                'show_in_menu' => true,
                'translations' => [
                    'en' => [
                        'title'            => 'Join Us',
                        'body'             => '<p>Become a member of the Progressive Citizens Party and help shape the future. Membership is open to everyone who shares our values.</p>',
                        'meta_description' => 'Join the Progressive Citizens Party today.',
                    ],
                    'fr' => [
                        'title'            => 'Nous rejoindre',
                        'body'             => '<p>Devenez membre du Parti Progressiste des Citoyens et contribuez à façonner l\'avenir. L\'adhésion est ouverte à toute personne partageant nos valeurs.</p>',
                        'meta_description' => 'Rejoignez le Parti Progressiste des Citoyens dès aujourd\'hui.',
                    ],
                    'ar' => [
                        'title'            => 'انضم إلينا',
                        'body'             => '<p>كن عضواً في حزب المواطنين التقدمي وساهم في تشكيل المستقبل. العضوية مفتوحة للجميع ممن يشاركوننا قيمنا.</p>',
                        'meta_description' => 'انضم إلى حزب المواطنين التقدمي اليوم.',
                    ],
                    'pt' => [
                        'title'            => 'Aderir',
                        'body'             => '<p>Torne-se membro do Partido Progressista dos Cidadãos e ajude a construir o futuro. A adesão está aberta a todos que partilhem os nossos valores.</p>',
                        'meta_description' => 'Adira ao Partido Progressista dos Cidadãos hoje.',
                    ],
                ],
            ],
        ];

        foreach ($pages as $data) {
            $translations = $data['translations'];
            unset($data['translations']);

            $page = Page::updateOrCreate(['slug' => $data['slug']], $data);

            foreach ($translations as $locale => $t) {
                $page->translations()->updateOrCreate(
                    ['locale' => $locale],
                    array_merge($t, ['translated_by' => 'human', 'reviewed' => true])
                );
            }
        }
    }

    // ── Posts ─────────────────────────────────────────────────────────────────

    private function seedPosts(): void
    {
        $posts = [
            [
                'slug'        => 'party-wins-local-elections',
                'type'        => PostType::News,
                'status'      => ContentStatus::Published,
                'author_name' => 'Press Office',
                'translations' => [
                    'en' => [
                        'title'   => 'Party Wins Local Elections',
                        'summary' => 'The Progressive Citizens Party secured strong results in this year\'s local elections, gaining seats across multiple districts.',
                        'body'    => '<p>The Progressive Citizens Party secured strong results in this year\'s local elections, gaining seats in twelve districts and increasing its share of the vote by eight percentage points compared to the previous cycle.</p><p>Party President Élise Moreau thanked supporters and volunteers, pledging to work closely with local communities to deliver on the party\'s programme.</p>',
                    ],
                    'fr' => [
                        'title'   => 'Le Parti remporte les élections locales',
                        'summary' => 'Le Parti Progressiste des Citoyens a obtenu de solides résultats aux élections locales de cette année, gagnant des sièges dans plusieurs circonscriptions.',
                        'body'    => '<p>Le Parti Progressiste des Citoyens a obtenu de solides résultats aux élections locales de cette année, gagnant des sièges dans douze circonscriptions et augmentant sa part de vote de huit points de pourcentage par rapport au cycle précédent.</p>',
                    ],
                    'ar' => [
                        'title'   => 'الحزب يفوز في الانتخابات المحلية',
                        'summary' => 'حقق حزب المواطنين التقدمي نتائج قوية في الانتخابات المحلية لهذا العام، محققاً مقاعد في عدة دوائر.',
                        'body'    => '<p>حقق حزب المواطنين التقدمي نتائج قوية في الانتخابات المحلية لهذا العام، محققاً مقاعد في اثنتي عشرة دائرة وزيادة حصته من الأصوات بمقدار ثماني نقاط مئوية مقارنة بالدورة السابقة.</p>',
                    ],
                    'pt' => [
                        'title'   => 'Partido vence eleições autárquicas',
                        'summary' => 'O Partido Progressista dos Cidadãos obteve resultados sólidos nas eleições autárquicas deste ano, conquistando lugares em vários municípios.',
                        'body'    => '<p>O Partido Progressista dos Cidadãos obteve resultados sólidos nas eleições autárquicas deste ano, conquistando lugares em doze municípios e aumentando a sua quota de voto em oito pontos percentuais face ao ciclo anterior.</p>',
                    ],
                ],
            ],
            [
                'slug'        => 'new-housing-policy-unveiled',
                'type'        => PostType::News,
                'status'      => ContentStatus::Published,
                'author_name' => 'Press Office',
                'translations' => [
                    'en' => [
                        'title'   => 'New Housing Policy Unveiled',
                        'summary' => 'The party has announced a comprehensive housing policy aimed at making affordable homes available to all citizens.',
                        'body'    => '<p>The Progressive Citizens Party today unveiled its comprehensive housing policy, centred on three pillars: expanding social housing, capping private rent increases, and creating first-time buyer incentives for young people.</p><p>"Every family deserves a safe and affordable home," said Secretary General Sara Al-Rashidi at the launch event.</p>',
                    ],
                    'fr' => [
                        'title'   => 'Nouvelle politique du logement dévoilée',
                        'summary' => 'Le parti a annoncé une politique complète du logement visant à rendre des logements abordables accessibles à tous les citoyens.',
                        'body'    => '<p>Le Parti Progressiste des Citoyens a dévoilé aujourd\'hui sa politique complète du logement, articulée autour de trois axes : développement du logement social, plafonnement des hausses de loyers privés et création d\'incitations pour les primo-accédants.</p>',
                    ],
                    'ar' => [
                        'title'   => 'الكشف عن سياسة إسكان جديدة',
                        'summary' => 'أعلن الحزب عن سياسة إسكان شاملة تهدف إلى توفير مساكن ميسورة التكلفة لجميع المواطنين.',
                        'body'    => '<p>كشف حزب المواطنين التقدمي اليوم عن سياسته الشاملة للإسكان، التي تقوم على ثلاثة محاور: توسيع الإسكان الاجتماعي، وتحديد سقف لزيادات الإيجار الخاص، وإنشاء حوافز لمشتري المساكن لأول مرة.</p>',
                    ],
                    'pt' => [
                        'title'   => 'Nova política de habitação apresentada',
                        'summary' => 'O partido anunciou uma política de habitação abrangente com o objetivo de tornar casas acessíveis disponíveis para todos os cidadãos.',
                        'body'    => '<p>O Partido Progressista dos Cidadãos apresentou hoje a sua política de habitação, assente em três pilares: expansão da habitação social, limitação dos aumentos de rendas privadas e criação de incentivos para jovens compradores de primeira habitação.</p>',
                    ],
                ],
            ],
            [
                'slug'        => 'statement-on-climate-action',
                'type'        => PostType::Statement,
                'status'      => ContentStatus::Published,
                'author_name' => 'Élise Moreau, President',
                'translations' => [
                    'en' => [
                        'title'   => 'Statement on Climate Action',
                        'summary' => 'The party reaffirms its commitment to the transition to renewable energy and calls on the government to accelerate climate legislation.',
                        'body'    => '<p>The Progressive Citizens Party reaffirms its unwavering commitment to decisive climate action. The scientific consensus is clear: immediate and ambitious measures are required to limit global warming and protect the most vulnerable communities.</p><p>We call on the government to accelerate the adoption of binding climate legislation, increase investment in renewable energy, and ensure a just transition that leaves no worker behind.</p>',
                    ],
                    'fr' => [
                        'title'   => 'Déclaration sur l\'action climatique',
                        'summary' => 'Le parti réaffirme son engagement en faveur de la transition vers les énergies renouvelables et appelle le gouvernement à accélérer la législation climatique.',
                        'body'    => '<p>Le Parti Progressiste des Citoyens réaffirme son engagement sans faille en faveur d\'une action climatique décisive. Le consensus scientifique est clair : des mesures immédiates et ambitieuses sont nécessaires pour limiter le réchauffement climatique.</p>',
                    ],
                    'ar' => [
                        'title'   => 'بيان حول العمل المناخي',
                        'summary' => 'يؤكد الحزب من جديد التزامه بالتحول إلى الطاقة المتجددة ويدعو الحكومة إلى تسريع التشريعات المناخية.',
                        'body'    => '<p>يؤكد حزب المواطنين التقدمي من جديد التزامه الراسخ بالعمل المناخي الحاسم. الإجماع العلمي واضح: تُعدّ تدابير فورية وطموحة ضرورية للحد من الاحترار العالمي.</p>',
                    ],
                    'pt' => [
                        'title'   => 'Declaração sobre Ação Climática',
                        'summary' => 'O partido reafirma o seu compromisso com a transição para energias renováveis e apela ao governo para acelerar a legislação climática.',
                        'body'    => '<p>O Partido Progressista dos Cidadãos reafirma o seu compromisso inabalável com uma ação climática decisiva. O consenso científico é claro: medidas imediatas e ambiciosas são necessárias para limitar o aquecimento global.</p>',
                    ],
                ],
            ],
            [
                'slug'        => 'digital-rights-and-democracy',
                'type'        => PostType::Article,
                'status'      => ContentStatus::Published,
                'author_name' => 'James Okafor, Vice-President',
                'translations' => [
                    'en' => [
                        'title'   => 'Digital Rights and Democracy',
                        'summary' => 'An analysis of how strong digital rights frameworks can strengthen democratic participation and protect citizens from surveillance.',
                        'body'    => '<p>Digital rights are democratic rights. As more of our civic, political, and economic lives move online, the frameworks that govern the digital space have a direct impact on the quality of our democracy.</p><p>The Progressive Citizens Party believes that universal access to the internet, strong data protection laws, and transparent algorithmic governance are not optional extras — they are foundations of a modern democratic society.</p>',
                    ],
                    'fr' => [
                        'title'   => 'Droits numériques et démocratie',
                        'summary' => 'Une analyse de la façon dont de solides cadres de droits numériques peuvent renforcer la participation démocratique et protéger les citoyens contre la surveillance.',
                        'body'    => '<p>Les droits numériques sont des droits démocratiques. À mesure que notre vie civique, politique et économique se déplace en ligne, les cadres régissant l\'espace numérique ont un impact direct sur la qualité de notre démocratie.</p>',
                    ],
                    'ar' => [
                        'title'   => 'الحقوق الرقمية والديمقراطية',
                        'summary' => 'تحليل لكيفية تعزيز أُطر الحقوق الرقمية القوية للمشاركة الديمقراطية وحماية المواطنين من المراقبة.',
                        'body'    => '<p>الحقوق الرقمية هي حقوق ديمقراطية. مع انتقال المزيد من حياتنا المدنية والسياسية والاقتصادية إلى الفضاء الرقمي، باتت الأُطر التي تحكم هذا الفضاء تؤثر مباشرةً على جودة ديمقراطيتنا.</p>',
                    ],
                    'pt' => [
                        'title'   => 'Direitos Digitais e Democracia',
                        'summary' => 'Uma análise de como estruturas sólidas de direitos digitais podem fortalecer a participação democrática e proteger os cidadãos da vigilância.',
                        'body'    => '<p>Os direitos digitais são direitos democráticos. À medida que mais da nossa vida cívica, política e económica se transfere para o espaço digital, os quadros que regem esse espaço têm um impacto direto na qualidade da nossa democracia.</p>',
                    ],
                ],
            ],
        ];

        foreach ($posts as $data) {
            $translations = $data['translations'];
            unset($data['translations']);

            $post = Post::updateOrCreate(['slug' => $data['slug']], $data);

            foreach ($translations as $locale => $t) {
                $post->translations()->updateOrCreate(
                    ['locale' => $locale],
                    array_merge($t, ['translated_by' => 'human', 'reviewed' => true])
                );
            }
        }
    }

    // ── Events ────────────────────────────────────────────────────────────────

    private function seedEvents(): void
    {
        $events = [
            [
                'slug'      => 'annual-party-congress-2027',
                'starts_at' => '2027-03-15 09:00:00',
                'ends_at'   => '2027-03-16 18:00:00',
                'timezone'  => 'Europe/Paris',
                'location'  => 'Central Conference Centre',
                'online_url'=> null,
                'status'    => ContentStatus::Published,
                'translations' => [
                    'en' => [
                        'title'          => 'Annual Party Congress 2027',
                        'description'    => '<p>The Annual Party Congress brings together delegates from all regions to set the party\'s political direction for the coming year. This year\'s theme is <em>Solidarity in Action</em>.</p><p>Agenda highlights include policy workshops, keynote speeches, and the election of party officers.</p>',
                        'location_label' => 'Central Conference Centre',
                    ],
                    'fr' => [
                        'title'          => 'Congrès annuel du Parti 2027',
                        'description'    => '<p>Le Congrès annuel du Parti réunit des délégués de toutes les régions pour définir l\'orientation politique du parti pour l\'année à venir. Le thème de cette année est <em>La solidarité en action</em>.</p>',
                        'location_label' => 'Centre de conférences central',
                    ],
                    'ar' => [
                        'title'          => 'المؤتمر السنوي للحزب 2027',
                        'description'    => '<p>يجمع المؤتمر السنوي للحزب مندوبين من جميع المناطق لتحديد التوجه السياسي للحزب للعام القادم. شعار هذا العام هو <em>التضامن في العمل</em>.</p>',
                        'location_label' => 'مركز المؤتمرات المركزي',
                    ],
                    'pt' => [
                        'title'          => 'Congresso Anual do Partido 2027',
                        'description'    => '<p>O Congresso Anual do Partido reúne delegados de todas as regiões para definir a orientação política do partido para o próximo ano. O tema deste ano é <em>Solidariedade em Ação</em>.</p>',
                        'location_label' => 'Centro de Conferências Central',
                    ],
                ],
            ],
            [
                'slug'      => 'community-forum-spring-2027',
                'starts_at' => '2027-01-25 14:00:00',
                'ends_at'   => '2027-01-25 18:00:00',
                'timezone'  => 'Europe/Paris',
                'location'  => null,
                'online_url'=> 'https://meet.example.org/pcp-forum',
                'status'    => ContentStatus::Published,
                'translations' => [
                    'en' => [
                        'title'          => 'Community Forum — Spring 2027',
                        'description'    => '<p>An open online forum for members and supporters to discuss local issues and contribute ideas ahead of the Annual Congress.</p><p>Register via the link below to receive joining details.</p>',
                        'location_label' => 'Online',
                    ],
                    'fr' => [
                        'title'          => 'Forum communautaire — Printemps 2027',
                        'description'    => '<p>Un forum en ligne ouvert aux membres et sympathisants pour discuter des enjeux locaux et contribuer des idées avant le Congrès annuel.</p>',
                        'location_label' => 'En ligne',
                    ],
                    'ar' => [
                        'title'          => 'المنتدى المجتمعي — ربيع 2027',
                        'description'    => '<p>منتدى إلكتروني مفتوح للأعضاء والمؤيدين لمناقشة القضايا المحلية والمساهمة بالأفكار قبيل المؤتمر السنوي.</p>',
                        'location_label' => 'عبر الإنترنت',
                    ],
                    'pt' => [
                        'title'          => 'Fórum Comunitário — Primavera 2027',
                        'description'    => '<p>Um fórum online aberto para membros e apoiantes discutirem questões locais e contribuírem com ideias antes do Congresso Anual.</p>',
                        'location_label' => 'Online',
                    ],
                ],
            ],
        ];

        foreach ($events as $data) {
            $translations = $data['translations'];
            unset($data['translations']);

            $event = Event::updateOrCreate(['slug' => $data['slug']], $data);

            foreach ($translations as $locale => $t) {
                $event->translations()->updateOrCreate(
                    ['locale' => $locale],
                    array_merge($t, ['translated_by' => 'human', 'reviewed' => true])
                );
            }
        }
    }

    // ── People ────────────────────────────────────────────────────────────────

    private function seedPeople(): void
    {
        $people = [
            [
                'name'        => 'Élise Moreau',
                'sort'        => 1,
                'group'       => PersonGroup::Leadership,
                'show_email'  => false,
                'show_phone'  => false,
                'translations' => [
                    'en' => ['position' => 'President',              'bio' => '<p>Élise Moreau has led the Progressive Citizens Party since 2021. A former community organiser, she brings a grassroots perspective to national politics.</p>'],
                    'fr' => ['position' => 'Présidente',             'bio' => '<p>Élise Moreau dirige le Parti Progressiste des Citoyens depuis 2021. Ancienne organisatrice communautaire, elle apporte une perspective de terrain à la politique nationale.</p>'],
                    'ar' => ['position' => 'رئيسة الحزب',            'bio' => '<p>تقود إليز مورو حزب المواطنين التقدمي منذ عام 2021. ومنظِّمة مجتمعية سابقة، تُدخل منظوراً شعبياً إلى السياسة الوطنية.</p>'],
                    'pt' => ['position' => 'Presidente',             'bio' => '<p>Élise Moreau lidera o Partido Progressista dos Cidadãos desde 2021. Ex-organizadora comunitária, traz uma perspetiva de base para a política nacional.</p>'],
                ],
            ],
            [
                'name'        => 'James Okafor',
                'sort'        => 2,
                'group'       => PersonGroup::Leadership,
                'show_email'  => false,
                'show_phone'  => false,
                'translations' => [
                    'en' => ['position' => 'Vice-President',         'bio' => '<p>James Okafor is an economist and civic activist who has championed digital rights and education reform throughout his career.</p>'],
                    'fr' => ['position' => 'Vice-Président',         'bio' => '<p>James Okafor est économiste et militant civique, défenseur des droits numériques et de la réforme de l\'éducation tout au long de sa carrière.</p>'],
                    'ar' => ['position' => 'نائب الرئيس',            'bio' => '<p>جيمس أوكافور اقتصادي وناشط مدني، دافع عن حقوق الرقمية وإصلاح التعليم طوال مسيرته المهنية.</p>'],
                    'pt' => ['position' => 'Vice-Presidente',        'bio' => '<p>James Okafor é economista e ativista cívico, defensor dos direitos digitais e da reforma educativa ao longo da sua carreira.</p>'],
                ],
            ],
            [
                'name'        => 'Sara Al-Rashidi',
                'sort'        => 3,
                'group'       => PersonGroup::Executive,
                'show_email'  => false,
                'show_phone'  => false,
                'translations' => [
                    'en' => ['position' => 'Secretary General',      'bio' => '<p>Sara Al-Rashidi coordinates the party\'s day-to-day operations and is the primary point of contact for affiliated organisations.</p>'],
                    'fr' => ['position' => 'Secrétaire Générale',    'bio' => '<p>Sara Al-Rachidi coordonne les opérations quotidiennes du parti et constitue le principal point de contact des organisations affiliées.</p>'],
                    'ar' => ['position' => 'الأمينة العامة',          'bio' => '<p>تُنسّق سارة الراشدي العمليات اليومية للحزب وتُعدّ جهة الاتصال الرئيسية للمنظمات المنتسبة.</p>'],
                    'pt' => ['position' => 'Secretária-Geral',       'bio' => '<p>Sara Al-Rashidi coordena as operações diárias do partido e é o principal ponto de contacto para as organizações afiliadas.</p>'],
                ],
            ],
            [
                'name'        => 'Tomás Ferreira',
                'sort'        => 4,
                'group'       => PersonGroup::Executive,
                'show_email'  => false,
                'show_phone'  => false,
                'translations' => [
                    'en' => ['position' => 'Treasurer',              'bio' => '<p>Tomás Ferreira oversees the party\'s finances and ensures full transparency in the management of membership contributions and public funding.</p>'],
                    'fr' => ['position' => 'Trésorier',              'bio' => '<p>Tomás Ferreira supervise les finances du parti et veille à la pleine transparence dans la gestion des cotisations et des financements publics.</p>'],
                    'ar' => ['position' => 'أمين الصندوق',            'bio' => '<p>يُشرف توماس فيريرا على مالية الحزب ويضمن الشفافية الكاملة في إدارة اشتراكات الأعضاء والتمويل العام.</p>'],
                    'pt' => ['position' => 'Tesoureiro',             'bio' => '<p>Tomás Ferreira supervisiona as finanças do partido e assegura total transparência na gestão das quotas dos membros e do financiamento público.</p>'],
                ],
            ],
            [
                'name'        => 'Nadia Hussain',
                'sort'        => 5,
                'group'       => PersonGroup::Executive,
                'show_email'  => false,
                'show_phone'  => false,
                'translations' => [
                    'en' => ['position' => 'Head of Communications', 'bio' => '<p>Nadia Hussain leads the party\'s communications and media strategy, with a focus on accessible, multilingual outreach.</p>'],
                    'fr' => ['position' => 'Responsable communication','bio' => '<p>Nadia Hussain dirige la stratégie de communication et médias du parti, avec un accent sur la diffusion accessible et multilingue.</p>'],
                    'ar' => ['position' => 'رئيسة قسم الإعلام',       'bio' => '<p>تقود نادية حسين استراتيجية الإعلام والتواصل في الحزب، مع تركيز على الوصول الميسّر والتعدد اللغوي.</p>'],
                    'pt' => ['position' => 'Responsável de Comunicação','bio' => '<p>Nadia Hussain lidera a estratégia de comunicação e media do partido, com foco na divulgação acessível e multilingue.</p>'],
                ],
            ],
        ];

        foreach ($people as $data) {
            $translations = $data['translations'];
            unset($data['translations']);

            $person = Person::updateOrCreate(['name' => $data['name']], $data);

            foreach ($translations as $locale => $t) {
                $person->translations()->updateOrCreate(
                    ['locale' => $locale],
                    array_merge($t, ['translated_by' => 'human', 'reviewed' => true])
                );
            }
        }
    }

    // ── Documents ─────────────────────────────────────────────────────────────

    private function seedDocuments(): void
    {
        $document = Document::updateOrCreate(
            ['file' => 'demo/party-manifesto-2026.pdf'],
            [
                'file_size' => 0,
                'mime_type' => 'application/pdf',
                'category'  => DocumentCategory::Manifesto,
            ]
        );

        $docTranslations = [
            'en' => [
                'title'       => 'Party Manifesto 2026',
                'description' => 'The full text of the Progressive Citizens Party manifesto for 2026, covering housing, climate, digital rights, and social policy.',
            ],
            'fr' => [
                'title'       => 'Programme du Parti 2026',
                'description' => 'Le texte intégral du programme du Parti Progressiste des Citoyens pour 2026, couvrant le logement, le climat, les droits numériques et la politique sociale.',
            ],
            'ar' => [
                'title'       => 'برنامج الحزب 2026',
                'description' => 'النص الكامل لبرنامج حزب المواطنين التقدمي لعام 2026، يشمل الإسكان والمناخ والحقوق الرقمية والسياسة الاجتماعية.',
            ],
            'pt' => [
                'title'       => 'Programa do Partido 2026',
                'description' => 'O texto integral do programa do Partido Progressista dos Cidadãos para 2026, abrangendo habitação, clima, direitos digitais e política social.',
            ],
        ];

        foreach ($docTranslations as $locale => $t) {
            $document->translations()->updateOrCreate(
                ['locale' => $locale],
                array_merge($t, ['translated_by' => 'human', 'reviewed' => true])
            );
        }
    }

    // ── Menus ─────────────────────────────────────────────────────────────────

    private function seedMenus(): void
    {
        $locales = ['en', 'fr', 'ar', 'pt'];

        $menuDefs = [
            'header' => [
                'en' => [
                    ['url' => '/en',          'label' => ['en' => 'Home',     'fr' => 'Accueil',          'ar' => 'الرئيسية',  'pt' => 'Início']],
                    ['url' => '/en/about',    'label' => ['en' => 'About',    'fr' => 'À propos',         'ar' => 'من نحن',    'pt' => 'Sobre']],
                    ['url' => '/en/news',     'label' => ['en' => 'News',     'fr' => 'Actualités',       'ar' => 'الأخبار',   'pt' => 'Notícias']],
                    ['url' => '/en/events',   'label' => ['en' => 'Events',   'fr' => 'Événements',       'ar' => 'الفعاليات', 'pt' => 'Eventos']],
                    ['url' => '/en/join',     'label' => ['en' => 'Join',     'fr' => 'Nous rejoindre',   'ar' => 'انضم إلينا','pt' => 'Aderir']],
                ],
                'fr' => [
                    ['url' => '/fr',          'label' => ['en' => 'Home',     'fr' => 'Accueil',          'ar' => 'الرئيسية',  'pt' => 'Início']],
                    ['url' => '/fr/about',    'label' => ['en' => 'About',    'fr' => 'À propos',         'ar' => 'من نحن',    'pt' => 'Sobre']],
                    ['url' => '/fr/news',     'label' => ['en' => 'News',     'fr' => 'Actualités',       'ar' => 'الأخبار',   'pt' => 'Notícias']],
                    ['url' => '/fr/events',   'label' => ['en' => 'Events',   'fr' => 'Événements',       'ar' => 'الفعاليات', 'pt' => 'Eventos']],
                    ['url' => '/fr/join',     'label' => ['en' => 'Join',     'fr' => 'Nous rejoindre',   'ar' => 'انضم إلينا','pt' => 'Aderir']],
                ],
                'ar' => [
                    ['url' => '/ar',          'label' => ['en' => 'Home',     'fr' => 'Accueil',          'ar' => 'الرئيسية',  'pt' => 'Início']],
                    ['url' => '/ar/about',    'label' => ['en' => 'About',    'fr' => 'À propos',         'ar' => 'من نحن',    'pt' => 'Sobre']],
                    ['url' => '/ar/news',     'label' => ['en' => 'News',     'fr' => 'Actualités',       'ar' => 'الأخبار',   'pt' => 'Notícias']],
                    ['url' => '/ar/events',   'label' => ['en' => 'Events',   'fr' => 'Événements',       'ar' => 'الفعاليات', 'pt' => 'Eventos']],
                    ['url' => '/ar/join',     'label' => ['en' => 'Join',     'fr' => 'Nous rejoindre',   'ar' => 'انضم إلينا','pt' => 'Aderir']],
                ],
                'pt' => [
                    ['url' => '/pt',          'label' => ['en' => 'Home',     'fr' => 'Accueil',          'ar' => 'الرئيسية',  'pt' => 'Início']],
                    ['url' => '/pt/about',    'label' => ['en' => 'About',    'fr' => 'À propos',         'ar' => 'من نحن',    'pt' => 'Sobre']],
                    ['url' => '/pt/news',     'label' => ['en' => 'News',     'fr' => 'Actualités',       'ar' => 'الأخبار',   'pt' => 'Notícias']],
                    ['url' => '/pt/events',   'label' => ['en' => 'Events',   'fr' => 'Événements',       'ar' => 'الفعاليات', 'pt' => 'Eventos']],
                    ['url' => '/pt/join',     'label' => ['en' => 'Join',     'fr' => 'Nous rejoindre',   'ar' => 'انضم إلينا','pt' => 'Aderir']],
                ],
            ],
            'footer' => [
                'en' => [
                    ['url' => '/en/about',     'label' => ['en' => 'About',     'fr' => 'À propos',   'ar' => 'من نحن',      'pt' => 'Sobre']],
                    ['url' => '/en/contact',   'label' => ['en' => 'Contact',   'fr' => 'Contact',    'ar' => 'اتصل بنا',    'pt' => 'Contacto']],
                    ['url' => '/en/documents', 'label' => ['en' => 'Documents', 'fr' => 'Documents',  'ar' => 'مستندات',     'pt' => 'Documentos']],
                ],
                'fr' => [
                    ['url' => '/fr/about',     'label' => ['en' => 'About',     'fr' => 'À propos',   'ar' => 'من نحن',      'pt' => 'Sobre']],
                    ['url' => '/fr/contact',   'label' => ['en' => 'Contact',   'fr' => 'Contact',    'ar' => 'اتصل بنا',    'pt' => 'Contacto']],
                    ['url' => '/fr/documents', 'label' => ['en' => 'Documents', 'fr' => 'Documents',  'ar' => 'مستندات',     'pt' => 'Documentos']],
                ],
                'ar' => [
                    ['url' => '/ar/about',     'label' => ['en' => 'About',     'fr' => 'À propos',   'ar' => 'من نحن',      'pt' => 'Sobre']],
                    ['url' => '/ar/contact',   'label' => ['en' => 'Contact',   'fr' => 'Contact',    'ar' => 'اتصل بنا',    'pt' => 'Contacto']],
                    ['url' => '/ar/documents', 'label' => ['en' => 'Documents', 'fr' => 'Documents',  'ar' => 'مستندات',     'pt' => 'Documentos']],
                ],
                'pt' => [
                    ['url' => '/pt/about',     'label' => ['en' => 'About',     'fr' => 'À propos',   'ar' => 'من نحن',      'pt' => 'Sobre']],
                    ['url' => '/pt/contact',   'label' => ['en' => 'Contact',   'fr' => 'Contact',    'ar' => 'اتصل بنا',    'pt' => 'Contacto']],
                    ['url' => '/pt/documents', 'label' => ['en' => 'Documents', 'fr' => 'Documents',  'ar' => 'مستندات',     'pt' => 'Documentos']],
                ],
            ],
        ];

        foreach ($menuDefs as $locationStr => $localeItems) {
            $menu = Menu::firstOrCreate(['location' => MenuLocation::from($locationStr)]);

            foreach ($locales as $locale) {
                $menu->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'items'         => $localeItems[$locale] ?? [],
                        'translated_by' => 'human',
                        'reviewed'      => true,
                    ]
                );
            }
        }
    }
}
