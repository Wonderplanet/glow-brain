using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.MessageView.Presentation;
using WPFramework.Modules.Environment;
using Zenject;

namespace GLOW.Core.Application.ErrorHandle.Handlers
{
    public class NetworkEnvironmentErrorHandler : INetworkEnvironmentErrorHandler
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        public async UniTask<bool> HandleNetworkError(CancellationToken cancellationToken)
        {
            var taskCompletionSource = new UniTaskCompletionSource<bool>();
            
            //ダイアログ名：確認
            // 文言：通信エラーが発生しました。
            // 通信環境の良い場所でリトライしてください。
            // ボタン：リトライ
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "通信エラーが発生しました。",
                "通信環境の良い場所でリトライしてください。",
                "リトライ",
                () => taskCompletionSource.TrySetResult(true));

            return await taskCompletionSource.Task.AttachExternalCancellation(cancellationToken);
        }
    }
}