<!-- SIDE MENU BAR -->
<aside class="app-sidebar"> 
    <div class="app-sidebar__logo">
        <a class="header-brand" href="<?php echo e(url('/')); ?>">
            <img src="<?php echo e(URL::asset('img/brand/logo.png')); ?>" class="header-brand-img desktop-lgo" alt="Admintro logo">
            <img src="<?php echo e(URL::asset('img/brand/favicon.png')); ?>" class="header-brand-img mobile-logo" alt="Admintro logo">
        </a>
    </div>
    <ul class="side-menu app-sidebar3">
        <?php if(auth()->check() && auth()->user()->hasRole('admin')): ?>
            <li class="side-item side-item-category mt-4"><?php echo e(__('Admin Dashboard')); ?></li>
            <li class="slide">
                <a class="side-menu__item"  href="<?php echo e(route('admin.dashboard')); ?>">
                    <span class="side-menu__icon fa-solid fa-chart-tree-map"></span>
                    <span class="side-menu__label"><?php echo e(__('Dashboard')); ?></span>
                </a>
            </li>
            <li class="side-item side-item-category"><?php echo e(__('Admin Panel')); ?></li>
            <li class="slide">
                    <a class="side-menu__item" data-toggle="slide" href="<?php echo e(url('#')); ?>">
                        <span class="side-menu__icon fa-solid fa-microchip-ai fs-18"></span>
                        <span class="side-menu__label"><?php echo e(__('Davinci Management')); ?></span><i class="angle fa fa-angle-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="<?php echo e(route('admin.davinci.dashboard')); ?>" class="slide-item"><?php echo e(__('Davinci Dashboard')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.davinci.templates')); ?>" class="slide-item"><?php echo e(__('Davinci Templates')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.davinci.configs')); ?>" class="slide-item"><?php echo e(__('Davinci Settings')); ?></a></li>
                    </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="<?php echo e(url('#')); ?>">
                    <span class="side-menu__icon fa-solid fa-id-badge"></span>
                    <span class="side-menu__label"><?php echo e(__('User Management')); ?></span><i class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li><a href="<?php echo e(route('admin.user.dashboard')); ?>" class="slide-item"><?php echo e(__('User Dashboard')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.user.list')); ?>" class="slide-item"><?php echo e(__('User List')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.user.activity')); ?>" class="slide-item"><?php echo e(__('Activity Monitoring')); ?></a></li>
                    </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="<?php echo e(url('#')); ?>">
                    <span class="side-menu__icon fa-solid fa-sack-dollar"></span>
                    <span class="side-menu__label"><?php echo e(__('Finance Management')); ?></span>
                    <?php if(auth()->user()->unreadNotifications->where('type', 'App\Notifications\PayoutRequestNotification')->count()): ?>
                        <span class="badge badge-warning"><?php echo e(auth()->user()->unreadNotifications->where('type', 'App\Notifications\PayoutRequestNotification')->count()); ?></span>
                    <?php else: ?>
                        <i class="angle fa fa-angle-right"></i>
                    <?php endif; ?>
                </a>
                    <ul class="slide-menu">
                        <li><a href="<?php echo e(route('admin.finance.dashboard')); ?>" class="slide-item"><?php echo e(__('Finance Dashboard')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.finance.transactions')); ?>" class="slide-item"><?php echo e(__('Transactions')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.finance.plans')); ?>" class="slide-item"><?php echo e(__('Subscription Plans')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.finance.prepaid')); ?>" class="slide-item"><?php echo e(__('Prepaid Plans')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.finance.subscriptions')); ?>" class="slide-item"><?php echo e(__('Subscribers')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.referral.settings')); ?>" class="slide-item"><?php echo e(__('Referral System')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.referral.payouts')); ?>" class="slide-item"><?php echo e(__('Referral Payouts')); ?>

                                <?php if((auth()->user()->unreadNotifications->where('type', 'App\Notifications\PayoutRequestNotification')->count())): ?>
                                    <span class="badge badge-warning ml-5"><?php echo e(auth()->user()->unreadNotifications->where('type', 'App\Notifications\PayoutRequestNotification')->count()); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><a href="<?php echo e(route('admin.settings.invoice')); ?>" class="slide-item"><?php echo e(__('Invoice Settings')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.finance.settings')); ?>" class="slide-item"><?php echo e(__('Payment Settings')); ?></a></li>
                    </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item"  href="<?php echo e(route('admin.support')); ?>">
                    <span class="side-menu__icon fa-solid fa-message-question"></span>
                    <span class="side-menu__label"><?php echo e(__('Support Requests')); ?></span>
                    <?php if(App\Models\SupportTicket::where('status', 'Open')->count()): ?>
                        <span class="badge badge-warning"><?php echo e(App\Models\SupportTicket::where('status', 'Open')->count()); ?></span>
                    <?php endif; ?> 
                </a>
            </li>
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="<?php echo e(url('#')); ?>">
                    <span class="side-menu__icon fa-solid fa-message-exclamation"></span>
                    <span class="side-menu__label"><?php echo e(__('Notifications')); ?></span>
                        <?php if(auth()->user()->unreadNotifications->where('type', '<>', 'App\Notifications\GeneralNotification')->count()): ?>
                            <span class="badge badge-warning" id="total-notifications-a"></span>
                        <?php else: ?>
                            <i class="angle fa fa-angle-right"></i>
                        <?php endif; ?>
                    </a>                     
                    <ul class="slide-menu">
                        <li><a href="<?php echo e(route('admin.notifications')); ?>" class="slide-item"><?php echo e(__('Mass Notifications')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.notifications.system')); ?>" class="slide-item"><?php echo e(__('System Notifications')); ?> 
                                <?php if((auth()->user()->unreadNotifications->where('type', '<>', 'App\Notifications\GeneralNotification')->count())): ?>
                                    <span class="badge badge-warning ml-5" id="total-notifications-b"></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="<?php echo e(url('#')); ?>">
                    <span class="side-menu__icon fa-solid fa-earth-americas"></span>
                    <span class="side-menu__label"><?php echo e(__('Frontend Management')); ?></span><i class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li><a href="<?php echo e(route('admin.settings.frontend')); ?>" class="slide-item"><?php echo e(__('Frontend Settings')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.settings.appearance')); ?>" class="slide-item"><?php echo e(__('SEO & Logos')); ?></a></li>                        
                        <li><a href="<?php echo e(route('admin.settings.blog')); ?>" class="slide-item"><?php echo e(__('Blogs Manager')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.settings.faq')); ?>" class="slide-item"><?php echo e(__('FAQs Manager')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.settings.review')); ?>" class="slide-item"><?php echo e(__('Reviews Manager')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.settings.terms')); ?>" class="slide-item"><?php echo e(__('Pages Manager')); ?></a></li>                           
                        <li><a href="<?php echo e(route('admin.settings.adsense')); ?>" class="slide-item"><?php echo e(__('Google Adsense')); ?></a></li>                           
                    </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="<?php echo e(url('#')); ?>">
                    <span class="side-menu__icon fa fa-sliders"></span>
                    <span class="side-menu__label"><?php echo e(__('General Settings')); ?></span><i class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li><a href="<?php echo e(route('admin.settings.global')); ?>" class="slide-item"><?php echo e(__('Global Settings')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.settings.oauth')); ?>" class="slide-item"><?php echo e(__('Auth Settings')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.settings.registration')); ?>" class="slide-item"><?php echo e(__('Registration Settings')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.settings.smtp')); ?>" class="slide-item"><?php echo e(__('SMTP Settings')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.settings.backup')); ?>" class="slide-item"><?php echo e(__('Database Backup')); ?></a></li>
                        <li><a href="<?php echo e(route('admin.settings.activation')); ?>" class="slide-item"><?php echo e(__('Activation')); ?></a></li>     
                        <li><a href="<?php echo e(route('admin.settings.upgrade')); ?>" class="slide-item"><?php echo e(__('Upgrade Software')); ?></a></li>                 
                    </ul>
            </li>
        <?php endif; ?>
        <?php if(auth()->check() && auth()->user()->hasRole('admin')): ?>
            <li class="side-item side-item-category"><?php echo e(__('My Account')); ?></li>
        <?php endif; ?>
        <?php if(auth()->check() && auth()->user()->hasRole('user|subscriber')): ?>
            <li class="side-item side-item-category mt-4"><?php echo e(__('My Account')); ?></li>
        <?php endif; ?>
        <li class="slide">
            <a class="side-menu__item" href="<?php echo e(route('user.profile')); ?>">
            <span class="side-menu__icon fa-solid fa-id-badge"></span>
            <span class="side-menu__label"><?php echo e(__('My Profile')); ?></span></a>
        </li> 
        <li class="slide">
            <a class="side-menu__item" href="<?php echo e(route('user.plans')); ?>">
            <span class="side-menu__icon lead-3 fa-solid fa-box-circle-check"></span>
            <span class="side-menu__label"><?php echo e(__('Pricing Plans')); ?></span></a>
        </li>        
        <li class="slide">
            <a class="side-menu__item" href="<?php echo e(route('user.purchases')); ?>">
            <span class="side-menu__icon lead-3 fa-solid fa-money-check-pen"></span>
            <span class="side-menu__label"><?php echo e(__('Purchase History')); ?></span></a>
        </li> 
        <li class="slide">
            <a class="side-menu__item" href="<?php echo e(route('user.referral')); ?>">
            <span class="side-menu__icon lead-3 fa-solid fa-badge-dollar"></span>
            <span class="side-menu__label"><?php echo e(__('Affiliate Program')); ?></span></a>
        </li> 
        <?php if(auth()->check() && auth()->user()->hasRole('user|subscriber')): ?>
            <?php if(config('settings.user_support') == 'enabled'): ?>
                <li class="slide">
                    <a class="side-menu__item" href="<?php echo e(route('user.support')); ?>">
                        <span class="side-menu__icon fa-solid fa-messages-question"></span>
                        <span class="side-menu__label"><?php echo e(__('Support Requests')); ?></span>
                    </a>
                </li>
            <?php endif; ?>        
            <?php if(config('settings.user_notification') == 'enabled'): ?>
                <li class="slide">
                    <a class="side-menu__item" href="<?php echo e(route('user.notifications')); ?>">
                        <span class="side-menu__icon fa-solid fa-message-exclamation"></span>
                        <span class="side-menu__label"><?php echo e(__('Notifications')); ?></span>
                        <?php if(auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification')->count()): ?>
                            <span class="badge badge-warning"><?php echo e(auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification')->count()); ?></span>
                        <?php endif; ?>                
                    </a>
                </li>
            <?php endif; ?> 
        <?php endif; ?>
        <?php if(auth()->check() && auth()->user()->hasRole('admin')): ?>
            <li class="slide">
                <a class="side-menu__item" href="<?php echo e(route('user.support')); ?>">
                    <span class="side-menu__icon fa-solid fa-messages-question"></span>
                    <span class="side-menu__label"><?php echo e(__('Support Request')); ?></span>
                </a>
            </li>    
            <li class="slide">
                <a class="side-menu__item" href="<?php echo e(route('user.notifications')); ?>">
                    <span class="side-menu__icon fa-solid fa-message-exclamation"></span>
                    <span class="side-menu__label"><?php echo e(__('Notifications')); ?></span>
                    <?php if(auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification')->count()): ?>
                        <span class="badge badge-warning"><?php echo e(auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification')->count()); ?></span>
                    <?php endif; ?>                
                </a>
            </li>
        <?php endif; ?>
        <li class="side-item side-item-category mt-4"><?php echo e(__('AI Panel')); ?></li>
        <li class="slide">
            <a class="side-menu__item" href="<?php echo e(route('user.dashboard')); ?>">
            <span class="side-menu__icon lead-3 fa-solid fa-chart-tree-map"></span>
            <span class="side-menu__label"><?php echo e(__('Dashboard')); ?></span></a>
        </li> 
        <li class="slide">
            <a class="side-menu__item" href="<?php echo e(route('user.templates')); ?>">
            <span class="side-menu__icon lead-3 fs-18 fa-solid fa-microchip-ai"></span>
            <span class="side-menu__label"><?php echo e(__('Templates')); ?></span></a>
        </li> 
        <li class="slide">
            <a class="side-menu__item" data-toggle="slide" href="<?php echo e(url('#')); ?>">
                <span class="side-menu__icon fa-solid fa-folder-bookmark"></span>
                <span class="side-menu__label"><?php echo e(__('My Documents')); ?></span><i class="angle fa fa-angle-right"></i></a>
                <ul class="slide-menu">
                    <li><a href="<?php echo e(route('user.documents')); ?>" class="slide-item"><?php echo e(__('All Documents')); ?></a></li>
                    <li><a href="<?php echo e(route('user.documents.images')); ?>" class="slide-item"><?php echo e(__('All Images')); ?></a></li>  
                    <li><a href="<?php echo e(route('user.workbooks')); ?>" class="slide-item"><?php echo e(__('Workbooks')); ?></a></li>                    
                </ul>
        </li>
        <?php if(auth()->check() && auth()->user()->hasRole('user')): ?>
            <?php if(config('settings.image_feature_user') == 'allow'): ?>
                <li class="slide">
                    <a class="side-menu__item" href="<?php echo e(route('user.images')); ?>">
                    <span class="side-menu__icon lead-3 fa-solid fa-camera-viewfinder"></span>
                    <span class="side-menu__label"><?php echo e(__('AI Images')); ?></span></a>
                </li> 
            <?php endif; ?>
        <?php endif; ?>
        <?php if(auth()->check() && auth()->user()->hasRole('admin|subscriber')): ?>
            <li class="slide">
                <a class="side-menu__item" href="<?php echo e(route('user.images')); ?>">
                <span class="side-menu__icon lead-3 fa-solid fa-camera-viewfinder"></span>
                <span class="side-menu__label"><?php echo e(__('AI Images')); ?></span></a>
            </li>
        <?php endif; ?> 
        
    </ul>
    <div class="aside-progress-position">
        <div class="inline-flex w-100">
            <div class="flex w-100">
                <span class="fs-10 font-weight-bold pl-5"><i class="fa-solid fa-feather text-yellow mr-2"></i><?php echo e(__('Available Words')); ?>: <span class="text-primary" id="available-words"><?php echo e(App\Services\HelperService::getTotalWords()); ?></span> </span>
            </div>
            <div class="flex w-100 mb-4">
                <span class="fs-10 font-weight-bold pl-5"><i class="fa-solid fa-palette text-yellow mr-2"></i><?php echo e(__('Available Images')); ?>: <span class="text-primary" id="available-images"><?php echo e(App\Services\HelperService::getTotalImages()); ?></span> </span>
            </div>            
        </div>
    </div>
</aside>
<!-- END SIDE MENU BAR --><?php /**PATH E:\workspace\openaidavinci-1.0\resources\views/layouts/nav-aside.blade.php ENDPATH**/ ?>