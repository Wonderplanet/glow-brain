using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Exceptions;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Modules.MessageView.Presentation;
using Zenject;

namespace GLOW.Core.Presentation.Wireframe
{
    public enum AdResultType
    {
        Success,
        Cancelled,
    }

    public class InAppAdvertisingWireframe
    {
        [Inject] IMessageViewUtil MessageView { get; }
        [Inject] InAppAdvertisingUseCase InAppAdvertisingUseCase { get; }

        public async UniTask<AdResultType> ShowAdAsync(
            IAARewardFeatureType iAARewardFeatureType,
            CancellationToken cancellationToken)
        {
            // 広告を表示
            var result = await InAppAdvertisingUseCase.ShowAdAsync(iAARewardFeatureType, cancellationToken);

            // ユーザーが視聴キャンセルした(キャンセル時HandlerのFinishもFailedも通らない)ときはキャンセルメッセージ出す
            if (result.Type == AdfurikunPlayRewardResultType.NotFinished)
            {
                var cancelModalCompletionSource = new UniTaskCompletionSource();
                ShowAdCancelledMessage(() => cancelModalCompletionSource.TrySetResult());
                await cancelModalCompletionSource.Task;
                return AdResultType.Cancelled;
            }
            if (result.Type == AdfurikunPlayRewardResultType.NetworkNotReachable)
            {
                var cancelModalCompletionSource = new UniTaskCompletionSource();
                ShowAdNetworkErrorMessage(() => cancelModalCompletionSource.TrySetResult());
                await cancelModalCompletionSource.Task;
                return AdResultType.Cancelled;
            }
            return AdResultType.Success;
        }

        void ShowAdCancelledMessage(Action onClosed)
        {
            MessageView.ShowMessageWithClose(
                "確認",
                "広告再生が中断されました",
                onClose: onClosed);
        }

        void ShowAdNetworkErrorMessage(Action onClosed)
        {
            MessageView.ShowMessageWithClose(
                "確認",
                "広告の再生に失敗しました。\nネットワーク接続を確認してください。",
                onClose: onClosed);
        }
    }
}
