using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.QuestSelect.Presentation;
using GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect.Component;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class ReleaseHardTutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);
            
            // ホーム画面
            await ShowTutorialText(cancellationToken, "クエストの選び方を教えるね！", 0f);
            await HideTutorialText(cancellationToken);
            
            ShowIndicator("home_quest_button");
            await WaitClickEvent(cancellationToken, "home_quest_button");
            HideIndicator();
            await WaitViewPresentation<QuestSelectViewController>(cancellationToken);
            
            // キャンペーン吹き出しを非表示にする
            var questSelectView = FindTargetObject("quest_select_view").GetComponent<QuestSelectView>();
            questSelectView.SetActiveCampaignBalloon(false);
            
            // クエスト選択画面
            await ShowTutorialText(cancellationToken, "挑戦するクエストはこの画面で選べるよ", 0f);
            await ShowTutorialText(cancellationToken, "左に移動すると\n1つ前のクエストを選べるんだ", 0f);
            await HideTutorialText(cancellationToken);
            
            ShowIndicator("left_arrow_button");
            await WaitClickEvent(cancellationToken, "left_arrow_button");
            HideIndicator();

            // ページスクロール完了待機
            await UniTask.Delay(300, cancellationToken: cancellationToken);

            // ハードボタン無効化・ハイライトして演出再生
            var hardButtonComponent = FindTargetObject("hard_button").GetComponent<QuestDifficultyButtonComponent>();
            hardButtonComponent.SetButtonEnabled(false);
            HighlightTarget("hard_button");
            hardButtonComponent.PlayReleaseAnimation();

            await UniTask.Delay(2000, cancellationToken: cancellationToken);
            UnHighlightTarget();

            await ShowTutorialText(cancellationToken, "クエストをクリアすると\n新しい難易度が開放されることもあるよ！", 0f);
            await ShowTutorialText(cancellationToken, "右に移動すると\n1つ次のクエストを選べるよ", 0f);
            await HideTutorialText(cancellationToken);
            
            // ハードボタンを有効化する
            hardButtonComponent.SetButtonEnabled(true);

            ShowIndicator("right_arrow_button");
            await WaitClickEvent(cancellationToken, "right_arrow_button");
            HideIndicator();
            
            // ページスクロール完了待機
            await UniTask.Delay(300, cancellationToken: cancellationToken);
            
            // カルーセルビューを無効化し、スクロールを禁止する
            questSelectView.CarouselView.enabled = false;
            
            await ShowTutorialText(cancellationToken, "真ん中のクエストアイコンをタップして\n挑戦するクエストを決めよう！", 0f);
            await HideTutorialText(cancellationToken);
            
            // チュートリアル進捗更新(通信が競合する場合があるため、ここで更新)
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.ReleaseHardStage);
            
            // newのクエストアイコンを取得
            bool QuestCellSearch(TutorialIndicatorTarget target) => target.GetComponent<QuestSelectCell>().IsInitialSelected;
            
            ShowIndicator("quest_icon_button", QuestCellSearch);
            await WaitViewPresentation<HomeMainViewController>(cancellationToken);
            HideIndicator();
            await UniTask.Delay(100, cancellationToken: cancellationToken);
            
            
            // 終了
            await FadeOutGrayOut(cancellationToken);
        }
    }
}