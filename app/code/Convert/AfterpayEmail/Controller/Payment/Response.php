<?php
/**
 * Magento 2 extensions for Afterpay Payment
 *
 * @author Afterpay
 * @copyright 2016-2018 Afterpay https://www.afterpay.com
 * Updated on 27th March 2018
 * Removed API V0 functionality
 */
namespace Convert\AfterpayEmail\Controller\Payment;

use Magento\Payment\Model\Method\AbstractMethod;
use Afterpay\Afterpay\Model\Payovertime;
use Afterpay\Afterpay\Model\Response as ResponseModel;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Response
 * @package Convert\AfterpayEmail\Controller\Payment
 */
class Response extends \Afterpay\Afterpay\Controller\Payment\Response
{
    /**
     * Actual action when accessing url
     */
    public function execute()
    {
        // debug mode
        $this->_helper->debug('Start \Afterpay\Afterpay\Controller\Payment\Response::execute() with request ' . $this->_jsonHelper->jsonEncode($this->getRequest()->getParams()));
        $query = $this->getRequest()->getParams();
        $order = $this->_checkoutSession->getLastRealOrder();
        $redirect = '';
        // Check if not fraud detected not doing anything (let cron update the order if payment successful)
        if ($this->_afterpayConfig->getPaymentAction() == AbstractMethod::ACTION_AUTHORIZE_CAPTURE) {
            //Steven - Bypass the response and do capture
            $redirect = $this->_processAuthCapture($query);
        } elseif (!$this->response->validCallback($order, $query)) {
            $this->_helper->debug('Request redirect url is not valid.');
        }
        // debug mode
        $this->_helper->debug('Finished \Afterpay\Afterpay\Controller\Payment\Response::execute()');
        // Redirect to cart
        $this->_redirect($redirect);
    }

    /**
     * @param $query
     * @return string
     */
    private function _processAuthCapture($query)
    {
        $redirect = self::DEFAULT_REDIRECT_PAGE;
        try {
            switch ($query['status']) {
                case ResponseModel::RESPONSE_STATUS_CANCELLED:
                    $this->messageManager->addErrorMessage(__('You have cancelled your Afterpay payment. Please select an alternative payment method.'));
                    break;
                case ResponseModel::RESPONSE_STATUS_FAILURE:
                    $this->messageManager->addErrorMessage(__('Afterpay payment failure. Please select an alternative payment method.'));
                    break;
                case ResponseModel::RESPONSE_STATUS_SUCCESS:
                    //Steven - Capture here
                    $quote = $this->_checkoutSession->getQuote();
                    $payment = $quote->getPayment();
                    $token = $payment->getAdditionalInformation(Payovertime::ADDITIONAL_INFORMATION_KEY_TOKEN);
                    $merchantOrderId = $quote->getReservedOrderId();

                    $responseCheck = $this->_tokenCheck->generate($token);
                    $responseCheck = $this->_jsonHelper->jsonDecode($responseCheck->getBody());

                    /**
                     * Validation to check between session and post request
                     */
                    if (!$responseCheck) {
                        // Check the order token being use
                        throw new LocalizedException(__('There are issues when processing your payment. Invalid Token'));
                    } elseif ($merchantOrderId != $responseCheck['merchantReference']) {
                        // Check order id
                        throw new LocalizedException(__('There are issues when processing your payment. Invalid Merchant Reference'));
                    } elseif (round($quote->getGrandTotal(), 2) != round($responseCheck['totalAmount']['amount'], 2)) {
                        // Check the order amount
                        throw new LocalizedException(__('There are issues when processing your payment. Invalid Amount'));
                    }


                    $response = $this->_directCapture->generate($token, $merchantOrderId);
                    $response = $this->_jsonHelper->jsonDecode($response->getBody());

                    if (empty($response['status'])) {
                        $response['status'] = ResponseModel::RESPONSE_STATUS_DECLINED;
                        $this->_helper->debug("Transaction Exception (Empty Response): " . json_encode($response));
                    }

                    switch ($response['status']) {
                        case ResponseModel::RESPONSE_STATUS_APPROVED:
                            $payment->setAdditionalInformation(Payovertime::ADDITIONAL_INFORMATION_KEY_ORDERID, $response['id']);

                            $this->_checkoutSession
                                ->setLastQuoteId($quote->getId())
                                ->setLastSuccessQuoteId($quote->getId())
                                ->clearHelperData();
                            // Create Order From Quote
                            $quote->collectTotals();
                            $order = $this->_quoteManagement->submit($quote);

                            if ($order) {
                                $this->_checkoutSession->setLastOrderId($order->getId())
                                    ->setLastRealOrderId($order->getIncrementId())
                                    ->setLastOrderStatus($order->getStatus());
                                $this->_createTransaction($order, $response);
                                //email sending mechanism
                                $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
                                if ($redirectUrl && $order->getCanSendNewEmailFlag()) {
                                    try {
                                        $this->_orderSender->send($order);
                                    } catch (\Exception $e) {
                                        $this->_helper->debug("Transaction Email Sending Error: " . json_encode($e));
                                    }
                                }
                                $this->messageManager->addSuccessMessage("Afterpay Transaction Completed");
                                $redirect = 'checkout/onepage/success';
                            }

                            break;
                        case ResponseModel::RESPONSE_STATUS_DECLINED:
                            $this->messageManager->addErrorMessage(__('Afterpay payment declined. Please select an alternative payment method.'));
                            break;
                        default:
                            $this->messageManager->addErrorMessage($response);
                            break;
                    }
                    break;
            }
        } catch (LocalizedException $e) {
            $this->_helper->debug("Transaction Exception: " . $e->getMessage());
            $this->messageManager->addErrorMessage(
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->_helper->debug("Transaction Exception: " . $e->getMessage());
            $this->messageManager->addErrorMessage(
                $e->getMessage()
            );
        }

        return $redirect;
    }

    /**
     * @param \Magento\Sales\Model\Order|null $order
     * @param array $paymentData
     * @return int
     */
    private function _createTransaction($order = null, $paymentData = [])
    {
        try {
            //get payment object from order object
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();
            $payment->setLastTransId($paymentData['id']);
            $payment->setTransactionId($paymentData['id']);
            $formattedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal()
            );
            $message = __('The authorized amount is %1.', $formattedPrice);
            //get the object of builder class
            $trans = $this->_transactionBuilder;
            /** @var \Magento\Sales\Model\Order\Payment\Transaction $transaction */
            $transaction = $trans->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentData['id'])
                ->setFailSafe(true)
                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

            $payment->addTransactionCommentsToOrder($transaction, $message);
            $payment->setParentTransactionId(null);
            $this->_paymentRepository->save($payment);
            $this->_orderRepository->save($order);
            $transaction = $this->_transactionRepository->save($transaction);

            return  $transaction->getTransactionId();
        } catch (\Exception $e) {
            //log errors here
            return 0;
        }
    }
}
