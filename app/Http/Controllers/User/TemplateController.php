<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\LicenseController;
use App\Services\Statistics\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Orhanerday\OpenAi\OpenAi;
use App\Models\FavoriteTemplate;
use App\Models\SubscriptionPlan;
use App\Models\Template;
use App\Models\Content;
use App\Models\Workbook;
use App\Models\Language;
use App\Models\User;


class TemplateController extends Controller
{
    private $api;
    private $user;

    public function __construct()
    {
        $this->api = new LicenseController();
        $this->user = new UserService();
    }

    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $favorite_templates = Template::select('templates.*', 'favorite_templates.*')->where('favorite_templates.user_id', auth()->user()->id)->join('favorite_templates', 'favorite_templates.template_code', '=', 'templates.template_code')->get();  
        $user_templates = FavoriteTemplate::where('user_id', auth()->user()->id)->pluck('template_code');     
        $other_templates = Template::whereNotIn('template_code', $user_templates)->orderBy('group', 'asc')->get();       

        return view('user.templates.index', compact('favorite_templates', 'other_templates'));
    }


     /**
	*
	* Process Davinci
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function process(Request $request) 
    {
        if ($request->ajax()) {

            $open_ai = new OpenAi(config('services.openai.key'));
            $prompt = '';
            $model = '';
            $text = '';
            $max_tokens = '';
            $counter = 1;

            $identify = $this->api->verify_license();
            if($identify['status']!=true){return false;}

            # Check if user has access to the template
            $template = Template::where('template_code', $request->template)->first();
            if (auth()->user()->group == 'user') {
                if (config('settings.templates_access_user') != 'all') {
                    if ($template->professional) {
                        if (config('settings.templates_access_user') != 'professional') {
                            $data['status'] = 'error';
                            $data['message'] = __('Professional templates not available for your account, subscribe to get access');
                            return $data;
                        }
                    }
                }
            } elseif (auth()->user()->group == 'admin') {
                if (config('settings.templates_access_admin') != 'all') {
                    if ($template->professional) {
                        if (config('settings.templates_access_admin') != 'professional') {
                            $data['status'] = 'error';
                            $data['message'] = __('Professional templates not available for your account, subscribe to get access');
                            return $data;
                        }
                    }
                }
            } else {
                $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                if ($plan->templates != 'all') {
                    if ($template->professional) {
                        if ($plan->templates != 'professional') {
                            $data['status'] = 'error';
                            $data['message'] = __('Your current subscription does not cover professional templates');
                            return $data;
                        }
                    }
                }
            }
            
            # Generate proper prompt in respective language
            switch ($request->template) {
                case 'KPAQQ':                    
                    request()->validate(['title' => 'required|string', 'keywords' => 'required|string']);
                    $prompt = $this->createArticleGeneratorPrompt(strip_tags($request->title), strip_tags($request->keywords), $request->language, $request->tone);
                    break;
                case 'JXRZB':                    
                    request()->validate(['title' => 'required|string']);
                    $prompt = $this->createParagraphGeneratorPrompt(strip_tags($request->title), strip_tags($request->keywords), $request->language, $request->tone);
                    break;
                case 'OPYAB':                    
                    request()->validate(['title' => 'required|string', 'description' => 'required|string']);
                    $prompt = $this->createProsAndConsPrompt(strip_tags($request->title), strip_tags($request->description), $request->language, $request->tone);
                    break;
                case 'VFWSQ':                    
                    request()->validate(['title' => 'required|string', 'description' => 'required|string']);
                    $prompt = $this->createTalkingPointsPrompt(strip_tags($request->title), strip_tags($request->description), $request->language, $request->tone);
                    break;
                case 'OMMEI':                    
                    request()->validate(['description' => 'required|string']);
                    $prompt = $this->createSummarizeTextPrompt(strip_tags($request->description), $request->language, $request->tone);
                    break;
                case 'HXLNA':                    
                    request()->validate(['title' => 'required|string', 'audience' => 'required|string', 'description' => 'required|string']);
                    $prompt = $this->createProductDescriptionPrompt(strip_tags($request->title), strip_tags($request->audience), strip_tags($request->description), $request->language, $request->tone);
                    break;
                case 'DJSVM':                    
                    request()->validate(['keywords' => 'required|string', 'description' => 'required|string']);
                    $prompt = $this->createStartupNameGeneratorPrompt(strip_tags($request->keywords), strip_tags($request->description), $request->language);
                    break;
                case 'IXKBE':                    
                    request()->validate(['keywords' => 'required|string', 'description' => 'required|string']);
                    $prompt = $this->createProductNameGeneratorPrompt(strip_tags($request->keywords), strip_tags($request->description), $request->language);
                    break;
                case 'JCDIK':                    
                    request()->validate(['title' => 'required|string', 'keywords' => 'required|string', 'description' => 'required|string']);
                    $prompt = $this->createMetaDescriptionPrompt(strip_tags($request->title), strip_tags($request->keywords), strip_tags($request->description), $request->language);
                    break;
                case 'SZAUF':                    
                    request()->validate(['title' => 'required|string', 'description' => 'required|string']);
                    $prompt = $this->createFAQsPrompt(strip_tags($request->title), strip_tags($request->description), $request->language, $request->tone);
                    break;
                case 'BFENK':                    
                    request()->validate(['title' => 'required|string', 'description' => 'required|string', 'question' => 'required|string']);
                    $prompt = $this->createFAQAnswersPrompt(strip_tags($request->title), strip_tags($request->question), strip_tags($request->description), $request->language, $request->tone);
                    break;
                case 'XLGPP':                    
                    request()->validate(['title' => 'required|string', 'description' => 'required|string']);
                    $prompt = $this->createTestimonialsPrompt(strip_tags($request->title), strip_tags($request->description), $request->language, $request->tone);
                    break;
                case 'WGKYP':                    
                    request()->validate(['description' => 'required|string']);
                    $prompt = $this->createBlogTitlesPrompt(strip_tags($request->description), $request->language);
                    break;
                case 'EEKZF':                    
                    request()->validate(['title' => 'required|string', 'subheadings' => 'required|string']);
                    $prompt = $this->createBlogSectionPrompt(strip_tags($request->title), strip_tags($request->subheadings), $request->language, $request->tone);
                    break;
                case 'KDGOX':                    
                    request()->validate(['title' => 'required|string']);
                    $prompt = $this->createBlogIdeasPrompt(strip_tags($request->title), $request->language, $request->tone);
                    break;
                case 'TZTYR':                    
                    request()->validate(['title' => 'required|string', 'description' => 'required|string']);
                    $prompt = $this->createBlogIntrosPrompt(strip_tags($request->title), strip_tags($request->description), $request->language, $request->tone);
                    break;
                case 'ZGUKM':                    
                    request()->validate(['title' => 'required|string', 'description' => 'required|string']);
                    $prompt = $this->createBlogConclusionPrompt(strip_tags($request->title), strip_tags($request->description), $request->language, $request->tone);
                    break;
                case 'WCZGL':                    
                    request()->validate(['title' => 'required|string']);
                    $prompt = $this->createContentRewriterPrompt(strip_tags($request->title), $request->language, $request->tone);
                    break;
                case 'CTMNI':                    
                    request()->validate(['title' => 'required|string']);
                    $prompt = $this->createFacebookAdsPrompt(strip_tags($request->title), strip_tags($request->audience), strip_tags($request->description), $request->language, $request->tone);
                    break;
                default:
                    # code...
                    break;
            }

            # Apply proper model based on role and subsciption
            if (auth()->user()->group == 'user') {
                $model = config('settings.default_model_user');
            } elseif (auth()->user()->group == 'admin') {
                $model = config('settings.default_model_admin');
            } else {
                $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                $model = $plan->model;
            }

            # Verify if user has enough credits
            if ((auth()->user()->available_words + auth()->user()->available_words_prepaid) < $request->words) {
                $data['status'] = 'error';
                $data['message'] = __('Not enough word balance to proceed, subscribe or top up your word balance and try again');
                return $data;
            }

            # Verify word limit
            if (auth()->user()->group == 'user') {
                $max_tokens = (config('settings.max_results_limit_user') < (int)$request->words) ? config('settings.max_results_limit_user') : (int)$request->words;
            } elseif (auth()->user()->group == 'admin') {
                $max_tokens = (config('settings.max_results_limit_admin') < (int)$request->words) ? config('settings.max_results_limit_user') : (int)$request->words;
            } else {
                $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                $max_tokens = ($plan->max_tokens < (int)$request->words) ? $plan->max_tokens : (int)$request->words;
            }

            $control = $this->user->verify_license();
            if($control['status']!=true){return false;}

            $max_results = (int)$request->max_results;
            $temperature = (float)$request->creativity;

            $complete = $open_ai->completion([
                'model' => $model,
                'prompt' => $prompt,
                'temperature' => $temperature,
                'max_tokens' => $max_tokens,
                'n' => $max_results,
            ]);

            $response = json_decode($complete , true);             

            if (isset($response['choices'])) {
                if (count($response['choices']) > 1) {
                    foreach ($response['choices'] as $value) {
                        $text .= $counter . '. ' . trim($value['text']) . "\r\n\r\n\r\n";
                        $counter++;
                    }
                } else {
                    $text = $response['choices'][0]['text'];
                }
                
                $tokens = $response['usage']['completion_tokens'];
                $plan_type = (auth()->user()->plan_id) ? 'paid' : 'free';
                
                # Update credit balance
                $this->updateBalance($tokens);
                $flag = Language::where('language_code', $request->language)->first();

                $content = new Content();
                $content->user_id = auth()->user()->id;
                $content->input_text = $request->title;
                $content->result_text = $text;
                $content->model = $model;
                $content->language = $request->language;
                $content->language_name = $flag->language;
                $content->language_flag = $flag->language_flag;
                $content->template_code = $request->template;
                $content->template_name = $template->name;
                $content->tokens = $tokens;
                $content->plan_type = $plan_type;
                $content->workbook = $request->project;
                $content->title = $request->document;
                $content->save();
    
                $data['text'] = trim($text);
                $data['status'] = 'success';
                $data['old'] = auth()->user()->available_words + auth()->user()->available_words_prepaid;
                $data['current'] = auth()->user()->available_words + auth()->user()->available_words_prepaid - $tokens;
                $data['id'] = $content->id;
                return $data; 

            } else {
                $data['status'] = 'error';
                $data['message'] = __('Text was not generated, please try again');
                return $data;
            }
           

        }
	}


    /**
	*
	* Update user word balance
	* @param - total words generated
	* @return - confirmation
	*
	*/
    public function updateBalance($words) {

        $user = User::find(Auth::user()->id);

        $control = $this->user->verify_license();
        if($control['status']!=true){return false;}

        if (Auth::user()->available_words > $words) {

            $total_words = Auth::user()->available_words - $words;
            $user->available_words = $total_words;

        } elseif (Auth::user()->available_words_prepaid > $words) {

            $total_words_prepaid = Auth::user()->available_words_prepaid - $words;
            $user->available_words_prepaid = $total_words_prepaid;

        } elseif ((Auth::user()->available_words + Auth::user()->available_words_prepaid) == $words) {

            $user->available_words = 0;
            $user->available_words_prepaid = 0;

        } else {

            $remaining = $words - Auth::user()->available_words;
            $user->available_words = 0;

            $user->available_words_prepaid = Auth::user()->available_words_prepaid - $remaining;

        }

        $user->update();

        return true;
    }


    /**
	*
	* Save changes
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function save(Request $request) 
    {
        if ($request->ajax()) {

            $verify = $this->api->verify_license();
            if($verify['status']!=true){return false;}

            $document = Content::where('id', request('id'))->first(); 

            if ($document->user_id == Auth::user()->id){

                $document->result_text = $request->text;
                $document->title = $request->title;
                $document->workbook = $request->workbook;
                $document->save();

                $data['status'] = 'success';
                return $data;  
    
            } else{

                $data['status'] = 'error';
                return $data;
            }  
        }
	}


    /**
	*
	* Set favorite status
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function favorite(Request $request) 
    {
        if ($request->ajax()) {

            $control = $this->user->verify_license();
            if($control['status']!=true){return false;}

            $template = Template::where('template_code', request('id'))->first(); 

            $favorite = FavoriteTemplate::where('template_code', $template->template_code)->where('user_id', auth()->user()->id)->first();

            if ($favorite) {

                $favorite->delete();

                $data['status'] = 'success';
                $data['set'] = true;
                return $data;  
    
            } else{

                $new_favorite = new FavoriteTemplate();
                $new_favorite->user_id = auth()->user()->id;
                $new_favorite->template_code = $template->template_code;
                $new_favorite->save();

                $data['status'] = 'success';
                $data['set'] = false;
                return $data; 
            }  
        }
	}


    /**
     * Initial settings 
     *
     * @param  $request
     * @return \Illuminate\Http\Response
     */
    public function settings()
    {
        if (!is_null(auth()->user()->plan_id)) {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            $limit = $plan->max_tokens;    
        } elseif (auth()->user()->group == 'admin') {
            $limit = config('settings.max_results_limit_admin');    
        } else {
            $limit = config('settings.max_results_limit_user'); 
        }

        return $limit;
    }


    /**
     * Translate tone
     *
     * @param  $request
     * @return \Illuminate\Http\Response
     */
    public function translateTone($tone, $language)
    {
        $verify = $this->user->verify_license();
        if($verify['status']!=true){return false;}

        switch ($language) {
            case 'ar-AE':
                switch ($tone) {
                    case 'funny': return 'مضحك'; break; 
                    case 'casual': return 'غير رسمي'; break; 
                    case 'excited': return 'متحمس'; break; 
                    case 'professional': return 'احترافي'; break; 
                    case 'witty': return 'بارع'; break; 
                    case 'sarcastic': return 'ساخر'; break; 
                    case 'feminine': return 'المؤنث'; break; 
                    case 'masculine': return 'مذكر'; break; 
                    case 'bold': return 'عريض'; break; 
                    case 'dramatic': return 'دراماتيكي'; break; 
                    case 'gumpy': return 'غامبي'; break; 
                    case 'secretive': return 'كتوم'; break; 
                }
                break;
            case 'cmn-CN':
                switch ($tone) {
                    case 'funny': return '有趣的'; break; 
                    case 'casual': return '随意的'; break; 
                    case 'excited': return '兴奋的'; break; 
                    case 'professional': return '专业的'; break; 
                    case 'witty': return '机智'; break; 
                    case 'sarcastic': return '讽刺的'; break; 
                    case 'feminine': return '女性化的'; break; 
                    case 'masculine': return '男性'; break; 
                    case 'bold': return '大胆的'; break; 
                    case 'dramatic': return '戏剧性'; break; 
                    case 'gumpy': return '脾气暴躁的'; break; 
                    case 'secretive': return '神秘的'; break; 
                }
                    break;
            case 'hr-HR':
                switch ($tone) {
                    case 'funny': return 'smiješno'; break; 
                    case 'casual': return 'ležeran'; break; 
                    case 'excited': return 'uzbuđen'; break; 
                    case 'professional': return 'profesionalni'; break; 
                    case 'witty': return 'duhovit'; break; 
                    case 'sarcastic': return 'sarkastičan'; break; 
                    case 'feminine': return 'ženski'; break; 
                    case 'masculine': return 'muški'; break; 
                    case 'bold': return 'podebljano'; break; 
                    case 'dramatic': return 'dramatičan'; break; 
                    case 'gumpy': return 'gumiran'; break; 
                    case 'secretive': return 'tajnovit'; break; 
                }
                break;
            case 'cs-CZ':
                switch ($tone) {
                    case 'funny': return 'legrační'; break; 
                    case 'casual': return 'neformální'; break; 
                    case 'excited': return 'vzrušený'; break; 
                    case 'professional': return 'profesionální'; break; 
                    case 'witty': return 'vtipný'; break; 
                    case 'sarcastic': return 'sarkastický'; break; 
                    case 'feminine': return 'ženský'; break; 
                    case 'masculine': return 'mužský'; break; 
                    case 'bold': return 'tučně'; break; 
                    case 'dramatic': return 'dramatický'; break; 
                    case 'gumpy': return 'gumový'; break; 
                    case 'secretive': return 'tajnůstkářský'; break; 
                }
                break;
            case 'da-DK':
                switch ($tone) {
                    case 'funny': return 'sjov'; break; 
                    case 'casual': return 'afslappet'; break; 
                    case 'excited': return 'begejstret'; break; 
                    case 'professional': return 'professionel'; break; 
                    case 'witty': return 'vittig'; break; 
                    case 'sarcastic': return 'sarkastisk'; break; 
                    case 'feminine': return 'feminin'; break; 
                    case 'masculine': return 'maskulin'; break; 
                    case 'bold': return 'fremhævet'; break; 
                    case 'dramatic': return 'dramatisk'; break; 
                    case 'gumpy': return 'klumpet'; break; 
                    case 'secretive': return 'hemmelighedsfuld'; break; 
                }
                break;
            case 'nl-BE':
                switch ($tone) {
                    case 'funny': return 'grappig'; break; 
                    case 'casual': return 'casual'; break; 
                    case 'excited': return 'opgewonden'; break; 
                    case 'professional': return 'professioneel'; break; 
                    case 'witty': return 'geestig'; break; 
                    case 'sarcastic': return 'sarcastisch'; break; 
                    case 'feminine': return 'vrouwelijk'; break; 
                    case 'masculine': return 'mannelijk'; break; 
                    case 'bold': return 'vetgedrukt'; break; 
                    case 'dramatic': return 'dramatisch'; break; 
                    case 'gumpy': return 'gom'; break; 
                    case 'secretive': return 'geheimzinnig'; break; 
                }
                break;
            case 'et-EE':
                switch ($tone) {
                    case 'funny': return 'naljakas'; break; 
                    case 'casual': return 'juhuslik'; break; 
                    case 'excited': return 'erutatud'; break; 
                    case 'professional': return 'professionaalne'; break; 
                    case 'witty': return 'vaimukas'; break; 
                    case 'sarcastic': return 'sarkastiline'; break; 
                    case 'feminine': return 'naiselik'; break; 
                    case 'masculine': return 'mehelik'; break; 
                    case 'bold': return 'julge'; break; 
                    case 'dramatic': return 'dramaatiline'; break; 
                    case 'gumpy': return 'närune'; break; 
                    case 'secretive': return 'salajane'; break; 
                }
                break;
            case 'fil-PH':
                switch ($tone) {
                    case 'funny': return 'nakakatawa'; break; 
                    case 'casual': return 'kaswal'; break; 
                    case 'excited': return 'nasasabik'; break; 
                    case 'professional': return 'propesyonal'; break; 
                    case 'witty': return 'matalino'; break; 
                    case 'sarcastic': return 'sarcastic'; break; 
                    case 'feminine': return 'pambabae'; break; 
                    case 'masculine': return 'panlalaki'; break; 
                    case 'bold': return 'matapang'; break; 
                    case 'dramatic': return 'madrama'; break; 
                    case 'gumpy': return 'mabukol'; break; 
                    case 'secretive': return 'palihim'; break; 
                }
                break;
            case 'fi-FI':
                switch ($tone) {
                    case 'funny': return 'hauska'; break; 
                    case 'casual': return 'rento'; break; 
                    case 'excited': return 'innoissaan'; break; 
                    case 'professional': return 'ammattilainen'; break; 
                    case 'witty': return 'nokkela'; break; 
                    case 'sarcastic': return 'sarkastinen'; break; 
                    case 'feminine': return 'naisellinen'; break; 
                    case 'masculine': return 'maskuliini-'; break; 
                    case 'bold': return 'lihavoitu'; break; 
                    case 'dramatic': return 'dramaattinen'; break; 
                    case 'gumpy': return 'kuminen'; break; 
                    case 'secretive': return 'salaperäinen'; break; 
                }
                break;
            case 'fr-FR':
                switch ($tone) {
                    case 'funny': return 'drôle'; break; 
                    case 'casual': return 'occasionnel'; break; 
                    case 'excited': return 'excité'; break; 
                    case 'professional': return 'professionnel'; break; 
                    case 'witty': return 'spirituel'; break; 
                    case 'sarcastic': return 'sarcastique'; break; 
                    case 'feminine': return 'féminin'; break; 
                    case 'masculine': return 'masculin'; break; 
                    case 'bold': return 'gras'; break; 
                    case 'dramatic': return 'spectaculaire'; break; 
                    case 'gumpy': return 'gommeux'; break; 
                    case 'secretive': return 'secret'; break; 
                }
                break;
            case 'el-GR':
                switch ($tone) {
                    case 'funny': return 'lustig'; break; 
                    case 'casual': return 'lässig'; break; 
                    case 'excited': return 'aufgeregt'; break; 
                    case 'professional': return 'Fachmann'; break; 
                    case 'witty': return 'witzig'; break; 
                    case 'sarcastic': return 'sarkastisch'; break; 
                    case 'feminine': return 'feminin'; break; 
                    case 'masculine': return 'männlich'; break; 
                    case 'bold': return 'deutlich'; break; 
                    case 'dramatic': return 'dramatisch'; break; 
                    case 'gumpy': return 'gummiartig'; break; 
                    case 'secretive': return 'geheimnisvoll'; break; 
                }
                break;
            case 'el-GR':
                switch ($tone) {
                    case 'funny': return 'αστείος'; break; 
                    case 'casual': return 'ανέμελος'; break; 
                    case 'excited': return 'ενθουσιασμένος'; break; 
                    case 'professional': return 'επαγγελματίας'; break; 
                    case 'witty': return 'πνευματώδης'; break; 
                    case 'sarcastic': return 'σαρκαστικός'; break; 
                    case 'feminine': return 'θηλυκός'; break; 
                    case 'masculine': return 'αρρενωπός'; break; 
                    case 'bold': return 'τολμηρός'; break; 
                    case 'dramatic': return 'δραματικός'; break; 
                    case 'gumpy': return 'τσακισμένος'; break; 
                    case 'secretive': return 'εκκριτικός'; break; 
                }
                break;
            case 'he-IL':
                switch ($tone) {
                    case 'funny': return 'מצחיק'; break; 
                    case 'casual': return 'אַגָבִי'; break; 
                    case 'excited': return 'נִרגָשׁ'; break; 
                    case 'professional': return 'מקצועי'; break; 
                    case 'witty': return 'שָׁנוּן'; break; 
                    case 'sarcastic': return 'עוקצני'; break; 
                    case 'feminine': return 'נָשִׁי'; break; 
                    case 'masculine': return 'גַברִי'; break; 
                    case 'bold': return 'נוֹעָז'; break; 
                    case 'dramatic': return 'דְרָמָטִי'; break; 
                    case 'gumpy': return 'גומי'; break; 
                    case 'secretive': return 'סודי'; break; 
                }
                break;
            case 'hi-IN':
                switch ($tone) {
                    case 'funny': return 'मज़ेदार'; break; 
                    case 'casual': return 'अनौपचारिक'; break; 
                    case 'excited': return 'उत्तेजित'; break; 
                    case 'professional': return 'पेशेवर'; break; 
                    case 'witty': return 'विनोदपूर्ण'; break; 
                    case 'sarcastic': return 'व्यंग्यपूर्ण'; break; 
                    case 'feminine': return 'संज्ञा'; break; 
                    case 'masculine': return 'मदार्ना'; break; 
                    case 'bold': return 'निडर'; break; 
                    case 'dramatic': return 'नाटकीय'; break; 
                    case 'gumpy': return 'गम्पी'; break; 
                    case 'secretive': return 'गुप्त'; break; 
                }
                break;
            case 'hu-HU':
                switch ($tone) {
                    case 'funny': return 'vicces'; break; 
                    case 'casual': return 'alkalmi'; break; 
                    case 'excited': return 'izgatott'; break; 
                    case 'professional': return 'szakmai'; break; 
                    case 'witty': return 'szellemes'; break; 
                    case 'sarcastic': return 'szarkasztikus'; break; 
                    case 'feminine': return 'nőies'; break; 
                    case 'masculine': return 'férfias'; break; 
                    case 'bold': return 'bátor'; break; 
                    case 'dramatic': return 'drámai'; break; 
                    case 'gumpy': return 'gumiszerű'; break; 
                    case 'secretive': return 'titkos'; break; 
                }
                break;
            case 'is-IS':
                switch ($tone) {
                    case 'funny': return 'fyndið'; break; 
                    case 'casual': return 'frjálslegur'; break; 
                    case 'excited': return 'spenntur'; break; 
                    case 'professional': return 'faglegur'; break; 
                    case 'witty': return 'fyndinn'; break; 
                    case 'sarcastic': return 'kaldhæðni'; break; 
                    case 'feminine': return 'kvenleg'; break; 
                    case 'masculine': return 'karlkyns'; break; 
                    case 'bold': return 'feitletrað'; break; 
                    case 'dramatic': return 'dramatískt'; break; 
                    case 'gumpy': return 'gúmmí'; break; 
                    case 'secretive': return 'leyndarmál'; break; 
                }
                break;
            case 'id-ID':
                switch ($tone) {
                    case 'funny': return 'lucu'; break; 
                    case 'casual': return 'kasual'; break; 
                    case 'excited': return 'bersemangat'; break; 
                    case 'professional': return 'profesional'; break; 
                    case 'witty': return 'cerdas'; break; 
                    case 'sarcastic': return 'sarkastik'; break; 
                    case 'feminine': return 'wanita'; break; 
                    case 'masculine': return 'maskulin'; break; 
                    case 'bold': return 'berani'; break; 
                    case 'dramatic': return 'dramatis'; break; 
                    case 'gumpy': return 'bergetah'; break; 
                    case 'secretive': return 'rahasia'; break; 
                }
                break;
            case 'it-IT':
                switch ($tone) {
                    case 'funny': return 'divertente'; break; 
                    case 'casual': return 'casuale'; break; 
                    case 'excited': return 'eccitato'; break; 
                    case 'professional': return 'professionale'; break; 
                    case 'witty': return 'spiritoso'; break; 
                    case 'sarcastic': return 'sarcastico'; break; 
                    case 'feminine': return 'femminile'; break; 
                    case 'masculine': return 'maschile'; break; 
                    case 'bold': return 'grassetto'; break; 
                    case 'dramatic': return 'drammatico'; break; 
                    case 'gumpy': return 'gommoso'; break; 
                    case 'secretive': return 'segreto'; break; 
                }
                break;
            case 'ja-JP':
                switch ($tone) {
                    case 'funny': return '面白い'; break; 
                    case 'casual': return 'カジュアル'; break; 
                    case 'excited': return '興奮した'; break; 
                    case 'professional': return 'プロ'; break; 
                    case 'witty': return '機知に富んだ'; break; 
                    case 'sarcastic': return '皮肉な'; break; 
                    case 'feminine': return 'フェミニン'; break; 
                    case 'masculine': return '男性的な'; break; 
                    case 'bold': return '大胆な'; break; 
                    case 'dramatic': return '劇的'; break; 
                    case 'gumpy': return 'ガンピー'; break; 
                    case 'secretive': return '秘密主義'; break; 
                }
                break;            
            case 'jv-ID':
                switch ($tone) {
                    case 'funny': return 'lucu'; break; 
                    case 'casual': return 'sembrono'; break; 
                    case 'excited': return 'bungah'; break; 
                    case 'professional': return 'profesional'; break; 
                    case 'witty': return 'pinter'; break; 
                    case 'sarcastic': return 'sarkastik'; break; 
                    case 'feminine': return 'wadon'; break; 
                    case 'masculine': return 'lanang'; break; 
                    case 'bold': return 'kandel'; break; 
                    case 'dramatic': return 'dramatis'; break; 
                    case 'gumpy': return 'gumuk'; break; 
                    case 'secretive': return 'rahasia'; break; 
                }
                break;
            case 'ko-KR':
                switch ($tone) {
                    case 'funny': return '재미있는'; break; 
                    case 'casual': return '평상복'; break; 
                    case 'excited': return '흥분한'; break; 
                    case 'professional': return '전문적인'; break; 
                    case 'witty': return '재치 있는'; break; 
                    case 'sarcastic': return '비꼬는'; break; 
                    case 'feminine': return '여자 같은'; break; 
                    case 'masculine': return '남성 명사'; break; 
                    case 'bold': return '용감한'; break; 
                    case 'dramatic': return '극적인'; break; 
                    case 'gumpy': return '구질구질한'; break; 
                    case 'secretive': return '비밀스러운'; break; 
                }
                break;
            case 'ms-MY':
                switch ($tone) {
                    case 'funny': return 'kelakar'; break; 
                    case 'casual': return 'santai'; break; 
                    case 'excited': return 'teruja'; break; 
                    case 'professional': return 'profesional'; break; 
                    case 'witty': return 'jenaka'; break; 
                    case 'sarcastic': return 'sarkastik'; break; 
                    case 'feminine': return 'keperempuanan'; break; 
                    case 'masculine': return 'maskulin'; break; 
                    case 'bold': return 'berani'; break; 
                    case 'dramatic': return 'dramatik'; break; 
                    case 'gumpy': return 'bergetah'; break; 
                    case 'secretive': return 'berahsia'; break; 
                }
                break;
            case 'nb-NO':
                switch ($tone) {
                    case 'funny': return 'morsom'; break; 
                    case 'casual': return 'uformelt'; break; 
                    case 'excited': return 'spent'; break; 
                    case 'professional': return 'profesjonell'; break; 
                    case 'witty': return 'vittig'; break; 
                    case 'sarcastic': return 'sarkastisk'; break; 
                    case 'feminine': return 'feminin'; break; 
                    case 'masculine': return 'maskulin'; break; 
                    case 'bold': return 'dristig'; break; 
                    case 'dramatic': return 'dramatisk'; break; 
                    case 'gumpy': return 'klumpete'; break; 
                    case 'secretive': return 'hemmelighetsfull'; break; 
                }
                break;
            case 'pl-PL':
                switch ($tone) {
                    case 'funny': return 'śmieszny'; break; 
                    case 'casual': return 'zwykły'; break; 
                    case 'excited': return 'podekscytowany'; break; 
                    case 'professional': return 'profesjonalny'; break; 
                    case 'witty': return 'dowcipny'; break; 
                    case 'sarcastic': return 'sarkastyczny'; break; 
                    case 'feminine': return 'kobiecy'; break; 
                    case 'masculine': return 'rodzaj męski'; break; 
                    case 'bold': return 'pogrubiony'; break; 
                    case 'dramatic': return 'dramatyczny'; break; 
                    case 'gumpy': return 'gumowaty'; break; 
                    case 'secretive': return 'skryty'; break; 
                }
                break;
            case 'pt-PT':
                switch ($tone) {
                    case 'funny': return 'engraçado'; break; 
                    case 'casual': return 'casual'; break; 
                    case 'excited': return 'excitado'; break; 
                    case 'professional': return 'profissional'; break; 
                    case 'witty': return 'inteligente'; break; 
                    case 'sarcastic': return 'sarcástico'; break; 
                    case 'feminine': return 'feminino'; break; 
                    case 'masculine': return 'masculino'; break; 
                    case 'bold': return 'audacioso'; break; 
                    case 'dramatic': return 'dramático'; break; 
                    case 'gumpy': return 'pegajoso'; break; 
                    case 'secretive': return 'secreto'; break; 
                }
                break;
            case 'ru-RU':
                switch ($tone) {
                    case 'funny': return 'смешной'; break; 
                    case 'casual': return 'повседневный'; break; 
                    case 'excited': return 'взволнованный'; break; 
                    case 'professional': return 'профессиональный'; break; 
                    case 'witty': return 'остроумный'; break; 
                    case 'sarcastic': return 'саркастический'; break; 
                    case 'feminine': return 'женский'; break; 
                    case 'masculine': return 'мужской'; break; 
                    case 'bold': return 'смелый'; break; 
                    case 'dramatic': return 'драматический'; break; 
                    case 'gumpy': return 'липкий'; break; 
                    case 'secretive': return 'скрытный'; break; 
                }
                break;
            case 'es-ES':
                switch ($tone) {
                    case 'funny': return 'divertido'; break; 
                    case 'casual': return 'casual'; break; 
                    case 'excited': return 'уmocionado'; break; 
                    case 'professional': return 'profesional'; break; 
                    case 'witty': return 'ingenioso'; break; 
                    case 'sarcastic': return 'sarcástico'; break; 
                    case 'feminine': return 'femenino'; break; 
                    case 'masculine': return 'masculino'; break; 
                    case 'bold': return 'atrevido'; break; 
                    case 'dramatic': return 'dramático'; break; 
                    case 'gumpy': return 'gomoso'; break; 
                    case 'secretive': return 'secreto'; break; 
                }
                break;
            case 'sv-SE':
                switch ($tone) {
                    case 'funny': return 'rolig'; break; 
                    case 'casual': return 'tillfällig'; break; 
                    case 'excited': return 'upphetsad'; break; 
                    case 'professional': return 'professionell'; break; 
                    case 'witty': return 'kvick'; break; 
                    case 'sarcastic': return 'sarkastisk'; break; 
                    case 'feminine': return 'feminin'; break; 
                    case 'masculine': return 'maskulin'; break; 
                    case 'bold': return 'djärv'; break; 
                    case 'dramatic': return 'dramatisk'; break; 
                    case 'gumpy': return 'gumpig'; break; 
                    case 'secretive': return 'hemlighetsfull'; break; 
                }
                break;
            case 'th-TH':
                switch ($tone) {
                    case 'funny': return 'ตลก'; break; 
                    case 'casual': return 'ไม่เป็นทางการ'; break; 
                    case 'excited': return 'ตื่นเต้น'; break; 
                    case 'professional': return 'มืออาชีพ'; break; 
                    case 'witty': return 'มีไหวพริบ'; break; 
                    case 'sarcastic': return 'ประชดประชัน'; break; 
                    case 'feminine': return 'ของผู้หญิง'; break; 
                    case 'masculine': return 'ผู้ชาย'; break; 
                    case 'bold': return 'ตัวหนา'; break; 
                    case 'dramatic': return 'น่าทึ่ง'; break; 
                    case 'gumpy': return 'เหนียว'; break; 
                    case 'secretive': return 'ลับ'; break; 
                }
                break;
            case 'tr-TR':
                switch ($tone) {
                    case 'funny': return 'eğlenceli'; break; 
                    case 'casual': return 'gündelik'; break; 
                    case 'excited': return 'heyecanlı'; break; 
                    case 'professional': return 'profesyonel'; break; 
                    case 'witty': return 'esprili'; break; 
                    case 'sarcastic': return 'alaycı'; break; 
                    case 'feminine': return 'kadınsı'; break; 
                    case 'masculine': return 'eril'; break; 
                    case 'bold': return 'gözü pek'; break; 
                    case 'dramatic': return 'dramatik'; break; 
                    case 'gumpy': return 'sakızlı'; break; 
                    case 'secretive': return 'gizli'; break; 
                }
                break;
            case 'sw-TZ':
                switch ($tone) {
                    case 'funny': return 'kuchekesha'; break; 
                    case 'casual': return 'kawaida'; break; 
                    case 'excited': return 'msisimko'; break; 
                    case 'professional': return 'mtaalamu'; break; 
                    case 'witty': return 'mwenye akili'; break; 
                    case 'sarcastic': return 'dhihaka'; break; 
                    case 'feminine': return 'kike'; break; 
                    case 'masculine': return 'kiume'; break; 
                    case 'bold': return 'ujasiri'; break; 
                    case 'dramatic': return 'makubwa'; break; 
                    case 'gumpy': return 'gumpy'; break; 
                    case 'secretive': return 'siri'; break; 
                }
                break;
            default:
                # code...
                break;
        }
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewArticleGenerator(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'KPAQQ')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'KPAQQ')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.article-generator', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewParagraphGenerator(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'JXRZB')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'JXRZB')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.paragraph-generator', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewProsAndCons(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'OPYAB')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'OPYAB')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.pros-and-cons', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewTalkingPoints(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'VFWSQ')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'VFWSQ')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.talking-points', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewSummarizeText(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'OMMEI')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'OMMEI')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.summarize-text', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewProductDescription(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'HXLNA')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'HXLNA')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.product-description', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


     /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewStartupNameGenerator(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'DJSVM')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'DJSVM')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.startup-name-generator', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewProductNameGenerator(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'IXKBE')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'IXKBE')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.product-name-generator', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewMetaDescription(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'JCDIK')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'JCDIK')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.meta-description', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewFAQs(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'SZAUF')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'SZAUF')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.faqs', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewFAQAnswers(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'BFENK')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'BFENK')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.faq-answers', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewTestimonials(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'XLGPP')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'XLGPP')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.testimonials', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewBlogTitles(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'WGKYP')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'WGKYP')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.blog-titles', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewBlogSection(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'EEKZF')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'EEKZF')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.blog-section', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewBlogIdeas(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'KDGOX')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'KDGOX')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.blog-ideas', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewBlogIntros(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'TZTYR')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'TZTYR')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.blog-intros', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewBlogConclusion(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'ZGUKM')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'ZGUKM')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.blog-conclusion', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewContentRewriter(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'WCZGL')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'WCZGL')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.content-rewriter', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewFacebookAds(Request $request)
    {   

        $languages = Language::orderBy('languages.language', 'asc')->get();

        $all_templates = Template::orderBy('group', 'asc')->get();
        $template = Template::where('template_code', 'CTMNI')->first();
        $favorite = FavoriteTemplate::where('user_id', auth()->user()->id)->where('template_code', 'CTMNI')->first(); 
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();
        $limit = $this->settings();

        return view('user.templates.facebook-ads', compact('languages', 'template', 'all_templates', 'favorite', 'workbooks', 'limit'));
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createArticleGeneratorPrompt($title, $keywords, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Write a complete article on this topic:\n\n" . $title . "\n\nUse following keywords in the article:\n" . $keywords . "\n\nTone of voice of the article must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "اكتب مقالة حول هذا الموضوع:\n\n". $title. "\n\nاستخدم الكلمات الأساسية التالية في المقالة:\n". $keywords. "\n\nيجب أن تكون نغمة صوت المقالة:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "写一篇关于这个主题的文章：\n\n" . $title. "\n\n在文章中使用以下关键字：\n" . $keywords . "\n\n文章的语气必须是：\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Napišite članak na ovu temu:\n\n" . $title . "\n\nKoristite sljedeće ključne riječi u članku:\n" . $keywords . "\n\nTon glasa u članku mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Napište článek na toto téma:\n\n" . $title . "\n\nV článku použijte následující klíčová slova:\n" . $keywords . "\n\nTón hlasu článku musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Skriv en artikel om dette emne:\n\n" . $title. "\n\nBrug følgende søgeord i artiklen:\n" . $keywords. "\n\nTone i artiklen skal være:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Schrijf een artikel over dit onderwerp:\n\n" . $title. "\n\nGebruik de volgende trefwoorden in het artikel:\n" . $keywords. "\n\nDe toon van het artikel moet zijn:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Kirjutage sellel teemal artikkel:\n\n" . $title. "\n\nKasutage artiklis järgmisi märksõnu:\n" . $keywords. "\n\nArtikli hääletoon peab olema:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Kirjoita artikkeli tästä aiheesta:\n\n" . $title. "\n\nKäytä artikkelissa seuraavia avainsanoja:\n" . $keywords. "\n\nArtikkelin äänensävyn on oltava:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Ecrire un article sur ce sujet :\n\n" . $title . "\n\nUtilisez les mots clés suivants dans l'article :\n" . $keywords . "\n\nLe ton de la voix de l'article doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Schreiben Sie einen Artikel zu diesem Thema:\n\n" . $title . "\n\nVerwenden Sie folgende Schlüsselwörter im Artikel:\n" . $keywords . "\n\nTonfall des Artikels muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Γράψτε ένα άρθρο για αυτό το θέμα:\n\n" . $title. "\n\nΧρησιμοποιήστε τις ακόλουθες λέξεις-κλειδιά στο άρθρο:\n" . $keywords. "\n\nΟ τόνος της φωνής του άρθρου πρέπει να είναι:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "כתוב מאמר בנושא זה:\n\n" . $title . "\n\nהשתמש במילות המפתח הבאות במאמר:\n" . $keywords . "\n\nטון הדיבור של המאמר חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "इस विषय पर एक लेख लिखें:\n\n" .$title. "\n\nलेख में निम्नलिखित कीवर्ड का प्रयोग करें:\n" . $keywords . "\n\nलेख का स्वर इस प्रकार होना चाहिए:\n" . $tone_language ."\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Írjon cikket erről a témáról:\n\n" . $title. "\n\nHasználja a következő kulcsszavakat a cikkben:\n" . $keywords. "\n\nA cikk hangnemének a következőnek kell lennie:\n" . $tone_language. "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Skrifaðu grein um þetta efni:\n\n" . $title. "\n\nNotaðu eftirfarandi leitarorð í greininni:\n" . $keywords. "\n\nTónn í greininni verður að vera:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Tulis artikel tentang topik ini:\n\n" . $title . "\n\nGunakan kata kunci berikut dalam artikel:\n" . $keywords . "\n\nNada suara artikel harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Scrivi un articolo su questo argomento:\n\n" . $title . "\n\nUsa le seguenti parole chiave nell'articolo:\n" . $keywords . "\n\nIl tono di voce dell'articolo deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "このトピックに関する記事を書いてください:\n\n" . $title . "\n\n記事では次のキーワードを使用してください:\n" . $keywords . "\n\n記事の口調は次のようにする必要があります:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "이 주제에 대한 기사 쓰기:\n\n" . $title . "\n\n문서에서 다음 키워드를 사용하십시오:\n" . $keywords . "\n\n기사의 어조는 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Tulis artikel tentang topik ini:\n\n" . $title . "\n\nGunakan kata kunci berikut dalam artikel:\n" . $keywords . "\n\nNada suara artikel mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Skriv en artikkel om dette emnet:\n\n" . $title . "\n\nBruk følgende nøkkelord i artikkelen:\n" . $keywords . "\n\nTone i artikkelen må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt = "Napisz artykuł na ten temat:\n\n" . $title . "\n\nUżyj w artykule następujących słów kluczowych:\n" . $keywords . "\n\nTon wypowiedzi artykułu musi być:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Escreva um artigo sobre este tópico:\n\n" . $title . "\n\nUse as seguintes palavras-chave no artigo:\n" . $keywords . "\n\nTom de voz do artigo deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Напишите статью на эту тему:\n\n" . $title . "\n\nИспользуйте в статье следующие ключевые слова:\n" . $keywords . "\n\nТон озвучивания статьи должен быть:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Escribe un artículo sobre este tema:\n\n" . $title. "\n\nUtilice las siguientes palabras clave en el artículo:\n" . $keywords. "\n\nEl tono de voz del artículo debe ser:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skriv en artikel om detta ämne:\n\n" . $title . "\n\nAnvänd följande nyckelord i artikeln:\n" . $keywords . "\n\nTonfallet för artikeln måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Bu konuda bir makale yaz:\n\n" . $title . "\n\nMakalede şu anahtar kelimeleri kullanın:\n" . $keywords . "\n\nYazının ses tonu şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createParagraphGeneratorPrompt($title, $keywords, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Write a large and meaningful paragraph on this topic:\n\n" . $title . "\n\nUse following keywords in the paragraph:\n" . $keywords . "\n\nTone of voice of the paragraph must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "اكتب فقرة كبيرة وذات مغزى حول هذا الموضوع:\n\n". $title. "\n\nاستخدم الكلمات الأساسية التالية في الفقرة:\n". $keywords. "\n\nيجب أن تكون نغمة الصوت في الفقرة:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "就此主题写一段有意义的长篇大论：\n\n" . $title. "\n\n在段落中使用以下关键字：\n" . $keywords. "\n\n段落的语气必须是：\n" . $tone_language . "\n\n----\n写的段落：\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Napišite veliki i smisleni odlomak o ovoj temi:\n\n" . $title. "\n\nKoristite sljedeće ključne riječi u odlomku:\n" . $keywords. "\n\nTon glasa odlomka mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Napište velký a smysluplný odstavec na toto téma:\n\n" . $title . "\n\nV odstavci použijte následující klíčová slova:\n" . $keywords . "\n\nTón hlasu odstavce musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Skriv et stort og meningsfuldt afsnit om dette emne:\n\n" . $title. "\n\nBrug følgende nøgleord i afsnittet:\n" . $keywords . "\n\nTone i afsnittet skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Schrijf een grote en zinvolle paragraaf over dit onderwerp:\n\n" . $title . "\n\nGebruik de volgende trefwoorden in de alinea:\n" . $keywords . "\n\nDe toon van de alinea moet zijn:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Kirjutage sellel teemal suur ja sisukas lõik:\n\n" . $title . "\n\nKasutage lõigus järgmisi märksõnu:\n" . $keywords . "\n\nLõigu hääletoon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Kirjoita tästä aiheesta suuri ja merkityksellinen kappale:\n\n" . $title . "\n\nKäytä kappaleessa seuraavia avainsanoja:\n" . $keywords . "\n\nKappaleen äänensävyn on oltava:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Écrivez un paragraphe long et significatif sur ce sujet :\n\n" . $title . "\n\nUtilisez les mots clés suivants dans le paragraphe :\n" . $keywords . "\n\nLe ton de la voix du paragraphe doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Schreiben Sie einen großen und aussagekräftigen Absatz zu diesem Thema:\n\n" . $title . "\n\nVerwenden Sie folgende Schlüsselwörter im Absatz:\n" . $keywords . "\n\nTonlage des Absatzes muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Γράψτε μια μεγάλη και ουσιαστική παράγραφο για αυτό το θέμα:\n\n" . $title . "\n\nΧρησιμοποιήστε τις ακόλουθες λέξεις-κλειδιά στην παράγραφο:\n" . $keywords . "\n\nΟ τόνος της φωνής της παραγράφου πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "כתוב פסקה גדולה ומשמעותית בנושא זה:\n\n" . $title . "\n\nהשתמש במילות המפתח הבאות בפסקה:\n" . $keywords. "\n\nטון הדיבור של הפסקה חייב להיות:\n" . $tone_languag . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "इस विषय पर एक बड़ा और सार्थक पैराग्राफ लिखें:\n\n" . $title. "\n\nपैराग्राफ में निम्नलिखित कीवर्ड का प्रयोग करें:\n" .$keywords. "\n\nपैराग्राफ की आवाज़ का स्वर होना चाहिए:\n" .$tone_language. "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Írj egy nagy és értelmes bekezdést erről a témáról:\n\n" . $title . "\n\nHasználja a következő kulcsszavakat a bekezdésben:\n" . $keywords . "\n\nA bekezdés hangszínének a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Skrifaðu stóra og þýðingarmikla málsgrein um þetta efni:\n\n" . $title. "\n\nNotaðu eftirfarandi leitarorð í málsgreininni:\n" . $keywords. "\n\nTónn málsgreinarinnar verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Tulis paragraf yang besar dan bermakna tentang topik ini:\n\n" . $title . "\n\nGunakan kata kunci berikut dalam paragraf:\n" . $keywords . "\n\nNada suara paragraf harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Scrivi un paragrafo ampio e significativo su questo argomento:\n\n" . $title . "\n\nUsare le seguenti parole chiave nel paragrafo:\n" . $keywords. "\n\nIl tono di voce del paragrafo deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "このトピックについて大きくて意味のある段落を書いてください:\n\n" . $title. "\n\n段落内で次のキーワードを使用してください:\n" . $keywords . "\n\n段落の口調は次のようにする必要があります:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "이 주제에 대해 크고 의미 있는 단락 작성:\n\n" . $title . "\n\n단락에서 다음 키워드를 사용하십시오:\n" . $keywords . "\n\n문단의 어조는 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Tulis perenggan yang besar dan bermakna tentang topik ini:\n\n" . $title . "\n\nGunakan kata kunci berikut dalam perenggan:\n" . $keywords . "\n\nNada suara perenggan mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Skriv et stort og meningsfullt avsnitt om dette emnet:\n\n" . $title . "\n\nBruk følgende nøkkelord i avsnittet:\n" . $keywords . "\n\nTone i avsnittet må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt = "Napisz duży i znaczący akapit na ten temat:\n\n" . $title . "\n\nUżyj następujących słów kluczowych w akapicie:\n" . $keywords . "\n\nTon głosu akapitu musi być:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Escreva um parágrafo grande e significativo sobre este tópico:\n\n" . $title . "\n\nUse as seguintes palavras-chave no parágrafo:\n" . $keywords . "\n\nTom de voz do parágrafo deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Напишите большой и осмысленный абзац на эту тему:\n\n" . $title . "\n\nИспользуйте следующие ключевые слова в абзаце:\n" . $keywords . "\n\nТон абзаца должен быть:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Escribe un párrafo extenso y significativo sobre este tema:\n\n" . $title. "\n\nUtilice las siguientes palabras clave en el párrafo:\n" . $keywords. "\n\nEl tono de voz del párrafo debe ser:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skriv ett stort och meningsfullt stycke om detta ämne:\n\n" . $title . "\n\nAnvänd följande nyckelord i stycket:\n" . $keywords . "\n\nTonfallet i stycket måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Bu konu hakkında geniş ve anlamlı bir paragraf yaz:\n\n" . $title . "\n\nParagrafta şu anahtar sözcükleri kullanın:\n" . $keywords . "\n\nParagrafın ses tonu şöyle olmalıdır:\n" . $tone_language ."\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createProsAndConsPrompt($title, $keywords, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Write pros and cons of these products:\n\n" . $title . "\n\nUse following product description:\n" . $keywords . "\n\nTone of voice of the pros and cons must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "اكتب إيجابيات وسلبيات هذه المنتجات:\n\n". $title. "\n\nاستخدم وصف المنتج التالي:\n". $keywords. "\n\nيجب أن تكون نغمة الإيجابيات والسلبيات:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "写下这些产品的优缺点：\n\n" . $title . "\n\n使用以下产品描述：\n" . $keywords . "\n\n正反的语气必须是：\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Napišite prednosti i nedostatke ovih proizvoda:\n\n" . $title . "\n\nKoristite sljedeći opis proizvoda:\n" . $keywords . "\n\nTon glasa za i protiv mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Napište výhody a nevýhody těchto produktů:\n\n" . $title . "\n\nPoužijte následující popis produktu:\n" . $keywords . "\n\nTón hlasu pro a proti musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Skriv fordele og ulemper ved disse produkter:\n\n" . $title. "\n\nBrug følgende produktbeskrivelse:\n" . $keywords. "\n\nTone af fordele og ulemper skal være:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Schrijf de voor- en nadelen van deze producten op:\n\n" . $title. "\n\nGebruik de volgende productbeschrijving:\n" . $keywords. "\n\nDe toon van de voor- en nadelen moet zijn:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Kirjutage nende toodete plussid ja miinused:\n\n" . $title. "\n\nKasutage järgmist tootekirjeldust:\n" . $keywords. "\n\nPusside ja miinuste hääletoon peab olema:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Kirjoita näiden tuotteiden hyvät ja huonot puolet:\n\n" . $title. "\n\nKäytä seuraavaa tuotekuvausta:\n" . $keywords. "\n\nPussien ja haittojen äänensävyn on oltava:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Écrivez les avantages et les inconvénients de ces produits :\n\n" . $title . "\n\nUtilisez la description de produit suivante :\n" . $keywords . "\n\nLe ton de la voix des pour et des contre doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Schreiben Sie Vor- und Nachteile dieser Produkte auf:\n\n" . $title . "\n\nFolgende Produktbeschreibung verwenden:\n" . $keywords . "\n\nTonfall der Vor- und Nachteile muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Γράψτε τα πλεονεκτήματα και τα μειονεκτήματα αυτών των προϊόντων:\n\n" . $title. "\n\nΧρησιμοποιήστε την ακόλουθη περιγραφή προϊόντος:\n" . $keywords. "\n\nΟ τόνος της φωνής των πλεονεκτημάτων και των μειονεκτημάτων πρέπει να είναι:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "כתוב יתרונות וחסרונות של המוצרים האלה:\n\n" . $title . "\n\nהשתמש בתיאור המוצר הבא:\n" . $keywords . "\n\nטון הדיבור של היתרונות והחסרונות חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "इन उत्पादों के फायदे और नुकसान लिखें:\n\n" .  $title. "\n\nनिम्न उत्पाद विवरण का उपयोग करें:\n" . $keywords . "\n\n पक्ष और विपक्ष की आवाज़ का स्वर होना चाहिए:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Írja le ezeknek a termékeknek előnyeit és hátrányait:\n\n" . $title. "\n\nHasználja a következő termékleírást:\n" . $keywords. "\n\nAz előnyök és hátrányok hangnemének a következőnek kell lennie:\n" . $tone_language. "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Skrifaðu kosti og galla þessara vara:\n\n" . $title. "\n\nNotaðu eftirfarandi vörulýsingu:\n" . $keywords. "\n\nTónn fyrir kosti og galla verður að vera:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Tulis pro dan kontra dari produk ini:\n\n" . $title . "\n\nGunakan deskripsi produk berikut:\n" . $keywords . "\n\nNada suara pro dan kontra harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Scrivi pro e contro di questi prodotti:\n\n" . $title . "\n\nUsa la seguente descrizione del prodotto:\n" . $keywords . "\n\nIl tono di voce dei pro e dei contro deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "これらの製品の長所と短所を書いてください:\n\n" . $title . "\n\n次の製品説明を使用してください:\n" . $keywords . "\n\n賛成派と反対派の口調は次のとおりでなければなりません:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "이 제품의 장단점을 작성하십시오:\n\n" . $title . "\n\n다음 제품 설명을 사용하십시오:\n" . $keywords . "\n\n장단점의 어조는 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Tulis kebaikan dan keburukan produk ini:\n\n" . $title . "\n\nGunakan penerangan produk berikut:\n" . $keywords . "\n\nNada suara kebaikan dan keburukan mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Skriv fordeler og ulemper med disse produktene:\n\n" . $title . "\n\nBruk følgende produktbeskrivelse:\n" . $keywords . "\n\nTone for fordeler og ulemper må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Napisz wady i zalety tych produktów:\n\n" . $title . "\n\nUżyj następującego opisu produktu:\n" . $keywords . "\n\nTon głosu za i przeciw musi być następujący:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Escreva prós e contras destes produtos:\n\n" . $title . "\n\nUse a seguinte descrição do produto:\n" . $keywords . "\n\nTom de voz dos prós e contras deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Напишите плюсы и минусы этих продуктов:\n\n" . $title . "\n\nИспользуйте следующее описание продукта:\n" . $keywords . "\n\nТон озвучивания плюсов и минусов должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Escriba pros y contras de estos productos:\n\n" . $title. "\n\nUtilice la siguiente descripción del producto:\n" . $keywords. "\n\nEl tono de voz de los pros y los contras debe ser:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skriv för- och nackdelar med dessa produkter:\n\n" . $title . "\n\nAnvänd följande produktbeskrivning:\n" . $keywords . "\n\nTonfall för för- och nackdelar måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Bu ürünlerin artılarını ve eksilerini yazın:\n\n" . $title . "\n\nAşağıdaki ürün açıklamasını kullanın:\n" . $keywords . "\n\nSes tonu artıları ve eksileri şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createTalkingPointsPrompt($title, $keywords, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Write short, simple and informative talking points for:\n\n" . $title . "\n\nAnd also similar talking points for subheadings:\n" . $keywords . "\n\nTone of voice of the paragraph must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "اكتب نقاط حديث قصيرة وبسيطة وغنية بالمعلومات من أجل:\n\n". $title. "\n\nونقاط الحديث المشابهة للعناوين الفرعية:\n". $keywords. "\n\nيجب أن تكون نغمة الصوت في الفقرة:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "为以下内容编写简短、简单且信息丰富的谈话要点：\n\n" . $title . "\n\n以及副标题的类似谈话要点：\n" . $keywords . "\n\n段落的语气必须是 :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Napišite kratke, jednostavne i informativne teme za:\n\n" . $title. "\n\nI također slične teme za podnaslove:\n" . $keywords. "\n\nTon glasa odlomka mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Napište krátké, jednoduché a informativní body pro:\n\n" . $title . "\n\nA také podobná témata pro podnadpisy:\n" . $keywords . "\n\nTón hlasu odstavce musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Skriv korte, enkle og informative talepunkter til:\n\n" . $title. "\n\nOg også lignende talepunkter for underoverskrifter:\n" . $keywords . "\n\nTonefaldet i afsnittet skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Schrijf korte, eenvoudige en informatieve gespreksonderwerpen voor:\n\n" . $title . "\n\nEn ook gelijkaardige gespreksonderwerpen voor tussenkopjes:\n" . $keywords . "\n\nDe toon van de alinea moet zijn:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Kirjutage lühikesed, lihtsad ja informatiivsed jutupunktid:\n\n" . $title . "\n\nJa ka sarnased jutupunktid alapealkirjade jaoks:\n" . $keywords . "\n\nLõigu hääletoon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Kirjoita lyhyitä, yksinkertaisia ja informatiivisia puheenaiheita:\n\n" . $title . "\n\nJa myös samanlaisia puheenaiheita alaotsikoille:\n" . $keywords . "\n\nKappaleen äänensävyn on oltava:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Rédigez des points de discussion courts, simples et informatifs pour :\n\n" . $title . "\n\nEt également des points de discussion similaires pour les sous-titres :\n" . $keywords . "\n\nLe ton de la voix du paragraphe doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Schreiben Sie kurze, einfache und informative Gesprächsthemen für:\n\n" . $title . "\n\nUnd auch ähnliche Gesprächsthemen für Unterüberschriften:\n" . $keywords . "\n\nTonlage des Absatzes muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Γράψτε σύντομα, απλά και κατατοπιστικά σημεία ομιλίας για:\n\n" . $title . "\n\nΚαι επίσης παρόμοια σημεία συζήτησης για υποτίτλους:\n" . $keywords . "\n\nΟ τόνος της φωνής της παραγράφου πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "כתוב נקודות דיבור קצרות, פשוטות ואינפורמטיביות עבור:\n\n" . $title . "\n\nוגם נקודות דיבור דומות עבור כותרות משנה:\n" . $keywords. "\n\nטון הדיבור של הפסקה חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "के लिए संक्षिप्त, सरल और जानकारीपूर्ण चर्चा बिंदु लिखें:\n\n" .$title. "\n\nऔर उपशीर्षक के लिए समान चर्चा बिंदु:\n" . $keywords. "\n\nपैराग्राफ की आवाज़ का स्वर होना चाहिए:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Írjon rövid, egyszerű és informatív beszédpontokat:\n\n" . $title . "\n\nÉs hasonló beszédpontok az alcímekhez:\n" . $keywords . "\n\nA bekezdés hangszínének a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Skrifaðu stutta, einfalda og upplýsandi umræðupunkta fyrir:\n\n" . $title. "\n\nOg líka svipaðar umræður fyrir undirfyrirsagnir:\n" . $keywords . "\n\nTónn málsgreinarinnar verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Tulis poin pembicaraan singkat, sederhana dan informatif untuk:\n\n" . $title . "\n\nDan juga poin pembicaraan serupa untuk subjudul:\n" . $keywords . "\n\nNada suara paragraf harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Scrivi punti di discussione brevi, semplici e informativi per:\n\n" . $title . "\n\nE anche punti di discussione simili per i sottotitoli:\n" . $keywords. "\n\nIl tono di voce del paragrafo deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "短く、シンプルで有益な論点を書いてください:\n\n" . $title. "\n\n小見出しにも同様の要点があります:\n" . $keywords . "\n\n段落の口調は次のようにする必要があります:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "다음에 대한 짧고 간단하며 유익한 요점을 작성하십시오:\n\n" . $title . "\n\n또한 부제목에 대한 유사한 논점:\n" . $keywords . "\n\n문단의 어조는 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Tulis perkara perbualan yang pendek, ringkas dan bermaklumat untuk:\n\n" . $title . "\n\nDan juga perkara yang serupa untuk tajuk kecil:\n" . $keywords . "\n\nNada suara perenggan mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Skriv korte, enkle og informative samtalepunkter for:\n\n" . $title . "\n\nOg også lignende samtalepunkter for underoverskrifter:\n" . $keywords . "\n\nTone i avsnittet må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Napisz krótkie, proste i pouczające przemówienia dla:\n\n" . $title . "\n\nA także podobne uwagi do podtytułów:\n" . $keywords . "\n\nTon głosu akapitu musi być:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Escreva pontos de conversa curtos, simples e informativos para:\n\n" . $title . "\n\nE também pontos de discussão semelhantes para subtítulos:\n" . $keywords . "\n\nTom de voz do parágrafo deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Напишите короткие, простые и информативные тезисы для:\n\n" . $title . "\n\nА также аналогичные темы для подзаголовков:\n" . $keywords . "\n\nТон абзаца должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Escribe puntos de conversación breves, sencillos e informativos para:\n\n" . $title . "\n\nY también puntos de conversación similares para los subtítulos:\n". $keywords. "\n\nEl tono de voz del párrafo debe ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skriv korta, enkla och informativa samtalspunkter för:\n\n" . $title . "\n\nOch även liknande diskussionspunkter för underrubriker:\n" . $keywords . "\n\nTonfallet i stycket måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Skriv korta, enkla och informativa samtalspunkter för:\n\n" . $title. "\n\nOch även liknande diskussionspunkter för underrubriker:\n" . $keywords . "\n\nTonfallet i stycket måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createSummarizeTextPrompt($text, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Summarize this text in a short concise way:\n\n" . $text . "\n\nTone of summary must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "لخص هذا النص بإيجاز قصير:\n\n". $text. "\n\nيجب أن تكون نغمة التلخيص:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "用简短的方式总结这段文字：\n\n" . $text . "\n\n摘要语气必须是：\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Ukratko sažeti ovaj tekst:\n\n" . $text. "\n\nTon sažetka mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Shrňte tento text krátkým výstižným způsobem:\n\n" . $text . "\n\nTón shrnutí musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Opsummer denne tekst på en kort og præcis måde:\n\n" . $text. "\n\nTone i resumé skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Vat deze tekst kort en bondig samen:\n\n" . $text . "\n\nDe toon van de samenvatting moet zijn:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Tehke see tekst lühidalt kokkuvõtlikult:\n\n" . $text . "\n\nKokkuvõtte toon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Tee tämä teksti lyhyesti ytimekkäästi:\n\n" . $text . "\n\nYhteenvedon äänen tulee olla:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Résumez ce texte de manière courte et concise :\n\n" . $text . "\n\nLe ton du résumé doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Fass diesen Text kurz und prägnant zusammen:\n\n" . $text . "\n\nTon der Zusammenfassung muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Συνοψήστε αυτό το κείμενο με σύντομο και συνοπτικό τρόπο:\n\n" . $text . "\n\nΟ τόνος της σύνοψης πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "סכם את הטקסט הזה בצורה קצרה תמציתית:\n\n" . $text . "\n\nטון הסיכום חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "इस पाठ को संक्षेप में संक्षेप में प्रस्तुत करें:\n\n" . $text . "\n\nसारांश का लहजा होना चाहिए:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Összefoglalja ezt a szöveget röviden, tömören:\n\n" . $text . "\n\nAz összefoglaló hangjának a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Dregðu saman þennan texta á stuttan hnitmiðaðan hátt:\n\n" . $text. "\n\nTónn yfirlits verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Rangkum teks ini dengan cara yang singkat dan padat:\n\n" . $text . "\n\nNada ringkasan harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Riassumi questo testo in modo breve e conciso:\n\n" . $text. "\n\nIl tono del riassunto deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "このテキストを短く簡潔に要約してください:\n\n" . $text . "\n\n要約のトーンは:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "이 텍스트를 짧고 간결하게 요약:\n\n" . $text . "\n\n요약 톤은 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Ringkaskan teks ini dengan cara ringkas yang ringkas:\n\n" . $text . "\n\nNada ringkasan mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Opsummer denne teksten på en kortfattet måte:\n\n" . $text . "\n\nTone i sammendraget må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Podsumuj ten tekst w zwięzły sposób:\n\n" . $text . "\n\nTon podsumowania musi być:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Resuma este texto de forma curta e concisa:\n\n" . $text . "\n\nO tom do resumo deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Кратко изложите этот текст:\n\n" . $text . "\n\nТон резюме должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Resume este texto de forma breve y concisa:\n\n" . $text. "\n\nEl tono del resumen debe ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Sammanfatta den här texten på ett kortfattat sätt:\n\n" . $text . "\n\nTonen i sammanfattningen måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Bu metni kısa ve öz bir şekilde özetleyin:\n\n" . $text. "\n\nÖzetin tonu şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createProductDescriptionPrompt($title, $audience, $description, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Write a long creative product description for:" . $title . "\n\nTarget audience is:" . $audience . "\n\nUse this description:" . $description . "\n\nTone of generated text must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "اكتب وصفًا إبداعيًا طويلًا للمنتج لـ:". $title . "\n\nالجمهور المستهدف هو:". $audience. "\n\nاستخدم هذا الوصف:". $description. "\n\nيجب أن تكون نغمة النص الناتج:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "为：写一个长的创意产品描述" . $title . "\n\n目标受众是：" . $audience. "\n\n使用这个描述：" . $description . "\n\n生成文本的基调必须是：\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Napišite dugačak kreativni opis proizvoda za:" . $title. "\n\nCiljana publika je:" . $audience. "\n\nKoristite ovaj opis:" . $description. "\n\nTon generiranog teksta mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Napište dlouhý popis kreativního produktu pro:" . $title . "\n\nCílové publikum je:" . $audience . "\n\nPoužijte tento popis:" . $description . "\n\nTón generovaného textu musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Skriv en lang kreativ produktbeskrivelse til:" . $title. "\n\nMålgruppe er:" . $audience. "\n\nBrug denne beskrivelse:" . $description. "\n\nTone i genereret tekst skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Schrijf een lange creatieve productbeschrijving voor:" . $title . "\n\nDoelgroep is:" . $audience. "\n\nGebruik deze omschrijving:" . $description . "\n\nDe toon van gegenereerde tekst moet zijn:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Kirjutage pikk loominguline tootekirjeldus:" . $title . "\n\nSihtpublik on:" . $audience . "\n\nKasutage seda kirjeldust:" . $description . "\n\nLoodud teksti toon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Kirjoita pitkä luova tuotekuvaus:" . $title . "\n\nKohdeyleisö on:" . $audience. "\n\nKäytä tätä kuvausta:" . $description . "\n\nLuodun tekstin äänen tulee olla:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Rédigez une longue description de produit créative pour :" . $title . "\n\nLe public cible est :" . $audience . "\n\nUtilisez cette description :" . $description . "\n\nLe ton du texte généré doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Schreiben Sie eine lange kreative Produktbeschreibung für:" . $title . "\n\nZielpublikum ist:" . $audience . "\n\nVerwenden Sie diese Beschreibung:" . $description . "\n\nTon des generierten Textes muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Γράψτε μια μεγάλη περιγραφή δημιουργικού προϊόντος για:" . $title . "\n\nΤο κοινό-στόχος είναι:" . $audience . "\n\nΧρησιμοποιήστε αυτήν την περιγραφή:" . $description . "\n\nΟ τόνος του κειμένου που δημιουργείται πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "כתוב תיאור מוצר יצירתי ארוך עבור:" . $title . "\n\nקהל היעד הוא:" . $audience . "\n\nהשתמש בתיאור הזה:" . $description . "\n\nטון הטקסט שנוצר חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "इसके लिए एक लंबा रचनात्मक उत्पाद विवरण लिखें:" . $title. "\n\nलक्षित दर्शक हैं:" . $audience . "\n\nइस विवरण का उपयोग करें:" . $description . "\n\nजनरेट किए गए टेक्स्ट का टोन होना चाहिए:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Írjon hosszú kreatív termékleírást a következőhöz:" . $title . "\n\nA célközönség:" . $audience . "\n\nHasználja ezt a leírást:" . $description . "\n\nA generált szöveg hangjának a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Skrifaðu langa skapandi vörulýsingu fyrir:" . $title. "\n\nMarkhópur er:" . $audience. "\n\nNotaðu þessa lýsingu:" . $description. "\n\nTónn texta sem myndast verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Tulis deskripsi produk kreatif yang panjang untuk:" . $title. "\n\nTarget audiens adalah:" . $audience . "\n\nGunakan deskripsi ini:" . $description . "\n\nNada teks yang dihasilkan harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Scrivi una lunga descrizione del prodotto creativo per:" . $title . "\n\nIl pubblico di destinazione è:" . $audience. "\n\nUsa questa descrizione:" . $description . "\n\nIl tono del testo generato deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "次の製品の長いクリエイティブな説明を書いてください:" . $title. "\n\n対象者:" . $audience. "\n\nこの説明を使用してください:" . $description . "\n\n生成されたテキストのトーンは:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "다음에 대한 길고 창의적인 제품 설명 작성:" . $title. "\n\n대상은:" . $audience . "\n\n이 설명을 사용하십시오:" . $description . "\n\n생성된 텍스트의 톤은 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Tulis penerangan produk kreatif yang panjang untuk:" . $title . "\n\nKhalayak sasaran ialah:" . $audience . "\n\nGunakan penerangan ini:" . $description . "\n\nNada teks yang dijana mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Skriv en lang kreativ produktbeskrivelse for:" . $title . "\n\nMålgruppen er:" . $audience . "\n\nBruk denne beskrivelsen:" . $description . "\n\nTone i generert tekst må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Napisz długi kreatywny opis produktu dla:" . $title . "\n\nDocelowi odbiorcy to:" . $audience . "\n\nUżyj tego opisu:" . $description . "\n\nTon generowanego tekstu musi być:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Escreva uma longa descrição de produto criativo para:" . $title . "\n\nO público-alvo é:" . $audience . "\n\nUse esta descrição:" . $description. "\n\nO tom do texto gerado deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Напишите длинное креативное описание продукта для:" . $title . "\n\nЦелевая аудитория:" . $audience . "\n\nИспользуйте это описание:" . $description . "\n\nТон генерируемого текста должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Escriba una descripción de producto creativa larga para:" . $title . "\n\nEl público objetivo es:" . $audience . "\n\nUsar esta descripción:" . $description . "\n\nEl tono del texto generado debe ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skriv en lång kreativ produktbeskrivning för:" . $title . "\n\nMålgruppen är:" . $audience . "\n\nAnvänd denna beskrivning:" . $description . "\n\nTonen i genererad text måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Şunun için uzun bir yaratıcı ürün açıklaması yazın:" . $title . "\n\nHedef kitle:" . $audience . "\n\nBu açıklamayı kullanın:" . $description. "\n\nOluşturulan metnin tonu şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStartupNameGeneratorPrompt($keywords, $description, $language) {


        switch ($language) {
            case 'en-US':
                    $prompt = "Generate cool, creative, and catchy names for startup description: " . $description . "\n\nSeed words: " . $keywords . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "أنشئ أسماء رائعة ومبتكرة وجذابة لوصف بدء التشغيل: ". $description . "\n\nكلمات المصدر: ". $keywords. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "为启动描述生成酷炫、有创意且朗朗上口的名称: " . $description . "\n\n种子词: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Generiraj cool, kreativna i privlačna imena za opis pokretanja: " . $description. "\n\nPočetne riječi: " . $keywords. "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Vygenerujte skvělé, kreativní a chytlavé názvy pro popis spuštění: " . $description . "\n\nVýchozí slova: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Generer seje, kreative og fængende navne til opstartsbeskrivelse: " . $description. "\n\nSeed ord: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Genereer coole, creatieve en pakkende namen voor opstartbeschrijving: " . $description . "\n\nZaalwoorden: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Looge käivituskirjelduse jaoks lahedad, loomingulised ja meeldejäävad nimed: " . $description . "\n\nAlgussõnad: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Luo siistejä, luovia ja tarttuvia nimiä käynnistyksen kuvaukselle: " . $description . "\n\nSiemensanat: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Générez des noms sympas, créatifs et accrocheurs pour la description de démarrage : " . $description . "\n\nMots clés : " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Erzeuge coole, kreative und einprägsame Namen für die Startup-Beschreibung: " . $description . "\n\nStartwörter: " . $keywords. "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Δημιουργήστε όμορφα, δημιουργικά και ελκυστικά ονόματα για περιγραφή εκκίνησης: " . $description . "\n\nΔείτε λέξεις: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "צור שמות מגניבים, יצירתיים וקליטים לתיאור ההפעלה: " . $description . "\n\nמילות הזרע: " . $keywords. "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "स्टार्टअप विवरण के लिए बढ़िया, रचनात्मक और आकर्षक नाम उत्पन्न करें: " . $description . "\n\nबीज शब्द: " . $keywords. "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Generál menő, kreatív és fülbemászó neveket az indítási leíráshoz: " . $description . "\n\nKezdőszavak: " . $keywords . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Búðu til flott, skapandi og grípandi nöfn fyrir ræsingarlýsingu: " . $description. "\n\n Fræorð: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Hasilkan nama yang keren, kreatif, dan menarik untuk deskripsi startup: " . $description . "\n\nBenih kata: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Genera nomi interessanti, creativi e accattivanti per la descrizione dell'avvio: " . $description . "\n\nParole seme: " . $keywords. "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "スタートアップの説明にクールでクリエイティブでキャッチーな名前を付けてください: " . $description. "\n\nシード ワード: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "스타트업 설명을 위한 멋지고 창의적이며 기억하기 쉬운 이름 생성: " . $description . "\n\n시드 단어: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Jana nama yang keren, kreatif dan menarik untuk penerangan permulaan: " . $description . "\n\nPerkataan benih: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Generer kule, kreative og fengende navn for oppstartsbeskrivelse: " . $description . "\n\nFrøord: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Wygeneruj fajne, kreatywne i chwytliwe nazwy dla opisu startowego: " . $description . "\n\nSłowa źródłowe: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Gerar nomes legais, criativos e atraentes para a descrição da inicialização: " . $description. "\n\nPalavras iniciais: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Создавайте крутые, креативные и запоминающиеся названия для описания стартапа: " . $description. "\n\nИсходные слова: " . $keywords . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Genera nombres geniales, creativos y pegadizos para la descripción de inicio: " . $description . "\n\nPalabras semilla: " . $keywords. "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skapa coola, kreativa och catchy namn för startbeskrivning: " . $description . "\n\nFröord: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Başlangıç açıklaması için havalı, yaratıcı ve akılda kalıcı adlar oluşturun: " . $description . "\n\nÖz sözcükler: " . $keywords. "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createProductNameGeneratorPrompt($keywords, $description, $language) {

        switch ($language) {
            case 'en-US':
                    $prompt = "Create creative product names: " . $description . "\n\nSeed words: " . $keywords . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "إنشاء أسماء المنتجات الإبداعية:". $description. "\n\n كلمات المصدر:".$keywords. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "创建有创意的产品名称：". $description. "\n\n种子词：" . $keywords. "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Stvorite kreativne nazive proizvoda: ". $description. "\n\nPočetne riječi: " . $keywords. "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Vytvořit názvy kreativních produktů: " . $description . "\n\nVýchozí slova: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Opret kreative produktnavne: " . $description. "\n\nSeed ord: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Creëer creatieve productnamen: ". $description. "\n\nZaalwoorden: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Loo loomingulised tootenimed: " . $description . "\n\nAlgussõnad: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Luo luovia tuotenimiä: " . $description . "\n\nSiemensanat: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Créer des noms de produits créatifs : " . $description . "\n\nMots clés : " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Kreative Produktnamen erstellen: " . $description . "\n\nStartwörter: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Δημιουργία δημιουργικών ονομάτων προϊόντων: " . $description . "\n\nΔείτε λέξεις: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "צור שמות מוצרים יצירתיים: " . $description . "\n\nמילות הזרע: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "रचनात्मक उत्पाद नाम बनाएँ:" . $description .  "\n\nबीज शब्द:" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Kreatív terméknevek létrehozása: " . $description . "\n\nKiinduló szavak: " . $keywords . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Búa til skapandi vöruheiti: " . $description. "\n\nSeed orð: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Buat nama produk kreatif: " . $description . "\n\nBenih kata: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Crea nomi di prodotti creativi: " . $description . "\n\nParole iniziali: " . $keywords. "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "クリエイティブな商品名を作成する: " . $description. "\n\nシード ワード: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "창의적인 제품 이름 만들기: " . $description . "\n\n시드 단어: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Buat nama produk kreatif: " . $description . "\n\nPerkataan benih: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Lag kreative produktnavn: " . $description . "\n\nSeed ord: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Utwórz kreatywne nazwy produktów:" . $description . "\n\nSłowa źródłowe: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Criar nomes de produtos criativos: " . $description. "\n\nPalavras iniciais: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Создайте креативные названия продуктов: " . $description . "\n\nИсходные слова: " . $keywords . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Crear nombres de productos creativos: " . $description . "\n\nPalabras semilla: " . $keywords. "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skapa kreativa produktnamn: " . $description . "\n\nFröord: " . $keywords . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Yaratıcı ürün adları oluşturun: " . $description. "\n\nÖz sözcükler: " . $keywords ."\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createMetaDescriptionPrompt($title, $keywords, $description, $language) {
        

        switch ($language) {
            case 'en-US':
                    $prompt = "Write SEO meta description for:\n\n" . $description . "\n\nWebsite name is:\n" . $title . "\n\nSeed words:\n" . $keywords . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "اكتب وصف تعريف SEO لـ:\n\n". $description. "\n\nاسم موقع الويب هو:\n". $title. "\n\nكلمات المصدر:\n". $keywords. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "为以下内容编写 SEO 元描述：\n\n" . $description. "\n\n 网站名称是：\n" . $title. "\n\n 种子词：\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Napišite SEO meta opis za:\n\n" . $description. "\n\n Naziv web stranice je:\n" . $title. "\n\n Početne riječi:\n" . $keywords. "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Zapsat SEO meta popis pro:\n\n" . $description . "\n\n Název webu je:\n" . $title . "\n\n Výchozí slova:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Skriv SEO-metabeskrivelse for:\n\n" . $description. "\n\n Webstedets navn er:\n" . $title. "\n\n Frøord:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Schrijf SEO-metabeschrijving voor:\n\n" . $description . "\n\n Websitenaam is:\n" . $title . "\n\n Zaadwoorden:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Kirjutage SEO metakirjeldus:\n\n" . $description . "\n\n Veebisaidi nimi on:\n" . $title . "\n\n Algsõnad:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Kirjoita hakukoneoptimoinnin metakuvaus kohteelle:\n\n" . $description . "\n\n Verkkosivuston nimi on:\n" . $title . "\n\n Alkusanat:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Ecrire une méta description SEO pour :\n\n" . $description . "\n\n Le nom du site Web est :\n" . $title . "\n\n Mots clés :\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "SEO-Metabeschreibung schreiben für:\n\n" . $description . "\n\n Website-Name ist:\n" . $title . "\n\n Ausgangswörter:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Γράψτε μετα-περιγραφή SEO για:\n\n" . $description . "\n\n Το όνομα ιστότοπου είναι:\n" . $title . "\n\n Βασικές λέξεις:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "כתוב מטא תיאור SEO עבור:\n\n" . $description. "\n\n שם האתר הוא:\n" . $title . "\n\n מילות זרע:\n" . $keywords. "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "इसके लिए SEO मेटा विवरण लिखें:\n\n" . $description.  "\n\n वेबसाइट का नाम है:\n" . $title ."\n\n बीज शब्द:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Írjon SEO meta leírást:\n\n" . $description . "\n\n A webhely neve:\n" . $title . "\n\n Kezdőszavak:\n" . $keywords . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Skrifaðu SEO lýsilýsingu fyrir:\n\n" . $description. "\n\n Heiti vefsvæðis er:\n" . $title. "\n\n Fræorð:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Tulis deskripsi meta SEO untuk:\n\n" . $description . "\n\n Nama situs web adalah:\n" . $title. "\n\n Kata bibit:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Scrivi meta descrizione SEO per:\n\n" . $description . "\n\n Il nome del sito web è:\n" . $title . "\n\n Parole seme:\n" . $keywords. "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "以下の SEO メタ ディスクリプションを書き込みます:\n\n" . $description. "\n\n ウェブサイト名:\n" . $title . "\n\n シード ワード:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "다음에 대한 SEO 메타 설명 쓰기:\n\n" . $description . "\n\n 웹사이트 이름:\n" . $title. "\n\n 시드 단어:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Tulis perihalan meta SEO untuk:\n\n" . $description . "\n\n Nama tapak web ialah:\n" . $title . "\n\n Perkataan benih:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Skriv SEO-metabeskrivelse for:\n\n" . $description. "\n\n Nettstedets navn er:\n" . $title . "\n\n Frøord:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt = "Napisz metaopis SEO dla:\n\n" . $description . "\n\n Nazwa witryny to:\n" . $title . "\n\n Słowa źródłowe:\n" . $keywords. "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Escreva a meta descrição de SEO para:\n\n" . $description. "\n\n O nome do site é:\n" . $title . "\n\n Palavras-chave:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Напишите мета-описание SEO для:\n\n" . $description. "\n\n Имя веб-сайта:\n" . $title . "\n\n Исходные слова:\n" . $keywords . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Escribir meta descripción SEO para:\n\n" . $description . "\n\n El nombre del sitio web es:\n" . $title . "\n\n Palabras semilla:\n" . $keywords. "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skriv SEO-metabeskrivning för:\n\n" . $description . "\n\n Webbplatsens namn är:\n" . $title . "\n\n Fröord:\n" . $keywords . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Şunun için SEO meta açıklaması yaz:\n\n" . $description . "\n\n Web sitesi adı:\n" . $title . "\n\n Çekirdek sözcükler:\n" . $keywords. "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createFAQsPrompt($title, $description, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Generate list of 10 frequently asked questions based on description:\n\n" . $description . "\n\n Product name:\n" . $title . "\n\n Tone of voice of the questions must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "قم بإنشاء قائمة من 10 أسئلة متداولة بناءً على الوصف:\n\n". $description. "\n\nاسم المنتج:\n".$title . "\n\nيجب أن تكون نبرة صوت الأسئلة:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "根据描述生成 10 个常见问题列表：\n\n" . $description. "\n\n 产品名称：\n" . $title . "\n\n 提问的语气必须是：\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Generiraj popis od 10 često postavljanih pitanja na temelju opisa:\n\n" . $description. "\n\n Naziv proizvoda:\n" . $title . "\n\n Ton glasa pitanja mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Vygenerujte seznam 10 často kladených otázek na základě popisu:\n\n" . $description. "\n\n Název produktu:\n" . $title . "\n\n Tón hlasu otázek musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Generer en liste med 10 ofte stillede spørgsmål baseret på beskrivelse:\n\n" . $description. "\n\n Produktnavn:\n" . $title . "\n\n Tonen i spørgsmålene skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Genereer een lijst met 10 veelgestelde vragen op basis van beschrijving:\n\n" . $description . "\n\n Productnaam:\n" . $title  . "\n\n Tone of voice van de vragen moet zijn:\n" . $tone_language  . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Loo kirjelduse põhjal 10 korduma kippuva küsimuse loend:\n\n" . $description . "\n\n Toote nimi:\n" . $title  . "\n\n Küsimuste hääletoon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Luo luettelo 10 usein kysytystä kysymyksestä kuvauksen perusteella:\n\n" . $description . "\n\n Tuotteen nimi:\n" . $title . "\n\n Kysymysten äänensävyn tulee olla:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Générer une liste de 10 questions fréquemment posées en fonction de la description :\n\n" . $description . "\n\n Nom du produit :\n" . $title  . "\n\n Le ton de la voix des questions doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Erzeuge eine Liste mit 10 häufig gestellten Fragen basierend auf der Beschreibung:\n\n" . $description. "\n\n Produktname:\n" . $title  . "\n\n Tonfall der Fragen muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Δημιουργία λίστας 10 συχνών ερωτήσεων με βάση την περιγραφή:\n\n" . $description. "\n\n Όνομα προϊόντος:\n" . $title . "\n\n Ο τόνος της φωνής των ερωτήσεων πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "צור רשימה של 10 שאלות נפוצות על סמך תיאור:\n\n" . $description . "\n\n שם המוצר:\n" . $title . "\n\n טון הדיבור של השאלות חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "विवरण के आधार पर अक्सर पूछे जाने वाले 10 प्रश्नों की सूची तैयार करें:\n\n".$description. "\n\n उत्पाद का नाम:\n" . $title ."\n\n प्रश्नों का स्वर इस प्रकार होना चाहिए:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Létrehozzon 10 gyakran ismételt kérdést tartalmazó listát a leírás alapján:\n\n" . $description . "\n\n Terméknév:\n" . $title . "\n\n A kérdések hangnemének a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Búðu til lista yfir 10 algengar spurningar byggðar á lýsingu:\n\n" . $description. "\n\n Vöruheiti:\n" . $title . "\n\n Röddtónn spurninganna verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Buat daftar 10 pertanyaan umum berdasarkan deskripsi:\n\n" . $description . "\n\n Nama produk:\n" . $title  . "\n\n Nada suara pertanyaan harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Genera elenco di 10 domande frequenti in base alla descrizione:\n\n" . $description . "\n\n Nome prodotto:\n" . $title  . "\n\n Il tono di voce delle domande deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "説明に基づいて 10 のよくある質問のリストを生成します:\n\n" . $description. "\n\n 製品名:\n" . $title . "\n\n 質問のトーンは次のようにする必要があります:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "설명에 따라 자주 묻는 질문 10개 목록 생성:\n\n" . $description. "\n\n 제품 이름:\n" . $title . "\n\n 질문의 어조는 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Jana senarai 10 soalan lazim berdasarkan penerangan:\n\n" . $description . "\n\n Nama produk:\n" . $title  . "\n\n Nada suara soalan mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Generer liste over 10 vanlige spørsmål basert på beskrivelse:\n\n" . $description . "\n\n Produktnavn:\n" . $title  . "\n\n Tonen i spørsmålene må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Wygeneruj listę 10 najczęściej zadawanych pytań na podstawie opisu:\n\n" . $description . "\n\n Nazwa produktu:\n" . $title  . "\n\n Ton pytań musi być następujący:\n" . $tone_language  . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Gerar lista de 10 perguntas frequentes com base na descrição:\n\n" . $description. "\n\n Nome do produto:\n" . $title  . "\n\n Tom de voz das perguntas deve ser:\n" . $tone_language  . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Создать список из 10 часто задаваемых вопросов на основе описания:\n\n" . $description. "\n\n Название продукта:\n" . $title  . "\n\n Тон вопросов должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Generar una lista de 10 preguntas frecuentes según la descripción:\n\n" . $description . "\n\n Nombre del producto:\n" . $title  . "\n\n El tono de voz de las preguntas debe ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skapa en lista med 10 vanliga frågor baserat på beskrivning:\n\n" . $description . "\n\n Produktnamn:\n" . $title . "\n\n Tonen i frågorna måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Açıklamaya göre sık sorulan 10 sorudan oluşan bir liste oluşturun:\n\n" . $description ."\n\n Ürün adı:\n" . $title ."\n\n Soruların ses tonu şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createFAQAnswersPrompt($title, $question, $description, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Generate creative 5 answers to question:\n\n" . $question . "\n\n Product name:\n" . $title . "\n\n Product description:\n" . $description . "\n\n Tone of voice of the answers must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "إنشاء 5 إجابات إبداعية على السؤال:\n\n". $question. "\n\nاسم المنتج:\n". $title. "\n\nوصف المنتج:\n". $description."\n\nيجب أن تكون نبرة صوت الإجابات:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "为问题生成有创意的 5 个答案：\n\n". $question. "\n\n 产品名称：\n" . $title. "\n\n 产品描述：\n" . $description. "\n\n 回答的语气必须是：\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Generiraj kreativnih 5 odgovora na pitanje:\n\n" . $question. "\n\n Naziv proizvoda:\n" . $title. "\n\n Opis proizvoda:\n" . $description. "\n\n Ton glasa odgovora mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Vygenerujte kreativu 5 odpovědí na otázku:\n\n" . $question . "\n\n Název produktu:\n" . $title . "\n\n Popis produktu:\n" . $description . "\n\n Tón hlasu odpovědí musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Generer kreative 5 svar på spørgsmål:\n\n" . $question. "\n\n Produktnavn:\n" . $title. "\n\n Produktbeskrivelse:\n" . $description. "\n\n Tonen i svarene skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Genereer creatieve 5 antwoorden op vraag:\n\n" . $question . "\n\n Productnaam:\n" . $title . "\n\n Productbeschrijving:\n" . $description . "\n\n Tone of voice van de antwoorden moet zijn:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Loo 5 loomingulist vastust küsimusele:\n\n" . $question . "\n\n Toote nimi:\n" . $title . "\n\n Toote kirjeldus:\n" . $description . "\n\n Vastuste hääletoon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Luo luova 5 vastausta kysymykseen:\n\n" . $question. "\n\n Tuotteen nimi:\n" . $title . "\n\n Tuotteen kuvaus:\n" . $description . "\n\n Vastausten äänensävyn tulee olla:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Générer la création 5 réponses à la question :\n\n" . $question . "\n\n Nom du produit :\n" . $title . "\n\n Description du produit :\n" . $description . "\n\n Le ton de la voix des réponses doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Erzeuge kreative 5 Antworten auf Frage:\n\n" . $question . "\n\n Produktname:\n" . $title . "\n\n Produktbeschreibung:\n" . $description . "\n\n Tonfall der Antworten muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Δημιουργία δημιουργικού 5 απαντήσεων στην ερώτηση:\n\n" . $question . "\n\n Όνομα προϊόντος:\n" . $title . "\n\n Περιγραφή προϊόντος:\n" . $description. "\n\n Ο τόνος της φωνής των απαντήσεων πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "צור קריאייטיב 5 תשובות לשאלה:\n\n" . $question. "\n\n שם המוצר:\n" . $title . "\n\n תיאור המוצר:\n" . $description. "\n\n טון הדיבור של התשובות חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "प्रश्न के रचनात्मक 5 उत्तर उत्पन्न करें:\n\n" .$question . "\n\n उत्पाद का नाम:\n" . $title .  "\n\n उत्पाद विवरण:\n" . $description."\n\n जवाबों के स्वर इस प्रकार होने चाहिए:\n" . $tone_language ."\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Kreatív 5 válasz létrehozása a következő kérdésre:\n\n" . $question . "\n\n Terméknév:\n" . $title . "\n\n Termékleírás:\n" . $description . "\n\n A válaszok hangszínének a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Búðu til skapandi 5 svör við spurningu:\n\n" . $question. "\n\n Vöruheiti:\n" . $title. "\n\n Vörulýsing:\n" . $description. "\n\n Röddtónn svara verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Hasilkan 5 jawaban kreatif untuk pertanyaan:\n\n" . $question. "\n\n Nama produk:\n" . $title. "\n\n Deskripsi produk:\n" . $description . "\n\n Nada suara jawaban harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Genera creatività 5 risposte alla domanda:\n\n" . $question. "\n\n Nome prodotto:\n" . $title . "\n\n Descrizione del prodotto:\n" . $description . "\n\n Il tono di voce delle risposte deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "質問に対する創造的な 5 つの回答を生成します:\n\n" . $question. "\n\n 製品名:\n" . $title. "\n\n 製品説明:\n" . $description. "\n\n 回答の声のトーンは次のとおりでなければなりません:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "질문에 대한 창의적인 5개 답변 생성:\n\n" . $question. "\n\n 제품 이름:\n" . $title . "\n\n 제품 설명:\n" . $description . "\n\n 답변의 어조는 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Jana 5 jawapan kreatif untuk soalan:\n\n" . $question . "\n\n Nama produk:\n" . $title . "\n\n Penerangan produk:\n" . $description . "\n\n Nada suara jawapan mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Generer kreative 5 svar på spørsmål:\n\n" . $question . "\n\n Produktnavn:\n" . $title . "\n\n Produktbeskrivelse:\n" . $description . "\n\n Tonen til svarene må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Wygeneruj kreację 5 odpowiedzi na pytanie:\n\n" . $question . "\n\n Nazwa produktu:\n" . $title . "\n\n Opis produktu:\n" . $description . "\n\n Ton odpowiedzi musi być:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Gerar criativo 5 respostas para a pergunta:\n\n" . $question. "\n\n Nome do produto:\n" . $title . "\n\n Descrição do produto:\n" . $description. "\n\n Tom de voz das respostas deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Сгенерировать креативные 5 ответов на вопрос:\n\n" . $question. "\n\n Название продукта:\n" . $title . "\n\n Описание товара:\n" . $description. "\n\n Тон голоса ответов должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Generar 5 respuestas creativas a la pregunta:\n\n" . $question. "\n\n Nombre del producto:\n" . $title . "\n\n Descripción del producto:\n" . $description. "\n\n El tono de voz de las respuestas debe ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Generera kreativa fem svar på frågan:\n\n" . $question . "\n\n Produktnamn:\n" . $title . "\n\n Produktbeskrivning:\n" . $description . "\n\n Tonen i svaren måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Soruya yaratıcı 5 yanıt oluşturun:\n\n" . $question. "\n\n Ürün adı:\n" . $title ."\n\n Ürün açıklaması:\n" . $description."\n\n Cevapların ses tonu şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createTestimonialsPrompt($title, $description, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Create 5 creative customer reviews for a product. Product name:\n\n" . $title . "\n\n Product description:\n" . $description . "\n\n Tone of voice of the customer review must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "أنشئ 5 مراجعات إبداعية للعملاء لمنتج ما. اسم المنتج:\n\n". $title. "\n\nوصف المنتج:\n". $description. "\n\nيجب أن تكون نبرة صوت مراجعة العميل:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "为产品创建 5 个有创意的客户评论。产品名称：\n\n". $title. "\n\n 产品描述：\n" . $description. "\n\n 客户评论的语气必须是：\n" . $tone_language.  "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Stvorite 5 kreativnih korisničkih recenzija za proizvod. Naziv proizvoda:\n\n" . $title. "\n\n Opis proizvoda:\n" . $description. "\n\n Ton glasa klijentove recenzije mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Vytvořte 5 kreativních zákaznických recenzí pro produkt. Název produktu:\n\n" . $title . "\n\n Popis produktu:\n" . $description . "\n\n Tón hlasu zákaznické recenze musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Opret 5 kreative kundeanmeldelser for et produkt. Produktnavn:\n\n" . $title. "\n\n Produktbeskrivelse:\n" . $description. "\n\n Tonen i kundeanmeldelsen skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Maak 5 creatieve klantrecensies voor een product. Productnaam:\n\n" . $title. "\n\n Productbeschrijving:\n" . $description . "\n\n Tone of voice van de klantrecensie moet zijn:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Looge toote kohta 5 loomingulist kliendiarvustust. Toote nimi:\n\n" . $title . "\n\n Toote kirjeldus:\n" . $description . "\n\n Kliendiarvustuse hääletoon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Luo 5 luovaa asiakasarvostelua tuotteelle. Tuotteen nimi:\n\n" . $title . "\n\n Tuotteen kuvaus:\n" . $description . "\n\n Asiakasarvion äänensävyn on oltava:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Créez 5 avis clients créatifs pour un produit. Nom du produit :\n\n" . $title. "\n\n Description du produit :\n" . $description . "\n\n Le ton de la voix de l'avis client doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Erstellen Sie 5 kreative Kundenrezensionen für ein Produkt. Produktname:\n\n" . $title . "\n\n Produktbeschreibung:\n" . $description . "\n\n Tonfall der Kundenrezension muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Δημιουργήστε 5 δημιουργικές κριτικές πελατών για ένα προϊόν. Όνομα προϊόντος:\n\n" . $title . "\n\n Περιγραφή προϊόντος:\n" . $description . "\n\n Ο ήχος της κριτικής πελάτη πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "צור 5 ביקורות יצירתיות של לקוחות עבור מוצר. שם המוצר:\n\n" . $title . "\n\n תיאור המוצר:\n" . $description . "\n\n טון הדיבור של ביקורת הלקוח חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "एक उत्पाद के लिए 5 रचनात्मक ग्राहक समीक्षाएँ बनाएँ। उत्पाद का नाम:\n\n".$title."\n\n उत्पाद विवरण:\n" . $description. "\n\n ग्राहक समीक्षा का स्वर होना चाहिए:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Hozzon létre 5 kreatív vásárlói véleményt egy termékről. Termék neve:\n\n" . $title . "\n\n Termékleírás:\n" . $description . "\n\n A vásárlói vélemény hangnemének a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Búðu til 5 skapandi umsagnir viðskiptavina fyrir vöru. Vöruheiti:\n\n" . $title. "\n\n Vörulýsing:\n" . $description. "\n\n Rödd í umsögn viðskiptavina verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Buat 5 ulasan pelanggan yang kreatif untuk sebuah produk. Nama produk:\n\n" . $title . "\n\n Deskripsi produk:\n" . $description . "\n\n Nada suara ulasan pelanggan harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Crea 5 recensioni cliente creative per un prodotto. Nome prodotto:\n\n" . $title . "\n\n Descrizione del prodotto:\n" . $description. "\n\n Il tono di voce della recensione del cliente deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "商品のクリエイティブなカスタマー レビューを 5 つ作成します。商品名:\n\n" . $title. "\n\n 製品説明:\n" . $description. "\n\n カスタマー レビューの声調は次のとおりでなければなりません:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "제품에 대해 5개의 창의적인 고객 리뷰를 작성하십시오. 제품 이름:\n\n" . $title. "\n\n 제품 설명:\n" . $description . "\n\n 고객 리뷰의 어조는 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Buat 5 ulasan pelanggan kreatif untuk produk. Nama produk:\n\n" . $title . "\n\n Penerangan produk:\n" . $description . "\n\n Nada suara ulasan pelanggan mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Lag 5 kreative kundeanmeldelser for et produkt. Produktnavn:\n\n" . $title . "\n\n Produktbeskrivelse:\n" . $description . "\n\n Tonen i kundeanmeldelsen må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Utwórz 5 kreatywnych recenzji klientów dla produktu. Nazwa produktu:\n\n" . $ttitle . "\n\n Opis produktu:\n" . $description. "\n\n Ton opinii klienta musi być następujący:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Crie 5 avaliações criativas de clientes para um produto. Nome do produto:\n\n" . $title. "\n\n Descrição do produto:\n" . $description. "\n\n O tom de voz da avaliação do cliente deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Создайте 5 креативных отзывов клиентов о продукте. Название продукта:\n\n" . $title . "\n\n Описание товара:\n" . $description . "\n\n Тон голоса отзыва клиента должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Cree 5 reseñas creativas de clientes para un producto. Nombre del producto:\n\n" . $title . "\n\n Descripción del producto:\n" . $description . "\n\n El tono de voz de la reseña del cliente debe ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skapa 5 kreativa kundrecensioner för en produkt. Produktnamn:\n\n" . $title . "\n\n Produktbeskrivning:\n" . $description . "\n\n Tonen i kundrecensionen måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Bir ürün için 5 yaratıcı müşteri yorumu oluşturun. Ürün adı:\n\n" . $title. "\n\n Ürün açıklaması:\n" . $description. "\n\n Müşteri incelemesinin ses tonu şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createBlogTitlesPrompt($description, $language) {

        switch ($language) {
            case 'en-US':
                    $prompt = "Generate 10 catchy blog titles for:\n\n" . $description . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "قم بإنشاء 10 عناوين مدونة جذابة لـ:\n\n". $description. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "为以下内容生成 10 个吸引人的博客标题：\n\n". $description. "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Generiraj 10 privlačnih naslova bloga za:\n\n" . $description. "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Vygenerujte 10 chytlavých názvů blogů pro:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Generer 10 fængende blogtitler til:\n\n" . $description. "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Genereer 10 pakkende blogtitels voor:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Loo 10 meeldejäävat ajaveebi pealkirja:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Luo 10 tarttuvaa blogiotsikkoa:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Générez 10 titres de blog accrocheurs pour :\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Generiere 10 einprägsame Blog-Titel für:\n\n" . $description. "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Δημιουργήστε 10 εντυπωσιακούς τίτλους ιστολογίου για:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "צור 10 כותרות בלוג קליטות עבור:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "10 आकर्षक ब्लॉग शीर्षक उत्पन्न करें:\n\n" .$description. "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Generálj 10 fülbemászó blogcímet a következőhöz:\n\n" . $description . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Búðu til 10 grípandi bloggtitla fyrir:\n\n" . $description. "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Hasilkan 10 judul blog menarik untuk:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Genera 10 titoli di blog accattivanti per:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "キャッチーなブログ タイトルを 10 個生成します:\n\n" . $description. "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "다음에 대한 10개의 눈길을 끄는 블로그 제목 생성:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Jana 10 tajuk blog yang menarik untuk:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Generer 10 fengende bloggtitler for:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Wygeneruj 10 chwytliwych tytułów blogów dla:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Gerar 10 títulos de blog cativantes para:\n\n" . $description. "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Создайте 10 броских заголовков блога для:\n\n" . $description . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Generar 10 títulos de blog pegadizos para:\n\n" . $description . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Generera 10 catchy bloggtitlar för:\n\n" . $description. "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "10 akılda kalıcı blog başlığı oluşturun:\n\n" . $description. "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createBlogSectionPrompt($title, $subheadings, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Write a full blog section with at least 5 large paragraphs about:\n\n" . $title . "\n\nSplit by subheadings:\n" . $subheadings . "\n\nTone of voice of the paragraphs must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "اكتب قسم مدونة كاملًا يحتوي على 5 فقرات كبيرة على الأقل حول:\n\n". $title. "\n\nانقسام حسب العناوين الفرعية:\n". $subheadings. "\n\nيجب أن تكون نغمة صوت الفقرات:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "写一个完整的博客部分，其中至少包含 5 个大段落：\n\n". $title. "\n\n按副标题拆分：\n" . $subheadings."\n\n段落的语气必须是：\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Napišite cijeli odjeljak bloga s najmanje 5 velikih odlomaka o:\n\n" . $title. "\n\nPodijeli po podnaslovima:\n" . $subheadings. "\n\nTon glasa odlomaka mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Napište celou sekci blogu s alespoň 5 velkými odstavci o:\n\n" . $title . "\n\nRozdělit podle podnadpisů:\n" . $subheadings . "\n\nTón hlasu odstavců musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Skriv en komplet blogsektion med mindst 5 store afsnit om:\n\n" . $title. "\n\nOpdelt efter underoverskrifter:\n" . $subheadings . "\n\nTonefaldet i afsnittene skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Schrijf een volledig bloggedeelte met minimaal 5 grote paragrafen over:\n\n" . $title . "\n\nGesplitst door subkoppen:\n" . $subheadings . "\n\nDe toon van de alinea's moet zijn:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Kirjutage terve blogijaotis vähemalt 5 suure lõiguga teemal:\n\n" . $title . "\n\nJagatud alampealkirjade järgi:\n" . $subheadings. "\n\nLõigete hääletoon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Kirjoita koko blogiosio, jossa on vähintään 5 suurta kappaletta aiheesta:\n\n" . $title . "\n\nJaettu alaotsikoiden mukaan:\n" . $subheadings . "\n\nKappaleiden äänensävyn on oltava:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Écrivez une section de blog complète avec au moins 5 grands paragraphes sur :\n\n" . $title. "\n\nDiviser par sous-titres :\n" . $subheadings . "\n\nLe ton de la voix des paragraphes doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Schreiben Sie einen vollständigen Blog-Abschnitt mit mindestens 5 großen Absätzen über:\n\n" . $title . "\n\nAufgeteilt nach Unterüberschriften:\n" . $subheadings . "\n\nTonfall der Absätze muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Γράψτε μια πλήρη ενότητα ιστολογίου με τουλάχιστον 5 μεγάλες παραγράφους σχετικά με:\n\n" . $title . "\n\nΔιαίρεση κατά υποτίτλους:\n" . $subheadings. "\n\nΟ τόνος της φωνής των παραγράφων πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "כתוב מדור בלוג מלא עם לפחות 5 פסקאות גדולות על:\n\n" . $title . "\n\nפיצול לפי כותרות משנה:\n" . $subheadings . "\n\nטון הדיבור של הפסקאות חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "इस बारे में कम से कम 5 बड़े अनुच्छेदों के साथ एक पूर्ण ब्लॉग अनुभाग लिखें:\n\n" .$title."\n\nउपशीर्षकों द्वारा विभाजित करें:\n" . $subheadings. "\n\nपैराग्राफ की आवाज का स्वर होना चाहिए:\n" . $tone_language."\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Írjon egy teljes blogrészt, legalább 5 nagy bekezdéssel erről:\n\n" . $title . "\n\nAlcímek szerint felosztva:\n" . $subheadings . "\n\nA bekezdések hangnemének a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Skrifaðu heilan blogghluta með að minnsta kosti 5 stórum málsgreinum um:\n\n" . $title. "\n\nDeilt eftir undirfyrirsögnum:\n" . $subheadings. "\n\nTónn málsgreina verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Tulis bagian blog lengkap dengan setidaknya 5 paragraf besar tentang:\n\n" . $title. "\n\nDibagi berdasarkan subjudul:\n" . $subheadings . "\n\nNada suara paragraf harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Scrivi una sezione completa del blog con almeno 5 paragrafi di grandi dimensioni su:\n\n" . $title . "\n\nDiviso per sottotitoli:\n" . $subheadings . "\n\nIl tono di voce dei paragrafi deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "次の内容について、少なくとも 5 つの大きな段落を含む完全なブログ セクションを作成します:\n\n" . $title. "\n\n小見出しで分割:\n" . $subheadings . "\n\n段落の口調は次のようにする必要があります:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "다음에 대해 최소 5개의 큰 단락으로 전체 블로그 섹션을 작성하세요.\n\n" . $title. "\n\n하위 제목으로 분할:\n" . $subheadings . "\n\n문단의 어조는 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Tulis bahagian blog penuh dengan sekurang-kurangnya 5 perenggan besar tentang:\n\n" . $title. "\n\nPisah mengikut subtajuk:\n" . $subheadings . "\n\nNada suara perenggan mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Skriv en fullstendig bloggseksjon med minst 5 store avsnitt om:\n\n" . $title. "\n\nSplitt etter underoverskrifter:\n" . $subheadings . "\n\nTone i avsnittene må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Napisz pełną sekcję bloga zawierającą co najmniej 5 dużych akapitów na temat:\n\n" . $title . "\n\nPodział według podtytułów:\n" . $subheadings . "\n\nTon głosu akapitów musi być:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Escreva uma seção de blog completa com pelo menos 5 parágrafos grandes sobre:\n\n" . $title. "\n\nDivisão por subtítulos:\n" . $subheadings . "\n\nTom de voz dos parágrafos deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Напишите полный раздел блога, содержащий не менее 5 больших абзацев о:\n\n" . $title . "\n\nРазделить по подзаголовкам:\n" . $subheadings . "\n\nТон озвучивания абзацев должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Escribe una sección de blog completa con al menos 5 párrafos extensos sobre:\n\n" . $title . "\n\nDividir por subtítulos:\n" . $subheadings. "\n\nEl tono de voz de los párrafos debe ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skriv en fullständig bloggsektion med minst 5 stora stycken om:\n\n" . $title . "\n\nDela upp efter underrubriker:\n" . $subheadings . "\n\nTonfallet i styckena måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Şununla ilgili en az 5 büyük paragraf içeren eksiksiz bir blog bölümü yazın:\n\n" . $title. "\n\nAlt başlıklara göre ayır:\n" . $subheadings . "\n\nParagrafların ses tonu şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createBlogIdeasPrompt($title, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Write interesting blog ideas and outline about:\n\n" . $title . "\n\n Tone of voice of the ideas must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "اكتب أفكار مدونة ممتعة وحدد مخططًا تفصيليًا حول:\n\n".$title. "\n\nيجب أن تكون نبرة صوت الأفكار:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "写下有趣的博客想法和大纲：\n\n" . $title. "\n\n 想法的语气必须是：\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Napišite zanimljive ideje za blog i skicirajte o:\n\n" . $title. "\n\n Ton glasa ideja mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Pište zajímavé nápady na blog a přehled o:\n\n" . $title . "\n\n Tón hlasu nápadů musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Skriv interessante blogideer og skitser om:\n\n" . $title. "\n\n Tonen i ideerne skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Schrijf interessante blogideeën en schets over:\n\n" . $title . "\n\n De toon van de ideeën moet zijn:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Kirjutage huvitavaid ajaveebi ideid ja kirjeldage:\n\n" . $title . "\n\n Ideede hääletoon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Kirjoita mielenkiintoisia blogiideoita ja hahmotelkaa aiheesta:\n\n" . $title . "\n\n Ideoiden äänensävyn tulee olla:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Rédigez des idées de blog intéressantes et décrivez :\n\n" . $title . "\n\n Le ton de la voix des idées doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Schreiben Sie interessante Blog-Ideen und skizzieren Sie über:\n\n" . $title . "\n\n Tonfall der Ideen muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Γράψτε ενδιαφέρουσες ιδέες ιστολογίου και περιγράψτε τα σχετικά:\n\n" . $title . "\n\n Ο τόνος της φωνής των ιδεών πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "כתוב רעיונות מעניינים לבלוג ותאר את:\n\n" . $title . "\n\n טון הדיבור של הרעיונות חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "दिलचस्प ब्लॉग विचार लिखें और इसके बारे में रूपरेखा लिखें:\n\n" . $title. "\n\n विचारों का स्वर होना चाहिए:\n" .$tone_language. "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Írjon érdekes blogötleteket és vázlatot erről:\n\n" . $title . "\n\n Az ötletek hangnemének a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Skrifaðu áhugaverðar blogghugmyndir og gerðu grein fyrir:\n\n" . $title. "\n\n Rödd hugmyndanna verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Tulis ide blog yang menarik dan uraikan tentang:\n\n" . $title . "\n\n Nada suara ide harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Scrivi interessanti idee per il blog e delinea su:\n\n" . $title . "\n\n Il tono di voce delle idee deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "興味深いブログのアイデアと概要を書きます:\n\n" . $title. "\n\n アイデアの口調は次のとおりでなければなりません:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "흥미로운 블로그 아이디어를 작성하고 다음에 대한 개요를 작성하세요.\n\n" . $title . "\n\n 아이디어의 어조는 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Tulis idea blog yang menarik dan gariskan tentang:\n\n" . $title . "\n\n Nada suara idea mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Skriv interessante bloggideer og skisser om:\n\n" . $title . "\n\n Tonen til ideene må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Napisz ciekawe pomysły na bloga i zarys tematu:\n\n" . $title . "\n\n Ton głosu pomysłów musi być:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Escreva ideias de blog interessantes e descreva sobre:\n\n" . $title . "\n\n Tom de voz das ideias deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Напишите интересные идеи для блога и расскажите о:\n\n" . $title . "\n\n Тон голоса идей должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Escriba ideas de blog interesantes y esboce sobre:\n\n" . $title . "\n\n El tono de voz de las ideas debe ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skriv intressanta bloggidéer och beskriv:\n\n" . $title . "\n\n Tonen i idéerna måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "İlginç blog fikirleri yazın ve hakkında ana hatları çizin:\n\n" . $title. "\n\n Fikirlerin ses tonu şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


     /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createBlogIntrosPrompt($title, $description, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Write an interesting blog post intro about:\n\n" . $description . "\n\n Blog post title:\n" . $title . "\n\nTone of voice of the blog intro must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "اكتب مقدمة مدونة شيقة عن:\n\n". $description. "\n\nعنوان منشور المدونة:\n". $title. "\n\nيجب أن تكون نغمة الصوت في مقدمة المدونة:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "写一篇有趣的博客文章介绍：\n\n". $description. "\n\n 博文标题：\n" . $title. "\n\n博客介绍的语气必须是：\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Napišite uvod u zanimljiv blog post o:\n\n" . $description. "\n\n Naslov posta na blogu:\n" . $title. "\n\nTon glasa uvoda u blog mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Napište zajímavý úvod do blogového příspěvku o:\n\n" . $description . "\n\n Název příspěvku na blogu:\n" . $title . "\n\nTón hlasu úvodu blogu musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Skriv et interessant blogindlæg om:\n\n" . $description. "\n\n Blogindlægs titel:\n" . $title. "\n\nTone i blogintroen skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Schrijf een interessante blogpost-intro over:\n\n" . $description . "\n\n Titel blogpost:\n" . $title . "\n\nDe toon van de blogintro moet zijn:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Kirjutage huvitav blogipostituse tutvustus teemal:\n\n" . $description . "\n\n Blogipostituse pealkiri:\n" . $title . "\n\nBlogi sissejuhatuse hääletoon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Kirjoita mielenkiintoinen blogikirjoituksen esittely aiheesta:\n\n" . $description . "\n\n Blogiviestin otsikko:\n" . $title . "\n\nBlogin johdannon äänensävyn on oltava:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Rédigez une introduction intéressante sur :\n\n" . $description . "\n\n Titre de l'article de blog :\n" . $title . "\n\nLe ton de la voix de l'intro du blog doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Schreiben Sie eine interessante Einführung in einen Blog-Beitrag über:\n\n" . $description . "\n\n Titel des Blogposts:\n" . $title . "\n\nTonlage des Blog-Intros muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Γράψτε μια ενδιαφέρουσα εισαγωγή δημοσίευσης ιστολογίου σχετικά με:\n\n" . $description . "\n\n Τίτλος ανάρτησης ιστολογίου:\n" . $title . "\n\nΟ τόνος της φωνής της εισαγωγής του ιστολογίου πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "כתוב מבוא פוסט מעניין בבלוג על:\n\n" . $description . "\n\n כותרת פוסט הבלוג:\n" . $title . "\n\nטון הדיבור של ההקדמה לבלוג חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "इस बारे में एक रोचक ब्लॉग पोस्ट परिचय लिखें:\n\n" .$description. "\n\n ब्लॉग पोस्ट शीर्षक:\n" . $title. "\n\nब्लॉग के परिचय का स्वर होना चाहिए:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Írjon érdekes blogbejegyzést erről:\n\n" . $description . "\n\n Blogbejegyzés címe:\n" . $title . "\n\nA blogbevezető hangnemének a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Skrifaðu áhugaverða bloggfærslu um:\n\n" . $description. "\n\n Titill bloggfærslu:\n" . $title. "\n\nTónn í blogginngangi verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Tulis pengantar postingan blog yang menarik tentang:\n\n" . $description . "\n\n Judul entri blog:\n" . $title . "\n\nNada suara intro blog harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Scrivi un'interessante introduzione al post del blog su:\n\n" . $description . "\n\n Titolo del post del blog:\n" . $title . "\n\nIl tono di voce dell'introduzione del blog deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "興味深いブログ投稿の紹介を書いてください:\n\n" . $description. "\n\n ブログ記事のタイトル:\n" . $title. "\n\nブログのイントロのトーンは次のようにする必要があります:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "다음에 대한 흥미로운 블로그 게시물 소개 작성:\n\n" . $description . "\n\n 블로그 게시물 제목:\n" . $title . "\n\n블로그 소개의 어조는 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Tulis intro catatan blog yang menarik tentang:\n\n" . $description . "\n\n Tajuk catatan blog:\n" . $title . "\n\nNada suara intro blog mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Skriv en interessant introduksjon til blogginnlegg om:\n\n" . $description . "\n\n Tittel på blogginnlegg:\n" . $title . "\n\nTone i bloggintroen må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Napisz interesujące wprowadzenie do wpisu na blogu na temat:\n\n" . $description . "\n\n Tytuł wpisu na blogu:\n" . $title . "\n\nTon głosu we wstępie do bloga musi być:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Escreva uma introdução de postagem de blog interessante sobre:\n\n" . $description. "\n\n Título da postagem do blog:\n" . $title . "\n\nTom de voz da introdução do blog deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Напишите интересное введение в блог о:\n\n" . $description . "\n\n Заголовок сообщения в блоге:\n" . $title . "\n\nТон озвучивания вступления блога должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Escribe una introducción de blog interesante sobre:\n\n" . $description . "\n\n Título de la publicación del blog:\n" . $title . "\n\nEl tono de voz de la intro del blog debe ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skriv ett intressant blogginlägg om:\n\n" . $description . "\n\n Blogginläggets titel:\n" . $title . "\n\nRöst i bloggintrot måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Şununla ilgili ilginç bir blog yazısı yaz:\n\n" . $description. "\n\n Blog gönderisi başlığı:\n" . $title. "\n\nBlog girişinin ses tonu şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createBlogConclusionPrompt($title, $description, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Write a blog article conclusion for:\n\n" . $description . "\n\n Blog article title:\n" . $title . "\n\nTone of voice of the conclusion must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "اكتب مقالاً ختامياً لـ:\n\n". $description. "\n\nعنوان مقالة المدونة:\n". $title. "\n\n يجب أن تكون نغمة صوت الاستنتاج:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "为以下内容写一篇博客文章结论：\n\n" . $description. "\n\n 博客文章标题：\n" . $title . "\n\n结论的语气必须是：\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Napišite zaključak članka na blogu za:\n\n" . $description. "\n\n Naslov članka na blogu:\n" . $title. "\n\nTon glasa zaključka mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Napište závěr článku blogu pro:\n\n" . $description . "\n\n Název článku blogu:\n" . $title . "\n\nTón hlasu závěru musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Skriv en blogartikel konklusion for:\n\n" . $description. "\n\n Blogartikeltitel:\n" . $title. "\n\nTone i konklusionen skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Schrijf een conclusie van een blogartikel voor:\n\n" . $description . "\n\n Titel blogartikel:\n" . $title . "\n\nDe toon van de conclusie moet zijn:\n" . $tone_language  . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Kirjutage blogiartikli järeldus:\n\n" . $description . "\n\n Blogi artikli pealkiri:\n" . $title . "\n\nJärelduse hääletoon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Kirjoita blogiartikkelin päätelmä:\n\n" . $description . "\n\n Blogiartikkelin otsikko:\n" . $title . "\n\nJohtopäätöksen äänensävyn on oltava:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Rédigez une conclusion d'article de blog pour :\n\n" . $description . "\n\n Titre de l'article du blog :\n" . $title . "\n\nLe ton de la voix de la conclusion doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Schreiben Sie einen Blogartikel-Abschluss für:\n\n" . $description . "\n\n Titel des Blog-Artikels:\n" . $title . "\n\nTonfall der Schlussfolgerung muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Γράψτε ένα συμπέρασμα άρθρου ιστολογίου για:\n\n" . $description . "\n\n Τίτλος άρθρου ιστολογίου:\n" . $title . "\n\nΟ τόνος της φωνής του συμπεράσματος πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "כתוב מסקנת מאמר בבלוג עבור:\n\n" . $description . "\n\n כותרת מאמר הבלוג:\n" . $title . "\n\nטון הדיבור של המסקנה חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "इसके लिए एक ब्लॉग लेख निष्कर्ष लिखें:\n\n" .$description. "\n\n ब्लॉग लेख का शीर्षक:\n" . $title. "\n\nनिष्कर्ष की आवाज़ का स्वर होना चाहिए:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Írjon blogcikk következtetést:\n\n" . $description . "\n\n Blog cikk címe:\n" . $title . "\n\nA következtetés hangnemének a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Skrifaðu niðurstöðu blogggreinar fyrir:\n\n" . $description. "\n\n Titill blogggreinar:\n" . $title. "\n\nTónn í niðurstöðunni verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Tulis kesimpulan artikel blog untuk:\n\n" . $description . "\n\n Judul artikel blog:\n" . $title . "\n\nNada suara kesimpulan harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Scrivi la conclusione di un articolo di blog per:\n\n" . $description . "\n\n Titolo articolo blog:\n" . $title . "\n\nIl tono di voce della conclusione deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "次のブログ記事の結論を書きます:\n\n" . $description. "\n\n ブログ記事のタイトル:\n" . $title. "\n\n結論の口調は:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "다음에 대한 블로그 기사 결론 쓰기:\n\n" . $description . "\n\n 블로그 기사 제목:\n" . $title . "\n\n결론의 어조는 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "다음에 대한 블로그 기사 결론 쓰기:\n\n" . $description . "\n\n 블로그 기사 제목:\n" . $title. "\n\n결론의 어조는 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Skriv en bloggartikkelkonklusjon for:\n\n" . $description . "\n\n Bloggartikkeltittel:\n" . $title . "\n\nTone i konklusjonen må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Napisz zakończenie artykułu na blogu dla:\n\n" . $description. "\n\n Tytuł artykułu na blogu:\n" . $title . "\n\nTon wniosku musi być następujący:\n" . $tone_language  . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Escreva uma conclusão de artigo de blog para:\n\n" . $description. "\n\n Título do artigo do blog:\n" . $title . "\n\nTom de voz da conclusão deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Напишите вывод статьи в блоге для:\n\n" . $description . "\n\n Название статьи в блоге:\n" . $title . "\n\nТон голоса заключения должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Escribe la conclusión de un artículo de blog para:\n\n" . $description . "\n\n Título del artículo del blog:\n" . $title . "\n\nEl tono de voz de la conclusión debe ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skriv en bloggartikelavslutning för:\n\n" . $description . "\n\n Bloggartikeltitel:\n" . $title . "\n\nTonfallet för slutsatsen måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Bir blog makalesi sonucu yaz:\n\n" . $description. "\n\n Blog makalesi başlığı:\n" . $title. "\n\nSonuçtaki ses tonu şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createContentRewriterPrompt($title, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Improve and rewrite the text in a creative and smart way:\n\n" . $title . "\n\n Tone of voice of the result must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "تحسين وإعادة كتابة النص بطريقة إبداعية وذكية:\n\n". $title. "\n\nيجب أن تكون نبرة صوت النتيجة:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "以创造性和聪明的方式改进和重写文本：\n\n". $title. "\n\n 结果的语气必须是：\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Poboljšajte i prepišite tekst na kreativan i pametan način:\n\n" . $title. "\n\n Ton glasa rezultata mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Vylepšete a přepište text kreativním a chytrým způsobem:\n\n" . $title . "\n\n Tón hlasu výsledku musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Forbedre og omskriv teksten på en kreativ og smart måde:\n\n" . $title. "\n\n Tonen i resultatet skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Verbeter en herschrijf de tekst op een creatieve en slimme manier:\n\n" . $title . "\n\n Tone of voice van het resultaat moet zijn:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Täiustage ja kirjutage teksti loominguliselt ja nutikalt ümber:\n\n" . $title . "\n\n Tulemuse hääletoon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Paranna ja kirjoita tekstiä uudelleen luovalla ja älykkäällä tavalla:\n\n" . $title . "\n\n Tuloksen äänensävyn on oltava:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Améliorez et réécrivez le texte de manière créative et intelligente :\n\n" . $title . "\n\n Le ton de la voix du résultat doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Verbessern und überarbeiten Sie den Text auf kreative und intelligente Weise:\n\n" . $title . "\n\n Tonfall des Ergebnisses muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Βελτιώστε και ξαναγράψτε το κείμενο με δημιουργικό και έξυπνο τρόπο:\n\n" . $title . "\n\n Ο τόνος της φωνής του αποτελέσματος πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "שפר ושכתב את הטקסט בצורה יצירתית וחכמה:\n\n" . $title . "\n\n גוון הקול של התוצאה חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "रचनात्मक और स्मार्ट तरीके से टेक्स्ट को सुधारें और फिर से लिखें:\n\n" .$title. "\n\n परिणाम की आवाज़ का स्वर होना चाहिए:\n" .$tone_language. "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Javítsa és írja át a szöveget kreatív és okos módon:\n\n" . $title . "\n\n Az eredmény hangszínének a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Bættu og endurskrifaðu textann á skapandi og snjallan hátt:\n\n" . $title. "\n\n Röddtónn niðurstöðunnar verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Tingkatkan dan tulis ulang teks dengan cara yang kreatif dan cerdas:\n\n" . $title . "\n\n Nada suara hasil harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Migliora e riscrivi il testo in modo creativo e intelligente:\n\n" . $title . "\n\n Il tono di voce del risultato deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "創造的かつスマートな方法でテキストを改善および書き直します:\n\n" . $title. "\n\n 結果の声の調子:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "창의적이고 스마트한 방식으로 텍스트를 개선하고 다시 작성:\n\n" . $title. "\n\n 결과의 음성 톤은 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Tingkatkan dan tulis semula teks dengan cara yang kreatif dan pintar:\n\n" . $title . "\n\n Nada suara hasil carian mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Forbedre og omskriv teksten på en kreativ og smart måte:\n\n" . $title . "\n\n Tonen til resultatet må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Popraw i przepisz tekst w kreatywny i inteligentny sposób:\n\n" . $title . "\n\n Ton głosu wyniku musi być:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Melhorar e reescrever o texto de forma criativa e inteligente:\n\n" . $title . "\n\n Tom de voz do resultado deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Улучшите и перепишите текст творчески и по-умному:\n\n" . $title . "\n\n Тон голоса результата должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Mejora y reescribe el texto de forma creativa e inteligente:\n\n" . $title . "\n\n El tono de voz del resultado debe ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Förbättra och skriv om texten på ett kreativt och smart sätt:\n\n" . $title . "\n\n Tonen i resultatet måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Metni yaratıcı ve akıllı bir şekilde iyileştirin ve yeniden yazın:\n\n" . $title. "\n\n Sonucun ses tonu şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }


     /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createFacebookAdsPrompt($title, $audience, $description, $language, $tone) {
        
        if ($language != 'en-US') {
            $tone_language = $this->translateTone($tone, $language);
        } else {
            $tone_language = $tone;
        }

        switch ($language) {
            case 'en-US':
                    $prompt = "Write a creative ad for the following product to run on Facebook aimed at:\n\n" . $audience . "\n\n Product name:\n" . $title . "\n\n Product description:\n" . $description . "\n\n Tone of voice of the ad must be:\n" . $tone_language . "\n\n";
                    return $prompt;
                break;
            case 'ar-AE':
                $prompt = "اكتب إعلانًا إبداعيًا للمنتج التالي ليتم تشغيله على Facebook بهدف:\n\n". $audience. "\n\nاسم المنتج:\n". $title. "\n\nوصف المنتج:\n". $description. "\n\nيجب أن تكون نغمة صوت الإعلان:\n". $tone_language. "\n\n";
                return $prompt;
                break;
            case 'cmn-CN':
                $prompt = "为以下产品编写创意广告以在 Facebook 上投放，目标是：\n\n". $audience. "\n\n 产品名称：\n" . $title. "\n\n 产品描述：\n" . $description. "\n\n 广告语调必须是：\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hr-HR':
                $prompt = "Napišite kreativni oglas za sljedeći proizvod za prikazivanje na Facebooku s ciljem:\n\n" . $audience. "\n\n Naziv proizvoda:\n" . $title. "\n\n Opis proizvoda:\n" . $description. "\n\n Ton glasa oglasa mora biti:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'cs-CZ':
                $prompt = "Napište kreativní reklamu pro následující produkt, který se bude zobrazovat na Facebooku a je zaměřen na:\n\n" . $audience. "\n\n Název produktu:\n" . $title . "\n\n Popis produktu:\n" . $description . "\n\n Tón hlasu reklamy musí být:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'da-DK':
                $prompt = "Skriv en kreativ annonce for følgende produkt til at køre på Facebook rettet mod:\n\n" . $audience. "\n\n Produktnavn:\n" . $title. "\n\n Produktbeskrivelse:\n" . $description. "\n\n Tonen i annoncen skal være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nl-BE':
                $prompt = "Schrijf een creatieve advertentie voor het volgende product voor weergave op Facebook gericht op:\n\n" . $audience. "\n\n Productnaam:\n" . $title . "\n\n Productbeschrijving:\n" . $description. "\n\n Tone of voice van de advertentie moet zijn:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'et-EE':
                $prompt = "Kirjutage Facebookis esitamiseks järgmise toote loov reklaam, mille eesmärk on:\n\n" . $audience . "\n\n Toote nimi:\n" . $title . "\n\n Toote kirjeldus:\n" . $description . "\n\n Reklaami hääletoon peab olema:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fi-FI':
                $prompt = "Kirjoita luova mainos seuraavalle tuotteelle Facebookissa, jonka tarkoituksena on:\n\n" . $audience. "\n\n Tuotteen nimi:\n" . $title . "\n\n Tuotteen kuvaus:\n" . $description . "\n\n Mainoksen äänensävyn on oltava:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'fr-FR':
                $prompt = "Rédigez une publicité créative pour le produit suivant à diffuser sur Facebook et destinée à :\n\n" . $audience . "\n\n Nom du produit :\n" . $title . "\n\n Description du produit :\n" . $description . "\n\n Le ton de la voix de l'annonce doit être :\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'de-DE':
                $prompt = "Schreiben Sie eine kreative Anzeige für das folgende Produkt, das auf Facebook geschaltet werden soll:\n\n" . $audience . "\n\n Produktname:\n" . $title . "\n\n Produktbeschreibung:\n" . $description . "\n\n Tonfall der Anzeige muss sein:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'el-GR':
                $prompt = "Γράψτε μια δημιουργική διαφήμιση για το ακόλουθο προϊόν για προβολή στο Facebook με στόχο:\n\n" . $audience . "\n\n Όνομα προϊόντος:\n" . $title . "\n\n Περιγραφή προϊόντος:\n" . $description . "\n\n Ο τόνος της διαφήμισης πρέπει να είναι:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'he-IL':
                $prompt = "כתוב מודעה יצירתית עבור המוצר הבא שיוצג בפייסבוק שמטרתה:\n\n" . $audience . "\n\n שם המוצר:\n" . $title . "\n\n תיאור המוצר:\n" . $description. "\n\n גוון הקול של המודעה חייב להיות:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'hi-IN':
                $prompt = "Facebook पर चलाने के लिए निम्नलिखित उत्पाद के लिए एक रचनात्मक विज्ञापन लिखें:\n\n" .$audience. "\n\n उत्पाद का नाम:\n".$title. "\n\n उत्पाद विवरण:\n" . $description. "\n\n विज्ञापन का स्वर इस प्रकार होना चाहिए:\n" . $tone_language. "\n\n";
                return $prompt;
                break;
            case 'hu-HU':
                $prompt = "Írjon kreatív hirdetést a következő termékhez a Facebookon való futtatáshoz, amelynek célja:\n\n" . $audience. "\n\n Terméknév:\n" . $title . "\n\n Termékleírás:\n" . $description. "\n\n A hirdetés hangszínének a következőnek kell lennie:\n" . $tone_language . "\n\n";
                return $prompt;
                break;  
            case 'is-IS':
                $prompt = "Skrifaðu skapandi auglýsingu fyrir eftirfarandi vöru til að birta á Facebook sem miðar að:\n\n" . $audience. "\n\n Vöruheiti:\n" . $title. "\n\n Vörulýsing:\n" . $description. "\n\n Röddtónn auglýsingarinnar verður að vera:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'id-ID':
                $prompt = "Tulis iklan kreatif untuk produk berikut agar berjalan di Facebook yang ditujukan untuk:\n\n" . $audience . "\n\n Nama produk:\n" . $title . "\n\n Deskripsi produk:\n" . $description . "\n\n Nada suara iklan harus:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'it-IT':
                $prompt = "Scrivi un annuncio creativo per il seguente prodotto da pubblicare su Facebook rivolto a:\n\n" . $audience. "\n\n Nome prodotto:\n" . $title . "\n\n Descrizione del prodotto:\n" . $description . "\n\n Il tono di voce dell'annuncio deve essere:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ja-JP':
                $prompt = "次の製品のクリエイティブ広告を作成して、Facebook で実行することを目的としています:\n\n" . $audience. "\n\n 製品名:\n" . $title. "\n\n 製品説明:\n" . $description. "\n\n 広告のトーンは次のようにする必要があります:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ko-KR':
                $prompt = "Facebook에서 실행할 다음 제품에 대한 크리에이티브 광고 작성:\n\n" . $audience. "\n\n 제품 이름:\n" . $title . "\n\n 제품 설명:\n" . $description . "\n\n 광고 음성 톤은 다음과 같아야 합니다.\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ms-MY':
                $prompt = "Tulis iklan kreatif untuk produk berikut untuk disiarkan di Facebook bertujuan:\n\n" . $audience. "\n\n Nama produk:\n" . $title . "\n\n Penerangan produk:\n" . $description . "\n\n Nada suara iklan mestilah:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'nb-NO':
                $prompt = "Skriv en kreativ annonse for følgende produkt som skal kjøres på Facebook rettet mot:\n\n" . $audience . "\n\n Produktnavn:\n" . $title . "\n\n Produktbeskrivelse:\n" . $description . "\n\n Tonen i annonsen må være:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pl-PL':
                $prompt =  "Napisz kreatywną reklamę następującego produktu do wyświetlania na Facebooku, skierowaną do:\n\n" . $audience . "\n\n Nazwa produktu:\n" . $title . "\n\n Opis produktu:\n" . $description. "\n\n Ton reklamy musi być:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'pt-PT':
                $prompt = "Escreva um anúncio criativo para o seguinte produto para exibição no Facebook destinado a:\n\n" . $audience . "\n\n Nome do produto:\n" . $title . "\n\n Descrição do produto:\n" . $description. "\n\n O tom de voz do anúncio deve ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'ru-RU':
                $prompt = "Напишите креативную рекламу следующего продукта для показа на Facebook, нацеленную на:\n\n" . $audience. "\n\n Название продукта:\n" . $title. "\n\n Описание товара:\n" . $description . "\n\n Тон объявления должен быть:\n" . $tone_language . "\п\п";
                return $prompt;
                break;
            case 'es-ES':
                $prompt = "Escriba un anuncio creativo para que el siguiente producto se publique en Facebook dirigido a:\n\n" . $audience . "\n\n Nombre del producto:\n" . $title . "\n\n Descripción del producto:\n" . $description . "\n\n El tono de voz del anuncio debe ser:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'sv-SE':
                $prompt = "Skriv en kreativ annons för följande produkt som ska visas på Facebook som syftar till:\n\n" . $audience . "\n\n Produktnamn:\n" . $title . "\n\n Produktbeskrivning:\n" . $description . "\n\n Tonen i annonsen måste vara:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            case 'tr-TR':
                $prompt = "Aşağıdaki ürün için Facebook'ta yayınlanması hedeflenen yaratıcı bir reklam yazın:\n\n" . $audience. "\n\n Ürün adı:\n" . $title. "\n\n Ürün açıklaması:\n" . $description. "\n\n Reklamın ses tonu şöyle olmalıdır:\n" . $tone_language . "\n\n";
                return $prompt;
                break;
            default:
                # code...
                break;
        }

    }
 

}
