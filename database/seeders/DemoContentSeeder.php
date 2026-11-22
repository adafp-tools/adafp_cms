<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentTranslation;
use App\Models\Event;
use App\Models\EventTranslation;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\GalleryImageTranslation;
use App\Models\GalleryTranslation;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuTranslation;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Person;
use App\Models\PersonTranslation;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a complete, fictional demo installation for the "National Progress Party".
 * All content is in EN, FR, PT, AR.
 *
 * Run as part of cms:demo-reset — not in production migrations.
 */
class DemoContentSeeder extends Seeder
{
    // Fictional party — four locales
    private const LOCALES = ['en', 'fr', 'pt', 'ar'];

    public function run(): void
    {
        $this->seedSettings();
        $this->seedUsers();
        $this->seedPages();
        $this->seedPosts();
        $this->seedEvents();
        $this->seedPeople();
        $this->seedDocuments();
        $this->seedGalleries();
        $this->seedMenus();
    }

    // -------------------------------------------------------------------------
    // Settings
    // -------------------------------------------------------------------------

    private function seedSettings(): void
    {
        $settings = [
            'site_name'        => 'National Progress Party',
            'default_locale'   => 'en',
            'active_locales'   => 'en,fr,pt,ar',
            'theme'            => 'classic',
            'contact_email'    => 'contact@example-npp.test',
            'maintenance_mode' => '0',
            'noindex'          => '1',   // demo — always noindex
            'join_form_enabled'    => '1',
            'contact_form_enabled' => '1',
            'org_name'         => 'National Progress Party',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }

    // -------------------------------------------------------------------------
    // Users
    // -------------------------------------------------------------------------

    private function seedUsers(): void
    {
        // Owner / admin demo account — password printed nowhere; .env controls it
        $email = config('cms.demo_admin_email', 'demo@example-npp.test');

        if (! User::where('email', $email)->exists()) {
            User::create([
                'name'     => 'Demo Administrator',
                'email'    => $email,
                'password' => Hash::make(config('cms.demo_admin_password', 'changeme-in-env')),
                'role'     => 'owner',
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Pages
    // -------------------------------------------------------------------------

    private function seedPages(): void
    {
        $pages = [
            [
                'slug'       => 'about',
                'template'   => 'about',
                'status'     => 'published',
                'sort'       => 1,
                'show_in_menu' => true,
                'published_at' => now()->subDays(30),
                'translations' => [
                    'en' => [
                        'title' => 'About Us',
                        'body'  => '<p>The National Progress Party was founded in 2010 with a commitment to democratic governance, social justice, and sustainable development. We believe that strong institutions, transparent government, and inclusive policies are the foundation of a prosperous nation.</p><p>Our members come from every region of the country, united by shared values and a determination to build a better future for all citizens.</p>',
                        'meta_description' => 'Learn about the National Progress Party — our history, values, and mission.',
                    ],
                    'fr' => [
                        'title' => 'À propos de nous',
                        'body'  => '<p>Le Parti du Progrès National a été fondé en 2010 avec un engagement envers la gouvernance démocratique, la justice sociale et le développement durable. Nous croyons que des institutions solides, un gouvernement transparent et des politiques inclusives constituent le fondement d\'une nation prospère.</p><p>Nos membres viennent de toutes les régions du pays, unis par des valeurs communes et une détermination à construire un avenir meilleur pour tous les citoyens.</p>',
                        'meta_description' => 'En savoir plus sur le Parti du Progrès National — notre histoire, nos valeurs et notre mission.',
                    ],
                    'pt' => [
                        'title' => 'Sobre nós',
                        'body'  => '<p>O Partido do Progresso Nacional foi fundado em 2010 com um compromisso com a governação democrática, a justiça social e o desenvolvimento sustentável. Acreditamos que instituições sólidas, um governo transparente e políticas inclusivas são a base de uma nação próspera.</p><p>Os nossos membros vêm de todas as regiões do país, unidos por valores comuns e pela determinação de construir um futuro melhor para todos os cidadãos.</p>',
                        'meta_description' => 'Saiba mais sobre o Partido do Progresso Nacional — a nossa história, valores e missão.',
                    ],
                    'ar' => [
                        'title' => 'من نحن',
                        'body'  => '<p>تأسس حزب التقدم الوطني عام 2010 بالتزام راسخ بالحوكمة الديمقراطية والعدالة الاجتماعية والتنمية المستدامة. نؤمن بأن المؤسسات القوية والحكومة الشفافة والسياسات الشاملة هي أساس الازدهار الوطني.</p><p>يأتي أعضاؤنا من كل مناطق البلاد، متحدين بقيم مشتركة وعزيمة راسخة لبناء مستقبل أفضل لجميع المواطنين.</p>',
                        'meta_description' => 'تعرّف على حزب التقدم الوطني — تاريخنا وقيمنا ورسالتنا.',
                    ],
                ],
            ],
            [
                'slug'       => 'programme',
                'template'   => 'default',
                'status'     => 'published',
                'sort'       => 2,
                'show_in_menu' => true,
                'published_at' => now()->subDays(28),
                'translations' => [
                    'en' => [
                        'title' => 'Our Programme',
                        'body'  => '<h2>Economy</h2><p>We will invest in small and medium enterprises, reduce bureaucracy, and create an environment where businesses can thrive and create jobs for all citizens.</p><h2>Education</h2><p>Quality education is a right, not a privilege. We will increase education funding, improve teacher salaries, and expand access to universities and vocational training.</p><h2>Health</h2><p>Universal healthcare coverage by 2030. We will build new clinics in underserved rural areas and ensure medicines are affordable for everyone.</p><h2>Environment</h2><p>Transition to 50% renewable energy by 2035. Protect national parks, rivers and coastlines for future generations.</p>',
                        'meta_description' => 'Read the National Progress Party electoral programme across four key areas.',
                    ],
                    'fr' => [
                        'title' => 'Notre programme',
                        'body'  => '<h2>Économie</h2><p>Nous investirons dans les petites et moyennes entreprises, réduirons la bureaucratie et créerons un environnement où les entreprises peuvent prospérer et créer des emplois pour tous les citoyens.</p><h2>Éducation</h2><p>Une éducation de qualité est un droit, non un privilège. Nous augmenterons le financement de l\'éducation, améliorerons les salaires des enseignants et élargirons l\'accès aux universités et à la formation professionnelle.</p><h2>Santé</h2><p>Couverture santé universelle d\'ici 2030. Nous construirons de nouvelles cliniques dans les zones rurales mal desservies.</p><h2>Environnement</h2><p>Transition vers 50 % d\'énergie renouvelable d\'ici 2035. Protection des parcs nationaux, des rivières et des côtes pour les générations futures.</p>',
                        'meta_description' => 'Lisez le programme électoral du Parti du Progrès National sur quatre domaines clés.',
                    ],
                    'pt' => [
                        'title' => 'O nosso programa',
                        'body'  => '<h2>Economia</h2><p>Investiremos nas pequenas e médias empresas, reduziremos a burocracia e criaremos um ambiente propício ao crescimento empresarial e à criação de emprego.</p><h2>Educação</h2><p>A educação de qualidade é um direito, não um privilégio. Aumentaremos o financiamento da educação, melhoraremos os salários dos professores e ampliaremos o acesso às universidades.</p><h2>Saúde</h2><p>Cobertura universal de saúde até 2030. Construiremos novas clínicas em zonas rurais carenciadas.</p><h2>Ambiente</h2><p>Transição para 50% de energia renovável até 2035. Proteção dos parques nacionais, rios e costas para as gerações futuras.</p>',
                        'meta_description' => 'Leia o programa eleitoral do Partido do Progresso Nacional em quatro áreas-chave.',
                    ],
                    'ar' => [
                        'title' => 'برنامجنا',
                        'body'  => '<h2>الاقتصاد</h2><p>سنستثمر في الشركات الصغيرة والمتوسطة، ونقلل من البيروقراطية، ونهيئ بيئة ملائمة لازدهار الأعمال وخلق فرص العمل لجميع المواطنين.</p><h2>التعليم</h2><p>التعليم الجيد حق لا امتياز. سنزيد تمويل التعليم ونحسن رواتب المعلمين ونوسع إمكانية الوصول إلى الجامعات والتدريب المهني.</p><h2>الصحة</h2><p>تغطية صحية شاملة بحلول عام 2030. سنبني عيادات جديدة في المناطق الريفية المحرومة.</p><h2>البيئة</h2><p>الانتقال إلى 50% من الطاقة المتجددة بحلول عام 2035. حماية المتنزهات الوطنية والأنهار والسواحل للأجيال القادمة.</p>',
                        'meta_description' => 'اقرأ البرنامج الانتخابي لحزب التقدم الوطني في أربعة مجالات رئيسية.',
                    ],
                ],
            ],
            [
                'slug'       => 'contact',
                'template'   => 'contact',
                'status'     => 'published',
                'sort'       => 10,
                'show_in_menu' => true,
                'published_at' => now()->subDays(25),
                'translations' => [
                    'en' => [
                        'title' => 'Contact',
                        'body'  => '<p>We welcome your questions, suggestions and feedback. Fill in the form below and our team will respond within two working days.</p>',
                        'meta_description' => 'Contact the National Progress Party.',
                    ],
                    'fr' => [
                        'title' => 'Contact',
                        'body'  => '<p>Nous accueillons vos questions, suggestions et commentaires. Remplissez le formulaire ci-dessous et notre équipe vous répondra dans les deux jours ouvrables.</p>',
                        'meta_description' => 'Contactez le Parti du Progrès National.',
                    ],
                    'pt' => [
                        'title' => 'Contacto',
                        'body'  => '<p>Bem-vindas as suas perguntas, sugestões e comentários. Preencha o formulário abaixo e a nossa equipa responderá em dois dias úteis.</p>',
                        'meta_description' => 'Contacte o Partido do Progresso Nacional.',
                    ],
                    'ar' => [
                        'title' => 'اتصل بنا',
                        'body'  => '<p>نرحب بأسئلتكم واقتراحاتكم وملاحظاتكم. املأ النموذج أدناه وسيرد فريقنا خلال يومي عمل.</p>',
                        'meta_description' => 'تواصل مع حزب التقدم الوطني.',
                    ],
                ],
            ],
            [
                'slug'       => 'join',
                'template'   => 'join',
                'status'     => 'published',
                'sort'       => 11,
                'show_in_menu' => true,
                'published_at' => now()->subDays(25),
                'translations' => [
                    'en' => [
                        'title' => 'Join Us',
                        'body'  => '<p>Become a member of the National Progress Party and help shape the future of our country. As a member you receive our newsletter, can vote at party congresses, and join local chapters in your city.</p>',
                        'meta_description' => 'Join the National Progress Party as a member.',
                    ],
                    'fr' => [
                        'title' => 'Nous rejoindre',
                        'body'  => '<p>Devenez membre du Parti du Progrès National et contribuez à façonner l\'avenir de notre pays. En tant que membre, vous recevrez notre newsletter, pourrez voter lors des congrès du parti et rejoindre des sections locales dans votre ville.</p>',
                        'meta_description' => 'Rejoignez le Parti du Progrès National en tant que membre.',
                    ],
                    'pt' => [
                        'title' => 'Junte-se a nós',
                        'body'  => '<p>Torne-se membro do Partido do Progresso Nacional e ajude a moldar o futuro do nosso país. Como membro, receberá a nossa newsletter, poderá votar nos congressos do partido e aderir a secções locais na sua cidade.</p>',
                        'meta_description' => 'Junte-se ao Partido do Progresso Nacional como membro.',
                    ],
                    'ar' => [
                        'title' => 'انضم إلينا',
                        'body'  => '<p>كن عضواً في حزب التقدم الوطني وساعدنا في تشكيل مستقبل بلدنا. بوصفك عضواً ستتلقى نشرتنا الإخبارية، ويمكنك التصويت في مؤتمرات الحزب والانضمام إلى الفروع المحلية في مدينتك.</p>',
                        'meta_description' => 'انضم إلى حزب التقدم الوطني عضواً.',
                    ],
                ],
            ],
        ];

        foreach ($pages as $data) {
            $translations = $data['translations'];
            unset($data['translations']);

            $page = Page::create($data);

            foreach ($translations as $locale => $t) {
                PageTranslation::create(array_merge(['page_id' => $page->id, 'locale' => $locale], $t));
            }
        }
    }

    // -------------------------------------------------------------------------
    // Posts (news / statements)
    // -------------------------------------------------------------------------

    private function seedPosts(): void
    {
        $posts = [
            [
                'slug'        => 'npp-wins-regional-elections',
                'type'        => 'news',
                'status'      => 'published',
                'published_at' => now()->subDays(5),
                'author_name' => 'Press Office',
                'translations' => [
                    'en' => [
                        'title'   => 'NPP Wins Regional Elections in Five Provinces',
                        'summary' => 'Historic victory as the National Progress Party secures majorities in five of eight regional assemblies.',
                        'body'    => '<p>The National Progress Party secured a historic victory last Sunday, winning majority seats in five of the country\'s eight regional assemblies. Party leader Dr. Amara Diallo called it "a mandate for real change" and pledged to begin delivering on the party\'s programme within 100 days.</p><p>"The people have spoken," said Dr. Diallo at the party\'s victory gathering. "They want better schools, better clinics, and an economy that works for everyone — not just those at the top."</p><p>Turnout reached 68%, the highest in a regional election since 2004.</p>',
                        'meta_description' => 'NPP wins majorities in five regional assemblies in historic Sunday election.',
                    ],
                    'fr' => [
                        'title'   => 'Le PPN remporte les élections régionales dans cinq provinces',
                        'summary' => 'Victoire historique du Parti du Progrès National qui obtient des majorités dans cinq des huit assemblées régionales.',
                        'body'    => '<p>Le Parti du Progrès National a remporté une victoire historique dimanche dernier, obtenant la majorité dans cinq des huit assemblées régionales du pays. Le chef du parti, le Dr Amara Diallo, a qualifié ce résultat de « mandat pour un vrai changement » et s\'est engagé à mettre en œuvre le programme du parti dans les 100 jours.</p><p>« Le peuple a parlé », a déclaré le Dr Diallo lors du rassemblement de victoire du parti. « Il veut de meilleures écoles, de meilleures cliniques et une économie qui fonctionne pour tout le monde. »</p><p>La participation a atteint 68 %, la plus élevée lors d\'une élection régionale depuis 2004.</p>',
                        'meta_description' => 'Le PPN remporte des majorités dans cinq assemblées régionales lors d\'une élection historique.',
                    ],
                    'pt' => [
                        'title'   => 'O PPN ganha eleições regionais em cinco províncias',
                        'summary' => 'Vitória histórica do Partido do Progresso Nacional, que obtém maioria em cinco das oito assembleias regionais.',
                        'body'    => '<p>O Partido do Progresso Nacional obteve uma vitória histórica no domingo passado, conquistando a maioria em cinco das oito assembleias regionais do país. O líder do partido, Dr. Amara Diallo, classificou o resultado como "um mandato para a mudança real" e comprometeu-se a cumprir o programa do partido em 100 dias.</p><p>"O povo falou", disse o Dr. Diallo na celebração da vitória. "Quer melhores escolas, melhores clínicas e uma economia que funcione para todos."</p><p>A participação atingiu 68%, a mais alta numa eleição regional desde 2004.</p>',
                        'meta_description' => 'O PPN conquista maiorias em cinco assembleias regionais numa eleição histórica.',
                    ],
                    'ar' => [
                        'title'   => 'حزب التقدم الوطني يفوز بانتخابات المناطق في خمس مقاطعات',
                        'summary' => 'انتصار تاريخي لحزب التقدم الوطني يحقق فيه الأغلبية في خمس من أصل ثماني مجالس إقليمية.',
                        'body'    => '<p>حقق حزب التقدم الوطني انتصاراً تاريخياً الأحد الماضي، إذ حصل على أغلبية المقاعد في خمس من ثماني مجالس إقليمية في البلاد. ووصف زعيم الحزب الدكتور عمارة ديالو النتيجة بأنها "تفويض للتغيير الحقيقي"، وتعهد بالشروع في تنفيذ برنامج الحزب خلال 100 يوم.</p><p>وقال الدكتور ديالو في احتفال النصر: "لقد تكلم الشعب. إنه يريد مدارس أفضل وعيادات أفضل واقتصاداً يخدم الجميع."</p><p>بلغت نسبة المشاركة 68%، وهي الأعلى في انتخابات إقليمية منذ عام 2004.</p>',
                        'meta_description' => 'حزب التقدم الوطني يحقق الأغلبية في خمس مجالس إقليمية في انتخابات تاريخية.',
                    ],
                ],
            ],
            [
                'slug'        => 'new-healthcare-bill-passed',
                'type'        => 'statement',
                'status'      => 'published',
                'published_at' => now()->subDays(12),
                'author_name' => 'Dr. Amara Diallo',
                'translations' => [
                    'en' => [
                        'title'   => 'Statement on the New Healthcare Bill',
                        'summary' => 'Party leader Dr. Amara Diallo welcomes the passage of the Universal Healthcare Access Act.',
                        'body'    => '<p>Today marks a historic step forward for our country. After three years of advocacy by our party and civil society partners, the National Assembly has passed the Universal Healthcare Access Act with 72% of votes in favour.</p><p>This legislation will extend primary healthcare coverage to two million citizens who currently have none. It is a down payment on our promise of universal coverage by 2030.</p><p>We call on the government to implement the act swiftly and transparently, and we will monitor progress closely in the months ahead.</p>',
                        'meta_description' => 'NPP statement welcoming the Universal Healthcare Access Act.',
                    ],
                    'fr' => [
                        'title'   => 'Déclaration sur le nouveau projet de loi sur la santé',
                        'summary' => 'Le chef du parti, le Dr Amara Diallo, se félicite de l\'adoption de la loi sur l\'accès universel aux soins de santé.',
                        'body'    => '<p>Aujourd\'hui marque une étape historique pour notre pays. Après trois ans de plaidoyer de notre parti et de partenaires de la société civile, l\'Assemblée nationale a adopté la loi sur l\'accès universel aux soins de santé avec 72 % des voix pour.</p><p>Cette législation étendra la couverture des soins de santé primaires à deux millions de citoyens qui n\'en bénéficient pas actuellement. C\'est un acompte sur notre promesse de couverture universelle d\'ici 2030.</p>',
                        'meta_description' => 'Déclaration du PPN sur l\'adoption de la loi sur l\'accès universel aux soins de santé.',
                    ],
                    'pt' => [
                        'title'   => 'Declaração sobre o novo projeto de lei de saúde',
                        'summary' => 'O líder do partido, Dr. Amara Diallo, acolhe favoravelmente a aprovação da Lei de Acesso Universal aos Cuidados de Saúde.',
                        'body'    => '<p>Hoje marca um passo histórico para o nosso país. Após três anos de defesa pelo nosso partido e parceiros da sociedade civil, a Assembleia Nacional aprovou a Lei de Acesso Universal aos Cuidados de Saúde com 72% dos votos a favor.</p><p>Esta legislação alargará a cobertura de cuidados de saúde primários a dois milhões de cidadãos que atualmente não têm acesso. É um avanço na nossa promessa de cobertura universal até 2030.</p>',
                        'meta_description' => 'Declaração do PPN sobre a aprovação da Lei de Acesso Universal aos Cuidados de Saúde.',
                    ],
                    'ar' => [
                        'title'   => 'بيان حول مشروع قانون الرعاية الصحية الجديد',
                        'summary' => 'زعيم الحزب الدكتور عمارة ديالو يرحب بإقرار قانون الوصول الشامل إلى الرعاية الصحية.',
                        'body'    => '<p>يمثل اليوم خطوة تاريخية لبلدنا. بعد ثلاث سنوات من المناصرة من قِبل حزبنا وشركاء المجتمع المدني، أقرّت الجمعية الوطنية قانون الوصول الشامل إلى الرعاية الصحية بنسبة 72% من الأصوات.</p><p>سيوسّع هذا التشريع تغطية الرعاية الصحية الأولية لمليوني مواطن محرومين منها حالياً. إنه خطوة نحو وعدنا بتغطية شاملة بحلول عام 2030.</p>',
                        'meta_description' => 'بيان حزب التقدم الوطني بشأن إقرار قانون الوصول الشامل إلى الرعاية الصحية.',
                    ],
                ],
            ],
            [
                'slug'        => 'youth-council-launch',
                'type'        => 'news',
                'status'      => 'published',
                'published_at' => now()->subDays(20),
                'author_name' => 'Press Office',
                'translations' => [
                    'en' => [
                        'title'   => 'NPP Launches National Youth Council',
                        'summary' => 'The party launches a new youth wing to amplify the voices of young citizens aged 16–35.',
                        'body'    => '<p>The National Progress Party today officially launched its National Youth Council, a new structure designed to give citizens between the ages of 16 and 35 a direct voice in party policy.</p><p>The Council will elect its own leadership, hold quarterly summits, and submit annual policy recommendations to the party\'s executive committee. Its first priority will be developing an Employment and Skills Strategy targeting youth unemployment.</p>',
                        'meta_description' => 'NPP launches National Youth Council to amplify young citizens\' political voice.',
                    ],
                    'fr' => [
                        'title'   => 'Le PPN lance le Conseil national de la jeunesse',
                        'summary' => 'Le parti lance une nouvelle structure jeunesse pour amplifier la voix des jeunes citoyens âgés de 16 à 35 ans.',
                        'body'    => '<p>Le Parti du Progrès National a officiellement lancé aujourd\'hui son Conseil national de la jeunesse, une nouvelle structure conçue pour donner aux citoyens âgés de 16 à 35 ans une voix directe dans la politique du parti.</p><p>Le Conseil élira sa propre direction, tiendra des sommets trimestriels et soumettra des recommandations politiques annuelles au comité exécutif du parti.</p>',
                        'meta_description' => 'Le PPN lance le Conseil national de la jeunesse pour amplifier la voix politique des jeunes.',
                    ],
                    'pt' => [
                        'title'   => 'O PPN lança o Conselho Nacional da Juventude',
                        'summary' => 'O partido lança uma nova estrutura juvenil para amplificar as vozes dos jovens cidadãos entre os 16 e os 35 anos.',
                        'body'    => '<p>O Partido do Progresso Nacional lançou hoje oficialmente o seu Conselho Nacional da Juventude, uma nova estrutura destinada a dar aos cidadãos entre os 16 e os 35 anos uma voz direta na política do partido.</p><p>O Conselho elegerá a sua própria liderança, realizará cimeiras trimestrais e apresentará recomendações políticas anuais ao comité executivo do partido.</p>',
                        'meta_description' => 'O PPN lança o Conselho Nacional da Juventude para amplificar a voz política dos jovens.',
                    ],
                    'ar' => [
                        'title'   => 'حزب التقدم الوطني يطلق المجلس الوطني للشباب',
                        'summary' => 'يطلق الحزب هيكلاً شبابياً جديداً لتعزيز أصوات الشباب من المواطنين بين 16 و35 عاماً.',
                        'body'    => '<p>أطلق حزب التقدم الوطني اليوم رسمياً مجلسه الوطني للشباب، وهو هيكل جديد مصمم لإعطاء المواطنين الذين تتراوح أعمارهم بين 16 و35 عاماً صوتاً مباشراً في سياسات الحزب.</p><p>سينتخب المجلس قيادته الخاصة ويعقد قمماً فصلية ويقدم توصيات سياسية سنوية إلى اللجنة التنفيذية للحزب.</p>',
                        'meta_description' => 'حزب التقدم الوطني يطلق المجلس الوطني للشباب لتعزيز المشاركة السياسية للشباب.',
                    ],
                ],
            ],
        ];

        foreach ($posts as $data) {
            $translations = $data['translations'];
            unset($data['translations']);

            $post = Post::create($data);

            foreach ($translations as $locale => $t) {
                PostTranslation::create(array_merge(['post_id' => $post->id, 'locale' => $locale], $t));
            }
        }
    }

    // -------------------------------------------------------------------------
    // Events
    // -------------------------------------------------------------------------

    private function seedEvents(): void
    {
        $events = [
            [
                'slug'        => 'annual-party-congress-2026',
                'starts_at'   => now()->addDays(14)->setTime(9, 0),
                'ends_at'     => now()->addDays(14)->setTime(18, 0),
                'timezone'    => 'Africa/Nairobi',
                'location'    => 'City Convention Centre, 12 Freedom Square',
                'online_url'  => null,
                'status'      => 'published',
                'published_at' => now()->subDays(7),
                'translations' => [
                    'en' => ['title' => 'Annual Party Congress 2026', 'description' => '<p>The annual congress of the National Progress Party brings together delegates from all 24 regional branches. This year\'s agenda covers the mid-term policy review, election of new executive officers, and adoption of the 2027–2030 national strategy.</p><p>Admission is for registered delegates. Media accreditation available on request.</p>', 'location_label' => 'City Convention Centre'],
                    'fr' => ['title' => 'Congrès annuel du parti 2026', 'description' => '<p>Le congrès annuel du Parti du Progrès National réunit des délégués de toutes les 24 branches régionales. L\'ordre du jour de cette année porte sur l\'examen de la politique à mi-parcours, l\'élection de nouveaux responsables exécutifs et l\'adoption de la stratégie nationale 2027–2030.</p>', 'location_label' => 'Centre des congrès municipal'],
                    'pt' => ['title' => 'Congresso Anual do Partido 2026', 'description' => '<p>O congresso anual do Partido do Progresso Nacional reúne delegados de todas as 24 secções regionais. A agenda deste ano cobre a revisão política a meio prazo, eleição de novos membros da direção e adoção da estratégia nacional 2027–2030.</p>', 'location_label' => 'Centro de Congressos Municipal'],
                    'ar' => ['title' => 'المؤتمر السنوي للحزب 2026', 'description' => '<p>يجمع المؤتمر السنوي لحزب التقدم الوطني مندوبين من جميع الفروع الإقليمية الأربعة والعشرين. تتضمن أجندة هذا العام مراجعة السياسات في منتصف الدورة وانتخاب أعضاء هيئة تنفيذية جدد وإقرار الاستراتيجية الوطنية 2027–2030.</p>', 'location_label' => 'مركز المؤتمرات البلدي'],
                ],
            ],
            [
                'slug'        => 'youth-summit-digital-economy',
                'starts_at'   => now()->addDays(28)->setTime(10, 0),
                'ends_at'     => now()->addDays(28)->setTime(17, 0),
                'timezone'    => 'Africa/Nairobi',
                'location'    => null,
                'online_url'  => 'https://live.example-npp.test/youth-summit',
                'status'      => 'published',
                'published_at' => now()->subDays(3),
                'translations' => [
                    'en' => ['title' => 'Youth Summit: Digital Economy and Jobs', 'description' => '<p>An online summit bringing together young entrepreneurs, students and policy-makers to debate how digital transformation can create employment opportunities. Register via our website for the free live stream.</p>', 'location_label' => null],
                    'fr' => ['title' => 'Sommet jeunesse : économie numérique et emplois', 'description' => '<p>Un sommet en ligne réunissant de jeunes entrepreneurs, des étudiants et des décideurs pour débattre de la façon dont la transformation numérique peut créer des opportunités d\'emploi. Inscrivez-vous sur notre site pour le flux en direct gratuit.</p>', 'location_label' => null],
                    'pt' => ['title' => 'Cimeira da Juventude: Economia Digital e Empregos', 'description' => '<p>Uma cimeira online que reúne jovens empreendedores, estudantes e decisores políticos para debater como a transformação digital pode criar oportunidades de emprego. Registe-se no nosso site para a transmissão ao vivo gratuita.</p>', 'location_label' => null],
                    'ar' => ['title' => 'قمة الشباب: الاقتصاد الرقمي وفرص العمل', 'description' => '<p>قمة إلكترونية تجمع رواد الأعمال الشباب والطلاب وصانعي القرار لمناقشة كيف يمكن للتحول الرقمي أن يخلق فرص عمل. سجّل في موقعنا للبث المباشر المجاني.</p>', 'location_label' => null],
                ],
            ],
            [
                'slug'        => 'town-hall-northern-region',
                'starts_at'   => now()->addDays(7)->setTime(14, 0),
                'ends_at'     => now()->addDays(7)->setTime(16, 30),
                'timezone'    => 'Africa/Nairobi',
                'location'    => 'Northern Region Community Hall, Mubende Road',
                'online_url'  => null,
                'status'      => 'published',
                'published_at' => now()->subDays(2),
                'translations' => [
                    'en' => ['title' => 'Town Hall Meeting — Northern Region', 'description' => '<p>Party members and all interested citizens are invited to this open town hall meeting. Local MP candidates will present their priorities and answer questions from the floor.</p>', 'location_label' => 'Northern Region Community Hall'],
                    'fr' => ['title' => 'Réunion publique — Région Nord', 'description' => '<p>Les membres du parti et tous les citoyens intéressés sont invités à cette réunion publique ouverte. Les candidats locaux au Parlement présenteront leurs priorités et répondront aux questions de la salle.</p>', 'location_label' => 'Salle communautaire de la Région Nord'],
                    'pt' => ['title' => 'Reunião Pública — Região Norte', 'description' => '<p>Os membros do partido e todos os cidadãos interessados são convidados para esta reunião pública aberta. Os candidatos locais ao Parlamento apresentarão as suas prioridades e responderão a perguntas.</p>', 'location_label' => 'Sala Comunitária da Região Norte'],
                    'ar' => ['title' => 'اجتماع المجتمع المفتوح — المنطقة الشمالية', 'description' => '<p>يُدعى أعضاء الحزب وجميع المواطنين المهتمين لحضور هذا الاجتماع المفتوح. سيقدم المرشحون المحليون للبرلمان أولوياتهم ويجيبون على أسئلة الحضور.</p>', 'location_label' => 'قاعة المجتمع في المنطقة الشمالية'],
                ],
            ],
        ];

        foreach ($events as $data) {
            $translations = $data['translations'];
            unset($data['translations']);

            $event = Event::create($data);

            foreach ($translations as $locale => $t) {
                EventTranslation::create(array_merge(['event_id' => $event->id, 'locale' => $locale], $t));
            }
        }
    }

    // -------------------------------------------------------------------------
    // People
    // -------------------------------------------------------------------------

    private function seedPeople(): void
    {
        $people = [
            [
                'name'  => 'Dr. Amara Diallo',
                'sort'  => 1,
                'group' => 'leadership',
                'translations' => [
                    'en' => ['position' => 'Party Leader', 'bio' => 'Dr. Amara Diallo has led the National Progress Party since 2016. A former professor of economics and author of two books on development policy, she is known for her pragmatic approach and her commitment to inclusive growth.'],
                    'fr' => ['position' => 'Chef du parti', 'bio' => 'Le Dr Amara Diallo dirige le Parti du Progrès National depuis 2016. Ancienne professeure d\'économie et auteure de deux ouvrages sur la politique de développement, elle est connue pour son approche pragmatique et son engagement envers une croissance inclusive.'],
                    'pt' => ['position' => 'Líder do Partido', 'bio' => 'A Dr.ª Amara Diallo lidera o Partido do Progresso Nacional desde 2016. Antiga professora de economia e autora de dois livros sobre política de desenvolvimento, é conhecida pela sua abordagem pragmática e pelo seu compromisso com o crescimento inclusivo.'],
                    'ar' => ['position' => 'رئيسة الحزب', 'bio' => 'تقود الدكتورة عمارة ديالو حزب التقدم الوطني منذ عام 2016. أستاذة اقتصاد سابقة ومؤلفة كتابَين في سياسات التنمية، تُعرف بنهجها البراغماتي والتزامها بالنمو الشامل.'],
                ],
            ],
            [
                'name'  => 'Mr. Kwame Asante',
                'sort'  => 2,
                'group' => 'leadership',
                'translations' => [
                    'en' => ['position' => 'Secretary-General', 'bio' => 'Kwame Asante joined the party in 2008 and has served as Secretary-General since 2019. He oversees party organisation, membership and communications.'],
                    'fr' => ['position' => 'Secrétaire général', 'bio' => 'Kwame Asante a rejoint le parti en 2008 et occupe le poste de Secrétaire général depuis 2019. Il supervise l\'organisation du parti, les adhésions et les communications.'],
                    'pt' => ['position' => 'Secretário-Geral', 'bio' => 'Kwame Asante aderiu ao partido em 2008 e ocupa o cargo de Secretário-Geral desde 2019. Supervisiona a organização do partido, as filiações e as comunicações.'],
                    'ar' => ['position' => 'الأمين العام', 'bio' => 'انضم كوامي أسانتي إلى الحزب عام 2008 وشغل منصب الأمين العام منذ عام 2019. يشرف على تنظيم الحزب وعضويته واتصالاته.'],
                ],
            ],
            [
                'name'  => 'Ms. Fatou Ndiaye',
                'sort'  => 3,
                'group' => 'executive',
                'translations' => [
                    'en' => ['position' => 'Head of Policy', 'bio' => 'Fatou Ndiaye leads the party\'s policy unit. She previously worked as a senior adviser at the Ministry of Finance and brings deep expertise in fiscal policy and social protection.'],
                    'fr' => ['position' => 'Responsable des politiques', 'bio' => 'Fatou Ndiaye dirige l\'unité de politique du parti. Elle a précédemment travaillé comme conseillère principale au ministère des Finances et apporte une expertise approfondie en matière de politique fiscale et de protection sociale.'],
                    'pt' => ['position' => 'Chefe de Políticas', 'bio' => 'Fatou Ndiaye lidera a unidade de políticas do partido. Trabalhou anteriormente como assessora sénior no Ministério das Finanças, trazendo uma vasta experiência em política fiscal e proteção social.'],
                    'ar' => ['position' => 'رئيسة وحدة السياسات', 'bio' => 'تقود فاطو ندياي وحدة السياسات في الحزب. عملت سابقاً مستشارة أولى في وزارة المالية وتتمتع بخبرة عميقة في السياسة المالية والحماية الاجتماعية.'],
                ],
            ],
            [
                'name'  => 'Dr. Ibrahim Mensah',
                'sort'  => 4,
                'group' => 'parliament',
                'translations' => [
                    'en' => ['position' => 'Member of Parliament, Western District', 'bio' => 'Dr. Ibrahim Mensah has represented the Western District since 2018. He chairs the Parliamentary Committee on Health and is a lead sponsor of the Universal Healthcare Access Act.'],
                    'fr' => ['position' => 'Membre du Parlement, district Ouest', 'bio' => 'Le Dr Ibrahim Mensah représente le district Ouest depuis 2018. Il préside la Commission parlementaire de la santé et est l\'un des principaux parrains de la loi sur l\'accès universel aux soins de santé.'],
                    'pt' => ['position' => 'Deputado, Distrito Oeste', 'bio' => 'O Dr. Ibrahim Mensah representa o Distrito Oeste desde 2018. Preside à Comissão Parlamentar de Saúde e é um dos principais promotores da Lei de Acesso Universal aos Cuidados de Saúde.'],
                    'ar' => ['position' => 'عضو البرلمان، الدائرة الغربية', 'bio' => 'يمثل الدكتور إبراهيم منساح الدائرة الغربية منذ عام 2018. يترأس لجنة الصحة البرلمانية وهو أحد الرعاة الرئيسيين لقانون الوصول الشامل إلى الرعاية الصحية.'],
                ],
            ],
        ];

        foreach ($people as $data) {
            $translations = $data['translations'];
            unset($data['translations']);

            $person = Person::create($data);

            foreach ($translations as $locale => $t) {
                PersonTranslation::create(array_merge(['person_id' => $person->id, 'locale' => $locale], $t));
            }
        }
    }

    // -------------------------------------------------------------------------
    // Documents (no real files — demo placeholder with text/plain stub)
    // -------------------------------------------------------------------------

    private function seedDocuments(): void
    {
        $docs = [
            [
                'file'         => 'demo/manifesto-2026.pdf',
                'file_size'    => 524288,
                'mime_type'    => 'application/pdf',
                'category'     => 'manifesto',
                'published_at' => now()->subDays(60),
                'translations' => [
                    'en' => ['title' => 'Electoral Manifesto 2026', 'description' => 'The full manifesto of the National Progress Party for the 2026 general elections.'],
                    'fr' => ['title' => 'Manifeste électoral 2026', 'description' => 'Le manifeste complet du Parti du Progrès National pour les élections générales de 2026.'],
                    'pt' => ['title' => 'Manifesto Eleitoral 2026', 'description' => 'O manifesto completo do Partido do Progresso Nacional para as eleições gerais de 2026.'],
                    'ar' => ['title' => 'البيان الانتخابي 2026', 'description' => 'البيان الانتخابي الكامل لحزب التقدم الوطني للانتخابات العامة 2026.'],
                ],
            ],
            [
                'file'         => 'demo/party-constitution.pdf',
                'file_size'    => 262144,
                'mime_type'    => 'application/pdf',
                'category'     => 'constitution',
                'published_at' => now()->subDays(90),
                'translations' => [
                    'en' => ['title' => 'Party Constitution', 'description' => 'The founding constitution and by-laws of the National Progress Party, adopted at the founding congress.'],
                    'fr' => ['title' => 'Constitution du parti', 'description' => 'La constitution fondatrice et les statuts du Parti du Progrès National, adoptés lors du congrès fondateur.'],
                    'pt' => ['title' => 'Constituição do Partido', 'description' => 'A constituição fundadora e os estatutos do Partido do Progresso Nacional, adoptados no congresso fundador.'],
                    'ar' => ['title' => 'دستور الحزب', 'description' => 'الدستور التأسيسي ولوائح حزب التقدم الوطني المعتمدة في المؤتمر التأسيسي.'],
                ],
            ],
            [
                'file'         => 'demo/annual-report-2025.pdf',
                'file_size'    => 786432,
                'mime_type'    => 'application/pdf',
                'category'     => 'report',
                'published_at' => now()->subDays(45),
                'translations' => [
                    'en' => ['title' => 'Annual Report 2025', 'description' => 'Activities, finances and outcomes of the National Progress Party for the year 2025.'],
                    'fr' => ['title' => 'Rapport annuel 2025', 'description' => 'Activités, finances et résultats du Parti du Progrès National pour l\'année 2025.'],
                    'pt' => ['title' => 'Relatório Anual 2025', 'description' => 'Atividades, finanças e resultados do Partido do Progresso Nacional para o ano de 2025.'],
                    'ar' => ['title' => 'التقرير السنوي 2025', 'description' => 'أنشطة وشؤون مالية ونتائج حزب التقدم الوطني للعام 2025.'],
                ],
            ],
        ];

        foreach ($docs as $data) {
            $translations = $data['translations'];
            unset($data['translations']);

            $doc = Document::create($data);

            foreach ($translations as $locale => $t) {
                DocumentTranslation::create(array_merge(['document_id' => $doc->id, 'locale' => $locale], $t));
            }
        }
    }

    // -------------------------------------------------------------------------
    // Galleries (no real images — media rows only, no physical files)
    // -------------------------------------------------------------------------

    private function seedGalleries(): void
    {
        $galleries = [
            [
                'slug'   => 'congress-2025',
                'status' => 'published',
                'translations' => [
                    'en' => ['title' => 'Annual Congress 2025'],
                    'fr' => ['title' => 'Congrès annuel 2025'],
                    'pt' => ['title' => 'Congresso Anual 2025'],
                    'ar' => ['title' => 'المؤتمر السنوي 2025'],
                ],
                'images' => [
                    ['caption_en' => 'Opening ceremony', 'caption_fr' => 'Cérémonie d\'ouverture', 'caption_pt' => 'Cerimónia de abertura', 'caption_ar' => 'حفل الافتتاح'],
                    ['caption_en' => 'Keynote address by Dr. Diallo', 'caption_fr' => 'Discours inaugural du Dr Diallo', 'caption_pt' => 'Discurso principal da Dr.ª Diallo', 'caption_ar' => 'الكلمة الرئيسية للدكتورة ديالو'],
                    ['caption_en' => 'Delegates voting on the 2026–2029 strategy', 'caption_fr' => 'Les délégués votent sur la stratégie 2026–2029', 'caption_pt' => 'Delegados a votar na estratégia 2026–2029', 'caption_ar' => 'المندوبون يصوتون على استراتيجية 2026–2029'],
                ],
            ],
            [
                'slug'   => 'campaign-trail-2026',
                'status' => 'published',
                'translations' => [
                    'en' => ['title' => 'Campaign Trail 2026'],
                    'fr' => ['title' => 'Campagne électorale 2026'],
                    'pt' => ['title' => 'Campanha Eleitoral 2026'],
                    'ar' => ['title' => 'جولة الحملة الانتخابية 2026'],
                ],
                'images' => [
                    ['caption_en' => 'Rally in the Southern Province', 'caption_fr' => 'Rassemblement dans la province du Sud', 'caption_pt' => 'Comício na Província Sul', 'caption_ar' => 'مهرجان في المقاطعة الجنوبية'],
                    ['caption_en' => 'Meeting with farmers\' cooperative', 'caption_fr' => 'Rencontre avec la coopérative agricole', 'caption_pt' => 'Reunião com cooperativa de agricultores', 'caption_ar' => 'لقاء مع تعاونية المزارعين'],
                ],
            ],
        ];

        foreach ($galleries as $data) {
            $translations = $data['translations'];
            $images       = $data['images'];
            unset($data['translations'], $data['images']);

            $gallery = Gallery::create($data);

            foreach ($translations as $locale => $t) {
                GalleryTranslation::create(array_merge(['gallery_id' => $gallery->id, 'locale' => $locale], $t));
            }

            foreach ($images as $i => $img) {
                // Placeholder media record — no physical file
                $media = Media::create([
                    'disk'              => 'public',
                    'directory'         => 'demo',
                    'filename'          => 'placeholder-' . ($i + 1) . '.jpg',
                    'original_filename' => 'placeholder-' . ($i + 1) . '.jpg',
                    'mime_type'         => 'image/jpeg',
                    'extension'         => 'jpg',
                    'size'              => 51200,
                    'width'             => 1200,
                    'height'            => 800,
                ]);

                $gi = GalleryImage::create([
                    'gallery_id' => $gallery->id,
                    'media_id'   => $media->id,
                    'sort'       => $i,
                ]);

                foreach (self::LOCALES as $locale) {
                    $captionKey = "caption_{$locale}";
                    GalleryImageTranslation::create([
                        'gallery_image_id' => $gi->id,
                        'locale'           => $locale,
                        'caption'          => $img[$captionKey] ?? null,
                    ]);
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Menus
    // -------------------------------------------------------------------------

    private function seedMenus(): void
    {
        $headerItems = [
            'en' => [
                ['label' => 'Home',        'url' => '/en/',          'target' => '_self', 'children' => []],
                ['label' => 'About',       'url' => '/en/about',     'target' => '_self', 'children' => []],
                ['label' => 'Programme',   'url' => '/en/programme', 'target' => '_self', 'children' => []],
                ['label' => 'News',        'url' => '/en/news',      'target' => '_self', 'children' => []],
                ['label' => 'Events',      'url' => '/en/events',    'target' => '_self', 'children' => []],
                ['label' => 'Leadership',  'url' => '/en/leadership','target' => '_self', 'children' => []],
                ['label' => 'Contact',     'url' => '/en/contact',   'target' => '_self', 'children' => []],
            ],
            'fr' => [
                ['label' => 'Accueil',     'url' => '/fr/',          'target' => '_self', 'children' => []],
                ['label' => 'À propos',    'url' => '/fr/about',     'target' => '_self', 'children' => []],
                ['label' => 'Programme',   'url' => '/fr/programme', 'target' => '_self', 'children' => []],
                ['label' => 'Actualités',  'url' => '/fr/news',      'target' => '_self', 'children' => []],
                ['label' => 'Événements',  'url' => '/fr/events',    'target' => '_self', 'children' => []],
                ['label' => 'Direction',   'url' => '/fr/leadership','target' => '_self', 'children' => []],
                ['label' => 'Contact',     'url' => '/fr/contact',   'target' => '_self', 'children' => []],
            ],
            'pt' => [
                ['label' => 'Início',      'url' => '/pt/',          'target' => '_self', 'children' => []],
                ['label' => 'Sobre nós',   'url' => '/pt/about',     'target' => '_self', 'children' => []],
                ['label' => 'Programa',    'url' => '/pt/programme', 'target' => '_self', 'children' => []],
                ['label' => 'Notícias',    'url' => '/pt/news',      'target' => '_self', 'children' => []],
                ['label' => 'Eventos',     'url' => '/pt/events',    'target' => '_self', 'children' => []],
                ['label' => 'Liderança',   'url' => '/pt/leadership','target' => '_self', 'children' => []],
                ['label' => 'Contacto',    'url' => '/pt/contact',   'target' => '_self', 'children' => []],
            ],
            'ar' => [
                ['label' => 'الرئيسية',    'url' => '/ar/',          'target' => '_self', 'children' => []],
                ['label' => 'من نحن',      'url' => '/ar/about',     'target' => '_self', 'children' => []],
                ['label' => 'برنامجنا',    'url' => '/ar/programme', 'target' => '_self', 'children' => []],
                ['label' => 'الأخبار',     'url' => '/ar/news',      'target' => '_self', 'children' => []],
                ['label' => 'الفعاليات',   'url' => '/ar/events',    'target' => '_self', 'children' => []],
                ['label' => 'القيادة',     'url' => '/ar/leadership','target' => '_self', 'children' => []],
                ['label' => 'اتصل بنا',    'url' => '/ar/contact',   'target' => '_self', 'children' => []],
            ],
        ];

        $footerItems = [
            'en' => [
                ['label' => 'About',       'url' => '/en/about',     'target' => '_self', 'children' => []],
                ['label' => 'Programme',   'url' => '/en/programme', 'target' => '_self', 'children' => []],
                ['label' => 'Documents',   'url' => '/en/documents', 'target' => '_self', 'children' => []],
                ['label' => 'Join Us',     'url' => '/en/join',      'target' => '_self', 'children' => []],
                ['label' => 'Contact',     'url' => '/en/contact',   'target' => '_self', 'children' => []],
            ],
            'fr' => [
                ['label' => 'À propos',    'url' => '/fr/about',     'target' => '_self', 'children' => []],
                ['label' => 'Programme',   'url' => '/fr/programme', 'target' => '_self', 'children' => []],
                ['label' => 'Documents',   'url' => '/fr/documents', 'target' => '_self', 'children' => []],
                ['label' => 'Nous rejoindre', 'url' => '/fr/join',   'target' => '_self', 'children' => []],
                ['label' => 'Contact',     'url' => '/fr/contact',   'target' => '_self', 'children' => []],
            ],
            'pt' => [
                ['label' => 'Sobre nós',   'url' => '/pt/about',     'target' => '_self', 'children' => []],
                ['label' => 'Programa',    'url' => '/pt/programme', 'target' => '_self', 'children' => []],
                ['label' => 'Documentos',  'url' => '/pt/documents', 'target' => '_self', 'children' => []],
                ['label' => 'Junte-se a nós', 'url' => '/pt/join',  'target' => '_self', 'children' => []],
                ['label' => 'Contacto',    'url' => '/pt/contact',   'target' => '_self', 'children' => []],
            ],
            'ar' => [
                ['label' => 'من نحن',      'url' => '/ar/about',     'target' => '_self', 'children' => []],
                ['label' => 'برنامجنا',    'url' => '/ar/programme', 'target' => '_self', 'children' => []],
                ['label' => 'الوثائق',     'url' => '/ar/documents', 'target' => '_self', 'children' => []],
                ['label' => 'انضم إلينا',  'url' => '/ar/join',      'target' => '_self', 'children' => []],
                ['label' => 'اتصل بنا',    'url' => '/ar/contact',   'target' => '_self', 'children' => []],
            ],
        ];

        foreach (['header' => $headerItems, 'footer' => $footerItems] as $location => $allItems) {
            $menu = Menu::firstOrCreate(['location' => $location]);

            foreach (self::LOCALES as $locale) {
                MenuTranslation::updateOrCreate(
                    ['menu_id' => $menu->id, 'locale' => $locale],
                    ['items' => json_encode($allItems[$locale])]
                );
            }
        }
    }
}
