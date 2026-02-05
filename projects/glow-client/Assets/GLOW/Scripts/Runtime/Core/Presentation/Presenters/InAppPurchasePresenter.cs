using System;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Exceptions;
using GLOW.Modules.MessageView.Presentation;
using UnityHTTPLibrary;
using Wonderplanet.IAP.Exception;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Core.Presentation.Presenters
{
    public class InAppPurchasePresenter : IInAppPurchaseExecuteDelegate
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        const string Title = "購入できません";

        void IInAppPurchaseExecuteDelegate.ExecutePurchase(CancellationToken cancellationToken, Func<CancellationToken, UniTask> purchaseTask)
        {
            DoAsync.Invoke(cancellationToken, ScreenInteractionControl, async ct =>
            {
                try
                {
                    await purchaseTask(ct);
                }
                // クライアントエラー
                catch (StoreProductNotFoundException)
                {
                    ShowErrorMessage("商品が見つかりませんでした。");
                }
                catch (MstNotFoundException)
                {
                    ShowErrorMessage("購入に失敗しました。\n期限切れの商品です。");
                }
                catch (IAPStorePurchaseException e)
                {
                    // ストアエラー: 購入失敗
                    var message = e.Reason switch
                    {
                        IAPStorePurchaseExceptionReason.PurchasingUnavailable => "購入に失敗しました。端末設定に問題がないか確認してください。",
                        IAPStorePurchaseExceptionReason.ExistingPurchasePending => "購入に失敗しました。\n購入済みの商品です。",
                        IAPStorePurchaseExceptionReason.ProductUnavailable => "購入に失敗しました。\n購入できない商品です。",
                        IAPStorePurchaseExceptionReason.UserCancelled => "購入をキャンセルしました。",
                        IAPStorePurchaseExceptionReason.PaymentDeclined => "購入に失敗しました。\n購入支払いに問題が発生しました。",
                        IAPStorePurchaseExceptionReason.DuplicateTransaction => "購入に失敗しました。\n再購入できない商品です。",
                        _ => "購入処理に失敗しました。\n アプリの再起動をお願いします。"
                    };

                    ShowErrorMessage(message);
                }
                catch (Exception e)
                    when (e is IAPServerDuplicatePurchaseException
                              or IAPServerInvalidPurchaseException
                              or IAPServerInvalidReceiptException
                              or IAPServerPurchaseFailedException)
                {
                    var message = "購入処理に失敗しました。\n アプリの再起動をお願いします。";
                    ShowErrorMessage(message);
                }
                catch (IAPStoreDeferredPurchaseException)
                {
                    var message = "コンビニ決済が選択されました。\n決済完了後、ホーム画面にて購入結果の表示がされます。";
                    ShowErrorMessage(message, "確認");
                }
                // サーバーエラー
                catch (BillingVerifyReceiptFailedException)
                {
                    ShowErrorMessage("購入できない商品が購入されました。");
                }
                catch (BillingUnderagePurchaseLimitExceededException)
                {
                    ShowErrorMessage("購入限度額を超過しているため、\n購入いただくことができません。\n\n※ないようがわからないときは、\nおうちのひとにがめんをみせてください。");
                }
                catch (ShopTradeCountLimitException)
                {
                    ShowErrorMessage("購入上限回数に達しているため、\n購入いただくことができません。\n\n※ないようがわからないときは、\nおうちのひとにがめんをみせてください。");
                }
                catch (CurrencyAddCurrencyByOverMaxException)
                {
                    ShowErrorMessage("所持上限を超えるため、購入できません。");
                }
                catch (Exception e)
                    when (e is IAPServerBillingTransactionEndPurchaseLimitException or IAPServerBillingTransactionEndException)
                {
                    ShowErrorMessage(
                        "商品ご購入の処理中にエラーが発生したため、ご購入が正しく終了されませんでした。\n【ホーム画面>MENU>お問い合わせ】よりお問い合わせください。",
                        "重要");
                }
                catch (ServerBillingException e)
                {
                    // サーバーエラー汎用表示
                    ShowErrorMessage(ZString.Format("購入に失敗しました。\n【エラーコード:{0}】", e.ErrorCode));
                }
            });
        }

        void ShowErrorMessage(string message, string title = Title)
        {
            MessageViewUtil.ShowMessageWithClose(title, message);
        }
    }
}
