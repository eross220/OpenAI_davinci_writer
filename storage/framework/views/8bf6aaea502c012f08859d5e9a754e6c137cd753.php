<!-- TOP MENU BAR -->
<div class="app-header header">
    <div class="container-fluid"> 
        <div class="d-flex">
            <a class="header-brand" href="<?php echo e(url('/')); ?>">
                <img src="<?php echo e(URL::asset('img/brand/logo.png')); ?>" class="header-brand-img desktop-lgo" alt="Polly logo">
                <img src="<?php echo e(URL::asset('img/brand/favicon.png')); ?>" class="header-brand-img mobile-logo" alt="Polly logo">
            </a>
            <div class="app-sidebar__toggle nav-link icon" data-toggle="sidebar">
                <a class="open-toggle" href="<?php echo e(url('#')); ?>">
                    <span class="fa fa-align-left header-icon"></span>
                </a>
            </div>
            <!-- SEARCH BAR -->
            <div id="search-bar">                
                <div>
                    <a class="nav-link icon">
                        <form id="search-field" action="<?php echo e(route('search')); ?>" method="POST" enctype="multipart/form-data">         
                            <?php echo csrf_field(); ?>                   
                            <input type="search" name='keyword'>
                        </form>                        
                    </a>
                </div>                
            </div>
            <!-- END SEARCH BAR -->
            <!-- MENU BAR -->
            <div class="d-flex order-lg-2 ml-auto"> 
                <div class="dropdown header-notify">
                    <a class="nav-link icon" data-bs-toggle="dropdown">                        
                        <?php if(auth()->check() && auth()->user()->hasRole('admin')): ?>
                            <span class="header-icon fa-regular fa-bell pr-3"></span>
                            <?php if(auth()->user()->unreadNotifications->where('type', '<>', 'App\Notifications\GeneralNotification')->count()): ?>
                                <span class="pulse "></span>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if(auth()->check() && auth()->user()->hasRole('user|subscriber')): ?>
                            <?php if(config('settings.user_notification') == 'enabled'): ?>
                                <span class="header-icon fa-solid fa-bell pr-3"></span>                            
                                    <?php if(auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification')->count()): ?>
                                        <span class="pulse "></span>
                                    <?php endif; ?>                            
                            <?php endif; ?>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow  animated">
                        <?php if(auth()->check() && auth()->user()->hasRole('admin')): ?>
                            <?php if(auth()->user()->unreadNotifications->where('type', '<>', 'App\Notifications\GeneralNotification')->count()): ?>
                                <div class="dropdown-header">
                                    <h6 class="mb-0 fs-12 font-weight-bold"><span id="total-notifications"></span> <span class="text-primary"><?php echo e(__('New')); ?></span> <?php echo e(__('Notification(s)')); ?></h6>
                                    <a href="#" class="mb-1 badge badge-primary ml-auto pl-3 pr-3 mark-read" id="mark-all"><?php echo e(__('Mark All Read')); ?></a>
                                </div>
                                <div class="notify-menu">
                                    <div class="notify-menu-inner">
                                        <?php $__currentLoopData = auth()->user()->unreadNotifications->where('type', '<>', 'App\Notifications\GeneralNotification'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="d-flex dropdown-item border-bottom pl-4 pr-4">
                                                <?php if($notification->data['type'] == 'new-user'): ?>                                                
                                                    <div>
                                                        <a href="<?php echo e(route('admin.notifications.systemShow', [$notification->id])); ?>" class="d-flex">
                                                            <div class="notifyimg bg-info-transparent text-info"> <i class="fa-solid fa-user-check fs-18"></i></div>
                                                            <div class="mr-6">
                                                                <div class="font-weight-bold fs-12"><?php echo e(__('New User Registered')); ?></div>
                                                                <div class="text-muted fs-10"><?php echo e(__('Name')); ?>: <?php echo e($notification->data['name']); ?></div>
                                                                <div class="small text-muted fs-10"><?php echo e($notification->created_at->diffForHumans()); ?></div>
                                                            </div>                                            
                                                        </a>
                                                    </div>
                                                    <div>
                                                        <a href="#" class="badge badge-primary mark-read mark-as-read" data-id="<?php echo e($notification->id); ?>"><?php echo e(__('Mark as Read')); ?></a>
                                                    </div>
                                                <?php endif; ?>  
                                                <?php if($notification->data['type'] == 'new-payment'): ?>                                                
                                                    <div>
                                                        <a href="<?php echo e(route('admin.notifications.systemShow', [$notification->id])); ?>" class="d-flex">
                                                            <div class="notifyimg bg-info-green"> <i class="fa-solid fa-sack-dollar leading-loose"></i></div>
                                                            <div class="mr-4">
                                                                <div class="font-weight-bold fs-12"><?php echo e(__('New User Payment')); ?></div>
                                                                <div class="text-muted fs-10"><?php echo e(__('From')); ?>: <?php echo e($notification->data['name']); ?></div>
                                                                <div class="small text-muted fs-10"><?php echo e($notification->created_at->diffForHumans()); ?></div>
                                                            </div>                                            
                                                        </a>
                                                    </div>
                                                    <div class="text-right">
                                                        <a href="#" class="badge badge-primary mark-read mark-as-read ml-5" data-id="<?php echo e($notification->id); ?>"><?php echo e(__('Mark as Read')); ?></a>
                                                    </div>
                                                <?php endif; ?>  
                                                <?php if($notification->data['type'] == 'payout-request'): ?>                                                
                                                    <div>
                                                        <a href="<?php echo e(route('admin.notifications.systemShow', [$notification->id])); ?>" class="d-flex">
                                                            <div class="notifyimg bg-info-green"> <i class="fa-solid fa-face-tongue-money fs-20 leading-loose"></i></div>
                                                            <div class="mr-4">
                                                                <div class="font-weight-bold fs-12"><?php echo e(__('New Payout Request')); ?></div>
                                                                <div class="text-muted fs-10"><?php echo e(__('From')); ?>: <?php echo e($notification->data['name']); ?></div>
                                                                <div class="small text-muted fs-10"><?php echo e($notification->created_at->diffForHumans()); ?></div>
                                                            </div>                                            
                                                        </a>
                                                    </div>
                                                    <div class="text-right">
                                                        <a href="#" class="badge badge-primary mark-read mark-as-read ml-5" data-id="<?php echo e($notification->id); ?>"><?php echo e(__('Mark as Read')); ?></a>
                                                    </div>
                                                <?php endif; ?>                                                
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>  
                                    </div>                              
                                </div>
                                <div class="view-all-button text-center">                            
                                    <a href="<?php echo e(route('admin.notifications.system')); ?>" class="fs-12 font-weight-bold"><?php echo e(__('View All Notifications')); ?></a>
                                </div>                            
                            <?php else: ?>
                                <div class="view-all-button text-center">
                                    <h6 class=" fs-12 font-weight-bold mb-1"><?php echo e(__('There are no new notifications')); ?></h6>                                    
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if(config('settings.user_notification') == 'enabled'): ?>
                            <?php if(auth()->check() && auth()->user()->hasRole('user|subscriber')): ?>
                                <?php if(auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification')->count()): ?>
                                    <div class="dropdown-header">
                                        <h6 class="mb-0 fs-12 font-weight-bold"><?php echo e(auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification')->count()); ?> <span class="text-primary">New</span> Notification(s)</h6>
                                        <a href="#" class="mb-1 badge badge-primary ml-auto pl-3 pr-3 mark-read" id="mark-all"><?php echo e(__('Mark All Read')); ?></a>
                                    </div>
                                    <div class="notify-menu">
                                        <div class="notify-menu-inner">
                                            <?php $__currentLoopData = auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="dropdown-item border-bottom pl-4 pr-4">
                                                    <div>
                                                        <a href="<?php echo e(route('user.notifications.show', [$notification->id])); ?>" class="d-flex">
                                                            <div class="notifyimg bg-info-transparent text-info"> <i class="fa fa-bell fs-18"></i></div>
                                                            <div>
                                                                <div class="font-weight-bold fs-12 mt-2"><?php echo e(__('New')); ?> <?php echo e($notification->data['type']); ?> <?php echo e(__('Notification')); ?></div>
                                                                <div class="small text-muted fs-10"><?php echo e($notification->created_at->diffForHumans()); ?></div>
                                                            </div>                                            
                                                        </a>
                                                    </div>                                            
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                                
                                        </div>
                                    </div>
                                    <div class="view-all-button text-center">                            
                                        <a href="<?php echo e(route('user.notifications')); ?>" class="fs-12 font-weight-bold"><?php echo e(__('View All Notifications')); ?></a>
                                    </div>                             
                                <?php else: ?>
                                    <div class="view-all-button text-center">
                                        <h6 class=" fs-12 font-weight-bold mb-1"><?php echo e(__('There are no new notifications')); ?></h6>                                    
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>                        
                    </div>
                </div>
                <div class="dropdown header-expand" >
                    <a  class="nav-link icon" id="fullscreen-button">
                        <span class="header-icon fa-solid fa-expand" id="fullscreen-icon"></span>
                    </a>
                </div>
                <div class="dropdown header-locale">
                    <a class="nav-link icon" data-bs-toggle="dropdown">
                        <span class="header-icon flag flag-<?php echo e(Config::get('locale')[App::getLocale()]['flag']); ?> pr-1"></span><span class="header-text fs-13 pr-5"><?php echo e(Config::get('locale')[App::getLocale()]['code']); ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow animated">
                        <div class="local-menu">
                            <?php $__currentLoopData = Config::get('locale'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($lang != App::getLocale()): ?>
                                    <a href="<?php echo e(route('locale', $lang)); ?>" class="dropdown-item d-flex pl-4">
                                        <div class="text-info"><i class="flag flag-<?php echo e($language['flag']); ?> mr-4"></i></div>
                                        <div>
                                            <span class="font-weight-normal fs-12"><?php echo e($language['display']); ?></span>
                                        </div>
                                    </a>                                        
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>                
                <div class="dropdown profile-dropdown">
                    <a href="#" class="nav-link" data-bs-toggle="dropdown">
                        <span class="float-right">
                            <img src="<?php if(auth()->user()->profile_photo_path): ?><?php echo e(asset(auth()->user()->profile_photo_path)); ?> <?php else: ?> <?php echo e(URL::asset('img/users/avatar.jpg')); ?> <?php endif; ?>" alt="img" class="avatar avatar-md">
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow animated">
                        <div class="text-center pt-2">
                            <span class="text-center user fs-12 pb-0 font-weight-bold"><?php echo e(Auth::user()->name); ?></span><br>
                            <span class="text-center fs-12 text-muted"><?php echo e(Auth::user()->job_role); ?></span>
                            <div class="dropdown-divider mt-3"></div>
                        </div>
                        <a class="dropdown-item d-flex" href="<?php echo e(route('user.templates')); ?>">
                            <span class="profile-icon fa-solid fa-microchip-ai"></span>
                            <div class="fs-12"><?php echo e(__('Templates')); ?></div>
                        </a>
                        <a class="dropdown-item d-flex" href="<?php echo e(route('user.workbooks')); ?>">
                            <span class="profile-icon fa-solid fa-folder-bookmark"></span>
                            <div class="fs-12"><?php echo e(__('Workbooks')); ?></div>
                        </a>
                        <a class="dropdown-item d-flex" href="<?php echo e(route('user.profile')); ?>">
                            <span class="profile-icon fa-solid fa-user-shield"></span>
                            <div class="fs-12"><?php echo e(__('My Profile')); ?></div>
                        </a>
                        <a class="dropdown-item d-flex" href="<?php echo e(route('user.security')); ?>">
                            <span class="profile-icon fa-solid fa-lock-hashtag"></span>
                            <div class="fs-12"><?php echo e(__('Change Password')); ?></div>
                        </a>
                        <a class="dropdown-item d-flex" href="<?php echo e(route('logout')); ?>" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();"> 
                            <span class="profile-icon fa-solid fa-right-from-bracket"></span>          
                            <div class="fs-12"><?php echo e(__('Logout')); ?></div>                            
                        </a>
                        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                            <?php echo csrf_field(); ?>
                        </form>
                    </div>
                </div>
            </div>
            <!-- END MENU BAR -->
        </div>
    </div>
</div>
<!-- END TOP MENU BAR -->
<?php /**PATH E:\workspace\openaidavinci-1.0\resources\views/layouts/nav-top.blade.php ENDPATH**/ ?>