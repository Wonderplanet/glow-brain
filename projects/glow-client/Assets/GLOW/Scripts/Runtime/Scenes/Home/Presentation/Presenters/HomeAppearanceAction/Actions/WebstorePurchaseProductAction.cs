using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Domain.UseCases;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    public class WebstorePurchaseProductAction : IHomeAppearanceAction
    {
        public class Factory : PlaceholderFactory<WebstorePurchaseProductAction> { }
        [Inject] WebstorePurchaseProductUseCase WebstorePurchaseProductUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        public async UniTask ExecuteAsync(HomeAppearanceActionContext context, Action onCloseCompletion,
            CancellationToken cancellationToken)
        {
            await ShowWebstorePurchaseProductResult(cancellationToken);
        }

        async UniTask ShowWebstorePurchaseProductResult(CancellationToken cancellationToken)
        {
            var isWebstore = WebstorePurchaseProductUseCase.HasWebstorePurchaseProduct();
            if (!isWebstore) return;

            var isClose = false;
            MessageViewUtil.ShowMessageWithOk(
                "購入完了",
                "外部購入が完了し、反映されました。\n※購入が反映されていない場合は、\n【ホーム画面>MENU>お問い合わせ】よりお問い合わせください。",
                onOk: () => isClose = true);

            // ユーザーが閉じるまで待機
            await UniTask.WaitUntil(() => isClose, cancellationToken: cancellationToken);
        }
    }
}
