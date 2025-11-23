<?php
require_once 'config/config.php';
$pageTitle = 'Часто задаваемые вопросы';
include 'includes/header.php';
?>

<h1 class="mb-4 fade-in-on-scroll">
    <i class="fas fa-question-circle text-primary me-2"></i>Часто задаваемые вопросы
</h1>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="accordion" id="faqAccordion">
            
            <!-- Вопрос 1 -->
            <div class="accordion-item fade-in-on-scroll mb-3">
                <h2 class="accordion-header" id="faq1">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        Какие часы работы фитнес-центра?
                    </button>
                </h2>
                <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="faq1" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Наш фитнес-центр работает ежедневно с <strong>06:00 до 23:00</strong>. В праздничные дни часы работы могут быть изменены - информацию уточняйте заранее по телефону или на нашем сайте.</p>
                        <p class="mb-0"><i class="fas fa-phone me-2 text-primary"></i>Телефон: +7 (999) 123-45-67</p>
                    </div>
                </div>
            </div>

            <!-- Вопрос 2 -->
            <div class="accordion-item fade-in-on-scroll mb-3">
                <h2 class="accordion-header" id="faq2">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                        <i class="fas fa-id-card me-2 text-primary"></i>
                        Какие виды абонементов доступны?
                    </button>
                </h2>
                <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Мы предлагаем различные типы абонементов:</p>
                        <ul class="mb-3">
                            <li><strong>Разовые посещения</strong> - для тех, кто хочет попробовать</li>
                            <li><strong>Месячные абонементы</strong> - доступ на месяц с определенным количеством посещений</li>
                            <li><strong>Годовые абонементы</strong> - самые выгодные предложения</li>
                            <li><strong>Безлимитные абонементы</strong> - неограниченное количество посещений</li>
                        </ul>
                        <p class="mb-0">Подробную информацию о всех тарифах вы можете найти в разделе <a href="subscriptions.php">"Абонементы"</a>.</p>
                    </div>
                </div>
            </div>

            <!-- Вопрос 3 -->
            <div class="accordion-item fade-in-on-scroll mb-3">
                <h2 class="accordion-header" id="faq3">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                        <i class="fas fa-user-tie me-2 text-primary"></i>
                        Можно ли заниматься с персональным тренером?
                    </button>
                </h2>
                <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Да, конечно! У нас работают профессиональные тренеры с многолетним опытом. Вы можете выбрать персонального тренера, который подходит именно вам.</p>
                        <p>Персональные тренировки помогут:</p>
                        <ul>
                            <li>Достичь ваших целей быстрее и безопаснее</li>
                            <li>Изучить правильную технику выполнения упражнений</li>
                            <li>Составить индивидуальную программу тренировок</li>
                            <li>Скорректировать питание</li>
                        </ul>
                        <p class="mb-0">Записаться на персональную тренировку можно через раздел <a href="trainers.php">"Тренеры"</a> или у администратора клуба.</p>
                    </div>
                </div>
            </div>

            <!-- Вопрос 4 -->
            <div class="accordion-item fade-in-on-scroll mb-3">
                <h2 class="accordion-header" id="faq4">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                        <i class="fas fa-users me-2 text-primary"></i>
                        Какие групповые занятия проводятся?
                    </button>
                </h2>
                <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="faq4" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Мы проводим различные групповые занятия для всех уровней подготовки:</p>
                        <ul>
                            <li><strong>Йога</strong> - для развития гибкости и расслабления</li>
                            <li><strong>Пилатес</strong> - для укрепления мышц кора</li>
                            <li><strong>Функциональный тренинг</strong> - для общей физической подготовки</li>
                            <li><strong>Аэробика</strong> - для кардиотренировок</li>
                            <li><strong>Стретчинг</strong> - для улучшения гибкости</li>
                            <li><strong>Силовые тренировки</strong> - для набора мышечной массы</li>
                        </ul>
                        <p class="mb-0">Расписание групповых занятий доступно в разделе <a href="services.php">"Услуги"</a> или у администратора клуба.</p>
                    </div>
                </div>
            </div>

            <!-- Вопрос 5 -->
            <div class="accordion-item fade-in-on-scroll mb-3">
                <h2 class="accordion-header" id="faq5">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                        <i class="fas fa-shower me-2 text-primary"></i>
                        Какие удобства предоставляются?
                    </button>
                </h2>
                <div id="collapse5" class="accordion-collapse collapse" aria-labelledby="faq5" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Для вашего комфорта мы предоставляем:</p>
                        <ul>
                            <li><strong>Раздевалки</strong> - просторные с индивидуальными шкафчиками</li>
                            <li><strong>Душевые</strong> - современные с горячей водой</li>
                            <li><strong>Сауна</strong> - для восстановления после тренировок</li>
                            <li><strong>Зона отдыха</strong> - комфортное место для отдыха</li>
                            <li><strong>Точка питания</strong> - полезные снэки и напитки</li>
                            <li><strong>Wi-Fi</strong> - бесплатный доступ к интернету</li>
                            <li><strong>Парковка</strong> - бесплатная парковка для посетителей</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Вопрос 6 -->
            <div class="accordion-item fade-in-on-scroll mb-3">
                <h2 class="accordion-header" id="faq6">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                        <i class="fas fa-dollar-sign me-2 text-primary"></i>
                        Как оплатить абонемент?
                    </button>
                </h2>
                <div id="collapse6" class="accordion-collapse collapse" aria-labelledby="faq6" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Оплатить абонемент можно несколькими способами:</p>
                        <ul>
                            <li><strong>Онлайн</strong> - через наш сайт банковской картой</li>
                            <li><strong>В клубе</strong> - наличными или банковской картой</li>
                            <li><strong>Банковским переводом</strong> - для юридических лиц</li>
                        </ul>
                        <p>Также доступна рассрочка на годовые абонементы. Подробности уточняйте у администратора.</p>
                    </div>
                </div>
            </div>

            <!-- Вопрос 7 -->
            <div class="accordion-item fade-in-on-scroll mb-3">
                <h2 class="accordion-header" id="faq7">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                        <i class="fas fa-ban me-2 text-primary"></i>
                        Можно ли заморозить абонемент?
                    </button>
                </h2>
                <div id="collapse7" class="accordion-collapse collapse" aria-labelledby="faq7" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Да, вы можете заморозить абонемент в следующих случаях:</p>
                        <ul>
                            <li><strong>Медицинские показания</strong> - по справке от врача (до 30 дней)</li>
                            <li><strong>Командировка</strong> - при предъявлении документов (до 14 дней)</li>
                            <li><strong>Отпуск</strong> - при предварительном уведомлении (до 14 дней)</li>
                        </ul>
                        <p class="mb-0">Для заморозки абонемента необходимо заранее обратиться к администратору клуба с соответствующими документами.</p>
                    </div>
                </div>
            </div>

            <!-- Вопрос 8 -->
            <div class="accordion-item fade-in-on-scroll mb-3">
                <h2 class="accordion-header" id="faq8">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
                        <i class="fas fa-calendar-check me-2 text-primary"></i>
                        Нужна ли предварительная запись на групповые занятия?
                    </button>
                </h2>
                <div id="collapse8" class="accordion-collapse collapse" aria-labelledby="faq8" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Предварительная запись рекомендуется, особенно на популярные занятия, чтобы гарантировать себе место в группе.</p>
                        <p>Записаться можно:</p>
                        <ul>
                            <li>Через наш сайт в разделе "Услуги"</li>
                            <li>По телефону: +7 (999) 123-45-67</li>
                            <li>Лично у администратора клуба</li>
                        </ul>
                        <p class="mb-0">Запись открывается за 7 дней до начала занятия.</p>
                    </div>
                </div>
            </div>

            <!-- Вопрос 9 -->
            <div class="accordion-item fade-in-on-scroll mb-3">
                <h2 class="accordion-header" id="faq9">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9" aria-expanded="false" aria-controls="collapse9">
                        <i class="fas fa-child me-2 text-primary"></i>
                        Есть ли программы для детей?
                    </button>
                </h2>
                <div id="collapse9" class="accordion-collapse collapse" aria-labelledby="faq9" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Да, мы предлагаем специальные программы для детей:</p>
                        <ul>
                            <li><strong>Детская йога</strong> - для детей от 5 лет</li>
                            <li><strong>Общая физическая подготовка</strong> - для детей от 7 лет</li>
                            <li><strong>Спортивная гимнастика</strong> - для детей от 6 лет</li>
                            <li><strong>Детский фитнес</strong> - игровые тренировки для детей от 4 лет</li>
                        </ul>
                        <p class="mb-0">Все занятия проводятся опытными тренерами с учетом возрастных особенностей детей. Запись и дополнительная информация у администратора клуба.</p>
                    </div>
                </div>
            </div>

            <!-- Вопрос 10 -->
            <div class="accordion-item fade-in-on-scroll mb-3">
                <h2 class="accordion-header" id="faq10">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse10" aria-expanded="false" aria-controls="collapse10">
                        <i class="fas fa-undo me-2 text-primary"></i>
                        Можно ли вернуть абонемент?
                    </button>
                </h2>
                <div id="collapse10" class="accordion-collapse collapse" aria-labelledby="faq10" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Возврат абонемента возможен в следующих случаях:</p>
                        <ul>
                            <li><strong>В течение 14 дней</strong> с момента покупки - полный возврат средств (если абонемент не использовался)</li>
                            <li><strong>При медицинских противопоказаниях</strong> - возврат пропорционально неиспользованным дням</li>
                            <li><strong>Переезд</strong> - возврат пропорционально неиспользованным дням при предъявлении документов</li>
                        </ul>
                        <p class="mb-0">Для возврата необходимо обратиться к администратору клуба с заявлением и соответствующими документами (при необходимости).</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Дополнительная информация -->
        <div class="card mt-5 fade-in-on-scroll">
            <div class="card-body p-4 text-center">
                <h3 class="mb-3">
                    <i class="fas fa-headset me-2 text-primary"></i>Не нашли ответ на свой вопрос?
                </h3>
                <p class="mb-3">Наша команда всегда готова помочь вам!</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="tel:+79991234567" class="btn btn-primary">
                        <i class="fas fa-phone me-2"></i>+7 (999) 123-45-67
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#contactModal">
                        <i class="fas fa-envelope me-2"></i>Написать нам
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>


<?php include 'includes/footer.php'; ?>

