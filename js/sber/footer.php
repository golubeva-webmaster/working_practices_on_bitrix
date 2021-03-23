        <?// Sber credit -------------------------------------------------{ ?>

        <?// Диалоговые окна для Сбер Бизнес?>
        <div style="display:none">
            <?// удачная авторизация через Сбер Кредит?>
            <a href="javascript:void(0)" class="btn btn-default has-ripple msc-regions" data-fancybox data-src="#succes-sber-auth" id="succes-sber-auth-link">Вызов модального окна</a>
            <div id="succes-sber-auth" style="display:none" class="succes-sber-auth">
                <h2>Вы успешно авторизовались через СберБизнес</h2>
                <div class="descrip">
                    При оформлении заказа, выбирайте способ оплаты Кредит от Сбербанка. И нажимайте кнопку "Выбрать кредитные условия".
                </div>
            </div>

            <?// НЕудачная авторизация через Сбер Кредит?>
            <a href="javascript:void(0)" class="btn btn-default has-ripple msc-regions" data-fancybox data-src="#error-sber-auth" id="error-sber-auth-link">Вызов модального окна</a>
            <div id="error-sber-auth" style="display:none" class="error-sber-auth">
                <h2>Авторизация через СберБизнес не удалась</h2>
                <div class="descrip">
                    <ul>
                        <li><a href="https://www.sberbank.ru/businesscredit/partner/info?id=volma&site=http://wdgw3.sap.volma.ru:43485/dealer/">Зачем нужен аккаунт СберБизнес?</a></li>
                        <li><a href="https://www.sberbank.ru/ru/s_m_business/open-accounts">Открыть счёт в СберБизнес</a></li>
                    </ul>
                </div>
            </div>

            <?// Сообщение о поданных данных Сбер Кредит?>
            <a href="javascript:void(0)" class="btn btn-default has-ripple msc-regions" data-fancybox data-src="#sber-appl-itog" id="sber-appl-itog-link">Вызов модального окна</a>
            <div id="sber-appl-itog" style="display:none" class="sber-appl-itog">
                <h2>Вы подали заявку на кредит</h2>
                <div class="descrip">
					<ul>
						<li>После нажатия на кнопку "Оформить заказ" вы будете перемещены на страницу Сбербанка для оформления кредита.</li>
					</ul>
                </div>
            </div>


            <?// Сообщение что клиент будет перенаправлен в сбербанк для оформления кредита?>
            <a href="javascript:void(0)" class="btn btn-default has-ripple msc-regions" data-fancybox data-src="#sber-goto-sber" id="sber-goto-sber-link">Вызов модального окна</a>
            <div id="sber-goto-sber" style="display:none" class="sber-goto-sber">
                <h2>Заказ оформлен</h2>
                <div class="descrip">Сейчас вы будете направлены на страницу оформления кредита</div>
            </div>

            <?// Сообщение о необходимости авторизоваться в сбербизнес для оформления кредита?>
            <a href="javascript:void(0)" class="btn btn-default has-ripple msc-regions" data-fancybox data-src="#sber-go-auth" id="sber-go-auth-link">Вызов модального окна</a>
            <div id="sber-go-auth" style="display:none" class="sber-go-auth">
                <h2>Выбран способ оплаты "Сбербанк Кредит"</h2>
                <div class="descrip">
                    <?//mari sber Кнопка авторизации Сбербанка?>
                    <div class="buttons clearfix sber-btn-auth">
                        <button type="button" class="btn btn-sber sberbank_button_auth">Войти по СберБизнес ID</button>
                        <!--noindex--><a class="auth_link" href="https://www.sberbank.ru/businesscredit/partner/info?id=volma&site=http://wdgw3.sap.volma.ru:43485/dealer/" target="_blank">Зачем мне это надо?</a><!--/noindex-->
                        <!--noindex--><br><a class="auth_link-sber-open-acc" href="https://www.sberbank.ru/ru/s_m_business/open-accounts" target="_blank">Открыть счёт в СберБизнес</a><!--/noindex-->
                    </div>
                </div>
            </div>



        </div>

        <script src="/local/sber/sber_footer.js" type="text/javascript" charset="utf-8" defer></script>
        <?// Sber credit -------------------------------------------------} ?>
