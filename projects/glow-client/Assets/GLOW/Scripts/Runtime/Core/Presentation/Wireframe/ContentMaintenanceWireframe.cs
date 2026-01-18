using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Modules.MessageView.Presentation;
using Zenject;

namespace GLOW.Core.Presentation.Wireframe
{
    public class ContentMaintenanceWireframe
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        public void ShowDialog(Action onClosed = null)
        {
            MessageViewUtil.ShowMessageWithOk(
                "確認",
                "このコンテンツはメンテナンス中です。" +
                "\n終了まで今しばらくお待ちください。" +
                "\n" +
                "\n※メンテナンスの詳細は、お知らせをご確認ください。",
                "",
                onClosed);
        }

        public async UniTask ShowDialogAsync(CancellationToken cancellationToken)
        {
            bool isClosed = false;
            ShowDialog(() =>
            {
                isClosed = true;
            });
            await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);
        }

        public void ShowDialogForResume(ContentMaintenanceType contentMaintenanceType, Action okClosed = null)
        {
            var contentMaintenanceTypeStr = ContentMaintenanceTypeToString(contentMaintenanceType);
            var firstLineMessage = string.IsNullOrEmpty(contentMaintenanceTypeStr)
                ? "現在、このコンテンツはメンテナンス中です。"
                : $"現在、「{contentMaintenanceTypeStr}」は\nメンテナンス中です。";


            MessageViewUtil.ShowMessageWithOk(
                "確認",
                firstLineMessage +
                "\n終了まで今しばらくお待ちください。" +
                "\n" +
                "\n※メンテナンスの詳細は、お知らせをご確認ください。",
                "",
                okClosed);
        }

        public async UniTask ShowDialogForResumeAsync(ContentMaintenanceType maintenanceType, CancellationToken cancellationToken)
        {
            bool isClosed = false;
            ShowDialogForResume(maintenanceType,() =>
            {
                isClosed = true;
            });
            await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);
        }

        string ContentMaintenanceTypeToString(ContentMaintenanceType type)
        {
            return type switch
            {
                ContentMaintenanceType.AdventBattle => "降臨バトル",
                ContentMaintenanceType.Pvp => "ランクマッチ",
                ContentMaintenanceType.EnhanceQuest => "コイン獲得クエスト",
                _ => ""
            };
        }
    }
}
