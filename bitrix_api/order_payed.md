# Сделать заказ Оплаченным
```
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
$orderId = 771;
// используем пространство имён интернет-магазина
use Bitrix\Sale;
// int $orderId ID заказа
$order = Sale\Order::load($orderId);
$paymentCollection = $order->getPaymentCollection();
$onePayment = $paymentCollection[0];
$onePayment->setPaid("Y"); // выставляем оплату
// сохранение изменений
$order->save();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
```
