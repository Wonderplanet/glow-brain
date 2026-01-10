using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using UnityEngine.UI;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class ReleaseEventQuestTutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);

            // ハイライトするボタンのタップ無効化
            var eventButton = FindTargetObject("ModeSelectButton").GetComponent<Button>();
            eventButton.enabled = false;
            
            // ホーム画面
            HighlightTarget("ModeSelectButton");
            await ShowTutorialText(cancellationToken, "いいジャン祭が開催中だよ！", 0);
            await HideTutorialText(cancellationToken);
            
            ShowIndicator("ModeSelectButton");
            // ボタンを有効化する
            eventButton.enabled = true;
            await WaitClickEvent(cancellationToken, "ModeSelectButton");
            HideIndicator();

            // ゲームモード表示の待機(UIKit使っていないView)
            await UniTask.Delay(300, cancellationToken: cancellationToken);

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