using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.CommonReceiveView.Presentation.Views;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.IdleIncentiveTop.Presentation.Views;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class IdleIncentiveTutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);

            await ShowTutorialText(cancellationToken, "探索報酬が受け取れるようになったよ！", 0f);
            await HideTutorialText(cancellationToken);

            ShowIndicator("home_main_idle_incentive");
            await WaitClickEvent(cancellationToken, "home_main_idle_incentive");
            HideIndicator();

            // 探索画面
            await WaitViewPresentation<IdleIncentiveTopViewController>(cancellationToken);
            ShowIndicator("idle_incentive_receive_button");
            await WaitClickEvent(cancellationToken, "idle_incentive_receive_button");
            HideIndicator();

            // 報酬受け取り画面を閉じるのを待つ
            await WaitDismissModal<CommonReceiveViewController>(cancellationToken);

            await ShowTutorialText(cancellationToken, "探索報酬は10分ごとに\n自動でたまっていくんだ", 0f);
            await ShowTutorialText(cancellationToken, "最大24時間分までためておけるから\n1日1回受け取っておこうね！", 0f);
            await HideTutorialText(cancellationToken);

            // 終了
            await FadeOutGrayOut(cancellationToken);

            // チュートリアル進捗更新
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.IdleIncentive);
        }
    }
}