using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class ReleaseEventQuestTutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);

            HighlightTarget("EventQuestCell");
            await ShowTutorialText(cancellationToken, "いいジャン祭クエストには\nここから参加しよう！", -400);
            UnHighlightTarget();
            HighlightTarget("EventMissionButton");
            await ShowTutorialText(cancellationToken, "イベントミッションも開催されるから\n確認してみてね！", -400);
            UnHighlightTarget();
            await HideTutorialText(cancellationToken);

            // 終了
            await FadeOutGrayOut(cancellationToken);

            // チュートリアル進捗更新
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.ReleaseEventQuest);
        }
    }
}