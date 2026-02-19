using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Exceptions;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ValueObject;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// 未処理のリストア課金結果を表示
    /// </summary>
    public class DeferredPurchaseResultAction : IHomeAppearanceAction
    {
        public class Factory : PlaceholderFactory<DeferredPurchaseResultAction> { }

        [Inject] GetHomeDeferredPurchaseUseCase GetHomeDeferredPurchaseUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }

        public async UniTask ExecuteAsync(HomeAppearanceActionContext context, Action onCloseCompletion,
            CancellationToken cancellationToken)
        {
            await ShowDeferredPurchaseResult(cancellationToken);

            HomeHeaderDelegate.UpdateStatus();
        }

        async UniTask ShowDeferredPurchaseResult(CancellationToken cancellationToken)
        {
            var deferredPurchaseResultModel = GetHomeDeferredPurchaseUseCase
                .GetDeferredPurchaseResult();

            if (!deferredPurchaseResultModel.ErrorCodes.IsEmpty())
            {
                await ShowDeferredPurchaseError(cancellationToken, deferredPurchaseResultModel.ErrorCodes);
                return;
            }

            foreach(var productResult in deferredPurchaseResultModel.ProductResults)
            {
                if (productResult.IsEmpty()) continue;
                if (productResult.ProductType == ProductType.Pass)
                {
                    var isClose = false;
                    MessageViewUtil.ShowMessageWithClose(
                        "購入完了",
                        ZString.Format("{0}の購入が完了しました", productResult.ProductName.ToString()),
                        onClose: () => isClose = true);
                    await UniTask.WaitUntil(() => isClose, cancellationToken: cancellationToken);
                    continue;
                }
                var viewModels = productResult.CommonReceiveResourceModels
                    .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                    .ToList();

                await CommonReceiveWireFrame.ShowAsync(
                    cancellationToken,
                    viewModels,
                    new RewardTitle("購入完了"),
                    new ReceivedRewardDescription(ZString.Format("{0}\nを購入しました", productResult.ProductName.Value)));
            }
        }

        async UniTask ShowDeferredPurchaseError(CancellationToken cancellationToken, IReadOnlyList<DeferredPurchaseErrorCode> errorCodes)
        {
            var isClose = false;
                var errorCodeList = errorCodes
                .Select(e => e.Value)
                .Distinct()
                .ToList();

            // トランザクション強制終了のメッセージ表示
            if (errorCodeList.Any(error =>
                    error is (int)ServerErrorCode.BillingTransactionEndPurchaseLimit
                        or (int)ServerErrorCode.BillingTransactionEnd))
            {
                await ShowMessageView("重要",
                    "商品ご購入の処理中にエラーが発生したため、ご購入が正しく終了されませんでした。\n【ホーム画面>MENU>お問い合わせ】よりお問い合わせください。",
                    cancellationToken);

                // トランザクション強制終了のエラーコードを除外
                errorCodeList = errorCodeList
                    .Where(error =>
                        error is not (int)ServerErrorCode.BillingTransactionEndPurchaseLimit
                        and not (int)ServerErrorCode.BillingTransactionEnd)
                    .ToList();

                if(errorCodeList.IsEmpty()) return;
            }

            errorCodeList.Remove(DeferredPurchaseErrorCode.RestoreFailed.Value);

            var errorCode = string.Join(", ", errorCodeList);
            var message = string.IsNullOrEmpty(errorCode)
                ? "処理できない購入情報があります。\n改善しない場合は「メニュー」>「お問い合わせ」よりお問い合わせください。"
                : ZString.Format("処理できない購入情報があります。\n改善しない場合は「メニュー」>「お問い合わせ」よりお問い合わせください。\n【エラーコード:{0}】", errorCode);

            await ShowMessageView("購入できません", message, cancellationToken);
        }

        async UniTask ShowMessageView(string title, string message, CancellationToken cancellationToken)
        {
            var isClose = false;
            MessageViewUtil.ShowMessageWithOk(title, message, onOk: () => isClose = true);
            await UniTask.WaitUntil(() => isClose, cancellationToken: cancellationToken);
        }
    }
}








